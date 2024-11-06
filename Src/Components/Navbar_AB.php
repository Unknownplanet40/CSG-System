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

<nav>
    <ul class="sidebars bg-body bg-opacity-25 bg-blur-10 d-lg-none" style="cursor: pointer;">
        <li onclick="HideSidebars()" class="" style="margin-top: 16px; padding-right: 8px;">
            <a>
                <svg width="26" height="26">
                    <use xlink:href="#Close" />
                </svg>
            </a>
        </li>
        <hr style="width: 90%; margin: 10px auto; border: 1px solid var(--bs-body-color);" />
        <li class="list-hover <?php echo $seleceDashboard; ?> border-0 RHBS" onclick="window.location.href = '<?php echo $link1; ?>'" title="Dashboard">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#Dashboard" />
            </svg>
            Dashboard
        </li>
        <!-- devider -->
        <hr style="width: 90%; margin: 10px auto; border: 1px solid var(--bs-body-color);" />
        <li class="list-hover border-0 RHBS">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#TestIcon" />
            </svg>
            Placeholder
        </li>
        <hr style="width: 90%; margin: 10px auto; border: 1px solid var(--bs-body-color);" />
        <li class="list-hover border-0 RHBS">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#Acad" />
            </svg>
            Academic Programs
        </li>
        <li class="list-hover border-0 RHBS">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#Organic" />
            </svg>
            Organizations
        </li>
        <li class="list-hover <?php echo $selectUserManagement; ?> border-0 RHBS" onclick="window.location.href = '<?php echo $link5; ?>'" title="User Management">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#ManageAct">
            </svg>
            User Managements
        </li>
        <hr style="width: 90%; margin: 10px auto; border: 1px solid var(--bs-body-color);" />
        <li class="list-hover border-0 RHBS" onclick="window.location.href = '<?php echo $link2; ?>'" title="Feed">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#Feed" />
            </svg>
            News Feed
        </li>
        <li class="list-hover border-0 RHBS" onclick="window.location.href = '<?php echo $link4; ?>'" title="Settings">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#Setting" />
            </svg>
            Settings
        </li>
        <li class="list-hover border-0 RHBS" onclick="window.location.href = '<?php echo $link3; ?>'" title="Logout">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#Logout" />
            </svg>
            Logout
        </li>
    </ul>
    <ul class="mt-3 bg-body bg-opacity-25 bg-blur-10 rounded shadow d-lg-none">
        <li class="LogoMain">
            <a href="<?php echo $home; ?>">
                <span></span>
                <!-- <svg width="32" height="32">
                        <use xlink:href="#TestIcon" />
                    </svg> -->
            </a>
        </li>
        <li class="hideLabel d-none">
            <a href="<?php echo $link1; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Place 1
            </a>
        </li>
        <li class="d-lg-none" onclick="ShowSidebars()">
            <a style="cursor: pointer;">
                <svg width="26" height="26">
                    <use xlink:href="#Menu" />
                </svg>
            </a>
        </li>
        <script>
            function ShowSidebars() {
                $(".sidebars").css("display", "flex");
            }

            function HideSidebars() {
                $(".sidebars").css("display", "none");
            }
        </script>
    </ul>
</nav>