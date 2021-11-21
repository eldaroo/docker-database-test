<?php 
require_once 'classes/reset.class.php';
require_once 'classes/responses.class.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$_resetPass = new reset;
$_respuestas = new responses;



if($_SERVER['REQUEST_METHOD'] == "PUT"){

    //recibir datos
    $postBody = file_get_contents("php://input");

    //enviamos los datos al manejador
    $dataArray = $_resetPass->resetPass($postBody);

    //delvolvemos una respuesta
    header('Content-Type: application/json');
    if(isset($dataArray["result"]["error_id"])){
        $responseCode = $dataArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($dataArray);


}else{
    header('Content-Type: application/json');
    $dataArray = $_respuestas->error_405();
    echo json_encode($dataArray);

}

?>