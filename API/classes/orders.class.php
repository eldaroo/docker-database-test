<?php

use function PHPSTORM_META\elementType;

require_once "connection/connection.php";
require_once "responses.class.php";
require_once "auth.class.php";
require_once 'recorder.class.php';

class orders extends connection {

    private $table = "orders";
    private $completedTable = "orders_completed";
    private $ordersId = "";
    private $usersId = "";
    private $delivery = "pendiente";
    private $deliveryDate = "";
    private $priority = 0;
    private $destination = "";
    private $recibes = "";
    private $phoneRecibes = "";
    private $bundle = "";
    private $missing = "";
    private $observations = "";
    private $deliveryObservations = "";
    private $qry_error = "";
    private $bundles = 0;
    private $rain = false;
    private $ooo = false;
    private $delay = 0;
    private $removed = 0;

    // listOrders is used in the client view avoiding listing the admin's orders.
    // Also list only not removed orders.
    public function listOrders($page = 1){// FIX ME
        $start  = 0 ;
        $cant = 100;
        if($page > 1){
            $start = ($cant * ($page - 1)) +1 ;
            $cant = $cant * $page;
        }
        $query = "SELECT * FROM orders_completed";
        $data = parent::getData($query);
        return ($data);
    }

    // listAllOrders is used in the admin view listing all client and admin orders.
    // Also list only not removed and not completed orders.
    public function listAllOrders($page = 1){// FIX ME
        $start  = 0 ;
        $cant = 100;
        if($page > 1){
            $start = ($cant * ($page - 1)) +1 ;
            $cant = $cant * $page;
        }
        $query = "SELECT * FROM orders INNER JOIN usuarios ON orders.usersId=usuarios.UsuarioId WHERE NOT orders.removed AND NOT (orders.state = 'entregado') limit $start,$cant";
        $data = parent::getData($query);
        return ($data);
    }

    // getCompletedOrders is used in the admin listing completed orders with delivery details.
    public function getCompletedOrders(){
        $query = "SELECT * FROM orders INNER JOIN orders_completed ON orders.id=orders_completed.ordersId";
        $data = parent::getData($query);
        return $data;
    }

    // listTrash is used in the  client views listing removed orders.
    // Also list only not admin created orders.
    public function listTrash(){
        $query = "SELECT * FROM orders INNER JOIN usuarios ON orders.usersId=usuarios.UsuarioId WHERE orders.removed AND NOT usuarios.Rol = 'admin'";
        $data = parent::getData($query);
        return $data;
    }

    // listAllTrash is used in the admin views listing all removed orders.
    public function listAllTrash(){
        $query = "SELECT * FROM orders INNER JOIN usuarios ON orders.usersId=usuarios.UsuarioId WHERE orders.removed";
        $data = parent::getData($query);
        return $data;
    }

    // listTrashByUser is used in the admin and client views listing only by logged ion user removed orders.
    public function listTrashByUser($id){
        $query = "SELECT * FROM orders INNER JOIN usuarios ON orders.usersId=usuarios.UsuarioId WHERE orders.removed AND usuarios.UsuarioId='$id'";
        $data = parent::getData($query);
        return $data;
    }

    // getOrders is deprecated.
    public function getOrders($id){
        $query = "SELECT orders.id FROM orders INNER JOIN usuarios ON orders.usersId=usuarios.UsuarioId WHERE orders.id = '$id'";
        return parent::getData($query);
    }

    // getImg is in development.
    public function getImg($id){
        $url = $id.".jpg";
        return $url;
    }

