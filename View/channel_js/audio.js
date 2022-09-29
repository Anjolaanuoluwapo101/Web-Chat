let mediaRecorder,audioBlob;//initialize local variables
let check=0,audioChunks=[];

//this grabs the query string as allows the value to the accessible to the other js scripts
var qs = (function(a) {
    if (a == "") return {};
    var b = {};
    for (var i = 0; i < a.length; ++i)
    {
        var p=a[i].split('=', 2);
        if (p.length == 1)
            b[p[0]] = "";
        else
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
    }
    return b;
})(window.location.search.substr(1).split('&'));

//now qs becomes an array 

function a(id){
  return document.getElementById(id);
}

function recordAudio(){
  check =1;
  navigator.mediaDevices.getUserMedia({
    audio: true
  })
  .then(stream => {
    mediaRecorder = new MediaRecorder(stream);
    mediaRecorder.start();


    mediaRecorder.addEventListener("dataavailable", event => {
      audioChunks.push(event.data);
    });


    //this fires when audio is recording is stopped and audio is converted to blob
    mediaRecorder.addEventListener("stop", () => {
      alert('stopped')
      audioBlob = new Blob(audioChunks);
      uploadAudio(audioBlob)

      /*const audioUrl = URL.createObjectURL(audioBlob);
      const audio = new Audio(audioUrl);
      audio.play();*/
    });
    
   // setTimeout(function() {mediaRecorder.stop()}, 2000);

  });
}

//responsible for uoloading audio message to backend
function uploadAudio( blob ) {
  var reader = new FileReader();
  reader.onload = function(){
    var fd = {};
    fd.id=generateIDForNewMessage();
    fd.fname = String(fd.id)+".wav";
    fd.sender=qs['sender'];
    fd.receiver=qs['receiver'];
    fd.data = reader.result;
    fd.repliedMessage = a('repliedMessageDiv').innerHTML;
    fd.type = 'audio';
    $.ajax({
      url:'../linkFrontendToBackend.php',
      type: 'POST',
      data: fd,
      dataType: 'text',
      success : function(d){
      }
    }).done(function(d) {
      check=0;//resets check
      audioChunks=[];//clears the just recorded data from the array
    });
  };
  reader.readAsDataURL(blob);
}


function determine(){
  if(check != 1){
    recordAudio();
  }else if(check==1){
    mediaRecorder.stop()
  }
}



/*
This snippet shows that,when a function containing an event listener is called,
the event listener keeps on listening for events even if the function block has executed finish.

*/