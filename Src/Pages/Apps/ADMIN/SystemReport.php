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

if ($_SESSION['role'] != 1 && !($_SESSION['role'] == 2 && ($_SESSION['org_position'] == 1 || $_SESSION['org_position'] == 2 || $_SESSION['org_position'] == 3))) {
    header('Location: ../../../Pages/Feed.php');
    exit();
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
    <title>System Report</title>
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
                    <?php include_once "./DSB.php"; ?>
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
                        <button class="d-none btn btn-sm btn-outline-secondary rounded-0" onclick="window.location.href='./SystemReport.php#SystemReport-1'">
                            System Logs
                        </button>
                        <button class="d-none btn btn-sm btn-outline-secondary rounded-0" onclick="window.location.href='./SystemReport.php#SystemReport-2'">
                            Account Logs
                        </button>
                        <button class="d-none btn btn-sm btn-outline-secondary rounded-0" onclick="window.location.href='./SystemReport.php#SystemReport-3'">
                            Device Logs
                        </button>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="card glass-default bg-opacity-10 border-0">
                                <div class="card-body">
                                    <h5 class="card-title" id="SystemReport-1">System Logs</h5>
                                    <table class="table table-hover table-striped table-responsive" id="SystemAudit">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-nowrap">ID</th>
                                                <th scope="col" class="text-nowrap">Name</th>
                                                <th scope="col" class="text-nowrap">Event</th>
                                                <th scope="col" class="text-nowrap">IP</th>
                                                <th scope="col" class="text-nowrap">Details</th>
                                                <th scope="col" class="text-nowrap">Status</th>
                                                <th scope="col" class="text-nowrap">Date</th>
                                                <th scope="col" class="text-nowrap">Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card glass-default bg-opacity-10 border-0">
                                <div class="card-body">
                                    <h5 class="card-title" id="SystemReport-2">Account Status Logs</h5>
                                    <table class="table table-hover table-striped table-responsive" id="AccountAudit">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-nowrap">ID</th>
                                                <th scope="col" class="text-nowrap">Name</th>
                                                <th scope="col" class="text-nowrap">Student Number</th>
                                                <th scope="col" class="text-nowrap">Password</th>
                                                <th scope="col" class="text-nowrap">Login Status</th>
                                                <th scope="col" class="text-nowrap">IP Address</th>
                                                <th scope="col" class="text-nowrap">Status</th>
                                                <th scope="col" class="text-nowrap">Last Access</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card glass-default bg-opacity-10 border-0">
                                <div class="card-body">
                                    <h5 class="card-title" id="SystemReport-3">Device Status Logs</h5>
                                    <table class="table table-hover table-striped table-responsive" id="DeviceAudit">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-nowrap">ID</th>
                                                <th scope="col" class="text-nowrap">Name</th>
                                                <th scope="col" class="text-nowrap">Device</th>
                                                <th scope="col" class="text-nowrap">Device Info</th>
                                                <th scope="col" class="text-nowrap">IP Address</th>
                                                <th scope="col" class="text-nowrap">Details</th>
                                                <th scope="col" class="text-nowrap">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
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