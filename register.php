<?php
/**
* Created by EtienneJ on 05/12/2015
* This webservice is used to register a new user on the Student'N3 plateform
*/

//Si aucune donnée n'est envoyée, on renvoit un message d'erreur et on arrête.
if(!$_POST) {
	echo json_encode(array("message" => "No POST"));
}

//S'il manque des paramètres, on envoit un message d'erreur et on s'arrête
if(!isset($_POST['nom']) || !isset($_POST["prenom"]) || !isset($_POST["dateNaissance"]) || !isset($_POST["email"]) || !isset($_POST["motdepasse"]) || !isset($_POST["ecole"])) {
	echo json_encode(array("message" => "Missing params"));
	die();
}

//Sinon, si on a les bonnes entrées, on les récupère et on les traite
else {

$nom = strtoupper(htmlspecialchars($_POST["nom"]));
$prenom = htmlspecialchars($_POST["prenom"]);
$dateNaissance = date('Y-m-d', strtotime($_POST["dateNaissance"]));
$email = htmlspecialchars($_POST["email"]);
$ecole = htmlspecialchars($_POST["ecole"]);
$passwd = htmlspecialchars($_POST["motdepasse"]);

//On gère le bon formatage du numéro de téléphone et son côté optionel
if(isset($_POST["telephone"])) {
	
	if (sizeof($_POST["telephone"]) != 10 && $_POST["telephone"] != 0) {
		echo json_encode(array("message" => "Telephone length error"));
		die();
	}
	else {
		$telephone = htmlspecialchars($_POST["telephone"]);
	}
}
else {
	$telephone = 0;
}

//Si l'email n'est pas au bon format, on renvoit un message d'erreur et on s'arrête
if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo json_encode(array("message" => "No valide email"));
	die();
}

//Cryptage du mot de passe
$hashPasswd = password_hash($passwd, PASSWORD_DEFAULT);

$pdo = new PDO('mysql:host=localhost;dbname=StudentN3;charset=utf8', '****', '****');

$sql_prepared = $pdo->prepare("SELECT email FROM users WHERE email = :email", array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sql_prepared->execute(array(':email' => $email));
$res = $sql_prepared->fetchAll();

//Si l'utilisateur est déjà en base de données, on renvoit un message d'erreur et on s'arrête
if(!empty($res)) {
	echo json_encode(array("message" => "Already registered"));
}
//Sinon, on insère le nouvel utilisateur en base, et on renvoit un message de succès
else {
	$sql_prepared = $pdo->prepare('INSERT INTO users(nom, prenom, date_naissance, email, ecole, mot_de_passe) VALUES (:nom, :prenom, :date_naissance, :email, :ecole, :mot_de_passe)', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sql_prepared->execute(array(':nom' => $nom, ':prenom' => $prenom, ':date_naissance' => $dateNaissance, ':email' => $email, 'ecole'=> $ecole, ':mot_de_passe' => $hashPasswd));
	$res = $sql_prepared->fetchAll();

	echo json_encode(array("message" => "Success"));
}



}

?>