<?php
require_once 'classes/responses.class.php';
require_once 'classes/orders.class.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, id, token, User-Agent, state");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
$_responses = new responses;
$_orders = new orders;

//recibimos los datos enviados
$postBody = file_get_contents("php://input");
//enviamos los datos al manejador
$dataArray = $_orders->completeOrder($postBody);
//delvovemos una respuesta 
 header('Content-Type: application/json');
 if(isset($dataArray["result"]["error_id"])){
     $responseCode = $dataArray["result"]["error_id"] ;
     http_response_code($responseCode);
 }else{
     http_response_code(200);
 }
 echo json_encode($dataArray);
