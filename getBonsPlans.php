<?php
/**
* Created by EtienneJ on 20/12/2015
* This webservice is used to get all the "bon plans", in the database, witch are currently available */
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

//On récupère en base tous les bons plans dont la date de fin est plus grande que la date du jour ou si la date de fin est vide
$collection = $db->StudentN3_BonsPlans;
$query = array( '$or' => array( array('dateFin' => array( '$gt' => new MongoDate())), array('dateFin' => '') ) );
$cursor = $collection->find($query);

$result = array();

//On formate les résultats pour pouvoir les renvoer correctement
foreach ($cursor as $doc) {
    $bonPlan["nom"] = $doc['nom'];
    $bonPlan["auteur"] = $doc['auteur'];
    $bonPlan["dateCreation"] = date('d-m-Y', $doc['dateCreation']->sec);
    $bonPlan["adresse"] = $doc['adresse'];
    $bonPlan["description"] = $doc['description'];
    $bonPlan["type"] = $doc['type'];
    $bonPlan["dateDebut"] = date('d-m-Y',$doc['dateDebut']->sec);
    $bonPlan["dateFin"] = date('d-m-Y',$doc['dateFin']->sec);
    $bonPlan["longitude"] = $doc['longitude'];
    $bonPlan["latitude"] = $doc['latitude'];

    $result[] = $bonPlan;
}
//On renvoit les résultats sous forme d'un JSON
echo(json_encode(array("message" => $result)));
?>