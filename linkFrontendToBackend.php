<?php
//ini_set("display_errors",1);

require __DIR__ . "/inc/bootstrap.php"; //we import all classes from Model and Controller
require __DIR__ . "/Controller/Api/UserController.php"; //we import the extended controller class

/* METHODS FOR CHANNEL.PHP STARTS HERE!*/


//this if block runs when you send a message only
if (isset($_POST['text_chat'])) {
  $fileName = $_FILES['file']['name'];
  $fileSize = $_FILES['file']['size'];
  $fileType = $_FILES['file']['type'];
  $fileTempStorage = $_FILES['file']['tmp_name'];
  $fileClearance = $_FILES['file']['error'];

  $objFeedController = new UserController();
  echo $objFeedController->input_1([$_POST['sender'], $_POST['receiver'], $_POST['text_chat'], $_POST['text_chatID'], $_POST['reply_text_chat'], $fileName, $fileSize, $fileTempStorage, $fileClearance, $fileType,$_POST['channel_type']]);
}


//this block runs to display private chats from db .....
else if (isset($_GET['sender'], $_GET['receiver'], $_GET['offset'])) {
  $objFeedController = new UserController();
  echo $objFeedController->output_1([$_GET['sender'], $_GET['receiver'], $_GET['offset'],$_GET['channel_type']]);

}


//this only runs if a message is to be deleted from the frontend
else if (isset($_POST['sender'], $_POST['receiver'], $_POST['idOfMessageToBeDeleted'])) {
  $objFeedController = new UserController();
  echo $objFeedController->input_3($_POST['idOfMessageToBeDeleted']);
}

//this is triggered by the setTimeout function in automaticallyLoadRecipient.js ..it checks if a user opening a conversation has been blocked or not
else if (isset($_POST['type'], $_POST['sender'], $_POST['receiver'])) {
  $objFeedController = new UserController();
  echo $objFeedController->input_4([$_POST['receiver'], $_POST['sender']]);

}
//this is used by an admin of a group or a user to block someone
else if (isset($_POST['blocker'],$_POST['blockee'])) {
  $objFeedController = new UserController();
  echo $objFeedController->input_5([$_POST['blocker'], $_POST['blockee'],$_POST['block_type'],$_POST['channel']]);
 
}
//when a user first opens a group..he/she is seen as an intruder...this helps to check if the user is a group member
else if(isset($_GET['intruder'],$_GET['group_name'])){
  $objFeedController = new UserController();
  echo $objFeedController->input_6([$_GET['intruder'],$_GET['group_name']]);
  
//if your message is directly tagged...this function populates the notif div and displays it (for better ux)
}else if(isset($_GET['sender'],$_GET['receiver'],$_GET['taggedMessages'])){
  $objFeedController = new UserController();
  echo $objFeedController->output_3([$_GET['sender'],$_GET['receiver'],$_GET['taggedMessages']]);
}
/*METHODS FOR CHANNEL.PHP ENDS HERE  */



?>