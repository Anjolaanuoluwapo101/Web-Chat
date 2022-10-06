<?php
class UserController extends BaseController
{
  /**
  * "/user/list" Endpoint - Get list of users
  */
  //["Anjola","Joe",$_POST['text_chat'],$_POST['reply_text_chat'],$_POST['reply_text_chatID']]
  public function input_1($params) {
    try {
      $instance = new UserModel;
      return $instance->saveChat($params);

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }

  }

  public function input_3($param) {
    try {
      $instance = new UserModel;
      return $instance->deleteMessage($param);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }



  public function input_4($params = []) {
    try {
      $instance = new UserModel;
      return $instance->checkBlockedUser($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  public function input_5($params = []) {
    try {
      $instance = new UserModel;
      return $instance->block_unblock_User($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }


  public function input_6($params = []) {
    try {
      $instance = new UserModel;
      return $instance->checkIfGroupMember($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }


  public function input_7($params = []) {
    try {
      $instance = new UserModel;
      return $instance->saveChatHistoryOnFirstVisit($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }


  public function input_8($params = []) {
    try {
      $instance = new UserModel;
      return $instance->populateChatHistory($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  public function input_9($params = []) {
    try {
      $instance = new UserModel;
      return $instance->resetNumberOfMessages($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }


  public function input_10($params = []) {
    try {
      $instance = new UserModel;
      return $instance->updateChatHistory($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  public function input_11($params = []) {
    try {
      $instance = new UserModel;
      return $instance->fetchLastMessages($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

//input 15 is the same as this but slighly diff
  public function input_12() {
    try {
      if (isset($_POST['username'], $_POST['password'], $_POST['email'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];

        //we check if the email is a valid one
        if (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) != true) {
          $emailErr = "Invalid Email";
        }
        //next we check the password
        else if (preg_match('/[\'^£$%&*()}{@#~><>,|=_+¬-]/', $password, $subject)) {
          $passwordErr = "Special characters not allowed";
        }

        //we check the image too
        else if (!isset($_FILES['file']['name']) || ($_FILES['file']['size'] > 5000000) ) {
          $filesErr = "Files size should be less than 5MB";
        }
        
        else if($username != ''){
          $instance = new UserModel;
          return $instance->registerNewAccount([$username,$password,$email,$_FILES['file']],'private');
        } else{
          $usernameErr ='Invalid Username';
        } 
      }
      

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  public function input_13() {
    try {
      if (isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        //next we check the password
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=!"%@_+¬-]/', $password, $subject)) {
          $passwordErr = "Special characters not allowed";
        }

        else if($username != '' ){
          $instance = new UserModel;
          return $instance->loginToAccount([$username,$password]);
        } else{
          $usernameErr = 'Invalid Username';
        } 
      }
      

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }




  public function input_14($param) {
    try {
      $instance = new UserModel;
      return $instance->verifyNewAccount($param);

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }
  
   public function input_15($gc_name,$files) {
    try {
          $instance = new UserModel;
          return $instance->registerNewAccount([$gc_name,'','',$files],'public');
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  
  public function output_1($params) {
    try {
      $instance = new UserModel;
      return $instance->retrieve($params);

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }



  public function output_2($params) {
    try {
      $instance = new UserModel;
      return $instance->locateParticularTaggedMessage($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }

  }

  public function output_3($params) {
    try {
      $instance = new UserModel;
      return $instance->loadTaggedMessages($params);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }


  public function output_4($param) {
    try {
      $instance = new UserModel;
      return $instance->getChatHistoryFromDB($param);
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }




}

?>