<?php
class UserController extends BaseController
{
    /**
     * "/user/list" Endpoint - Get list of users
     */
     //["Anjola","Joe",$_POST['text_chat'],$_POST['reply_text_chat'],$_POST['reply_text_chatID']]
   public function input_1($params)
   {
     try{
       $instance = new UserModel;
       return $instance->saveChat($params);
       
     }catch(Exception $e)
     {
       throw new Exception($e->getMessage());
     }
     
   }
   
   public function input_3($param)
   {
     try{
       $instance = new UserModel;
       return $instance->deleteMessage($param);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
   
   
   public function input_4($params=[])
   {
     try{
       $instance =new UserModel;
       return $instance->checkBlockedUser($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
   public function input_5($params=[])
   {
     try{
       $instance =new UserModel;
       return $instance->block_unblock_User($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
   
   public function input_6($params=[]){
     try{
       $instance =new UserModel;
       return $instance->checkIfGroupMember($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
   
   public function input_7($params=[]){
     try{
       $instance =new UserModel;
       return $instance->saveChatHistoryOnFirstVisit($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
   
   public function input_8($params=[]){
     try{
       $instance =new UserModel;
       return $instance->populateChatHistory($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
   public function input_9($params=[]){
     try{
       $instance =new UserModel;
       return $instance->resetNumberOfMessages($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
  
   public function input_10($params=[]){
     try{
       $instance =new UserModel;
       return $instance->updateChatHistory($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
  
   
   
   public function output_1($params)
   {
     try{
       $instance = new UserModel;
       return $instance->retrieve($params);
       
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
  
   
    
   public function output_2($params)
   {
     try{
       $instance = new UserModel;
       return $instance->retrieveChatHistory($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
       
   }
   
   public function output_3($params)
   {
     try{
       $instance = new UserModel;
       return $instance->loadTaggedMessages($params);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   
   
   public function output_4($param)
   {
     try{
       $instance = new UserModel;
       return $instance->getChatHistoryFromDB($param);
     }catch(Exception $e){
       throw new Exception($e->getMessage());
     }
   }
   

  
  
}

?>