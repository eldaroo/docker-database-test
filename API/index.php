<!DOCTYPE html>
<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API - Prubebas</title>
    <link rel="stylesheet" href="assets/style.css" type="text/css">
</head>
<body>

<div  class="container">
    <h1>Api de pruebas</h1>
    <div class="divbody">
        <h3>Auth - login</h3>
        <code>
           POST  /auth 
           <br>
           {
               <br>
               "user" :"",  -> REQUERIDO
               <br>
               "password": "" -> REQUERIDO
               <br>
            }
            <br>
            </code>
            <code>
            Ejemplo:
            <br>
            {
               <br>
               "user" :"usuario1@gmail.com",
               <br>
               "password": "123456"
               <br>
            }
            </code>
        <h3>Sign Off - logout</h3>
        <code>
           POST  /logout
           <br>
           {
               <br>
               "userId" :"",  -> REQUERIDO
               <br>
               "token": "" -> REQUERIDO
               <br>
            }
            <br>
            </code>
        <h3>Reset - resetPass</h3>
            <code>
               PUT  /reset (para cambiar password)
               <br>
               {
                  <br>
                  "user" :"",  -> REQUERIDO
                  <br>
                  "password": "" -> REQUERIDO
                  <br>
                  "newPassword": "" -> REQUERIDO
                  <br>
                  "newPassword2": "" -> REQUERIDO
                  <br>
               }
               <br>
            </code>
        <h3>Forgotten - getToken</h3>
            <code>
               GET  /forgotten?userId=1 (obtener token para volver a pedir password)
            </code>
        <h3>Forgotten - newPass</h3>
            <code>
               PUT  /forgotten (para cambiar password random)
               <br>
               {
                  <br>
                  "user" :"",  -> REQUERIDO
                  <br>
                  "token": "" -> REQUERIDO
                  <br>
               }
               <br>
            </code>
    </div>      
    <div class="divbody">   
        <h3>Listar Orden / Ordenes</h3>
        <code>
           GET  /orders?page=$numeroPagina (example: localhost/domain/orders?page=1, muestra primera pagina de ordenes)
           <br>
           GET  /orders?id=$idPaciente (example: localhost/domain/orders?id=1, muestra primer orden)
        </code>

        <code>
           POST  /orders
           <br> 
           {
            <br> 
               "deliveryDate" : "",               -> REQUERIDO
               <br> 
               "token" : "",                  -> REQUERIDO
               <br> 
               "rol":"",                 -> REQUERIDO (admin, delivery o client)
               <br>        
           }

        </code>
        <code>
           PUT  /orders
           <br> 
           {
            <br> 
               "deliveryDate" : "",
               <br>         
               "token" : "" ,                -> REQUERIDO        
               <br>       
               "id" : ""   -> REQUERIDO
               <br>
               "rol":"",                 -> REQUERIDO (admin, delivery o client)
               <br>        
           }

        </code>
        <code>
           DELETE  /orders
           <br> 
           {   
               <br>    
               "token" : "",                -> REQUERIDO        
               <br>       
               "id" : ""   -> REQUERIDO
               <br>
               "rol":"",                 -> REQUERIDO (admin, delivery o client)
               <br>        
           }

        </code>
    </div>


</div>
    
</body>
</html>

