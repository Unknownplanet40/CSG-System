import { QueueNotification } from "./Queueing_Notification.js";
// this fuction will display the ReAttempt time for the user to try again
function displayReAttempt() {
  $("#log-btn-lbl").removeClass("d-none");

  if (
    localStorage.getItem("isLocked") != null &&
    localStorage.getItem("isLocked") == "true"
  ) {
    // get current date and time
    var lockoutTime = new Date(localStorage.getItem("lockoutTime")).getTime();
    var x = setInterval(function () {
      // Get today's date and time
      var now = new Date().getTime();

      // Find the distance between now and the count down date
      var distance = lockoutTime - now;

      // Time calculations for days, hours, minutes and seconds
      var days = Math.floor(distance / (1000 * 60 * 60 * 24));
      var hours = Math.floor(
        (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
      );
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Display the result in the element with id="demo"
      $("#log-btn-lbl").text(
        "Last attempt was unsuccessful. Please wait " +
          minutes +
          "m " +
          seconds +
          "s"
      );

      // If the count down is finished, write some text
      if (distance < 0) {
        clearInterval(x);
        $("#log-btn-lbl").addClass("d-none").text("it's fucking done");
      }
    }, 1000);
  } else {
    $("#log-btn-lbl").text("Login").addClass("d-none");
  }
}

/* displayReAttempt();
setInterval(function () {
  if (localStorage.getItem("isLocked") != null && localStorage.getItem("isLocked") == "true") {
    displayReAttempt();
  }
}, 1000); */

export async function LoginProcess(data) {
  try {
    let redirectTo = "";
    const response = await fetch("../../Src/Functions/api/UserLogin.php", {
      method: "POST",
      body: JSON.stringify(data),
      timeout: 5000,
      headers: {
        "Content-type": "application/json; charset=UTF-8",
      },
    });

    if (!response.ok) {
      const message = `An error has occured: ${response.status}`;
      throw new Error(message);
    }

    const resData = await response.json();

    if (resData.status == "critical") {
      throw new Error("Reason - [ " + resData.message + " ]");
    }

    if (resData.status == "error") {
      $("#Login-btn").addClass("shake");

      $("#Login-btn-label").removeClass("d-none");
      $("#Login-btn-loader").addClass("d-none");

      QueueNotification(["info", resData.message, 3000]);

      if (resData.isLocked) {
        localStorage.setItem("isLocked", resData.isLocked);
        localStorage.setItem("lockoutTime", resData.Date);
      }

      setTimeout(function () {
        $("#log-btn-lbl").text("Login").addClass("d-none");
      }, 3500);

      setTimeout(function () {
        $("#Login-btn").removeClass("shake").removeAttr("disabled");
      }, 2000);

      return;
    }

    if (resData.status == "fatal") {
      QueueNotification(["error", resData.message, 10000, "top"]);

      setTimeout(function () {
        window.location.reload();
      }, 15000);
      return;
    }

    if (resData.status == "success") {
      $("#Login-stdnum").val("");
      $("#Login-password").val("");

      $("#Login-btn-label").removeClass("d-none");
      $("#Login-btn-loader").addClass("d-none");
      $("#Login-btn-label").text("Welcome " + resData.data.FirstName + "!");

      localStorage.setItem("currentSession", resData.data.SessionID);
      localStorage.removeItem("isLocked");
      localStorage.removeItem("lockoutTime");

      if (resData.data.role == 1) {
        if (resData.isMobile == "true") {
          redirectTo = "../../Src/Pages/Feed.php";
        } else {
          redirectTo = "../../Src/Pages/Apps/ADMIN/Dashboard.php";
        }
      } else if (resData.data.role >= 2) {
        if (resData.data.accountStat == "pending") {
          QueueNotification([
            "info",
            "Your account is still pending for approval. Please wait for the admin to approve your account.",
            3000,
          ]);
          redirectTo = "../../Src/Pages/WaitingArea.php";
        } else {
          redirectTo = "../../Src/Pages/Feed.php";
        }
      }

      if (resData.message == "Login successful.") {
        setTimeout(function () {
          window.location.href = redirectTo;
        }, 1000);
      } else if (resData.message == "Auto login successful.") {
        window.location.href = redirectTo;
      } else {
        $("#log-btn-lbl").removeClass("d-none");
        $("#log-btn-lbl").text(resData.message);
        setTimeout(function () {
          $("#log-btn-lbl").text("").addClass("d-none");
          window.location.href = redirectTo;
        }, 5000);
      }
    }
  } catch (error) {
    console.error(error.message);
    $("#Login-btn").addClass("shake");

    setTimeout(function () {
      $("#Login-btn").removeClass("shake").removeAttr("disabled");
      $("#Login-btn-label").removeClass("d-none");
      $("#Login-btn-loader").addClass("d-none");
    }, 5000);

    QueueNotification([
      "error",
      "An error has occured: " + error.message,
      5000,
    ]);
  }
}

export async function FPStep1Process(data) {
  if (localStorage.getItem("ForgotStep") != null) {
    localStorage.removeItem("ForgotStep");
  }

  localStorage.setItem("ForgotStep", "1");
  const response = await fetch("../../Src/Functions/api/ResetPass.php", {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-type": "application/json; charset=UTF-8",
    },
  });

  if (!response.ok) {
    const message = `An error has occured: ${response.status}`;
    throw new Error(message);
  }

  const resData = await response.json();

  if (resData.status == "error") {
    $("#FpS1-btn").addClass("shake");

    $("#FpS1-btn-loader").addClass("d-none");
    $("#FpS1-btn-label").removeClass("d-none");
    $("#FpS1-btn-label").text(resData.message);
    $("#resendOTP").text(resData.message);

    setTimeout(function () {
      $("#FpS1-btn").removeClass("shake");
      $("#FpS1-btn").removeAttr("disabled");
      $("#FpS1-btn-label").text("Next");
      $("#resendOTP").removeAttr("disabled");
      $("#resendOTP").text("Resend OTP");
    }, 2500);

    return;
  }

  if (resData.status == "success") {
    //id otpTimer
    // get Expiry time
    var expiryTime = resData.ExpireAt; // 2024-11-02 04:30:58
    var countDownDate = new Date(expiryTime).getTime();

    // Update the count down every 1 second
    var x = setInterval(function () {
      // Get today's date and time
      var now = new Date().getTime();

      // Find the distance between now and the count down date
      var distance = countDownDate - now;

      // Time calculations for days, hours, minutes and seconds
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Display the result in the element with id="demo"
      $("#otpTimer").text(minutes + "m " + seconds + "s");

      // If the count down is finished, write some text
      if (distance < 0) {
        clearInterval(x);
        $("#otpTimer").text("EXPIRED");
        $("#resendOTP").removeAttr("disabled");
      }
    }, 1000);

    $("#resendOTP").removeAttr("disabled");
    $("#resendOTP").text("Successfully sent Token");

    setTimeout(function () {
      $("#resendOTP").text("Resend OTP");
    }, 5000);

    $("#FpS1-btn").removeAttr("disabled");
    $("#FpS1-btn-label").removeClass("d-none");
    $("#FpS1-btn-loader").addClass("d-none");

    // add data-isvalid="true" to the $("#fps1-studnum") element
    $("#fps1-studnum").attr("data-isvalid", "true");
    $("#fps1-email").attr("data-isvalid", "true");

    if (localStorage.getItem("ForgotStep") != null) {
      localStorage.removeItem("ForgotStep");
    }

    localStorage.setItem("ForgotStep", "2");

    localStorage.setItem("current-form", "Forgot");
    showCurrentForm();
  }
}

