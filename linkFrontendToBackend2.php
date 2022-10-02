<?
require __DIR__ . "/inc/bootstrap.php"; //we import all classes from Model and Controller
require __DIR__ . "/Controller/Api/UserController.php"; //we import the extended controller class

//this object method 
if(isset($_GET['sender'],$_GET['receiver'],$_GET['link'] ,$_GET['channel_type'])){
    $objFeedController = new UserController();
    echo $objFeedController->input_7([$_GET['sender'],$_GET['receiver'],$_GET['link'] ,$_GET['channel_type']]);
}

else if(isset($_GET['sender'],$_GET['array'])){
  $objFeedController = new UserController();
  echo $objFeedController->input_8([$_GET['sender'],$_GET['array']]);
}

//$_GET['x']..is used to distinguish between other if blocks
else if(isset($_GET['sender'],$_GET['receiver'],$_GET['channel_type'],$_GET['x'])){
  $objFeedController = new UserController();
  echo $objFeedController->input_9([$_GET['sender'],$_GET['receiver'],$_GET['channel_type']]);
}

else if(isset($_POST['sender'],$_POST['updatedChatHistory'])){
  $objFeedController = new UserController();
  echo $objFeedController->input_10([$_POST['sender'],$_POST['updatedChatHistory']]);
}

else if(isset($_GET['sender'],$_GET['getChatHistory'])){
  $objFeedController = new UserController();
  echo $objFeedController->output_4($_GET['sender']);
}

?>