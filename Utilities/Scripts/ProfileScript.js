import { QueueNotification } from "./Modules/Queueing_Notification.js";

function setPriority(priority) {
  $("#input-priority").val(priority);
  if (priority == 1) {
    $("#in-T").addClass("d-none");
    $("#in-P-Low").removeClass("d-none");
    $("#in-P-Norm").addClass("d-none");
    $("#in-P-High").addClass("d-none");
  } else if (priority == 2) {
    $("#in-T").addClass("d-none");
    $("#in-P-Low").addClass("d-none");
    $("#in-P-Norm").removeClass("d-none");
    $("#in-P-High").addClass("d-none");
  } else if (priority == 3) {
    $("#in-T").addClass("d-none");
    $("#in-P-Low").addClass("d-none");
    $("#in-P-Norm").addClass("d-none");
    $("#in-P-High").removeClass("d-none");
  } else {
    $("#in-T").removeClass("d-none");
    $("#in-P-Low").addClass("d-none");
    $("#in-P-Norm").addClass("d-none");
    $("#in-P-High").addClass("d-none");
  }
}

function createPost() {
  var postDetails = $("#post-details").val();
  var priority = $("#input-priority").val();
  var userUUID = $("#USER_UUID").val();

  if (postDetails == "") {
    QueueNotification(["info", "Post details cannot be empty", 3000]);
    $("#post-details").focus();
    return;
  }

  if (postDetails.length < 50) {
    QueueNotification([
      "info",
      "Post details must be at least 50 characters",
      3000,
    ]);
    $("#post-details").focus();
    return;
  }

  if (priority == 0) {
    QueueNotification(["info", "Priority must be set", 3000]);
    $("#priority").focus();
    return;
  }

  $.ajax({
    url: "../../Src/Functions/api/postAnnouncement.php",
    type: "POST",
    data: {
      postDetails: postDetails,
      priority: priority,
      userUUID: userUUID,
    },
    success: function (response) {
      if (response.status == "success") {
        QueueNotification(["success", "Post created successfully", 5000]);
        $("#closePostModal").click();
        getuserPosts(userUUID);
      } else {
        QueueNotification(["info", "Post creation failed", 3000]);
      }
    },
    error: function (xhr, status, error) {
      QueueNotification(["error", "Something went wrong", 3000]);
    },
  });
}

function getuserPosts(UUID) {
  let annCon = $("#Announcements");
  $.ajax({
    url: "../Functions/api/getAnnouncements.php",
    type: "GET",
    data: { UUID: UUID },
    success: function (data) {
      if (data.status === "success") {
        annCon.empty();
        data.data.forEach((announcement) => {
          // Format the likes and dislikes
          let postLikes = announcement.postLikes;
          let postDislikes = announcement.postDislikes;
          let largeNum = ["K", "M", "B", "T"];

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
                      "You and " +
                      announcement.dislikeBy_Name.length +
                      " others";
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

          annCon.append(`<div class="col-12 anal" data-aos="fade-up" data-aos-anchor="#Announcements" data-aos-offset="150"
                            data-aos-duration="500">
                            <div class="alert bg-body rounded-1 border shadow" role="alert">
                                <div class="hstack gap-0">
                                    <div>
                                        <div class="hstack gap-1 mb-1 user-select-none">
                                            <div class="p-2">
                                                <img src="../../Assets/Images/UserProfiles/${announcement.profileImage}" alt="" width="42" height="42" class="shadow rounded-circle">
                                            </div>
                                            <div class="p-2">
                                                <p class="alert-heading text-truncate fw-bold moved"
                                                    style="max-width: 315px;">
                                                    ${announcement.postedBy}
                                                </p>
                                                <small class="moveu">${announcement.postedDate}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ms-auto me-1 p-1">
                                        <div class="priority">
                                            <svg width="24" height="24">
                                                <use
                                                    xlink:href="#${announcement.priority}"></use>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <p class="alert-heading" style="white-space: pre-wrap;">${announcement.postContent}</p>
                                <!-- Reactions -->
                                <div class="hstack gap-1 user-select-none d-none">
                                    <div
                                        class="p-1 rounded-5 d-flex align-items-center fw-bold user-select-none reaction" id="like-${announcement.postID}">
                                        <small class="me-2" id="likeCount-${announcement.postID}">${postLikesFormatted}</small>
                                        <svg width="16" height="16">
                                            <use xlink:href="#Like" />
                                        </svg>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="p-1 rounded-5 d-flex align-items-center user-select-none reaction" id="dislike-${announcement.postID}">
                                        <small class="me-2" id="dislikeCount-${announcement.postID}">${postDislikesFormatted}</small>
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
        annCon.append(`<div class="col-12 anal" data-aos="fade-up" data-aos-anchor="#Announcements" data-aos-offset="150"
                            data-aos-duration="500">
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
      annCon.append(`<div class="col-12 anal" data-aos="fade-up" data-aos-anchor="#Announcements" data-aos-offset="150"
                            data-aos-duration="500">
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

$(document).ready(function () {
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

  var maxRows = 10;
  $("#post-details").on("input", function () {
    this.style.height = "auto";
    this.style.height = this.scrollHeight + "px";
    const lines = this.value.split("\n");
    $("#charCount").text(this.value.length + "/500");
    if (this.value.length >= 500) {
      $("#post-details").addClass("is-invalid shake");
      setTimeout(() => {
        $("#post-details").removeClass("is-invalid shake");
      }, 2000);
    }

    if (lines.length > maxRows) {
      this.value = this.value.split("\n").slice(0, maxRows).join("\n");
      this.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
          e.preventDefault();
        }
      });
    }
  });

  $("#LP").click(function () {
    setPriority(1);
  });

  $("#NP").click(function () {
    setPriority(2);
  });

  $("#HP").click(function () {
    setPriority(3);
  });

  $("#SubmitPost").click(function () {
    createPost();
  });

  getuserPosts($("#USER_UUID").val());
});
