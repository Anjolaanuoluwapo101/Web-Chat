<?php
//This file is not included in the bootstrap file 
require __DIR__ . "/inc/bootstrap.php"; //we import all classes from Model and Controller
require __DIR__ . "/Controller/Api/UserController.php"; //we import the extended controller class

$username = '';
$password ='';

$usernameErr = '';
$passwordErr ='';
if (isset($_POST['username'], $_POST['password'])) {;
   $username = htmlspecialchars(trim($_POST['username']));
   $password = htmlspecialchars(trim($_POST['password']));

  //next we check the password
  if (preg_match('/[\'^£$%&*()}{@#~?><>,|=!"%@_+¬-]/', $password, $subject)) {
    $passwordErr = "Special characters not allowed";
    echo "<script> history.go(-1)</script>";
    
  } else if ($username != '') {
      $objFeedController = new UserController();
      $sign_in = $objFeedController->input_13($username,$password);
    
      //first two if blocks means that the acct exists but only a verofied acct will be allowed to proceed
      if($sign_in == "unverified"){
        echo "<script>alert('Your account is not verified. Please check you mail(Inbox and Spam folder');history.go(-1)</script>";
      }else if($sign_in == "verified"){
       header("Location:View/chathistory.php?sender=$username");
      }else{
        echo $sign_in;//this throws an alert that indicates such acct does not exist
      }

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

    <title>LOGIN-Web Chat</title>
  
  </head>
  <body class="w3-theme-l3">
    <fieldset class="w3-padding-large w3-container w3-round-xxlarge w3-theme-l1">
    <div class="w3-center">LOGIN</div>
      <br>
      <br>
      <form class="w3-container w3-form w3-center" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST"  accept-charset="utf-8">
       
            <span><?php echo $usernameErr ?></span>
        <div class="w3-border w3-round-xxlarge w3-bar w3-animate-right">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Username
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input w3-round-large" style="" type="text" min="8" name="username" id="username" placeholder="8+ characters" value="<?php echo $username ?>" required>
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
            <input class="w3-input w3-round-large" style="" min="5" type="password" name="password" id="password" placeholder="No special chars" value="<?php echo $password ?>" required >
          </div>
        </div>
        <br>
        <br>
        
        <button class="w3-button w3-right w3-indigo" type="submit">LOGIN</button>
        <br>
        <br>
        <br>
        <a class="w3-button w3-indigo w3-round-large w3-tiny" href="Sign_up.php">DON'T HAVE AN ACCOUNT?</a>
      </form>
    </fieldset>
   <script src="View/channel_js/global.js"></script>
    <script type="text/javascript" charset="utf-8">
      if(getCookie('timeout')){
        alert('Welcome new user');
      }
      if(qs['q']){
        a('username').value = qs['q'];
      }
      
      if(qs['new']){
        alert('Sign in required');
      }
    </script>
  </body>
</html>