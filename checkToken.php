<?php
/**
* Created by EtienneJ on 03/12/2015
* This webservice is used to manage token checking
*/

//Si aucune donnée n'est envoyée, on renvoit un message d'erreur et on arrête.
if(!$_POST) {
	echo json_encode(array("message" => "No POST"));
	die();
}
//S'il manque des paramètres, on envoit un message d'erreur et on s'arrête
if(!isset($_POST["token"])) {
	echo json_encode(array("message" => "No token"));
	die();
}

//Sinon, si on a les bonnes entrées, on les récupère et on les traite
else {

	$token = htmlspecialchars($_POST["token"]);
	$ipAddress = $_SERVER["REMOTE_ADDR"];

	//On vérifie que l'adresse IP est au bon format (normalement, impossible que a arrive)
	if(!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
		echo json_encode(array("message" => "No valid ipaddress"));
		die();
	}

	try
	{
	    $m = new MongoClient("mongodb://*****:******@localhost/StudentN3"); // connect
	    $db = $m->selectDB("StudentN3");
	}
	catch ( MongoConnectionException $e )
	{
	    echo json_encode(array("message" => 'Couldn\'t connect to mongodb, is the "mongo" process running?'));
	    die();
	}

	//On vérifie l'existence d'une ligne dans la base correspondant au couple "token"/"adresse IP" donné
	$collection = $db->StudentN3_tokens;
	$query = array( '$and' => array( 
									array('token' => $token), 
									array('ip' => $ipAddress) 
									) 
					);

	$cursor = $collection->find($query);

	$doc = array();
	foreach ($cursor as $doc) {
		$result[] = $doc;
	}

	//Si ce couple existe, on renvoit un message de succès, sinon on renvoit un message d'erreur
	if(!empty($result)) {
		echo json_encode(array("message" => "Success"));
		die();
	}
	else {
		echo json_encode(array("message" => "Invalid token"));
	}
}
?>