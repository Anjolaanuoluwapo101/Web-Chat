<?php
//This file is not included in the bootstrap file 
require __DIR__ . "/inc/bootstrap.php"; //we import all classes from Model and Controller
require __DIR__ . "/Controller/Api/UserController.php"; //we import the extended controller class

if(isset($_GET['q'])){
 //first we decrypt this value:
  $encryptedString = $_GET['q'];
  $ciphering = "AES-128-CTR";
  $iv_length = openssl_cipher_iv_length($ciphering);
  $options = 0;
  $encryption_iv = '3002200330022003';
  $encryption_key = "webchatbyAJ";
  $decrypted = openssl_decrypt($encryptedString, $ciphering,$encryption_key, $options, $encryption_iv);
  
  $objFeedController = new UserController();
  $verify = $objFeedController->input_14($decrypted);
  if($verify == "verified"){
    echo "<script>alert('You have been verified!!!')</script>";
    header('Location:Sign_in.php');
  }else{
    echo "<script>alert('An error occurred')</script>";
  }
}else{
  http_response_code(403);//we throw an http error if no encrypted value is passed
}



?>

<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>VERIFY-Account</title>
    
  </head>
  <body>
    
  </body>
</html>