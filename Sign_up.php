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
$filesErr = '';

if (isset($_POST['username'], $_POST['password'], $_POST['email'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $username = str_ireplace(array('/','\\'),'',$username);
   // $username = $result;
    $password = htmlspecialchars(trim($_POST['password']));
    $email = trim($_POST['email']);

    //we check if the email is a valid one
    if (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) != true) {
      //$emailErr = "Invalid Email";
      //echo "<script> history.back()</script>";
      echo "<script> alert('Invalid Email');history.back() </script>";
    }
    
    
    //next we check the password
    else if (preg_match('/[\'^£$%&/\*()};{@#~>.<>,|=_+¬-]/', $password, $subject)) {
     // $passwordErr = "Special characters not allowed";
      //echo "<script> history.back()</script>";
      echo "<script> alert('Special characters not allowed');history.back() </script>";
    }
    
    
    //next we check the password
    else if (preg_match('/[\'^£$%&*()}{@;#~><.>,|=_+¬-]/', $username, $subject)) {
     // $usernameErr = "Special characters not allowed";
     // echo "<script> history.back()</script>";
      echo "<script> alert('Special characters not allowed');history.back() </script>";
    }

    //we check the image too
    else if (!isset($_FILES['file']['name']) || ($_FILES['file']['size'] > 5000000) || ($_FILES['file']['type'] != "image/jpg" && $_FILES['file']['type'] != "image/png" && $_FILES['file']['type'] != "image/jpeg") && $_FILES['file']['type'] != "image/webp" ) {
      //$filesErr = "Invalid File Type/File size must be < 5MB";
      echo "<script> alert('Invalid File Type,File size must be < 5MB,Check Image Chosen');history.back() </script>";
    }
    
    else if(strlen($username) >= 8){
      $objFeedController = new UserController();
      $sign_up = $objFeedController->input_12([$username, $password, $email, $_FILES['file']], 'private');
      if($sign_up == 'false'){
        echo "<script> alert('Username Taken');history.go(-1)</script>";
        
      }else if($sign_up == 'true'){
        echo "<script> alert('Account created successfully');window.location.href='Sign_in.php?q=$username'; </script>";
        
      }
    }else{
    //  $usernameErr = "Username too short";
      echo "<script> alert('Username must contain 8 characters');history.back() </script>";
    }
  }
 
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
      <form class="w3-container w3-form w3-center" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data" accept-charset="utf-8">
       
            <span><?php echo $usernameErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar w3-animate-left">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Username
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input w3-round-large" style="" type="text" min="8" name="username" id="username" placeholder="8+ characters" value="<?php echo $username ?>" required>
          </div>
        </div>
        <br>
        <br>
        
            <span><?php echo $emailErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar w3-animate-right">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Email
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input w3-round-large" style="" type="text" name="email" id="email" placeholder="Active Email Account" value="<?php echo $email ?>" required>
          </div>
        </div>
        <br>
        <br>
        
            <span><?php echo $passwordErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar w3-animate-left">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Password
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input w3-round-large" style="" min="5" placeholder="No special chars allowed" type="password" name="password" id="password" value="<?php echo $password ?>" required >
          </div>
        </div>
        <br>
        <br>
        
            <span><?php echo $filesErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar w3-animate-right">
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
        <br>
        <br>
        <br>
        <a class="w3-center w3-button w3-indigo w3-round-large w3-tiny" href="Sign_in.php">ALREADY HAVE AN ACCOUNT?</a>
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