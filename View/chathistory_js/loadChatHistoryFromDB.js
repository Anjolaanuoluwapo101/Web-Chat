
async function downloadChatHistoryFromDB() {
  return new Promise((resolve,
    reject) => {
    $.ajax({
      url: '../linkFrontendToBackend2.php',
      type: 'GET',
      async: true,
      data: {
        sender: function() {
          return qs['sender'];
        },
        getChatHistory : true
      },
      success: function (dt) {
        var data = resolve(dt);
      },
      error: function (error) {
        reject(error)
      },
    }).done((data)=>{
      //localStorage.clear();//wipes previous data without wiping other localstorage variables
      localStorage.setItem("chatHistory",data);
      chatHistory = JSON.parse(data);
    });
  });
}


async function updateChatHistory(updatedChatHistory) {
  return new Promise((resolve,
    reject) => {
    $.ajax({
      url: '../linkFrontendToBackend2.php',
      type: 'POST',
      async: true,
      data: {
        sender: function() {
          return qs['sender'];
        },
        updatedChatHistory: function() {
          return updatedChatHistory;
        }
      },
      success: function (dt) {
        var data = resolve(dt);
      },
      error: function (error) {
        reject(error)
      }
    })
  })
}

