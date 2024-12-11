<?php
// get current page name
$McurrentPage = basename($_SERVER['PHP_SELF']);

if ($McurrentPage == 'Feed.php') {
    $Mtitle = "News Feed";
} else if ($McurrentPage == 'Profile.php') {
    $Mtitle = "Profile";
} else if ($McurrentPage == 'Dashboard.php') {
    $Mtitle = "Dashboard";
} else if ($McurrentPage == 'Preference.php') {
    $Mtitle = "Preferences";
} else {
    $Mtitle = "Home";
}

$MFeed = "./Feed.php";
$MProfile = "./Profile.php";
$MPreference = "./Preference.php";

// Dashboards
$MDashboard = "";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 2) {
        if (isset($_SESSION['org_position']) && ($_SESSION['org_position'] == 1 || $_SESSION['org_position'] == 2 || $_SESSION['org_position'] == 3)) {
            $MDashboard = "#CSGDashboardforPresident";
        } else {
            $MDashboard = "#CSGDashboardforSecretary";
        }
    } else if ($_SESSION['role'] == 3) {
        if (isset($_SESSION['org_position']) && ($_SESSION['org_position'] == 1 || $_SESSION['org_position'] == 2 || $_SESSION['org_position'] == 3)) {
            $MDashboard = "#OFCDashboardforPresident";
        } else {
            $MDashboard = "#OFCDashboardforSecretary";
        }
    } else {
        $MDashboard = "./Apps/ADMIN/Dashboard.php";
    }
}

?>

<div class="b_navbar d-lg-none bg-body bg-blur-5 bg-opacity-25" id="b_Navbar">
        <div class="row h-100 item-parent">
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_Log" onclick="RedirectToLogin()">
                    <svg width="32" height="32">
                        <use xlink:href="#Login"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_Reg" onclick="RedirectToRegister()">
                    <svg width="32" height="32">
                        <use xlink:href="#Register"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_For" onclick="RedirectToForgot()">
                    <svg width="32" height="32">
                        <use xlink:href="#Lock"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_For">
                    <img src="../../Assets/Images/Default_CSG_LOGO.png" alt="csg logo" width="32">
                </a>
            </div>
            <div class="col-3 dropdown d-none">
                <a class="col-Items" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <svg width="32" height="32">
                        <use xlink:href="#Menu"></use> <!-- Menu Icon  / Dont change -->
                    </svg>
                </a>
                <div class="dropdown-menu me-3 mb-3 shadow dd-menu" style="cursor: pointer;">
                    <div class="dropdown-header">
                        <div class="hstack gap-1">
                            <img src="../../Assets/Images/Logo-Layers/Logo.png" class="rounded-circle" width="32" height="32">
                            <span class="ms-2 fs-4 fw-bold"><?php echo $Mtitle; ?></span>
                        </div>
                    </div>
                    <hr class="dropdown-divider">
                    <a class="dropdown-item" id="b_ItemNav_MSet">
                        <svg width="18" height="18" class="me-3 my-2">
                            <use xlink:href="#Messages"></use>
                        </svg>
                        Messages
                    </a>
                    <a class="dropdown-item <?php echo $McurrentPage == 'Preference.php' ? 'nav-active' : ''; ?>" <?php echo $McurrentPage != 'Preference.php' ? "onclick='window.location.href=\"$MPreference\"'" : ''; ?> 
                    id="b_ItemNav_Pref">
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
<script>
    $("#b_ItemNav_MSet").click(function() {
        $('html, body').animate({
            scrollTop: $("#UserBox").offset().top
        }, 500);
    });
</script>