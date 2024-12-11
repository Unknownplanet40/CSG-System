import { QueueNotification } from "./Modules/Queueing_Notification.js";

let TableAPI = "../../../Functions/api/getOrgData.php?status=active";

$(document).ready(function () {
  $("#OrgTable").DataTable({
    layout: {
      topStart: {
        search: {
          placeholder: "Search...",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: function () {
        return $(
          '<select id="utype" class="form-select"><option value="active">Active</option><option value="archived">Archived</option></select>'
        );
      },
    },
    responsive: true,
    autoWidth: false,
    order: [[1, "desc"]],
    ordering: false,

    ajax: {
      url: TableAPI,
      dataSrc: "data",

      error: function (xhr, error, code) {
        console.log(xhr);
        console.log(error);
        console.log(code);
      },
    },

    columns: [
      { data: "ID" },
      { data: "org_code" },
      { data: "org_name" },
      { data: "org_short_name" },
      { data: null, render: function (data) { return "<p style='cursor: pointer;' title='" + data.org_Desc + "'>" + (data.org_Desc ? data.org_Desc.substring(0, 35) + "..." : "") + "</p>"; } },
      { data: "created_At" },
      {
        data: null,
        render: function (data, type, row) {
          if (data.stat === 1) {
            return `<button id="Enable-Btn" class="btn btn-sm btn-success">Enable</button>`;
          } else if (data.stat === 0) {
            return `<button id="Disable-Btn" class="btn btn-sm btn-danger">Disable</button>`;
          } else {
            return `<p class="text-danger">Error</p>`;
          }
        },
      },
    ],

    columnDefs: [
      {
        targets: [0],
        visible: false,
      },
      {
        targets: [6],
        orderable: false,
      },
    ],

    initComplete: function () {
      var table = this;
      $("#OrgTable").on("click", "#Enable-Btn", function () {
        var data = table.api().row($(this).parents("tr")).data();
        $.ajax({
          url: "../../../Functions/api/changeOrgStatus.php",
          type: "GET",
          data: {
            status: "enable",
            ID: data.org_code,
          },
          success: function (response) {
            if (response.status === "success") {
              table.api().ajax.reload();
              QueueNotification(["success", "Organization has been enabled."]);
            } else {
              QueueNotification(["danger", "Error enabling organization."]);
            }
          },
          error: function (xhr, error, code) {
            QueueNotification(["danger", "Server error. Please try again."]);
          },
        });
      });

      $("#OrgTable").on("click", "#Disable-Btn", function () {
        var data = table.api().row($(this).parents("tr")).data();
        $.ajax({
          url: "../../../Functions/api/changeOrgStatus.php",
          type: "GET",
          data: {
            status: "disable",
            ID: data.org_code,
          },
          success: function (response) {
            if (response.status === "success") {
              table.api().ajax.reload();
              QueueNotification(["success", "Organization has been disabled."]);
            } else {
              QueueNotification(["danger", "Error disabling organization."]);
            }
          },
          error: function (xhr, error, code) {
            QueueNotification(["danger", "Server error. Please try again."]);
          },
        });
      });

      $("#utype").change(function () {
        if ($(this).val() == "active") {
          TableAPI = "../../../Functions/api/getOrgData.php?status=active";
        } else {
          TableAPI = "../../../Functions/api/getOrgData.php?status=archived";
        }

        table.api().ajax.url(TableAPI).load();
      });

      $("#btn_addorg").click(function () {
        let code = Math.floor(Math.random() * 100000);
        let name = $("#input_orgname").val();
        let shortname = $("#input_orgshortname").val();
        let desc = $("#input_orgdesc").val();



        if (name === "" || shortname === "" || desc === "") {
          QueueNotification(["danger", "Please fill out all fields."]);
          return;
        }

        $.ajax({
          url: "../../../Functions/api/CreateOrg.php",
          type: "POST",
          data: {
            code: code,
            name: name,
            shortname: shortname,
            desc: desc,
          },
          success: function (response) {
            if (response.status === "success") {
              table.api().ajax.reload();
              name = $("#input_orgname").val("");
              shortname = $("#input_orgshortname").val("");
              desc = $("#input_orgdesc").val("");
              QueueNotification(["success", "Organization has been created."]);
            } else {
              QueueNotification(["danger", "Error creating organization."]);
            }
          },
          error: function (xhr, error, code) {
            QueueNotification(["danger", "Server error. Please try again."]);
          },
        });
      });

      $("#btn_clear").click(function () {
        $("#input_orgname").val("");
        $("#input_orgshortname").val("");
        $("#input_orgdesc").val("");
      });
    },
  });
});
