import { QueueNotification } from "./Queueing_Notification.js";

let usersCons = $("#userContainers");
let isAlertOpen = false;

export function openChat(
  toUserUUID,
  toUserFullName,
  toUserEmail,
  Profile = "Default-Profile.gif",
  isActive = 0
) {
  const chatRoom = new bootstrap.Modal("#chatRoom", {
    keyboard: false,
  });

  chatRoom.show();

  $("#toUserFullName").text(toUserFullName);
  $("#toUserEmail").text(isActive === 1 ? "Active Now" : "Currently Offline");
  $("#toUserUUID").val(toUserUUID);
  if (Profile === "Default-Profile.gif") {
    $("#toUserImage").attr("src", "../../Assets/Images/Default-Profile.gif");
  } else {
  $("#toUserImage").attr("src", "../../Assets/Images/UserProfiles/" + Profile);
  }
  let chatContainer = $("#messageContainer");
  let chatInput = $("#messageInput");
  let chatSend = $("#sendMessage");

  function updatemessage() {
    $.ajax({
      url: "../Functions/api/getMessages.php",
      type: "GET",
      data: {
        FromUser: UUID,
        ToUser: toUserUUID,
      },

      success: function (data) {
        if (data.status === "success") {
          chatContainer.empty();
          $("#noMessage").addClass("d-none");
          $("#messageContainer").removeClass("d-none");
          data.data.forEach((message) => {
            let date = new Date(message.Date);
            let newformat = "";
            let dateNow = new Date();
            let diff = dateNow - date;
            let diffSeconds = diff / 1000;
            let diffMinutes = diffSeconds / 60;
            let diffHours = diffMinutes / 60;
            let diffDays = diffHours / 24;
            let diffMonths = diffDays / 30;
            let diffYears = diffMonths / 12;
            let time =
              (date.getHours() % 12 || 12) +
              ":" +
              (date.getMinutes() < 10
                ? "0" + date.getMinutes()
                : date.getMinutes()) +
              " " +
              (date.getHours() >= 12 ? "PM" : "AM");

            if (diffSeconds < 60) {
              newformat = Math.floor(diffSeconds) + " seconds ago";
            } else if (diffMinutes < 60) {
              newformat = Math.floor(diffMinutes) + " minutes ago";
            } else if (diffHours < 24) {
              newformat = Math.floor(diffHours) + " hours ago";
            } else if (diffDays < 30) {
              newformat = Math.floor(diffDays) + " days ago at " + time;
            } else if (diffMonths < 12) {
              newformat = Math.floor(diffMonths) + " months ago at " + time;
            } else {
              newformat = Math.floor(diffYears) + " years ago at " + time;
            }

            if (message.FromUser !== UUID) {
              if (message.isDeleted === 1) {
                chatContainer.append(`
                    <div class="col-12">
                        <div class="d-flex justify-content-start">
                            <div class="vstack d-flex align-items-start">
                                <span class="p-2 rounded-3 text-danger-emphasis bg-danger-subtle text-wrap me-5">This message was deleted</span>
                                <small class="text-body mb-2 user-select-none">${newformat}</small>
                            </div>
                        </div>
                    </div>`);
              } else {
                chatContainer.append(`
                          <div class="col-12">
                              <div class="d-flex justify-content-start">
                                  <div class="vstack d-flex align-items-start">
                                      <span class="p-2 rounded-1 text-bg-secondary bg-opacity-100 text-wrap me-5">${message.Message}</span>
                                      <small class="text-body mb-2 user-select-none">${newformat}</small>
                                  </div>
                              </div>
                          </div>`);
              }
            } else {
              if (message.isDeleted === 1) {
                chatContainer.append(`
                        <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <div class="vstack d-flex align-items-end">
                                        <span class="p-2 rounded-3 text-danger-emphasis bg-danger-subtle text-wrap ms-5">This message was deleted</span>
                                        <small class="text-body mb-2 user-select-none">${newformat}</small>
                                    </div>
                                </div>
                            </div>`);
              } else {
                chatContainer.append(`
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <div class="hstack">
                                    <div class="vstack d-flex align-items-end">
                                        <div>
                                          <button class="btn btn-sm btn-danger bg-transparent border-0" id="Umid-${message.UMID}">
                                            <svg width="16" height="16" class="text-danger fw-bold">
                                              <use xlink:href="#Trash"></use>
                                            </svg>
                                          </button>
                                          <span class="p-2 rounded-1 text-bg-primary bg-opacity-100 text-wrap">${message.Message}</span>
                                        </div>
                                        <small class="text-body mb-3 mt-1 user-select-none">${newformat}</small>
                                    </div>
                                    </div>
                                </div>
                            </div>`);
              }

              $(`#Umid-${message.UMID}`).on("click", function () {
                swal
                  .fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!",
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    reverseButtons: true,
                    customClass: {
                      popup: "alert-popup-inform",
                      confirmButton:
                        "alert-button-confirm btn btn-sm btn-danger",
                      cancelButton: "btn btn-sm btn-primary",
                      container: "alert-container",
                      htmlContainer: "alert-html-container",
                      title: "alert-title",
                    },
                  })
                  .then((result) => {
                    if (result.isConfirmed) {
                      $.ajax({
                        url: "../Functions/api/deleteMessage.php",
                        type: "POST",
                        data: {
                          UMID: message.UMID,
                        },
                        success: function (data) {
                          if (data.status === "success") {
                            updatemessage();
                          }
                        },
                      });
                    }
                  });
              });
            }
          });
        } else {
          chatContainer.empty();
          $("#noMessage").removeClass("d-none");
          $("#messageContainer").addClass("d-none");
        }
      },
    });
  }

  let refreshchat = null;

  updatemessage();
  $("#messageBody").animate({ scrollTop: 1000000 }, "slow");
  refreshchat = setInterval(() => {
    updatemessage();
  }, 1000);

  $("#closeChatRoom").on("click", function () {
    clearInterval(refreshchat);
  });

  chatSend.on("click", function () {
    let message = chatInput.val();
    if (message === "") {
      chatInput.addClass("is-invalid");

      setTimeout(() => {
        chatInput.removeClass("is-invalid");
      }, 3000);

      return;
    } else {
      $.ajax({
        url: "../Functions/api/sendMessage.php",
        type: "POST",
        data: {
          FromUser: UUID,
          ToUser: toUserUUID,
          Message: message,
          isActivewhenSend: isActive === 1 ? 0 : 1,
        },

        success: function (data) {
          if (data.status === "success") {
            updatemessage();
            chatInput.val("");
            $("#messageBody").animate({ scrollTop: 1000000 }, "slow");
          } else {
            chatContainer.empty();
            chatContainer.append(
              `<div class="alert alert-danger text-center fw-bold">No messages found</div>`
            );
          }
        },
      });
    }
  });
}

