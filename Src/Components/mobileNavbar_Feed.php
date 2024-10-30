
<div class="b_navbar d-lg-none bg-body bg-blur-5 bg-opacity-25" id="b_Navbar">
<div class="position-relative d-lg-none">
    <div class="position-fixed bottom-0 end-0 p-3 mb-5" style="z-index: 11;">
        <div type="button" class="btn btn-primary bg-transparent border-0 rounded-circle p-3 chat-aniSide" id="chatButton">
            <svg width="32" height="32" class="text-body" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" data-bs-trigger="hover" data-bs-title="Messages"
            style="cursor: pointer; outline: none;">
                <use xlink:href="#Messages"></use>
            </svg>
        </div>
    </div>
</div>

<script>
    $("#chatButton").click(function() {
        $('html, body').animate({
            scrollTop: $("#UserBox").offset().top
        }, 500);
    });

    setInterval(() => {
        if ($(window).scrollTop() > 100) {
            $("#chatButton").addClass("d-none");
        } else {
            $("#chatButton").removeClass("d-none");
        }
    }, 100);
</script>
        <div class="row h-100 item-parent">
            <div class="col-3">
                <a class="col-Items nav-active" id="b_ItemNav_Log">
                    <svg width="32" height="32">
                        <use xlink:href="#Feed"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_Reg" onclick="window.location.href='../Pages/Profile.php'">
                    <svg width="32" height="32">
                        <use xlink:href="#Profile"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_For">
                    <svg width="32" height="32">
                        <use xlink:href="#Dashboard"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3 dropdown">
                <a class="col-Items" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <svg width="32" height="32">
                        <use xlink:href="#Menu"></use> <!-- Menu Icon  / Dont change -->
                    </svg>
                </a>
                <div class="dropdown-menu me-3 mb-3 shadow dd-menu" style="cursor: pointer;">
                    <div class="dropdown-header">
                        <div class="hstack gap-1">
                            <img src="https://i.imgur.com/Xd06F5f.jpeg" class="rounded-circle" width="32" height="32">
                            <span class="ms-2 fs-4 fw-bold">CSG</span>
                        </div>
                    </div>
                    <hr class="dropdown-divider">
                    <a class="dropdown-item" id="b_ItemNav_PSet">
                        <svg width="18" height="18" class="me-3 my-2">
                            <use xlink:href="#TestIcon"></use>
                        </svg>
                        Placeholder
                    </a>
                    <a class="dropdown-item" id="b_ItemNav_Pref">
                        <svg width="18" height="18" class="me-3 my-2">
                            <use xlink:href="#Settings"></use>
                        </svg>
                        Preferences
                    </a>
                    <a class="dropdown-item" id="Logout-NavButton" data-LogoutLink="<?php echo $logout; ?>">
                        <svg width="18" height="18" class="me-3 my-2">
                            <use xlink:href="#Logout"></use>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>