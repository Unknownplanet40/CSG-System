// Purpose: Contains the scripts for the Access Page (Login, Register, Forgot Password 1, Forgot Password 2)
import {
  LoginProcess,
  FPStep1Process,
  FPStep2Process,
  FPStep3Process,
  RegisterProcess,
  showCurrentForm,
  changeColor,
  ActivateProcess,
} from "./Modules/AccessModules.js";

import {
  StudentNumRegex,
  PasswordRegex,
  EmailRegex,
} from "./Modules/RegexModules.js";

import { QueueNotification } from "./Modules/Queueing_Notification.js";

import { sha256 } from "./Modules/hash256.js";

function getDeviceName() {
  //Windows NT 10.0
  //Win64
  //x64

  var device = "";
  var deviceDetails = "";

  const isMobileDevice = window.navigator.userAgent
    .toLowerCase()
    .includes("mobi");

  if (isMobileDevice) {
    if (window.navigator.userAgent.includes("Android")) {
      device = "android";
      deviceDetails = window.navigator.userAgent.split(")")[0].split("(")[1];
    } else {
      device = "ios";
      deviceDetails = window.navigator.userAgent.split(")")[0].split("(")[1];
    }
  } else {
    device = "windows";
    deviceDetails = window.navigator.userAgent.split(")")[0].split("(")[1];
  }

  var details = {
    device: device,
    deviceDetails: deviceDetails,
  };

  return details;
}

var $ipAddress = null;

if (navigator.onLine) {
  $.getJSON("https://api.ipify.org?format=json", function (data) {
    $ipAddress = data.ip;
  });
} else {
  window.location.href = "../../Src/Pages/Error.html?Error=NoInternet";
}

