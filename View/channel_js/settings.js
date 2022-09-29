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