    // getOrdersByUser is used in client view for listing orders created only by logged in user.
    // Also list only not removed orders.
    // NEEDS REFACTOR TO VALIDATE BY USER ID
    public function getOrdersByUser($user_id){
        $user_name = $this->getUserName($user_id);
        $user_name = $user_name[0];
        if($user_name["Nombre"] && $user_name["Apellido"]){
            $user_firstN=$user_name["Nombre"];
            $user_lastN=$user_name["Apellido"];
        }else{
            $user_firstN="fake";
            $user_lastN="fake";
        }
        $user_name = $user_firstN.' '.$user_lastN;
        $query = "SELECT * FROM orders INNER JOIN usuarios ON orders.usersId=usuarios.UsuarioId WHERE (usuarios.Nombre = '$user_firstN') AND (usuarios.Apellido = '$user_lastN') AND NOT orders.removed";
        $resp = parent::getData($query);
        return $resp;
    }
    // getOrdersByDelivery is used in delivery view for listing orders assigned only to logged in user.
    // Also list only ready and hurry orders with priority setted.
    // NEEDS REFACTOR TO VALIDATE BY USER ID
    public function getOrdersByDelivery($user_id){
        $user_name = $this->getUserName($user_id);
        $user_name = $user_name[0];
        if($user_name["Nombre"] && $user_name["Apellido"]){
            $user_firstN=$user_name["Nombre"];
            $user_lastN=$user_name["Apellido"];
        }else{
            $user_firstN="fake";
            $user_lastN="fake";
        }
        $user_name = $user_firstN.' '.$user_lastN;
        $query = "SELECT * FROM orders INNER JOIN usuarios ON orders.usersId=usuarios.UsuarioId WHERE (orders.delivery = '$user_name') AND NOT (orders.state = 'entregado') AND ((orders.state = 'listo') OR (orders.state = 'tiene prisa')) AND (orders.priority >= 1)";
        $resp = parent::getData($query);
        if ($resp){
            return $resp;
        }else{
            return 0;
        }
    }

    // getCompletedOrdersByUser is used in delivery view for listing completed orders assigned only to logged in user.
    // NEEDS REFACTOR TO VALIDATE BY USER ID
    public function getCompletedOrdersByDelivery($user_id){
        $user_name = $this->getUserName($user_id);
        $user_name = $user_name[0];
        if($user_name["Nombre"] && $user_name["Apellido"]){
            $user_firstN=$user_name["Nombre"];
            $user_lastN=$user_name["Apellido"];
        }else{
            $user_firstN="fake";
            $user_lastN="fake";
        }
        $user_name = $user_firstN.' '.$user_lastN;
        $query = "SELECT * FROM orders INNER JOIN usuarios ON orders.usersId=usuarios.UsuarioId WHERE (orders.delivery = '$user_name') AND (orders.state = 'entregado')";
        $resp = parent::getData($query);
        if ($resp){
            return $resp;
        }else{
            return 0;
        }
    }
    
    // getUserName is used to get logged in user first and last names
    public function getUserName($id){
        $query = "SELECT Nombre,Apellido FROM usuarios WHERE UsuarioId = '$id'";
        $resp = parent::getData($query);
        return $resp;
    }

    // post is used to create new orders and new completed orders
    // ooo field needs to be renamed in order to get accuarate reference
    public function post($json){
        $_responses = new responses;
        $_auth = new auth;
        $data = json_decode($json,true);
        if(!isset($data['token']) || !isset($data['rol']) || !isset($data['usersId'])){
            return $_responses->error_401("no token, no rol, no user id");
        }else{
            $this->token = $data['token'];
            $arrayToken =   $this->checkToken();
            if($arrayToken){
                if(isset($data['complete'])){
                    $this->ordersId = $data['id'];
                    $this->bundles= isset($data['bundles']) ? $data['bundles'] : 0;
                    $this->rain= isset($data['rain']) ? $data['rain'] : false;
                    $this->ooo= isset($data['ooo']) ? $data['ooo'] : false;
                    $this->delay= isset($data['delay']) ? $data['delay'] : 0;
                    $resp = $this->completeOrder();
                    if($resp){
                        $response = $_responses->response;
                        $response["result"] = array(
                            "id" => $resp
                        );
                        return $response;
                    }else{
                        return $_responses->error_500($this->qry_error);
                    }
                }
                if(!isset($data['deliveryDate'])){
                    return $_responses->error_400();
                }else{
                    if(strlen($data['deliveryDate']) <= 10){
                        $this->deliveryDate = $data['deliveryDate'];
                    }else{
                        return $_responses->error_200("formato de fecha incorrecto");
                    }
                        $this->usersId = $data['usersId'];
                        $this->destination= isset($data['destination']) ? $data['destination'] : "pendiente";
                        $this->recibes= isset($data['recibes']) ? $data['recibes'] : "pendiente";
                        $this->phoneRecibes= isset($data['phoneRecibes']) ? $data['phoneRecibes'] : "";
                        $this->bundle= isset($data['bundle']) ? $data['bundle'] : "pendiente";
                        $this->missing= isset($data['missing']) ? $data['missing'] : "";
                        $this->observations= isset($data['observations']) ? $data['observations'] : "";
                        $this->delivery= isset($data['delivery']) ? $data['delivery'] : "pendiente";
                        $this->priority= isset($data['priority']) ? $data['priority'] : 0;
                        $this->removed= isset($data['removed']) ? $data['removed'] : 0;
                        $this->state= isset($data['state']) ? $data['state'] : 'a preparar';
                    $resp = $this->insertOrder();
                    if($resp){
                        $response = $_responses->response;
                        $response["result"] = array(
                            "id" => $resp
                        );
                        return $response;
                    }else{
                        return $_responses->error_500($this->qry_error);
                    }
                }
            }else{
                return $_responses->error_401("El Token que envio es invalido o ha caducado");
            }
        }
    }

