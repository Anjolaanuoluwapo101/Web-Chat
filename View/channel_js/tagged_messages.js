//this script deals the anything tagged messages...

function loadTaggedMessages() {

  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'GET',
      async: true,
      data: {
        taggedMessages: "", //this is not needed but need it to distinguish between if blocks at the backend
        sender: function() {
          return qs['sender'];
        },
        receiver: function() {
          return qs['receiver'];
        }
      },
      success: function (dt) {
        var data = resolve(dt);
      },
      error: function (error) {
        reject(error)
      },
    }).done((data)=> {
      if (data) {
        alert(data);
        alert("Tagged Messages loaded");
        a('notif').innerHTML = data;
        // history.go(-1);
      } else {
        // checkIfGroupMember();
      }
    })
  });
}