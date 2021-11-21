<?php

require_once "connection/connection.php";
require_once "responses.class.php";
require_once "auth.class.php";
require_once 'recorder.class.php';

class bundles extends connection {

    private $table = "bundles";

    public function getBundles(){
        $query = "SELECT * FROM bundles";
        $data = parent::getData($query);
        return ($data);
    }

    public function addBundle($json){
        $_responses = new responses;
        $data = json_decode($json,true);
        if(!isset($data['token'])){
            return $_responses->error_401("no token, no rol, no user id");
        }else{
            $this->token = $data['token'];
            $arrayToken = $this->checkToken();
            if($arrayToken){
                $this->name= $data['name'];
                $query = "INSERT INTO " . $this->table . " (name) VALUES ('$this->name')";
                $resp = parent::nonQueryId($query);
                if($resp){
                    $response = $_responses->response;
                    $response["result"] = array(
                        "id" => $resp
                    );
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