export async function FPStep2Process(data) {
  const response = await fetch("../../Src/Functions/api/ResetPass.php", {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-type": "application/json; charset=UTF-8",
    },
  });

  if (!response.ok) {
    const message = `An error has occured: ${response.status}`;
    throw new Error(message);
  }

  const resData = await response.json();

  if (resData.status == "error") {
    $("#FpS2-btn").addClass("shake");

    $("#FpS2-btn-label").removeClass("d-none");
    $("#FpS2-btn-loader").addClass("d-none");
    $("#fps2-btn-label").text(resData.message);

    setTimeout(function () {
      $("#FpS2-btn").removeClass("shake").removeAttr("disabled");
      $("#fps2-btn-label").text("Verify Token and Proceed");
    }, 2500);
    return;
  }

  if (resData.status == "success") {
    $("#FpS2-btn").removeAttr("disabled");
    $("#FpS2-btn-label").removeClass("d-none");
    $("#FpS2-btn-loader").addClass("d-none");

    if (localStorage.getItem("ForgotStep") != null) {
      localStorage.removeItem("ForgotStep");
    }

    localStorage.setItem("ForgotStep", "3");

    localStorage.setItem("current-form", "Forgot");
    showCurrentForm();
  }
}

export async function FPStep3Process(data) {
  const response = await fetch("../../Src/Functions/api/ResetPass.php", {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-type": "application/json; charset=UTF-8",
    },
  });

  if (!response.ok) {
    const message = `An error has occured: ${response.status}`;
    throw new Error(message);
  }

  const resData = await response.json();

  if (resData.status == "error") {
    $("#FpS3-btn").addClass("shake");

    $("#FpS3-btn-label").removeClass("d-none");
    $("#FpS3-btn-loader").addClass("d-none");
    $("#fps3-btn-lbl").removeClass("d-none");

    $("#fps3-btn-lbl").text(resData.message);

    setTimeout(function () {
      $("#FpS3-btn").removeClass("shake");
      $("#fps3-btn-lbl").text("");
      $("#fps3-btn-lbl").addClass("d-none");
      $("#FpS3-btn").text("Reset Password").removeAttr("disabled");
    }, 2500);

    return;
  }

  if (resData.status == "success") {
    alert(resData.message);

    $("#FpS3-btn").removeAttr("disabled");
    $("#FpS3-btn-label").removeClass("d-none");
    $("#FpS3-btn-loader").addClass("d-none");

    if (localStorage.getItem("ForgotStep") != null) {
      localStorage.removeItem("ForgotStep");
    }

    localStorage.setItem("current-form", "Login");
    window.location.reload();
  }
}

