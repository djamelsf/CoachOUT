<?php

namespace Djs\Framework;
use Djs\Application\StorageMySQL;

set_include_path("./src");

session_start();


require ("autoload.php");




/* Cette page est simplement le point d'arrivée de l'internaute
 * sur notre site. On se contente de lancer le FrontController.
 *
 */

$server = $_SERVER;



//////BD
$servername = "localhost";
$username = "DJAM";
$password = "17421742";
$dbname = "API_STRAVA";
$connexion=null;


try {
    $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $connexion=$conn;
}
catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
}

/////////////
///
$storage=new StorageMySQL($connexion);
// simuler une requête AJAX
//$server['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
$request = new Request($_GET, $_POST, $_FILES, $server,$_SESSION);
$response = new Response();
$router = new FrontController($request, $response,$storage);
$router->execute();

?>