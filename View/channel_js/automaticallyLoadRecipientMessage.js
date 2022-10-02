//this function automatically runs and queries the server to chceck if
//the recipient has replies a message....
let storePreviousMessage, offset = 0;

function changeOffset(move) {
  if (move == "back") {
  //  alert("Previous message will be displayed soon")
    offset = offset + 5; //since want to go further down the db table to load older message

    //what if we want to go up the table? we need a code piece that displays a div that when click..increases ye offset value..
    a("increaseOffset").style.display = "block";
  } else if (move == "forward") {
   // alert('Newer messages will be display soon')
    offset = offset - 5;
    if (offset == 0) {
      a("increaseOffset").style.display = "none"; //hide newer messages div if no message will be present since offset is 0
    }
  }
}

function loadRecipientMessage() {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'GET',
      async:true,
      data: {
        offset: function() {
          if (offset == 0) {
            return offset;
          } else if (offset < 0) {
            return 0;
          } else if (offset > 0) {
            return offset;
          }
        },
        sender: function(){
          return qs['sender'];
        },
        receiver: function(){
          return qs['receiver'];
        },
        channel_type:function(){
          return qs['channel_type'];
        }
      },
      success: function (dt) {
        var data = resolve(dt);
      },
      error: function (err) {
       var error= reject(error)
      },
    }).done(function (data) {
      updateDOMWithDBData(data);
    },function(error){
      //alert(error);
    });
  });
}


function deleteMessage(id){
 return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'POST',
      tryCount:0,
      retryLimit:2,
      async:true,
      data: {
        idOfMessageToBeDeleted :id,
        sender: function(){
          return qs['sender'];
        },
        receiver:function(){
          return qs['receiver'];
        }
      },
      success: function (dt) {
        var data = resolve(dt);
        alert('Message deleted')
      },
      error: function (error) {
        reject(error)
      },
    })
  });
}



async function triggerFunction() {
  await loadRecipientMessage();
}

//makes a request to the db
setInterval(()=> {
  triggerFunction();
}, 5000);


function updateDOMWithDBData(data) {
  //this if block prevents the dom from being updated with the same data..
  //prevents unnecessary refresh
  if (storePreviousMessage == data) {
    return false;
  } else {
    storePreviousMessage = data;
  }

  let newMessages ='';
  let file //initialize helper variables
  let newData = JSON.parse(data);
 
  newData.reverse();
  for (let eachConvo of newData) {
  let orientation = '2%' ;
  let hide = 'none';
    //check if an messageObject is empty
    if (eachConvo == '') {
      continue;
    }

    if (eachConvo.Chat == '') {
      classesForMessageDiv = '';
    } else {
      classesForMessageDiv = 'w3-padding w3-bar-item w3-panel w3-border w3-round-xxlarge w3-indigo w3-reduce';
    }

    if (eachConvo.RefChat == '') {
      classesForReplyMessageDiv = '';
    } else {
      classesForReplyMessageDiv = 'w3-padding w3-border w3-round-xxlarge w3-indigo w3-opacity w3-reduce';
    }

    //determine file media data type....
    if (eachConvo.fileType == '') {
      file = '';
      classesForFileDiv='';
    } else if (eachConvo.fileType == 'image') {
      file = `<img style="margin:auto" width="100%" src='../${eachConvo.fileName}' alt='Image not found' >`;
    } else if (eachConvo.fileType == 'video') {
      file = `
      <video width="320" height="240" controls>
      <source src="../${eachConvo.fileName}" type="video/mp4">
      <source src="../${eachConvo.fileName}" type="video/ogg">
      Your browser does not support the video tag.
      </video>

      `;
    } else if (eachConvo.fileType == "audio") {
      file =
      `
      <audio style="width:95%" controls autoplay="true">
      <source src="../${eachConvo.fileName}" type="audio/wav" />
      <source src="../${eachConvo.fileName}" type="audio/ogg" />
      <source src="../${eachConvo.fileName}" type="audio/mp3" />
      <source src="../${eachConvo.fileName}" type="audio/mpeg" />
      </audio>
      `;
    }
    
    if(eachConvo.Sender == qs['sender']){
       orientation = '38%';
       hide ='';
    }
    
    newMessages +=
    `
    <div style="width:60%;margin-left:${orientation}" class="w3-padding-small w3-light-grey w3-round-xxlarge w3-bar-block"> 
    <div style="width:80%;margin:auto" class="w3-opacity w3-round-small"> 
    ${eachConvo.RefChat}
    </div>
    <div style="width:100%" class="w3-padding-small" id="${eachConvo.ChatID}"> 
    ${eachConvo.Chat}
    <br>
    ${file}
    </div>
    
    <span class="w3-padding" onclick="populateTaggedMessageDiv('${eachConvo.ChatID}')">
    <i class="fa fa-mail-reply"></i>
    </span>
    <span style="display:${hide}" class="w3-padding" onclick="deleteMessage('${eachConvo.ChatID}')">
    <i class="fa fa-times"></i>
    </span>
    </div>
    <br>
    `
  }
  a('conversation').innerHTML = "<br>"+newMessages;
}
