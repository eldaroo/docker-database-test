<?php
    require_once '../classes/token.class.php';
    $_token = new token;
    $date = date('Y-m-d');
    echo $_token->updateTokens($date);
?>