function PageReady() {
  $("#main-content").removeClass("d-none");
  $("#loader").addClass("d-none");
}

export function showCurrentForm() {
  switch (localStorage.getItem("current-form")) {
    case "Login":
      /* if () {
                if (confirm("Ready to leave? Don’t forget to save your details, or your future self might have to fill it all out again!")) {
                    localStorage.setItem("current-form", "Register");
                    showCurrentForm();
                    return;
                }
            } */

      $("#Reg-stdnum").val("");

      $("#Login-container").removeClass("d-none");
      $("#Register-container").addClass("d-none");
      $("#Forgot-Step1-container").addClass("d-none");
      $("#Forgot-Step2-container").addClass("d-none");
      $("#Forgot-Step3-container").addClass("d-none");

      $("#side-login").addClass("selected");
      $("#side-register").removeClass("selected");
      $("#side-forgot").removeClass("selected");

      $("#b_ItemNav_Log").addClass("nav-active");
      $("#b_ItemNav_Reg").removeClass("nav-active");
      $("#b_ItemNav_For").removeClass("nav-active");

      $("#b_Navbar").removeClass("d-none");

      PageReady();
      break;
    case "Register":
      /* if (ReghasData()) {
                window.onbeforeunload = function () {
                    return "Just Leave you fucking shit";
                };
            } */

      $("#Register-container").removeClass("d-none");
      $("#Login-container").addClass("d-none");
      $("#Forgot-Step1-container").addClass("d-none");
      $("#Forgot-Step1-container").addClass("d-none");
      $("#Forgot-Step2-container").addClass("d-none");
      $("#Forgot-Step3-container").addClass("d-none");

      $("#side-register").addClass("selected");
      $("#side-login").removeClass("selected");
      $("#side-forgot").removeClass("selected");

      $("#b_ItemNav_Log").removeClass("nav-active");
      $("#b_ItemNav_Reg").addClass("nav-active");
      $("#b_ItemNav_For").removeClass("nav-active");

      $("#b_Navbar").removeClass("d-none");

      PageReady();
      break;
    case "Forgot":
      /* if (ReghasData()) {
                if (confirm("Almost done! Don’t let your hard work go to waste—save your registration before you go!")) {
                    localStorage.setItem("current-form", "Register");
                    showCurrentForm();
                    return;
                }
            } */

      $("#Reg-stdnum").val("");

      // check if stdnum and email is valid

      if (
        $("#fps1-studnum").attr("data-isvalid") == "true" &&
        $("#fps1-email").attr("data-isvalid") == "true"
      ) {
        if (localStorage.getItem("ForgotStep") != null) {
          if (localStorage.getItem("ForgotStep") == "1") {
            $("#Forgot-Step1-container").toggleClass("d-none", false);
            $("#Forgot-Step2-container").toggleClass("d-none", true);
            $("#Forgot-Step3-container").toggleClass("d-none", true);
          } else if (localStorage.getItem("ForgotStep") == "2") {
            $("#Forgot-Step1-container").toggleClass("d-none", true);
            $("#Forgot-Step2-container").toggleClass("d-none", false);
            $("#Forgot-Step3-container").toggleClass("d-none", true);
          } else {
            $("#Forgot-Step1-container").toggleClass("d-none", true);
            $("#Forgot-Step2-container").toggleClass("d-none", true);
            $("#Forgot-Step3-container").toggleClass("d-none", false);
          }
        } else {
          $("#Forgot-Step1-container").toggleClass("d-none", false);
          $("#Forgot-Step2-container").toggleClass("d-none", true);
          $("#Forgot-Step3-container").toggleClass("d-none", true);
        }
      } else {
        $("#Forgot-Step1-container").toggleClass("d-none", false);
        $("#Forgot-Step2-container").toggleClass("d-none", true);
        $("#Forgot-Step3-container").toggleClass("d-none", true);
      }

      $("#Login-container").addClass("d-none");
      $("#Register-container").addClass("d-none");

      $("#side-forgot").addClass("selected");
      $("#side-login").removeClass("selected");
      $("#side-register").removeClass("selected");

      $("#b_ItemNav_Log").removeClass("nav-active");
      $("#b_ItemNav_Reg").removeClass("nav-active");
      $("#b_ItemNav_For").addClass("nav-active");

      $("#b_Navbar").removeClass("d-none");

      PageReady();
      break;
    default:
      $("#side-login").removeClass("selected");
      $("#side-register").removeClass("selected");
      $("#side-forgot").removeClass("selected");

      $("#b_ItemNav_Log").removeClass("nav-active");
      $("#b_ItemNav_Reg").removeClass("nav-active");
      $("#b_ItemNav_For").removeClass("nav-active");

      $("#b_Navbar").addClass("d-none");

      $("#main-content").addClass("d-none");
      $("#loader").removeClass("d-none");
      $("#noForm").removeClass("d-none");
      $("#noForm-label").html(
        "The code was fine until someone decided to '<b class='text-danger'>improve</b>' it. <br> <small class='text-muted mt-3'>Reverting to default form please wait...</small>"
      );

      setTimeout(function () {
        localStorage.setItem("current-form", "Login");
        showCurrentForm();
      }, 5000);

      break;
  }
}

