//When a user visits a recipient to chat with them...
//an array is created.The array consist of [sender,receiver,recipientLink,channel_type]
//this is taken to the backend and saved in a column called chatHistory
//the column in the backend is structured differently....
//localStorage.clear();
setTimeout(async function() {
  /*
  if (getCookie(qs["receiver"]) != '') {
    return true;
  }*/
  //next is to check if the receiver has actually blocked the sender before
  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend2.php',
      type: 'GET',
      async: true,
      data: {
        sender: function() {
          return qs['sender'];
        },
        receiver: function() {
          return qs['receiver'];
        },
        link: function() {
          return window.location.href;
        },
        channel_type: function() {
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
      if (data != 'already saved') {
        data = JSON.parse(data);
        let chatHistory = localStorage.getItem("chatHistory");

        if (!chatHistory) {
          alert('undefined')
          localStorage.setItem("chatHistory", JSON.stringify([]));
          chatHistory = JSON.parse(localStorage.getItem("chatHistory"));
        } else {
          alert('not null')
          chatHistory = JSON.parse(chatHistory);

        }
  
        chatHistory.push(data);//we add the newly opened chat to the chatHistory for better ux
        localStorage.setItem("chatHistory", JSON.stringify(chatHistory));

      } else {
        // checkIfGroupMember();
      }
    })
  });
}, 500)