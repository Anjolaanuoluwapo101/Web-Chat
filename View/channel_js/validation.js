//Validation functions...this function carries out necessary tasks before loading a chat history
//it runs before automaticallyloadrecipient.js can carry out it own ajax requests...
setTimeout(async function() {
  //we chcek if the link has been tampered with
  //if it has been..this if block immediately escapes the page.
    if(qs.length < 3){
      alert("This link has been tampered with");
      history.back();
    }else if(qs['receiver'] == qs['sender']){
      alert("This link has been tampered with");
      history.back();
    }else if((qs['receiver'] == '') || (qs['sender'] == '') || (qs['channel_type'] == '') ){
      alert("This link has been tampered with");
      history.back();
    }

    a('conversation').innerHTML = 
    `
    <p style="width:20%;position:fixed;top:40%;left:40%;">
    <i class="fa fa-spinner w3-spin" style="font-size:64px"></i>
    <br>
    <br>
    <span class="w3-small">LOADING...</span>
    </p>
    `

  //next is to check if the receiver has actually blocked the sender before
  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'POST',
       async:true,
      data: {
        type: "", //this variable is not needed at the backend but helps distinguish between if blocks at the linkFrontendToBackend page
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
      if (data) {
        alert("This contact blocked you!!!");
        history.go(-1);
      } else {
       if(qs['channel_type'] == 'public' ){
        checkIfGroupMember();
       } 
      
      }
    })
  });
}, 500)



function checkIfGroupMember() {
  if (qs['channel_type'] == 'public') {
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'GET',
       async:true,
      data: {
        intruder: function() {
          return qs['sender'];
        },
        group_name: function() {
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
      if (data) {
        alert("You're not an associate of this channel");
        alert("Normally you would be kicked out of the GC but since ")
       // history.go(-1);
      } else {
      }
    })
  }
}