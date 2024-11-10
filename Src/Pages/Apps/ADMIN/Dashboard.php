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
}

if ($_SESSION['role'] != 1) {
    header('Location: ../../../Pages/Feed.php');

}

$inactive = 1800; // 30 minutes inactivity
if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];

    if ($session_life > $inactive) {
        writeLog($logPath, "WARN", $_SESSION['UUID'], "Session Timeout", $_SERVER['REMOTE_ADDR'], "User Logged Out");
        header('Location: ../../../Functions/api/UserLogout.php?error=002');
    }
}

$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html lang="en"
    data-bs-theme="<?php echo $_SESSION['theme']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script defer type="module" src="../../../../Utilities/Scripts/BS_DBScript.js"></script>
    <title>Dashboard</title>
</head>
<?php include_once "../../../../Assets/Icons/Icon_Assets.php"; ?>
<?php $_SESSION['useBobbleBG'] == 1 ? include_once "../../../Components/BGanimation.php" : null;?>

<body>
    <div class="bg-dark bg-opacity-75 bg-blur z-3 position-fixed top-0 start-0 w-100 h-100 d-md-none">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <img src="../../../../Assets/Images/Loader-v1.gif" alt="Loading" width="100" height="100">
                    <br>
                    <h3 class="text-white mt-3">You can't access this page on mobile devices.</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid d-flex flex-row p-0 d-none d-lg-flex">
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
                        <small class="text-secondary text-uppercase fw-bold">
                            <?php
                            if ($_SESSION['role'] == 1) {
                                $role = 'Administrator';
                            } elseif ($_SESSION['role'] == 2) {
                                $role = "CSG Officer";
                            } else {
                                $role = 'Officer';
                            }echo $role;?>
                        </small>
                    </div>
                </div>
                <div class="container">
                    <ul class="list-group">
                        <li class="list-group-item lg my-2 lg-active">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#Dashboard" />
                            </svg>
                            Dashboard
                        </li>
                        <span class="hr-divider-start text-secondary d-none"></span>
                        <li class="list-group-item lg d-none">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#TestIcon" />
                            </svg>
                            Placeholder
                        </li>
                        <span class="hr-divider-start text-secondary d-none"></span>
                        <div class="accordion accordion-flush" id="Modules_Accord">
                            <div class="accordion-item">
                                <li class="list-group-item my-2 lg collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#flush-collapseOne" aria-expanded="false"
                                    aria-controls="flush-collapseOne">
                                    <svg class="me-3" width="24" height="24">
                                        <use xlink:href="#TestIcon" />
                                    </svg>
                                    Modules
                                </li>
                                <div id="flush-collapseOne" class="accordion-collapse collapse"
                                    data-bs-parent="#Modules_Accord">
                                    <div class="accordion-body">
                                        <li class="list-group-item lg my-2 text-truncate">
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#Acad" />
                                            </svg>
                                            Courses & Sections
                                        </li>
                                        <li class="list-group-item lg my-2">
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#Organic" />
                                            </svg>
                                            Organizations
                                        </li>
                                        <li class="list-group-item lg my-2"
                                            onclick="window.location.href = './User-Management.php'"
                                            title="User Management">
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#ManageAct" />
                                            </svg>
                                            User Management
                                        </li>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="hr-divider-end text-secondary"></span>
                        <li class="list-group-item lg my-2" onclick="window.location.href = '../../Feed.php'"
                            title="Feed">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Feed" />
                            </svg>
                            News Feed
                        </li>
                        <li class="list-group-item lg my-2" onclick="window.location.href = '../../Preference.php'"
                            title="Messages">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Setting" />
                            </svg>
                            Settings
                        </li>
                        <li class="list-group-item lg text-danger my-2"
                            onclick="window.location.href = '../../../Functions/api/UserLogout.php'" title="Logout">
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
                                    <p class="fs-1 fw-bold text-truncate mb-0" id="activeUsers">
                                        0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">CSG Officers</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0" id="csgOfficers">
                                        0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">Daily Logins</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0" id="dailyLogins">
                                        0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">Locked Accounts</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0" id="lockedAccounts">
                                        0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        $(document).ready(function() {
                            function updateData() {
                                $.ajax({
                                    url: '../../../Functions/api/updateDashboardData.php',
                                    type: 'GET',
                                    success: function(data) {
                                        if (data.status == 'success') {
                                            $('#activeUsers').text(data.activeUsers);
                                            $('#csgOfficers').text(data.csguser);
                                            $('#dailyLogins').text(data.dailyLogins);
                                            $('#lockedAccounts').text(data.lockedUsers);
                                        } else {
                                            $('#activeUsers').text('0');
                                            $('#csgOfficers').text('0');
                                            $('#dailyLogins').text('0');
                                            $('#lockedAccounts').text('0');
                                        }
                                    },

                                    error: function() {
                                        $('#activeUsers').text('0');
                                        $('#csgOfficers').text('0');
                                        $('#dailyLogins').text('0');
                                        $('#lockedAccounts').text('0');
                                    }
                                });
                            }
                            updateData();
                            setInterval(() => {
                                updateData()
                            }, 3000);
                        });
                    </script>
                </div>
                <div class="row row-cols-1 row-cols-md-2 g-2 mt-3">
                    <div class="col-md-7">
                        <div class="card card bg-body bg-opacity-25 bg-blur-5 rounded-1">
                            <div class="card-body">
                                <h5 class="card-title"></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card bg-body bg-opacity-25 bg-blur-5 rounded-1">
                            <div class="card-body">
                                <h4 class="card- text-uppercase text-bold text-center">User Access Log</h4>
                                <div class="overflow-auto" style="max-height: 65svh;">
                                    <div class="list-group list-group-flush user-select-none" id="logList">
                                        <div class="emptyfeed" id="EmptyFeed">
                                            <div class="card border-0">
                                                <div class="card-body text-center">
                                                    <img src="../../../../Assets/Images/Loader-v1.gif" alt="Loading"
                                                        width="50" height="50">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Puking ina mo -->
                                        <?php include_once "../../../Debug/Users/dispalyData.php"; ?>
                                    </div>
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