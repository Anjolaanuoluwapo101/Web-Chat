var updateVar = '';
var lastUpdate = '';
var chatHistory = ''; //this js  global variable that is accessed and modified by the functions here alone nd loadChatHistoryFromDB
var unopened_chats = 0; //takes note of chat that hasnt been opened with unread messages
/*
Logic behind this page is straighforward.
..to be explained
...it is a constant loop

*/
load();
setInterval(async function() {
  await load();
}, 7000)

async function load() {
  if (localStorage.getItem("chatHistory") != undefined) {
    //first we initiate the download of the chathistory from the database
    //please note that this chat history isn't generated.It is already stored on the db(if one exists).This function just fetches it
    await downloadChatHistoryFromDB();

    //we parse it..since it comes in a string ..parsed into aray of arrays
    chatHistory = JSON.parse(localStorage.getItem("chatHistory"));
    //
    await displayLoadedChatHistoryFromDB();

  } else {
    await downloadChatHistoryFromDB();
    //chatHistory = JSON.parse(localStorage.getItem('chatHistory'));

    if (chatHistory == '' || chatHistory == '[]') {
      alert("You have not chatted anyone yet \n Please send this link to your friends");
    } else {
      alert('Chat history backup loaded from database');

      displayLoadedChatHistoryFromDB();
    }
  }
}


async function displayLoadedChatHistoryFromDB() {
  for (let chat in chatHistory) {
    let x = chatHistory[chat];
    let unreadmessage,
    last_Message;
    if (x[5] != 0) {
      unreadmessage = `<span stye="font-size:6px!important" class="w3-tag w3-circle w3-indigo">${x[5]}</span>`;
      unopened_chats++;
    } else {
      unreadmessage = '';
    }
    let time = x[6];
    time = analyzeTime(time);
    if (time == null) {
      time = '---';
    }
    if (x[7]) {
      last_Message = x[7];
      if (last_Message.indexOf('<br>') != -1) {
        last_Message = last_Message.substring(last_Message.indexOf('<br>'));
      }
    } else {
      last_Message = '';
    }
    updateVar +=
    `
    <div onclick="a('${x[1]+'_link'}').click()"  style="width:100%;height:70px;border-bottom:1px solid whitesmoke"; class="w3-bar">
    <div style="width:25%;height:100%" class="w3-bar-item">
    <img src="../${x[4]}" class="w3-circle" style="width:100%;height:100%">
    </div>

    <div style="width:55%" class="w3-bar-item w3-bar-block ">
    <div class="w3-monospace" style="height:10px">${x[1]}</div>
    <div class="last_Message_Container w3-opacity" style="overflow-x:hidden;padding:0">${last_Message}</div>
    <a id="${x[1]+'_link'}" style="display:none" href="${x[2]}" target="_blank"></a>
    </div>

    <div style="width:20%" class="w3-center w3-bar-item">
    <div class="w3-bar-block">
    <div style="display:none"  class="receivers">${x[1]+'|||'+ x[3]}</div><!--||| serves as a delimiter because the backend will split the string into two amd use them both to query the database-->
    <div style="" class="w3-bar-item w3-padding-small" >${unreadmessage}</div>
    <div class="w3-opacity" style="padding-top:6px;font-size:6px!important;"  >${time}</div>
    </div>
    </div>
    </div>
    `
  }
  //update the GUi
  if (lastUpdate != updateVar && updateVar != '') {
    a('chatHistory').innerHTML = updateVar;
  }
  lastUpdate = updateVar;

  updateVar = '';
  a('unopenedchats').innerHTML = unopened_chats;

  //we grb the contents of an hidden div from each chatHistory data
  var markers = document.getElementsByClassName('receivers');
  var dataArray = [];
  for (let marker in markers) {
    if (markers.hasOwnProperty(marker)) {
      dataArray.push(markers[marker].innerHTML);
      //this creates an array basically that would be sent to the backend
    }
  }
  dataArray = JSON.stringify(dataArray);
  //this retrieves the number of unread messages and last message time
  await retrieveUnreadMessagesCountAndLastMessageTime(dataArray);
  //await getLastMessage(dataArray);

}




