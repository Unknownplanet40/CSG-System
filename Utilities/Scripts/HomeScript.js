const showModal = new bootstrap.Modal("#Sys_Permissions", {
  keyboard: false,
  backdrop: "static",
});

let isNotifWorking = false;
let isLocWorking = false;

function requestPermission(code) {
  if (code == 100) {
    if ("Notification" in window) {
      Notification.requestPermission().then(function (result) {
        if (result === "granted") {
          notifUI(result);
          requestPermission(200);
          setTimeout(function () {
            notify();
          }, 2000);
        } else {
          $("#notification_status").addClass("shake");
          setTimeout(function () {
            $("#notification_status").removeClass("shake");
          }, 1000);
        }
      });
    } else {
      console.log("Notification is not supported");
    }
  } else if (code == 200) {
    if ("geolocation" in navigator) {
      navigator.geolocation.getCurrentPosition(
        function (position) {
          locUI("granted");

          if (Notification.permission === "granted") {
            var notification = new Notification("Location Permission Granted", {
              body: "You have granted the location permission",
            });
            showModal.hide();
          }

          if (
            position.coords.latitude != null &&
            position.coords.longitude != null
          ) {
            console.log("Latitude: " + position.coords.latitude);
            console.log("Longitude: " + position.coords.longitude);
          }

          // ip address
          $.getJSON("https://api.ipify.org?format=json", function (data) {
            console.log("IP Address: " + data.ip);
          });

          if (position.coords.accuracy != null) {
            console.log("Accuracy: " + position.coords.accuracy);
          }
        },
        function (error) {
          if (error.code == error.PERMISSION_DENIED) {
            $("#location_status").addClass("shake");
            setTimeout(function () {
              $("#location_status").removeClass("shake");
            }, 1000);
          } else {
            console.log("Error occurred. Error code: " + error.code);
          }
        }
      );
    } else {
      console.log("Geolocation is not supported");
    }
  }
}

function notify() {
  var notification = new Notification("Permission Granted", {
    body: "You have granted the permission",
  });
}

function notifUI(status) {
  if (status == "granted") {
    $("#notification_status").addClass("text-bg-success");
    $("#notification_status").removeClass("text-bg-danger");
    $("#notification_active").removeClass("d-none");
    $("#notification_inactive").addClass("d-none");
  } else {
    $("#notification_status").addClass("text-bg-danger");
    $("#notification_status").removeClass("text-bg-success");
    $("#notification_active").addClass("d-none");
    $("#notification_inactive").removeClass("d-none");
  }
}

function locUI(status) {
  if (status == "granted") {
    $("#location_status").addClass("text-bg-success");
    $("#location_status").removeClass("text-bg-danger");
    $("#location_active").removeClass("d-none");
    $("#location_inactive").addClass("d-none");
  } else {
    $("#location_status").addClass("text-bg-danger");
    $("#location_status").removeClass("text-bg-success");
    $("#location_active").addClass("d-none");
    $("#location_inactive").removeClass("d-none");
  }
}

/* if ("serviceWorker" in navigator) {
  navigator.serviceWorker.register("../../Utilities/Scripts/service-worker.js")
    .then(function (registration) {
      console.log("Service Worker Registered");
    })
    .catch(function (error) {
      console.log("Service Worker Registration Failed: ", error);
    });
} */

$(document).ready(function () {
  
});