    // insertOrder is used to create new order
    private function insertOrder(){
        $query = "INSERT INTO " . $this->table . " (usersId, deliveryDate, priority, destination, recibes, phoneRecibes, observations, delivery, bundle, missing, state, removed) VALUES ('$this->usersId', '$this->deliveryDate', '$this->priority', '$this->destination', '$this->recibes', '$this->phoneRecibes', '$this->observations', '$this->delivery', '$this->bundle', '$this->missing', '$this->state', '$this->removed')";
        $resp = parent::nonQueryId($query);
        if($resp){   
            $action = array(
                "title"=> "nueva orden agregada",
                "user"=> $this->usersId,
                "deliveryDate"=> $this->deliveryDate,
                "change"=> ""
            ); 
            recorder::recordData($action);
             return $resp;
        }else{
            $this->qry_error = 'insertOrder() return affected rows=' . $resp . ' Query=' . $query;
            return 0;
        }
    }

    // insertOrder is used to create new completed order
    function completeOrder($json){
        $data = json_decode($json,true);
        $this->rain= isset($data['rain']) ? $data['rain'] : false;
        $query = "INSERT INTO " . $this->completedTable . " (rain) VALUES ('$this->rain')";
        $resp = parent::nonQueryId($query);
        if($resp){   
             return $resp;
        }else{
            $this->qry_error = 'completeOrder() return affected rows=' . $resp . ' Query=' . $query;
            return 0;
        }
    }

    // put is used to edit orders
    public function put($json){
        $_responses = new responses;
        $data = json_decode($json,true);

        if(!isset($data['token'])){
            return $_responses->error_401();
        }else{
            $this->token = $data['token'];
            $arrayToken =   $this->checkToken();
            if($arrayToken){
                if(!isset($data['id'])){
                    return $_responses->error_400();
                }else{
                    $this->orderId = $data['id'];
                    if(isset($data['deliveryDate'])) { $this->deliveryDate = $data['deliveryDate']; }
                    if(isset($data['priority'])) { $this->priority = $data['priority']; $resp = $this->editPriority(); }
                    if(isset($data['delivery'])) { $this->delivery = $data['delivery']; $resp = $this->editDelivery(); }
                    if(isset($data['origin'])) { $this->origin = $data['origin']; $resp = $this->editOrigin();}
                    if(isset($data['state'])) { $this->state = $data['state']; $resp = $this->editState();}
                    if(isset($data['logistic'])) { $this->logistic = $data['logistic']; $resp = $this->editLogistic();}
                    if(isset($data['destination'])) { $this->destination = $data['destination']; $resp = $this->editDestination();}
                    if(isset($data['recibes'])) { $this->recibes = $data['recibes']; $resp = $this->editRecibes();}
                    if(isset($data['phoneRecibes'])) { $this->phoneRecibes = $data['phoneRecibes']; $resp = $this->editPhoneRecibes();}
                    if(isset($data['bundle'])) { $this->bundle = $data['bundle']; $resp = $this->editBundle();}
                    if(isset($data['missing'])) { $this->missing = $data['missing']; $resp = $this->editMissing();}
                    if(isset($data['observations'])) { $this->observations = $data['observations']; $resp = $this->editObservations();}
                    if(isset($data['deliveryObservations'])) { $this->deliveryObservations = $data['deliveryObservations']; $resp = $this->editDeliveryObservations(); }
                    if(isset($data['removed'])) { $this->removed = $data['removed']; $resp = $this->editRemoved(); }
                    //$resp = $this->editOrder();
                    return $resp; //FIX ME//
                    if($resp){
                        return $resp;
                        $response = $_responses->response;
                        $response["result"] = array(
                            "id" => $this->orderId
                        );
                        return $response;
                    }else{
                        return $_responses->error_500();
                    }
                }

            }else{
                return $_responses->error_401("El Token que envio es invalido o ha caducado");
            }
        }
    }

