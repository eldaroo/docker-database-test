<?php
require_once 'auth.class.php';
require_once 'responses.class.php';


class signOff extends auth{

    public function logout($json){
      
        $_responses = new responses;
        $logout_data = json_decode($json,true);
        if(!isset($logout_data['userId']) || !isset($logout_data['token'])){
            //error con los campos
            return $_responses->error_400();
        }else{
            //todo esta bien 
            $user_id = $logout_data['userId'];
            $user_token = $logout_data['token'];
            $token_db = parent::userHasToken($user_id);
            if(true){
                if($token_db[0]['Estado'] != 'Inactivo'){
                    $logout = parent::deactivateToken($user_id);
                    if ($logout){
                        return $logout;
                    }else{
                        return $_responses->error_500('Error desconocido de servidor');
                    }
                }else{
                    return $_responses->error_200("Ya cerro sesion");
                }
            }else{
                return $_responses->error_401("Los datos de sesion no coinciden");
            }         
        }
    }
}




?>