<?php
require_once 'connection/connection.php';
require_once 'responses.class.php';


class auth extends connection{

    public function login($json){
      
        $_responses = new responses;
        $login_data = json_decode($json,true);
        if(!isset($login_data['user']) || !isset($login_data["password"])){
            //error con los campos
            return $_responses->error_400();
        }else{
            //todo esta bien 
            $login_user = $login_data['user'];
            $login_password = $login_data['password'];
            $login_pass = parent::encript($login_password);
            $login_db = $this->getUserData($login_user);
            if($login_db){
                $user_id = $login_db[0]["UsuarioId"];
                $user_pass = $login_db[0]["Password"];
                $user_rol = $login_db[0]['Rol'];
                $user_status = $login_db[0]['Estado'];
                //verificar si la contraseña es igual
                    if($login_pass == $user_pass){
                            if($user_status == "Activo"){
                                if($user_rol){
                                    //crear el token o actualiza si existe
                                    $token_db = $this->userHasToken($user_id);
                                    if($token_db){
                                        if (count($token_db) <= 1) {
                                            $token_status = $token_db[0]["Estado"];
                                            if ($token_status == "Inactivo"){
                                                $user_token = $this->updateToken($user_id);
                                            }elseif ($token_status == "Activo"){
                                                $user_token = $token_db[0]["Token"];
                                            }else{
                                                $this->deactivateToken($user_id);
                                                return $_responses->error_200("Base de datos actualizada. Volver a intentar");
                                            }
                                        }else{
                                            $this->removeDuplicateTokens($user_id);
                                            return $_responses->error_200("Base de datos actualizada. Volver a intentar");
                                        }
                                    }else{
                                        $user_token = $this->insertToken($user_id);
                                    }
                                    
                                    if($user_token){
                                            // si se guardo el token
                                            $result = $_responses->response;
                                            $result["result"] = array(
                                                "id" => $user_id,
                                                "token" => $user_token,
                                                "rol" => $user_rol
                                            );

                                            return $result; //retorna el token y el rol!
                                    }else{
                                            //error al guardar
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
                        //la contraseña no es igual
                        return $_responses->error_200("El password es invalido");
                    }
            }else{
                //no existe el usuario
                return $_responses->error_200("El usuario $login_user no existe ");
            }
        }
    }


    protected function getUserData($email){
        $query = "SELECT UsuarioId,Password,Estado,Rol FROM usuarios WHERE Usuario = '$email'";
        $data = parent::getData($query);
        if(isset($data[0]["UsuarioId"])){
            return $data;
        }else{
            return 0;
        }
    }


    protected function insertToken($user_id){
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $date = date("Y-m-d");
        $status = "Activo";
        $query = "INSERT INTO usuarios_token (UsuarioId,Token,Estado,Fecha)VALUES('$user_id','$token','$status','$date')";
        $verifica = parent::nonQuery($query);
        if($verifica){
            return $token;
        }else{
            return 0;
        }
    }

    protected function updateToken($user_id){
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $date = date("Y-m-d");
        $status = "Activo";
        $query = "UPDATE usuarios_token SET Token = '$token', Estado = '$status', Fecha = '$date' WHERE UsuarioId = '$user_id'";
        $verifica = parent::nonQuery($query);
        if($verifica){
            return $token;
        }else{
            return 0;
        }
    }
    protected function userHasToken($user_id){
        $query = "SELECT Estado,Token,TokenId FROM usuarios_token WHERE UsuarioID = '$user_id'";
        $resp = parent::getData($query);
        if(isset($resp)){
            return $resp;
        }else{
            return 0;
        }
    }
    public function userExists($user_id){
        $query = "SELECT UsuarioId FROM usuarios WHERE UsuarioID = '$user_id'";
        $resp = parent::getData($query);
        if(isset($resp)){
            return $user_id;
        }else{
            return 0;
        }
    }

    protected function removeDuplicateTokens($user_id){
        $query = "DELETE FROM usuarios_token WHERE UsuarioId = '$user_id'";
        $resp = parent::nonQuery($query);
        if($resp >= 1 ){
            return $resp;
        }else{
            return 0;
        }
    }
    
    protected function deactivateToken($user_id){
        $query = "UPDATE usuarios_token SET Estado = 'Inactivo' WHERE UsuarioId = '$user_id' ";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }
    }
    protected function updatePass($user_id, $new_pass){
        $query = "UPDATE usuarios SET Password = '$new_pass' WHERE UsuarioId = '$user_id'";
        $verifica = parent::nonQuery($query);
        if($verifica){
            return $verifica;
        }else{
            return 0;
        }
    }
}




?>