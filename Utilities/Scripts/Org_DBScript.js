import { QueueNotification } from "./Modules/Queueing_Notification.js";

let TableAPI = "../../../Functions/api/getOrgData.php?status=active";

$(document).ready(function () {
  $("#OrgTable").DataTable({
    layout: {
      topStart: {
        search: {
          placeholder: "Search...",
          className: "form-control form-control-sm",
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
    order: [[1, "desc"]],
    language: {
      emptyTable: "No Organizations have been created.",
      zeroRecords: "No matching Organizations found.",
      info: "Showing _START_ to _END_ of _TOTAL_ Organizations",
      infoEmpty: "Showing 0 to 0 of 0 Organizations",
      infoFiltered: "(filtered from _MAX_ total Organizations)",
      search: "_INPUT_",
      searchPlaceholder: "Search Organizations",
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
      { data: "onlyForCourse" },
      {
        data: null,
        render: function (data) {
          return (
            "<p style='cursor: pointer;' title='" +
            data.org_Desc +
            "'>" +
            (data.org_Desc ? data.org_Desc.substring(0, 35) + "..." : "") +
            "</p>"
          );
        },
      },
      { data: "created_At" },
      {
        data: null,
        render: function (data, type, row) {
          if (data.stat === 1) {
            return `<button id="Enable-Btn" class="btn btn-sm btn-outline-success border-0 text-success bg-transparent" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" title="Enable Organization"
            ><i class="bi bi-toggle2-on fs-4"></i></button>`;
          } else if (data.stat === 0) {
            return `<button id="Disable-Btn" class="btn btn-sm btn-outline-danger border-0 text-danger bg-transparent" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" title="Disable Organization"
            ><i class="bi bi-toggle2-off fs-4"></i></button>
            <button id="Edit-Btn" class="btn btn-sm btn-outline-primary border-0 text-primary bg-transparent" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" title="Edit Organization"
            ><i class="bi bi-pen-fill"></i></button>`;
          } else {
            return `<p class="text-danger">Error</p>`;
          }
        },
      },
    ],

    columnDefs: [
      {
        targets: [0, 1],
        visible: false,
      },
      {
        targets: [5, 6, -1],
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
          error: function () {
            QueueNotification(["danger", "Server error. Please try again."]);
          },
        });
      });

      $("#OrgTable").on("click", "#Edit-Btn", function () {
        var data = table.api().row($(this).parents("tr")).data();
        $("#input_ID").val(data.ID);
        $("#btn_editorg").removeClass("d-none");
        $("#btn_addorg").addClass("d-none");

        $("#input_orgname").val(data.org_name);
        $("#input_orgshortname").val(data.org_short_name);
        $("#input_orgdesc").val(data.org_Desc);
        let courseDropdown = $("#input_Course");
        if (data.onlyForCourse === "All Courses") {
          courseDropdown.val("ALL");
        } else {
          let matchingOption = courseDropdown.find(
            "option[data-shorthand='" + data.onlyForCourse + "']"
          );

          if (matchingOption.length > 0) {
            courseDropdown.val(matchingOption.val());
          } else {
            courseDropdown.append(
              new Option(data.onlyForCourse, data.onlyForCourse)
            );
            courseDropdown.val(data.onlyForCourse);
          }
        }
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
        let forCourse = $("#input_Course").val();
        let desc = $("#input_orgdesc").val();

        if (
          name === "" ||
          shortname === "" ||
          desc === "" ||
          forCourse === ""
        ) {
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
            forCourse: forCourse,
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
        $("#input_Course").val("");
        $("#input_orgdesc").val("");
      });
    },
  });

  $("#OrgTable").on("draw.dt", function () {
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
  
  $("#btn_editorg").click(function () {
    var ID = $("#input_ID").val();
    var name = $("#input_orgname").val();
    var shortname = $("#input_orgshortname").val();
    var forCourse = $("#input_Course").val();
    var desc = $("#input_orgdesc").val();

    if (name === "" || shortname === "" || desc === "" || forCourse === "") {
      QueueNotification(["danger", "Please fill out all fields."]);
      return;
    }

    $.ajax({
      url: "../../../Functions/api/EditOrg.php",
      type: "POST",
      data: {
        ID: ID,
        name: name,
        shortname: shortname,
        desc: desc,
        forCourse: forCourse,
      },
      success: function (response) {
        if (response.status === "success") {
          $("#btn_editorg").addClass("d-none");
          $("#btn_addorg").removeClass("d-none");
          $("#input_orgname").val("");
          $("#input_orgshortname").val("");
          $("#input_orgdesc").val("");
          $("#input_Course").val("");
          $("#input_ID").val("");
          $("#OrgTable").DataTable().ajax.reload();
          QueueNotification(["success", "Organization has been updated."]);
        } else {
          QueueNotification(["danger", "Error updating organization."]);
        }
      },
      error: function (xhr, error, code) {
        QueueNotification(["danger", "Server error. Please try again."]);
      },
    });

  });
});