    private function editOrder(){
        $query = "UPDATE " . $this->table . " SET deliveryDate='$this->deliveryDate', priority='$this->priority', destination='$this->destination', recibes='$this->recibes', phoneRecibes='$this->phoneRecibes', bundle='$this->bundle', observations='$this->observations', missing='$this->missing' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            $action = array(
                "title"=> "Orden editada",
                "user"=> $this->usersId,
                "deliveryDate"=> $this->deliveryDate,
                "change"=> "la orden fue actualizada"
            ); 
            recorder::recordData($action);
             return $resp;
        }else{
            return 0;
        }
    }

    private function editPriority(){
        $query = "UPDATE " . $this->table . " SET priority='$this->priority' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            $action = array(
                "title"=> "prioridad modificado",
                "user"=> "",
                "deliveryDate" => "",
                "change"=> "La nueva prioridad de la orden es ".$this->priority
            ); 
            recorder::recordData($action);
             return $resp;
        }else{
            return 0;
        }
    }

    private function editRemoved(){
        $query = "UPDATE " . $this->table . " SET removed='$this->removed' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            $action = array(
                "title"=> "orden removida",
                "user"=> "",
                "deliveryDate" => "",
                "change"=> "La orden removida es ".$this->orderId
            ); 
            recorder::recordData($action);
             return $resp;
        }else{
            return 0;
        }
    }

    private function editOrigin(){
        $query = "UPDATE " . $this->table . " SET origin='$this->origin' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            $action = array(
                "title"=> "origen modificado",
                "user"=> "",
                "deliveryDate" => "",
                "change"=> "El nuevo origen de la orden es ".$this->origin
            ); 
            recorder::recordData($action);
             return $resp;
        }else{
            return 0;
        }
    }

    private function editState(){
        $query = "UPDATE " . $this->table . " SET state='$this->state' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editDelivery(){
        $query = "UPDATE " . $this->table . " SET delivery='$this->delivery' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editDeliveryObservations(){
        $query = "UPDATE " . $this->table . " SET deliveryobservations='$this->deliveryObservations' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editLogistic(){
        $query = "UPDATE " . $this->table . " SET logistic='$this->logistic' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editDestination(){
        $query = "UPDATE " . $this->table . " SET destination='$this->destination' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editRecibes(){
        $query = "UPDATE " . $this->table . " SET recibes='$this->recibes' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editPhoneRecibes(){
        $query = "UPDATE " . $this->table . " SET phoneRecibes='$this->phoneRecibes' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editBundle(){
        $query = "UPDATE " . $this->table . " SET bundle='$this->bundle' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editMissing(){
        $query = "UPDATE " . $this->table . " SET missing='$this->missing' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function editObservations(){
        $query = "UPDATE " . $this->table . " SET observations='$this->observations' WHERE id='$this->orderId' ";		
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    public function delete($json){
        $_responses = new responses;
        $data = json_decode($json,true);

        if(!isset($data['token'])){
            return $_responses->error_401();
        }else{
            
            $this->token = $data['token'];
            $arrayToken =   $this->checkToken();
            if($arrayToken){
                if(!isset($data['id'])){
                    return $_responses->error_400();
                }else{
                    $this->orderId = $data['id'];
                    $resp = $this->removeOrder();
                    if($resp){
                        $response = $_responses->response;
                        $response["result"] = array(
                            "id" => $this->orderId
                        );
                        return $response;
                    }else{
                        return $_responses->error_500();
                    }
                }
            }else{
                return $_responses->error_401("El Token que envio es invalido o ha caducado");
            }
        }
    }

    private function removeOrder(){
        $query = "DELETE FROM " . $this->table . " WHERE id= '" . $this->orderId . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1 ){
            return $resp;
        }else{
            return 0;
        }
    }

}
?>