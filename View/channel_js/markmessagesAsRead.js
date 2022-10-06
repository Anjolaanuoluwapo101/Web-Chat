setTimeout(
  function () {
    $.ajax({
      url: '../linkFrontendToBackend.php',
      type: 'POST',
      async: true,
      data: {
        sender: function() {
          return qs['sender'];
        },
        receiver: function() {
          return qs['receiver'];
        },
        channel_type: function() {
          return qs['channel_type'];
        },
        markUnreadAsRead: function() {
          return '';
        },

      },
      success: function (dt) {},
      error: function (error) {},
    });
  }, 1000);