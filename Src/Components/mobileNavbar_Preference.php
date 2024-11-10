
<div class="b_navbar d-lg-none bg-body bg-blur-5 bg-opacity-25" id="b_Navbar">
        <div class="row h-100 item-parent">
            <div class="col-3">
                <a class="col-Items nav-active" id="b_ItemNav_Log" onclick="window.location.href='../Pages/Feed.php'">
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
                <a class="col-Items" id="b_ItemNav_For" <?php echo $_SESSION['role'] != 1 ? '' : 'onclick="window.location.href=\'../Pages/Apps/ADMIN/Dashboard.php\'"' ?>>
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
                    <a class="dropdown-item d-none" id="b_ItemNav_MSet">
                        <svg width="18" height="18" class="me-3 my-2">
                            <use xlink:href="#Messages"></use>
                        </svg>
                        Messages
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