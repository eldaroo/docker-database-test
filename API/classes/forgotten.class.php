<?php
require_once 'auth.class.php';
require_once 'responses.class.php';


class forgotten extends auth{

    public function getToken($userId){ /* FIX ME */
        $_responses = new responses;
        $hasToken = parent::userHasToken($userId);
        if ($hasToken){
            $query = "SELECT Token FROM usuarios_token WHERE UsuarioId = '$userId'";
            return parent::getData($query);
        }else{
            return $_responses->error_200("Para cambiar la contraseña debes iniciar sesion al menos una vez");
        
        }
    }
    public function newPass($json){
      
        $_responses = new responses;
        $reset_data = json_decode($json,true);
        if(!isset($reset_data['user']) || !isset($reset_data["token"])){
            //error con los campos
            return $_responses->error_400();
        }else{
            //todo esta bien 
            $forgotten_user = $reset_data['user'];
            $forgotten_token = $reset_data['token'];
            $reset_db = parent::getUserData($forgotten_user);
            
            if($reset_db){
                $user_id = $reset_db[0]["UsuarioId"];
                $user_pass = $reset_db[0]["Password"];
                $user_status = $reset_db[0]['Estado'];
                $user_rol = $reset_db[0]['Rol'];
                //verificar si la contraseña es igual
                if($user_status == "Activo"){
                    if($user_rol){
                        //crear el token o actualiza si existe
                        $token_db = parent::userHasToken($user_id);
                        if($token_db){
                            if (count($token_db) <= 1) {
                                if($token_db[0]["Token"] == $forgotten_token){
                                    $token_status = $token_db[0]["Estado"];
                                    $newPass = $this->generatePass();
                                    $newPass_encripted = parent::encript($newPass);
                                    if($newPass_encripted != $user_pass){
                                        if ($token_status == "Inactivo"){
                                            $user_token = parent::updateToken($user_id);
                                        }elseif ($token_status == "Activo"){
                                            $user_token = $token_db[0]["Token"];
                                            parent::deactivateToken($user_id);
                                        }else{
                                            parent::deactivateToken($user_id);
                                            return $_responses->error_200("Base de datos actualizada. Volver a intentar");
                                        }
                                    }else{
                                        //la nueva contraseña es igual
                                        return $_responses->error_200("El nuevo password no puede ser el mismo");
                                    }
                                }else{
                                    return $_responses->error_401();
                                }
                            }else{
                                parent::removeDuplicateTokens($user_id);
                                return $_responses->error_200("Base de datos actualizada. Volver a intentar");
                            }
                        }else{
                            return $_responses->error_200("Intente iniciar sesion con la contraseña asignada");
                        }
                        if($user_token){
                            // si se guardo el token
                            parent::updatePass($user_id,$newPass_encripted);
                            $result = $_responses->response;
                            $result["result"] = array(
                                "token" => $user_token,
                                "rol" => $user_rol,
                                "newPass" => $newPass
                            );
                            return $result; //retorna el token, el rol y nueva contraseña!
                        }else{
                                //error al guardar token
                                return $_responses->error_500("Error interno, No hemos podido guardar");
                        }
                    }else{
                        return $_responses->error_200("El usuario no tiene rol definido");
                    }
                }else{
                    //el usuario esta inactivo
                    return $_responses->error_200("El usuario esta inactivo");
                }
            }else{
                //no existe el usuario
                return $_responses->error_200("El usuario $forgotten_user no existe ");
            }
        }
    }

    private function generatePass(){
        $val = true;
        $newPass = bin2hex(openssl_random_pseudo_bytes(8,$val));
        return $newPass;
    }
}




?>