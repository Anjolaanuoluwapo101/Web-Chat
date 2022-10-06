<?php
require_once PROJECT_ROOT_PATH . "/Model/Database.php";

class UserModel extends Database
{
  public function saveChat($params) {
    return $this->executeStatement_1("INSERT INTO Message(`Sender`,`Receiver`,`Chat`,`ChatID`,`RefChat`,`fileName`,`fileType`) VALUES(?,?,?,?,?,?,?)", "SELECT * FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ?", $params);
  }

  public function retrieve($params = []) {
    if ($params[3] == 'private') {
      $query = "SELECT * FROM `Message` WHERE `Sender` IN(?,?) AND `Receiver` IN(?,?) ORDER BY `Time` DESC LIMIT ?,5 ";
    } else if ($params[3] == 'public') {
      $query = "SELECT * FROM `Message` WHERE  `Receiver` = ? ORDER BY `Time` DESC LIMIT ?,5 ";
    }
    return $this->executeStatement_2($query, $params);
  }

  public function saveAudioMessage($params = []) {
    return $this->executeStatement_3("INSERT INTO Message(`Sender`,`Receiver`,`ChatID`,`fileName`,`fileType`,`RefChat`) VALUES(?,?,?,?,?,?)", $params);
  }

  public function deleteMessage($param) {
    //since chatID is unique we only need that paramater to fetch that particular row from the db
    //first query obtains the row and check if the message contains a file..so that file can be deleted
    return $this->executeStatement_4("SELECT * FROM `Message` WHERE `ChatID` = ? LIMIT 1", "DELETE FROM `Message` WHERE `ChatID` = ?", $param);
  }

  public function retrieveChatHistory($param) {
    //since User table is unique we only need that paramater to fetch that particular row from the db
    //first query obtains the row and check if the message contains a file..so that file can be deleted

    return $this->executeStatement_5("SELECT `ChatHistory` FROM `UserAccount` WHERE `User` = ?", $param);
  }

  //this function automatically runs when a user opens a chat to cht someone or gc...it check is such person has been initially blocked
  public function checkBlockedUser($params = []) {
    return $this->executeStatement_6("SELECT `Blocked_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` =? LIMIT 1", $params);
  }

  //used by the admin to block or unblock a user in a group
  public function block_unblock_User($params = []) {
    return $this->executeStatement_7("SELECT `Blocked_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? ", "SELECT `Group_Admin`,`Blocked_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ?", "UPDATE `User_and_Groups_Details` SET `Blocked_List` = ? WHERE `User_or_Group_Name` =?", $params);
  }

  //checks if user is a group memeber
  public function checkIfGroupMember($params = []) {
    return $this->executeStatement_8("SELECT * FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? AND (`Group_Members` LIKE ?  OR `Group_Admin` LIKE ?)", $params);
  }

  public function loadTaggedMessages($params = []) {
    return $this->executeStatement_9("SELECT `Notification_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? ", $params);
  }

  public function saveChatHistoryOnFirstVisit($params = []) {
    if ($params[3] == 'public') {
      return $this->executeStatement_10("SELECT COUNT(*) FROM Message WHERE `Sender` =? OR `Receiver` =? ", "UPDATE `User_and_Groups_Details` SET `ChatHistory` = ? WHERE `User_or_Group_Name` =?", $params);
    } else if ($params[3] == 'private') {
      return $this->executeStatement_10("SELECT COUNT(*) FROM `Message` WHERE `Sender` IN(?,?) AND `Receiver` IN(?,?)  ", "UPDATE `User_and_Groups_Details` SET `ChatHistory` = ? WHERE `User_or_Group_Name` =?", $params);
    }
  }

  //this builds the chat history
  public function populateChatHistory($params = []) {
    return $this->executeStatement_11($params);
  }

  public function resetNumberOfMessages($params = []) {
    return $this->executeStatement_12($params);
  }

  public function updateChatHistory($params = []) {
    return $this->executeStatement_13("UPDATE `User_and_Groups_Details` SET `chatHistory` = ? WHERE `User_or_Group_Name` = ? ",$params);
  }
  
  public function getChatHistoryFromDB($param) {
    return $this->executeStatement_14("SELECT `chatHistory` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` =? ",$param);
  }

  //it is used to grab the position of a particular message 
  public function locateParticularTaggedMessage($params) {
     if ($params[3] == 'private') {
      return $this->executeStatement_15("SELECT `RefChat`,`ChatID` FROM `Message` WHERE `Sender` IN(?,?) AND `Receiver` IN(?,?) ORDER BY `Time` DESC",$params);
    } else if ($params[3] == 'public') {
      return $this->executeStatement_15("SELECT `RefChat`,`ChatID` FROM `Message` WHERE `Receiver` =? ORDER BY `Time` DESC",$params);
    }
  }

  //fetches the last message sent in a channel.This helps populate the chatHistory with last messages
  public function fetchLastMessages($params){
    return $this->executeStatement_16($params);
  }
  
  //this triggers the database object method for creating a new account or group
  public function registerNewAccount($params,$option1){
    return $this->executeStatement_17("SELECT * FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? ",$params,$option1);
  }
  
  public function loginToAccount($params){
    return $this->executeStatement_18("SELECT * FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? AND `Password` =? ",$params);
  }
  
  public function verifyNewAccount($param){
    return $this->executeStatement_19("SELECT * FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ?","UPDATE `User_and_Groups_Details` SET `VerifiedAccount` = ? WHERE `User_or_Group_Name` = ? ",$param);
  }
  
}
?>