export function loadUsers() {
  $.ajax({
    url: "../Functions/api/OnlineUsers.php",
    type: "GET",
    data: {
      UUID: UUID,
    },
    success: function (data) {
      if (data.status === "info") {
        usersCons.empty();
        usersCons.append(`
                    <div class="card rounded-1 mb-2 conusers-card card-search border-0 text-bg-secondary bg-opacity-25">
                        <div class="card-body">
                            <div class="hstack gap-1 d-flex justify-content-center">
                                <svg width="48" height="48" class="text-body">
                                    <use xlink:href="#NoUsers"></use>
                                </svg>
                                <div class="ps-3">
                                    <p class="fw-bold my-1 text-body">No Users Found</p>
                                </div>
                            </div>
                        </div>
                    </div>`);
      } else if (data.status === "error") {
        usersCons.empty();
        usersCons.append(`
                    <div class="card rounded-1 mb-2 conusers-card card-search border-0">
                        <div class="card-body">
                            <div class="hstack gap-1 d-flex justify-content-center">
                                <svg width="48" height="48">
                                    <use xlink:href="#NoUsers"></use>
                                </svg>
                                <div class="ps-3">
                                    <p class="fw-bold text-body my-1"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>`);
        setTimeout(() => {
          usersCons.empty();
          usersCons.append(`
                    <div class="card rounded-1 mb-2 conusers-card card-search border-0 text-bg-danger">
                        <div class="card-body">
                            <div class="hstack gap-1 d-flex justify-content-center">
                                <svg width="48" height="48">
                                    <use xlink:href="#Close"></use>
                                </svg>
                                <div class="ps-3">
                                    <p class="fw-bold my-1">Oops!</p>
                                    <p class="text-body my-1">Something went wrong</p>
                                </div>
                            </div>
                        </div>
                    </div>`);
        }, 2500);
      } else {
        usersCons.empty();
        data.data.forEach((user) => {
          var randNum = Math.floor(Math.random() * 1000);
          let userProfPath = "";

          if (user.toUser_Profile.includes("Default-Profile.gif")) {
            userProfPath = "../../Assets/Images/Default-Profile.gif";
          } else {
            userProfPath = `../../Assets/Images/UserProfiles/${user.toUser_Profile}`;
          }
          
          usersCons.append(`
                <div class="card rounded-0 border-0 mb-2 conusers-card" id="user-${randNum}">
                    <div class="card-body">
                        <div class="hstack gap-1">
                            <div class="position-relative">
                                <img src="${userProfPath}" alt="" width="48" height="48" class="rounded-circle">
                                <span class="position-absolute top-100 start-100 translate-middle p-1 ${
                                  user.isLogin === 1
                                    ? `bg-success`
                                    : `bg-danger`
                                } border border-light rounded-circle">
                                    <span class="visually-hidden">New alerts</span>
                                </span>
                            </div>
                            <div class="p-2">
                                <p class="alert-heading fw-bold mb-0 text-wrap">${
                                  user.First_Name
                                } ${user.Last_Name}</p>
                                ${
                                  user.Sentby === "You"
                                    ? `<small class="fw-light d-inline-block text-truncate" style="max-width: 115px;">You: <span class="fw-light">${
                                        user.currentMessageDeleted === true
                                          ? `This message was deleted`
                                          : user.latestMessage
                                      }</span></small>`
                                    : `<small class="fw-light d-inline-block text-truncate" style="max-width: 115px;">${
                                        user.currentMessageDeleted === true
                                          ? `This message was deleted`
                                          : user.latestMessage
                                      }</small>`
                                }
                            </div>
                        </div>
                    </div>
                </div>`);
          $(`#user-${randNum}`).on("click", function () {
            if (user.isLogin === 1 || user.isLogin === 0) {
              openChat(
                user.UUID,
                `${user.First_Name} ${user.Last_Name}`,
                user.primary_email,
                user.toUser_Profile,
                user.isLogin
              );
            } else {
              $(`#user-${randNum}`).addClass("shake");
              setTimeout(() => {
                $(`#user-${randNum}`).removeClass("shake");
              }, 1000);
            }
          });
        });
      }
    },

    error: function () {
      usersCons.empty();
      usersCons.append(`
                    <div class="card rounded-1 mb-2 conusers-card card-search border-0">
                        <div class="card-body">
                            <div class="hstack gap-1 d-flex justify-content-center">
                                <svg width="48" height="48">
                                    <use xlink:href="#NoUsers"></use>
                                </svg>
                                <div class="ps-3">
                                    <p class="fw-bold text-body my-1"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>`);
      setTimeout(() => {
        usersCons.empty();
        usersCons.append(`
                    <div class="card rounded-1 mb-2 conusers-card card-search border-0 text-bg-danger">
                        <div class="card-body rounded-1">
                            <div class="hstack gap-1 d-flex justify-content-center">
                                <svg width="48" height="48">
                                    <use xlink:href="#Close"></use>
                                </svg>
                                <div class="ps-3">
                                    <p class="fw-bold my-1">Oops!</p>
                                    <p class="text-body my-1">Something went wrong</p>
                                </div>
                            </div>
                        </div>
                    </div>`);
      }, 3000);
    },

    complete: function () {
      $("#userLoading").addClass("d-none");
      $("#userContainers").removeClass("d-none");
    },
  });
}

