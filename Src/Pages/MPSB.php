<?php
// get current page name
$currentPage = basename($_SERVER['PHP_SELF']);

$Feed = "./Feed.php";
$Profile = "./Profile.php";
$Preference = "./Preference.php";
$logout = "../Functions/api/UserLogout.php";

// Dashboards
$Dashboard = "";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 2) {
        if (isset($_SESSION['org_position']) && ($_SESSION['org_position'] == 1 || $_SESSION['org_position'] == 2 || $_SESSION['org_position'] == 3)) {
            $Dashboard = "./Apps/User_Modules/Dashboard.php";
        } else {
            $Dashboard = "./Apps/User_Modules/Dashboard.php";
        }
    } else if ($_SESSION['role'] == 3) {
        if (isset($_SESSION['org_position']) && ($_SESSION['org_position'] == 1 || $_SESSION['org_position'] == 2 || $_SESSION['org_position'] == 3)) {
            $Dashboard = "./Apps/User_Modules/Dashboard.php";
        } else {
            $Dashboard = "./Apps/User_Modules/Dashboard.php";
        }
    } else {
        $Dashboard = "./Apps/ADMIN/Dashboard.php";
    }
}
?>

<div class="sidecon d-flex align-items-center">
    <ul class="list-group list-group-flush feedsidebar user-select-none">
        <li class="list-group-item border-0 rounded-pill feedsidebaritems <?php echo $currentPage == 'Feed.php' ? 'feed-slected' : ''; ?>"
            <?php echo $currentPage != 'Feed.php' ? "onclick='window.location.href=\"$Feed\"'" : ''; ?>
            id="Feed-btn">
            <svg width="24" height="24">
                <use xlink:href="#Feed"></use>
            </svg>
            <span class="ms-2">Feed</span>
        </li>
        <li class="list-group-item border-0 rounded-pill feedsidebaritems <?php echo $currentPage == 'Profile.php' ? 'feed-slected' : ''; ?>"
            <?php echo $currentPage != 'Profile.php' ? "onclick='window.location.href=\"$Profile\"'" : ''; ?>
            id="Profile-btn">
            <svg width="24" height="24">
                <use xlink:href="#Profile"></use>
            </svg>
            <span class="ms-2">Profile</span>
        </li>
        <li class="list-group-item border-0 rounded-pill feedsidebaritems <?php echo $currentPage == 'Dashboard.php' ? 'feed-slected' : ''; ?>"
            id="Dashboard-btn" <?php echo $currentPage != 'Dashboard.php' ? "onclick='window.location.href=\"$Dashboard\"'" : ''; ?>>
            <svg width="24" height="24">
                <use xlink:href="#Dashboard"></use>
            </svg>
            <span class="ms-2">Dashboard</span>
        </li>
        <li class="list-group-item border-0 rounded-pill feedsidebaritems <?php echo $currentPage == 'Preference.php' ? 'feed-slected' : ''; ?>"
            id="Preferences-btn" <?php echo $currentPage != 'Preference.php' ? "onclick='window.location.href=\"$Preference\"'" : ''; ?>>
            <svg width="24" height="24">
                <use xlink:href="#Settings"></use>
            </svg>
            <span class="ms-2">Preferences</span>
        </li>
        <li class="list-group-item border-0 rounded-pill feedsidebaritems" id="Logout-Button"
            data-LogoutLink="<?php echo $logout; ?>">
            <svg width="24" height="24">
                <use xlink:href="#Logout"></use>
            </svg>
            <span class="ms-2">Logout</span>
        </li>
    </ul>
</div>