$(document).ready(function () {
  if (localStorage.getItem("current-form") == null) {
    localStorage.setItem("current-form", "Login");
  }

  if (localStorage.getItem("activesession") != null) {
    var short;
    QueueNotification([
      "warning",
      "An account is already logged into this browser.",
      100000,
    ]);
  }

  if (localStorage.getItem("error") != null) {
    var message = localStorage.getItem("error");
    QueueNotification(["error", message, 5000, "top-end"]);
    localStorage.removeItem("error");
  }

  if (
    localStorage.getItem("studentnum") != null &&
    localStorage.getItem("password") != null
  ) {
    var autoLoginData = {
      studentnum: localStorage.getItem("studentnum"),
      password: localStorage.getItem("password"),
      ipAddress: $ipAddress,
      stats: "autologin",
    };

    localStorage.removeItem("studentnum");
    localStorage.removeItem("password");

    LoginProcess(autoLoginData);
  }

  // change form (Login, Register, Forgot Password 1, Forgot Password 2)
  setInterval(showCurrentForm, 100);

  // Login Form Validation
  $("#eyecon").click(function () {
    if ($("#Login-password").attr("type") == "password") {
      $("#Login-password").attr("type", "text");
      $("#eyecon").html(
        '<svg width="22" height="22"><use xlink:href="#PassHide" /></svg>'
      );
    } else {
      $("#Login-password").attr("type", "password");
      $("#eyecon").html(
        '<svg width="22" height="22"><use xlink:href="#PassShow" /></svg>'
      );
    }
  });

  $("#Login-stdnum").keyup(function () {
    changeColor();

    if ($("#Login-stdnum").val().length == 0) {
      $("#Login-stdnum").removeClass("is-invalid");
      $("#stdnumHelp").text("");
      return;
    }

    if (!StudentNumRegex.test($("#Login-stdnum").val())) {
      $("#Login-stdnum").addClass("is-invalid");
      $("#stdnumHelp").text("Student number must be 9 digits long.");
      return;
    }

    $("#Login-stdnum").removeClass("is-invalid");
    $("#Login-stdnum").addClass("is-valid");
    $("#stdnumHelp").text("");

    setTimeout(function () {
      $("#Login-stdnum").removeClass("is-valid");
    }, 1000);
  });

  $("#Login-password").keyup(function (e) {
    if (e.keyCode == 13) {
      $("#Login-btn").click();
    }

    changeColor();

    if ($("#Login-password").val().length == 0) {
      $("#Login-password").removeClass("is-invalid");
      $("#passwordHelp").text("");
      return;
    }

    if (!PasswordRegex.test($("#Login-password").val())) {
      $("#Login-password").addClass("is-invalid");
      $("#passwordHelp").text(
        "Password must be at least 8 characters long and must contain at least one uppercase letter, one lowercase letter, one number and one special character."
      );
      return;
    }

    $("#Login-password").removeClass("is-invalid");
    $("#Login-password").addClass("is-valid");
    $("#passwordHelp").text("");

    setTimeout(function () {
      $("#Login-password").removeClass("is-valid");
    }, 1000);
  });

  $("#Login-btn").click(function () {
    //added this to prevent the form from submitting when requirements are not met

    //before Secondary Validation
    if ($("#Login-stdnum").val() == "" || $("#Login-password").val() == "") {
      if ($("#Login-stdnum").val() == "") {
        $("#Login-stdnum").focus();
        $("#Login-stdnum").addClass("is-invalid");
        $("#stdnumHelp").text("Please enter your student number.");

        $("#Login-stdnum").addClass("shake");
        setTimeout(function () {
          $("#Login-stdnum").removeClass("shake");
        }, 500);
      } else {
        $("#Login-stdnum").removeClass("is-invalid");
        $("#stdnumHelp").text("");

        $("#Login-password").focus();
        $("#Login-password").addClass("is-invalid");
        $("#passwordHelp").text("Please enter your password.");

        $("#Login-password").addClass("shake");
        setTimeout(function () {
          $("#Login-password").removeClass("shake");
        }, 500);
      }

      $("#Login-btn").attr("disabled", "disabled");

      setTimeout(function () {
        $("#Login-btn").removeAttr("disabled");
      }, 1500);
      return;
    }

    // for Student Number
    if (!StudentNumRegex.test($("#Login-stdnum").val())) {
      $("#Login-stdnum").focus();
      $("#Login-stdnum").addClass("is-invalid");
      $("#stdnumHelp").text("Your student number is not valid.");
      // QueueNotification([icon, title, message, duration])
      QueueNotification([
        "info",
        '"' + $("#Login-stdnum").val() + " is not a valid student number.",
        3000,
      ]);
      return;
    }

    // for Password
    if (!PasswordRegex.test($("#Login-password").val())) {
      $("#Login-password").focus();
      $("#Login-password").addClass("is-invalid");
      $("#passwordHelp").text("Your password format is not valid.");

      setTimeout(function () {
        $("#Login-password").removeClass("is-invalid");
        $("#passwordHelp").text("");
      }, 3000);

      QueueNotification([
        "info",
        '"' + $("#Login-password").val() + " is not a valid password.",
        3000,
      ]);
      return;
    }

    // after validation
    $("#Login-stdnum").removeClass("is-invalid");
    $("#Login-password").removeClass("is-invalid");

    $("#stdnumHelp").text("");
    $("#passwordHelp").text("");

    $("#Login-btn").attr("disabled", "disabled");
    $("#Login-btn-label").addClass("d-none");
    $("#Login-btn-loader").removeClass("d-none");

    if (localStorage.getItem("activesession") != null) {
      if (
        sha256($("#Login-stdnum").val()) !== localStorage.getItem("currentUser")
      ) {
        Swal.fire({
          title: "Session Already Active",
          text: localStorage.getItem("activesession"),
          icon: "info",
          confirmButtonText: "Understood",
          allowOutsideClick: false,
          customClass: {
            popup: "alert-popup-inform",
            confirmButton: "alert-button-confirm",
            container: "alert-container",
            htmlContainer: "alert-html-container",
            title: "alert-title",
          },
        });
        setTimeout(function () {
          $("#Login-btn").removeAttr("disabled");
          $("#Login-btn-label").removeClass("d-none");
          $("#Login-btn-loader").addClass("d-none");
        }, 1000);
      } else {
        QueueNotification(["info", "Confirming your session...", 1200]);
        setTimeout(function () {
          window.location.href =
            "../../Src/Functions/api/UserLogout.php?studentnum=" +
            $("#Login-stdnum").val() +
            "&password=" +
            $("#Login-password").val();
        }, 1500);
      }
    } else {
      localStorage.removeItem("activesession");
      localStorage.removeItem("currentUser");

      var data = {
        studentnum: $("#Login-stdnum").val(),
        password: $("#Login-password").val(),
        ipAddress: $ipAddress,
        Device: getDeviceName(),
      };

      LoginProcess(data);
    }
  });

  // Register Form Validation
  $("#eyecon-input2").click(function () {
    if ($("#Reg-password").attr("type") == "password") {
      $("#Reg-password").attr("type", "text");
      $("#eyecon-input2").html(
        '<svg width="22" height="22"><use xlink:href="#PassHide" /></svg>'
      );
    } else {
      $("#Reg-password").attr("type", "password");
      $("#eyecon-input2").html(
        '<svg width="22" height="22"><use xlink:href="#PassShow" /></svg>'
      );
    }
  });

  // Forgot Password 1 & 2 Form Validation

  // Step 1 - Check if Student Number and Email is valid
  $("#fps1-studnum").keyup(function () {
    if ($("#fps1-studnum").val().length == 0) {
      $("#fps1-studnum").removeClass("is-invalid");
      $("#studnumHelp").text("");
      return;
    }

    if (!StudentNumRegex.test($("#fps1-studnum").val())) {
      $("#fps1-studnum").addClass("is-invalid");
      $("#studnumHelp").text("Student number must be 10 digits long.");
      return;
    }

    $("#fps1-studnum").removeClass("is-invalid");
    $("#fps1-studnum").addClass("is-valid");
    $("#studnumHelp").text("");

    setTimeout(function () {
      $("#fps1-studnum").removeClass("is-valid");
    }, 1000);
  });

  $("#fps1-email").keyup(function () {
    if ($("#fps1-email").val().length == 0) {
      $("#fps1-email").removeClass("is-invalid");
      $("#emailHelp").text("");
      return;
    }

    if (!EmailRegex.test($("#fps1-email").val())) {
      $("#fps1-email").addClass("is-invalid");
      $("#emailHelp").text("Email address is not valid.");
      return;
    }

    $("#fps1-email").removeClass("is-invalid");
    $("#fps1-email").addClass("is-valid");
    $("#emailHelp").text("");

    setTimeout(function () {
      $("#fps1-email").removeClass("is-valid");
    }, 1000);
  });

  $("#FpS1-btn").click(function () {
    //before Secondary Validation
    if ($("#fps1-studnum").val() == "" || $("#fps1-email").val() == "") {
      if ($("#fps1-studnum").val() == "") {
        $("#fps1-studnum").focus();
        $("#fps1-studnum").addClass("is-invalid");
        $("#studnumHelp").text("Please enter your student number.");

        $("#fps1-studnum").addClass("shake");
        setTimeout(function () {
          $("#fps1-studnum").removeClass("shake");
        }, 500);
      } else {
        $("#fps1-studnum").removeClass("is-invalid");
        $("#studnumHelp").text("");

        $("#fps1-email").focus();
        $("#fps1-email").addClass("is-invalid");
        $("#emailHelp").text("Please enter your email address.");

        $("#fps1-email").addClass("shake");
        setTimeout(function () {
          $("#fps1-email").removeClass("shake");
        }, 500);
      }

      $("#FpS1-btn").attr("disabled", "disabled");

      setTimeout(function () {
        $("#FpS1-btn").removeAttr("disabled");
      }, 1500);
      return;
    }

    // for Student Number
    if (!StudentNumRegex.test($("#fps1-studnum").val())) {
      $("#fps1-studnum").focus();
      $("#fps1-studnum").addClass("is-invalid");
      $("#studnumHelp").text("Your student number is not valid.");
      return;
    }

    // for Email
    if (!EmailRegex.test($("#fps1-email").val())) {
      $("#fps1-email").focus();
      $("#fps1-email").addClass("is-invalid");
      $("#emailHelp").text("Your email address is not valid.");

      setTimeout(function () {
        $("#fps1-email").removeClass("is-invalid");
        $("#emailHelp").text("");
      }, 3000);
      return;
    }

    // after validation
    $("#fps1-studnum").removeClass("is-invalid");
    $("#fps1-email").removeClass("is-invalid");

    $("#studnumHelp").text("");
    $("#emailHelp").text("");

    $("#FpS1-btn").attr("disabled", "disabled");
    $("#FpS1-btn-label").addClass("d-none");
    $("#FpS1-btn-loader").removeClass("d-none");

    // api or ajax call here
    var data = {
      studentnum: $("#fps1-studnum").val(),
      primaryEmail: $("#fps1-email").val(),
      step: 1,
    };

    FPStep1Process(data);
  });

  // resend OTP
  $("#resendOTP").click(function () {
    $("#resendOTP").attr("disabled", "disabled");
    $("#resendOTP").html("Resending OTP...");

    var data = {
      studentnum: $("#fps1-studnum").val(),
      primaryEmail: $("#fps1-email").val(),
      step: 1,
    };

    FPStep1Process(data);
  });
  // Step 2 - Verify the OTP

  $("#FpS2-btn").click(function () {
    //before Secondary Validation
    if ($("#ResetToken").val() == "") {
      $("#ResetToken").focus();
      $("#ResetToken").addClass("is-invalid");
      $("#otpHelp").text("Please enter the OTP.");

      $("#ResetToken").addClass("shake");
      setTimeout(function () {
        $("#ResetToken").removeClass("shake");
      }, 500);

      $("#FpS2-btn").attr("disabled", "disabled");

      setTimeout(function () {
        $("#FpS2-btn").removeAttr("disabled");
      }, 1500);
      return;
    }

    // after validation
    $("#ResetToken").removeClass("is-invalid");
    $("#otpHelp").text("");

    $("#FpS2-btn").attr("disabled", "disabled");
    $("#FpS2-btn-label").addClass("d-none");
    $("#FpS2-btn-loader").removeClass("d-none");

    var data = {
      studentnum: $("#fps1-studnum").val(),
      primaryEmail: $("#fps1-email").val(),
      token: $("#ResetToken").val(),
      step: 2,
    };

    FPStep2Process(data);
  });

  // Step 3 - Change Password
  $("#eyecon-input3").click(function () {
    if ($("#fpS3-password").attr("type") == "password") {
      $("#fpS3-password").attr("type", "text");
      $("#eyecon-input3").html(
        '<svg width="22" height="22"><use xlink:href="#PassHide" /></svg>'
      );
    } else {
      $("#fpS3-password").attr("type", "password");
      $("#eyecon-input3").html(
        '<svg width="22" height="22"><use xlink:href="#PassShow" /></svg>'
      );
    }
  });

  $("#eyecon-input4").click(function () {
    if ($("#fpS3-cpassword").attr("type") == "password") {
      $("#fpS3-cpassword").attr("type", "text");
      $("#eyecon-input4").html(
        '<svg width="22" height="22"><use xlink:href="#PassHide" /></svg>'
      );
    } else {
      $("#fpS3-cpassword").attr("type", "password");
      $("#eyecon-input4").html(
        '<svg width="22" height="22"><use xlink:href="#PassShow" /></svg>'
      );
    }
  });

  $("#fpS3-password").keyup(function () {
    if ($("#fpS3-password").val().length == 0) {
      $("#fpS3-password").removeClass("is-invalid");
      $("#passwordHelp1").text("");
      return;
    }

    if (!PasswordRegex.test($("#fpS3-password").val())) {
      $("#fpS3-password").addClass("is-invalid");
      $("#passwordHelp1").text(
        "Password must be at least 8 characters long and must contain at least one uppercase letter, one lowercase letter, one number and one special character."
      );
      return;
    }

    $("#fpS3-password").removeClass("is-invalid");
    $("#fpS3-password").addClass("is-valid");
    $("#passwordHelp1").text("");

    setTimeout(function () {
      $("#fpS3-password").removeClass("is-valid");
    }, 1000);
  });

  $("#fpS3-cpassword").keyup(function () {
    if ($("#fpS3-cpassword").val().length == 0) {
      $("#fpS3-cpassword").removeClass("is-invalid");
      $("#cpasswordHelp").text("");
      return;
    }

    if ($("#fpS3-password").val() != $("#fpS3-cpassword").val()) {
      $("#fpS3-cpassword").addClass("is-invalid");
      $("#cpasswordHelp").text("Password does not match.");
      return;
    }

    $("#fpS3-cpassword").removeClass("is-invalid");
    $("#fpS3-cpassword").addClass("is-valid");
    $("#cpasswordHelp").text("");

    setTimeout(function () {
      $("#fpS3-cpassword").removeClass("is-valid");
    }, 1000);
  });

  $("#fpS3-btn").click(function () {
    //before Secondary Validation
    if ($("#fpS3-password").val() == "" || $("#fpS3-cpassword").val() == "") {
      if ($("#fpS3-password").val() == "") {
        $("#fpS3-password").focus();
        $("#fpS3-password").addClass("is-invalid");
        $("#passwordHelp1").text("Please enter your password.");

        $("#fpS3-password").addClass("shake");
        setTimeout(function () {
          $("#fpS3-password").removeClass("shake");
        }, 500);
      } else {
        $("#fpS3-password").removeClass("is-invalid");
        $("#passwordHelp1").text("");

        $("#fpS3-cpassword").focus();
        $("#fpS3-cpassword").addClass("is-invalid");
        $("#cpasswordHelp").text("Please confirm your password.");

        $("#fpS3-cpassword").addClass("shake");
        setTimeout(function () {
          $("#fpS3-cpassword").removeClass("shake");
        }, 500);
      }

      $("#fpS3-btn").attr("disabled", "disabled");

      setTimeout(function () {
        $("#fpS3-btn").removeAttr("disabled");
      }, 1500);

      return;
    }

    // for Password
    if (!PasswordRegex.test($("#fpS3-password").val())) {
      $("#fpS3-password").focus();
      $("#fpS3-password").addClass("is-invalid");
      $("#passwordHelp").text("Your password format is not valid.");
      return;
    }

    // for Confirm Password
    if ($("#fpS3-password").val() != $("#fpS3-cpassword").val()) {
      $("#fpS3-cpassword").focus();
      $("#fpS3-cpassword").addClass("is-invalid");
      $("#cpasswordHelp").text("Password does not match.");

      setTimeout(function () {
        $("#fpS3-cpassword").removeClass("is-invalid");
        $("#cpasswordHelp").text("");
      }, 3000);
      return;
    }

    // after validation
    $("#fpS3-password").removeClass("is-invalid");
    $("#fpS3-cpassword").removeClass("is-invalid");

    $("#passwordHelp1").text("");
    $("#cpasswordHelp").text("");

    $("#fpS3-btn").attr("disabled", "disabled");
    $("#fpS3-btn-label").addClass("d-none");
    $("#fpS3-btn-loader").removeClass("d-none");

    var data = {
      studentnum: $("#fps1-studnum").val(),
      primaryEmail: $("#fps1-email").val(),
      newpassword: $("#fpS3-password").val(),
      step: 3,
    };

    FPStep3Process(data);
  });

  $("#Reg-year").empty().prop("disabled", true);
  $("#Reg-section").empty().prop("disabled", true);

  $.ajax({
    url: "../Functions/api/getAcadData.php",
    type: "POST",
    data: {
      CourseID: null,
      Action: "Get-Course",
    },

    success: function (res) {
      if (res.status === "success") {
        $("#Reg-course").empty();
        $("#Reg-course").append(`<option selected hidden>Choose...</option>`);
        res.data.forEach((courses) => {
          $("#Reg-course").append(
            `<option value="${courses.ShortName}">${courses.CourseName}</option>`
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
    },
  });

  $("#Reg-course").change(function () {
    var year = $("#Reg-year");
    year.prop("disabled", false);
    $("#Reg-section").empty().prop("disabled", true);
    $.ajax({
      url: "../Functions/api/getAcadData.php",
      type: "POST",
      data: {
        CourseID: $("#Reg-course").val(),
        Action: "Get-Year",
      },

      success: function (res) {
        if (res.status === "success") {
          year.empty();
          year.append(`<option selected hidden>Choose...</option>`);
          res.data.forEach((courses) => {
            year.append(
              `<option value="${courses.Year}">${courses.CourseName}</option>`
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
      },
    });
  });

  $("#Reg-year").change(function () {
    var section = $("#Reg-section");
    var yearlvl = $("#Reg-year").val();
    section.prop("disabled", false);
    $.ajax({
      url: "../Functions/api/getAcadData.php",
      type: "POST",
      data: {
        CourseID: $("#Reg-course").val(),
        Action: "Get-Section",
        YearLevel: yearlvl,
      },

      success: function (res) {
        if (res.status === "success") {
          section.empty();
          section.append(`<option selected hidden>Choose...</option>`);
          res.data.forEach((courses) => {
            section.append(
              `<option value="${courses.CourseName}">${courses.Section}</option>`
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
      },
    });
  });

  $("#Reg-stdnum").keyup(function () {
    if ($("#Reg-stdnum").val().length == 0) {
      $("#Reg-stdnum").removeClass("is-invalid");
      $("#stdnumHelp-Reg").text("");
      return;
    }

    if (!StudentNumRegex.test($("#Reg-stdnum").val())) {
      $("#Reg-stdnum").addClass("is-invalid");
      $("#stdnumHelp-Reg").text("Student number must be 9 digits long.");
      return;
    }

    if ($("#Reg-stdnum").val().length == 9) {
      $.ajax({
        url: "../../Src/Functions/api/chekSTDNUM.php",
        type: "POST",
        data: { studentnum: $("#Reg-stdnum").val() },
        success: function (data) {
          if (data.status == "success") {
            if (data.isStudentNumExist == "true") {
              $("#Reg-stdnum").addClass("is-invalid");
              $("#stdnumHelp-Reg").text(
                "Student number is already registered."
              );
              return;
            } else {
              $("#Reg-stdnum").removeClass("is-invalid");
              $("#Reg-stdnum").addClass("is-valid");
              $("#stdnumHelp").text("");
            }
          } else {
            $("#Reg-stdnum").addClass("is-invalid");
            $("#stdnumHelp-Reg").text(
              "Something went wrong. Please try again."
            );
            return;
          }
        },

        error: function () {
          $("#Reg-stdnum").addClass("is-invalid");
          $("#stdnumHelp-Reg").text("Something went wrong. Please try again.");
          return;
        },
      });
    }

    if (isNaN($("#Reg-stdnum").val())) {
      $("#Reg-stdnum").addClass("is-invalid");
      $("#stdnumHelp-Reg").text("Student number must be numeric.");
      return;
    }

    setTimeout(function () {
      $("#Reg-stdnum").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-firstname").keyup(function () {
    if ($("#Reg-firstname").val().length == 0) {
      $("#Reg-firstname").removeClass("is-invalid");
      $("#firstnameHelp-Reg").text("");
      return;
    }

    if ($("#Reg-firstname").val().length < 2) {
      $("#Reg-firstname").addClass("is-invalid");
      $("#firstnameHelp-Reg").text(
        "First name must be at least 2 characters long."
      );
      return;
    }

    if (/[0-9]/.test($("#Reg-firstname").val())) {
      $("#Reg-firstname").addClass("is-invalid");
      $("#firstnameHelp-Reg").text("First name must not contain numbers.");
      return;
    }

    $("#Reg-firstname").removeClass("is-invalid");
    $("#Reg-firstname").addClass("is-valid");
    $("#firstnameHelp-Reg").text("");

    setTimeout(function () {
      $("#Reg-firstname").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-lastname").keyup(function () {
    if ($("#Reg-lastname").val().length == 0) {
      $("#Reg-lastname").removeClass("is-invalid");
      $("#lastnameHelp-Reg").text("");
      return;
    }

    if ($("#Reg-lastname").val().length < 2) {
      $("#Reg-lastname").addClass("is-invalid");
      $("#lastnameHelp-Reg").text(
        "Last name must be at least 2 characters long."
      );
      return;
    }

    if (/[0-9]/.test($("#Reg-lastname").val())) {
      $("#Reg-lastname").addClass("is-invalid");
      $("#lastnameHelp-Reg").text("Last name must not contain numbers.");
      return;
    }

    $("#Reg-lastname").removeClass("is-invalid");
    $("#Reg-lastname").addClass("is-valid");
    $("#lastnameHelp-Reg").text("");

    setTimeout(function () {
      $("#Reg-lastname").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-course").change(function () {
    if ($("#Reg-course").val() == "default") {
      $("#Reg-course").addClass("is-invalid");
      $("#courseHelp-Reg").text("Please select your course.");
      return;
    }

    $("#Reg-course").removeClass("is-invalid");
    $("#Reg-course").addClass("is-valid");
    $("#courseHelp-Reg").text("");

    setTimeout(function () {
      $("#Reg-course").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-year").change(function () {
    if ($("#Reg-year").val() == "default") {
      $("#Reg-year").addClass("is-invalid");
      $("#courseHelp-Reg").text("Please select your year level.");
      return;
    }

    $("#Reg-year").removeClass("is-invalid");
    $("#Reg-year").addClass("is-valid");
    $("#courseHelp-Reg").text("");

    setTimeout(function () {
      $("#Reg-year").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-section").change(function () {
    if ($("#Reg-section").val() == "default") {
      $("#Reg-section").addClass("is-invalid");
      $("#courseHelp-Reg").text("Please select your section.");
      return;
    }

    $("#Reg-section").removeClass("is-invalid");
    $("#Reg-section").addClass("is-valid");
    $("#courseHelp-Reg").text("");

    setTimeout(function () {
      $("#Reg-section").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-email").keyup(function () {
    if ($("#Reg-email").val().length == 0) {
      $("#Reg-email").removeClass("is-invalid");
      $("#emailHelp-Reg").text("");
      return;
    }

    if (!EmailRegex.test($("#Reg-email").val())) {
      $("#Reg-email").addClass("is-invalid");
      $("#emailHelp-Reg").text("Email address is not valid.");
      return;
    }

    // after @ validate if the domain is valid
    var onlyValidDomain = "@cvsu.edu.ph";
    var email = $("#Reg-email").val();
    var domain = email.substring(email.indexOf("@"));

    if (domain != onlyValidDomain) {
      $("#Reg-email").addClass("is-invalid");
      $("#emailHelp-Reg").text(
        "You are using an email address outside of the organization."
      );
      return;
    }

    $("#Reg-email").removeClass("is-invalid");
    $("#Reg-email").addClass("is-valid");
    $("#emailHelp-Reg").text("");

    setTimeout(function () {
      $("#Reg-email").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-password").keyup(function () {
    if ($("#Reg-password").val().length == 0) {
      $("#Reg-password").removeClass("is-invalid");
      $("#passwordHelp-Reg").text("");
      return;
    }

    if (!PasswordRegex.test($("#Reg-password").val())) {
      $("#Reg-password").addClass("is-invalid");
      $("#passwordHelp-Reg").text(
        "Password must be at least 8 characters long and must contain at least one uppercase letter, one lowercase letter, one number and one special character."
      );
      return;
    }

    $("#Reg-password").removeClass("is-invalid");
    $("#Reg-password").addClass("is-valid");
    $("#passwordHelp-Reg").text("");

    setTimeout(function () {
      $("#Reg-password").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-phone").keyup(function () {
    if ($("#Reg-phone").val().length == 0) {
      $("#Reg-phone").removeClass("is-invalid");
      $("#phoneHelp-Reg").text("");
      return;
    }

    if (isNaN($("#Reg-phone").val())) {
      $("#Reg-phone").addClass("is-invalid");
      $("#phoneHelp-Reg").text("Phone number must be numeric.");
      return;
    }

    if ($("#Reg-phone").val().length < 11) {
      $("#Reg-phone").addClass("is-invalid");
      $("#phoneHelp-Reg").text("Phone number must be 11 digits long.");
      return;
    }

    $("#Reg-phone").removeClass("is-invalid");
    $("#Reg-phone").addClass("is-valid");
    $("#phoneHelp-Reg").text("");

    setTimeout(function () {
      $("#Reg-phone").removeClass("is-valid");
    }, 1000);
  });

  $("#Reg-btn").click(function () {
    if (
      $("#Reg-stdnum").val() == "" ||
      $("#Reg-firstname").val() == "" ||
      $("#Reg-lastname").val() == "" ||
      $("#Reg-course").val() == null ||
      $("#Reg-year").val() == null ||
      $("#Reg-section").val() == null ||
      $("#Reg-email").val() == "" ||
      $("#Reg-phone").val() == "" ||
      $("#Reg-password").val() == ""
    ) {
      if ($("#Reg-stdnum").val() == "") {
        $("#Reg-stdnum").focus();
        $("#Reg-stdnum").addClass("is-invalid");
        $("#stdnumHelp-Reg").text("Please enter your student number.");

        $("#Reg-stdnum").addClass("shake");
        setTimeout(function () {
          $("#Reg-stdnum").removeClass("shake");
        }, 500);
      } else {
        $("#Reg-stdnum").removeClass("is-invalid");
        $("#stdnumHelp-Reg").text("");

        if ($("#Reg-firstname").val() == "") {
          $("#Reg-firstname").focus();
          $("#Reg-firstname").addClass("is-invalid");
          $("#firstnameHelp-Reg").text("Please enter your first name.");

          $("#Reg-firstname").addClass("shake");
          setTimeout(function () {
            $("#Reg-firstname").removeClass("shake");
          }, 500);
        } else {
          $("#Reg-firstname").removeClass("is-invalid");
          $("#firstnameHelp-Reg").text("");

          if ($("#Reg-lastname").val() == "") {
            $("#Reg-lastname").focus();
            $("#Reg-lastname").addClass("is-invalid");
            $("#lastnameHelp-Reg").text("Please enter your last name.");

            $("#Reg-lastname").addClass("shake");
            setTimeout(function () {
              $("#Reg-lastname").removeClass("shake");
            }, 500);
          }
        }
      }

      $("#Reg-btn").attr("disabled", "disabled");

      setTimeout(function () {
        $("#Reg-btn").removeAttr("disabled");
      }, 1500);
      return;
    } else {
      if (!StudentNumRegex.test($("#Reg-stdnum").val())) {
        $("#Reg-stdnum").focus();
        $("#Reg-stdnum").addClass("is-invalid");
        $("#stdnumHelp-Reg").text("Your student number is not valid.");
        return;
      }

      if ($("#Reg-firstname").val().length < 2) {
        $("#Reg-firstname").focus();
        $("#Reg-firstname").addClass("is-invalid");
        $("#firstnameHelp-Reg").text(
          "First name must be at least 2 characters long."
        );
        return;
      }

      if (/[0-9]/.test($("#Reg-firstname").val())) {
        $("#Reg-firstname").focus();
        $("#Reg-firstname").addClass("is-invalid");
        $("#firstnameHelp-Reg").text("First name must not contain numbers.");
        return;
      }

      if ($("#Reg-lastname").val().length < 2) {
        $("#Reg-lastname").focus();
        $("#Reg-lastname").addClass("is-invalid");
        $("#lastnameHelp-Reg").text(
          "Last name must be at least 2 characters long."
        );
        return;
      }

      if (/[0-9]/.test($("#Reg-lastname").val())) {
        $("#Reg-lastname").focus();
        $("#Reg-lastname").addClass("is-invalid");
        $("#lastnameHelp-Reg").text("Last name must not contain numbers.");
        return;
      }

      if ($("#Reg-course").val() == "default") {
        $("#Reg-course").addClass("is-invalid");
        $("#courseHelp-Reg").text("Please select your course.");
        return;
      }

      if ($("#Reg-year").val() == "default") {
        $("#Reg-year").addClass("is-invalid");
        $("#courseHelp-Reg").text("Please select your year level.");
        return;
      }

      if ($("#Reg-section").val() == "default") {
        $("#Reg-section").addClass("is-invalid");
        $("#courseHelp-Reg").text("Please select your section.");
        return;
      }

      if (!EmailRegex.test($("#Reg-email").val())) {
        $("#Reg-email").focus();
        $("#Reg-email").addClass("is-invalid");
        $("#emailHelp-Reg").text("Your email address is not valid.");
        return;
      }

      var onlyValidDomain = "@cvsu.edu.ph";
      var email = $("#Reg-email").val();
      var domain = email.substring(email.indexOf("@"));

      if (domain != onlyValidDomain) {
        $("#Reg-email").addClass("is-invalid");
        $("#emailHelp-Reg").text(
          "You are using an email address outside of the organization."
        );
        return;
      }

      if (!PasswordRegex.test($("#Reg-password").val())) {
        $("#Reg-password").focus();
        $("#Reg-password").addClass("is-invalid");
        $("#passwordHelp-Reg").text("Your password format is not valid.");

        setTimeout(function () {
          $("#Reg-password").removeClass("is-invalid");
          $("#passwordHelp-Reg").text("");
        }, 3000);
        return;
      }

      if (isNaN($("#Reg-phone").val())) {
        $("#Reg-phone").addClass("is-invalid");
        $("#phoneHelp-Reg").text("Phone number must be numeric.");
        return;
      }

      if ($("#Reg-phone").val().length < 11) {
        $("#Reg-phone").addClass("is-invalid");
        $("#phoneHelp-Reg").text("Phone number must be 11 digits long.");
        return;
      }

      $("#Reg-stdnum").removeClass("is-invalid");
      $("#Reg-firstname").removeClass("is-invalid");
      $("#Reg-lastname").removeClass("is-invalid");
      $("#Reg-course").removeClass("is-invalid");
      $("#Reg-year").removeClass("is-invalid");
      $("#Reg-section").removeClass("is-invalid");
      $("#Reg-email").removeClass("is-invalid");
      $("#Reg-phone").removeClass("is-invalid");
      $("#Reg-password").removeClass("is-invalid");

      $("#stdnumHelp-Reg").text("");
      $("#firstnameHelp-Reg").text("");
      $("#lastnameHelp-Reg").text("");
      $("#courseHelp-Reg").text("");
      $("#emailHelp-Reg").text("");
      $("#phoneHelp-Reg").text("");
      $("#passwordHelp-Reg").text("");

      $("#Reg-btn").attr("disabled", "disabled");
      $("#Reg-btn-label").addClass("d-none");
      $("#Reg-btn-loader").removeClass("d-none");

      var data = {
        studentnum: $("#Reg-stdnum").val(),
        firstname: $("#Reg-firstname").val(),
        lastname: $("#Reg-lastname").val(),
        course: $("#Reg-course").val(),
        year: $("#Reg-year").val(),
        section: $("#Reg-section").val(),
        email: $("#Reg-email").val(),
        phone: $("#Reg-phone").val(),
        password: $("#Reg-password").val(),
      };

      RegisterProcess(data);
    }
  });

  $("#act-btn").click(function () {
    let tempMail = $("#act-email").val();
    let tempPass = $("#act-password").val();

    if (tempMail == "" || tempPass == "") {
      if (tempMail == "") {
        $("#act-email").focus();
        $("#act-email").addClass("is-invalid");
        $("#act_emailHelp").text("Please enter your email address.");

        $("#act-email").addClass("shake");
        setTimeout(function () {
          $("#act-email").removeClass("shake");
          $("#act-email").removeClass("is-invalid");
        }, 1500);
        return;
      }

      if (tempPass == "") {
        $("#act-password").focus();
        $("#act-password").addClass("is-invalid");
        $("#act_passwordHelp").text("Please enter your password.");

        $("#act-password").addClass("shake");
        setTimeout(function () {
          $("#act-password").removeClass("shake");
          $("#act-password").removeClass("is-invalid");
        }, 1500);
        return;
      }
    }

    if (!EmailRegex.test(tempMail)) {
      $("#act-email").focus();
      $("#act-email").addClass("is-invalid");
      $("#act_emailHelp").text("Email address is not valid.");
      return;
    }

    if (!PasswordRegex.test(tempPass)) {
      $("#act-password").focus();
      $("#act-password").addClass("is-invalid");
      $("#act_passwordHelp").text("Password format is not valid.");
      return;
    }

    let data = {
      email: tempMail,
      password: tempPass,
    };

    $("#act-btn").attr("disabled", "disabled");
    $("#act-btn-label").addClass("d-none");
    $("#act-btn-loader").removeClass("d-none");

    ActivateProcess(data);
  });
});
