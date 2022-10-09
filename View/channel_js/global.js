function setCookie(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  let expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}



//document.getElementById makes the code look ugly so we pass it as a return value from this function
function a(id) {
  return document.getElementById(id);
}

//this function generates an ID for a message being sent to backend
function generateIDForNewMessage() {
  let suffix = String(Date.now());
  suffix = suffix.slice(-5, -1);
  let prefix = Math.floor(Math.random() * 10000);
  let ID = prefix+suffix;
  return ID;
}

//this grabs the query string as allows the value to the accessible to the other js scripts
var qs = (function(a) {
  if (a == "") return {};
  var b = {};
  for (var i = 0; i < a.length; ++i) {
    var p = a[i].split('=', 2);
    if (p.length == 1)
      b[p[0]] = "";
    else
      b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
  }
  return b;
})(window.location.search.substr(1).split('&'));

//now qs becomes an array which is available to the rest of the scripts


//this immediately populates the form with the query values
a('receiver').value = qs['receiver'];
a('sender').value = qs['sender'];

//not in use
function time() {
  var timestamp = Math.floor(new Date().getTime() / 1000)
  return timestamp;
}

//not in use
function convertTimestampToTime(timestamp) {
  let unix_timestamp = timestamp;
  // Create a new JavaScript Date object based on the timestamp
  // multiplied by 1000 so that the argument is in milliseconds, not seconds.
  var date = new Date(unix_timestamp * 1000);
  // Hours part from the timestamp
  var hours = date.getHours();
  // Minutes part from the timestamp
  var minutes = "0" + date.getMinutes();
  // Seconds part from the timestamp
  var seconds = "0" + date.getSeconds();

  var formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
  return formattedTime;
}

 









