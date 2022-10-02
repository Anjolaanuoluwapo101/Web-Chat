
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
      localStorage.clear();
      
      localStorage.setItem("chatHistory",data);
    });
  });
}

/*
setInterval(function(){
  updateChatHistoryFromDB();
},8000)
*/