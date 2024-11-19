import { QueueNotification } from "./Modules/Queueing_Notification.js";

function ChangeStatus(
  TxtColor = "052c65",
  TypeText = "None",
  Duration = 6000,
  Pause = 3500,
  size = 24
) {
  var NewTypeFormat = `https://readme-typing-svg.demolab.com?font=Poppins&weight=800&size=${size}&duration=${Duration}&pause=${Pause}&color=${TxtColor}&center=true&vCenter=true&random=true&width=225&height=25&lines=${TypeText}`;
  $("#typing").attr("src", NewTypeFormat);
}

$(document).ready(function () {
  $("#loader").addClass("d-none");
  $("#main-content").removeClass("d-none");

  ChangeStatus("052c65", "PENDING", 6000, 3500, 24);

  $("#perBirthdate").on("change", function () {
    var birthDate = $("#perBirthdate").val();
    var today = new Date();
    var birthDate = new Date(birthDate);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    if (age < 18) {
      QueueNotification(["error", "You must be 18 years old and above.", 4000, "top-end"]);
      $("#perBirthdate").val("");
    } else if (age < 0) {
      QueueNotification(["error", "Invalid birthdate.", 4000, "top-end"]);
      $("#perBirthdate").val("");
    } else {
      $("#perAge").val(age);
    }
  });

  //dont allow to input letters in perStudentno
  $("#perStudentno").on("keypress", function (e) {
    if (e.which < 48 || e.which > 57) {
      e.preventDefault();
    }
  });

  $.ajax({
    url: "../Functions/api/getAcadData.php",
    type: "POST",
    data: {
      CourseID: null,
      Action: "Get-Course",
    },

    success: function (res) {
      if (res.status === "success") {
        $("#perCourse").empty();
        $("#perCourse").append(`<option selected hidden>Choose...</option>`);
        res.data.forEach((courses) => {
          $("#perCourse").append(
            `<option value="${courses.CourseID}">${courses.CourseName}</option>`
          );
        });
      } else {
        console.error(
          "Failed to fetch courses:",
          res.message || "Unknown error"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      QueueNotification(["error", "Failed to fetch courses.", 4000, "top-end"]);
    },
  });

  $("#perCourse").on("change", function () {
    var course = $("#perCourse").val();
    $.ajax({
      url: "../Functions/api/getAcadData.php",
      type: "POST",
      data: {
        CourseID: course,
        Action: "Get-Year",
      },

      success: function (res) {
        if (res.status === "success") {
          $("#perYear").empty();
          $("#perYear").append(`<option selected hidden>Choose...</option>`);
          res.data.forEach((year) => {
            $("#perYear").append(
              `<option value="${year.Year}">${year.CourseName}</option>`
            );
          });
        } else {
          console.error(
            "Failed to fetch years:",
            res.message || "Unknown error"
          );
          QueueNotification(["error", "Failed to fetch years lvl.", 4000, "top-end"]);
        }
      }
    });
  });

  $("#perYear").on("change", function () {
    var course = $("#perCourse").val();
    var year = $("#perYear").val();
    $.ajax({
      url: "../Functions/api/getAcadData.php",
      type: "POST",
      data: {
        CourseID: course,
        YearLevel: year,
        Action: "Get-Section",
      },

      success: function (res) {
        if (res.status === "success") {
          $("#perSection").empty();
          $("#perSection").append(`<option selected hidden>Choose...</option>`);
          res.data.forEach((section) => {
            $("#perSection").append(
              `<option value="${section.code}">${section.Section}</option>`
            );
          });
        } else {
          console.error(
            "Failed to fetch sections:",
            res.message || "Unknown error"
          );
          QueueNotification(["error", "Failed to fetch sections.", 4000, "top-end"]);
        }
      }
    });
  });

  $("#perSection").on("change", function () {
    $('#CourseCode').val($("#perSection").val());
  });



  
  $("#submitForm").click(function () {
    var perFname = $("#perFname").val();
    var perLname = $("#perLname").val();
    var perEmail = $("#perEmail").val();
    var perCourse = $("#CourseCode").val();
    var perStudentno = $("#perStudentno").val();
    var perContact = $("#perContact").val();
    var perAge= $("#perAge").val();
    var perOrg = $("#perOrg").val();
    var perPosition = $("#perPosition").val();
    var perUUID = $("#perUUID").val();
    var perPassword = $("#perpassword").val();

    if (
      perFname === "" ||
      perLname === "" ||
      perEmail === "" ||
      perCourse === "" ||
      perStudentno === "" ||
      perContact === "" ||
      perAge === "" ||
      perOrg === null ||
      perPosition === "" ||
      perUUID === "" ||
      perPassword === ""
    ) {
      QueueNotification(["error", "Please fill out all fields.", 4000, "top-end"]);
      return;
    }

    if (perContact.length != 11 || isNaN(perContact)) {
      QueueNotification(["error", "Invalid contact number.", 4000, "top-end"]);
      return;
    }

    if (perContact.charAt(0) != "0") {
      QueueNotification(["error", "Please input a valid contact number.", 4000, "top-end"]);
      return;
    }

    if (perStudentno.length != 9 || isNaN(perStudentno)) {
      QueueNotification(["error", "Invalid student number.", 4000, "top-end"]);
      return;
    }

    let emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    let validDomain = "@cvsu.edu.ph";
    if (!emailPattern.test(perEmail)) {
      QueueNotification(["error", "Invalid email address.", 4000, "top-end"]);
      return;
    }

    if (!perEmail.includes(validDomain)) {
      QueueNotification(["error", "CVSU email address is required.", 4000, "top-end"]);
      return;
    }

    if (perOrg === null) {
      QueueNotification(["error", "Please select your organization.", 4000, "top-end"]);
      return;
    }

    if (perPosition === "") {
      QueueNotification(["error", "Please input your position.", 4000, "top-end"]);
      return;
    }

    if (perCourse === "") {
      QueueNotification(["error", "Please select your course.", 4000, "top-end"]);
      return;
    }

    if (perAge < 18) {
      QueueNotification(["error", "You must be 18 years old and above.", 4000, "top-end"]);
      return;
    }

    if (perPassword.length < 8) {
      QueueNotification(["error", "Password must be at least 8 characters.", 4000, "top-end"]);
      return;
    }

    if (perPassword.length > 20) {
      QueueNotification(["error", "Password must not exceed 20 characters.", 4000, "top-end"]);
      return;
    }

    if (perPassword.includes(" ")) {
      QueueNotification(["error", "Password must not contain spaces.", 4000, "top-end"]);
      return;
    }

    if (perPassword.search(/[a-z]/) < 0) {
      QueueNotification(["error", "Password must contain at least one lowercase letter.", 4000, "top-end"]);
      return;
    }

    if (perPassword.search(/[A-Z]/) < 0) {
      QueueNotification(["error", "Password must contain at least one uppercase letter.", 4000, "top-end"]);
      return;
    }

    if (perPassword.search(/[0-9]/) < 0) {
      QueueNotification(["error", "Password must contain at least one number.", 4000, "top-end"]);
      return;
    }

    if (perPassword.search(/[!@#$%^&*]/) < 0) {
      QueueNotification(["error", "Password must contain at least one special character.", 4000, "top-end"]);
      return;
    }

    var data = {
      perFname: perFname,
      perLname: perLname,
      perEmail: perEmail,
      perCourse: perCourse,
      perStudentno: perStudentno,
      perContact: perContact,
      perAge: perAge,
      perBirthdate: $("#perBirthdate").val(),
      perOrg: perOrg,
      perPosition: perPosition,
      perUUID: perUUID,
      perPassword: perPassword,
    };

    $.ajax({
      url: ".../../../Functions/api/postupdateAccount.php",
      type: "POST",
      data: data,
      success: function (data) {
        if (data.stat == "success") {
          $("#alert").removeClass("alert-primary alert-danger").addClass("alert-success");
          ChangeStatus("4caf50", "SUBMITTED", 6000, 3500, 24);
          QueueNotification(["success", data.message, 4000, "top-end"]);
          setTimeout(() => {
            window.location.href = "./Accesspage.php?pending=logout";
          }, 10000);
        } else {
          QueueNotification(["error", data.message, 4000, "top-end"]);
        }
      },

      error: function (xhr, status, error) {
        QueueNotification(["error", "An error occured. Please try again later.", 4000, "top-end"]);
      }
    });

    


    






  });
  setInterval(() => {
    $.ajax({
      url: "../../Src/Functions/api/getAccountStatus.php",
      type: "POST",
      data: { action: "Accstat" },
      success: function (data) {
        if (data.status == "success") {
          if (data.accountStat == "pending") {
            $("#alert")
              .removeClass("alert-success alert-danger")
              .addClass("alert-primary");
            ChangeStatus("052c65", "PENDING", 6000, 3500, 24);
          } else if (data.accountStat == "active") {
            $("#alert")
              .removeClass("alert-primary alert-danger")
              .addClass("alert-success");
            ChangeStatus("4caf50", "APPROVED", 6000, 3500, 24);
            setTimeout(() => {
              window.location.href = "../../Src/Pages/Feed.php";
            }, 4000);
          } else if (data.accountStat == "rejected") {
            $("#alert")
              .removeClass("alert-primary alert-success")
              .addClass("alert-danger");
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
