let updateVar = '';

/*
Logic behind this page is straighforward.


*/

if (localStorage.getItem("chatHistory") != undefined) {
  
  downloadChatHistoryFromDB();
  
  var chatHistory = JSON.parse(localStorage.getItem("chatHistory"));
  
  loadSavedChatHistoryFromStorage();
  
} else {
  
  downloadChatHistoryFromDB();
  
  alert("You have not chatted anyone yet \n Please send this link to your friends");

}



function loadSavedChatHistoryFromStorage() {

  for (let chat in chatHistory) {
    let x = chatHistory[chat];

    updateVar +=
    `
    <div style="width:100%;height:70px;border:1px solid silver";border-top:0px white; class="w3-bar">
    <div style="width:15%" class="w3-bar-item">
    <img src="" class="w3-circle" style="width:100%;height:100%">
    </div>
    <div style="width:60%" class="w3-bar-item"> </div>
    <div style="width:25%" class="w3-bar-item">
    <div class="w3-bar-block">
    <div class="receivers">${x[1]+'|||'+ x[3]}</div><!--||| serves as a delimiter because the backend will split the string into two amd use them both to query the database-->
    <div class="w3-bar-item" id="${x[1]+'|||' +x[3]+'_last_Message_Time'}"></div>
    </div>
    </div>
    </div>
    `

  }
  a('chatHistory').innerHTML = updateVar;


}


var markers = document.getElementsByClassName('receivers');
var dataArray = [];
for (let marker in markers) {
  if (markers.hasOwnProperty(marker)) {
    dataArray.push(markers[marker].innerHTML);
    //this creates an array basically that would be sent to the backend
    
  }
}
    retrieveUnreadMessagesCountAndLastMessageTime(JSON.stringify(dataArray));


//this function takes  an array of arrays
//the array has a structure of such as [[recipientName1|||private/pubic],[recipientName2|||private/public]]
//this array is stringified an carried to the backend
//it outputs an array of arrays which each array having two elements..
//the first being the number of unread messages for each chat
//the second being the last message time
//we the remove the last two elements of each array in chatHistory stored on localstorage and append the new two elements..

//we would need to also update the chatHistory column in the databse to signal this change

//this function allows us update the saved chathistory..
async function retrieveUnreadMessagesCountAndLastMessageTime(stringifiedArray) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend2.php',
      type: 'GET',
      async: true,
      data: {
        sender: function() {
          return qs['sender'];
        },
        array: function() {
          return stringifiedArray;
        }
      },
      success: function (dt) {
        var data = resolve(dt);
      },
      error: function (error) {
        reject(error)
      },
    }).done((data)=> {
      if (data == '') {
        return false;
      }

      let newChatHistory = [];
      let dataArray = JSON.parse(data);
      let dataArraylength = dataArray.length;
      for (let a = 0; a < dataArraylength; a++) {
        if (localStorage.getItem('chatHistory') != undefined) {
          var chatHistory = JSON.parse(localStorage.getItem('chatHistory'));
          let x = chatHistory[a];

          x.splice(5, 2)//we remove the last two elements from the 5th positon..
          //remember that at the backend... if a message is sent to a channel...the last two elements will be altered...
          //we would need to alter those two elemets here! ..so we sploce them from the array first
          newChatHistory.push(x.concat(dataArray[a]));
        }
      }
      //now we need to sort newChatHistory based on last message time value of each child array...
      newChatHistory = newChatHistory.sort(Comparator);
      
      //then the locslstorage is overwritten with this new data
      localStorage.setItem("chatHistory",JSON.stringify(newChatHistory));
      
      //then the chatHistory of the db is also updated
      updateTrigger(JSON.stringify(newChatHistory));
      
      
    })
  })
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
      },
    })
  })
}


function Comparator(a, b) {
  if (a[6] < b[6]) return 1;
  if (a[6] > b[6]) return -1;
  return 0;
}

async function updateTrigger(arr) {
  await updateChatHistory(arr);
}


