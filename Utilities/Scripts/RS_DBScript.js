import {
  checkifISLogin,
  checkIfSessionChange,
  sessionAlert,
} from "./Modules/FeedModules.js";

import { QueueNotification } from "./Modules/Queueing_Notification.js";

const tooltipTriggerList = document.querySelectorAll(
  '[data-bs-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
  (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
);

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

  $("#SystemAudit").DataTable({
    responsive: true,
    autoWidth: true,
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    layout: {
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
          return `<p class='text-truncate' style='max-width: 128px; cursor: pointer;' data-bs-toggle='popover' data-bs-trigger='hover' data-bs-placement='top' data-bs-title="Details" data-bs-content='${data.details}'><span class='badge rounded-1 bg-secondary d-print-none'>Details</span><span class='d-none d-print-block'>${data.details}</span></p>`;
        },
      },
      {
        data: function (data) {
          return `<span class="badge rounded-1 bg-${
            data.status.toLowerCase().includes("success")
              ? "success"
              : "danger"
          }">${data.status}</span>`;
        },
      },
      { data: "dateCreate" },
      { data: "timestamp" },
    ],

    columnDefs: [
      {
      },
    ],

    error: function (xhr, error, code) {
      QueueNotification([
        "error",
        "An error occurred while fetching data.",
        4000,
        "top-end",
      ]);
    },
  });

  $("#SystemAudit").on("draw.dt", function () {
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

  $("#ExportReport").on("click", function () {
    $("#SystemAudit").DataTable().button("0").trigger();
  });

  $("#RefreshReport").on("click", function () {
    $("#SystemAudit").DataTable().ajax.reload();
  });
});
