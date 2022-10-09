# Web-Chat
This project is basically whatsapp on the web but with lesser features.It allows for two users or more to chat from the comfort of their browsers
Reason for this project:
To understand MVC architectural style of backend development and play around OOP before moving into PHP frameworks.

Here's a link to the live Test of this web chat app
https://twilightmessage.000webhostapp.com/Chat/Sign_up.php



This is a highly scalable web chatting platform that using PHP as it backend to connect users together to chat.
The stack used in this is at followed.
HTML
CSS
JS(+jQuery)
PHP(strictly OOP,try and catch for error catching,control structures if/else if)
MariaDB MySQL 
Git Version Control
MVC Architecture


The major focus of this project was the backend so please pardon the irregularities in the frontend.

The linkFrontendToBackend scripts,receives http requests via jQuery and depending on the variables available will determine what if block would run.
These blocks instantiates a particular class and calls various class methods from Controller/Api/UserController which then calls another method from another instantiated class.(Basic concepts of MVC)

linkFrontEndToBackend.php script works with View/channel.php (a PHP script but it houses the HTML elements so it's considered a frontend file).Request initiated
from channel.php go to linkFrontendToBackend.php.

Likewise linkFrontendToBackend2.php works is triggered by jQuery calls from chathistory.php 

More on View/channel.php: It's the script that contains the HTML elements with renders previous chat messages along with messaging options like sending videos,image and audio.
It works with the View/channel_js(where the jQuery ajax calls are initially made ).They're different ajax calls for various purposes.But all run using the async method(while some run only once)
and each ajax call isn't affected by the previous or the succeeding one.jQuery request are wrapped in promises and resolved on success.

View/chathistory.php helps display the previous people you have had conversation with.

Current features supported by this web chat as at of second commit include
Ability to Create Group Chat.
Check if a user is a member of a group chat(Although the back end for this exist,it is deactivated for no reason in particular)
Block people so they can't chat with you.
View tagged messages(if any of your message was tagged,there's a div that displays this)

This is the very basics of this web chat.
Database.php has several comments in it for proper understanding of some class method.To understand what an execute_statement does(kindly check the Model/UserModel.php)
