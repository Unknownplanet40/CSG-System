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

const tooltipTriggerList = document.querySelectorAll(
  '[data-bs-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
  (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
);

$(document).ready(function () {
  $("#loader").addClass("d-none");
  $("#main-content").removeClass("d-none");

  setInterval(() => {
    $(".no-ann-box").html($(".no-ann-box").html());
  }, 15000);

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
      QueueNotification([
        "error",
        "You must be 18 years old and above.",
        4000,
        "top",
      ]);
      $("#perBirthdate").val("");
    } else if (age < 0) {
      QueueNotification(["error", "Invalid birthdate.", 4000, "top"]);
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

    if ($("#perStudentno").val().length >= 9) {
      e.preventDefault();
    }

    if (e.which == 32) {
      e.preventDefault();
    }

    if ($("#perStudentno").val().length >= 8) {
      var studentno = $("#perStudentno").val();
      $.ajax({
        url: "../Functions/api/checkStudentno.php",
        type: "POST",
        data: { studentno: studentno },
        beforeSend: function () {
          swal
            .mixin({
              toast: true,
              position: "top",
              showConfirmButton: false,
              didOpen: () => {
                swal.showLoading();
              },
            })
            .fire({
              icon: "info",
              title: "Checking student number...",
            });
        },

        success: function (data) {
          Swal.close();
          if (data.status == "success") {
            $("#perStudentno").removeClass("is-invalid");
            $("#perStudentno").addClass("is-valid");
            setTimeout(() => {
              $("#perStudentno").removeClass("is-valid");
            }, 3000);
          } else {
            QueueNotification(["error", data.message, 3000, "top"]);
            $("#perStudentno").removeClass("is-valid");
            $("#perStudentno").addClass("is-invalid");
            setTimeout(() => {
              $("#perStudentno").removeClass("is-invalid");
            }, 3000);
          }
        },

        error: function (error) {
          console.error("AJAX Error:", error);
          QueueNotification([
            "error",
            "An error occured. Please try again later.",
            4000,
            "top",
          ]);
        },
      });
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
        $("#perCourse").empty();
        $("#perCourse").append(
          `<option selected disabled>No courses available</option>`
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      QueueNotification(["error", "Failed to fetch courses.", 4000, "top"]);
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
          $("#perYear").empty();
          $("#perYear").append(
            `<option selected disable>No year levels available</option>`
          );
        }
      },
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
          $("#perSection").empty();
          $("#perSection").append(
            `<option selected disable>No sections available</option>`
          );
        }
      },
    });
  });

  $("#perSection").on("change", function () {
    $("#CourseCode").val($("#perSection").val());
  });

  $("#submitForm").click(function () {
    var perFname = $("#perFname").val();
    var perLname = $("#perLname").val();
    var perEmail = $("#perEmail").val();
    var perStudentno = $("#perStudentno").val();
    var perContact = $("#perContact").val();
    var perirreg = $("#perirreg").val();
    var perbirthDate = $("#perBirthdate").val();
    var perCourse =
      TemporaryRole == 1
        ? "NULL"
        : perirreg == 0
        ? $("#perCourse").val()
        : "NULL";
    var perAge = $("#perAge").val();
    var perOrg = TemporaryRole == 1 ? "NULL" : $("#perOrg").val();
    var perPosition = TemporaryRole == 1 ? "NULL" : $("#perPosition").val();
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
      QueueNotification(["error", "Please fill out all fields.", 4000, "top"]);

      // add is-invalid class to empty fields
      if (perFname === "") {
        $("#perFname").addClass("is-invalid");
      }
      if (perLname === "") {
        $("#perLname").addClass("is-invalid");
      }
      if (perEmail === "") {
        $("#perEmail").addClass("is-invalid");
      }
      if (perCourse === "") {
        $("#perCourse").addClass("is-invalid");
      }
      if (perStudentno === "") {
        $("#perStudentno").addClass("is-invalid");
      }
      if (perContact === "") {
        $("#perContact").addClass("is-invalid");
      }

      if (perAge === "") {
        $("#perAge").addClass("is-invalid");
      }

      if (perOrg === null) {
        $("#perOrg").addClass("is-invalid");
      }

      if (perPosition === "") {
        $("#perPosition").addClass("is-invalid");
      }

      if (perUUID === "") {
        $("#perUUID").addClass("is-invalid");
      }

      if (perPassword === "") {
        $("#perpassword").addClass("is-invalid");
      }

      setTimeout(() => {
        $(".is-invalid").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perContact.length != 11 || isNaN(perContact)) {
      QueueNotification(["error", "Invalid contact number.", 4000, "top"]);
      $("#perContact").addClass("is-invalid");
      setTimeout(() => {
        $("#perContact").removeClass("is-invalid");
      }, 5000);
      return;
    }

    if (perContact.charAt(0) != "0") {
      QueueNotification([
        "error",
        "Please input a valid contact number.",
        4000,
        "top",
      ]);
      $("#perContact").addClass("is-invalid");
      setTimeout(() => {
        $("#perContact").removeClass("is-invalid");
      }, 5000);
      return;
    }

    if (perStudentno.length != 9 || isNaN(perStudentno)) {
      QueueNotification(["error", "Invalid student number.", 4000, "top"]);
      $("#perStudentno").addClass("is-invalid");
      setTimeout(() => {
        $("#perStudentno").removeClass("is-invalid");
      }, 5000);
      return;
    }

    let emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    let validDomain = "@cvsu.edu.ph";
    if (!emailPattern.test(perEmail)) {
      QueueNotification(["error", "Invalid email address.", 4000, "top"]);
      $("#perEmail").addClass("is-invalid");
      setTimeout(() => {
        $("#perEmail").removeClass("is-invalid");
      }, 5000);
      return;
    }

    if (!perEmail.includes(validDomain)) {
      QueueNotification([
        "error",
        "CVSU email address is required.",
        4000,
        "top",
      ]);
      $("#perEmail").addClass("is-invalid");
      setTimeout(() => {
        $("#perEmail").removeClass("is-invalid");
      }, 5000);
      return;
    }

    if (perOrg === null) {
      QueueNotification([
        "error",
        "Please select your organization.",
        4000,
        "top",
      ]);

      $("#perOrg").addClass("is-invalid");
      setTimeout(() => {
        $("#perOrg").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perPosition === "") {
      QueueNotification(["error", "Please input your position.", 4000, "top"]);

      $("#perPosition").addClass("is-invalid");
      setTimeout(() => {
        $("#perPosition").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perCourse === "") {
      QueueNotification(["error", "Please select your course.", 4000, "top"]);
      return;
    }

    if (perAge < 18) {
      QueueNotification([
        "error",
        "You must be 18 years old and above.",
        4000,
        "top",
      ]);

      $("#perbirthDate").addClass("is-invalid");
      setTimeout(() => {
        $("#perbirthDate").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perPassword.length < 8) {
      QueueNotification([
        "error",
        "Password must be at least 8 characters.",
        4000,
        "top",
      ]);

      $("#perpassword").addClass("is-invalid");
      setTimeout(() => {
        $("#perpassword").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perPassword.length > 20) {
      QueueNotification([
        "error",
        "Password must not exceed 20 characters.",
        4000,
        "top",
      ]);

      $("#perpassword").addClass("is-invalid");
      setTimeout(() => {
        $("#perpassword").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perPassword.includes(" ")) {
      QueueNotification([
        "error",
        "Password must not contain spaces.",
        4000,
        "top",
      ]);
      return;
    }

    if (perPassword.search(/[a-z]/) < 0) {
      QueueNotification([
        "error",
        "Password must contain at least one lowercase letter.",
        4000,
        "top",
      ]);

      $("#perpassword").addClass("is-invalid");
      setTimeout(() => {
        $("#perpassword").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perPassword.search(/[A-Z]/) < 0) {
      QueueNotification([
        "error",
        "Password must contain at least one uppercase letter.",
        4000,
        "top",
      ]);

      $("#perpassword").addClass("is-invalid");
      setTimeout(() => {
        $("#perpassword").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perPassword.search(/[0-9]/) < 0) {
      QueueNotification([
        "error",
        "Password must contain at least one number.",
        4000,
        "top",
      ]);

      $("#perpassword").addClass("is-invalid");
      setTimeout(() => {
        $("#perpassword").removeClass("is-invalid");
      }, 5000);

      return;
    }

    if (perPassword.search(/[!@#$%^&*]/) < 0) {
      QueueNotification([
        "error",
        "Password must contain at least one special character.",
        4000,
        "top",
      ]);

      $("#perpassword").addClass("is-invalid");
      setTimeout(() => {
        $("#perpassword").removeClass("is-invalid");
      }, 5000);

      return;
    }

    const perData = {
      perFname,
      perLname,
      perEmail,
      perCourse,
      perStudentno,
      perContact,
      perAge,
      perbirthDate,
      perOrg,
      perPosition,
      perUUID,
      perPassword,
      perirreg,
    };

    function ProcessAccount(perData) {
      $.ajax({
        url: ".../../../Functions/api/postupdateAccount.php",
        type: "POST",
        data: perData,
        beforeSend: function () {
          swal
            .mixin({
              toast: true,
              position: "top",
              showConfirmButton: false,
              didOpen: () => {
                swal.showLoading();
              },
            })
            .fire({
              icon: "info",
              title: "Please wait...",
              text: "We are processing your request.",
            });
          $("#submitForm").attr("disabled", true);
        },

        success: function (data) {
          Swal.close();
          QueueNotification([
            "success",
            "Getting your account status. Please wait.",
            4000,
            "top",
          ]);
        },
        error: function (xhr, status, error) {
          QueueNotification([
            "error",
            "An error occured. Please try again later.",
            4000,
            "top",
          ]);
          console.error("AJAX Error:", error);
          console.error("AJAX Error:", xhr);
          console.error("AJAX Error:", status);
          $("#submitForm").attr("disabled", false);
        },
        complete: function (response) {
          setTimeout(() => {
            if (response.responseJSON.stat == "success") {
              QueueNotification([
                "success",
                response.responseJSON.message,
                4000,
                "top",
              ]);
              $("#alert")
                .removeClass("alert-primary alert-danger")
                .addClass("alert-success");
              ChangeStatus("4caf50", "APPROVED", 6000, 3500, 24);
              Swal.mixin({
                toast: true,
                position: "top",
                showConfirmButton: false,
                didOpen: () => {
                  swal.showLoading();
                },
              }).fire({
                icon: "info",
                title: "Clearing session...",
                text: "You will be redirected to the login page.",
              });
            } else {
              QueueNotification([
                "error",
                response.responseJSON.message,
                4000,
                "top",
              ]);
              $("#alert")
                .removeClass("alert-primary alert-success")
                .addClass("alert-danger");
              ChangeStatus("f44336", "REJECTED", 6000, 3500, 24);
              Swal.mixin({
                toast: true,
                position: "top",
                showConfirmButton: false,
                didOpen: () => {
                  swal.showLoading();
                },
              }).fire({
                icon: "info",
                title: "Clearing session...",
                text: "You will be redirected to the login page.",
              });
            }
          }, 4500);
        },
      });
    }

    $.ajax({
      url: ".../../../Functions/api/SendSMS.php",
      type: "POST",
      data: { contact: perContact, Fname: perFname, Lname: perLname },
      beforeSend: function () {
        swal
          .mixin({
            toast: true,
            position: "top",
            showConfirmButton: false,
            didOpen: () => {
              swal.showLoading();
            },
          })
          .fire({
            icon: "info",
            title: "Please wait...",
            text: "Sending OTP to your contact number.",
          });
      },

      success: function (data) {
        Swal.close();
        if (data.status == "success") {
          Swal.fire({
            title: "OTP Verification",
            text: "Enter the OTP sent to your contact number.",
            html: `<input type="text" id="otp" class="form-control" placeholder="Enter OTP here" required>`,
            showCancelButton: true,
            confirmButtonText: "Verify",
            cancelButtonText: "Cancel",
            showLoaderOnConfirm: true,
            customClass: {
              popup: "swal-popup-class",
            },
            preConfirm: () => {
              const otp = Swal.getPopup().querySelector("#otp").value.trim();
              if (!otp) {
                Swal.showValidationMessage("Please enter the OTP.");
                return false;
              }
              if (otp.length != 6) {
                Swal.showValidationMessage("OTP must be 6 digits.");
                return false;
              }
              if (isNaN(otp)) {
                Swal.showValidationMessage("OTP must be a number.");
                return false;
              }
              return otp;
            },
            allowOutsideClick: () => !Swal.isLoading(),
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: ".../../../Functions/api/verifyOTP.php",
                type: "POST",
                data: { otp: result.value },
                beforeSend: function () {
                  swal
                    .mixin({
                      toast: true,
                      position: "top",
                      showConfirmButton: false,
                      didOpen: () => {
                        swal.showLoading();
                      },
                    })
                    .fire({
                      icon: "info",
                      title: "Please wait...",
                      text: "Verifying OTP...",
                    });
                },
                success: function (data) {
                  Swal.close();
                  if (data.status == "success") {
                    ProcessAccount(perData);
                  } else {
                    QueueNotification(["error", data.message, 4000, "top"]);
                  }
                },
                error: function (error) {
                  console.error("AJAX Error:", error);
                  QueueNotification([
                    "error",
                    "An error occured. Please try again later.",
                    4000,
                    "top",
                  ]);
                },
              });
            }
          });
        } else {
          QueueNotification(["error", data.message, 4000, "top"]);
        }
      },
    });

    //ProcessAccount(perData);
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
              window.location.href =
                "../../Src/Pages/Accesspage.php?pending=logout";
            }, 4000);
          } else if (data.accountStat == "rejected") {
            $("#alert")
              .removeClass("alert-primary alert-success")
              .addClass("alert-danger");
            ChangeStatus("f44336", "REJECTED", 6000, 3500, 24);
            setTimeout(() => {
              window.location.href = "../../Src/Pages/Accesspage.php?error=006";
            }, 4000);
          }
        } else {
          console.log("Error: " + data.message);
        }
      },
    });
  }, 10000);
});