export function checkifISLogin() {
  $.ajax({
    url: "../Functions/api/checkUserLogin.php",
    type: "GET",
    data: {
      UUID: UUID,
    },
    success: function (data) {
      if (data.status === "success") {
        if (!data.isLogin) {
          window.location.href =
            "../../Src/Functions/api/UserLogout.php?error=001";
        }
      }
    },
  });
}

export function checkIfSessionChange() {
  $.ajax({
    url: "../Functions/api/checkSession.php",
    type: "GET",
    data: {
      UUID: UUID,
      SessionID: localStorage.getItem("currentSession"),
    },
    success: function (data) {
      if (data.status === "success") {
        if (data.isSessionChange) {
          localStorage.setItem("anotherSession", "true");
        } else {
          localStorage.removeItem("anotherSession");
        }
      }
    },
  });
}

export function sessionAlert() {
  if (
    localStorage.getItem("anotherSession") !== null &&
    localStorage.getItem("anotherSession") === "true"
  ) {
    if (!isAlertOpen) {
      isAlertOpen = true;

      // lagout text array
      let funnyLogoutButtons = [
        "Bye Felicia!",
        "Adios, Amigos!",
        "Peace Out!",
        "I’m Outta Here!",
        "Log Out and Chill",
        "Escape!",
        "Poof, Gone!",
        "See Ya, Wouldn’t Wanna Be Ya!",
        "Ninja Vanish!",
        "Take the Exit Ramp!",
      ];

      swal
        .fire({
          title: "Security Notice",
          text: "We have detected an active session in your account. For your security, please log out and sign back in to confirm your identity and ensure the safety of your account.",
          icon: "warning",
          confirmButtonText:
            funnyLogoutButtons[
              Math.floor(Math.random() * funnyLogoutButtons.length)
            ],
          allowOutsideClick: false,
          confirmButtonColor: "#d33",
          customClass: {
            popup: "alert-popup-inform",
            confirmButton: "alert-button-confirm",
            container: "alert-container",
            htmlContainer: "alert-html-container",
            title: "alert-title",
          },
          didClose: () => {
            isAlertOpen = false;
          },
        })
        .then((result) => {
          if (result.isConfirmed) {
            localStorage.removeItem("currentSession");
            localStorage.removeItem("anotherSession");
            window.location.href =
              "../../Src/Functions/api/UserLogout.php?error=003";
            isAlertOpen = false;
          }
        });
    }
  }
}

