<?php
/**
* Created by EtienneJ on 17/12/2015
* This webservice is used to add a "Bon plan" in the Student'N3 data base.
*/

//Si aucune donnée n'est envoyée, on renvoit un message d'erreur et on arrête.
if(!$_POST) {
	echo json_encode(array("message" => "No POST"));
	die();
}

//Si aucun token n'est passé, on renvoit un message d'erreur spécifique et on s'arrête
if(!$_POST['token']) {
	echo json_encode(array("message" => "No token gived"));
	die();
}


//Sinon, on va vérifier le token
$token = htmlspecialchars($_POST['token']);
try
{
	$m = new MongoClient("mongodb://****:****@localhost/StudentN3"); // connect
	$db = $m->selectDB("StudentN3");
}
catch ( MongoConnectionException $e )
{
	echo json_encode(array("message" => 'Couldn\'t connect to mongodb, is the "mongo" process running?'));
	die();
}
$collection = $db->StudentN3_tokens;
$cursor = $collection->find(array('token' => $token));
	$doc = array();
	foreach ($cursor as $doc) {
		$result[] = $doc;
	}

//Si on ne trouve aucun résultat, on renvoit un message d'erreur et on s'arrête
if(empty($doc)) {
	echo json_encode(array("message" => "Invalid token"));
	die();
}
//Si l'adresse IP associée au token n'est pas celle de l'utilisateur qui tente d'accèder à ce service, on renvoit un message d'erreur et on s'arrête
if($result[0]["ip"] != $_SERVER["REMOTE_ADDR"]) {
	echo json_encode(array('message' => 'Invalid IP'));
	die();
}

//Si il manque des arguments obligatoires pour créer un bon plan, on renvoit un message d'erreur et on s'arrête
if(!isset($_POST['nom']) || !isset($_POST["adresse"]) || !isset($_POST["description"]) || !isset($_POST["type"]) || !isset($_POST["dateDebut"]) || !isset($_POST["dateFin"])) {
	echo json_encode(array("message" => "Missing params"));
	die();
}

//Si le format de date en entrée n'est pas bon, on renvoit un message d'erreur et on s'arrête
if( strlen(htmlspecialchars($_POST["dateDebut"])) != 10 || substr_count(htmlspecialchars($_POST["dateDebut"]), '-') != 2) {
	echo json_encode(array("message" => "Bad date format"));
	die();
}

//Sinon, on traite les informations passées
$nom = htmlspecialchars($_POST['nom']);
$adresse = htmlspecialchars($_POST["adresse"]);
$description = htmlspecialchars($_POST["description"]);
$type = htmlspecialchars($_POST["type"]);
$longitude = htmlspecialchars($_POST["longitude"]);
$latitude = htmlspecialchars($_POST["latitude"]);
//On formate la date pour la base de données
$dateDebut = new MongoDate(strtotime(htmlspecialchars($_POST["dateDebut"])));

/*
* Traitement spécial pour la date de fin (optionnelle)
*/
//Si on a une date de fin en entrée
if(isset($_POST["dateFin"]) && $_POST["dateFin"] !== "") {
	//Si le format de la date n'est pas bon, on renvoit un message d'erreur et on s'arrête
	if( strlen(htmlspecialchars($_POST["dateFin"])) != 10 || substr_count(htmlspecialchars($_POST["dateFin"]), '-') != 2) {
		echo json_encode(array("message" => "Bad date format"));
		die();
	}
	//Sinon on la formate correctement pour la base de données
	else {
		$dateFin = new MongoDate(strtotime(htmlspecialchars($_POST["dateFin"])));
	}
}
//Si aucune date n'est donnée, on initialise la variable à vide
else {
	$dateFin = '';
}

//On récupère le nom de l'auteur depuis la requête concernant le token
$auteur = $result[0]["prenom"];
//La date de création à la date du jour et bien formatée pour la base
$dateCreation = new MongoDate();

if(!$db->StudentN3_BonsPlans) {
	$collection = $db->createCollection("StudentN3_BonsPlans");
}
else {

	$collection = $db->StudentN3_BonsPlans;
}

//On vérifie que le bon plan n'existe pas encore
$cursor = $collection->find(array('nom' => $nom));
$doc = array();
foreach ($cursor as $doc) {
	$result[] = $doc;
}

//Si le bon plan existe déjà, on renvoit un message d'erreur et on s'arrête
if(!empty($doc)) {
	echo json_encode(array("message" => "Already recorded"));
	die();
}
//Sinon on ajoute le bon plan et on renvoit le message de succes
else {
	$collection->insert( array( 'nom' => $nom,
								'auteur' => $auteur,
								'dateCreation' => $dateCreation,
								'adresse' => $adresse,
								'description' => $description,
								'type' => $type,
								'dateDebut' => $dateDebut,
								'dateFin' => $dateFin,
								'longitude' => $longitude,
								'latitude' => $latitude,

	 							) 
						);
	echo json_encode(array("message" => "Success"));
}

?>