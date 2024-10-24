// Purpose: Contains the scripts for the Access Page (Login, Register, Forgot Password 1, Forgot Password 2)
import {
  LoginProcess,
  FPStep1Process,
  FPStep2Process,
  showCurrentForm,
  changeColor,
} from "./Modules/AccessModules.js";

import {
  StudentNumRegex,
  PasswordRegex,
  EmailRegex,
} from "./Modules/RegexModules.js";

import { QueueNotification } from "./Modules/Queueing_Notification.js";

import { sha256 } from "./Modules/hash256.js";

var $ipAddress = null;
if (navigator.onLine) {
  $.getJSON("https://api.ipify.org?format=json", function (data) {
    $ipAddress = data.ip;
  });
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
    QueueNotification(["error", message, 5000, "top"]);
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
        ipAddress: $ipAddress, // get the Public IP Address of the user
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

  // Step 1
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
      password: $("#fps1-email").val(),
    };

    FPStep1Process(data);
  });

  // Step 2
  $("#eyecon-input3").click(function () {
    if ($("#fpS2-password").attr("type") == "password") {
      $("#fpS2-password").attr("type", "text");
      $("#eyecon-input3").html(
        '<svg width="22" height="22"><use xlink:href="#PassHide" /></svg>'
      );
    } else {
      $("#fpS2-password").attr("type", "password");
      $("#eyecon-input3").html(
        '<svg width="22" height="22"><use xlink:href="#PassShow" /></svg>'
      );
    }
  });

  $("#eyecon-input4").click(function () {
    if ($("#fpS2-cpassword").attr("type") == "password") {
      $("#fpS2-cpassword").attr("type", "text");
      $("#eyecon-input4").html(
        '<svg width="22" height="22"><use xlink:href="#PassHide" /></svg>'
      );
    } else {
      $("#fpS2-cpassword").attr("type", "password");
      $("#eyecon-input4").html(
        '<svg width="22" height="22"><use xlink:href="#PassShow" /></svg>'
      );
    }
  });

  $("#fpS2-password").keyup(function () {
    if ($("#fpS2-password").val().length == 0) {
      $("#fpS2-password").removeClass("is-invalid");
      $("#passwordHelp1").text("");
      return;
    }

    if (!PasswordRegex.test($("#fpS2-password").val())) {
      $("#fpS2-password").addClass("is-invalid");
      $("#passwordHelp1").text(
        "Password must be at least 8 characters long and must contain at least one uppercase letter, one lowercase letter, one number and one special character."
      );
      return;
    }

    $("#fpS2-password").removeClass("is-invalid");
    $("#fpS2-password").addClass("is-valid");
    $("#passwordHelp1").text("");

    setTimeout(function () {
      $("#fpS2-password").removeClass("is-valid");
    }, 1000);
  });

  $("#fpS2-cpassword").keyup(function () {
    if ($("#fpS2-cpassword").val().length == 0) {
      $("#fpS2-cpassword").removeClass("is-invalid");
      $("#cpasswordHelp").text("");
      return;
    }

    if ($("#fpS2-password").val() != $("#fpS2-cpassword").val()) {
      $("#fpS2-cpassword").addClass("is-invalid");
      $("#cpasswordHelp").text("Password does not match.");
      return;
    }

    $("#fpS2-cpassword").removeClass("is-invalid");
    $("#fpS2-cpassword").addClass("is-valid");
    $("#cpasswordHelp").text("");

    setTimeout(function () {
      $("#fpS2-cpassword").removeClass("is-valid");
    }, 1000);
  });

  $("#FpS2-btn").click(function () {
    //before Secondary Validation
    if ($("#fpS2-password").val() == "" || $("#fpS2-cpassword").val() == "") {
      if ($("#fpS2-password").val() == "") {
        $("#fpS2-password").focus();
        $("#fpS2-password").addClass("is-invalid");
        $("#passwordHelp1").text("Please enter your password.");

        $("#fpS2-password").addClass("shake");
        setTimeout(function () {
          $("#fpS2-password").removeClass("shake");
        }, 500);
      } else {
        $("#fpS2-password").removeClass("is-invalid");
        $("#passwordHelp1").text("");

        $("#fpS2-cpassword").focus();
        $("#fpS2-cpassword").addClass("is-invalid");
        $("#cpasswordHelp").text("Please confirm your password.");

        $("#fpS2-cpassword").addClass("shake");
        setTimeout(function () {
          $("#fpS2-cpassword").removeClass("shake");
        }, 500);
      }

      $("#FpS2-btn").attr("disabled", "disabled");

      setTimeout(function () {
        $("#FpS2-btn").removeAttr("disabled");
      }, 1500);

      return;
    }

    // for Password
    if (!PasswordRegex.test($("#fpS2-password").val())) {
      $("#fpS2-password").focus();
      $("#fpS2-password").addClass("is-invalid");
      $("#passwordHelp").text("Your password format is not valid.");
      return;
    }

    // for Confirm Password
    if ($("#fpS2-password").val() != $("#fpS2-cpassword").val()) {
      $("#fpS2-cpassword").focus();
      $("#fpS2-cpassword").addClass("is-invalid");
      $("#cpasswordHelp").text("Password does not match.");

      setTimeout(function () {
        $("#fpS2-cpassword").removeClass("is-invalid");
        $("#cpasswordHelp").text("");
      }, 3000);
      return;
    }

    // after validation
    $("#fpS2-password").removeClass("is-invalid");
    $("#fpS2-cpassword").removeClass("is-invalid");

    $("#passwordHelp1").text("");
    $("#cpasswordHelp").text("");

    $("#FpS2-btn").attr("disabled", "disabled");
    $("#FpS2-btn-label").addClass("d-none");
    $("#FpS2-btn-loader").removeClass("d-none");

    var data = {
      studentnum: $("#fps1-studnum").val(),
      password: $("#fps1-email").val(),
    };

    FPStep2Process(data);
  });

  let iconMenu = $(".bodymovinanim");

  let animation = bodymovin.loadAnimation({
    container: iconMenu[0],
    renderer: "svg",
    loop: false,
    autoplay: false,
    path: "../../Assets/Icons/animated-SVG/menuV3.json",
  });

  var direction = 1;
  iconMenu.click(function () {
    animation.setDirection(direction);
    animation.play();
    direction = -direction;
    animation.setSpeed(2);
  });
});
