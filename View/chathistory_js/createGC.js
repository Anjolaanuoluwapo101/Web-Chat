$(function () {
  $('#createGCForm').on('submit', function (e) {
    alert('brooooo')
    e.preventDefault();

    a('senderAdmin').value = qs['sender'];
         
    $.ajax({
      url: '../linkFrontendToBackend2.php',
      type: 'POST',
      data: new FormData(this),
      contentType: false,
      processData: false,
      success: function (data) {
       alert('blah')
      }
    });

  });

});
