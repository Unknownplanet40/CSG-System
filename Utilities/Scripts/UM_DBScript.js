import { QueueNotification } from "./Modules/Queueing_Notification.js";

const tooltipTriggerList = document.querySelectorAll(
  '[data-bs-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
  (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
);

function uuidv4() {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
    var r = (Math.random() * 16) | 0,
      v = c == "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

let userTempMail = "";
let userTempPass = "";

$(document).ready(function () {
  let CSGSelectedPositions = [];
  let OfficerSelectedPositions = [];

  $("#MultipleAccounts_0").removeClass("d-none");

  for (let i = 0; i < 10; i++) {
    (function (index) {
      $("#Role_Select_" + index).change(function () {
        let role = $(this).val();
        let posSelect = $("#Pos_Select_" + index);
        if (role === "1") {
          posSelect.prop("disabled", true).val("null");
          toggleNextAccount(index, "null");
        } else if (role !== "null") {
          posSelect.prop("disabled", false);
          updatePositionOptions(role, posSelect, index);
        }
      });

      $("#Pos_Select_" + index).change(function () {
        let role = $("#Role_Select_" + index).val();
        let pos = $(this).val();

        if (pos !== "null") {
          if (role === "2" && !CSGSelectedPositions.includes(pos)) {
            CSGSelectedPositions.push(pos);
          } else if (role === "3" && !OfficerSelectedPositions.includes(pos)) {
            OfficerSelectedPositions.push(pos);
          }

          toggleNextAccount(index, pos);
          updatePositionOptions(
            role,
            $("#Pos_Select_" + (index + 1)),
            index + 1
          );
        }
      });

      function toggleNextAccount(currentIndex, pos) {
        let nextIndex = currentIndex + 1;
        if (nextIndex < 10) {
          $("#MultipleAccounts_" + nextIndex).removeClass("d-none");
        }
      }

      function updatePositionOptions(role, posSelect, index) {
        posSelect.find("option").prop("disabled", false);

        if (role === "2") {
          CSGSelectedPositions.forEach(function (selectedPos) {
            posSelect
              .find('option[value="' + selectedPos + '"]')
              .prop("disabled", true);
          });
        } else if (role === "3") {
          OfficerSelectedPositions.forEach(function (selectedPos) {
            posSelect
              .find('option[value="' + selectedPos + '"]')
              .prop("disabled", true);
          });
        }
      }
    })(i);
  }

  $("#CreateMultiple_Btn").click(function () {
    let accounts = [];
    for (let i = 0; i < 10; i++) {
      let role = $("#Role_Select_" + i).val();
      let pos = $("#Pos_Select_" + i).val();

      if (role !== "null") {
        if (role > 1 && pos !== "null") {
          accounts.push({
            role: role,
            pos: pos,
          });
        } else if (role == 1) {
          accounts.push({
            role: role,
            pos: null,
          });
        }
      }
    }

    if (accounts.length >= 2) {
      $.ajax({
        type: "POST",
        url: "../../../Functions/api/postTempAccount.php",
        data: {
          action: "multi-create",
          accounts: accounts,
        },
        success: function (response) {
          if (response.stat === "success") {
            if (response.ZipPath) {
              var dlBtn = $("#DL_zip_Btn");
              dlBtn.attr("href", `../../Src/TempPDF/${response.ZipPath}`);
              dlBtn.removeClass("d-none");
              dlBtn.attr("download", response.ZipPath);

              // clear all fields
              for (let i = 0; i < 10; i++) {
                $("#Role_Select_" + i).val("null");
                $("#Pos_Select_" + i).val("null");
              }

              // hide all MultipleAccounts divs except the first one
              for (let i = 1; i < 10; i++) {
                $("#MultipleAccounts_" + i).addClass("d-none");
              }

              //clear account arrays
              CSGSelectedPositions = [];
              OfficerSelectedPositions = [];
              accounts = [];

              $("#cococlose").data("zip-path", response.ZipPath);
            } else {
              QueueNotification([
                "error",
                "An error occurred while creating the accounts.",
                3000,
              ]);
            }
          } else {
            QueueNotification(["error", response.message, 3000]);
          }
        },
      });
    } else {
      QueueNotification([
        "error",
        "If you are creating a single account, please close this modal and use the main form.",
        3000,
      ]);
    }
  });

  $("#GenTemp_Btn").click(function () {
    var uuid = uuidv4();
    var TempMail = $("#TempMail_Input").val();
    var TempPass = $("#TempPass_Input").val();

    var TempMail =
      Math.random().toString(36).substring(2, 10) + "@cvsu.temp.com";
    var TempPass = Math.random().toString(36).substring(2, 10) + "Aa1!";

    $("#UUID_Input").val(uuid);
    $("#TempMail_Input").val(TempMail);
    $("#TempPass_Input").val(TempPass);

    $("#print_Btn").prop("disabled", false);
  });

  $("#print_Btn").click(function () {

    if (userTempMail == "" || userTempPass == "") {
      QueueNotification(["error", "Please generate a temporary account first.", 3000]);
      return;
    } else if (userTempMail == "Expired" || userTempPass == "Expired") {
      QueueNotification(["error", "Temporary account has expired. Please generate a new one.", 3000]);
      return;
    }

    window.location.href = `../../../Functions/TempAccount_Gen.php?Email=${userTempMail}&Password=${userTempPass}`;
  });

  $("#CreateAccount_Btn").click(function () {
    var UUID = $("#UUID_Input").val();
    var Role = $("#Role_Select").val();
    var Pos = $("#Pos_Select").val();
    var TempMail = $("#TempMail_Input").val();
    var TempPass = $("#TempPass_Input").val();

    if (
      UUID == "" ||
      Role == null ||
      TempMail == "" ||
      TempPass == ""
    ) {
      QueueNotification(["error", "Please fill up all the fields.", 3000]);
      return;
    }

    var data = {
      action: "create",
      uuid: UUID,
      role: Role,
      pos: Pos,
      email: TempMail,
      password: TempPass,
      accounts: {
        role: Role,
        pos: Pos,
      },
    };

    $.ajax({
      type: "POST",
      url: "../../../Functions/api/postTempAccount.php",
      data: data,
      success: function (response) {
        if (response.stat === "success") {
          QueueNotification(["info", "Account has been created.", 3000]);

          userTempMail = TempMail;
          userTempPass = TempPass;
          window.location.href = `../../../Functions/TempAccount_Gen.php?Email=${TempMail}&Password=${TempPass}`;
          
          $("#UUID_Input").val("");
          $("#Role_Select").val("null");
          $("#Pos_Select").val("null").attr("disabled", false);
          $("#TempMail_Input").val("");
          $("#TempPass_Input").val("");
          $("#print_Btn").removeClass("d-none");

          setTimeout(function () {
            $("#print_Btn").prop("disabled", false);
            userTempMail = "Expired";
            userTempPass = "Expired";
            QueueNotification(["error", "Temporary account has been expired.", 3000]);
          }, 300000); // 5 minutes
        } else {
          QueueNotification(["error", response.message, 3000]);
        }
      },
    });
  });

  $("#cococlose").click(function () {
    console.log($(this).data("zip-path"));

    if ($(this).data("zip-path") !== undefined) {
      $.ajax({
        type: "POST",
        url: "../../../Functions/api/postTempAccount.php",
        data: {
          action: "delete-zip",
          zipname: $(this).data("zip-path"),
        },
        success: function (response) {
          if (response.stat === "success") {
            QueueNotification(["info", "Zip has been deleted.", 3000]);
          } else {
            console.log("error", response.message, 3000);
          }
        },
      });
    }
  });
});
