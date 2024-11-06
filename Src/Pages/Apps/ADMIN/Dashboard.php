<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    require_once '../../../Database/Config.php';
    require_once '../../../Debug/GenLog.php';
}

if (!isset($_SESSION['UUID'])) {
    header('Location: ../../Accesspage.php?error=001');
} else {
    $logPath = "../../../Debug/Users/UUID.log";

    // active users
    $stmt = $conn->prepare("SELECT COUNT(*) AS totalUsers FROM usercredentials WHERE accountStat = 'active'");
    $stmt->execute();
    $activeUsers = $stmt->get_result()->fetch_assoc()['totalUsers'];
    $stmt->close();

    // csg officers
    $stmt = $conn->prepare("SELECT COUNT(*) AS totalCSGOfficers FROM userpositions WHERE role = 2");
    $stmt->execute();
    $csgOfficers = $stmt->get_result()->fetch_assoc()['totalCSGOfficers'];
    $stmt->close();

    // daily logins
    $stmt = $conn->prepare("SELECT COUNT(*) AS totalLogins FROM accounts WHERE access_date >= CURDATE()");
    $stmt->execute();
    $dailyLogins = $stmt->get_result()->fetch_assoc()['totalLogins'];
    $stmt->close();

    // locked accounts
    $stmt = $conn->prepare("SELECT COUNT(*) AS totalLocked FROM usercredentials WHERE accountStat = 'locked'");
    $stmt->execute();
    $lockedAccounts = $stmt->get_result()->fetch_assoc()['totalLocked'];
    $stmt->close();
}

if ($_SESSION['role'] != 1) {
    header('Location: ../../../Pages/Feed.php');
}

$inactive = 1800; // 30 minutes inactivity
if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];

    if ($session_life > $inactive) {
        writeLog($logPath, "WARN", $_SESSION['UUID'], "Session Timeout", $_SERVER['REMOTE_ADDR'], "Session Timeout");
        header('Location: ../../../../Functions/api/UserLogout.php?error=002');
    }
}

$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $_SESSION['theme']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/NavbarStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script defer type="module" src="../../../../Utilities/Scripts/BS_DBScript.js"></script>
    <title>Dashboard</title>
</head>

<body>
    <?php include_once "../../../../Assets/Icons/Icon_Assets.php"; ?>
    <?php include_once "../../../Components/Navbar_AB.php";?>
    <?php $_SESSION['useBobbleBG'] == 1 ? include_once "../../../Components/BGanimation.php" : null;?>

    <div class="container-fluid d-flex flex-row p-0">
        <div class="BS-Side d-none d-lg-block border-end glass-10 bg-opacity-50">
            <div class="d-flex flex-column justify-content-between h-100">
                <div class="container text-center my-2">
                    <img src="../../../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']?>"
                        class="rounded-circle img-fluid border border-3 mt-2" alt="Profile Picture" width="84"
                        height="84">
                    <div class="vstack gap-0 mt-1">
                        <p class="lead fw-bold text-truncate mb-0">
                            <?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?>
                        </p>
                        <small class="text-secondary text-uppercase">
                            <?php
                            if ($_SESSION['role'] == 1) {
                                $role = 'Administrator';
                            } elseif ($_SESSION['role'] == 2) {
                                $role = "CSG Officer";
                            } else {
                                $role = 'Officer';
                            }echo $role;
?>
                        </small>
                    </div>
                </div>
                <div class="container">
                    <ul class="list-group">
                        <li class="list-group-item lg lg-active">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#Dashboard" />
                            </svg>
                            Dashboard
                        </li>
                        <span class="hr-divider-start text-secondary"></span>
                        <li class="list-group-item lg">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#TestIcon" />
                            </svg>
                            Placeholder
                        </li>
                        <span class="hr-divider-start text-secondary"></span>
                        <li class="list-group-item lg text-truncate">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#Acad" />
                            </svg>
                            Academic Programs
                        </li>
                        <li class="list-group-item lg">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#Organic" />
                            </svg>
                            Organizations
                        </li>
                        <li class="list-group-item lg" onclick="window.location.href = './User-Management.php'" title="User Management">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#ManageAct" />
                            </svg>
                            User Management
                        </li>
                        <span class="hr-divider-end text-secondary"></span>
                        <li class="list-group-item lg" onclick="window.location.href = '../../Feed.php'" title="Feed">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Feed" />
                            </svg>
                            News Feed
                        </li>
                        <li class="list-group-item lg" onclick="window.location.href = '../../Preference.php'" title="Messages">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Setting" />
                            </svg>
                            Settings
                        </li>
                        <li class="list-group-item lg text-danger" onclick="window.location.href = '../../../Functions/api/UserLogout.php'" title="Logout">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Logout" />
                            </svg>
                            Logout
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="BS-Main mt-2">
            <div class="container">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-2">
                    <div class="col" data-bs-toggle="tooltip" data-bs-placement="top" title="Total Users">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">Active Users</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0"><?php echo $activeUsers; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">CSG Officers</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0"><?php echo $csgOfficers; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">Daily Logins</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0"><?php echo $dailyLogins; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">Locked Accounts</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0"><?php echo $lockedAccounts; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>