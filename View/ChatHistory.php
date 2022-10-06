<!DOCTYPE html>
<?php
//this cookie is initially set during sign_up and sign_in
if(!isset($_COOKIE['WEBCHAT'])){
 header('Location:../Sign_in.php');
}else{
  $userName = json_decode($_COOKIE['WEBCHAT'])[0];
  $pattern = '/'.$userName.'/';
  $serverValues = $_SERVER['HTTP_COOKIE'];
  if(!preg_match($pattern,$serverValues) || !preg_match($pattern,$_SERVER['QUERY_STRING'])){
     header('Location:../Sign_in.php');
  }
}

?>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/w3.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/chathistory.css">
  
    <title>Chat</title>
  </head>
  <body>
  <div class="w3-bar-block w3-padding-small w3-indigo" style="width:100%">
    <div class="w3-bar-item"><span class="w3-xlarge" style="font-weight:900">WEB CHAT</span></div>
      <div class="w3-bar-item" style="font-weight:700;">
          CHATS <span id="unopenedchats" class="w3-tag w3-circle w3-blue-grey w3-small"></span>
    </div>
  </div>


  <!--Loads chat history here here-->
  <div id="chatHistory"></div>
  <div id="showGroup" onclick="showGroupCreator()" class="w3-circle w3-indigo w3-center w3-xxlarge w3-padding-large"><i class="fa fa-plus"></i></div>
  
   <fieldset id="createGroup" class="w3-padding-large w3-container w3-round-xxlarge">
      <legend><div class="w3-border w3-circle"></div></legend>
      <form class="w3-container w3-form" id="createGCForm" enctype="multipart/form-data" accept-charset="utf-8">
       
       <input style="display:none" name="sender" id="senderAdmin" >
           
            <span></span>
        <div class="w3-border w3-round-xxlarge w3-bar">
          <div class="w3-bar-item w3-border-right w3-small" style="width:30%;">
            Group Name
          </div>
          <div class="w3-bar-item" style="width:60%">
            <input class="w3-input" style="" type="text"  name="group_name" id="group_name" placeholder="Type in a suitable group name" value="<?php echo $username ?>" required>
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
        <button class="w3-button w3-right w3-indigo" type="submit">CREATE GROUP</button>
      </form>
    </fieldset>
  <!--Load JS scripts-->
  <script src="channel_js/jquery-1.9.1.js"></script>
  <script src="channel_js/global.js"></script>
  <script src="channel_js/grabQueryString.js"></script>
  <script src="chathistory_js/loadChatHistoryFromDB.js"></script>
  <script src="chathistory_js/loadChatHistoryFromLocalStorage.js"></script>
  <script src="chathistory_js/createGC.js"></script>
 <script>
    function showGroupCreator(){
      if(a('createGroup').style.display != 'none'){
        a('createGroup').style.display = 'none';
      }else{
        a('createGroup').style.display = 'block';
      }
    }
   
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