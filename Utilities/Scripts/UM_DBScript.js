import { QueueNotification } from "./Modules/Queueing_Notification.js";

function loadNUPData(page) {
  $.ajax({
    url: "../../../../Src/Functions/api/getNUP.php",
    type: "POST",
    data: { page: page, pageSize: pageSize },
    success: function (res) {
      var NUPData = $("#NUP_Table_Data");
      NUPData.empty();

      // Populate the table with data
      var statformat = "";
      res.data.forEach(function (data) {
        if (data.accountStat === "pending") {
          statformat = `<span class="badge bg-warning">Pending</span>`;
        } else if (data.accountStat === "active") {
          statformat = `<span class="badge bg-success">Active</span>`;
        } else if (data.accountStat === "rejected") {
          statformat = `<span class="badge bg-danger">Rejected</span>`;
        } else {
          statformat = `<span class="badge bg-secondary">Unknown</span>`;
        }

        NUPData.append(`<tr class="align-middle">
                <td><span class="text-truncate">${data.fullName}</span></td>
                <td><span class="text-truncate">${data.primary_email}</span></td>
                <td><span class="text-truncate">${data.contactNumber}</span></td>
                <td><span class="text-truncate">${data.course_code}</span></td>
                <td><span class="text-truncate">${data.student_Number}</span></td>
                <td>${statformat}</td>
                <td class="vstack gap-1">
                  <button class="btn btn-sm btn-outline-success" id="approve-${data.UUID}">
                    <svg class="me-1" width="24" height="24"><use xlink:href="#Approved" /></svg>
                    <span class="d-none d-md-inline">Approve</span>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#RejectModal" onclick="getNUPInfo(${data.UUID})">
                    <svg class="me-1" width="24" height="24"><use xlink:href="#Rejected" /></svg>
                    <span class="d-none d-md-inline">Reject</span>
                  </button>
                </td>
              </tr>`);

        $(`#approve-${data.UUID}`).click(function () {
          $("#userUUID").val(data.UUID);
          asignRole.show();
        });
      });

      // Generate pagination
      pagination.empty();
      pagination.append(`<li class="page-item ${res.prev ? "" : "disabled"}">
                                <span class="page-link" href="#" onclick="loadNUPData(${
                                  res.prev || page
                                })">Previous</span>
                              </li>`);
      for (var i = 1; i <= res.totalPages; i++) {
        pagination.append(`<li class="page-item ${i === page ? "active" : ""}">
          <span class="page-link" href="#" onclick="loadNUPData(${i})">${i}</span>
          </li>`);
      }
      pagination.append(`<li class="page-item ${res.next ? "" : "disabled"}">
                                <span class="page-link" href="#" onclick="loadNUPData(${
                                  res.next || page
                                })">Next</span>
                              </li>`);
    },
    error: function (xhr, status, error) {
      console.error("Error loading data:", error);
    },
  });
}

if ($("#NUP-tab-pane").hasClass("active")) {
  $("#user-tab").removeClass("active");
  $("#admin-tab").removeClass("active");
  var pagination = $("#NUP_Pagination");
  var currentPage = 1;
  var pageSize = 5;
  const asignRole = new bootstrap.Modal(document.getElementById("userRole"), {
    keyboard: false,
    backdrop: "static",
  });

  $.ajax({
    url: "../../../Functions/api/getOrgData.php",
    type: "POST",
    success: function (data) {
      data.forEach((org) => {
        $("#InputOrg").append(
          `<option value="${org.org_code}">${org.org_name}</option>`
        );
      });
    },
  });

  $("#InputOrg").on("change", function () {
    if ($("#InputOrg").val() == "Choose...") {
      $("#InputRole").prop("disabled", true);
    } else {
      $("#InputRole").prop("disabled", false);
      $("#InputRole").empty();
      $("#InputRole").append("<option selected hidden>Choose...</option>");
      if ($("#InputOrg").val() == 10001) {
        $("#InputRole").append(
          '<option value="2" Selected>CSG Officer</option>'
        );
      } else {
        $("#InputRole").append('<option value="2">CSG Officer</option>');
      }
      $("#InputRole").append('<option value="3">Officer</option>');
    }
  });

  // Initial load
  loadNUPData(currentPage);
} else {
  $("#NUP_Table_Data").empty();
}

$(document).ready(function () {
  $("#searchUser").on("change keyup paste", function () {
    var value = $(this).val().toLowerCase();
    var hasResult = false;
    $("#NUP_Table_Data tr").filter(function () {
      var match = $(this).text().toLowerCase().indexOf(value) > -1;
      $(this).toggle(match);
      if (match) {
        hasResult = true;
      }
    });
    if (!hasResult) {
      $("#NUP_Table_Data").append(
        '<tr class="no-result"><td colspan="7" class="text-center">No matching records found.</td></tr>'
      );
    } else {
      $("#NUP_Table_Data .no-result").remove();
    }
  });

  $("#saveRole").click(function () {
    var userUUID = $("#userUUID").val();
    var userRole = $("#InputRole").val();
    var userOrg = $("#InputOrg").val();
    var userIposition = $("#InputPosition").val();

    if (userRole == null) {
      QueueNotification(["error", "Please select a role."]);
      return;
    }

    if (userOrg == "Choose...") {
      QueueNotification(["error", "Please select an organization."]);
      return;
    }

    if (userIposition == "Choose...") {
      QueueNotification(["error", "Please input a position."]);
      return;
    }

    $.ajax({
      url: "../../../Functions/api/postAddRole.php",
      type: "POST",
      data: {
        userUUID: userUUID,
        userRole: userRole,
        userOrg: userOrg,
        userIposition: userIposition,
      },
      success: function (res) {
        //response(['status' => 'success', 'message' => 'User role updated successfully']);
        if (res.status == "success") {
          QueueNotification(["success", "User role updated successfully"]);
          $("#userRole").modal("hide");
          loadNUPData(1);
        } else {
          QueueNotification(["error", "Failed to update user role"]);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error updating user role:", error);
      },
    });
  });
});
