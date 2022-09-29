<?
require __DIR__ . "/inc/bootstrap.php"; //we import all classes from Model and Controller
require __DIR__ . "/Controller/Api/UserController.php"; //we import the extended controller class

//this object method 
if(isset($_GET['sender'],$_GET['receiver'],$_GET['link'] ,$_GET['channel_type'])){
    $objFeedController = new UserController();
    //header("Content-Type: application/json");..not needed
    echo $objFeedController->input_7([$_GET['sender'],$_GET['receiver'],$_GET['link'] ,$_GET['channel_type']]);
}


?>