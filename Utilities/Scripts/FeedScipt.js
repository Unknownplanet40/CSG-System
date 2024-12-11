import {
  loadUsers,
  openChat,
  checkifISLogin,
  checkIfSessionChange,
  sessionAlert,
  fetch_Announcements,
} from "../Scripts/Modules/FeedModules.js";
import { QueueNotification } from "./Modules/Queueing_Notification.js";

let currentTheme = theme === "dark" ? "dark" : "light";

$(document).ready(function () {
  const calendar = new tui.Calendar("#calendar", {
    defaultView: "month",
    isReadOnly: true,
    useDetailPopup: true,
    useCreationPopup: false,
    timezone: {
      zones: [
        {
          timezoneName: "Asia/Manila",
          displayLabel: "Philippine Time",
          tooltip: "Philippine Time",
        },
      ],
    },
    borderRadius: "5px",
    template: {
      alldayTitle: "All Day",
      time: function (schedule) {
        return `<span class="fw-bold text-capitalize text-body">${schedule.title}</span>`;
      },
      popupDetailTitle: function () {
        return "<span class='text-body fw-bold text-uppercase'>Event Details</span>";
      },

      popupDetailDate: function (Schedule) {
        return `<span class="fw-bold">Date: </span> <span>${
          Schedule.start
            ? new Date(Schedule.start).toLocaleString("en-US", {
                month: "long",
                day: "numeric",
                year: "numeric",
              })
            : ""
        }</span>`;
      },

      popupDetailBody: function (schedule) {
        return `<div class="d-flex flex-column gap-2">
        <div class="d-flex gap-2">
          <div class="fw-bold">Title:</div>
          <div>${schedule.title || ""}</div>
        </div>
      <div class="d-flex gap-2">
        <div class="fw-bold">Start:</div>
        <div>${
          schedule.start
            ? new Date(schedule.start).toLocaleString("en-US", {
                month: "long",
                day: "numeric",
                year: "numeric",
                hour: "numeric",
                minute: "numeric",
                hour12: true,
              })
            : ""
        }</div>
      </div>
      <div class="d-flex gap-2">
        <div class="fw-bold">End:</div>
        <div>${
          schedule.end
            ? new Date(schedule.end).toLocaleString("en-US", {
                month: "long",
                day: "numeric",
                year: "numeric",
                hour: "numeric",
                minute: "numeric",
                hour12: true,
              })
            : ""
        }</div>
      </div>
      <div class="d-flex gap-2">
        <div class="fw-bold">Body:</div>
        <div>${schedule.body || ""}</div>
      </div>
      `;
      },
    },
  });

  calendar.setOptions({
    month: {
      isAlways6Weeks: false,
      startDayOfWeek: 0,
      dayNames: [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
      ],
      narrowWeekend: true,
    },
  });

  calendar.setTheme({
    common: {
      backgroundColor: "var(--bs-body-bg)",
      border: "1px solid var(--bs-body-border-color)",
      dayName: {
        color: "var(--bs-body-text-color)",
      },
      holiday: {
        color: "var(--bs-danger)",
      },
      saturday: {
        color: "var(--bs-primary)",
      },
    },
    month: {
      dayExceptThisMonth: {
        color: "var(--bs-secondary)",
      },
      dayName: {
        borderLeft: "none",
        backgroundColor: "var(--bs-primary-bg-subtle)",
      },
      weekend: {
        backgroundColor: "var(--bs-primary-bg-subtle)",
      },
      moreView: {
        backgroundColor: "var(--bs-body-bg)",
        color: "var(--bs-body-text-color)",
        boxShadow: "0 2px 6px 0 rgba(0, 0, 0, 0.1)",
        borderRadius: "5px",
        height: "256px",
      },
      moreViewTitle: {
        backgroundColor: "var(--bs-secondary)",
        borderRadius: "5px",
      },
    },
    popup: {
      backgroundColor: "var(--bs-body-bg)",
      border: "1px solid var(--bs-body-border-color)",
      showArrow: true,
    },
  });

  function UpdateEvents() {
    $.ajax({
      url: "../Functions/api/getCalendarEvents.php",
      method: "GET",

      success: function (data) {
        if (data.status === "success") {
          if (data.data.length === 0) {
            return;
          }
          calendar.clear();

          data.data.forEach((event) => {
            let start = new Date(event.start);
            let end = new Date(event.end);

            calendar.createEvents([
              {
                id: event.id,
                calendarId: event.calendarId,
                title: event.title,
                category: event.category,
                body: event.body,
                location: event.location,
                state: event.state,
                attendees: [event.attendees[0]],
                isReadOnly: event.isReadOnly,
                start: start,
                end: end,
                backgroundColor: `var(--${event.backgroundColor})`,
              },
            ]);
          });
        } else {
          console.log("An error occurred while fetching events");
        }
      },

      error: function () {
        console.log("An error occurred while fetching events");
      },
    });
  }

  UpdateEvents();
  setInterval(() => {
    if (!$("#CalendarEvent").hasClass("collapsed")) {
      UpdateEvents();
    }
  }, 3000);

  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.addedNodes.length > 0) {
        $(
          ".toastui-calendar-popup-section.toastui-calendar-section-button"
        ).remove();
      }
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });

  $("#nextBtn").on("click", function () {
    calendar.next();
    let currentMonth = calendar.getDate().getMonth();
    let currentYear = calendar.getDate().getFullYear();
    let formattedMonth = new Intl.DateTimeFormat("en-US", {
      month: "long",
    }).format(new Date(currentYear, currentMonth));
    $("#CurrentMonth").html(`${formattedMonth} ${currentYear}`);
  });

  $("#prevBtn").on("click", function () {
    calendar.prev();
    let currentMonth = calendar.getDate().getMonth();
    let currentYear = calendar.getDate().getFullYear();
    let formattedMonth = new Intl.DateTimeFormat("en-US", {
      month: "long",
    }).format(new Date(currentYear, currentMonth));
    $("#CurrentMonth").html(`${formattedMonth} ${currentYear}`);
  });

  $("#todayBtn").on("click", function () {
    calendar.today();
    let currentMonth = calendar.getDate().getMonth();
    let currentYear = calendar.getDate().getFullYear();
    let formattedMonth = new Intl.DateTimeFormat("en-US", {
      month: "long",
    }).format(new Date(currentYear, currentMonth));
    $("#CurrentMonth").html(`${formattedMonth} ${currentYear}`);
  });

  $("#addEvent").click(function () {
    var title = $("#eventTitle").val();
    var location = $("#eventLocation").val();
    var description = $("#eventDescription").val();
    var color = $("#eventColor").val();
    var start = $("#eventStart").val();
    var end = $("#eventEnd").val();

    if (!title || !location || !description || !color || !start || !end) {
      QueueNotification(["info", "Please fill up all fields", 1500, "top"]);

      const fields = [
        { id: "#eventTitle", value: title },
        { id: "#eventLocation", value: location },
        { id: "#eventDescription", value: description },
        { id: "#eventColor", value: color },
        { id: "#eventStart", value: start },
        { id: "#eventEnd", value: end },
      ];

      fields.forEach((field) => {
        if (!field.value) {
          $(field.id).addClass("is-invalid");
        } else {
          $(field.id).removeClass("is-invalid");
        }
      });

      return;
    }

    $.ajax({
      url: "../Functions/api/postEvent.php",
      method: "POST",
      data: {
        UUID: UUID,
        title: title,
        location: location,
        description: description,
        color: color,
        start: start,
        end: end,
      },

      beforeSend: function () {
        $("#addEvent").prop("disabled", true);
        $("#addEvent").html(
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...'
        );
      },

      success: function (data) {
        if (data.status === "success") {
          QueueNotification([
            "success",
            "Event added successfully",
            1500,
            "top",
          ]);
          UpdateEvents();

          $("#eventTitle").val("");
          $("#eventLocation").val("");
          $("#eventDescription").val("");
          $("#eventColor").val("");
          $("#eventStart").val("");
          $("#eventEnd").val("");

          $("#addEventModal").modal("hide");

          $("#addEvent").prop("disabled", false);
          $("#addEvent").html("Add Event");
        } else {
          QueueNotification(["error", "An error occurred", 1500, "top"]);
        }
      },

      error: function () {
        QueueNotification(["error", "An error occurred", 1500, "top"]);
        $("#addEvent").prop("disabled", false);
        $("#addEvent").html("Add Event");
      },
    });
  });

  setTimeout(function () {
    $("#EmptyFeed").addClass("d-none");
    $("#Announcements").removeClass("d-none");

    window.scrollTo(0, 0);
  }, 1000);

  let inactivityTimeout;

  function resetInactivityTimer() {
    clearTimeout(inactivityTimeout);
    let warningShown = false;

    inactivityTimeout = setTimeout(() => {
      if (!warningShown) {
        warningShown = true;
        Swal.fire({
          title: "Inactivity Alert",
          text: "We noticed that you have been inactive for a while. To continue using the system, please interact with the page.",
          icon: "warning",
          confirmButtonText: "Got it",
        });
        setTimeout(() => {
          location.reload();
        }, 15 * 60 * 1000); // 15 minutes
      }
    }, 30 * 60 * 1000); // 30 minutes
  }

  window.addEventListener("load", resetInactivityTimer);

  // Reset timer on user activity
  ["mousedown", "mousemove", "keypress", "scroll", "touchstart"].forEach(
    (event) => {
      document.addEventListener(event, resetInactivityTimer);
    }
  );

  // check if user is still logged in every 5 seconds
  setInterval(() => {
    //checkifISLogin();
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
    /* Swal.mixin({
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
    }); */
    window.location.href = $("#Logout-Button").attr("data-LogoutLink");
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
                userSearchProfile = `../../Assets/Images/UserProfiles/${user.profile}`;
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
                                                      ? `
                                                      <div class="hstack gap-1 fw-bold text-success">
                                                        <span>${user.org} ${user.position}</span>
                                                      </div>`
                                                      : `
                                                      <div class="hstack gap-1 fw-bold text-danger">
                                                        <span>${user.org} ${user.position}</span>
                                                      </div>`
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

  // get the current filename of the page
  const filename = window.location.pathname.split("/").pop();

  if (filename === "Feed.php") {
    loadUsers();
    setInterval(() => {
      loadUsers();
    }, 3000);
  }

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
