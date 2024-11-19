<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    require_once '../../../Database/Config.php';
    require_once '../../../Debug/GenLog.php';
    date_default_timezone_set('Asia/Manila');
}

if (!isset($_SESSION['UUID'])) {
    header('Location: ../../Accesspage.php?error=001');
} else {
    $logPath = "../../../Debug/Users/UUID.log";
    echo '<script>var UUID = "' . $_SESSION['UUID'] . '";</script>';
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
    <!-- Bootstrap Hyper Theme CSS Start -->
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <!-- Bootstrap Hyper Theme CSS End -->
    <!-- Custom CSS Start -->
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Datatable/css/datatables.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">
    <!-- Custom CSS End -->

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>

    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer type="module" src="../../../../Utilities/Scripts/RS_DBScript.js"></script>
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
                    <h3 class="text-white mt-3">You can't access this page on this viewport</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid d-flex flex-row p-0 d-none d-lg-flex">
        <div class="modal" id="AuditDetails" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content bg-transparent border-0 rounded-1">
                    <div class="modal-body glass-default bg-opacity-10">
                        <div class="hstack">
                            <h4 class="modal-title text-center text-light">Audit Details</h4>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" id="cococlose"
                                aria-label="Close"></button>
                        </div>
                        <div class="my-3">
                            <table class="table table-sm table-hover table-striped table-responsive table-borderless">
                                <thead class="table-dark">
                                    <tr class="rounded rounded-top rounded-3">
                                        <th scope="col" class="text-nowrap">#</th>
                                        <th scope="col" class="text-nowrap">Field</th>
                                        <th scope="col" class="text-nowrap">Old Value</th>
                                        <th scope="col" class="text-nowrap">New Value</th>
                                    </tr>
                                </thead>
                                <tbody id="audit_details_body" class="table-group-divider">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="BS-Side d-none d-lg-block border-end glass-10 bg-opacity-50">
            <div class="d-flex flex-column justify-content-between h-100">
                <div class="container text-center my-2">
                    <?php
                    if ($_SESSION['ProfileImage'] == "Default-Profile.gif") {?>
                    <img src="../../../../Assets/Images/Default-Profile.gif"
                        class="rounded-circle img-fluid border border-3 mt-2" alt="Profile Picture" width="84"
                        height="84">
                    <?php } else {?>
                    <img src="../../../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']?>"
                        class="rounded-circle img-fluid border border-3 mt-2" alt="Profile Picture" width="84"
                        height="84">
                    <?php }?>
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
                        <li class="list-group-item lg my-2" onclick="window.location.href = './Dashboard.php'">
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
                            <div class="accordion-item bg-transparent border-0">
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
                                        <li class="list-group-item lg my-2 text-truncate"
                                            onclick="window.location.href = './CourseSection.php'">
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#Acad" />
                                            </svg>
                                            Courses & Sections
                                        </li>
                                        <li class="list-group-item lg my-2"
                                            onclick="window.location.href = './Organizations.php'">
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
                        <li class="list-group-item lg my-2 lg-active" title="Messages">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Report" />
                            </svg>
                            Report
                        </li>
                        <li class="list-group-item lg text-danger my-2 mb-4"
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
                <div class="hstack gap-3">
                    <h3 class="fw-bold text-uppercase">System Report</h3>
                    <div class="ms-auto">
                        <button class="btn btn-sm btn-outline-success rounded-0" id="ExportReport">Export All</button>
                        <button class="btn btn-sm btn-outline-secondary rounded-0" id="RefreshReport">
                            <svg class="me-2" width="18" height="18">
                                <use xlink:href="#Refresh" />
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="container-fluid">

                </div>
            </div>
        </div>
    </div>
</body>

</html>