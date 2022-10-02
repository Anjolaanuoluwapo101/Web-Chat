let idForSentMessage; //every message id-ed before displayed and sent to a backend.


//this function allows user to send a message to the backend without causing a page refresh
$(function () {
  $('#messageForm').on('submit', function (e) {
    e.preventDefault();

    //this ensures that the MessageID is generated for messages that aren't replying to a tagged message
    //if the message is replying a previous message...it means repliedMessageDiv will NOT BE empty and populateTaggedMessageDiv() will handle the id generation....
    if (a('repliedMessageDiv').innerHTML == '') {
      idForSentMessage = generateIDForNewMessage();
      a('MessageID').value = idForSentMessage;
    }
    
    a('channel_type').value=qs.channel_type;
    
     //we need to add the sender's name for better ux....
     a('message').value = `<a href=''> <b>${qs['sender']}</b> </a><br>`+a('message').value;
     
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'POST',
      beforeSend: function(xhr){
        xhr.setRequestHeader('Accept', 'application/json');
      },
      data: new FormData(this),
      contentType: false,
      processData: false,
      success: function (data) {
        //we clear the message div whose content has just being sent for better ux
        a('message').value="";
        a('repliedMessage').value="";
        a('repliedMessageDiv').innerHTML="";
      }
    });

  });

});


//this function runs when you click the reply icon to reply a particular message..
//it generates an id for the new Message and populates the repliedMessageDiv
//the repliedMessageDiv is a div thag contains the message to be replied
function populateTaggedMessageDiv(id) {
  let taggedArea = a('repliedMessageDiv');
  taggedArea.innerHTML = `<span onclick="clearTaggedMessageDiv()" class="w3-right"><i class="fa fa-times"></i></span>`+document.getElementById(id).innerHTML;
  a('repliedMessage').value = a(id).innerHTML;
  idForSentMessage = generateIDForNewMessage();
  a('MessageID').value = idForSentMessage;
}

//if the user decides to cancel the tagged message....this function is called for that purpose
function clearTaggedMessageDiv() {
  a('repliedMessageDiv').innerHTML = '';
  a('repliedMessage').value = '';
  a('MessageID').value = '';
}

//this updates the conversation div tag with the message the user just send
function updateChatDOM() {
  let file, updateVar, message = a('message').value;

  if (a('file').value == "") {
    file = '';
  } else {
    file = `<img style="margin:auto" width="50%"  src="../Storage_Files/Imgs/Wednesday_September_2022/`+a('file').files[0].name+`" alt="Refresh to display \n Recepient can see Image displayed ">`;
  }
  let repliedMessage = a('repliedMessage').value;
  updateVar =
  `
  <div style="width:50%;">
  <!--this div contains a tagged message...it can be empty and hence invisible-->
  <div class="w3-padding-large w3-bar-block w3-opacity w3-center" style="margin:auto;">
  ${repliedMessage}
  <!-- <div class="w3-bar-item  w3-leftbar w3-border-black" style="overflow:hidden"></div>-->
  </div>
  <!--This div contains the file for that message..it can be empty and not visible if the user doesn't send a file  -->
  <div class="w3-padding-large w3-bar-block w3-opacity w3-center" style="">
  ${file}
  </div>

  <!--this div contains the reply mesaage to a tagged message or just a normal message if it wasnt a reply to a message-->
  <div class="w3-padding-large w3-bar-block">
  <div class="w3-bar-item w3-container w3-leftbar w3-border-black" id="${idForSentMessage}">
  ${message}
  </div>
  <div class="w3-bar-item" onclick="populateTaggedMessageDiv('${idForSentMessage}')">
  <i class="fa fa-mail-reply" ></i>
  </div>
  </div>
  </div>
  `
  a('conversation').innerHTML += updateVar;
}