import {
  loadUsers,
  openChat,
  checkifISLogin,
  checkIfSessionChange,
  sessionAlert,
  fetch_Announcements,
} from "../Scripts/Modules/FeedModules.js";
import { QueueNotification } from "./Modules/Queueing_Notification.js";

$(document).ready(function () {
  setTimeout(function () {
    $("#EmptyFeed").addClass("d-none");
    $("#Announcements").removeClass("d-none");

    window.scrollTo(0, 0);
  }, 1000);

  // check if user is still logged in every 5 seconds
  setInterval(() => {
    checkifISLogin();
    checkIfSessionChange();
  }, 5000);

  // check if user has another session open every 500 milliseconds
  setInterval(() => {
    sessionAlert();
  }, 500);

  $("#messageInput").on("input", function () {
    let charCount = $("#charCount");
    let maxChar = 500;
    let char = $(this).val().length;
    charCount.text(`${char}/${maxChar}`);
    if (char >= maxChar) {
      charCount.addClass("text-danger");
    } else {
      charCount.removeClass("text-danger");
    }
  });

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

  function LogoutBTN() {
    Swal.mixin({
      toast: true,
      position: "bottom-start",
      showConfirmButton: true,
      confirmButtonText: "Logout",
      confirmButtonColor: "#d33",
      showCancelButton: true,
      cancelButtonText: "Cancel",
      customClass: {
        popup: "colored-toast",
        timerProgressBar: "colored-progress-bar",
      },
      timer: 10000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
      },

      preConfirm: () => {
        window.location.href = $("#Logout-Button").attr("data-LogoutLink");
      },
    }).fire({
      icon: "warning",
      title: "Are you sure you want to logout?",
    });
  }

  $("#Logout-Button").click(function () {
    LogoutBTN();
  });

  $("#Logout-NavButton").click(function () {
    LogoutBTN();
  });

  // page is at the bottom remove anal class
  $(window).scroll(function () {
    if ($(window).scrollTop() + $(window).height() == $(document).height()) {
      $(".anal").addClass("anals");
      $(".anals").removeClass("anal");
    } else {
      $(".anals").addClass("anal");
      $(".anal").removeClass("anals");
    }
  });

  $("#SearchUser").on("input", function () {
    let search = $(this).val();
    let result = $("#searchResult");
    let displaycount = 0;
    if (search.length > 0) {
      result.removeClass("d-none");
      result.empty();
      $.ajax({
        url: "../Functions/api/SearchUser.php",
        method: "POST",
        data: {
          search: search,
        },
        success: function (data) {
          result.empty();
          if (data.success) {
            data.data.forEach((user) => {
              let userSearchProfile = "";
              if (user.profile.includes("Default-Profile.gif")) {
                userSearchProfile = "../../Assets/Images/Default-Profile.gif";
              } else {
                userSearchProfile = `../../Assets/Images/UserProfiles/${data.profile}`;
              }

              let card = `<div class="card rounded-1 mb-2 conusers-card card-search user-select-none" id="${
                user.UUID
              }">
              <div class="card-body">
                                          <div class="hstack gap-1">
                                              <div class="position-relative">
                                                <img src="${userSearchProfile}" alt="" width="48" height="48" class="rounded-circle">
                                                <span class="position-absolute top-100 start-100 translate-middle p-1 border border-light rounded-circle ${
                                                  user.isLogin === 1
                                                    ? "bg-success"
                                                    : "bg-danger"
                                                }">
                                                  <span class="visually-hidden">New alerts</span>
                                                </span>
                                              </div>
                                              <div class="p-2">
                                                  <p class="alert-heading fw-bold moved text-wrap">${
                                                    user.First_Name
                                                  } ${user.Last_Name}</p>
                                                  <small class="moveu text-truncate" style="max-width: 150px;">${
                                                    user.isLogin === 1
                                                      ? `<span class="fw-bold text-success">Online</span>`
                                                      : `<span class="fw-light text-danger">Offline</span>`
                                                  }</small>
                                              </div>
                                          </div>
                                      </div>
                                  </div>`;
              displaycount++;
              if (user.UUID === UUID) {
                if (displaycount === 1) {
                  displaycount = 0;
                  result.append(
                    '<div class="alert alert-danger fw-bold text-center">No results found</div>'
                  );
                }
              } else {
                result.append(card);
                $(`#${user.UUID}`).on("click", function () {
                  if (user.isLogin === 1) {
                    $("#SearchUser").val("");
                    result.addClass("d-none");
                    openChat(
                      user.UUID,
                      `${user.First_Name} ${user.Last_Name}`,
                      user.primary_email,
                      user.profile,
                      user.isLogin
                    );
                  } else {
                    QueueNotification([
                      "info",
                      `${user.First_Name} ${user.Last_Name} is offline`,
                      1500,
                      "bottom-end",
                    ]);
                  }
                });
              }
            });
          } else {
            result.append(
              '<div class="alert alert-danger fw-bold text-center">No results found</div>'
            );
          }
        },

        error: function () {
          result.append(
            '<div class="alert alert-secondary text-center fw-bold"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>'
          );
          setTimeout(() => {
            result.empty();
            result.append(
              '<div class="alert alert-danger text-center fw-bold shake">An error occurred</div>'
            );
          }, 3000);
        },

        complete: function () {
          if (displaycount === 0) {
            result.empty();
            result.append(
              '<div class="alert alert-danger fw-bold text-center">No results found</div>'
            );
          } else {
            result.append(
              `<small class="text-center fw-bold text-muted">Displaying ${displaycount} results</small>`
            );
          }
        },
      });
    } else {
      result.addClass("d-none");
    }
  });

  loadUsers();
  setInterval(() => {
    loadUsers();
  }, 3000);

  const driver = window.driver.js.driver;

  const DesktopView = driver({
    shoProgress: true,
    steps: [
      {
        element: "#Feed-btn",
        popover: {
          title: "Announcements Feeds",
          description:
            "View the latest announcements posted by your organization",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#Profile-btn",
        popover: {
          title: "Profile Settings",
          description:
            "Customize your profile settings and post a Announcements",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#Dashboard-btn",
        popover: {
          title: "Dashboard",
          description: "Open your Assigned Organization Dashboard",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#Placeholder-btn",
        popover: {
          title: "Placeholder",
          description: "This is just a placeholder",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#Preferences-btn",
        popover: {
          title: "Preferences",
          description: "Modify your Website Preferences",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#Logout-Button",
        popover: {
          title: "Logout",
          description: "Logout your account and end your session",
          side: "left",
          align: "start",
        },
      },
      {
        element: ".anal",
        popover: {
          title: "Announcements",
          description:
            "See the latest announcements posted by your organization",
          side: "top",
          align: "start",
        },
      },
      {
        element: "#SearchUser",
        popover: {
          title: "Search User",
          description: "Search for a user and start a conversation",
          side: "top",
          align: "start",
        },
      },
      {
        element: "#userContainers",
        popover: {
          title: "Chat History",
          description: "View your chat history with other users",
          side: "top",
          align: "start",
        },
      },
    ],
    allowClose: false,
  });

  const MobileView = driver({
    shoProgress: true,
    steps: [
      {
        element: "#b_ItemNav_Log",
        popover: {
          title: "Announcements Feeds",
          description:
            "View the latest announcements posted by your organization",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#b_ItemNav_Reg",
        popover: {
          title: "Profile Settings",
          description:
            "Customize your profile settings and post a Announcements",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#b_ItemNav_For",
        popover: {
          title: "Dashboard",
          description: "Open your Assigned Organization Dashboard",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#b_ItemNav_PSet",
        popover: {
          title: "Placeholder",
          description: "This is just a placeholder",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#b_ItemNav_Pref",
        popover: {
          title: "Preferences",
          description: "Modify your Website Preferences",
          side: "left",
          align: "start",
        },
      },
      {
        element: "#Logout-NavButton",
        popover: {
          title: "Logout",
          description: "Logout your account and end your session",
          side: "left",
          align: "start",
        },
      },
      {
        element: ".anal",
        popover: {
          title: "Announcements",
          description:
            "See the latest announcements posted by your organization",
          side: "top",
          align: "start",
        },
      },
      {
        element: "#SearchUser",
        popover: {
          title: "Search User",
          description: "Search for a user and start a conversation",
          side: "top",
          align: "center",
        },
      },
      {
        element: "#userContainers",
        popover: {
          title: "Chat History",
          description: "View your chat history with other users",
          side: "top",
          align: "center",
        },
      },
    ],
    allowClose: false,
  });

  $("#Placeholder-btn").on("click", function () {
    DesktopView.drive();
  });

  $("#b_ItemNav_PSet").on("click", function () {
    MobileView.drive();
  });

  fetch_Announcements();
});
