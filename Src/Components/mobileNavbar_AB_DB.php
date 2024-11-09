<?php
$home = $_SERVER['REQUEST_URI'];
$link1 = "../../Apps/ADMIN/Dashboard.php";
$link2 = "../../Feed.php";
$link3 = "../../Functions/api/UserLogout.phps";
$link4 = "../../Preference.php";
$link5 = "../../Apps/ADMIN/User-Management.php";

// git file name from the path
$currentFile = $_SERVER["SCRIPT_NAME"];

$seleceDashboard = "";
$selectUserManagement = "";

if (strpos($currentFile, "Dashboard.php") !== false) {
    $link1 = "#";
    $seleceDashboard = "selected";
    $selectUserManagement = "";
} elseif (strpos($currentFile, "User-Management.php") !== false) {
    $link3 = "#";
    $seleceDashboard = "";
    $selectUserManagement = "selected";
}
?>
<div class="b_navbar d-lg-none bg-body bg-blur-5 bg-opacity-25" id="b_Navbar">
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
                        <img src="../../Assets/Icons/PWA-Icon/MainIcon.png" class="rounded-circle" width="32"
                            height="32">
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
                <a class="dropdown-item" id="b_ItemNav_Pref" onclick="window.location.href='../Pages/Preference.php'">
                    <svg width="18" height="18" class="me-3 my-2">
                        <use xlink:href="#Settings"></use>
                    </svg>
                    Preferences
                </a>
                <a class="dropdown-item" id="Logout-NavButton"
                    data-LogoutLink="<?php echo $logout; ?>">
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