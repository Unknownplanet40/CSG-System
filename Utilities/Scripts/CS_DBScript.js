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
          '<select id="utype" class="form-select form-select-sm"><option value="active">Active</option><option value="archived">Archived</option></select>'
        );
      },
    },
    responsive: true,
    autoWidth: false,
    order: [[2, "desc"]],
    pageLength: 7,
    language: {
      emptyTable: "No Course & Section has been created yet.",
      zeroRecords: "No matching Course & Section found.",
      info: "Showing _START_ to _END_ of _TOTAL_ Courses & Sections",
      infoEmpty: "Showing 0 to 0 of 0 Courses & Sections",
      infoFiltered: "(filtered from _MAX_ total Courses & Sections)",
      search: "_INPUT_",
      searchPlaceholder: "Search Course & Section",
      lengthMenu: "Show _MENU_",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Previous",
      },
    },

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
            return `<button id="Enable-Btn" class="btn btn-sm btn-outline-success border-0 text-success bg-transparent" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" title="Enable Course"
            ><i class="bi bi-toggle2-on fs-4"></i></button>`;
          } else if (data.course_status === "active") {
            return `<button id="Disable-Btn" class="btn btn-sm btn-outline-danger border-0 text-danger bg-transparent" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" title="Disable Course"
            ><i class="bi bi-toggle2-off fs-4"></i></button>
            <button id="Edit-Btn" class="btn btn-sm btn-outline-primary border-0 text-primary bg-transparent" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" title="Edit Course"
            ><i class="bi bi-pen-fill"></i></button>`;
          } else {
            return `<p class="text-danger">Error</p>`;
          }
        },
      },
    ],

    columnDefs: [
      {
        targets: [0, 1, 6],
        visible: false,
      },
      {
        targets: [7, -1],
        orderable: false,
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

      table.on("click", "#Edit-Btn", function () {
        const data = table.row($(this).parents("tr")).data();

        //check if containe collapsed class

        if ($("#flush-headingOne").hasClass("collapsed")) {
          $("#flush-headingOne").click();
        }
        $("#CSName").val(data.course_name);
        $("#CSShortName").val(data.course_short_name);
        let yearLevel = data.year;
        switch (yearLevel) {
          case "1st Year":
            yearLevel = 1;
            break;
          case "2nd Year":
            yearLevel = 2;
            break;
          case "3rd Year":
            yearLevel = 3;
            break;
          case "4th Year":
            yearLevel = 4;
            break;
        }
        $("#CSYearLevel").val(yearLevel);
        $("#CSSection").val(data.section);
        $("#CSID").val(data.ID);
        $("#CreateCS").addClass("d-none");
        $("#EditCS").removeClass("d-none");
      });
    },
  });

  $("#CSTable").on("draw.dt", function () {
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
          QueueNotification([
            "success",
            "Course section created successfully",
            4000,
            "top-end",
          ]);
          $("#CSTable").DataTable().ajax.reload();
          $("#CSShortName").val("");
          $("#CSName").val("Bachelor Of Science In");
          $("#CSYearLevel").val("");
          $("#CSSection").val("");
        } else {
          QueueNotification([
            "error",
            response.message || "Failed to create course section",
            4000,
            "top-end",
          ]);
        }
      },
      error: function () {
        QueueNotification([
          "error",
          "An error occurred while creating the course section",
          4000,
          "top-end",
        ]);
      },
    });
  });

  $("#EditCS").on("click", function () {
    let CSShortName = $("#CSShortName").val();
    let CSName = $("#CSName").val();
    let CSYearLevel = $("#CSYearLevel").val();
    let CSSection = $("#CSSection").val();
    let CSID = $("#CSID").val();

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

    $.ajax({
      url: "../../../Functions/api/editCS.php",
      method: "GET",
      data: {
        short_name: CSShortName,
        name: CSName,
        year: CSYearLevel,
        section: CSSection,
        id: CSID,
      },
      success: function (response) {
        if (response.status === "success") {
          QueueNotification([
            "success",
            "Course section updated successfully",
            4000,
            "top-end",
          ]);
          $("#CSTable").DataTable().ajax.reload();
          $("#CSShortName").val("");
          $("#CSName").val("Bachelor Of Science in ");
          $("#CSYearLevel").val("");
          $("#CSSection").val("");
          $("#CSID").val("");
          $("#CreateCS").removeClass("d-none");
          $("#EditCS").addClass("d-none");
        } else {
          QueueNotification([
            "error",
            response.message || "Failed to update course section",
            4000,
            "top-end",
          ]);
        }
      },
    });
  });

  $("#ResetCS").on("click", function () {
    $("#CSShortName").val("");
    $("#CSName").val("Bachelor Of Science in ");
    $("#CSYearLevel").val("");
    $("#CSSection").val("");
    $("#CSID").val("");
    $("#CreateCS").removeClass("d-none");
    $("#EditCS").addClass("d-none");
  });
});
