<?php
require_once PROJECT_ROOT_PATH . "/Model/Database.php";

class UserModel extends Database
{
  public function saveChat($params) {
    return $this->executeStatement_1("INSERT INTO Message(`Sender`,`Receiver`,`Chat`,`ChatID`,`RefChat`,`fileName`,`fileType`) VALUES(?,?,?,?,?,?,?)","SELECT * FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ?",$params);
  }

  public function retrieve($params = []) {
    if($params[3] == 'private'){
      $query= "SELECT * FROM `Message` WHERE `Sender` IN(?,?) AND `Receiver` IN(?,?) ORDER BY `Time` DESC LIMIT ?,5 ";
    }else if($params[3] == 'public'){
      $query = "SELECT * FROM `Message` WHERE  `Receiver` = ? ORDER BY `Time` DESC LIMIT ?,5 ";
    }
    return $this->executeStatement_2($query , $params);
  }

  public function saveAudioMessage($params = []) {
    return $this->executeStatement_3("INSERT INTO Message(`Sender`,`Receiver`,`ChatID`,`fileName`,`fileType`,`RefChat`) VALUES(?,?,?,?,?,?)", $params);
  }
  
  public function deleteMessage($param) {
   //since chatID is unique we only need that paramater to fetch that particular row from the db
   //first query obtains the row and check if the message contains a file..so that file can be deleted
    return $this->executeStatement_4("SELECT * FROM `Message` WHERE `ChatID` = ? LIMIT 1","DELETE FROM `Message` WHERE `ChatID` = ?", $param);
  }
  
  public function retrieveChatHistory($param){
   //since User table is unique we only need that paramater to fetch that particular row from the db
   //first query obtains the row and check if the message contains a file..so that file can be deleted
   
    return $this->executeStatement_5("SELECT `ChatHistory` FROM `UserAccount` WHERE `User` = ?",$param);
  }
 
  //this function automatically runs when a user opens a chat to cht someone or a gc
  public function checkBlockedUser($params=[]){
    return $this->executeStatement_6("SELECT `Blocked_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` =? LIMIT 1",$params);
  }
  
  public function block_unblock_User($params=[]){
    return $this->executeStatement_7("SELECT `Blocked_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? ","SELECT `Group_Admin`,`Blocked_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ?" ,"UPDATE `User_and_Groups_Details` SET `Blocked_List` = ? WHERE `User_or_Group_Name` =?",$params);
  }
  
 public function checkIfGroupMember($params=[]){
   return $this->executeStatement_8("SELECT * FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? AND (`Group_Members` LIKE ?  OR `Group_Admin` LIKE ?)",$params);
 }
 
 public function loadTaggedMessages($params=[]){
   return $this->executeStatement_9("SELECT `Notification_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? ",$params);
 }
 
 public function saveChatHistory($params=[]){
   if($params[3] == 'public'){
     return $this->executeStatement_10("SELECT COUNT(*) FROM Message WHERE `Sender` =? OR `Receiver` =? ","UPDATE `User_and_Groups_Details` SET `ChatHistory` = ? WHERE `User_or_Group_Name` =?",$params);
   }else if($params[3] == 'private'){
     return $this->executeStatement_10("SELECT COUNT(*) FROM `Message` WHERE `Sender` IN(?,?) AND `Receiver` IN(?,?)  ","UPDATE `User_and_Groups_Details` SET `ChatHistory` = ? WHERE `User_or_Group_Name` =?",$params);
   }
 }

}
?>