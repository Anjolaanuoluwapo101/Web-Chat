<?php
//this cookie is initially set during sign_up and sign_in
/*if(!isset($_COOKIE['WEBCHAT'])){
 header('Location:../Sign_up.php');
}else{
  $userName = json_decode($_COOKIE['WEBCHAT'])[0];
  $pattern = '/'.$userName.'/';
  $serverValues = $_SERVER['HTTP_COOKIE'];
  if(!preg_match($pattern,$serverValues)){
     header('Location:../Sign_up.php');
  }
}


if(!(isset($_GET['ref'])) || !($_GET['ref'] != '')){
  http_response_code(403);
  die();
}

if(!(isset($_GET['channel_type'])) || !($_GET['channel_type'] == 'public' || $_GET['channel_type'] == 'private')){
  http_response_code(403);
  die();
}*/
?>

<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/w3.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/meet.css">
    <link rel="stylesheet" href="css/w3-theme-khaki.css">
    <title>MEET-Web Chat</title>
    
  </head>
  <body>
      <div class="w3-border w3-circle w3-center w3-cursive ">Connect with</div>
      <br>
      <br>
      <fieldset  class="w3-padding-large w3-container w3-round-xxlarge">
      <form class="w3-container w3-form" id="meetDiv" enctype="application/x-www-form-urlencoded" accept-charset="utf-8">
       
       <input style="display:none" type="text" name="referer" value="<?php echo $_GET['ref']; ?>" id="referer" >
       <input style="display:none" type="text" name="channel_type" value="<?php echo $_GET['channel_type']; ?>" id="channel_type" >
           
            <span></span>
        <div class="w3-border w3-round-xxlarge w3-bar">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Type in your username
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input" style="" type="text"  name="referee" id="referee" placeholder=""  required>
          </div>
        </div>
        <br>
        <br>
       
        
        <button class="w3-button w3-right w3-indigo" type="submit">CONNECT</button>
      </form>
    </fieldset>
    
     <script src="channel_js/jquery-1.9.1.js"></script>
    <script src="channel_js/global.js"></script>
    <script src="channel_js/meet.js"></script>
    <script>
      //when someone is referred to the webchat with this link..we store two cookies
      setCookie('ref',qs['ref']);
      setCookie('channel_type',qs['channel_type']);
      
      
      
      
    </script>
  </body>
</html>