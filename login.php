<?php

/**
* Created by EtienneJ on 03/12/2015
* This webservice is used to manage the login on the Student'N 3 plateform.
*/

//Si aucune donnée n'est envoyée, on renvoit un message d'erreur et on arrête.
if(!$_POST) {
	echo json_encode(array("message" => "No params"));
	die();
}

//S'il manque des paramètres, on envoit un message d'erreur et on s'arrête
if(!isset($_POST["email"]) || !isset($_POST["passwd"])) {
	echo json_encode(array("message" => "Missing arguments"));
	die();
}

//Sinon, si on a les bonnes entrées, on les récupère et on les traite
else {

	$email = htmlspecialchars($_POST["email"]);
	$passwd = htmlspecialchars($_POST["passwd"]);
	$ipAddress = $_SERVER["REMOTE_ADDR"];

	//Si l'adresse email n'est pas bien formatée, on renvoit un message d'erreur et on s'arrête
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo json_encode(array("message" => "No valid email"));
		die();
	}

	//Si l'adresse IP n'est pas valide (ne devrait jamais arriver), on renvoit un message d'erreur et on s'arrête
	if(!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
		echo json_encode(array("message" => "No valid ipaddress"));
		die();
	}
	
	//Si les paramètres données sont bons, on peu continuer, on va vérifier que l'utilisateur existe dans la base de données MySQL des utilisateurs, avec le couple email/password
	$pdo = new PDO('mysql:host=localhost;dbname=StudentN3;charset=utf8', '****', '****');

	$sql = 'SELECT * FROM users WHERE email = :email';
	$sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$sth->execute(array(':email' => $email));
	$res = $sth->fetch();

	//Si on a un résultat
	if($res) {
		//On vérifie que les mot de passe de la base et celui donné en POST correspondent
		if(password_verify($passwd, $res["mot_de_passe"])) {
			
			//Si les mots de passe correspondent, on passe à la base mongoDB pour voir si un token n'est pas déjà attribué à cet utilisateur
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

			//On vérifie si un token n'existe pas déj pour cet utilisateur
			$cursor = $collection->find(array('email' => $email));
			$doc = array();
			foreach ($cursor as $doc) {
				$result[] = $doc;
			}

			//Si on a un résultat, on renvoit le token d'authentification, et n met à jour l'adresse IP correspondant au token passé
			if(!empty($result)) {
				$updateCursor = $collection->update(array("email" => $email), array('$set' => array('ip' => $ipAddress)));
				echo json_encode(array("message" => "Already registered", 'token' => $result[0]['token'], 'prenom' => $res["prenom"], 'nom' => $res["nom"], 'date_naissance' => date("d-m-Y", strtotime($res["date_naissance"])), 'telephone' => $res["telephone"], 'ecole' => $res["ecole"]));
				die();
			}

			//Sinon, on crée un nouveau token, on enregistre en base et on renvoit les infos à l'utilisateur
			$token = bin2hex(openssl_random_pseudo_bytes(16));
			$collection->insert( array( 'token' => $token,
								'prenom' => $res["prenom"],
								'email' => $email,
								'ip' => $ipAddress,
								'dateCreation' => $dateCreation = new MongoDate(),
	 							) 
						);
			echo json_encode(array("message" => 'Success', 'token' => $token, 'prenom' => $res["prenom"], 'nom' => $res["nom"], 'date_naissance' => date("d-m-Y", strtotime($res["date_naissance"])), 'telephone' => $res["telephone"], 'ecole' => $res["ecole"]));	
		
		}

		//Si le mot de passe est incorrect, on renvoit un message d'erreur et on s'arrête
		else {
			echo json_encode(array("message" => "Bad password"));
		}
	}

	//Si on a pas trouvé de couple email/mot de passe correspondant dans la base, on renvoit un message d'erreur et on s'arrête
	else {
		echo json_encode(array("message" => "Not match"));
	}
}
?>