export function fetch_Announcements() {
  let annCon = $("#Announcements"); 
  $.ajax({
    url: "../Functions/api/getAnnouncements.php",
    type: "GET",
    success: function (data) {
      if (data.status === "success") {
        annCon.empty();

        // check data.data count
        if (data.data.length > 2) {
          $('#annsmain').addClass('ansscroll');
        } else {
          $('#annsmain').removeClass('ansscroll');
        }

        data.data.forEach((announcement) => {
          // Format the likes and dislikes
          let postLikes = announcement.postLikes;
          let postDislikes = announcement.postDislikes;
          let largeNum = ["K", "M", "B", "T"];
          let lastPost = "";
          let profpath = "";

          if (data.data[data.data.length - 1] !== announcement) {
            lastPost = "anal";
          }

          if (announcement.profileImage === "Default-Profile.gif") {
            profpath = "../../Assets/Images/Default-Profile.gif";
          } else {
            profpath = "../../Assets/Images/UserProfiles/" + announcement.profileImage;
          } 

          function formatNumber(num) {
            let formatted = num;

            for (let i = 0; i < largeNum.length && num >= 1000; i++) {
              num /= 1000;
              formatted = num.toFixed(1) + largeNum[i];
            }

            return formatted;
          }

          let postLikesFormatted = formatNumber(postLikes);
          let postDislikesFormatted = formatNumber(postDislikes);

          // Format the date
          let adate = new Date(announcement.postedDate);
          let anow = new Date();
          let adiff = anow - adate;
          let adiffSeconds = adiff / 1000;
          let adiffMinutes = adiffSeconds / 60;
          let adiffHours = adiffMinutes / 60;
          let adiffDays = adiffHours / 24;
          let adiffMonths = adiffDays / 30;
          let adiffYears = adiffMonths / 12;

          if (adiffSeconds < 60) {
            adate = Math.floor(adiffSeconds) + " seconds ago";
          } else if (adiffMinutes < 60) {
            adate = Math.floor(adiffMinutes) + " minutes ago";
          } else if (adiffHours < 24) {
            adate = Math.floor(adiffHours) + " hours ago";
          } else if (adiffDays < 30) {
            adate = Math.floor(adiffDays) + " days ago";
          } else if (adiffMonths < 12) {
            adate = Math.floor(adiffMonths) + " months ago";
          } else {
            adate = Math.floor(adiffYears) + " years ago";
          }

          let isLiked = announcement.likeBy.includes(UUID);
          let isDisliked = announcement.dislikeBy.includes(UUID);

          function displayWhoReacts(reacts = "like") {
            if (reacts === "like") {
              let display_likeby = "";
              if (announcement.likeBy_Name.length > 0) {
                if (announcement.likeBy_Name.includes("You")) {
                  if (announcement.likeBy_Name.length === 1) {
                    display_likeby = "You";
                  } else {
                    let index = announcement.likeBy_Name.indexOf("You");
                    announcement.likeBy_Name.splice(index, 1);
                    display_likeby =
                      "You and " + announcement.likeBy_Name.length + " others";
                  }
                } else {
                  if (announcement.likeBy_Name.length === 1) {
                    display_likeby = announcement.likeBy_Name[0];
                  } else {
                    display_likeby =
                      announcement.likeBy_Name[0] +
                      " and " +
                      (announcement.likeBy.length - 1) +
                      " others";
                  }
                }
              }

              return display_likeby;
            } else {
              let display_dislikeby = "";

              if (announcement.dislikeBy_Name.length > 0) {
                if (announcement.dislikeBy_Name.includes("You")) {
                  if (announcement.dislikeBy_Name.length === 1) {
                    display_dislikeby = "You";
                  } else {
                    let index = announcement.dislikeBy_Name.indexOf("You");
                    announcement.dislikeBy_Name.splice(index, 1);
                    display_dislikeby =
                      "You and " + announcement.dislikeBy_Name.length + " others";
                  }
                } else {
                  if (announcement.dislikeBy_Name.length === 1) {
                    display_dislikeby = announcement.dislikeBy_Name[0];
                  } else {
                    display_dislikeby =
                      announcement.dislikeBy_Name[0] +
                      " and " +
                      (announcement.dislikeBy.length - 1) +
                      " others";
                  }
                }
              }
              return display_dislikeby;
            }
          }

          annCon.append(`<div class="col-12 ${lastPost}">
                            <div class="alert bg-body rounded-1 border shadow" role="alert">
                                <div class="hstack gap-0">
                                    <div>
                                        <div class="hstack gap-1 mb-1 user-select-none">
                                            <div class="p-2">
                                                <img src="${profpath}" alt="" width="42" height="42" class="shadow rounded-circle">
                                            </div>
                                            <div class="p-2">
                                                <p class="alert-heading text-truncate fw-bold moved"
                                                    style="max-width: 315px;">
                                                    ${announcement.postedBy}
                                                </p>
                                                <small class="moveu">${
                                                  announcement.postedDate
                                                }</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ms-auto me-1 p-1">
                                        <div class="priority">
                                            <svg width="24" height="24">
                                                <use
                                                    xlink:href="#${
                                                      announcement.priority
                                                    }"></use>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <p class="alert-heading" style="white-space: pre-wrap;">${
                                  announcement.postContent
                                }</p>
                                <hr>
                                <!-- Reactions -->
                                <div class="hstack gap-1 user-select-none">
                                    <div
                                        class="p-1 rounded-5 d-flex align-items-center fw-bold user-select-none reaction" id="like-${announcement.postID}">
                                        <small class="me-2" id="likeCount-${
                                          announcement.postID
                                        }">${postLikesFormatted}</small>
                                        <svg width="16" height="16">
                                            <use xlink:href="#Like" />
                                        </svg>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="p-1 rounded-5 d-flex align-items-center user-select-none reaction" id="dislike-${announcement.postID}">
                                        <small class="me-2" id="dislikeCount-${
                                          announcement.postID
                                        }">${postDislikesFormatted}</small>
                                        <svg width="16" height="16">
                                            <use xlink:href="#Dislike" />
                                        </svg>
                                    </div>
                                    <div class="ms-auto blockquote-footer my-1">
                                        <small><cite>${adate}</cite></small>
                                    </div>
                                </div>
                            </div>
                        </div>`);

          // Update the like and dislike buttons based on user reactions
          $(`#like-${announcement.postID}`).toggleClass(
            "text-primary",
            isLiked
          );
          $(`#dislike-${announcement.postID}`).toggleClass(
            "text-danger",
            isDisliked
          );

          // Reaction events for like and dislike
          $(`#like-${announcement.postID}`).on("click", function () {
            if (!isLiked) {
              if (!isDisliked) {
                // Like the post
                $(`#likeCount-${announcement.postID}`).text(
                  parseInt($(`#likeCount-${announcement.postID}`).text()) + 1
                );
                isLiked = true;
                $(`#like-${announcement.postID}`).addClass("text-primary");

                $.ajax({
                  url: "../Functions/api/postReaction.php",
                  type: "POST",
                  data: {
                    postID: announcement.postID,
                    UUID: UUID,
                    reaction: "like",
                  },

                  success: function (recRes) {
                    if (recRes.status === "error") {
                      QueueNotification("error", "Failed to like the post");
                    }
                  },
                });
              } else {
                // Remove dislike and like the post
                $(`#dislikeCount-${announcement.postID}`).text(
                  parseInt($(`#dislikeCount-${announcement.postID}`).text()) - 1
                );
                $(`#likeCount-${announcement.postID}`).text(
                  parseInt($(`#likeCount-${announcement.postID}`).text()) + 1
                );
                isLiked = true;
                isDisliked = false;
                $(`#dislike-${announcement.postID}`).removeClass("text-danger");
                $(`#like-${announcement.postID}`).addClass("text-primary");

                $.ajax({
                  url: "../Functions/api/postReaction.php",
                  type: "POST",
                  data: {
                    postID: announcement.postID,
                    UUID: UUID,
                    reaction: "dislike-like",
                  },

                  success: function (recRes) {
                    if (recRes.status === "error") {
                      QueueNotification(
                        "error",
                        "Failed to update the reaction"
                      );
                    }
                  },
                });
              }
            } else {
              // Unlike the post
              $(`#likeCount-${announcement.postID}`).text(
                parseInt($(`#likeCount-${announcement.postID}`).text()) - 1
              );
              isLiked = false;
              $(`#like-${announcement.postID}`).removeClass("text-primary");

              $.ajax({
                url: "../Functions/api/postReaction.php",
                type: "POST",
                data: {
                  postID: announcement.postID,
                  UUID: UUID,
                  reaction: "unlike",
                },

                success: function (recRes) {
                  if (recRes.status === "error") {
                    QueueNotification("error", "Failed to remove like");
                  }
                },
              });
            }
          });

          $(`#dislike-${announcement.postID}`).on("click", function () {
            if (!isDisliked) {
              if (!isLiked) {
                // Dislike the post
                $(`#dislikeCount-${announcement.postID}`).text(
                  parseInt($(`#dislikeCount-${announcement.postID}`).text()) + 1
                );
                isDisliked = true;
                $(`#dislike-${announcement.postID}`).addClass("text-danger");

                $.ajax({
                  url: "../Functions/api/postReaction.php",
                  type: "POST",
                  data: {
                    postID: announcement.postID,
                    UUID: UUID,
                    reaction: "dislike",
                  },

                  success: function (recRes) {
                    if (recRes.status === "error") {
                      QueueNotification("error", "Failed to dislike the post");
                    }
                  },
                });
              } else {
                // Remove like and dislike the post
                $(`#likeCount-${announcement.postID}`).text(
                  parseInt($(`#likeCount-${announcement.postID}`).text()) - 1
                );
                $(`#dislikeCount-${announcement.postID}`).text(
                  parseInt($(`#dislikeCount-${announcement.postID}`).text()) + 1
                );
                isLiked = false;
                isDisliked = true;
                $(`#like-${announcement.postID}`).removeClass("text-primary");
                $(`#dislike-${announcement.postID}`).addClass("text-danger");

                $.ajax({
                  url: "../Functions/api/postReaction.php",
                  type: "POST",
                  data: {
                    postID: announcement.postID,
                    UUID: UUID,
                    reaction: "like-dislike",
                  },

                  success: function (recRes) {
                    if (recRes.status === "error") {
                      QueueNotification(
                        "error",
                        "Failed to update the reaction"
                      );
                    }
                  },
                });
              }
            } else {
              // Undislike the post
              $(`#dislikeCount-${announcement.postID}`).text(
                parseInt($(`#dislikeCount-${announcement.postID}`).text()) - 1
              );
              isDisliked = false;
              $(`#dislike-${announcement.postID}`).removeClass("text-danger");

              $.ajax({
                url: "../Functions/api/postReaction.php",
                type: "POST",
                data: {
                  postID: announcement.postID,
                  UUID: UUID,
                  reaction: "undislike",
                },

                success: function (recRes) {
                  if (recRes.status === "error") {
                    QueueNotification("error", "Failed to remove dislike");
                  }
                },
              });
            }
          });
        });
      } else {
        annCon.empty();
        annCon.append(`<div class="col-12 anal">
                            <div class="alert bg-body rounded-1 border-0 bg-transparent d-flex justify-content-center" role="alert">
                                <div class="card-body text-center">
                                    <div class="hstack gap-3 d-flex justify-content-center">
                                        <div class="p-2 ms-5">
                                            <div class="no-ann-box">
                                                <img src="../../Assets/Images/Logo-Layers/layer 1.png" alt="Layer 1"
                                                    width="100" height="100" class="rounded-circle no-ann">
                                                <img src="../../Assets/Images/Logo-Layers/layer 2.png" alt="Layer 2"
                                                    width="100" height="100" class="rounded-circle no-ann">
                                                <img src="../../Assets/Images/Logo-Layers/layer 3.png" alt="Layer 3"
                                                    width="100" height="100" class="rounded-circle no-ann">
                                                <img src="../../Assets/Images/Logo-Layers/layer 4.png" alt="Layer 4"
                                                    width="100" height="100" class="rounded-circle no-ann">
                                            </div>
                                        </div>
                                        <div class="p-2 text-start ms-5">
                                            <h5 class="fw-bold mt-2">No Announcements Found</h5>
                                            <p class="text-secondary">Stay tuned for more updates</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`);
      }
    },
    error: function () {
      annCon.empty();
      annCon.append(`<div class="col-12 anal">
                            <div class="alert bg-body rounded-1 border-0 bg-transparent d-flex justify-content-center" role="alert">
                                <div class="card-body text-center">
                                    <div class="hstack gap-3 d-flex justify-content-center">
                                        <div class="p-2 ms-5">
                                            <div class="no-ann-box">
                                                <img src="../../Assets/Images/Logo-Layers/layer 1.png" alt="Layer 1"
                                                    width="100" height="100" class="rounded-circle no-ann">
                                                <img src="../../Assets/Images/Logo-Layers/layer 2.png" alt="Layer 2"
                                                    width="100" height="100" class="rounded-circle no-ann">
                                                <img src="../../Assets/Images/Logo-Layers/layer 3.png" alt="Layer 3"
                                                    width="100" height="100" class="rounded-circle no-ann">
                                                <img src="../../Assets/Images/Logo-Layers/layer 4.png" alt="Layer 4"
                                                    width="100" height="100" class="rounded-circle no-ann">
                                            </div>
                                        </div>
                                        <div class="p-2 text-start ms-5">
                                            <h5 class="fw-bold mt-2">No Announcements Found</h5>
                                            <p class="text-secondary">Stay tuned for more updates</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`);
    },
  });
}
