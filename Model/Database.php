<?php
class Database
{
  protected $connection = null;
  public $folderExists;
  private $audioTypes = ['mpeg','mp3','ogg','wav','3gpp'];

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


  //handles the $_POST['sender'],$_POST['reciever'],$_POST[text_chat],$_POST['text_chatID'],$_POST['text_chat_reply'],$_FILES['file']list and lastly $_POST['channel_type'] ..... in an array
  protected function executeStatement_1($query1, $query2, $params = []) {
    try {
      $stmt = $this->connection->prepare($query1);

      if ($stmt === false) {
        throw New Exception("Unable to do prepared statement: " . $query1);
      }

      $mediaDestinationAndFileTypePair = $this->checkForMediaInMessage($params[5], $params[6], $params[7], $params[8], $params[9]);


      if ($params && count($params) != 0) {
        $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
        $stmt->bindParam(2, $params[1], PDO::PARAM_STR);
        $stmt->bindParam(3, $params[2], PDO::PARAM_STR);
        $stmt->bindParam(4, $params[3], PDO::PARAM_INT);
        $stmt->bindParam(5, $params[4], PDO::PARAM_STR);
        $stmt->bindParam(6, $mediaDestinationAndFileTypePair[0], PDO::PARAM_STR);
        $stmt->bindParam(7, $mediaDestinationAndFileTypePair[1], PDO::PARAM_STR);

      }


      $stmt->execute();

      //we need to update the message array of the receiver
      $this->updateMessageArray($query2, [$params[0], $params[1], $params[10]]);

      //we need to update the notification list column too..by default..it is a serialized empty array
      //when someone tags you message,whether in a gc or private chat...we need to store their replies so that the other person tagged from the message in an array so that auser can reply them later
      //the array will consist of
      //[the receiver name,the senders name,the reply to your message,generated id for that reply message,your initial message ,"unseen"]...
      //note that,someone can tag and reply your messages more than once...
      //what we do is that when somebody initally replies your message...we create an array for them in the Notification_List column(which itself is an array)
      //Example:Anjola replies Joe  message(tags it)...
      //in the Notification_List=["Anjola"=>[["Anjola,Joe,reply content...."],[.....]],"Another person"=>[[],[]]]

      //Extra logic is needed for a message tagged in a group because the receiver will be the group according to params[1]... a group cannot reply a message..it is the group members that do that
      //so we grab the name of the initial owner of the comment from the comment itself..remember a comment has the owner of the comment name in it.(it is prepended to it t the frontend before sent here)
      //the sender also becomes the group chat! instead of the actual sender(params[0])

      //if the message tagged is in a private channel instead...we simply use the receiver like that because a private channel consists of just two poeple
      if ($params[4] != '') {
        if (preg_match('/<b>(.*?)<\/b>/s', $params[4], $match) == 1) {
          $match = $match[1];
        }
      }

      if ($params[4] != '' && $params[10] == 'public') {
        $this->tagged_messages([$params[1], $match, $params[2], $params[3], $params[4]]);
      } else if ($params[4] != '' && $params[10] == 'private') {
        $this->tagged_messages([$params[0], $params[1], $params[2], $params[3], $params[4]]);
      }
    } catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  //this function is called by the protected function executeStatement_1...
  //it adds tagged messages notification....to the database..
  //it is retrieved by executeStatement_9;
  private function tagged_messages($params = []) {
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

      //this helps us reset the tagged messages list if it getting too long
      if (count($unserializedNotifList[$params[0]]) > 10) {
        $unserializedNotifList[$params[0]] = [];
      }

      $unserializedNotifList[$params[0]][] = [$params[0],$params[1],$params[2],$params[3],"unseen"];
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
        $destination = $savepath."/".time().$fileName;

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

      return [$destination,$fileType];
    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }
  }



  //helps check if a folder exists,if not...it creates one
  private function fileManager($param) {
    if (file_exists("Storage_Files/".$param)) {
      $this->folderExists = "Storage_Files/".$param;
    } else {
      mkdir("Storage_Files/".$param, 0777);
      $this->folderExists = "Storage_Files/".$param;
    }

    return $this->folderExists;
  }



  //this object method is responsible for displaying the messages in a channel,so that recipients can see their previous chat conversations....
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