export function changeColor() {
  if ($("#Login-stdnum").val() != "" || $("#Login-password").val() != "") {
    $("#Login-btn").removeClass("btn-secondary").addClass("btn-success");
  } else {
    $("#Login-btn").removeClass("btn-success").addClass("btn-secondary");
  }
}

export function RegisterProcess(data) {
  $.ajax({
    url: "../../Src/Functions/api/PostReguser.php",
    type: "POST",
    data: data,
    success: function (response) {
      if (response.status == "error") {
        $("#Reg-btn").addClass("shake");

        $("#Reg-btn-label").removeClass("d-none");
        $("#Reg-btn-loader").addClass("d-none");

        $("#Reg-btn-label").text(response.message);

        setTimeout(function () {
          $("#Reg-btn").removeClass("shake").removeAttr("disabled");
          $("#Reg-btn-label").text("Submit");
        }, 2500);

        return;
      }

      if (response.status == "success") {
        $("#Reg-btn").removeAttr("disabled");
        $("#Reg-btn-label").removeClass("d-none");
        $("#Reg-btn-loader").addClass("d-none");

        $("#Login-stdnum").val(data.studentnum);
        $("#Login-password").val(data.password);
        $("#log-btn").click();

        localStorage.setItem("current-form", "Login");
        showCurrentForm();
      }
    },
    error: function (error) {
      console.error(error.message);
      $("#Reg-btn").addClass("shake");

      setTimeout(function () {
        $("#Reg-btn").removeClass("shake").removeAttr("disabled");
        $("#Reg-btn-label").removeClass("d-none");
        $("#Reg-btn-loader").addClass("d-none");
      }, 5000);

      QueueNotification(["error", "An error has occured: " + error.message]);
    },
  });
}

export function ActivateProcess(data) {
  $.ajax({
    url: "../../Src/Functions/api/ActivateUser.php",
    type: "POST",
    data: data,
    success: function (response) {
      if (response.status == "error") {
        $("#act-btn").addClass("shake");

        $("#act-btn-label").removeClass("d-none");
        $("#act-btn-loader").addClass("d-none");

        $("#act-btn-label").text(response.message);

        setTimeout(function () {
          $("#act-btn").removeClass("shake").removeAttr("disabled");
          $("#act-btn-label").text("Activate");
        }, 2500);

        return;
      }

      if (response.status == "success") {
        if (response.isAccountExist == "true") {
          QueueNotification(["info", "Redirecting...", 1000]);
          setTimeout(function () {
            $("#act-btn").removeAttr("disabled");
            $("#act-btn-label").removeClass("d-none");
            $("#act-btn-loader").addClass("d-none");

            window.location.href = "./WaitingArea.php";
          }, 1500);
        } else {
          QueueNotification(["error", response.message]);
          setTimeout(function () {
            $("#act-btn").removeAttr("disabled");
            $("#act-btn-label").removeClass("d-none");
            $("#act-btn-loader").addClass("d-none");
          }, 1500);
        }
      } else {
        QueueNotification(["error", response.message]);
        setTimeout(function () {
          $("#act-btn").removeAttr("disabled");
          $("#act-btn-label").removeClass("d-none");
          $("#act-btn-loader").addClass("d-none");
        }, 1500);
      }
    },
    error: function (error) {
      console.error(error.message);
      $("#act-btn").addClass("shake");

      setTimeout(function () {
        $("#act-btn").removeClass("shake").removeAttr("disabled");
        $("#act-btn-label").removeClass("d-none");
        $("#act-btn-loader").addClass("d-none");
      }, 5000);

      QueueNotification(["error", "An error has occured: " + error.message]);
    },
  });
}
