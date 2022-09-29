//this script helps remember that a public channel has been visited

setTimeout(async function() {
  
  
  if (getCookie(qs["receiver"]) != '') {
    return true;
  }
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
        //pls
      },
      error: function (error) {
        reject(error)
      },
    }).done((data)=> {
      if (data !== 'already saved') {
        data = JSON.parse(data);
        let chatHistory = localStorage.getItem("chatHistory");

        if (typeof chatHistory == undefined) {
          localStorage.setItem("chatHistory", JSON.stringify([]));
          chatHistory = JSON.parse(localStorage.getItem("chatHistory"));
        } else {
          chatHistory = JSON.parse(chatHistory);

        }
        alert(data[1]);
  
        setCookie(data[1],'true',50);
        chatHistory[data[1]] = data;
        localStorage.setItem("chatHistory", JSON.stringify(chatHistory));


      } else {
        // checkIfGroupMember();
      }
    })
  });
}, 500)