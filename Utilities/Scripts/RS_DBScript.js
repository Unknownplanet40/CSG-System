import {
  checkifISLogin,
  checkIfSessionChange,
  sessionAlert,
} from "./Modules/FeedModules.js";

import { QueueNotification } from "./Modules/Queueing_Notification.js";

var hash = window.location.hash;

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

  $("#SystemAudit").on("draw.dt", function () {
    const tooltipTriggerList = document.querySelectorAll(
      '[data-bs-toggle="tooltip"]'
    );
    tooltipTriggerList.forEach(
      (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
    );
    const popoverTriggerList = document.querySelectorAll(
      '[data-bs-toggle="popover"]'
    );
    popoverTriggerList.forEach(
      (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
    );
  });
  $("#DeviceAudit").on("draw.dt", function () {
    const tooltipTriggerList = document.querySelectorAll(
      '[data-bs-toggle="tooltip"]'
    );
    tooltipTriggerList.forEach(
      (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
    );
    const popoverTriggerList = document.querySelectorAll(
      '[data-bs-toggle="popover"]'
    );
    popoverTriggerList.forEach(
      (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
    );
  });

  //------[System Audit - Start]------//
  $("#SystemAudit").DataTable({
    responsive: true,
    autoWidth: true,
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TB1",
            text: "",
          },
        },
        {
          div: {
            className: "",
            text: "",
          },
        },
      ],
      topStart: {
        search: {
          placeholder: "Search...",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: {
        buttons: [
          {
            extend: "pdf",
            text: "Export PDF",
            title: "SYSTEM AUDIT",
            orientation: "landscape",
            pageSize: "letter",
            filename:
              "Audit_Report_" +
              new Date().toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
              }),
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
              columns: [1, 2, 3, 4, 5, 6],
            },
          },
          {
            extend: "print",
            text: "Print",
            title: "SYSTEM AUDIT",
            filename:
              "Audit_Report_" +
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
              columns: [1, 2, 4, 5, 6, 7],
            },
          },
        ],
      },
    },
    ajax: {
      url: "../../../Functions/api/SR_SystemAudit.php",
      dataSrc: "data",
    },

    columns: [
      { data: "SA_UID" },
      { data: "userID" },
      { data: "eventType" },
      { data: "ip_address" },
      {
        data: null,
        render: function (data) {
          return `<p class='text-truncate' style='max-width: 128px; cursor: pointer;' data-bs-toggle='tooltip' data-bs-placement='left' data-bs-trigger='hover' data-bs-title='${data.details}'><span class='badge rounded-1 bg-secondary d-print-none'>Details</span><span class='d-none d-print-block'> ${data.details}</span></p>`;
        },
      },
      {
        data: function (data) {
          return `<span class="badge rounded-1 bg-${
            data.status.toLowerCase().includes("success") ? "success" : "danger"
          }">${data.status}</span>`;
        },
      },
      { data: "dateCreate" },
      { data: "timestamp" },
    ],

    rowCallback: function (row, data) {
      $(row).find('[data-bs-toggle="tooltip"]').tooltip();
      $(row).hover(
        function () {
          const details = data.details;
          if (details) {
            $(this).find('[data-bs-toggle="tooltip"]').tooltip("show");
          }
        },
        function () {
          $(this).find('[data-bs-toggle="tooltip"]').tooltip("hide");
        }
      );
    },

    initComplete: function () {
      let hasDate = false;
      let DateParams = "";
      let hasEvent = false;
      let EventParams = "";
      const newContainer = $(".Select-DT-TB1");
      newContainer.addClass("d-flex justify-content-center");
      const EventSelect = document.createElement("select");
      EventSelect.id = "EventSelect";
      EventSelect.className = "form-select form-select-sm ms-2";

      // Fetch events and append to the container
      $.ajax({
        url: "../../../Functions/api/getEvents.php",
        type: "GET",
        success: function (data) {
          EventSelect.innerHTML = `<option value="">All Events</option>`;
          if (data && data.data && Array.isArray(data.data)) {
            data.data.forEach((event) => {
              EventSelect.innerHTML += `<option value="${event.eventType}">${event.eventType}</option>`;
            });
            newContainer.append(EventSelect);
          }
        },
        error: function () {
          QueueNotification([
            "error",
            "An error occurred while fetching data.",
            4000,
            "top-end",
          ]);
        },
      });

      // Create and append Date dropdown
      const DateSelect = document.createElement("select");
      DateSelect.className = "form-select form-select-sm me-2";
      DateSelect.id = "DateSelect";
      DateSelect.innerHTML = `<option value="">All Dates</option>
      <option value="Today">Today</option>
      <option value="Yesterday">Yesterday</option>
      <option value="This Week">This Week</option>
      <option value="This Month">This Month</option>
      <option value="This Year">This Year</option>`;
      newContainer.append(DateSelect);

      // Date change event
      $("#DateSelect").on("change", function () {
        const date = $(this).val();
        if (date === "") {
          DateParams = "";
          hasDate = false;
        } else {
          DateParams = date;
          hasDate = true;
        }
        updateTable_SA();
      });

      // Event change event
      $(document).on("change", "#EventSelect", function () {
        const event = $(this).val();
        if (event === "") {
          EventParams = "";
          hasEvent = false;
        } else {
          EventParams = event;
          hasEvent = true;
        }
        updateTable_SA();
      });

      function updateTable_SA() {
        const table = $("#SystemAudit").DataTable();
        let url = "../../../Functions/api/SR_SystemAudit.php";

        if (hasDate && hasEvent) {
          url += `?date=${DateParams}&event=${EventParams}`;
        } else if (hasDate && !hasEvent) {
          url += `?date=${DateParams}`;
        } else if (!hasDate && hasEvent) {
          url += `?event=${EventParams}`;
        }

        table.ajax.url(url).load();
      }
    },

    columnDefs: [{}],
    error: function (xhr, error, code) {
      QueueNotification([
        "error",
        "An error occurred while fetching data.",
        4000,
        "top-end",
      ]);
    },
  });

  //------[System Audit - End]------//
  //------[Account Status - Start]------//
  $("#AccountAudit").DataTable({
    responsive: true,
    autoWidth: true,
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TB2",
            text: "",
          },
        },
        {
          div: {
            className: "",
            text: "",
          },
        },
      ],
      topStart: {
        search: {
          placeholder: "Search...",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: {
        buttons: [
          {
            extend: "pdf",
            text: "Export PDF",
            title: "User Account Status",
            orientation: "portrait",
            pageSize: "letter",
            filename:
              "Account_Status_" +
              new Date().toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
              }),
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
              columns: [1, 2, 4, 5, 6, 7],
            },
          },
          {
            extend: "print",
            text: "Print",
            title: "User Account Status",
            filename:
              "Account_Status_" +
              new Date().toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
              }),
            pageSize: "letter",
            orientation: "portrait",
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
              columns: [1, 2, 4, 5, 6, 7],
            },
          },
        ],
      },
    },

    ajax: {
      url: "../../../Functions/api/SR_AccountStatus.php",
      dataSrc: "data",
    },

    columns: [
      { data: "ID" },
      { data: "UUID" },
      { data: "student_Number" },
      {
        data: null,
        render: function (data) {
          return `<p class="fw-bold">&#10033;&#10033;&#10033;&#10033;&#10033;&#10033;&#10033;&#10033;&#10033;&#10033;</p>`;
        },
      },
      {
        data: null,
        render: function (data) {
          var islogin = data.isLogin == 1 ? "Logged In" : "Logged Out";
          return `<span class="badge rounded-1 bg-${
            data.isLogin == 1 ? "success" : "danger"
          }">${islogin}</span>`;
        },
      },
      { data: "ipAddress" },
      {
        data: null,
        render: function (data) {
          var LoginStat =
            data.LoginStat == "Active"
              ? "Active"
              : data.LoginStat == "Locked"
              ? "Locked"
              : "Inactive";
          var color =
            data.LoginStat == "Active"
              ? "success"
              : data.LoginStat == "Locked"
              ? "danger"
              : "secondary";
          return `<span class="badge rounded-1 bg-${color}">${LoginStat}</span>`;
        },
      },
      { data: "access_date" },
    ],

    columnDefs: [
      {
        targets: [0],
        visible: false,
      },
    ],

    initComplete: function () {
      let hasDate = false;
      let DateParams = "";
      let hasEvent = false;
      let EventParams = "";
      const newContainer = $(".Select-DT-TB2");
      newContainer.addClass("d-flex justify-content-center");
      const EventSelect = document.createElement("select");
      EventSelect.id = "EventSelect-AS";
      EventSelect.className = "form-select form-select-sm ms-2";

      $.ajax({
        url: "../../../Functions/api/getEvents-AS.php",
        type: "GET",
        success: function (data) {
          EventSelect.innerHTML = `<option value="">All Events</option>`;
          if (data && data.data && Array.isArray(data.data)) {
            data.data.forEach((event) => {
              EventSelect.innerHTML += `<option value="${event.LoginStat}">${event.LoginStat}</option>`;
            });
            newContainer.append(EventSelect);
          }
        },
        error: function () {
          QueueNotification([
            "error",
            "An error occurred while fetching data.",
            4000,
            "top-end",
          ]);
        },
      });

      const DateSelect = document.createElement("select");
      DateSelect.className = "form-select form-select-sm me-2";
      DateSelect.id = "DateSelect-AS";
      DateSelect.innerHTML = `<option value="">All Dates</option>
      <option value="Today">Today</option>
      <option value="Yesterday">Yesterday</option>
      <option value="This Week">This Week</option>
      <option value="This Month">This Month</option>
      <option value="This Year">This Year</option>`;
      newContainer.append(DateSelect);

      $("#DateSelect-AS").on("change", function () {
        const date = $(this).val();
        if (date === "") {
          DateParams = "";
          hasDate = false;
        } else {
          DateParams = date;
          hasDate = true;
        }
        updateTable_AS();
      });

      $(document).on("change", "#EventSelect-AS", function () {
        const event = $(this).val();
        if (event === "") {
          EventParams = "";
          hasEvent = false;
        } else {
          EventParams = event;
          hasEvent = true;
        }
        updateTable_AS();
      });

      function updateTable_AS() {
        const table = $("#AccountAudit").DataTable();
        let url = "../../../Functions/api/SR_AccountStatus.php";

        if (hasDate && hasEvent) {
          url += `?date=${DateParams}&event=${EventParams}`;
        } else if (hasDate && !hasEvent) {
          url += `?date=${DateParams}`;
        } else if (!hasDate && hasEvent) {
          url += `?event=${EventParams}`;
        }

        table.ajax.url(url).load();
      }
    },
  });
  //------[Account Status - End]------//
  //------[Device Audit - Start]------//
  $("#DeviceAudit").DataTable({
    responsive: true,
    autoWidth: true,
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TB3",
            text: "",
          },
        },
        {
          div: {
            className: "",
            text: "",
          },
        },
      ],
      topStart: {
        search: {
          placeholder: "Search...",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: {
        buttons: [
          {
            extend: "pdf",
            text: "Export PDF",
            title: "User Device used to access the system",
            orientation: "landscape",
            pageSize: "legal",
            filename:
              "Device_Audit_" +
              new Date().toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
              }),
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
              columns: [1, 2, 3, 4, 5, 6],
            },
          },
          {
            extend: "print",
            text: "Print",
            title: "User Device used to access the system",
            filename:
              "Device_Audit_" +
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
              columns: [1, 2, 3, 6, 5],
            },
          },
        ],
      },
    },

    ajax: {
      url: "../../../Functions/api/SR_DeviceAudit.php",
      dataSrc: "data",
    },

    columns: [
      { data: "ID" },
      { data: "UUID" },
      { data: "Device" },
      { data: "DeviceDetails" },
      { data: "IPaddress" },
      {
        data: null,
        render: function (data) {
          return `<p class="text-truncate" style="max-width: 128px; cursor: pointer;" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" data-bs-title="${data.summary}"><span class="badge rounded-1 bg-secondary d-print-none">Details</span><span class="d-none d-print-block"> ${data.summary}</span></p>`;
        },
      },
      { data: "dateMade" },
    ],

    columnDefs: [
      {
        targets: [0],
        visible: false,
      },
    ],

    rowCallback: function (row, data) {
      $(row).find('[data-bs-toggle="tooltip"]').tooltip();
      $(row).hover(
        function () {
          const details = data.summary;
          if (details) {
            $(this).find('[data-bs-toggle="tooltip"]').tooltip("show");
          }
        },
        function () {
          $(this).find('[data-bs-toggle="tooltip"]').tooltip("hide");
        }
      );
    },

    initComplete: function () {
      let hasDate = false;
      let DateParams = "";
      let hasEvent = false;
      let EventParams = "";
      const newContainer = $(".Select-DT-TB3");
      newContainer.addClass("d-flex justify-content-center");
      const EventSelect = document.createElement("select");
      EventSelect.id = "EventSelect-DA";
      EventSelect.className = "form-select form-select-sm ms-2";

      // Fetch events and append to the container
      $.ajax({
        url: "../../../Functions/api/getEvents-DA.php",
        type: "GET",
        success: function (data) {
          EventSelect.innerHTML = `<option value="">All Devices</option>`;
          if (data && data.data && Array.isArray(data.data)) {
            data.data.forEach((event) => {
              EventSelect.innerHTML += `<option value="${event.Device}">${event.Device}</option>`;
            });
            newContainer.append(EventSelect);
          } else {
            EventSelect.innerHTML = `<option value="" Disabled>No Divices Found</option>`;
            newContainer.append(EventSelect);
          }
        },
        error: function () {
          QueueNotification([
            "error",
            "An error occurred while fetching data.",
            4000,
            "top-end",
          ]);
        },
      });

      // Create and append Date dropdown
      const DateSelect = document.createElement("select");
      DateSelect.className = "form-select form-select-sm me-2";
      DateSelect.id = "DateSelect-DA";
      DateSelect.innerHTML = `<option value="">All Dates</option>
      <option value="Today">Today</option>
      <option value="Yesterday">Yesterday</option>
      <option value="This Week">This Week</option>
      <option value="This Month">This Month</option>
      <option value="This Year">This Year</option>`;
      newContainer.append(DateSelect);

      // Date change event
      $("#DateSelect-DA").on("change", function () {
        const date = $(this).val();
        if (date === "") {
          DateParams = "";
          hasDate = false;
        } else {
          DateParams = date;
          hasDate = true;
        }
        updateTable_DA();
      });

      // Event change event
      $(document).on("change", "#EventSelect-DA", function () {
        const event = $(this).val();
        if (event === "") {
          EventParams = "";
          hasEvent = false;
        } else {
          EventParams = event;
          hasEvent = true;
        }
        updateTable_DA();
      });

      function updateTable_DA() {
        const table = $("#DeviceAudit").DataTable();
        let url = "../../../Functions/api/SR_DeviceAudit.php";

        if (hasDate && hasEvent) {
          url += `?date=${DateParams}&event=${EventParams}`;
        } else if (hasDate && !hasEvent) {
          url += `?date=${DateParams}`;
        } else if (!hasDate && hasEvent) {
          url += `?event=${EventParams}`;
        }

        table.ajax.url(url).load();
      }
    },
  });
  //------[Device Audit - End]------//

  $(".buttons-pdf, .buttons-print")
    .removeClass("btn-secondary")
    .addClass("btn-outline-success btn-sm");

  if (hash == "#SystemReport-1") {
    setTimeout(() => {
      console.log("SystemReport-1");
      $("button[onclick*='SystemReport-1']").click();
    }, 1000);
  } else if (hash == "#SystemReport-2") {
    setTimeout(() => {
      console.log("SystemReport-2");
      $("button[onclick*='SystemReport-2']").click();
    }, 1000);
  } else if (hash == "#SystemReport-3") {
    setTimeout(() => {
      console.log("SystemReport-3");
      $("button[onclick*='SystemReport-3']").click();
    }, 1000);
  }

  $("#ExportReport").on("click", function () {
    Swal.fire({
      title: "Export Report",
      text: "All reports will be exported to PDF, and they will appear one by one. Do you want to proceed?",
      icon: "question",
      customClass: {
        popup: "alert-popup glass-default bg-opacity-75",
        confirmButton: "btn btn-sm btn-success text-bg-success",
        cancelButton: "btn btn-sm btn-secondary text-bg-secondary",
        container: "alert-container",
        htmlContainer: "alert-html-container",
        title: "alert-title",
      },
      showCancelButton: true,
      confirmButtonText: "Yes",
      cancelButtonText: "No",
    }).then((result) => {
      if (result.isConfirmed) {
        $("#SystemAudit").DataTable().button("0").trigger();
        $("#AccountAudit").DataTable().button("0").trigger();
        $("#DeviceAudit").DataTable().button("0").trigger();
      }
    });
  });

  $("#RefreshReport").on("click", function () {
    QueueNotification(["info", "Data has been refreshed.", 1000, "top"]);
    $("#SystemAudit").DataTable().ajax.reload();
    $("#AccountAudit").DataTable().ajax.reload();
    $("#DeviceAudit").DataTable().ajax.reload();
  });
});
