$(function () {
  $('#createGCForm').on('submit', function (e) {
    e.preventDefault();

    a('senderAdmin').value = qs['sender'];
         
    $.ajax({
      url: '../linkFrontendToBackend2.php',
      type: 'POST',
      data: new FormData(this),
      contentType: false,
      processData: false,
      success: function (data) {
        var group_name = a('group_name').value;
        var link = `http://twilightmessage.000webhostapp.com/Chat/View/channel.php?sender=${qs['sender']}&receiver=${group_name}&channel_type=public`;
        alert('Group Chat Created');
        window.location.href = link;
      }
    });

  });

});


//allows for copying for link...this is used in both 
var copyTextareaBtn = document.querySelector('.js-textareacopybtn');

copyTextareaBtn.addEventListener('click', function(event) {
  var copyTextarea = document.querySelector('.js-copytextarea');
  copyTextarea.focus();
  copyTextarea.select();

  try {
    var successful = document.execCommand('copy');
    let msg = successful ? 'successful' : 'unsuccessful';
    if(msg == 'successful'){
      alert('Link has been copied to clipboard!');
    }else{
      alert('Link could not be copied \n Please try copying manually')
    }
  } catch (err) {
      alert('Link could not be copied \n Please try copying manually')
  }
});

//reaponsible for displaying group creation form
  function showGroupCreator(){
      if(a('createGroup').style.display != 'none'){
        a('createGroup').style.display = 'none';
      }else{
        a('createGroup').style.display = 'block';
      }
    }
   
    function a(id){
        setInterval(function(){
          b('file');
        },2000)
        return document.getElementById(id);
      }
      
      function b(id) {
        if(document.getElementById(id).files[0] != undefined){
          a('display').innerHTML = 'File Chosen!';
        }
      }