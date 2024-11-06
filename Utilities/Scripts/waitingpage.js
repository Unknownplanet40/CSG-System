import { QueueNotification } from "./Modules/Queueing_Notification.js";

function ChangeStatus(TxtColor = "052c65", TypeText = "None", Duration = 6000, Pause = 3500, size = 24) {
  var NewTypeFormat = `https://readme-typing-svg.demolab.com?font=Poppins&weight=800&size=${size}&duration=${Duration}&pause=${Pause}&color=${TxtColor}&center=true&vCenter=true&random=true&width=225&height=25&lines=${TypeText}`
  $("#typing").attr("src", NewTypeFormat);
}

$(document).ready(function () {
  $("#loader").addClass("d-none");
  $("#main-content").removeClass("d-none");

  ChangeStatus("052c65", "PENDING", 6000, 3500, 24);

  $("#btn-cancel").on("click", function () {
    swal
      .fire({
        title: "Are you sure?",
        text: "You will lose all the data you have entered. Do you want to continue?",
        icon: "warning",
        confirmButtonText: "Yes I'm sure",
        allowOutsideClick: false,
        confirmButtonColor: "#d33",
        customClass: {
          popup: "alert-popup-inform",
          confirmButton: "btn btn-sm btn-danger alert-btn",
          container: "alert-container",
          htmlContainer: "alert-html-container",
          title: "alert-title",
        },
      })
      .then((result) => {
        if (result.isConfirmed) {
          QueueNotification([
            "success",
            "You have successfully canceled the process.",
            3000,
          ]);
          setTimeout(() => {
            window.location.href = "../../Src/Pages/Accesspage.php";
          }, 3800);
        }
      });
  });

  $("#btn-refreshing").on("click", function () {
    window.location.reload();
    console.log("Page Refreshed");
  });

  $("#btn-logout").on("click", function () {
      Swal.mixin({
        toast: true,
        position: "center",
        showConfirmButton: true,
        confirmButtonText: "Logout",
        confirmButtonColor: "#d33",
        showCancelButton: true,
        cancelButtonText: "Cancel",
        customClass: {
          popup: "colored-toast",
          timerProgressBar: "colored-progress-bar",
        },
        timer: 10000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener("mouseenter", Swal.stopTimer);
          toast.addEventListener("mouseleave", Swal.resumeTimer);
        },

        preConfirm: () => {
          window.location.href = "../../Src/Functions/api/UserLogout.php";
        },
      }).fire({
        icon: "warning",
        title: "Are you sure you want to logout?",
      });
    });

  

  setInterval(() => {
    $.ajax({
      url: "../../Src/Functions/api/getAccountStatus.php",
      type: "POST",
      data: { action: "Accstat" },
      success: function (data) {
        if (data.status == "success"){
          if (data.accountStat == "pending") {
            $("#alert").removeClass("alert-success alert-danger").addClass("alert-primary");
            ChangeStatus("052c65", "PENDING", 6000, 3500, 24);
          } else if (data.accountStat == "active") {
            $("#alert").removeClass("alert-primary alert-danger").addClass("alert-success");
            ChangeStatus("4caf50", "APPROVED", 6000, 3500, 24);
            setTimeout(() => {
              window.location.href = "../../Src/Pages/Feed.php";
            }, 4000);
          } else if (data.accountStat == "rejected") {
            $("#alert").removeClass("alert-primary alert-success").addClass("alert-danger");
            ChangeStatus("f44336", "REJECTED", 6000, 3500, 24);
            setTimeout(() => {
              window.location.href = "../../Src/Functions/api/UserLogout.php";
            }, 4000);
          }
        } else {
          console.log("Error: " + data.message);
        }
      },
    });
  }, 10000);
});
