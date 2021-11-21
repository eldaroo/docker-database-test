<?php

require_once "connection/connection.php";
require_once "responses.class.php";
require_once "auth.class.php";
require_once 'recorder.class.php';

class directions extends connection {

    private $table = "directions";

    public function getDirections(){
        $query = "SELECT * FROM directions";
        $data = parent::getData($query);
        return ($data);
    }

    public function addDirection($json){
        $_responses = new responses;
        $data = json_decode($json,true);
        if(!isset($data['token'])){
            return $_responses->error_401("Se necesita un token y tiene que ser valido");
        }else{
            $this->token = $data['token'];
            $arrayToken = $this->checkToken();
            if($arrayToken){
                $this->direction = $data['direction'];
                $this->name= $data['name'];
                //$this->usersId = !!$data['usersId'] ? $data['userId'] : 0;
                //$this->deliveryDate = !!$data['deliveryDate'] ? $data['deliveryDate'] : 0;

                $query = "INSERT INTO " . $this->table . " (direction, name) VALUES ('$this->direction', '$this->name')";
                $resp = parent::nonQueryId($query);
                if($resp){
                    $response = $_responses->response;
                    $response["result"] = array(
                        "id" => $resp
                    );
                    $action = array(
                        "title"=> "Dirección Agregada agregada",
                        "user"=> $this->usersId,
                   //     "deliveryDate"=> $this->deliveryDate,
                   //     "change"=> "Se agregó la dirección: ". $this->direction ." correspondiente a ". $this->name
                    ); 
                    recorder::recordData($action);
                    return $response;
                }else{
                    return $_responses->error_500($this->qry_error);
                }
        }else{
                return $_responses->error_401("El Token que envio es invalido o ha caducado");
            }
        }
    }
}
?>