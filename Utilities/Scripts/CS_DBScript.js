import { QueueNotification } from "./Modules/Queueing_Notification.js";

let TableAPI = "../../../Functions/api/getCSData.php?status=active";

$(document).ready(function () {
  $("#CSTable").DataTable({
    layout: {
      topStart: {
        search: {
          placeholder: "Search Course Section",
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
    pageLength: 7,

    ajax: {
      url: TableAPI,
      dataSrc: "data",

      error: function (xhr, error, code) {
        QueueNotification(["error", "An error occurred while fetching data."]);
      },
    },

    columns: [
      { data: "ID" },
      { data: "course_code" },
      { data: "course_short_name" },
      {
        data: null,
        render: function (data) {
          const prefix = "Bachelor Of Science In";
          return data.course_name.startsWith(prefix)
            ? data.course_name.substring(prefix.length).trim().toUpperCase()
            : data.course_name.trim().toUpperCase();
        },
      },
      {
        data: null,
        render: function (data) {
          return `<p class='text-center'>${data.section}</p>`;
        },
      },
      {
        data: null,
        render: function (data) {
          return `<p class='text-center'>${data.year}</p>`;
        },
      },
      { data: "course_status" },
      { data: "created_at" },
      {
        data: null,
        render: function (data, type, row) {
          if (data.course_status === "archived") {
            return `<button id="Enable-Btn" class="btn btn-sm btn-success">Enable</button>`;
          } else if (data.course_status === "active") {
            return `<button id="Disable-Btn" class="btn btn-sm btn-danger">Disable</button>`;
          } else {
            return `<p class="text-danger">Error</p>`;
          }
        },
      },
    ],

    columnDefs: [
      {
        targets: [0, 6],
        visible: false,
      },
    ],

    initComplete: function () {
      const table = this.api();
      const select = $("#utype");

      select.on("change", function () {
        TableAPI = `../../../Functions/api/getCSData.php?status=${this.value}`;
        table.ajax.url(TableAPI).load();
      });

      table.on("click", "#Enable-Btn", function () {
        const data = table.row($(this).parents("tr")).data();
        const course_code = data.course_code;

        $.ajax({
          url: "../../../Functions/api/changeCSStatus.php",
          method: "GET",
          data: { course_code: course_code, action: "enable" },
          success: function (data) {
            if (data.status === "success") {
              QueueNotification(["success", "Course has been enabled."]);
              table.ajax.reload();
            } else {
              QueueNotification([
                "error",
                "An error occurred while enabling the course.",
              ]);
            }
          },
          error: function (xhr, error, code) {
            QueueNotification([
              "error",
              "An error occurred while enabling the course.",
            ]);
          },
        });
      });

      table.on("click", "#Disable-Btn", function () {
        const data = table.row($(this).parents("tr")).data();
        const course_code = data.course_code;

        $.ajax({
          url: "../../../Functions/api/changeCSStatus.php",
          method: "GET",
          data: { course_code: course_code, action: "disable" },
          success: function (data) {
            QueueNotification(["success", "Course has been disabled."]);
            table.ajax.reload();
          },
          error: function (xhr, error, code) {
            QueueNotification([
              "error",
              "An error occurred while disabling the course.",
            ]);
          },
        });
      });
    },
  });

  $("#CreateCS").on("click", function () {
    let CSShortName = $("#CSShortName").val();
    let CSName = $("#CSName").val();
    let CSYearLevel = $("#CSYearLevel").val();
    let CSSection = $("#CSSection").val();

    if (
      CSShortName === "" ||
      CSName === "" ||
      CSYearLevel === "" ||
      CSSection === ""
    ) {
      QueueNotification([
        "error",
        "Please fill out all fields.",
        4000,
        "top-end",
      ]);
      return;
    }

    if (CSShortName.length > 10) {
      QueueNotification([
        "error",
        "Short name should not exceed 10 characters.",
        4000,
        "top-end",
      ]);
      return;
    }

    if (CSName.length > 50) {
      QueueNotification([
        "error",
        "Course name should not exceed 50 characters.",
        4000,
        "top-end",
      ]);
      return;
    }

    if (CSSection.length > 2) {
      QueueNotification([
        "error",
        "Section should not exceed 2 characters.",
        4000,
        "top-end",
      ]);
      return;
    }

    if (CSYearLevel < 1) {
      QueueNotification([
        "error",
        "Year level should be greater than 0.",
        4000,
        "top-end",
      ]);
      return;
    }
    let code = Math.floor(Math.random() * 100000);
    $.ajax({
      url: "../../../Functions/api/createCS.php",
      method: "GET",
      data: {
        short_name: CSShortName,
        name: CSName,
        year: CSYearLevel,
        section: CSSection,
        code: code,
      },
      success: function (response) {
        if (response.status === "success") {
          QueueNotification(["success", "Course section created successfully", 4000, "top-end"]);
          $("#CSTable").DataTable().ajax.reload();
            $("#CSShortName").val("");
            $("#CSName").val("Bachelor Of Science In");
            $("#CSYearLevel").val("");
            $("#CSSection").val("");
        } else {
          QueueNotification(["error", response.message || "Failed to create course section", 4000, "top-end"]);
        }
      },
      error: function () {
        QueueNotification(["error", "An error occurred while creating the course section", 4000, "top-end"]);
      },
    });
  });
});
