<?php
//This file is not included in the bootstrap file 

require __DIR__ . "/inc/bootstrap.php"; //we import all classes from Model and Controller
require __DIR__ . "/Controller/Api/UserController.php"; //we import the extended controller class

$username = '';
$password ='';
$email = '';

$usernameErr = '';
$passwordErr ='';
$emailErr = '';
$objFeedController = new UserController();
echo $objFeedController->input_12();
 /* if($signup == "false"){
    echo "<script> alert('Username Taken'); history.back(); </script>";
  }else{
    $signup = json_decode($signup);
    header("Location:Sign_in.php");
  }*/

?>

<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="View/css/w3-theme-indigo.css" title="" type="" />
    <link rel="stylesheet" href="View/css/w3.css" title="" type="" />
    <link rel="stylesheet" href="View/css/SignIn_SignUp.css" title="" type="" />
    <title>JOIN-Web Chat</title>
    <style type="text/css" media="all">
  
    </style>
  </head>
  <body class="w3-theme-l3">
    <fieldset class="w3-padding-large w3-container w3-round-xxlarge w3-theme-l1">
      
      <div class="w3-center">REGISTER</div>
      <br>
      <br>
      <form class="w3-container w3-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data" accept-charset="utf-8">
       
            <span><?php echo $usernameErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Username
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input w3-round-large" style="" type="text" min="8" name="username" id="username" placeholder="Type in your username" value="<?php echo $username ?>" required>
          </div>
        </div>
        <br>
        <br>
        
            <span><?php echo $emailErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Email
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input w3-round-large" style="" type="text" name="email" id="email" value="<?php echo $email ?>" required>
          </div>
        </div>
        <br>
        <br>
        
            <span><?php echo $passwordErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Password
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input w3-round-large" style="" min="5" type="text" name="password" id="password" value="<?php echo $password ?>" required >
          </div>
        </div>
        <br>
        <br>
        
            <span></span>
        <div class="w3-border w3-round-xxlarge w3-bar">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Display Picture
          </div>
          <div class="w3-bar-item" style="width:60%">
            <span id='display' onclick="a('file').click()"> Choose Display Picture</span>
            <input class="w3-input" style="display:none"  type="file" name="file" id="file" required >
          </div>
        </div>
        <br>
        <br>
        <button class="w3-button w3-right w3-indigo" type="submit">REGISTER</button>
      </form>
    </fieldset>
    <script type="text/javascript" charset="utf-8">
      function a(id){
        setInterval(function(){
          b('file');
        },2000)
        return document.getElementById(id);
      }
      
      function b(id) {
        if(document.getElementById(id).files[0] != undefined){
          a('display').innerHTML = 'File Chosen!';
        }
      }
    </script>
  </body>
</html>