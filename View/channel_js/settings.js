$(function () {
  $('#settingsForm').on('submit', function (e) {
    e.preventDefault();
    if (a('blockee').value == '' || a('block_type') == '') {
      alert('Type in account name');
      return false;
    }
    
    
    a('blocker').value = qs['sender'];
    

    return new Promise((resolve, reject) => {
      $.ajax({
        async:true,
        url:"../linkFrontendToBackend.php",
        type: 'POST',
        data: new FormData(this),
        contentType: false,
        processData: false,
        success: function (dt) {
          var d = resolve(dt);
        }
      }).done(function(d) {
        alert(d);
      });
    });
  });
});

    
//populates the chat header 
  a('Header').innerHTML = qs['receiver'];

//displays and hide chat settings
function toggleSettings(){
  if(a('chatSettings').style.display == 'none'){
    a('chatSettings').style.display = 'block';
  }else{
    a('chatSettings').style.display = 'none';
  }
}

function showBlock(){
  a('blockDiv').style.display='block';
  a('chatSettings').style.display ='none';
}

//
if(qs['channel_type'] == 'public'){
  a('blockee').style.display = 'block';
  a('blocker').value = qs['sender'];
  a('channel').value = qs['receiver'];
}else{
  a('blocker').value = qs['sender'];
  a('blockee').value = qs['receiver'];
  a('channel').value = qs['sender'];
  a('Username').style.display = 'none';
  a('AdminWarning').style.display = 'none';
}

