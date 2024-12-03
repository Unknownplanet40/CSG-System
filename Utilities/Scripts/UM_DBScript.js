import { QueueNotification } from "./Modules/Queueing_Notification.js";
import {
  checkifISLogin,
  checkIfSessionChange,
  sessionAlert,
} from "./Modules/FeedModules.js";

const tooltipTriggerList = document.querySelectorAll(
  '[data-bs-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
  (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
);

const UserMods = new bootstrap.Modal("#UserEdModal", {
  keyboard: false,
  backdrop: "static",
});

const AdminMods = new bootstrap.Modal("#AdminEdModal", {
  keyboard: false,
  backdrop: "static",
});

function uuidv4() {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
    var r = (Math.random() * 16) | 0,
      v = c == "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

let adminAPI =
  "../../../Functions/api/getAccounts.php?type=admin&status=active";
let userAPI = "../../../Functions/api/getAccounts.php?type=user&status=active";

$("#NUP-tab").click(function () {
  localStorage.setItem("tab", "NUP-tab");
});

$("#user-tab").click(function () {
  localStorage.setItem("tab", "user-tab");
});

$("#admin-tab").click(function () {
  localStorage.setItem("tab", "admin-tab");
});

if (localStorage.getItem("tab")) {
  $("#" + localStorage.getItem("tab")).tab("show");
}
function GetCourse() {
  $.ajax({
    url: "../../../Functions/api/getAcadData.php",
    type: "POST",
    data: {
      CourseID: null,
      Action: "Get-Course",
    },

    success: function (res) {
      var old = $("#inputCourse").attr("data-old");
      if (res.status === "success") {
        $("#inputCourse").empty();
        $("#inputCourse").append(`<option hidden>Choose...</option>`);
        res.data.forEach((courses) => {
          if (courses.CourseID === old) {
            $("#inputCourse").append(
              `<option value="${courses.CourseID}" selected>${courses.CourseName}</option>`
            );
          } else {
            $("#inputCourse").append(
              `<option value="${courses.CourseID}">${courses.CourseName}</option>`
            );
          }
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
}
function GetYearlvlv(CourseID) {
  var year = $("#inputYear");
  $.ajax({
    url: "../../../Functions/api/getAcadData.php",
    type: "POST",
    data: {
      CourseID: CourseID,
      Action: "Get-Year",
    },

    success: function (res) {
      if (res.status === "success") {
        year.empty();
        year.append(`<option hidden>Choose...</option>`);
        res.data.forEach((courses) => {
          if (
            parseInt(courses.Year) ===
            parseInt($("#inputYear").attr("data-old"))
          ) {
            year.append(
              `<option value="${courses.Year}" selected>${courses.CourseName}</option>`
            );
          } else {
            year.append(
              `<option value="${courses.Year}">${courses.CourseName}</option>`
            );
          }
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
}

function GetSection(CourseID, YearLevel) {
  var section = $("#inputsection");
  $.ajax({
    url: "../../../Functions/api/getAcadData.php",
    type: "POST",
    data: {
      CourseID: CourseID,
      Action: "Get-Section",
      YearLevel: YearLevel,
    },

    success: function (res) {
      var old = $("#inputsection").attr("data-old");
      if (res.status === "success") {
        section.empty();
        section.append(`<option hidden>Choose...</option>`);
        res.data.forEach((courses) => {
          if (courses.Section === old) {
            section.append(
              `<option value="${courses.CourseName}" data-course-code="${courses.code}" selected>${courses.Section}</option>`
            );
          } else {
            section.append(
              `<option value="${courses.CourseName}" data-course-code="${courses.code}">${courses.Section}</option>`
            );
          }
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
}

function GetPosition(position = "null") {
  var pos = $("#inputPosition");
  var old = pos.attr("data-old");
  if (pos.length === 0) return;

  pos.empty();
  const positions = [
    { value: "null", text: "Choose...", isHidden: true },
    { value: "1", text: "President", isHidden: false },
    { value: "2", text: "Vice President (Internal)", isHidden: false },
    { value: "3", text: "Vice President (External)", isHidden: false },
    { value: "4", text: "Secretary", isHidden: false },
  ];

  positions.forEach((item) => {
    const isSelected = item.value === old;
    pos.append(
      `<option value="${item.value}" ${isSelected ? "selected" : ""} ${
        item.isHidden ? "hidden" : ""
      }>${item.text}</option>`
    );
  });
}

$("#inputCourse").change(function () {
  GetYearlvlv($(this).val());
  GetSection($(this).val(), $("#inputYear").val());
});

$("#inputYear").change(function () {
  GetSection($("#inputCourse").val(), $(this).val());
});

let userTempMail = "";
let userTempPass = "";

let funnyLogoutButtons = [
  "Bye Felicia!",
  "Adios, Amigos!",
  "Peace Out!",
  "I’m Outta Here!",
  "Log Out and Chill",
  "Escape!",
  "Poof, Gone!",
  "See Ya, Wouldn’t Wanna Be Ya!",
  "Ninja Vanish!",
  "Take the Exit Ramp!",
];

let funnyDeleteButtons = [
  "Hasta la vista, Data!",
  "Delete and Duck!",
  "Erase Like Nobody's Watching",
  "Gone with the Bits!",
  "Into the Digital Void!",
  "Data-B-Gone!",
  "Poof, Begone!",
  "Delete-a-roo!",
  "Digital Dustbin Time!",
  "Memory Muncher!",
];

$(document).ready(function () {
  setInterval(() => {
    checkifISLogin(
      "../../../Functions/api/checkUserLogin.php",
      "../../../Functions/api/UserLogout.php?error=001"
    );
    checkIfSessionChange("../../../Functions/api/checkSession.php");
  }, 5000);

  setInterval(() => {
    sessionAlert("../../../Functions/api/UserLogout.php?error=003");
  }, 500);

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
      QueueNotification([
        "error",
        "Please generate a temporary account first.",
        3000,
      ]);
      return;
    } else if (userTempMail == "Expired" || userTempPass == "Expired") {
      QueueNotification([
        "error",
        "Temporary account has expired. Please generate a new one.",
        3000,
      ]);
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

    if (UUID == "" || Role == null || TempMail == "" || TempPass == "") {
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
            QueueNotification([
              "error",
              "Temporary account has been expired.",
              3000,
            ]);
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

  $("#UserTable").DataTable({
    layout: {
      topStart: {
        search: {
          placeholder: "Find an account",
        },
        buttons: [
          {
            extend: "print",
            text: "Print Table",
            title: "USER ACCOUNTS",
            filename:
              "Users_Accounts_" +
              new Date().toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
              }),
            pageSize: "legal",
            orientation: "landscape",
            messageTop:
              "Generated on: " +
              new Date().toLocaleDateString("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric",
              }) +
              " " +
              new Date().toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
              }),
            exportOptions: {
              columns: [1,2,3,4,5,6,7],
            },
          },
        ],
      },
      topEnd: {
        info: true,
      },
      bottomStart: function () {
        return $(
          '<select id="usertype" class="form-select"><option value="active">Active</option><option value="pending">Pending</option><option value="archived">Archived</option></select>'
        )
      },
    },
    responsive: true,
    autoWidth: false,
    order: [[0, "desc"]],
    ordering: false,
    language: {
      emptyTable: function () {
        if ($("#usertype").val() === "active") {
          return "<span class='text-body'>Currently no <b>ACTIVE</b> user accounts can be found.</span>";
        } else if ($("#usertype").val() === "pending") {
          return "<span class='text-body'>Currently no <b>PENDING</b> user accounts can be found.</span>";
        } else {
          return "<span class='text-body'>Currently no <b>ARCHIVED</b> user accounts can be found.</span>";
        }
      }
    },
    ajax: {
      url: userAPI,
      dataSrc: "data",
    },

    columns: [
      { data: "UUID" },
      { data: "fullName" },
      { data: "student_Number" },
      { data: null,
        render: function (data) {
          return `<p class='text-truncate' style='max-width: 128px; cursor: pointer;' data-bs-toggle='popover' data-bs-trigger='hover' data-bs-placement='top' data-bs-title="Email Address" data-bs-content='${data.primary_email}'><span class='badge rounded-1 bg-secondary d-print-none'>View</span><span class='d-none d-print-block'>${data.primary_email}</span></p>`;
        }
      },
      { data: "contactNumber" },
      { data: "course_code" },
      { data: null,
        render: function (data) {
          if (data.org_position === "1") {
            return `<p class='text-truncate' style='max-width: 128px; cursor: pointer;' data-bs-toggle='popover' data-bs-trigger='hover' data-bs-placement='top' data-bs-title="Position and Term Status" data-bs-content='Position: ${data.org_code} President | ${data.isTermComplete === 0 ? "Ongoing" : "Ended"}'><span class='d-print-none'>${data.org_code} &#10148;</span><span class='d-none d-print-block'>${data.org_code} President</span></p>`;
          } else if (data.org_position === "2") {
            return `<p class='text-truncate' style='max-width: 128px; cursor: pointer;' data-bs-toggle='popover' data-bs-trigger='hover' data-bs-placement='top' data-bs-title="Position and Term Status" data-bs-content='${data.org_code} Vice President for Internal Affairs | ${data.isTermComplete == 0 ? "Ongoing" : "Ended"}'><span class='d-print-none'>${data.org_code} &#10148;</span><span class='d-none d-print-block'>${data.org_code} Vice President for Internal Affairs</span></p>`;
          } else if (data.org_position === "3") {
            return `<p class='text-truncate' style='max-width: 128px; cursor: pointer;' data-bs-toggle='popover' data-bs-trigger='hover' data-bs-placement='top' data-bs-title="Position and Term Status" data-bs-content='${data.org_code} Vice President for External Affairs | ${data.isTermComplete == 0 ? "Ongoing" : "Ended"}'><span class='d-print-none'>${data.org_code} &#10148;</span><span class='d-none d-print-block'>${data.org_code} Vice President for External Affairs</span></p>`;
          } else if (data.org_position === "4") {
            return `<p class='text-truncate' style='max-width: 128px; cursor: pointer;' data-bs-toggle='popover' data-bs-trigger='hover' data-bs-placement='top' data-bs-title="Position and Term Status" data-bs-content='${data.org_code} Secretary | ${data.isTermComplete == 0 ? "Ongoing" : "Ended"}'><span class='d-print-none'>${data.org_code} &#10148;</span><span class='d-none d-print-block'>${data.org_code} Secretary</span></p>`;
          } else {
            return "<span class='p-2 py-1 rounded-5 text-bg-secondary'>Not Assigned</span>";
          }
        }
       },
      { data: "isTermComplete",
        render: function (data) {
          if (data.isTermComplete === "1") {
            return "<span class='badge text-bg-success'>Yes</span>";
          } else {
            return "<span class='badge text-bg-info'>No</span>";
          }
        },
      },
      {
        data: "accountStat",
        render: function (data) {
          if (data.accountStat !== "archived") {
            if (data.isLocked) {
              return "<span class='badge bg-warning'>Locked</span>";
            } else {
              return "<span class='badge bg-success'>Active</span>";
            }
          } else {
            return "<span class='badge bg-danger'>Archived</span>";
          }
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          return `<button class="btn btn-sm btn-primary rounded-0 bg-transparent text-body border-0 text-uppercase">
                    <svg width="24" height="24" class="text-body fw-bold">
                      <use xlink:href="#Edit"></use>
                    </svg>
                  </button>`;
        },
      },
    ],

    columnDefs: [
      {
        targets: [0,7],
        visible: false,
      },
      {
        targets: 1,
        width: "20%",
        render: function (data, type, row) {
          return (
            "<span class='d-flex align-items-center text-capitalize text-truncate' style='white-space: nowrap;'>" +
            data +
            "</span>"
          );
        },
      },
      {
        targets: 7,
        render: function (data, type, row) {
          return data == "active"
            ? `<span class="badge bg-success">Active</span>`
            : data == "pending"
            ? `<span class="badge bg-warning">Pending</span>`
            : `<span class="badge bg-danger">Archived</span>`;
        },
      },
    ],
    initComplete: function () {
      var table = this;
      $("#UserTable").on("click", "button", function () {
        var data = table.api().row($(this).parents("tr")).data();
        $("#Edituser_ID").val(data.UUID);
        $("#inputFName").val(data.First_Name);
        $("#inputLName").val(data.Last_Name);
        $("#inputStudNum").val(data.student_Number);
        $("#inputEmail").val(data.primary_email);
        $("#inputContact").val(data.contactNumber);

        var courseCode = data.course_code || "";
        var parts = courseCode.split("-");
        var courseName = parts[0].trim() || "";
        var courseYear = parts[1] ? parts[1].slice(0, -1).trim() : "";
        var courseSection = parts[1] ? parts[1].slice(-1).trim() : "";
        var termchoice = $("#inputTerm");
        termchoice.empty();
        if (data.isTermComplete === 0) {
          termchoice.append(`<option value="1" selected>Ongoing</option>`);
          termchoice.append(`<option value="2">Ended</option>`);
        } else if (data.isTermComplete === 1) {
          termchoice.append(`<option value="1">Ongoing</option>`);
          termchoice.append(`<option value="2" selected>Ended</option>`);
        } else {
          termchoice.append(`<option selected hidden disabled>Select Term Status</option>`);
          termchoice.append(`<option value="1">Ongoing</option>`);
          termchoice.append(`<option value="2">Ended</option>`);
        }

        $("#inputCourse").attr("data-old", courseName);
        GetCourse();
        $("#inputYear").attr("data-old", courseYear);
        GetYearlvlv(courseName);
        $("#inputsection").attr("data-old", courseSection);
        GetSection(courseName, courseYear);
        $("#inputPosition").attr("data-old", data.org_position);
        GetPosition(data.org_position);

        if (data.accountStat === "pending") {
          $("#userSC")
            .prop("disabled", true)
            .text("Can't update pending account");
        } else if (data.accountStat === "archived") {
          $("#userRes")
            .prop("disabled", false)
            .text("Restore Account")
            .removeClass("d-none");
          $("#userSC").prop("disabled", true).text("Restore first");
          $("#userDel").prop("disabled", true).addClass("d-none");
        } else {
          $("#userSC").prop("disabled", false).text("Save Changes");
          $("#userRes")
            .prop("disabled", true)
            .text("Restore Account")
            .addClass("d-none");
          $("#userDel").prop("disabled", false);
        }
        UserMods.show();
      });

      $("#usertype").change(function () {
        console.log($(this).val());
        var val = $(this).val();
        if (val === "active") {
          userAPI = `../../../Functions/api/getAccounts.php?type=user&status=${val}`;
        } else {
          userAPI = `../../../Functions/api/getAccounts.php?type=user&status=${val}`;
        }
        table.api().ajax.url(userAPI).load();
      });
    },
  });

  $("#AdminTable").DataTable({
    layout: {
      topStart: {
        search: {
          placeholder: "Find an account",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: function () {
        return $(
          '<select id="utype" class="form-select"><option value="active">Active</option><option value="pending">Pending</option><option value="archived">Archived</option></select>'
        );
      },
    },
    responsive: true,
    autoWidth: false,
    order: [[6, "desc"]],
    ordering: false,

    ajax: {
      url: adminAPI,
      dataSrc: "data",
    },

    columns: [
      { data: "UUID" },
      { data: "fullName" },
      { data: "student_Number" },
      { data: "primary_email" },
      { data: "contactNumber" },
      { data: "course_code" },
      {
        data: null,
        render: function (data) {
          if (data.accountStat !== "archived") {
            if (data.isLocked) {
              return "<span class='badge bg-warning'>Locked</span>";
            } else {
              return "<span class='badge bg-success'>Active</span>";
            }
          } else {
            return "<span class='badge bg-danger'>Archived</span>";
          }
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          return `<button class="btn btn-sm btn-primary rounded-0 bg-transparent text-body border-0 text-uppercase">
                    <svg width="24" height="24" class="text-body fw-bold">
                      <use xlink:href="#Edit"></use>
                    </svg>
                  </button>`;
        },
      },
    ],

    columnDefs: [
      {
        targets: 0,
        visible: false,
        searchable: false,
      },
      {
        targets: 1,
        width: "20%",
        render: function (data, type, row) {
          return (
            "<span class='text-capitalize text-truncate' style='white-space: nowrap;'>" +
            data +
            "</span>"
          );
        },
      },
      {
        target: 5,
        visible: false,
      },
      {
        targets: 6,
        render: function (data) {
          return data === "active"
            ? `<span class="badge bg-success">Active</span>`
            : data === "pending"
            ? `<span class="badge bg-warning">Pending</span>`
            : `<span class="badge bg-danger">Archived</span>`;
        },
      },
    ],

    initComplete: function () {
      var table = this;
      $("#AdminTable").on("click", "button", function () {
        var data = table.api().row($(this).parents("tr")).data();
        $("#Editadmin_ID").val(data.UUID);
        $("#inputFName_admin").val(data.First_Name);
        $("#inputLName_admin").val(data.Last_Name);
        $("#inputStudNum_admin").val(data.student_Number);
        $("#inputEmail_admin").val(data.primary_email);
        $("#inputContact_admin").val(data.contactNumber);

        if (data.accountStat === "pending") {
          $("#adminSC")
            .prop("disabled", true)
            .text("Can't update pending account");
        } else if (data.accountStat === "archived") {
          $("#adminRes")
            .prop("disabled", false)
            .text("Restore Account")
            .removeClass("d-none");
          $("#adminSC").prop("disabled", true).text("Restore first");
          $("#adminDel").prop("disabled", true).addClass("d-none");
        } else if (data.UUID === UUID) {
          $("#adminSC").prop("disabled", false).text("Update your account");
          $("#adminRes").prop("disabled", true).addClass("d-none");
          $("#adminDel").prop("disabled", true).addClass("d-none");
        } else {
          $("#adminSC").prop("disabled", false).text("Save Changes");
          $("#adminRes").prop("disabled", true).addClass("d-none");
          $("#adminDel").prop("disabled", false).removeClass("d-none");
        }

        AdminMods.show();
      });

      $("#utype").change(function () {
        console.log($(this).val());
        var val = $(this).val();
        if (val === "active") {
          adminAPI = `../../../Functions/api/getAccounts.php?type=admin&status=${val}`;
        } else if (val === "pending") {
          adminAPI = `../../../Functions/api/getAccounts.php?type=admin&status=${val}`;
        } else {
          adminAPI = `../../../Functions/api/getAccounts.php?type=admin&status=${val}`;
        }

        table.api().ajax.url(adminAPI).load();
      });
    },
  });

  $("#AdminTable").on("draw.dt", function () {
    const tooltipTriggerList = document.querySelectorAll(
      '[data-bs-toggle="tooltip"]'
    );
    const tooltipList = [...tooltipTriggerList].map(
      (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
    );
    const popoverTriggerList = document.querySelectorAll(
      '[data-bs-toggle="popover"]'
    );
    const popoverList = [...popoverTriggerList].map(
      (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
    );
  });

  $("#UserTable").on("draw.dt", function () {
    const tooltipTriggerList = document.querySelectorAll(
      '[data-bs-toggle="tooltip"]'
    );
    const tooltipList = [...tooltipTriggerList].map(
      (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
    );
    const popoverTriggerList = document.querySelectorAll(
      '[data-bs-toggle="popover"]'
    );
    const popoverList = [...popoverTriggerList].map(
      (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
    );
  });

  $(".buttons-pdf, .buttons-print")
    .removeClass("btn-secondary")
    .addClass("btn-outline-success btn-sm");

  $("#userSC").click(function () {
    var ID = $("#Edituser_ID").val();
    var FName = $("#inputFName").val();
    var LName = $("#inputLName").val();
    var StudNum = $("#inputStudNum").val();
    var Email = $("#inputEmail").val();
    var Contact = $("#inputContact").val();
    var Org = $("#inputOrg").val();
    var Pos = $("#inputPosition").val();
    var Section = $("#inputsection option:selected").data("course-code");
    var password = $("#inputpassword").val();

    if (FName == "" || LName == "") {
      $("#userSCMsg").text("Please fill up all fields");
      setTimeout(() => {
        $("#userSCMsg").text("");
      }, 3000);
      return;
    }

    if (Email == "") {
      $("#userSCMsg").text("Please fill up all fields");
      setTimeout(() => {
        $("#userSCMsg").text("");
      }, 3000);
      return;
    } else if (Email.indexOf("@") == -1 || Email.indexOf(".") == -1) {
      $("#userSCMsg").text("Invalid Email Address");
      setTimeout(() => {
        $("#userSCMsg").text("");
      }, 3000);
      return;
      // not @cvsu.edu.ph
    } else if (Email.indexOf("@cvsu.edu.ph") == -1) {
      $("#userSCMsg").text("Can't use email outside of cvsu Organization");
      setTimeout(() => {
        $("#userSCMsg").text("");
      }, 3000);
      return;
    }

    if (Contact == "") {
      $("#userSCMsg").text("Please fill up all fields");
      setTimeout(() => {
        $("#userSCMsg").text("");
      }, 3000);
      return;
    } else if (Contact.length != 11 || Contact.indexOf("09") == -1) {
      $("#userSCMsg").text("Invalid Contact Number");
      setTimeout(() => {
        $("#userSCMsg").text("");
      }, 3000);
      return;
    } else if (isNaN(Contact)) {
      $("#userSCMsg").text("Invalid Contact Number");
      setTimeout(() => {
        $("#userSCMsg").text("");
      }, 3000);
      return;
    }

    var data = {
      action: "user-update",
      UUID: ID,
      First_Name: FName,
      Last_Name: LName,
      student_Number: StudNum,
      primary_email: Email,
      contactNumber: Contact,
      org_code: Org,
      org_position: Pos,
      course_code: Section,
      password: password,
    };

    $.ajax({
      type: "POST",
      url: "../../../Functions/api/updateAccounts.php",
      data: data,
      success: function (response) {
        if (response.stat === "success") {
          QueueNotification(["info", "Account has been updated.", 3000]);
          $("#UserTable").DataTable().ajax.reload();
          UserMods.hide();
        } else {
          QueueNotification(["error", response.message, 3000]);
        }
      },
    });
  });

  $("#userDel").click(function () {
    var ID = $("#Edituser_ID").val();

    swal
      .fire({
        title: "Are you sure?",
        text: "You can still recover this account in the archive.",
        icon: "info",
        confirmButtonText:
          funnyDeleteButtons[
            Math.floor(Math.random() * funnyDeleteButtons.length)
          ],
        confirmButtonColor: "#d33",
        customClass: {
          popup: "alert-popup-inform",
          confirmButton: "alert-button-confirm",
          container: "alert-container",
          htmlContainer: "alert-html-container",
          title: "alert-title",
        },
      })
      .then((result) => {
        if (result.isConfirmed) {
          var data = {
            action: "user-delete",
            UUID: ID,
          };

          $.ajax({
            type: "POST",
            url: "../../../Functions/api/updateAccounts.php",
            data: data,
            success: function (response) {
              if (response.stat === "success") {
                QueueNotification([
                  "info",
                  "Account has move to archive.",
                  3000,
                ]);
                $("#UserTable").DataTable().ajax.reload();
                UserMods.hide();
              } else {
                QueueNotification(["error", response.message, 3000]);
              }
            },
          });
        }
      });
  });

  $("#userRes").click(function () {
    var ID = $("#Edituser_ID").val();

    var data = {
      action: "user-restore",
      UUID: ID,
    };

    $.ajax({
      type: "POST",
      url: "../../../Functions/api/updateAccounts.php",
      data: data,
      success: function (response) {
        if (response.stat === "success") {
          QueueNotification(["info", "Account has been restored.", 3000]);
          userAPI =
            "../../../Functions/api/getAccounts.php?type=user&status=active";
          $("#UserTable").DataTable().ajax.reload();
          UserMods.hide();
        } else {
          QueueNotification(["error", response.message, 3000]);
        }
      },
    });
  });

  $("#adminSC").click(function () {
    var ID = $("#Editadmin_ID").val();
    var FName = $("#inputFName_admin").val();
    var LName = $("#inputLName_admin").val();
    var StudNum = $("#inputStudNum_admin").val();
    var Email = $("#inputEmail_admin").val();
    var Contact = $("#inputContact_admin").val();
    var password = $("#inputpassword_admin").val();

    if (FName == "" || LName == "") {
      $("#adminSCMsg").text("Please fill up all fields");
      setTimeout(() => {
        $("#adminSCMsg").text("");
      }, 3000);
      return;
    }

    if (Email == "") {
      $("#adminSCMsg").text("Please fill up all fields");
      setTimeout(() => {
        $("#adminSCMsg").text("");
      }, 3000);
      return;
    } else if (Email.indexOf("@") == -1 || Email.indexOf(".") == -1) {
      $("#adminSCMsg").text("Invalid Email Address");
      setTimeout(() => {
        $("#adminSCMsg").text("");
      }, 3000);
      return;
      // not @cvsu
    } else if (Email.indexOf("@cvsu.edu.ph") == -1) {
      $("#adminSCMsg").text("Can't use email outside of cvsu Organization");
      setTimeout(() => {
        $("#adminSCMsg").text("");
      }, 3000);
      return;
    }

    if (Contact == "") {
      $("#adminSCMsg").text("Please fill up all fields");
      setTimeout(() => {
        $("#adminSCMsg").text("");
      }, 3000);
      return;
    } else if (Contact.length != 11 || Contact.indexOf("09") == -1) {
      $("#adminSCMsg").text("Invalid Contact Number");
      setTimeout(() => {
        $("#adminSCMsg").text("");
      }, 3000);
      return;
    } else if (isNaN(Contact)) {
      $("#adminSCMsg").text("Invalid Contact Number");
      setTimeout(() => {
        $("#adminSCMsg").text("");
      }, 3000);
      return;
    }

    var data = {
      action: "admin-update",
      UUID: ID,
      First_Name: FName,
      Last_Name: LName,
      student_Number: StudNum,
      primary_email: Email,
      contactNumber: Contact,
      password: password,
    };

    $.ajax({
      type: "POST",
      url: "../../../Functions/api/updateAccounts.php",
      data: data,
      success: function (response) {
        if (response.stat === "success") {
          QueueNotification(["info", "Account has been updated.", 3000]);
          $("#AdminTable").DataTable().ajax.reload();

          AdminMods.hide();
          if (response.isneedtorelogin) {
            QueueNotification([
              "warning",
              "You will be automatically logged out in 5 seconds.",
              5000,
            ]);
            setTimeout(() => {
              window.location.href = "../../../Functions/api/UserLogout.php";
            }, 10000);
          }
        } else {
          QueueNotification(["error", response.message, 3000]);
        }
      },
    });
  });

  $("#adminDel").click(function () {
    var ID = $("#Editadmin_ID").val();
    swal
      .fire({
        title: "Are you sure?",
        text: "You can still recover this account in the archive.",
        icon: "info",
        confirmButtonText:
          funnyDeleteButtons[
            Math.floor(Math.random() * funnyDeleteButtons.length)
          ],
        allowOutsideClick: false,
        confirmButtonColor: "#d33",
        customClass: {
          popup: "alert-popup-inform",
          confirmButton: "alert-button-confirm",
          container: "alert-container",
          htmlContainer: "alert-html-container",
          title: "alert-title",
        },
      })
      .then((result) => {
        if (result.isConfirmed) {
          var data = {
            action: "admin-delete",
            UUID: ID,
          };

          $.ajax({
            type: "POST",
            url: "../../../Functions/api/updateAccounts.php",
            data: data,
            success: function (response) {
              if (response.stat === "success") {
                QueueNotification([
                  "info",
                  "Account has move to archive.",
                  3000,
                ]);
                $("#AdminTable").DataTable().ajax.reload();
                AdminMods.hide();
              } else {
                QueueNotification(["error", response.message, 3000]);
              }
            },
          });
        }
      });
  });

  $("#adminRes").click(function () {
    var ID = $("#Editadmin_ID").val();

    var data = {
      action: "admin-restore",
      UUID: ID,
    };

    $.ajax({
      type: "POST",
      url: "../../../Functions/api/updateAccounts.php",
      data: data,
      success: function (response) {
        if (response.stat === "success") {
          QueueNotification(["info", "Account has been restored.", 3000]);
          adminAPI =
            "../../../Functions/api/getAccounts.php?type=admin&status=active";
          $("#AdminTable").DataTable().ajax.reload();
          AdminMods.hide();
        } else {
          QueueNotification(["error", response.message, 3000]);
        }
      },
    });
  });
});