//this function takes  an array of arrays
//the array has a structure of such as [[recipientName1|||private/pubic],[recipientName2|||private/public]]
//this array is stringified and carried to the backend
//it outputs an array of arrays which each array having two elements..
//the first being the number of unread messages for each chat
//the second being the last message sent by the recipient... timestamp
//we then remove the last two elements of each array in chatHistory stored on localstorage and append the new two elements..

//we would need to also update the chatHistory column in the databse to signal this change

//this function allows us update the saved chathistory..
async function retrieveUnreadMessagesCountAndLastMessageTime(stringifiedArray) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend2.php',
      type: 'GET',
      //  async: true,
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

          x.splice(4)//we remove the last two elements from the 5th positon..
          //remember that at the backend... if a message is sent to a channel...the last two elements will be altered...
          //we would need to alter those two elemets here! ..so we sploce them from the array first
          newChatHistory.push(x.concat(dataArray[a]));
        }
      }
      //now we need to sort newChatHistory based on last message time value of each child array...
      newChatHistory = newChatHistory.sort(Comparator);
      //then the localstorage is overwritten with this new data
      localStorage.setItem("chatHistory", JSON.stringify(newChatHistory));

      //then the chatHistory of the db is also updated with new data
      //updateTrigger(JSON.stringify(newChatHistory));
      updateChatHistory(JSON.stringify(newChatHistory));
      //we also reset this counter that takes note of chats that havent been opened but have unread messages
      unopened_chats = 0;
    })
  })
}


//not in use
async function getLastMessage(stringifiedArray) {
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
        __array: function() {
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
      data = JSON.parse(data);
      last_Message_Container_Divs = document.querySelectorAll('.last_Message_Container');
      for (var i = 0; i < last_Message_Container_Divs.length; i++) {
        //let last_Message_Container_Div =  last_Message_Container_Divs[i];
        //when a message from the front end is sent to the backend...the name of the person who sent is prepended to the messag..
        //we have to remove it here..it is sepersted from the actual content of the message by a br tag
        let lastMessage,
        keywordPos = data[i].indexOf('<br>')
        if (keywordPos != -1) {
          lastMessage = data[i].substring(keywordPos);
        } else {
          lastMessage = data[i];
        }
        last_Message_Container_Divs[i].innerHTML = lastMessage;
      }

    });
  })
}


function Comparator(a, b) {
  if (a[6] < b[6]) return 1;
  if (a[6] > b[6]) return -1;
  return 0;
}



function analyzeTime(timestamp) {
  if (timestamp == '') {
    return '';
  }
  let latest_timestamp = Math.floor(Date.now() / 1000);
  let resolved_timestamp = latest_timestamp - timestamp;
  if (resolved_timestamp < 60) {
    return 'now';
  } else if (resolved_timestamp > 60 && resolved_timestamp < 3600) {
    return Math.floor(resolved_timestamp/60)+' min(s) ago';
  } else if (resolved_timestamp > 3600 && resolved_timestamp < 86400) {
    return Math.floor(resolved_timestamp/3600)+' hr(s) ago';
  } else if (resolved_timestamp > 86400 && resolved_timestamp < 604800) {
    return Math.floor(resolved_timestamp/86400)+' day(s) ago';
  } else if (resolved_timestamp > 604800 && resolved_timestamp < 2419200) {
    return Math.floor(resolved_timestamp/604800)+' week(s) ago';
  } else if (resolved_timestamp > 2419200 && resolved_timestamp < 20930400) {
    return Math.floor(resolved_timestamp/2419200)+' month(s) ago';
  } else {
    return '';
    //return Math.floor(resolved_timestamp/20930400)+' yr(s) ago';
  }
}