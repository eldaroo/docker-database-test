<?php



class connection {

    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $connection;


    function __construct(){
        $data_list = $this->connectData();
        foreach ($data_list as $key => $value) {
            $this->server = $value['server'];
            $this->user = $value['user'];
            $this->password = $value['password'];
            $this->database = $value['database'];
            $this->port = $value['port'];
        }
        $this->connection = new mysqli($this->server,$this->user,$this->password,$this->database,$this->port);
        if($this->connection->connect_errno){
            echo "algo va mal con la connection";
            die();
        }

    }

    private function connectData(){
        $base_url = dirname(__FILE__);
        $json_data = file_get_contents($base_url . "/" . "config");
        return json_decode($json_data, true);
    }

    private function convert_UTF8($array){
        array_walk_recursive($array,function(&$item,$key){
            if(!mb_detect_encoding($item,'utf-8',true)){
                $item = utf8_encode($item);
            }
        });
        return $array;
    }


    public function getData($sqlstr){
        $results = $this->connection->query($sqlstr);
        $resultArray = array();
        if (is_array($results) || is_object($results))
        {
            foreach ($results as $key) {
                $resultArray[] = $key;
            }
            return $this->convert_UTF8($resultArray);
        }
    }



    public function nonQuery($sqlstr){
        $results = $this->connection->query($sqlstr);
        return $this->connection->affected_rows;
    }


    //INSERT 
    public function nonQueryId($sqlstr){
         $results = $this->connection->query($sqlstr);
         $rows = $this->connection->affected_rows;
         if($rows >= 1){
            return $this->connection->insert_id;
         }else{
             return 0;
         }
    }
     
    //encript
    protected function encript($string){
        return md5($string);
    }

    protected function checkToken(){
        $query = "SELECT TokenId,UsuarioId,Estado from usuarios_token WHERE Token = '" . $this->token . "' AND Estado = 'Activo'";
        $resp = $this->getData($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }

}



?>