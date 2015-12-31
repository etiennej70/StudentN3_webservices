<?php
/**
* Created by EtienneJ on 03/12/2015
* This webservice is used to manage the logout on the Student'N 3 plateform.
*/

//Si aucune donnée n'est envoyée, on renvoit un message d'erreur et on arrête.
if(!$_POST) {
	echo json_encode(array("message" => "No POST"));
	die();
}

//S'il manque des paramètres, on envoit un message d'erreur et on s'arrête
if(!isset($_POST["token"])) {
	echo json_encode(array("message" => "Missing arguments"));
	die();
}

//Sinon, si on a les bonnes entrées, on les récupère et on les traite
else {
	$token = htmlspecialchars($_POST["token"]);

	//On vérifie si le token existe bien
	try
	{
	    $m = new MongoClient("mongodb://****:****@localhost/StudentN3"); // connect
	    $db = $m->selectDB("StudentN3");
	}
	catch ( MongoConnectionException $e )
	{
	    echo json_encode(array("message" => 'Couldn\'t connect to mongodb'));
	    die();
	}

	if(!$db->StudentN3_BonsPlans) {
		$collection = $db->createCollection("StudentN3_tokens");
	}
	else {

		$collection = $db->StudentN3_tokens;
	}

	$cursor = $collection->find(array('token' => $token));
	$doc = array();
	foreach ($cursor as $doc) {
		$result[] = $doc;
	}

	//Si le token est bien présent en base, on le supprime et on renvoit l'information de succès
	if(!empty($result)) {
		$updateCursor = $collection->remove(array("token" => $token), array("justOne" => true));
		echo json_encode(array("message" => "Success"));
		die();
	}

	//Sinon, on informe de l'erreur : le token n'est pas bon car introuvable en base
	else {
		echo json_encode(array("message" => "Bad token"));
	}
}
?>