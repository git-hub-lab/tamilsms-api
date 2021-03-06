<?php

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('X-Robots-Tag: noindex, nofollow', true);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'conf.php';
require 'vendor/autoload.php';

$app = new Slim\App();

$app->add(new \Tuupola\Middleware\HttpBasicAuthentication([
    "path" => ["/add", "/update"],
    "realm" => "Protected",
    "users" => [
        "username" => "password" // https://www.htaccesstools.com/htpasswd-%20generator/ - Don't add your plain password here
    ],
    "error" => function ($response, $arguments) {
        $data = [];
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
  
        $body = $response->getBody();
        $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));
  
        return $response->withBody($body);
    }
]));

$app->get('/all','getSMS');
$app->get('/random','getRandom');
$app->post('/add','addSMS');
$app->put('/update/{id}','addUpdate');

$app->get('/', function (Request $request, Response $response) {
  $response->withStatus(200)->write("API v0.0.1");
  return $response;
});

function getSMS(Request $request, Response $response, $args) {
    $sql = "SELECT * FROM tamilcontent";
    try {
        $stmt = getDB()->query($sql);
        $wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $response->withJson($wines);
       //echo json_encode($wines, JSON_PRETTY_PRINT);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getRandom(Request $request, Response $response, $args) {
    $sql = "SELECT * FROM tamilcontent ORDER by RAND() limit 1";
    try {
        $stmt = getDB()->query($sql);
        $wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $response->withJson($wines);
       //echo json_encode($wines, JSON_PRETTY_PRINT);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function addSMS(Request $request, Response $response) {
    $content = $request->getParam('content');
    if (!empty($content)){
    $Getsms = htmlspecialchars($content, ENT_COMPAT);
    $sql = "INSERT INTO tamilcontent (content) VALUES (:content)";
    try{
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':content', $Getsms);
        $stmt->execute();
        echo '{"notice": {"text": "Post Added"}';
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
     }
    } else {
    echo '{"error": {"text": "Please add the Post Content"}';
  }
};

function addUpdate(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $content = $request->getParam('content');
    if (!empty($content)){
    $GetUpdates = htmlspecialchars($content, ENT_COMPAT);
    $sql = "UPDATE tamilcontent SET content = :content WHERE id = $id";
    try{
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':content', $GetUpdates);
        $stmt->execute();
        echo '{"notice": {"text": "Post Updated"}';
    } catch(PDOException $e){
        echo '{"error": {"text": '.$e->getMessage().'}';
     }
    } else {
    echo '{"error": {"text": "Please add the Post Content"}';
  }
};


$app->run();

?>