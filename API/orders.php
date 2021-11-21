<?php
require_once 'classes/responses.class.php';
require_once 'classes/orders.class.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, id, token, User-Agent, state");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
$_responses = new responses;
$_orders = new orders;

$listOrders = $_orders->listOrders();
header("Content-Type: application/json");
echo json_encode($listOrders);
http_response_code(200);
