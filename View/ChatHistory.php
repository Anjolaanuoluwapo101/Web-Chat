<!DOCTYPE html>
<?php

?>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/w3.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/index.css">
  
    <title>Chat</title>
  </head>
  <body>
  <div class="w3-bar-block w3-padding-small w3-indigo" style="width:100%">
    <div class="w3-bar-item"><span class="w3-xlarge" style="font-weight:900">WEB CHAT</span></div>
      <div class="w3-bar-item" style="font-weight:700;">
          CHATS
    </div>
  </div>


  <!--Loads chat history here here-->
  <div id="chatHistory"></div>

  <!--Load JS scripts-->
  <script src="channel_js/jquery-1.9.1.js"></script>
  <script src="channel_js/global.js"></script>
  <script src="channel_js/grabQueryString.js"></script>
  <script src="chathistory_js/loadChatHistoryFromDB.js"></script>
  <script src="chathistory_js/loadChatHistoryFromLocalStorage.js"></script>
  
  <script src=""></script>
 
  </body>
</html>