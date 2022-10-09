$(function () {
  $('#meetDiv').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
      url: '../linkFrontendToBackend2.php',
      type: 'POST',
      data: new FormData(this),
      contentType: false,
      processData: false,
      success: function (data) {
        if (data == "true") {
          var referer = a('referer').value;
          var referee = a('referee').value;
          var channel_type = a('channel_type').value;
          var link = `https://twilightmessage.000webhostapp.com/Chat/View/channel.php?sender=${referee}&receiver=${referer}&channel_type=${channel_type}`;
          //document.getElementById('meetDiv').innerHTML = link;
          window.location.assign(link);
        } else {
          alert('You have no account registered under this name.');
          if (confirm("Would you like to register an account? \n Takes less than 15seconds!")) {
           // window.location.assign('../Sign_up.php');
           a('meetDiv').innerHTML += "<a href='../Sign_up.php' target ='_blank' id='justCreated' > </a>";
           a('justCreated').click();
          }else{
            alert('You need a valid account.');
          }
        }
        }
    }).done((data)=>{
    });




  });

});