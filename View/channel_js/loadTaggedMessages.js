setTimeout(async function() {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'GET',
       async:true,
      data: {
        taggedMessages:"",//this is not needed but need it to distinguish between if blocks at the backend
        sender: function() {
          return qs['sender'];
        },
        receiver: function() {
          return qs['receiver'];
        }
      },
      success: function (dt) {
        var data = resolve(dt);
      },
      error: function (error) {
        reject(error)
      },
    }).done((data)=> {
      let tagged='';
      if (data) {
        data = JSON.parse(data);
        taggedmessages= data[0];

       /* if(getCookie(qs['receiver']) == ''){
          let previous_no_of_tagged_messages = 0;
        }else{
          let previous_no_of_tagged_messages = getCookie(qs['receiver']) 
        }
        
        setCookie(qs['receiver'],data[1],500);
        */
        
       // a('no_of_tagged_messages').innerHTML =  parseInt(data[1]) - parseInt(previous_no_of_tagged_messages);
        for (var i = 0; i < taggedmessages.length; i++) {
          tagged += 
          `
          <div class='w3-container w3-padding-large w3-bar-item w3-round-xxlarge w3-theme' onclick="locateReplyToTaggedMessage('${taggedmessages[i][3]}')">
          <p class="w3-small"> ${taggedmessages[i][0]} replied your message </p>
          ${taggedmessages[i][2]}
          </div>
          <br>
          `
        }
        a('notifDiv').innerHTML = '<br>'+tagged;

      } else {
       // checkIfGroupMember();
      }
    })
  });
}, 1000)


function locateReplyToTaggedMessage(chatID) {

  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'GET',
       async:true,
      data: {
        sender: function() {
          return qs['sender'];
        },
        receiver: function() {
          return qs['receiver'];
        },
        reply_message_ChatID : function(){
          return chatID;
        },
        channel_type:function(){
          return qs['channel_type'];
        }
      },
      success: function (dt) {
        var data = resolve(dt);
      },
      error: function (error) {
        reject(error)
      },
    }).done((data)=> {
      if (data) {
        offset = 0;
        changeOffset("back",data);
      }
    })
  });
}
