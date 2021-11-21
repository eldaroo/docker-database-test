<?php
require_once 'connection/connection.php';

class token extends connection{

    function updateTokens($fecha){
        $query = "update usuarios_token set Estado = 'Inactivo' WHERE  Fecha < '$fecha'";
        $verify = parent::nonQuery($query);
        if($verify){
            $this->writeLog($verify);
            return $verify;
        }else{
            return 0;
        }
    }

    function logoutToken($userID){
        $query = "update usuarios_token set Estado = 'Inactivo' WHERE  UsuarioId < '$userID'";
        $verify = parent::nonQuery($query);
        if($verify){
            $this->writeLog($verify);
            return $verify;
        }else{
            return 0;
        }
    }
    /*function setTokenOutOfDate(){
        $verify = parent::getData("https://logisticait.test/API/cron/actualizar_tokens");
        if($verify){
            $this->writeLog($verify);
            return $verify;
        }else{
            return 0;
        }
    }*/

    function createTxt($log_url){
           $file = fopen($log_url, 'w') or die ("error al crear el archivo de registros");
           $text = "------------------------------------ Registros del CRON JOB ------------------------------------ \n";
           fwrite($file,$text) or die ("no pudimos escribir el registro");
           fclose($file);
    }

    function writeLog($logs){
        $log_url = "../cron/registros/log.txt";
        if(!file_exists($log_url)){
            $this->createTxt($log_url);
        }
        /* crear una entrada nueva */
        $this->writeTxt($log_url, $logs);
    }

    function writeTxt($log_url, $logs){
        $date = date("Y-m-d H:i");
        $file = fopen($log_url, 'a') or die ("error al abrir el archivo de registros");
           $text = "Se modificaron $logs registro(s) el dia [$date] \n";
           fwrite($file,$text) or die ("no pudimos escribir el registro");
           fclose($file);
    }
}

?>