      return json_encode($fetchedArray);

    }catch(Exception $e) {
      throw new Exception($e->getMessage());
    }

  }




  //sending audio messages requires a special function
  //atrophied...not in use.
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


  //this object method is called when a message is to be deleted,it checks initially if that mesage contains a media...deletes it before deleting the actual message body(the row it occupies in the db)
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

  //this works independently and also as an helper function to executeStatement_7...
  //how it works independently:it helps check if a user has been blocked by the recipient

  //this is also an helper function of executeStatement_7 but it is called independently too by user model
  //
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

  //this is an elaborated object method that allows a group admin to block somebody from chatting in the group
  //it also allows a user to block a recipient from chatting with them(there is a group admin column.Whoever creates a normal account-private channel or group-public channel ) becomes a group admin to it
  //the group admin column is a serialiazed array.
  //A public channel can have more than one admins->it checks if a person is a group admin.(this check is also done in a private channel though it's unnecessary)
  //After that is done,several checks are done to confirm if the user to be blocked has been blocked and the admin is trying to block such person again(it stops that from happening)
  //Also stops the admin from unblocking someone that has not been blocked before

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
    This is also how a public channel(group) will store this values although the usage of this data is different
      */

      $stmt = $this->connection->prepare($query);
      if ($stmt) {
        $stmt->bindParam(1, $params[1], PDO::PARAM_STR);
      }
      $stmt->execute();
      $stmt = $stmt -> fetchAll(PDO::FETCH_ASSOC)[0];

      $Number_of_Messages = unserialize($stmt['Number_of_Messages']);
      //the Number_of_Messages column has a default serialized empty array

      //we check if the sender has messaged before...
      if (array_key_exists($params[0], $Number_of_Messages)) {
        $Number_of_Messages[$params[0]] = $Number_of_Messages[$params[0]] + 1;
        //we also need to store the last time a message was sent to the backend from that person
        if ($params[2] == 'private') {
          $time = $params[0]."_last_Message_Time";
        } else {
          $time = "last_Message_Time";
        }
        $Number_of_Messages[$time] = time();

      } else {
        //if not..it means the sender is sending it first message...
        $Number_of_Messages[$params[0]] = 1;
        //we also need to store the last time a message was sent to the backend from that person
        //unlike private channels...for public channel we only need the last time a message was sent to the group not including who sent it
        if ($params[2] == 'private') {
          $time = $params[0]."_last_Message_Time";
        } else {
          $time = "last_Message_Time";
        }
        $Number_of_Messages[$time] = time();
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

  //this is the reverse of your tagged messages...
  //it helps dumps out the replies to your tagged messages
  //
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
        //it also outputs the counted notif
        $output = [$notif,count($notif)];
        echo json_encode($output);
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  //This object method works in tandem with helperfunction2 which is private because it is only accessed by this object method(executeStatement_10)
  /*
They both combine to actually create a chatHistory data
The chatHistory column is a serialized empty array at first.
When a user views the link of a recipient and tries to chat to them...the recipient data is converted to an array and added to this chatHistory data...
This allows the user to access the recipient later on

The database column that this object method alters is the chatHistory found in User_and_Groups_Details...
It takes in the following params [the user,the recipient,the recipientLink,the channel_type]
user and recipient are also sender and receiver respectively....

The first thing done by the method is to distinguish between the channel type ...
A public channel type means the recipient is a group(the receiver in the link refers to the recipient )
A private channel type means,it's just another person
This allows the method to use different queries to get the total number of messages sent between that private channel /public
The messages sent to a public channel includes all the messages send by each individual members to a group..
The messages sent to a private channel includes all the messages sent betwen two people
The value is gotten and passed to helperfunction2

*/
  protected function executeStatement_10($query1, $query2, $params) {
    try {
      /* $stmt1 = $this->connection->prepare($query1);
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
     */

      return $this->helperfunction2($query2, $params);

    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }


  //alias of executeStatement_10
  /*
this object method takes a query,the value gotten from executeStatement_10 and all the params taken by executeStatement_10(params is an array)
This helperfunction2 helps to alter chatHistory column
It's a serialized empty array by default...
Please note that we are altering the chat history column of the user... then the recipient....
The object method first retrieves the chatHistory of the user...
unserializes it next...
Using the recipient name as a key ,it then  stores the params(the array to it along the image of the recipient nd the value gotten from the other )
Then updates it back
*/
  private function helperfunction2($query, $params) {
    try {
      //we connect and obtain both rows for both the user and the recipient
      //the user first
      $stmt1 = $this->connection->prepare("SELECT `displayPicture`,`chatHistory` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` =? ");
      $stmt1->bindParam(1, $params[0], PDO::PARAM_STR);
      $stmt1->execute();
      $data1 = $stmt1->fetchAll(PDO::FETCH_ASSOC)[0];
      $chatHistory1 = unserialize($data1['chatHistory']);
      $img1 = $data1['displayPicture'];

      //the recipient next
      $stmt2 = $this->connection->prepare("SELECT `displayPicture`,`chatHistory` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` =? ");
      $stmt2->bindParam(1, $params[1], PDO::PARAM_STR);
      $stmt2->execute();
      $data2 = $stmt2->fetchAll(PDO::FETCH_ASSOC)[0];
      $chatHistory2 = unserialize($data2['chatHistory']);
      $img2 = $data2['displayPicture'];




      if (!array_key_exists($params[1], $chatHistory1)) {
        /*if ($number_of_messages == '') {
          $number_of_messages = 0;
        }*/

        $timeStamp = time();
        $chatHistory1[$params[1]] = [$params[0],$params[1],$params[2],$params[3],$img2,0,$timeStamp];

        $stmt1 = $this->connection->prepare($query);
        $stmt1->bindParam(1, serialize($chatHistory1), PDO::PARAM_STR);
        $stmt1->bindParam(2, $params[0], PDO::PARAM_STR);
        $stmt1->execute();

        if (!array_key_exists($params[0], $chatHistory2)) {
          //params[2] is the link useful for the user because in the link..the user is the sender and the reci is the receiver
          //we need to reverse that for the receipient so we use str replace
          $link = $params[2];
          $pattern1 = "/".$params[1]."/";
          $pattern2 = "/".$params[0]."/";
          $modLink = preg_replace($pattern1, $params[0], $link); //this changes the receipient query value
          $modLinkFinal = preg_replace($pattern2, $params[1], $modLink, 1);

          $chatHistory2[$params[0]] = [$params[1],$params[0],$modLinkFinal,$params[3],$img1,0,$timeStamp];

          $stmt2 = $this->connection->prepare($query);
          $stmt2->bindParam(1, serialize($chatHistory2), PDO::PARAM_STR);
          $stmt2->bindParam(2, $params[1], PDO::PARAM_STR);
          $stmt2->execute();

        }

        return json_encode($chatHistory1[$params[1]]);
      } else {
        return "already saved";
      }

    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /*
  */
  protected function executeStatement_11($params) {
    try {
      $output = [];
      $dataArray = json_decode($params[1]);
      $dataArrayLength = count($dataArray);
      for ($i = 0; $i <= $dataArrayLength; $i++) {
        $eachData = explode('|||', $dataArray[$i]);
        $stmt = $this->connection->prepare('SELECT `displayPicture` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ?');
        $stmt->bindParam(1, $eachData[0], PDO::PARAM_STR);
        $stmt->execute();
        $displayPictureOfRecipient = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['displayPicture'];
        $stmt = '';

        if ($eachData[1] == 'private') {
          $stmt = $this->connection->prepare("SELECT `Number_of_Messages` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ?");
          $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
          $stmt->execute();
          $Number_of_Messages = unserialize($stmt->fetchAll(PDO::FETCH_ASSOC)[0]['Number_of_Messages']);
          if (array_key_exists($eachData[0], $Number_of_Messages)) {
            $a = $Number_of_Messages[$eachData[0]];
            $b = $eachData[0]."_last_Message_Time";
            $c = $Number_of_Messages[$b];
            $output[] = [$displayPictureOfRecipient,$a,$c];
          }else{
            $output[] = [$displayPictureOfRecipient,0,''];
          }
        } else if ($eachData[1] == 'public') {
          $stmt = $this->connection->prepare("SELECT `Number_of_Messages`,`Number_of_Messages_Total` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ?");
          $stmt->bindParam(1, $eachData[0], PDO::PARAM_STR);
          $stmt->execute();
          $Result = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
          $Number_of_Messages = unserialize($Result['Number_of_Messages']);
          $Number_of_Messages_Total = unserialize($Result['Number_of_Messages_Total']);

          $b = $Number_of_Messages["last_Message_Time"];
          unset($Number_of_Messages["last_Message_Time"]);
          $a = array_sum($Number_of_Messages);
          $c = $Number_of_Messages_Total[$params[0]];
          $c = $a - $c;
          $output[] = [$displayPictureOfRecipient,$c,$b];
        }
      }
      return json_encode($output);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  protected function executeStatement_12($params) {
    try {
      if ($params[2] == 'public') {
        $stmt1 = $this->connection->prepare("SELECT `Number_of_Messages` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? ");
        if ($params) {
          $stmt1->bindParam(1, $params[1], PDO::PARAM_STR);
        }
        $stmt1->execute();
        $Number_of_Messages = unserialize($stmt1->fetchAll(PDO::FETCH_ASSOC)[0]['Number_of_Messages']);
        unset($Number_of_Messages['last_Message_Time']);
        $a = array_sum($Number_of_Messages);

        $stmt2 = $this->connection->prepare("SELECT `Number_of_Messages_Total` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? ");
        $stmt2->bindParam(1, $params[1], PDO::PARAM_STR);
        $stmt2->execute();
        $Number_of_Messages_Total = unserialize($stmt2->fetchAll(PDO::FETCH_ASSOC)[0]['Number_of_Messages_Total']);
        $Number_of_Messages_Total[$params[0]] = $a;

        $stmt3 = $this->connection->prepare("UPDATE `User_and_Groups_Details` SET `Number_of_Messages_Total` =?  WHERE `User_or_Group_Name` = ?");
        $stmt3->bindParam(1, serialize($Number_of_Messages_Total), PDO::PARAM_STR);
        $stmt3->bindParam(2, $params[1], PDO::PARAM_STR);
        $stmt3->execute();

      } else if ($params[2] == 'private') {
        $stmt1 = $this->connection->prepare("SELECT `Number_of_Messages` FROM `User_and_Groups_Details` WHERE `User_or_Group_Name` = ? ");
        $stmt1->bindParam(1, $params[0], PDO::PARAM_STR);
        $stmt1->execute();
        $Number_of_Messages = unserialize($stmt1->fetchAll(PDO::FETCH_ASSOC)[0]['Number_of_Messages']);
        if (array_key_exists($params[1], $Number_of_Messages)) {
          $Number_of_Messages[$params[1]] = 0; //since the user has viewed the recipient message...we reset the Number_of_Messages for that receipient

        }

        $stmt2 = $this->connection->prepare("UPDATE `User_and_Groups_Details` SET `Number_of_Messages` = ? WHERE `User_or_Group_Name` =? ");
        $stmt2->bindParam(1, serialize($Number_of_Messages), PDO::PARAM_STR);
        $stmt2->bindParam(2, $params[0], PDO::PARAM_STR);
        $stmt2->execute();

      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /*
  This object method helps us overwrite the chathistory stored on the db with chatHistory stored on the client device
  */
  protected function executeStatement_13($query, $params) {
    try {
      $stmt = $this->connection->prepare($query);
      $array = json_decode($params[1]);
      $newArray = [];
      foreach ($array as $arrayChild) {
        $newArray[$arrayChild[1]] = $arrayChild;
      }
      $newArray = serialize($newArray);
      if ($params) {
        $stmt->bindParam(1, $newArray, PDO::PARAM_STR);
        $stmt->bindParam(2, $params[0], PDO::PARAM_STR);
      }

      $stmt->execute();
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  /*
  This object method simply grabs the chathistory from in the db and overwrites the one on the localstorage...if it even exists..
  This object method runs usually when a user signs in at first,because we want to get the chathistory available on the localstorage too
  It is also ru periodically from time to time
  */
  protected function executeStatement_14($query, $param) {
    try {
      $stmt = $this->connection->prepare($query);
      if ($param) {
        $stmt->bindParam(1, $param, PDO::PARAM_STR);
      }
      $stmt->execute();
      $chatHistory = unserialize($stmt->fetchAll(PDO::FETCH_ASSOC)[0]['chatHistory']);
      $chatHistory = array_values($chatHistory);
      return json_encode($chatHistory);

    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  //Method input: sender,receiver ,chatID,channel_type...
  //This methods gets all the chat between a channel...
  //Then it searches the result array until a chatID that of the ChatID being searched since chatIDs are unique
  //Then a litte bit of data manipulation is done to actually obtain an offset and that offset is returned to the frontend
  protected function executeStatement_15($query, $params) {
    try {
      $stmt = $this->connection->prepare($query);
      if ($params[3] == 'private') {
        $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
        $stmt->bindParam(2, $params[1], PDO::PARAM_STR);
        $stmt->bindParam(3, $params[0], PDO::PARAM_STR);
        $stmt->bindParam(4, $params[1], PDO::PARAM_STR);
      } else if ($params[3] == 'public') {
        $stmt->bindParam(1, $params[1], PDO::PARAM_STR);
      }
      $stmt->execute();
      $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
      //$chats = array_reverse($chats);
      $count = -1;
      for ($i = 0; $i < count($chats); $i++) {
        $count++;
        if ($chats[$i]['ChatID'] == $params[2]) {
          $limit = 5; //this should be changed,depending on the amount of data that is dumped at the frontend
          return $count;
        }
      }

    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  protected function executeStatement_16($params) {
    try {
      $output = [];
      //params[0] is the sender or a user's name
      $dataArray = json_decode($params[1]);
      if ($params) {
        foreach ($dataArray as $data) {
          $receiverChannel_typePair = explode('|||', $data);
          if ($receiverChannel_typePair[1] == 'private') {
            $stmt = $this->connection->prepare('SELECT `Chat` FROM `Message` WHERE `Sender` IN(?,?) AND `Receiver` IN(?,?) ORDER BY `Time` DESC LIMIT 1');
            $stmt->bindParam(1, $params[0], PDO::PARAM_STR);
            $stmt->bindParam(2, $receiverChannel_typePair[0], PDO::PARAM_STR);
            $stmt->bindParam(3, $params[0], PDO::PARAM_STR);
            $stmt->bindParam(4, $receiverChannel_typePair[0], PDO::PARAM_STR);
            $stmt->execute();
            $output[] = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['Chat'];
          } else if ($receiverChannel_typePair[1] == 'public') {
            $stmt = $this->connection->prepare('SELECT `Chat` FROM `Message` WHERE  `Receiver` = ? ORDER BY `Time` DESC LIMIT 1');
            $stmt->bindParam(1, $receiverChannel_typePair[0], PDO::PARAM_STR);
            $stmt->execute();
            $output[] = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['Chat'];
          }
        }
      }
      return json_encode($output);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  protected function executeStatement_17($query1, $params,$option1) {
    try {
      if($option1 == 'private'){
      $username = $params[0];
      }else{
        $a = explode('|||',$params[0]);
        $username = $a[0];
        $adminName = $a[1];
      }
      if ($params) {
        //we check if the username already exists before
        $stmt = $this->connection->prepare($query1);
        $stmt->bindParam(1, $username, PDO::PARAM_STR);
        $stmt->execute();
      }
      $check = $stmt->fetchAll(PDO::FETCH_ASSOC);
      //print_r($check);

      if (count($check) != 0) {
        return "false";
      }
      //since username is not taken...we proceed
      //we upload the image first
      $mediaDestinationAndFileTypePair = $this->checkForMediaInMessage($params[3]['name'], $params[3]['size'], $params[3]['tmp_name'], $params[3]['error'], $params[3]['type']);
      //we proceed to update database with new row...
        
      //default input values 
      $serializedEmptyArray = "a:0:{}";
      $serializedArray = serialize([$adminName]);
      $zero = 0;
      $channel_type = $option1;
      $encrypted = '';
      $group_members = serialize([$adminName]);
      //$name = $username;
      $pass = '';
      $email = '';
      
      //this if block is critical in distinguishing betweem private amd public channels
      //we need to send an encrypted key to the email of the new user
      if($channel_type == 'private'){
        $ciphering = "AES-128-CTR";
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = 0;
        $encryption_iv = '3002200330022003';
        $encryption_key = "webchatbyAJ";
        $encrypted = openssl_encrypt($username, $ciphering, $encryption_key, $options, $encryption_iv);
        $group_members = '';//private channel is an acct for one person
        $pass = $params[1];
        $email = $params[2];
        $serializedArray = serialize([$username]);
        
        //cook the email to be sent
        $message = $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'],1)."/Verify.php?q=$encrypted";
        $subject = 'VERIFY WEB-CHAT Account';
        $to = $email;//email of the new user
        $headers = 'From: anjolaakinsoyinu@gmail.com' . "\r\n" .'Reply-To: anjolaakinsoyinu@gmail.com' . "\r\n" .'X-Mailer: PHP/' . phpversion();
        mail($to,$subject,$message);
      }
      
      $stmt = $this->connection->prepare('INSERT INTO `User_and_Groups_Details`(`User_or_Group_Name`,`Password`,`Email`,`Group_Admin`,`Blocked_List`,`Number_of_Messages`,`Number_of_Messages_Total`,`Status`,`EncryptionString`,`type`,`Group_Members`,`Notification_List`,`chatHistory`,`displayPicture`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
      $stmt->bindParam(1,$username,PDO::PARAM_STR);
      $stmt->bindParam(2,$pass,PDO::PARAM_STR);
      $stmt->bindParam(3,$email,PDO::PARAM_STR);
      $stmt->bindParam(4,$serializedArray,PDO::PARAM_STR);
      $stmt->bindParam(5,$serializedEmptyArray,PDO::PARAM_STR);
      $stmt->bindParam(6,$serializedEmptyArray,PDO::PARAM_STR);
      $stmt->bindParam(7,$serializedEmptyArray,PDO::PARAM_STR);
      $stmt->bindParam(8,$zero,PDO::PARAM_INT);
      $stmt->bindParam(9,$encrypted,PDO::PARAM_STR);
      $stmt->bindParam(10,$channel_type,PDO::PARAM_STR);
      $stmt->bindParam(11,$group_members,PDO::PARAM_STR);//a user friends are stored here...but if its a group,the group memebers will be stored in this array
      $stmt->bindParam(12,$serializedEmptyArray,PDO::PARAM_STR);
      $stmt->bindParam(13,$serializedEmptyArray,PDO::PARAM_STR);
      $stmt->bindParam(14,$mediaDestinationAndFileTypePair[0],PDO::PARAM_STR);
      
      $stmt->execute();
      if($channel_type == 'private'){
        $expireDate = time() + intval(700000);
        $expireDateForPopup = time() + intval(240);
        setcookie('WEBCHAT',json_encode([$username,$pass]),$expireDate,'/', $_SERVER['SERVER_NAME'],false,true);//this cookie should not be accessible by JS
        setcookie('timeout','timeout',$expireDateForPopup);
      }
      
      return json_encode([$username,$pass]);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function mailTo($to,$subject,$message,$headers) {
    try {
      mail($to, $subject, $message, $headers);

    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

//this object method is fired when a user tries to sign in....
//checks if the account is verified,if not...It tells the user to check their email....
  protected function executeStatement_18($query,$params){
    try {
      if($params){
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1,$params[0],PDO::PARAM_STR);
        $stmt->bindParam(2,$params[1],PDO::PARAM_STR);
        $stmt->execute();
      }
        $check = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($check) == 1){
          if($check[0]['VerifiedAccount'] == 0){
            return "unverified";
          }else{
            setcookie('WEBCHAT',json_encode([$params[0],$params[1]]),$expireDate,'/', $_SERVER['SERVER_NAME'],false,true);//this cookie should not be accessible by JS
            header('Location:View/chathistory.php?sender='.$params[0]);
          }
        }else if(count($check) == 0){
          return "<script>alert('Account does not exist'); history.back(); </script>";
        }
    } catch (Exception $e ) {
      throw new Exception($e->getMessage());
    }
  }
  
  //this works with verify.php script.It checks if the encrypted string(which is decrypted) exists in the db...
  //if it does...a single row(a new row-signifying a user acct ) is returned as the result.The verifiedaccount coln is then updated
  protected function executeStatement_19($query1,$query2,$param){
    try {
      //initialize some required paramss
      $one = 1;
      
      if($param){
        $stmt = $this->connection->prepare($query1);
        $stmt->bindParam(1,$param,PDO::PARAM_STR);
        $stmt->execute();
      }
      $check = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(count($check) == 1){
        //we have confimed that the new account exists 
        //Now we need to verify this account
        $stmt = $this->connection->prepare($query2);
        $stmt->bindParam(1,$one,PDO::PARAM_INT);
        $stmt->bindParam(2,$param,PDO::PARAM_STR);
        $stmt = $stmt->execute();
        if($stmt == 1){
          return "verified";
        }
      }
      
    } catch (Exception $e ) {
      throw new Exception($e->getMessage());
    }
  }
  
  protected function executeStatement_20($params){
    try {
      if($params){
        
      }
    } catch (Exception $e ) {
      throw new Exception($e->getMessage());
    }
  }
  
}