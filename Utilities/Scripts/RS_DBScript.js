import {
  checkifISLogin,
  checkIfSessionChange,
  sessionAlert,
} from "./Modules/FeedModules.js";

$(document).ready(function () {
  setInterval(() => {
    checkifISLogin("../../../Functions/api/checkUserLogin.php");
    checkIfSessionChange("../../../Functions/api/checkSession.php");
  }, 5000);

  setInterval(() => {
    sessionAlert("../../../Functions/api/UserLogout.php?error=003");
  }, 500);

  
});
