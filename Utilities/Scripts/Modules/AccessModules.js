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
        "Last attempt was unsuccessful. Please wait " + minutes + "m " + seconds + "s"
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
      return
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

      if (resData.message == "Login successful.") {
        setTimeout(function () {
          window.location.href = "../../Src/Pages/Feed.php";
        }, 1000);
      } else if (resData.message == "Auto login successful.") {
        window.location.href = "../../Src/Pages/Feed.php";
      } else {
        $("#log-btn-lbl").removeClass("d-none");
        $("#log-btn-lbl").text(resData.message);
        setTimeout(function () {
          $("#log-btn-lbl").text("").addClass("d-none");
          window.location.href = "../../Src/Pages/Feed.php";
        }, 5000);
      }
    }
  } catch (error) {
    console.error(error.message); // log error message to console
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
  const response = await fetch("../../Src/Functions/testing.php", {
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

    $("#FpS1-btn-label").removeClass("d-none");
    $("#FpS1-btn-loader").addClass("d-none");
    $("#fps1-btn-lbl").removeClass("d-none");

    $("#fps1-btn-lbl").text(resData.message);

    setTimeout(function () {
      $("#FpS1-btn").removeClass("shake");
      $("#fps1-btn-lbl").text("");
      $("#fps1-btn-lbl").addClass("d-none");
      $("#FpS1-btn").text("Next").removeAttr("disabled");
    }, 2500);

    return;
  }

  if (resData.status == "success") {
    alert(resData.message);

    $("#FpS1-btn").removeAttr("disabled");
    $("#FpS1-btn-label").removeClass("d-none");
    $("#FpS1-btn-loader").addClass("d-none");

    $("#Forgot-Step1-container").attr("hidden", "hidden");
    $("#Forgot-Step2-container").removeClass("d-none");

    localStorage.setItem("current-form", "Forgot");
    showCurrentForm();
  }
}

export async function FPStep2Process(data) {
  const response = await fetch("../../Src/Functions/testing.php", {
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
    $("#fps2-btn-lbl").removeClass("d-none");

    $("#fps2-btn-lbl").text(resData.message);

    setTimeout(function () {
      $("#FpS2-btn").removeClass("shake");
      $("#fps2-btn-lbl").text("");
      $("#fps2-btn-lbl").addClass("d-none");
      $("#FpS2-btn").text("Reset Password").removeAttr("disabled");
    }, 2500);

    return;
  }

  if (resData.status == "success") {
    alert(resData.message);

    $("#FpS2-btn").removeAttr("disabled");
    $("#FpS2-btn-label").removeClass("d-none");
    $("#FpS2-btn-loader").addClass("d-none");

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

      $("#Forgot-Step1-container").removeClass("d-none");
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
