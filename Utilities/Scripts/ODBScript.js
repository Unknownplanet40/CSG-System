$(document).ready(function () {
  $("#TaskDocTable").DataTable({
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    lengthMenu: [7],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Tasks",
      lengthMenu: "_MENU_",
      info: "Showing _START_ to _END_ of _TOTAL_ Tasks",
      infoEmpty: "",
      infoFiltered: "(filtered from _MAX_ total Tasks)",
      zeroRecords: "No matching Tasks found",
      thousands: ",",
      emptyTable: "Currently no Tasks available",
      paginate: {
        next: "Next",
        previous: "Previous",
      },
    },
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TDs",
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
      bottomStart: null,
    },
    columnDefs: [
      {
        targets: [0, 2, 3, 7],
        orderable: false,
        visible: false,
        searchable: false,
      },
    ],

    ajax: {
      url: "../../../Functions/api/getTaskDoc.php",
      type: "GET",
      dataType: "json",
      dataSrc: "data",
    },

    columns: [
      { data: "taskID" },
      { data: "postedBy" },
      {
        data: null,
        render: function (data) {
          return (
            '<p class="text-truncate" style="max-width: 90px;">' +
            data.taskTitle +
            "</p>"
          );
        },
      },
      { data: "taskDesc" },
      { data: "taskType" },
      { data: "AssignedTO" },
      {
        data: "tastStat",
        render: function (data) {
          if (data == "Pending") {
            return (
              '<span class="px-3 py-1 text-bg-warning rounded-5">' +
              data +
              "</span>"
            );
          } else if (data == "Completed") {
            return (
              '<span class="px-3 py-1 text-bg-success rounded-5">' +
              data +
              "</span>"
            );
          } else if (data == "Ongoing") {
            return (
              '<span class="px-3 py-1 text-bg-info rounded-5">' +
              data +
              "</span>"
            );
          } else if (data == "Overdue") {
            return (
              '<span class="px-3 py-1 text-bg-danger rounded-5">' +
              data +
              "</span>"
            );
          }
        },
      },
      { data: "taskDatecreated" },
      {
        data: null,
        render: function (data) {
          if (data.tastStat == "Completed") {
            return '<span class="text-success">' + data.taskDuedate + "</span>";
          } else {
            var today = new Date();
            var dueDate = new Date(data.taskDuedate);
            var diffTime = dueDate - today;
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays < 0) {
              return (
                '<span class="text-danger">' + data.taskDuedate + "</span>"
              );
            } else if (diffDays <= 3) {
              if (diffDays == 0) {
                return (
                  '<span class="text-danger">' +
                  data.taskDuedate +
                  " (Due Today)</span>"
                );
              } else if (diffDays == 1) {
                return (
                  '<span class="text-danger">' +
                  data.taskDuedate +
                  " (Due Tomorrow)</span>"
                );
              } else {
                return (
                  '<span class="text-danger">' +
                  data.taskDuedate +
                  " (" +
                  diffDays +
                  " days from now)</span>"
                );
              }
            } else {
              return (
                '<span class="text-success">' +
                data.taskDuedate +
                " (" +
                diffDays +
                " days from now)</span>"
              );
            }
          }
        },
      },
      {
        data: null,
        render: function (data) {
          return `<button id="View-Btn" class="btn btn-sm btn-success rounded-10">View Task</button>`;
        },
      },
    ],

    initComplete: function () {
      this.api()
        .columns([1, 3, 4, 5, 6])
        .every(function () {
          var column = this;
          var select = $(
            '<select class="form-select form-select-sm"><option value="">All</option></select>'
          )
            .appendTo($(column.header()).empty())
            .on("change", function () {
              var val = $.fn.dataTable.util.escapeRegex($(this).val());

              column.search(val ? "^" + val + "$" : "", true, false).draw();
            });

          column
            .data()
            .unique()
            .sort()
            .each(function (d) {
              select.append('<option value="' + d + '">' + d + "</option>");
            });
        });

      var table = this.api();
      table.on("click", "#View-Btn", function () {
        var data = table.row($(this).parents("tr")).data();
        Swal.fire({
          icon: "info",
          title: "Task Details",
          html: `<p class="text-start"><b>Task Title</b>: ${data.taskTitle}</p>
            <p class="text-start"><b>Task Description</b>: ${data.taskDesc}</p>
            <p class="text-start"><b>Task Type</b>: ${data.taskType}</p>
            <p class="text-start"><b>Assigned To</b>: ${data.AssignedTO}</p>
            <p class="text-start"><b>Task Status</b>: ${data.tastStat}</p>
            <p class="text-start"><b>Date Created</b>: ${
              data.taskDatecreated
            }</p>
            <p class="text-start"><b>Due Date</b>: ${
              data.taskDuedate
            } (<small>${
            data.taskDuedate === new Date().toISOString().split("T")[0]
              ? "Due Today"
              : new Date(data.taskDuedate) < new Date()
              ? "Overdue"
              : Math.floor(
                  (new Date(data.taskDuedate) - new Date()) /
                    (1000 * 60 * 60 * 24) +
                    1
                ) + " days from now"
          }</small>)
            </p>
            <p class="text-start"><b>Posted By</b>: ${data.postedBy}</p>
            <hr>
            <p>Do you want to accept this task?</p>`,
          showConfirmButton: data.tastStat == "Completed" ? false : true,
          showCancelButton: true,
          confirmButtonText: "Create Document",
          cancelButtonText: "Later",
          cancelButtonColor: "#d33",
          confirmButtonColor: "#28a745",
          customClass: {
            popup: "alert-popup glass-default bg-opacity-25 text-body",
            container: "alert-container",
            confirmButton: "btn btn-sm btn-success",
            cancelButton: "btn btn-sm",
            htmlContainer: "alert-html-container",
            title: "alert-title",
          },
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: "../../../Functions/api/acceptTask.php",
              type: "POST",
              data: {
                taskID: data.taskID,
                activeTask:
                  localStorage.getItem("taskID_AP") ||
                  localStorage.getItem("taskID_EL") ||
                  localStorage.getItem("taskID_MM") ||
                  localStorage.getItem("taskID_OM") ||
                  localStorage.getItem("taskID_PP"),
              },
              success: function (response) {
                if (response.status == "success") {
                  Swal.fire({
                    icon: "success",
                    title: "Task Accepted",
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    customClass: {
                      popup: "glass-default bg-opacity-25 text-body",
                    },
                  }).then(() => {
                    let doctype = response.tasktype;
                    let taskID = response.taskID;
                    let orgCODE = response.org_Code;
                    if (doctype == "Activity Proposal") {
                      localStorage.setItem("taskID_AP", taskID);
                      localStorage.setItem("orgCODE_AP", orgCODE);
                      window.location.href =
                        "../../../Pages/Apps/User_Modules/ActivityProposal.php";
                    } else if (doctype == "Excuse Letter") {
                      localStorage.setItem("taskID_EL", taskID);
                      localStorage.setItem("orgCODE_EL", orgCODE);
                      window.location.href =
                        "../../../Pages/Apps/User_Modules/ExcuseLetter.php";
                    } else if (doctype == "Meeting Minutes") {
                      localStorage.setItem("taskID_MM", taskID);
                      localStorage.setItem("orgCODE_MM", orgCODE);
                      window.location.href =
                        "../../../Pages/Apps/User_Modules/MinutesOfTheMeeting.php";
                    } else if (doctype == "Office Memorandum") {
                      localStorage.setItem("taskID_OM", taskID);
                      localStorage.setItem("orgCODE_OM", orgCODE);
                      window.location.href =
                        "../../../Pages/Apps/User_Modules/OfficeMemorandum.php";
                    } else if (doctype == "Project Proposal") {
                      localStorage.setItem("taskID_PP", taskID);
                      localStorage.setItem("orgCODE_PP", orgCODE);
                      window.location.href =
                        "../../../Pages/Apps/User_Modules/ProjectProposal.php";
                    } else {
                      $("#TaskDocTable").DataTable().ajax.reload();
                    }
                  });
                } else if (response.status == "info") {
                  Swal.mixin({
                    toast: true,
                    position: "top",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                      popup:
                        "alert-popup glass-default bg-opacity-25 text-body",
                      container: "alert-container",
                      htmlContainer: "alert-html-container",
                      title: "alert-title",
                    },
                  })
                    .fire({
                      icon: "info",
                      text: response.message,
                    })
                    .then(() => {
                      if (response.tasktype != null) {
                        let doctype = response.tasktype;
                        let taskID = response.taskID;
                        if (doctype == "Activity Proposal") {
                          localStorage.setItem("taskID_AP", taskID);
                          window.location.href =
                            "../../../Pages/Apps/User_Modules/ActivityProposal.php";
                        }
                        if (doctype == "Excuse Letter") {
                          localStorage.setItem("taskID_EL", taskID);
                          window.location.href =
                            "../../../Pages/Apps/User_Modules/ExcuseLetter.php";
                        }
                        if (doctype == "Meeting Minutes") {
                          localStorage.setItem("taskID_MM", taskID);
                          window.location.href =
                            "../../../Pages/Apps/User_Modules/MinutesOfTheMeeting.php";
                        }
                        if (doctype == "Office Memorandum") {
                          localStorage.setItem("taskID_OM", taskID);
                          window.location.href =
                            "../../../Pages/Apps/User_Modules/OfficeMemorandum.php";
                        }
                        if (doctype == "Project Proposal") {
                          localStorage.setItem("taskID_PP", taskID);
                          window.location.href =
                            "../../../Pages/Apps/User_Modules/ProjectProposal.php";
                        } else {
                          $("#TaskDocTable").DataTable().ajax.reload();
                        }
                      }
                    });
                } else {
                  Swal.mixin({
                    toast: true,
                    position: "top",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                      popup:
                        "alert-popup glass-default bg-opacity-25 text-body",
                      container: "alert-container",
                      htmlContainer: "alert-html-container",
                      title: "alert-title",
                    },
                  }).fire({
                    icon: "error",
                    text: "Task acceptance failed. Please try again later",
                  });
                }
              },
              error: function (data) {
                Swal.mixin({
                  toast: true,
                  position: "top",
                  showConfirmButton: false,
                  timer: 3000,
                  timerProgressBar: true,
                  customClass: {
                    popup: "alert-popup glass-default bg-opacity-25 text-body",
                    container: "alert-container",
                    htmlContainer: "alert-html-container",
                    title: "alert-title",
                  },
                }).fire({
                  icon: "error",
                  text: "Something went wrong. Please try again later",
                });
              },
            });
          }
        });
      });
    },
  });

  $("#APDocTable").DataTable({
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    lengthMenu: [7],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Activity Proposals",
      lengthMenu: "_MENU_",
      info: "Showing _START_ to _END_ of _TOTAL_ Tasks",
      infoEmpty: "",
      infoFiltered: "(filtered from _MAX_ total Activity Proposals)",
      zeroRecords: "No matching Activity Proposals found",
      thousands: ",",
      emptyTable: "Currently no Activity Proposals available",
      paginate: {
        next: "Next",
        previous: "Previous",
      },
    },
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TDs",
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
          placeholder: "Search Activity Proposals",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: null,
    },

    ajax: {
      url: "../../../Functions/api/getPreActivityProposal.php",
      type: "GET",
      dataType: "json",
      dataSrc: "data",
    },

    columns: [
      { data: "ID" },
      { data: "act_title" },
      { data: "admin_name" },
      { data: "Created_By" },
      {
        data: "file_Size",
        render: function (data) {
          if (data < 1024) {
            return data + " B";
          } else if (data < 1048576) {
            return (data / 1024).toFixed(2) + " KB";
          } else if (data < 1073741824) {
            return (data / 1048576).toFixed(2) + " MB";
          } else {
            return (data / 1073741824).toFixed(2) + " GB";
          }
        },
      },
      { data: "date_Created" },
    ],
  });

  $("#ELDocTable").DataTable({
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    lengthMenu: [7],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Activity Proposals",
      lengthMenu: "_MENU_",
      info: "Showing _START_ to _END_ of _TOTAL_ Tasks",
      infoEmpty: "",
      infoFiltered: "(filtered from _MAX_ total Activity Proposals)",
      zeroRecords: "No matching Activity Proposals found",
      thousands: ",",
      emptyTable: "Currently no Activity Proposals available",
      paginate: {
        next: "Next",
        previous: "Previous",
      },
    },
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TDs",
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
          placeholder: "Search Activity Proposals",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: null,
    },

    ajax: {
      url: "../../../Functions/api/getExcuseLetter.php",
      type: "GET",
      dataType: "json",
      dataSrc: "data",
    },

    columns: [
      { data: "ID" },
      { data: "Event" },
      { data: "excuseLetterType" },
      {
        data: "file_Size",
        render: function (data) {
          if (data < 1024) {
            return data + " B";
          } else if (data < 1048576) {
            return (data / 1024).toFixed(2) + " KB";
          } else if (data < 1073741824) {
            return (data / 1048576).toFixed(2) + " MB";
          } else {
            return (data / 1073741824).toFixed(2) + " GB";
          }
        },
      },
      { data: "Created_By" },
      { data: "DateCreated" },
    ],

    columnDefs: [
      {
        targets: [0],
        orderable: false,
        visible: false,
        searchable: false,
      },
    ],
  });

  $("#MMDocTable").DataTable({
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    lengthMenu: [7],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Meeting Minutes",
      lengthMenu: "_MENU_",
      info: "Showing _START_ to _END_ of _TOTAL_ Meeting Minutes",
      infoEmpty: "",
      infoFiltered: "(filtered from _MAX_ total Meeting Minutes)",
      zeroRecords: "No matching Meeting Minutes found",
      thousands: ",",
      emptyTable: "Currently no Meeting Minutes available",
      paginate: {
        next: "Next",
        previous: "Previous",
      },
    },
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TDs",
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
          placeholder: "Search Meeting Minutes",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: null,
    },

    ajax: {
      url: "../../../Functions/api/getMinuteMeeting.php",
      type: "GET",
      dataType: "json",
      dataSrc: "data",
    },

    columns: [
      { data: "ID" },
      { data: "MMPresider" },
      { data: "MMLocation" },
      { data: "MMdate" },
      {
        data: "file_Size",
        render: function (data) {
          if (data < 1024) {
            return data + " B";
          } else if (data < 1048576) {
            return (data / 1024).toFixed(2) + " KB";
          } else if (data < 1073741824) {
            return (data / 1048576).toFixed(2) + " MB";
          } else {
            return (data / 1073741824).toFixed(2) + " GB";
          }
        },
      },
      { data: "Created_By" },
      { data: "DateCreated" },
    ],

    columnDefs: [
      {
        targets: [0],
        orderable: false,
        visible: false,
        searchable: false,
      },
    ],
  });

  $("#OMDocTable").DataTable({
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    lengthMenu: [7],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Office Memorandum",
      lengthMenu: "_MENU_",
      info: "Showing _START_ to _END_ of _TOTAL_ Office Memorandum",
      infoEmpty: "",
      infoFiltered: "(filtered from _MAX_ total Office Memorandum)",
      zeroRecords: "No matching Office Memorandum found",
      thousands: ",",
      emptyTable: "Currently no Office Memorandum available",
      paginate: {
        next: "Next",
        previous: "Previous",
      },
    },
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TDs",
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
          placeholder: "Search Office Memorandum",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: null,
    },

    ajax: {
      url: "../../../Functions/api/getOfficeMemorandum.php",
      type: "GET",
      dataType: "json",
      dataSrc: "data",
    },

    columns: [
      { data: "ID" },
      { data: "OM_Sub" },
      { data: "OM_To" },
      { data: "Created_By" },
      {
        data: "file_Size",
        render: function (data) {
          if (data < 1024) {
            return data + " B";
          } else if (data < 1048576) {
            return (data / 1024).toFixed(2) + " KB";
          } else if (data < 1073741824) {
            return (data / 1048576).toFixed(2) + " MB";
          } else {
            return (data / 1073741824).toFixed(2) + " GB";
          }
        },
      },
      { data: "DateCreated" },
    ],


  });

  $("#PPDocTable").DataTable({
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    lengthMenu: [7],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Project Proposals",
      lengthMenu: "_MENU_",
      info: "Showing _START_ to _END_ of _TOTAL_ Project Proposals",
      infoEmpty: "",
      infoFiltered: "(filtered from _MAX_ total Project Proposals)",
      zeroRecords: "No matching Project Proposals found",
      thousands: ",",
      emptyTable: "Currently no Project Proposals available",
      paginate: {
        next: "Next",
        previous: "Previous",
      },
    },
    layout: {
      top1: [
        {
          div: {
            className: "",
          },
        },
        {
          div: {
            className: "select-DT-TDs",
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
          placeholder: "Search Project Proposals",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: null,
    },

    ajax: {
      url: "../../../Functions/api/getPreProjectProposal.php",
      type: "GET",
      dataType: "json",
      dataSrc: "data",
    },

    columns: [
      { data: "ID" },
      { data: "act_title" },
      { data: "admin_name" },
      { data: "Created_By" },
      {
        data: "file_Size",
        render: function (data) {
          if (data < 1024) {
            return data + " B";
          } else if (data < 1048576) {
            return (data / 1024).toFixed(2) + " KB";
          } else if (data < 1073741824) {
            return (data / 1048576).toFixed(2) + " MB";
          } else {
            return (data / 1073741824).toFixed(2) + " GB";
          }
        },
      },
      { data: "date_Created" },
    ],
  });
});
