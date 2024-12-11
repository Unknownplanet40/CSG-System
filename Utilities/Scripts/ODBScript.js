$(document).ready(function () {
  //<script>var UUID = "a5dcd436-74fe-4405-9de9-4eaca56f432a";</script><script>var role = "3";</script><script>var Position = "4";</script>
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
        targets: [0, 2, 3],
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
          if ((role == 2 && Position  > 1) || (role == 3 && Position  > 1)) {
            return `<button id="View-Btn" class="btn btn-sm btn-outline-success rounded-0">View Task</button>`;
          } else {
            return `<button id="View-Btn" class="btn btn-sm btn-outline-secondary rounded-0">Details</button>`;
          }
        },
      },
    ],

    initComplete: function () {
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
          cancelButtonText: role == 1 ? "Close" : role == 2 ? "Nevermind" : "Later",
          cancelButtonColor: "#d33",
          confirmButtonColor: "#28a745",
          customClass: {
            popup: "bg-dark text-body rounded-0 glass-default bg-opacity-25",
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
                      popup: "bg-dark text-body rounded-0 glass-default",
                    },
                  }).then(() => {
                    let doctype = response.tasktype;
                    let taskID = response.taskID;
                    let orgCODE = response.org_Code;
                    if (doctype == "Activity Proposal") {
                      $("#TaskDocTable").DataTable().ajax.reload();
                      localStorage.setItem("taskID_AP", taskID);
                      localStorage.setItem("orgCODE_AP", orgCODE);
                      window.location.href = "../../../Pages/Apps/User_Modules/ActivityProposal.php";
                    } else if (doctype == "Excuse Letter") {
                      $("#TaskDocTable").DataTable().ajax.reload();
                      localStorage.setItem("taskID_EL", taskID);
                      localStorage.setItem("orgCODE_EL", orgCODE);
                      window.location.href = "../../../Pages/Apps/User_Modules/ExcuseLetter.php";
                    } else if (doctype == "Meeting Minutes") {
                      $("#TaskDocTable").DataTable().ajax.reload();
                      localStorage.setItem("taskID_MM", taskID);
                      localStorage.setItem("orgCODE_MM", orgCODE);
                      window.location.href =
                        "../../../Pages/Apps/User_Modules/MinutesOfTheMeeting.php";
                    } else if (doctype == "Office Memorandum") {
                      $("#TaskDocTable").DataTable().ajax.reload();
                      localStorage.setItem("taskID_OM", taskID);
                      localStorage.setItem("orgCODE_OM", orgCODE);
                      window.location.href =
                        "../../../Pages/Apps/User_Modules/OfficeMemorandum.php";
                    } else if (doctype == "Project Proposal") {
                      $("#TaskDocTable").DataTable().ajax.reload();
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
                      popup: "bg-dark text-body rounded-0 glass-default",
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
                      popup: "bg-dark text-body rounded-0 glass-default",
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
                    popup: "bg-dark text-body rounded-0 glass-default",
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

      if (table.rows().count() !== 0) {
        const TDContainer = $(".select-DT-TDs");
        TDContainer.addClass("d-flex justify-content-center gap-2 w-50");

        const TDDateSelect = document.createElement("select");
        TDDateSelect.classList.add("form-select", "form-select-sm");
        TDDateSelect.id = "TDDateSelect_TDs";
        TDDateSelect.innerHTML =
          "<option value=''>All Date</option>" +
          "<option value='1'>Today</option>" +
          "<option value='2'>Yesterday</option>" +
          "<option value='3'>This Week</option>" +
          "<option value='4'>This Month</option>" +
          "<option value='5'>Last Month</option>" +
          "<option value='6'>This Year</option>";
        TDContainer.append(TDDateSelect);

        const TDStatusSelect = document.createElement("select");
        TDStatusSelect.classList.add("form-select", "form-select-sm");
        TDStatusSelect.id = "TDStatusSelect_TDs";
        TDStatusSelect.innerHTML =
          "<option value=''>All Status</option>" +
          "<option value='Pending'>Pending</option>" +
          "<option value='Completed'>Completed</option>" +
          "<option value='Ongoing'>Ongoing</option>" +
          "<option value='Overdue'>Overdue</option>";
        TDContainer.append(TDStatusSelect);

        const TDTypeSelect = document.createElement("select");
        TDTypeSelect.classList.add("form-select", "form-select-sm", "w-100");
        TDTypeSelect.id = "TDTypeSelect_TDs";
        TDTypeSelect.innerHTML =
          "<option value=''>All Document Type</option>" +
          "<option value='Activity Proposal'>Activity Proposal</option>" +
          "<option value='Excuse Letter'>Excuse Letter</option>" +
          "<option value='Meeting Minutes'>Meeting Minutes</option>" +
          "<option value='Office Memorandum'>Office Memorandum</option>" +
          "<option value='Project Proposal'>Project Proposal</option>";
        TDContainer.append(TDTypeSelect);

        const dateSearchFunction = function (settings, data, dataIndex) {
          const TDDateVal = $("#TDDateSelect_TDs").val();
          if (!TDDateVal) return true;

          const TDDateString = data[7];
          const TDDateCreated = new Date(TDDateString);
          TDDateCreated.setHours(0, 0, 0, 0);

          const TDToday = new Date();
          TDToday.setHours(0, 0, 0, 0);

          switch (TDDateVal) {
            case "1":
              return TDDateCreated.getTime() === TDToday.getTime();
            case "2": {
              const yesterday = new Date(TDToday);
              yesterday.setDate(yesterday.getDate() - 1);
              return TDDateCreated.getTime() === yesterday.getTime();
            }
            case "3": {
              const weekStart = new Date(TDToday);
              weekStart.setDate(weekStart.getDate() - 7);
              return TDDateCreated >= weekStart;
            }
            case "4": {
              const monthStart = new Date(
                TDToday.getFullYear(),
                TDToday.getMonth(),
                1
              );
              return TDDateCreated >= monthStart;
            }
            case "5": {
              const lastMonthStart = new Date(
                TDToday.getFullYear(),
                TDToday.getMonth() - 1,
                1
              );
              const lastMonthEnd = new Date(
                TDToday.getFullYear(),
                TDToday.getMonth(),
                0
              );
              return (
                TDDateCreated >= lastMonthStart && TDDateCreated <= lastMonthEnd
              );
            }
            case "6": {
              const yearStart = new Date(TDToday.getFullYear(), 0, 1);
              return TDDateCreated >= yearStart;
            }
            default:
              return true;
          }
        };

        const statusSearchFunction = function (settings, data, dataIndex) {
          const TDStatusVal = $("#TDStatusSelect_TDs").val();

          if (!TDStatusVal) return true;

          const TDStatusString = data[6];

          return TDStatusString === TDStatusVal;
        };

        const typeSearchFunction = function (settings, data, dataIndex) {
          const TDTypeVal = $("#TDTypeSelect_TDs").val();

          if (!TDTypeVal) return true;

          const TDTypeString = data[4];

          return TDTypeString === TDTypeVal;
        };

        $.fn.dataTable.ext.search.push(dateSearchFunction);

        $.fn.dataTable.ext.search.push(statusSearchFunction);

        $.fn.dataTable.ext.search.push(typeSearchFunction);

        $("#TDTypeSelect_TDs").on("change", function () {
          table.draw();
        });

        $("#TDStatusSelect_TDs").on("change", function () {
          table.draw();
        });

        $("#TDDateSelect_TDs").on("change", function () {
          table.draw();
        });
      }
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
            className: "select-DT-APs",
          },
        },
        {
          div: {
            className: "",
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
      url: "../../../Functions/api/getPreActivityProposal.php?isSubmittedtoCSG=0",
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
      {
        data: "date_Created",
        render: function (data) {
          return new Date(data).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
          });
        },
      },
      {
        data: null,
        render: function (data) {
          if (role == 1) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-secondary rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> See Details</button>'
            )
          } else if (Position < 4) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-warning rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> Submit</button>'
            );
          } else {
            return (
              '<button id="View-Btn-P4" class="btn btn-sm btn-outline-success rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> View</button>'
            )
          }
        },
      },
    ],
    initComplete: function () {
      var table = this.api();
      table.on("click", "#View-Btn", function () {
        var data = table.row($(this).parents("tr")).data();
        function sweetAlert() {
          Swal.fire({
            icon: "info",
            title: "Document Approval",
            text: "Do you want to Submit this document to CSG?",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showCancelButton: true,
            showDenyButton: true,
            denyButtonText: "VIEW DOCUMENT",
            confirmButtonText: "SUBMIT",
            cancelButtonText: "MAYBE LATER",
            cancelButtonColor: "#d33",
            confirmButtonColor: "#28a745",
            denyButtonColor: "#6c757d",
            customClass: {
              popup: "text-light rounded-0 bg-opacity-10 glass-default",
              actions: "hstack gap-1",
              confirmButton: "btn btn-sm rounded-1 w-100",
              denyButton: "btn btn-sm rounded-1 w-100",
              cancelButton: "btn btn-sm rounded-1 w-100",
            },
          }).then((result) => {
            if (result.isDenied) {
              var path = "..\\..\\..\\..\\" + data.file_path;
              var width = window.innerWidth;
              var height = window.innerHeight;
              window.open(
                path,
                "_blank",
                "toolbar=0,location=0,menubar=0,width=" +
                  width +
                  ",height=" +
                  height
              );
              sweetAlert();
            } else if (result.isConfirmed) {
              $.ajax({
                url: "../../../Functions/api/ApproveDocument.php",
                type: "POST",
                data: {
                  docID: data.fileID,
                  document: "D-AP",
                  orgCode: data.org_code,
                },

                success: function (response) {
                  if (response.status == "success") {
                    Swal.fire({
                      icon: "success",
                      title: "Document Approved",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity-25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    });
                    $("#APDocTable").DataTable().ajax.reload();
                  } else {
                    Swal.fire({
                      icon: "error",
                      title: "Document Approval Failed",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    });
                    $("#APDocTable").DataTable().ajax.reload();
                  }
                },
                error: function (data) {
                  Swal.fire({
                    icon: "error",
                    title: "Document Approval Failed",
                    text: "Something went wrong. Please try again later",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    customClass: {
                      popup: "text-light rounded-0 bg-opacity-25 glass-default",
                      confirmButton: "btn btn-sm",
                      denyButton: "btn btn-sm",
                      cancelButton: "btn btn-sm",
                    },
                  }).then(() => {
                    $("#APDocTable").DataTable().ajax.reload();
                  });
                },
              });
            }
          });
        }
        sweetAlert();
      });

      table.on("click", "#View-Btn-P4", function () {
        var data = table.row($(this).parents("tr")).data();
        var path = "..\\..\\..\\..\\" + data.file_path;
        var width = window.innerWidth;
        var height = window.innerHeight;
        window.open(path, "_blank", "toolbar=0,location=0,menubar=0,width=" + width + ",height=" + height);
      });

      if (table.rows().count() !== 0) {
        const APContainer = $(".select-DT-APs");
        APContainer.addClass("d-flex justify-content-center gap-2 ms-1");
        const APStatusSelect = document.createElement("select");
        APStatusSelect.classList.add("form-select", "form-select-sm");
        APStatusSelect.id = "APDateSelect_APs";
        APStatusSelect.innerHTML =
          "<option value=''>All Date</option>" +
          "<option value='1'>Today</option>" +
          "<option value='2'>Yesterday</option>" +
          "<option value='3'>This Week</option>" +
          "<option value='4'>This Month</option>" +
          "<option value='5'>Last Month</option>" +
          "<option value='6'>This Year</option>";
        APContainer.append(APStatusSelect);
        const statusSearchFunction = function (settings, data, dataIndex) {
          const APStatusVal = $("#APDateSelect_APs").val();
          if (!APStatusVal) return true;
          const APStatusString = data[5];
          const APStatusCreated = new Date(APStatusString);
          APStatusCreated.setHours(0, 0, 0, 0);
          const APToday = new Date();
          APToday.setHours(0, 0, 0, 0);
          switch (APStatusVal) {
            case "1":
              return APStatusCreated.getTime() === APToday.getTime();
            case "2": {
              const yesterday = new Date(APToday);
              yesterday.setDate(yesterday.getDate() - 1);
              return APStatusCreated.getTime() === yesterday.getTime();
            }
            case "3": {
              const weekStart = new Date(APToday);
              weekStart.setDate(weekStart.getDate() - 7);
              return APStatusCreated >= weekStart;
            }
            case "4": {
              const monthStart = new Date(
                APToday.getFullYear(),
                APToday.getMonth(),
                1
              );
              return APStatusCreated >= monthStart;
            }
            case "5": {
              const lastMonthStart = new Date(
                APToday.getFullYear(),
                APToday.getMonth() - 1,
                1
              );
              const lastMonthEnd = new Date(
                APToday.getFullYear(),
                APToday.getMonth(),
                0
              );
              return (
                APStatusCreated >= lastMonthStart &&
                APStatusCreated <= lastMonthEnd
              );
            }
            case "6": {
              const yearStart = new Date(APToday.getFullYear(), 0, 1);
              return APStatusCreated >= yearStart;
            }
            default:
              return true;
          }
        };

        $.fn.dataTable.ext.search.push(statusSearchFunction);

        $("#APDateSelect_APs").on("change", function () {
          table.draw();
        });
      }
    },
  });

  $("#ELDocTable").DataTable({
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    lengthMenu: [7],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Excuse Letters",
      lengthMenu: "_MENU_",
      info: "Showing _START_ to _END_ of _TOTAL_ Excuse Letters",
      infoEmpty: "",
      infoFiltered: "(filtered from _MAX_ total Excuse Letters)",
      zeroRecords: "No matching Excuse Letters found",
      thousands: ",",
      emptyTable: "Currently no Excuse Letters available",
      paginate: {
        next: "Next",
        previous: "Previous",
      },
    },
    layout: {
      top1: [
        {
          div: {
            className: "select-DT-ELs",
          },
        },
        {
          div: {
            className: "",
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
          placeholder: "Search Excuse Letters",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: null,
    },

    ajax: {
      url: "../../../Functions/api/getExcuseLetter.php?isSubmittedtoCSG=0",
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
      {
        data: null,
        render: function (data) {
          if (role == 1) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-secondary rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> See Details</button>'
            );
          }else if (Position < 4) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-warning rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> Submit</button>'
            );
          } else {
            return (
              '<button id="View-Btn-P4" class="btn btn-sm btn-outline-success rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> View</button>');
          }
        },
      },
    ],

    initComplete: function () {
      var table = this.api();
      table.on("click", "#View-Btn", function () {
        var data = table.row($(this).parents("tr")).data();
        function sweetAlert() {
          Swal.fire({
            icon: "info",
            title: "Document Approval",
            text: "Do you want to Submit this document to CSG?",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showCancelButton: true,
            showDenyButton: true,
            denyButtonText: "VIEW DOCUMENT",
            confirmButtonText: "YES SUBMIT",
            cancelButtonText: "MAYBE LATER",
            cancelButtonColor: "#d33",
            confirmButtonColor: "#28a745",
            denyButtonColor: "#6c757d",
            customClass: {
              popup: "text-light rounded-0 bg-opacity-10 glass-default",
              actions: "hstack gap-1",
              confirmButton: "btn btn-sm rounded-1 w-100",
              denyButton: "btn btn-sm rounded-1 w-100",
              cancelButton: "btn btn-sm rounded-1 w-100",
            },
          }).then((result) => {
            if (result.isDenied) {
              var path = "..\\..\\..\\..\\" + data.file_path;
              var width = window.innerWidth;
              var height = window.innerHeight;
              window.open(
                path,
                "_blank",
                "toolbar=0,location=0,menubar=0,width=" +
                  width +
                  ",height=" +
                  height
              );
              sweetAlert();
            } else if (result.isConfirmed) {
              $.ajax({
                url: "../../../Functions/api/ApproveDocument.php",
                type: "POST",
                data: {
                  docID: data.fileID,
                  document: "D-EL",
                  orgCode: data.org_code,
                },

                success: function (response) {
                  if (response.status == "success") {
                    Swal.fire({
                      icon: "success",
                      title: "Document Approved",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity-25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    }).then(() => {
                      $("#APDocTable").DataTable().ajax.reload();
                    });
                  } else {
                    Swal.fire({
                      icon: "error",
                      title: "Document Approval Failed",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    }).then(() => {
                      $("#APDocTable").DataTable().ajax.reload();
                    });
                  }
                },
                error: function (data) {
                  Swal.fire({
                    icon: "error",
                    title: "Document Approval Failed",
                    text: "Something went wrong. Please try again later",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    customClass: {
                      popup: "text-light rounded-0 bg-opacity-25 glass-default",
                      confirmButton: "btn btn-sm",
                      denyButton: "btn btn-sm",
                      cancelButton: "btn btn-sm",
                    },
                  }).then(() => {
                    $("#APDocTable").DataTable().ajax.reload();
                  });
                },
              });
            }
          });
        }
        sweetAlert();
      });

      table.on("click", "#View-Btn-P4", function () {
        var data = table.row($(this).parents("tr")).data();
        var path = "..\\..\\..\\..\\" + data.file_path;
        var width = window.innerWidth;
        var height = window.innerHeight;
        window.open(path, "_blank", "toolbar=0,location=0,menubar=0,width=" + width + ",height=" + height);
      });

      if (table.rows().count() !== 0) {
        const ELContainer = $(".select-DT-ELs");
        ELContainer.addClass("d-flex justify-content-center gap-2 ms-1");
        const ELStatusSelect = document.createElement("select");
        ELStatusSelect.classList.add("form-select", "form-select-sm");
        ELStatusSelect.id = "ELDateSelect_ELs";
        ELStatusSelect.innerHTML =
          "<option value=''>All Date</option>" +
          "<option value='1'>Today</option>" +
          "<option value='2'>Yesterday</option>" +
          "<option value='3'>This Week</option>" +
          "<option value='4'>This Month</option>" +
          "<option value='5'>Last Month</option>" +
          "<option value='6'>This Year</option>";
        ELContainer.append(ELStatusSelect);
        const statusSearchFunction = function (settings, data, dataIndex) {
          const ELStatusVal = $("#ELDateSelect_ELs").val();
          if (!ELStatusVal) return true;
          const ELStatusString = data[5];
          const ELStatusCreated = new Date(ELStatusString);
          ELStatusCreated.setHours(0, 0, 0, 0);
          const ELToday = new Date();
          ELToday.setHours(0, 0, 0, 0);
          switch (ELStatusVal) {
            case "1":
              return ELStatusCreated.getTime() === ELToday.getTime();
            case "2": {
              const yesterday = new Date(ELToday);
              yesterday.setDate(yesterday.getDate() - 1);
              return ELStatusCreated.getTime() === yesterday.getTime();
            }
            case "3": {
              const weekStart = new Date(ELToday);
              weekStart.setDate(weekStart.getDate() - 7);
              return ELStatusCreated >= weekStart;
            }
            case "4": {
              const monthStart = new Date(
                ELToday.getFullYear(),
                ELToday.getMonth(),
                1
              );
              return ELStatusCreated >= monthStart;
            }
            case "5": {
              const lastMonthStart = new Date(
                ELToday.getFullYear(),
                ELToday.getMonth() - 1,
                1
              );
              const lastMonthEnd = new Date(
                ELToday.getFullYear(),
                ELToday.getMonth(),
                0
              );
              return (
                ELStatusCreated >= lastMonthStart &&
                ELStatusCreated <= lastMonthEnd
              );
            }
            case "6": {
              const yearStart = new Date(ELToday.getFullYear(), 0, 1);
              return ELStatusCreated >= yearStart;
            }
            default:
              return true;
          }
        };
        $.fn.dataTable.ext.search.push(statusSearchFunction);

        $("#ELDateSelect_ELs").on("change", function () {
          table.draw();
        });
      }
    },

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
            className: "select-DT-MMs",
          },
        },
        {
          div: {
            className: "",
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
      url: "../../../Functions/api/getMinuteMeeting.php?isSubmittedtoCSG=0",
      type: "GET",
      dataType: "json",
      dataSrc: "data",
    },

    columns: [
      { data: "ID" },
      { data: "MMPresider" },
      { data: "MMLocation" },
      {
        data: "MMdate",
        render: function (data) {
          return new Date(data).toLocaleDateString("en-US", {
            month: "long",
            day: "numeric",
            year: "numeric",
          });
        },
      },
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
      {
        data: null,
        render: function (data) {
          if (role == 1) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-secondary rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> Details</button>'
            );
          }else if (Position < 4) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-warning rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> Submit</button>'
            );
          } else {
            return (
              '<button id="View-Btn-P4" class="btn btn-sm btn-outline-success rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> View</button>');
          }
        },
      },
    ],

    initComplete: function () {
      var table = this.api();
      table.on("click", "#View-Btn", function () {
        var data = table.row($(this).parents("tr")).data();
        function sweetAlert() {
          Swal.fire({
            icon: "info",
            title: "Document Approval",
            text: "Do you want to Submit this document to CSG?",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showCancelButton: true,
            showDenyButton: true,
            denyButtonText: "VIEW DOCUMENT",
            confirmButtonText: "YES SUBMIT",
            cancelButtonText: "MAYBE LATER",
            cancelButtonColor: "#d33",
            confirmButtonColor: "#28a745",
            denyButtonColor: "#6c757d",
            customClass: {
              popup: "text-light rounded-0 bg-opacity-10 glass-default",
              actions: "hstack gap-1",
              confirmButton: "btn btn-sm rounded-1 w-100",
              denyButton: "btn btn-sm rounded-1 w-100",
              cancelButton: "btn btn-sm rounded-1 w-100",
            },
          }).then((result) => {
            if (result.isDenied) {
              var path = "..\\..\\..\\..\\" + data.file_path;
              var width = window.innerWidth;
              var height = window.innerHeight;
              window.open(
                path,
                "_blank",
                "toolbar=0,location=0,menubar=0,width=" +
                  width +
                  ",height=" +
                  height
              );
              sweetAlert();
            } else if (result.isConfirmed) {
              $.ajax({
                url: "../../../Functions/api/ApproveDocument.php",
                type: "POST",
                data: {
                  docID: data.fileID,
                  document: "D-MM",
                  orgCode: data.org_code,
                },

                success: function (response) {
                  if (response.status == "success") {
                    Swal.fire({
                      icon: "success",
                      title: "Document Approved",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity-25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    }).then(() => {
                      $("#APDocTable").DataTable().ajax.reload();
                    });
                  } else {
                    Swal.fire({
                      icon: "error",
                      title: "Document Approval Failed",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    }).then(() => {
                      $("#APDocTable").DataTable().ajax.reload();
                    });
                  }
                },
                error: function (data) {
                  Swal.fire({
                    icon: "error",
                    title: "Document Approval Failed",
                    text: "Something went wrong. Please try again later",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    customClass: {
                      popup: "text-light rounded-0 bg-opacity-25 glass-default",
                      confirmButton: "btn btn-sm",
                      denyButton: "btn btn-sm",
                      cancelButton: "btn btn-sm",
                    },
                  }).then(() => {
                    $("#APDocTable").DataTable().ajax.reload();
                  });
                },
              });
            }
          });
        }
        sweetAlert();
      });

      table.on("click", "#View-Btn-P4", function () {
        var data = table.row($(this).parents("tr")).data();
        var path = "..\\..\\..\\..\\" + data.file_path;
        var width = window.innerWidth;
        var height = window.innerHeight;
        window.open(path, "_blank", "toolbar=0,location=0,menubar=0,width=" + width + ",height=" + height);
      });
      
      if (table.rows().count() !== 0) {
        const MMContainer = $(".select-DT-MMs");
        MMContainer.addClass("d-flex justify-content-center gap-2 ms-1");
        const MMStatusSelect = document.createElement("select");
        MMStatusSelect.classList.add("form-select", "form-select-sm");
        MMStatusSelect.id = "MMDateSelect_MMs";
        MMStatusSelect.innerHTML =
          "<option value=''>All Date</option>" +
          "<option value='1'>Today</option>" +
          "<option value='2'>Yesterday</option>" +
          "<option value='3'>This Week</option>" +
          "<option value='4'>This Month</option>" +
          "<option value='5'>Last Month</option>" +
          "<option value='6'>This Year</option>";
        MMContainer.append(MMStatusSelect);

        const statusSearchFunction = function (settings, data, dataIndex) {
          const MMStatusVal = $("#MMDateSelect_MMs").val();
          if (!MMStatusVal) return true;
          const MMStatusString = data[6];
          const MMStatusCreated = new Date(MMStatusString);
          MMStatusCreated.setHours(0, 0, 0, 0);
          const MMToday = new Date();
          MMToday.setHours(0, 0, 0, 0);
          switch (MMStatusVal) {
            case "1":
              return MMStatusCreated.getTime() === MMToday.getTime();
            case "2": {
              const yesterday = new Date(MMToday);
              yesterday.setDate(yesterday.getDate() - 1);
              return MMStatusCreated.getTime() === yesterday.getTime();
            }
            case "3": {
              const weekStart = new Date(MMToday);
              weekStart.setDate(weekStart.getDate() - 7);
              return MMStatusCreated >= weekStart;
            }
            case "4": {
              const monthStart = new Date(
                MMToday.getFullYear(),
                MMToday.getMonth(),
                1
              );
              return MMStatusCreated >= monthStart;
            }
            case "5": {
              const lastMonthStart = new Date(
                MMToday.getFullYear(),
                MMToday.getMonth() - 1,
                1
              );
              const lastMonthEnd = new Date(
                MMToday.getFullYear(),
                MMToday.getMonth(),
                0
              );
              return (
                MMStatusCreated >= lastMonthStart &&
                MMStatusCreated <= lastMonthEnd
              );
            }
            case "6": {
              const yearStart = new Date(MMToday.getFullYear(), 0, 1);
              return MMStatusCreated >= yearStart;
            }
            default:
              return true;
          }
        };
        $.fn.dataTable.ext.search.push(statusSearchFunction);

        $("#MMDateSelect_MMs").on("change", function () {
          table.draw();
        });
      }
    },

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
            className: "select-DT-OMs",
          },
        },
        {
          div: {
            className: "",
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
      url: "../../../Functions/api/getOfficeMemorandum.php?isSubmittedtoCSG=0",
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
      {
        data: null,
        render: function (data) {
          if (role == 1) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-secondary rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> See Details</button>'
            );
          }else if (Position < 4) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-warning rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> Submit</button>'
            );
          } else {
            return (
              '<button id="View-Btn-P4" class="btn btn-sm btn-outline-success rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> View</button>');
          }
        },
      },
    ],

    initComplete: function () {
      var table = this.api();
      table.on("click", "#View-Btn", function () {
        var data = table.row($(this).parents("tr")).data();
        function sweetAlert() {
          Swal.fire({
            icon: "info",
            title: "Document Approval",
            text: "Do you want to Submit this document to CSG?",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showCancelButton: true,
            showDenyButton: true,
            denyButtonText: "VIEW DOCUMENT",
            confirmButtonText: "YES I DO",
            cancelButtonText: "MAYBE LATER",
            cancelButtonColor: "#d33",
            confirmButtonColor: "#28a745",
            denyButtonColor: "#6c757d",
            customClass: {
              popup: "text-light rounded-0 bg-opacity-10 glass-default",
              actions: "hstack gap-1",
              confirmButton: "btn btn-sm rounded-1 w-100",
              denyButton: "btn btn-sm rounded-1 w-100",
              cancelButton: "btn btn-sm rounded-1 w-100",
            },
          }).then((result) => {
            if (result.isDenied) {
              var path = "..\\..\\..\\..\\" + data.file_path;
              var width = window.innerWidth;
              var height = window.innerHeight;
              window.open(
                path,
                "_blank",
                "toolbar=0,location=0,menubar=0,width=" +
                  width +
                  ",height=" +
                  height
              );
              sweetAlert();
            } else if (result.isConfirmed) {
              $.ajax({
                url: "../../../Functions/api/ApproveDocument.php",
                type: "POST",
                data: {
                  docID: data.fileID,
                  document: "D-OM",
                  orgCode: data.org_code,
                },

                success: function (response) {
                  if (response.status == "success") {
                    Swal.fire({
                      icon: "success",
                      title: "Document Approved",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity-25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    }).then(() => {
                      $("#APDocTable").DataTable().ajax.reload();
                    });
                  } else {
                    Swal.fire({
                      icon: "error",
                      title: "Document Approval Failed",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    }).then(() => {
                      $("#APDocTable").DataTable().ajax.reload();
                    });
                  }
                },
                error: function (data) {
                  Swal.fire({
                    icon: "error",
                    title: "Document Approval Failed",
                    text: "Something went wrong. Please try again later",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    customClass: {
                      popup: "text-light rounded-0 bg-opacity-25 glass-default",
                      confirmButton: "btn btn-sm",
                      denyButton: "btn btn-sm",
                      cancelButton: "btn btn-sm",
                    },
                  }).then(() => {
                    $("#APDocTable").DataTable().ajax.reload();
                  });
                },
              });
            }
          });
        }
        sweetAlert();
      });

      table.on("click", "#View-Btn-P4", function () {
        var data = table.row($(this).parents("tr")).data();
        var path = "..\\..\\..\\..\\" + data.file_path;
        var width = window.innerWidth;
        var height = window.innerHeight;
        window.open(path, "_blank", "toolbar=0,location=0,menubar=0,width=" + width + ",height=" + height);
      });
      
      if (table.rows().count() !== 0) {
        const OMContainer = $(".select-DT-OMs");
        OMContainer.addClass("d-flex justify-content-center gap-2 ms-1");
        const OMStatusSelect = document.createElement("select");
        OMStatusSelect.classList.add("form-select", "form-select-sm");
        OMStatusSelect.id = "OMDateSelect_OMs";
        OMStatusSelect.innerHTML =
          "<option value=''>All Date</option>" +
          "<option value='1'>Today</option>" +
          "<option value='2'>Yesterday</option>" +
          "<option value='3'>This Week</option>" +
          "<option value='4'>This Month</option>" +
          "<option value='5'>Last Month</option>" +
          "<option value='6'>This Year</option>";
        OMContainer.append(OMStatusSelect);
        const statusSearchFunction = function (settings, data, dataIndex) {
          const OMStatusVal = $("#OMDateSelect_OMs").val();
          if (!OMStatusVal) return true;
          const OMStatusString = data[5];
          const OMStatusCreated = new Date(OMStatusString);
          OMStatusCreated.setHours(0, 0, 0, 0);
          const OMToday = new Date();
          OMToday.setHours(0, 0, 0, 0);
          switch (OMStatusVal) {
            case "1":
              return OMStatusCreated.getTime() === OMToday.getTime();
            case "2": {
              const yesterday = new Date(OMToday);
              yesterday.setDate(yesterday.getDate() - 1);
              return OMStatusCreated.getTime() === yesterday.getTime();
            }
            case "3": {
              const weekStart = new Date(OMToday);
              weekStart.setDate(weekStart.getDate() - 7);
              return OMStatusCreated >= weekStart;
            }
            case "4": {
              const monthStart = new Date(
                OMToday.getFullYear(),
                OMToday.getMonth(),
                1
              );
              return OMStatusCreated >= monthStart;
            }
            case "5": {
              const lastMonthStart = new Date(
                OMToday.getFullYear(),
                OMToday.getMonth() - 1,
                1
              );
              const lastMonthEnd = new Date(
                OMToday.getFullYear(),
                OMToday.getMonth(),
                0
              );
              return (
                OMStatusCreated >= lastMonthStart &&
                OMStatusCreated <= lastMonthEnd
              );
            }
            case "6": {
              const yearStart = new Date(OMToday.getFullYear(), 0, 1);
              return OMStatusCreated >= yearStart;
            }
            default:
              return true;
          }
        };
        $.fn.dataTable.ext.search.push(statusSearchFunction);

        $("#OMDateSelect_OMs").on("change", function () {
          table.draw();
        });
      }
    },
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
            className: "select-DT-PPs",
          },
        },
        {
          div: {
            className: "",
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
      url: "../../../Functions/api/getPreProjectProposal.php?isSubmittedtoCSG=0",
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
      {
        data: null,
        render: function (data) {
          if (role == 1) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-secondary rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> See Details</button>'
            );
          } else if (Position < 4) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-warning rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> Submit</button>'
            );
          } else {
            return (
              '<button id="View-Btn-P4" class="btn btn-sm btn-outline-success rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> View</button>'
            );
          }
        },
      },
    ],

    initComplete: function () {
      var table = this.api();
      table.on("click", "#View-Btn", function () {
        var data = table.row($(this).parents("tr")).data();
        function sweetAlert() {
          Swal.fire({
            icon: "info",
            title: "Document Approval",
            text: "Do you want to Submit this document to CSG?",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showCancelButton: true,
            showDenyButton: true,
            denyButtonText: "VIEW DOCUMENT",
            confirmButtonText: "YES SUBMIT",
            cancelButtonText: "MAYBE LATER",
            cancelButtonColor: "#d33",
            confirmButtonColor: "#28a745",
            denyButtonColor: "#6c757d",
            customClass: {
              popup: "text-light rounded-0 bg-opacity-10 glass-default",
              actions: "hstack gap-1",
              confirmButton: "btn btn-sm rounded-1 w-100",
              denyButton: "btn btn-sm rounded-1 w-100",
              cancelButton: "btn btn-sm rounded-1 w-100",
            },
          }).then((result) => {
            if (result.isDenied) {
              var path = "..\\..\\..\\..\\" + data.file_path;
              var width = window.innerWidth;
              var height = window.innerHeight;
              window.open(
                path,
                "_blank",
                "toolbar=0,location=0,menubar=0,width=" +
                  width +
                  ",height=" +
                  height
              );
              sweetAlert();
            } else if (result.isConfirmed) {
              $.ajax({
                url: "../../../Functions/api/ApproveDocument.php",
                type: "POST",
                data: {
                  docID: data.fileID,
                  document: "D-PP",
                  orgCode: data.org_code,
                },

                success: function (response) {
                  if (response.status == "success") {
                    Swal.fire({
                      icon: "success",
                      title: "Document Approved",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity-25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    }).then(() => {
                      $("#APDocTable").DataTable().ajax.reload();
                    });
                  } else {
                    Swal.fire({
                      icon: "error",
                      title: "Document Approval Failed",
                      text: response.message,
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      customClass: {
                        popup:
                          "text-light rounded-0 bg-opacity25 glass-default",
                        confirmButton: "btn btn-sm",
                        denyButton: "btn btn-sm",
                        cancelButton: "btn btn-sm",
                      },
                    }).then(() => {
                      $("#APDocTable").DataTable().ajax.reload();
                    });
                  }
                },
                error: function (data) {
                  Swal.fire({
                    icon: "error",
                    title: "Document Approval Failed",
                    text: "Something went wrong. Please try again later",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    customClass: {
                      popup: "text-light rounded-0 bg-opacity-25 glass-default",
                      confirmButton: "btn btn-sm",
                      denyButton: "btn btn-sm",
                      cancelButton: "btn btn-sm",
                    },
                  }).then(() => {
                    $("#APDocTable").DataTable().ajax.reload();
                  });
                },
              });
            }
          });
        }
        sweetAlert();
      });

      if (table.rows().count() !== 0) {
        const PPContainer = $(".select-DT-PPs");
        PPContainer.addClass("d-flex justify-content-center gap-2 ms-1");
        const PPStatusSelect = document.createElement("select");
        PPStatusSelect.classList.add("form-select", "form-select-sm");
        PPStatusSelect.id = "PPDateSelect_PPs";
        PPStatusSelect.innerHTML =
          "<option value=''>All Date</option>" +
          "<option value='1'>Today</option>" +
          "<option value='2'>Yesterday</option>" +
          "<option value='3'>This Week</option>" +
          "<option value='4'>This Month</option>" +
          "<option value='5'>Last Month</option>" +
          "<option value='6'>This Year</option>";
        PPContainer.append(PPStatusSelect);
        const statusSearchFunction = function (settings, data, dataIndex) {
          const PPStatusVal = $("#PPDateSelect_PPs").val();
          if (!PPStatusVal) return true;
          const PPStatusString = data[5];
          const PPStatusCreated = new Date(PPStatusString);
          PPStatusCreated.setHours(0, 0, 0, 0);
          const PPToday = new Date();
          PPToday.setHours(0, 0, 0, 0);
          switch (PPStatusVal) {
            case "1":
              return PPStatusCreated.getTime() === PPToday.getTime();
            case "2": {
              const yesterday = new Date(PPToday);
              yesterday.setDate(yesterday.getDate() - 1);
              return PPStatusCreated.getTime() === yesterday.getTime();
            }
            case "3": {
              const weekStart = new Date(PPToday);
              weekStart.setDate(weekStart.getDate() - 7);
              return PPStatusCreated >= weekStart;
            }
            case "4": {
              const monthStart = new Date(
                PPToday.getFullYear(),
                PPToday.getMonth(),
                1
              );
              return PPStatusCreated >= monthStart;
            }
            case "5": {
              const lastMonthStart = new Date(
                PPToday.getFullYear(),
                PPToday.getMonth() - 1,
                1
              );
              const lastMonthEnd = new Date(
                PPToday.getFullYear(),
                PPToday.getMonth(),
                0
              );
              return (
                PPStatusCreated >= lastMonthStart &&
                PPStatusCreated <= lastMonthEnd
              );
            }
            case "6": {
              const yearStart = new Date(PPToday.getFullYear(), 0, 1);
              return PPStatusCreated >= yearStart;
            }
            default:
              return true;
          }
        };

        $.fn.dataTable.ext.search.push(statusSearchFunction);

        $("#PPDateSelect_PPs").on("change", function () {
          table.draw();
        });
      }
    },
  });

  $("#SubDocTable").DataTable({
    ordering: false,
    pageLength: 5,
    order: [[1, "desc"]],
    lengthMenu: [7],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Submissions",
      lengthMenu: "_MENU_",
      info: "Showing _START_ to _END_ of _TOTAL_ Submissions",
      infoEmpty: "",
      infoFiltered: "(filtered from _MAX_ total Submissions)",
      zeroRecords: "No matching Submissions found",
      thousands: ",",
      emptyTable: "Currently no Project Submissions available",
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
            className: "select-DT-SDs",
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
          placeholder: "Search Submissions",
        },
      },
      topEnd: {
        info: true,
      },
      bottomStart: null,
    },

    ajax: {
      url: "../../../Functions/api/getFileSummision.php",
      type: "GET",
      dataType: "json",
      dataSrc: "data",
    },

    columnDefs: [
      {
        targets: [0],
        orderable: false,
        visible: false,
        searchable: false,
      },
    ],

    columns: [
      { data: "ID" },
      { data: "fileID" },
      { data: "document" },
      { data: "name" },
      { data: "org_code" },
      {
        data: "DateCreated",
        render: function (data) {
          return new Date(data).toLocaleDateString("en-US", {
            month: "long",
            day: "numeric",
            year: "numeric",
          });
        },
      },
      {
        data: null,
        render: function (data) {
          if (role == 1) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-secondary rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> See Details</button>'
            );
          } else if (Position < 4) {
            return (
              '<button id="View-Btn" class="btn btn-sm btn-outline-success rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> View</button>'
            );
          } else {
            return (
              '<button id="View-Btn-P4" class="btn btn-sm btn-outline-success rounded-0">' +
              '<svg width="16" height="16" fill="currentColor">' +
              '<use xlink:href="#docAction"></use></svg> View</button>'
            );
          } 
        },
      },
    ],

    initComplete: function () {
      var Orgs = [];

      for (var i = 0; i < this.api().rows().data().length; i++) {
        if (!Orgs.includes(this.api().rows().data()[i].org_code)) {
          Orgs.push(this.api().rows().data()[i].org_code);
        }
      }

      var table = this.api();
      table.on("click", "#View-Btn", function () {
        var data = table.row($(this).parents("tr")).data();
        var path = "..\\..\\..\\..\\" + data.file_path;
        var width = window.innerWidth;
        var height = window.innerHeight;
        window.open(
          path,
          "_blank",
          "toolbar=0,location=0,menubar=0,width=" + width + ",height=" + height
        );
      });

      if (table.rows().count() !== 0) {
        const SDContainer = $(".select-DT-SDs");
        SDContainer.addClass("d-flex justify-content-center gap-2 w-50");
        const SDStatusSelect = document.createElement("select");
        SDStatusSelect.classList.add("form-select", "form-select-sm");
        SDStatusSelect.id = "SDDateSelect_SDs";
        SDStatusSelect.innerHTML =
          "<option value=''>All Date</option>" +
          "<option value='1'>Today</option>" +
          "<option value='2'>Yesterday</option>" +
          "<option value='3'>This Week</option>" +
          "<option value='4'>This Month</option>" +
          "<option value='5'>Last Month</option>" +
          "<option value='6'>This Year</option>";
        SDContainer.append(SDStatusSelect);

        const SDDoctypes = document.createElement("select");
        SDDoctypes.classList.add("form-select", "form-select-sm");
        SDDoctypes.id = "SDDocTypeSelect_SDs";
        SDDoctypes.innerHTML =
          "<option value=''>All Documents</option>" +
          "<option value='Activity Proposal'>Activity Proposal</option>" +
          "<option value='Excuse Letter'>Excuse Letter</option>" +
          "<option value='Meeting Minutes'>Meeting Minutes</option>" +
          "<option value='Office Memorandum'>Office Memorandum</option>" +
          "<option value='Project Proposal'>Project Proposal</option>";
        SDContainer.append(SDDoctypes);

        const DocTypeSearchFunction = function (settings, data, dataIndex) {
          const SDDocTypeVal = $("#SDDocTypeSelect_SDs").val();
          if (!SDDocTypeVal) return true;
          const SDDocTypeString = data[2];
          return SDDocTypeString === SDDocTypeVal;
        };

        $.fn.dataTable.ext.search.push(DocTypeSearchFunction);

        $("#SDDocTypeSelect_SDs").on("change", function () {
          table.draw();
        });

        const statusSearchFunction = function (settings, data, dataIndex) {
          const SDStatusVal = $("#SDDateSelect_SDs").val();
          if (!SDStatusVal) return true;
          const SDStatusString = data[5];
          const SDStatusCreated = new Date(SDStatusString);
          SDStatusCreated.setHours(0, 0, 0, 0);
          const SDToday = new Date();
          SDToday.setHours(0, 0, 0, 0);
          switch (SDStatusVal) {
            case "1":
              return SDStatusCreated.getTime() === SDToday.getTime();
            case "2": {
              const yesterday = new Date(SDToday);
              yesterday.setDate(yesterday.getDate() - 1);
              return SDStatusCreated.getTime() === yesterday.getTime();
            }
            case "3": {
              const weekStart = new Date(SDToday);
              weekStart.setDate(weekStart.getDate() - 7);
              return SDStatusCreated >= weekStart;
            }
            case "4": {
              const monthStart = new Date(
                SDToday.getFullYear(),
                SDToday.getMonth(),
                1
              );
              return SDStatusCreated >= monthStart;
            }
            case "5": {
              const lastMonthStart = new Date(
                SDToday.getFullYear(),
                SDToday.getMonth() - 1,
                1
              );
              const lastMonthEnd = new Date(
                SDToday.getFullYear(),
                SDToday.getMonth(),
                0
              );
              return (
                SDStatusCreated >= lastMonthStart &&
                SDStatusCreated <= lastMonthEnd
              );
            }
            case "6": {
              const yearStart = new Date(SDToday.getFullYear(), 0, 1);
              return SDStatusCreated >= yearStart;
            }
            default:
              return true;
          }
        };
        $.fn.dataTable.ext.search.push(statusSearchFunction);

        $("#SDDateSelect_SDs").on("change", function () {
          table.draw();
        });

        const SDOrgs = document.createElement("select");
        SDOrgs.classList.add("form-select", "form-select-sm");
        SDOrgs.id = "SDOrgSelect_SDs";
        SDOrgs.innerHTML = "<option value=''>All Organizations</option>";
        for (var i = 0; i < Orgs.length; i++) {
          SDOrgs.innerHTML +=
            "<option value='" + Orgs[i] + "'>" + Orgs[i] + "</option>";
        }
        SDContainer.append(SDOrgs);

        const OrgSearchFunction = function (settings, data, dataIndex) {
          const SDOrgVal = $("#SDOrgSelect_SDs").val();
          if (!SDOrgVal) return true;
          const SDOrgString = data[4];
          return SDOrgString === SDOrgVal;
        };

        $.fn.dataTable.ext.search.push(OrgSearchFunction);

        $("#SDOrgSelect_SDs").on("change", function () {
          table.draw();
        });
      }
    },
  });
});
