<?php
require_once 'auth.class.php';
require_once 'responses.class.php';


class reset extends auth{

    public function resetPass($json){
      
        $_responses = new responses;
        $reset_data = json_decode($json,true);
        if(!isset($reset_data['user']) || !isset($reset_data["password"]) || !isset($reset_data["newPassword"]) || !isset($reset_data["newPassword2"])){
            //error con los campos
            return $_responses->error_400();
        }else{
            //todo esta bien 
            $reset_user = $reset_data['user'];
            $reset_oldPassword = $reset_data['password'];
            if ($reset_data['newPassword'] != '' || $reset_data['newPassword2'] != ''){
                $reset_newPassword = $reset_data['newPassword'];
                $reset_newPassword2 = $reset_data['newPassword2'];
            }else{
                return $_responses->error_200("La nueva contraseña no puede estar vacia");
            }
            $reset_oldPassword = parent::encript($reset_oldPassword);
            $reset_newPassword = parent::encript($reset_newPassword);
            $reset_newPassword2 = parent::encript($reset_newPassword2);
            if ($reset_newPassword != $reset_newPassword2){
                return $_responses->error_200("La nueva contraseña no coincide con la confirmacion de la nueva contraseña");
            }else{
                $reset_pass = $reset_newPassword;
                $reset_db = parent::getUserData($reset_user);
            }
            if($reset_db){
                $user_id = $reset_db[0]["UsuarioId"];
                $user_pass = $reset_db[0]["Password"];
                $user_status = $reset_db[0]['Estado'];
                $user_rol = $reset_db[0]['Rol'];
                //verificar si la contraseña es igual
                if($user_pass == $reset_oldPassword){
                    if($reset_pass != $user_pass){
                            if($user_status == "Activo"){
                                if($user_rol){
                                    //crear el token o actualiza si existe
                                    $token_db = parent::userHasToken($user_id);
                                    if($token_db){
                                        if (count($token_db) <= 1) {
                                            $token_status = $token_db[0]["Estado"];
                                            if ($token_status == "Inactivo"){
                                                $user_token = parent::updateToken($user_id);
                                            }elseif ($token_status == "Activo"){
                                                $user_token = $token_db[0]["Token"];
                                            }else{
                                                parent::deactivateToken($user_id);
                                                return $_responses->error_200("Base de datos actualizada. Volver a intentar");
                                            }
                                        }else{
                                            parent::removeDuplicateTokens($user_id);
                                            return $_responses->error_200("Base de datos actualizada. Volver a intentar");
                                        }
                                    }else{
                                        $user_token = parent::insertToken($user_id);
                                    }
                                    if($user_token){
                                        // si se guardo el token
                                        $passUpdated = $reset_pass ? parent::updatePass($user_id,$reset_pass) : null;
                                        if($passUpdated){
                                            $result = $_responses->response;
                                            $result["result"] = array(
                                                "token" => $user_token,
                                                "rol" => $user_rol
                                            );
                                            return $result; //retorna el token y el rol!
                                        }else{
                                            //error al guardar pass
                                            return $_responses->error_500("Error interno, No hemos podido guardar la password");
                                        }
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
                        //la nueva contraseña es igual
                        return $_responses->error_200("El nuevo password no puede ser el mismo");
                    }
                }else{
                    //la contraseña no es igual
                    return $_responses->error_200("El password es incorrecto ");
                }
            }else{
                //no existe el usuario
                return $_responses->error_200("El usuario $reset_user no existe ");
            }
        }
    }
}




?>