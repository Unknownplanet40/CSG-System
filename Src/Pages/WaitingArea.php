<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    require_once '../Database/Config.php';
    require_once '../Debug/GenLog.php';
}

if (!isset($_SESSION['UUID'])) {
    header('Location: ../Pages/Accesspage.php?error=001');
} else {
    $logPath = "../Debug/Users/UUID.log";
}

if (isset($_SESSION['accountStat'])){
    if ($_SESSION['accountStat'] === 'active') {
        header('Location: ./Feed.php');
    } else if ($_SESSION['accountStat'] === 'rejected') {
        header('Location: ../Functions/api/UserLogout.php');
    }
}


$inactive = 1800; // 30 minutes inactivity
if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];

    if ($session_life > $inactive) {
        writeLog($logPath, "WARN", $_SESSION['UUID'], "Session Timeout", $_SERVER['REMOTE_ADDR'], "User Logged Out");
        header('Location: ../Functions/api/UserLogout.php?error=002');
    }
}

$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../Utilities/Third-party/Sweetalert2/css/sweetalert2.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/MNavbarStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/Accessstyle.css">
    <link rel="shortcut icon" href="../../Assets/Icons/PWA-Icon/MainIcon.png" type="image/x-icon">
    <script src="../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../Utilities/Scripts/animate-browser-title.js"></script>
    <script defer type="module" src="../../Utilities/Scripts/waitingpage.js"></script>
    <title>For Approval</title>
</head>

<?php include_once '../../Assets/Icons/Icon_Assets.php'; ?>

<body>
    <div class="loader-container" id="loader">
        <div class="row g-4">
            <!-- version 1 -->
            <div class="col-md-12 d-flex justify-content-center">
                <span class="loader-v1-default"></span>
            </div>
            <!-- version 2 -->
            <div class="col-md-12 d-flex justify-content-center d-none">
                <span class="loader-v2"></span>
            </div>
            <!-- version 3 -->
            <div class="col-md-12 d-flex justify-content-center d-none">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <!-- Loading Label -->
            <div class="col-md-12 d-flex justify-content-center d-none" id="noForm">
                <h5 class="text-muted text-center" id="noForm-label">No form selected</h5>
            </div>
        </div>
    </div>
    <div class="container-fluid con-H d-none" id="main-content">
        <div class="container mt-5 pt-5">
            <div class="row g-4">
                <div class="col">
                    <div class="card mb-3 glass-default bg-opacity-5 border-0">
                        <div class="row g-0">
                            <div class="col-md-4 mt-5">
                                <div class="d-flex justify-content-center align-items-center d-flex flex-column">
                                    <img src="../../Assets/Images/Logo-Layers/Logo.png" class="img-fluid rounded-start"
                                        width="230" height="230" alt="...">
                                    <div class="text-center my-4">
                                        <h3> Approval Status </h3>
                                        <div class="alert alert-primary fade show" role="alert" style="width: 260px;" id="alert">
                                            <img src="" alt="Typing SVG" id="typing">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h4 class="card-title">Great! Your form has been successfully submitted.</h4>
                                    <p class="card-text" style="text-align: justify;">Just one more step—please wait
                                        while the admin reviews and approves your submission. Once your account is
                                        approved, you will be able to access the system with your assigned organization
                                        and role. Thank you for your patience!</p>
                                    <p class="card-text"><small class="text-muted">Note: We will notify you via email
                                            once your account has been approved.</small></p>
                                    <hr>
                                    <h5 class="card-title">Form Details</h5>
                                    <ul class="list-group list-group-flush border-0">
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            Full Name
                                            <input type="text" class="form-control bg-transparent border-0 w-50 fw-bold"
                                                value="<?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?>" disabled>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            Primary Email
                                            <input type="text" class="form-control bg-transparent border-0 w-50 fw-bold"
                                                value="<?php echo $_SESSION['PrimaryEmail']; ?>" disabled>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            Contact Number
                                            <input type="text" class="form-control bg-transparent border-0 w-50 fw-bold"
                                                value="<?php echo $_SESSION['contactNumber']; ?>" disabled>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            Student Number
                                            <input type="text" class="form-control bg-transparent border-0 w-50 fw-bold"
                                                value="<?php echo $_SESSION['student_Number']; ?>" disabled>
                                        </li>
                                        <li
                                            class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            Course, Year, and Section
                                            <input type="text" class="form-control bg-transparent border-0 w-50 fw-bold"
                                                value="<?php echo $_SESSION['course_code']; ?>" disabled>
                                        </li>
                                    </ul>
                                    <div class="hstack gap-2 mt-4">
                                        <button class="btn btn-sm btn-primary me-3" id="btn-logout">Logout</button>
                                        <button class="btn btn-sm btn-success" id="btn-refreshing">Refresh</button>
                                        <button class="btn btn-sm btn-danger ms-auto" id="btn-cancel">Cancel</button>
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