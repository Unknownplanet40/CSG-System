import {
  checkifISLogin,
  checkIfSessionChange,
  sessionAlert,
} from "./Modules/FeedModules.js";

function updateDateTime() {
  $("#currentDate").text(
    new Date().toLocaleDateString("en-US", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    })
  );

  $("#currentTime").text(
    new Date().toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: true,
    })
  );
}

updateDateTime();
setInterval(updateDateTime, 1000);

$(document).ready(function () {
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      $("div[style*='z-index:9999'][style*='position:relative']").each(
        function () {
          if (
            $(this).text().includes("Unlicensed copy of the Froala Editor.")
          ) {
            $(this).remove();
          }
        }
      );
    });
  });

  setInterval(() => {
    observer.observe(document.body, { childList: true, subtree: true });
  }, 1000);

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

  $("#audit_table").DataTable({
    responsive: true,
    autoWidth: true,
    ordering: false,
    order: [[1, "desc"]],
    pageLength: 5,
    layout: {
      topStart: {
        search: {
          placeholder: "Search...",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: null,
    },
    ajax: {
      url: "../../../Functions/api/getAudits.php",
      dataSrc: "data",
      error: function (xhr, error, code) {
        $("#audit_table").DataTable().clear().draw();
        console.error("Failed to fetch audit data:", error);
      },
    },

    columns: [
      {
        data: function (data) {
          return `<span class="badge rounded-1 bg-${
            data.action === "restore"
              ? "success"
              : data.action === "delete"
              ? "danger"
              : data.action === "update"
              ? "warning"
              : "primary"
          }">${data.action}</span>`;
        },
      },
      { data: "change_date" },
      { data: "changed_by" },
      { data: "affected_user" },
      {
        data: null,
        render: function () {
          return `<button class="btn btn-sm rounded-1 btn-success" id="viewAudit">View</button>`;
        },
      },
    ],

    columnDefs: [
      {
        targets: [0, 4],
        className: "text-center",
      },
    ],

    initComplete: function () {
      $("#audit_table").on("click", "#viewAudit", function () {
        let data = $("#audit_table")
          .DataTable()
          .row($(this).parents("tr"))
          .data();
        let modal = $("#AuditDetails");
        modal.modal("show");

        let body = $("#audit_details_body");
        let oldValues = JSON.parse(data.old_value);
        let newValues = JSON.parse(data.new_value);

        body.empty();

        // check if empty
        if (Object.keys(oldValues).length === 0) {
          body.append(
            `<tr>
                <td colspan="4" class="text-center">No changes were made</td>
            </tr>`
          );
          return;
        }
        let count = 0;

        for (let key in oldValues) {
          count++;
          body.append(
            `<tr>
                <td>${count}</td>
                <td>${key}</td>
                <td>${oldValues[key]}</td>
                <td>${newValues[key]}</td>
            </tr>`
          );
        }
      });
    },
  });
});
