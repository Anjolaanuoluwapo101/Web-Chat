<?php
class Database
{
  protected $connection = null;
  public $folderExists;
  private $audioTypes = ['mpeg',
    'mp3',
    'ogg',
    'wav',
    '3gpp'];

  public function __construct() {
    try {
      $this->connection = new PDO(DB_HOST, DB_USERNAME, DB_PASSWORD);
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->connection->setAttribute(PDO::ATTR_CURSOR, PDO::CURSOR_SCROLL);

      if (!$this->connection) {
        throw new Exception("Could not connect to database.");
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }


  //handles the $_POST['sender'],$_POST['reciever'],$_POST[text_chat],$_POST['text_chatID'],$_POST['text_chat_reply'],$_FILES['file']['name'] ..... in an array
  protected function executeStatement_1($query1, $query2, $params = []) {
    try {
      $stmt = $this->connection->prepare($query1);

      if ($stmt === false) {
        throw New Exception("Unable to do prepared statement: " . $query1);
      }

      $array = $this->checkForMediaInMessage($params[5], $params[6], $params[7], $params[8], $params[9]);
      if ($params[4] != '') {
        if (preg_match('/\[(.*?)\]/', $params[4], $match) == 1) {
          $match = $match[1];
        }
      }

      if ($params && count($params) != 0) {
        $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
        $stmt->bindParam(2, $params[1], PDO::PARAM_STR);
        $stmt->bindParam(3, $params[2], PDO::PARAM_STR);
        $stmt->bindParam(4, $params[3], PDO::PARAM_INT);
        $stmt->bindParam(5, $params[4], PDO::PARAM_STR);
        $stmt->bindParam(6, $array[0], PDO::PARAM_STR);
        $stmt->bindParam(7, $array[1], PDO::PARAM_STR);

      }


      $stmt->execute();

      //we need to update the message array of the receiver
      $this->updateMessageArray($query2, [$params[0], $params[1]]);
      //file type returned should either be video or audio...
      //  return $array[1];

      //we need to update the notification list too
      //when someone tags you message,whether in a gc or private chat...we nees to store it in a array
      //the array will consist of
      //[the receiver name,the senders name,the reply to your message,generated id for that reply message,your initial message ,"unseen"]...
      //note that,someone can tag and reply your messages more than once...
      //what we do is that when somebody initally replies your message...we create an array for them in the Notification_List column(which itself is an array)
      //Example:Anjola replies Joe  message(tags it)...
      //in the Notification_List=["Anjola"=>[["Anjola,Joe.."],[.....]],"Another person"=>[[],[]]]
      //Extra logic is needed for a message tagged in a group because the receiver will be the group instead of the owner of the message to be replied
      //so we grab the name of the imitial owner of the comment from the comment itself..remember a comment has the owner of the comment name in it.
      //the sender is also becomes the group chat! instead of the actual sender
      //
      if ($params[4] != '' && $params[10] == 'public') {
        $this->add_notification([$params[1], $match, $params[2], $params[3], $params[4]]);
      } else if ($params[4] != '' && $params[10] == 'private') {
        $this->add_notification([$params[0], $params[1], $params[2], $params[3], $params[4]]);
      }
    } catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  //this function is called by the protected function executeStatement_1...
  //it adds tagged messages notification....to rhe database..
  //it is retrieved by executeStatement_9;
  private function add_notification($params = []) {
    try {

      $stmt = $this->connection->prepare("SELECT `Notification_List` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` =?  ");
      if ($params) {
        $stmt->bindParam(1, $params[1], PDO::PARAM_STR);
      }
      $stmt->execute();
      $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $serializedNotifList = $stmt[0]['Notification_List'];
      $unserializedNotifList = unserialize($serializedNotifList);
      if (!array_key_exists($params[0], $unserializedNotifList)) {
        $unserializedNotifList[$params[0]] = [];
      }
      $unserializedNotifList[$params[0]][] = [$params[0],
        $params[1],
        $params[2],
        $params[3],
        $params[4],
        "unseen"];
      $serializedNotifList = serialize(($unserializedNotifList));

      $stmt = $this->connection->prepare("UPDATE `User_and_Groups_Details` SET `Notification_List` = ? WHERE `User_or_Group_Name` =?");
      $stmt->bindParam(1, $serializedNotifList, PDO::PARAM_STR);
      $stmt->bindParam(2, $params[1], PDO::PARAM_STR);
      $stmt->execute();


    }catch(Exception $e) {
      throw new ($e->getMessage());
    }
  }


  private function checkForMediaInMessage($fileName, $fileSize, $fileTempStorage, $fileClearance, $fileType):array
  {
    try {
      //if an audio is recorded from the browser itself..it is sent to the backend with a filetype 'video/3gpp' and name.3gpp
      //we need to write a logic that can help resolve this issue
      if ($fileName != "") {
        $audioFileType = substr($fileType, 6); //we need a special variable to track an audio file from a voice recorder

        //we also need know what file type is being uploaded,(whether a video or image)since video and image have same length....
        $fileType = substr($fileType, 0, 5);

        if (in_array($audioFileType, $this->audioTypes)) {
          $folder = "Audio/";
          $fileType = "audio"; //we also need to alter the file type
        } else if ($fileType == "video") {
          $folder = "Videos/";
        } else if ($fileType == "image") {
          $folder = "Imgs/";
        } else {
          return false;
        }

        $a = $folder.strval(date('l_F_Y')); //we need to check if a particular kind of folder exists to store files
        $savepath = $this->fileManager($a);
        $destination = $savepath."/".$fileName;

        if ($fileSize < 130000000 && $fileClearance == 0) {
          move_uploaded_file($fileTempStorage, $destination);
        } else {
          throw new Exception('File is larger than expected size');
        }
      } else {
        //this means the message contains no files....
        $destination = "";
        $fileType = "";
      }

      return [$destination,
        $fileType];
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }




  private function fileManager($param) {
    if (file_exists("Storage_Files/".$param)) {
      $this->folderExists = "Storage_Files/".$param;
    } else {
      mkdir("Storage_Files/".$param, 0777);
      $this->folderExists = "Storage_Files/".$param;
    }

    return $this->folderExists;
  }




  protected function executeStatement_2($query, $params = []) {
    try {
      $stmt = $this->connection->prepare($query);

      if ($params && $params[3] == "private") {
        $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
        $stmt->bindParam(2, $params[1], PDO::PARAM_STR);
        $stmt->bindParam(3, $params[0], PDO::PARAM_STR);
        $stmt->bindParam(4, $params[1], PDO::PARAM_STR);
        $stmt->bindParam(5, $params[2], PDO::PARAM_INT);
      } else if ($params && $params[3] == "public") {
        $stmt->bindParam(1, $params[1], PDO::PARAM_STR);
        $stmt->bindParam(2, $params[2], PDO::PARAM_INT);
      }

      $stmt->execute();
      $fetchedArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
      //array_push($fetchedArray, count($fetchedArray)); //we also send the number of chats to the frontend because we would store in a cookie
      //header('Content-Type:application/json');
      return json_encode($fetchedArray);

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }

  }




  //sending audio messages requires a special function
  protected function executeStatement_3($query, $params = []) {
    try {
      $stmt = $this->connection->prepare($query);

      if ($params) {
        $data = substr($params[5], strpos($params[5], ",") + 1);
        // decode it
        $decodedData = base64_decode($data);
        $filename = $params[3];
        // write the data out to the file
        $a = "Audio/".strval(date('l_F_Y')); //we need to check if a particular kind of folder exists to store files
        $savepath = $this->fileManager($a);
        $destination = $savepath."/".$params[3];

        $fp = fopen($destination, 'w');
        fwrite($fp, $decodedData);
        fclose($fp);


        $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
        $stmt->bindParam(2, $params[1], PDO::PARAM_STR);
        $stmt->bindParam(3, $params[2], PDO::PARAM_INT);
        $stmt->bindParam(4, $destination, PDO::PARAM_STR);
        $stmt->bindParam(5, $params[4], PDO::PARAM_STR);
        $stmt->bindParam(6, $params[6], PDO::PARAM_STR);
      }

      $stmt = $stmt->execute();
      //return "audio"
      return $params[4];


    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }


  protected function executeStatement_4($query1, $query2, $param) {
    try {
      //the first query helps us get the file associated with that message..if one exists
      //then deletes that file so as to save storage
      $stmt = $this->connection->prepare($query1);
      $stmt->bindParam(1, $param, PDO::PARAM_STR);
      $stmt->execute();
      $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $filePath = $stmt[0]['fileName'];

      if ($filePath != '') {
        unlink($filePath);
        $stmt = ''; //reset the variable
      }

      $stmt = $this->connection->prepare($query2);
      if ($param) {
        $stmt->bindParam(1, $param, PDO::PARAM_STR);
      }

      $stmt->execute();
      return "Deleted";

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  //this is also an helper function of executeStatement_7 but it is called independently too by user model

  protected function executeStatement_6($query, $params) {
    try {
      $stmt = $this->connection->prepare($query);
      if ($params) {
        $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
      }
      $stmt->execute();
      $listOfBlockedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['Blocked_List'];
      if ($listOfBlockedUsers != '') {
        $listOfBlockedUsers = unserialize($listOfBlockedUsers);
        if (in_array($params[1], $listOfBlockedUsers)) {
          return true;
        } else {
          return false;
        }
      } else {
        return;
      }

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  protected function executeStatement_7($query1, $query2, $query3, $params) {
    try {
      //we first check if the person that wants to block someone else is an admin
      //if true...we go on to perform admin oriviledges of blocking or unblocking
      $stmt = $this->connection->prepare($query2);
      if ($params) {
        $stmt->bindParam(1, $params[3], PDO::PARAM_STR);
        $stmt->execute();
        $a = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]; //this fetches the admin names and blocked user list(serialized)
        //we need to unserialize the group_admin because it is stored in an array
        $admins = unserialize($a["Group_Admin"]);
      }
      //we check if the person who wants to block another person is even an admin
      if (in_array($params[0], $admins)) {

        //we check if the person to be blocked has been included in the block list before
        $checkIfUserInList = $this->executeStatement_6($query1, [$params[0], $params[1]]);

        //if the person to be blocked has been included in the list and the Admin still wants to
        //block the fellow..we can use the next block to stop that action..because the person you want to blocked has been blocked
        if ($checkIfUserInList == true && $params[2] == 'block') {
          return "already blocked";

        }
        //if the person to be blocked has not been included in the block list before and
        //the admin tries to block them again...then this code block runs
        else if ($checkIfUserInList == false && $params[2] == 'block') {
          if ($a['Blocked_List'] != '') {
            $blocked_list = unserialize($a['Blocked_List']);
            array_push($blocked_list, $params[1]);
            $a = $this->helperfunction($query3, [$blocked_list, $params[0]]);
            if ($a == "added") {
              return "blocked";
            }
          } else if ($a['Blocked_List'] == '') {
            $blocked_list = [];
            array_push($blocked_list, $params[1]);
            $a = $this->helperfunction($query3, [$blocked_list, $params[0]]);
            if ($a == "added") {
              return "blocked";
            }
          }
        }
        //this if block run if the person to be blocked has been included in the block list
        //and the admin wants to unblock the person.
        else if ($checkIfUserInList == true && $params[2] == 'unblock') {
          if ($a['Blocked_List'] != '') {
            $blocked_list = unserialize(($a['Blocked_List']));
            if (in_array($params[1], $blocked_list)) {
              $blocked_list = array_diff($blocked_list, [$params[1]]);
              $this->helperfunction($query3, [$blocked_list, $params[0]]);
              return "unblocked";
            }
          }
        } else if ($checkIfUserInList == false && $params[2] == 'unblock') {
          return "already unblocked";
        }
        //if the person it's not an admin then nothing would happen as admin priviledges wont ve granted
      } else {
        return "Not admin";
      }
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  //works with executeStatement_7 only(as at now)
  private function helperfunction($query, $params = []) {
    try {
      $stmt = $this->connection->prepare($query);
      if ($params) {
        $serialized = serialize($params[0]); //we convert array type back to serialized data
        $stmt->bindParam(1, $serialized, PDO::PARAM_STR);
        $stmt->bindParam(2, $params[1], PDO::PARAM_STR);
      }
      $stmt->execute();
      return "added";
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }



  //this method is responsible for updating the database about how many messages has been sent by a someone to another person
  //it is triggered by executeStatement_1...this statement helps send a new message

  protected function updateMessageArray($query, $params) {
    try {
      /*
      When a sender messages a receiver,we store the number of messages sent by the sender
      in  a serialized array,this array is passed into the Number_of_Messages column of the receiver in the table User_and_Groups_Details...
      The Number_of_Messages column has a default empty serialized array..
      How this column works is that it stores a multidimensional array with each array having a key.For example:If Anjola sends a message to Joe for the first time;we have ["Anjola"=>1]
     If another person sends message to Joe,it becomes ["Anjola"=>1,"Another_User"=>1]
    The values are increnmented based on the number of messages sent

      */

      $stmt = $this->connection->prepare($query);
      if ($stmt) {
        $stmt->bindParam(1, $params[1], PDO::PARAM_STR);
      }
      $stmt->execute();
      $stmt = $stmt -> fetchAll(PDO::FETCH_ASSOC)[0];

      $Number_of_Messages = unserialize($stmt['Number_of_Messages']);
      //the Number_of_Messages column has a default serialized empty array

      //we check if the semder has messaged before...
      if (array_key_exists($params[0], $Number_of_Messages)) {
        $Number_of_Messages[$params[0]] = $Number_of_Messages[$params[0]] + 1;

      } else {
        //if not..it means the sender is sending it first message...
        $Number_of_Messages[$params[0]] = 1;
      }

      $serialized = serialize($Number_of_Messages);

      //we proceed to update that field of the database after editing
      $stmt = $this->connection->prepare("UPDATE `User_and_Groups_Details` SET `Number_of_Messages` = ? WHERE `User_or_Group_Name` = ?");
      $stmt->bindParam(1, $serialized, PDO::PARAM_STR);
      $stmt->bindParam(2, $params[1], PDO::PARAM_STR);
      $stmt->execute();


    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  //this helps check if the user viewing a group is a memeber of that group
  //it initially checks views someone has an intruder ...
  protected function executeStatement_8($query, $params) {
    if ($params) {
      $stmt = $this->connection->prepare($query);
    }
    //appending % allows us to use the Like keyword in the sql query
    //
    $serialized = "%".serialize($params[0])."%";

    $stmt->bindParam(1, $params[1], PDO::PARAM_STR);
    $stmt->bindParam(2, $serialized, PDO::PARAM_STR);
    $stmt->bindParam(3, $serialized, PDO::PARAM_STR);

    $stmt->execute();
    $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($stmt) == 0) {
      //this means that no result was returned and the intruder is not in that group
      return true;
    }
  }

  protected function executeStatement_9($query, $params) {
    try {
      $stmt = $this->connection->prepare($query);
      if ($params) {
        $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
      }
      $stmt->execute();
      $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
      $serializedNotifList = $stmt['Notification_List'];
      $unserializedNotifList = unserialize($serializedNotifList);
      if (array_key_exists($params[1], $unserializedNotifList)) {
        $notif = $unserializedNotifList[$params[1]];
        $notif = array_reverse($notif);
        //this will echo out an array...with each child element being an array of length 4
        //the consists of.. [the receiever,the sender,the reply to a receiver's message from the sender,the generatee chatID,the initial message of the sender,"unseen"]
        $output = [$notif,count($notif)];
        echo json_encode($output);
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  protected function executeStatement_10($query1, $query2, $params) {
    try {
      $stmt1 = $this->connection->prepare($query1);
      if ($params[3] == 'public') {
        $stmt1->bindParam(1, $params[1], PDO::PARAM_STR);
        $stmt1->bindParam(2, $params[1], PDO::PARAM_STR);
      } else if ($params[3] == 'private') {
        $stmt1->bindParam(1, $params[0], PDO::PARAM_STR);
        $stmt1->bindParam(2, $params[1], PDO::PARAM_STR);
        $stmt1->bindParam(3, $params[0], PDO::PARAM_STR);
        $stmt1->bindParam(4, $params[1], PDO::PARAM_STR);
      }
      $stmt1->execute();
      $totalNumberOfMessages = $stmt1->fetchAll(PDO::FETCH_ASSOC)[0]['COUNT(*)'];
     
      return $this->helperfunction2($query2,$totalNumberOfMessages,$params);
    
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function helperfunction2($query,$number_of_messages,$params) {
    try {
      $stmt = $this->connection->prepare("SELECT `displayPicture`,`chatHistory` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` =? ");
      $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
      $chatHistory = unserialize($data['chatHistory']);
      $img= $data['displayPicture'];
      if(!array_key_exists($params[1],$chatHistory)){
        $chatHistory[$params[1]] = [$params[0],$params[1],$params[2],$params[3],$number_of_messages,$img];
  
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, serialize($chatHistory), PDO::PARAM_STR);
        $stmt->bindParam(2, $params[0], PDO::PARAM_STR);
        $stmt->execute();
        return json_encode($chatHistory[$params[1]]);
      }else{
        return "already saved";
      }
      
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

}