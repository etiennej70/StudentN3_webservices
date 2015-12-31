<?php
/**
* Created by EtienneJ on 24/12/2015
* This webservice is used to get details of a bon plan from his name
*/

//Si aucune donnée n'est envoyée, on renvoit un message d'erreur et on arrête.
if(!$_POST) {
    echo json_encode(array("message" => "No POST"));
    die();
}

//S'il manque des paramètres, on envoit un message d'erreur et on s'arrête
if(!isset($_POST["nom"])) {
    echo json_encode(array("message" => "Missing arguments"));
    die();
}

//sinon, on récupère les infos passées et on les traite
$nom = htmlspecialchars($_POST['nom']);

try
{
    $m = new MongoClient("mongodb://****:****@localhost/StudentN3"); // connect
    $db = $m->selectDB("StudentN3");
}
catch ( MongoConnectionException $e )
{
    echo 'Couldn\'t connect to mongodb, is the "mongo" process running?';
    exit();
}

$collection = $db->StudentN3_BonsPlans;
$query = array( 'nom' => $nom );
$cursor = $collection->find($query);

$result = array();

//On récupère les infos du bon plan à partir de son nom et on les formate
foreach ($cursor as $doc) {
    $bonPlan["nom"] = $doc['nom'];
    $bonPlan["auteur"] = $doc['auteur'];
    $bonPlan["dateCreation"] = date('d-m-Y', $doc['dateCreation']->sec);
    $bonPlan["adresse"] = $doc['adresse'];
    $bonPlan["description"] = $doc['description'];
    $bonPlan["type"] = $doc['type'];
    $bonPlan["dateDebut"] = date('d-m-Y',$doc['dateDebut']->sec);
    $bonPlan["dateFin"] = date('d-m-Y',$doc['dateFin']->sec);

    $result[] = $bonPlan;
}

//Si il n'y a pas de bon plan avec ce nom, on renvoit un message d'erreur
if(empty($result)) {
    echo(json_encode(array("message" => "No match"))); 
}
//Sinon on renvoit le résultat sous la forme d'un JSON
else {
    echo(json_encode(array("message" => $result)));   
}

?>