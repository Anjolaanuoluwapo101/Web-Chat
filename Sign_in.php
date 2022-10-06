<?php
//This file is not included in the bootstrap file 

require __DIR__ . "/inc/bootstrap.php"; //we import all classes from Model and Controller
require __DIR__ . "/Controller/Api/UserController.php"; //we import the extended controller class

$username = '';
$password ='';

$usernameErr = '';
$passwordErr ='';
if(isset($_POST['username'])){
  $objFeedController = new UserController();
  $sign_in = $objFeedController->input_13();
  
  if($sign_in == "unverified"){
    echo "<script>alert('Your account is not verified. Please check you mail(Inbox and Spam folder');history.back()</script>";
  }else{
    echo $sign_in;
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
  
  </head>
  <body class="w3-theme-l3">
    <fieldset class="w3-padding-large w3-container w3-round-xxlarge w3-theme-l1">
    <div class="w3-center">LOGIN</div>
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
        
            <span><?php echo $passwordErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Password
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input w3-round-large" style="" min="5" type="password" name="password" id="password" value="<?php echo $password ?>" required >
          </div>
        </div>
        <br>
        <br>
        
        <button class="w3-button w3-right w3-indigo" type="submit">LOGIN</button>
      </form>
    </fieldset>
   <script src="View/channel_js/global.js"></script>
    <script type="text/javascript" charset="utf-8">
      if(getCookie('timeout')){
        alert('Welcome new user');
      }
      
      /*
      if(getCookie('tahcbewJA')){  
        a('username').value = JSON.parse(getCookie('tahcbewJA'))[0]; 
        a('password').value = JSON.parse(getCookie('tahcbewJA'))[1]; 
      }
      */
    </script>
  </body>
</html>