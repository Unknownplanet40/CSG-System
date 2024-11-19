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


    $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
    $stmt->bind_param('s', $_SESSION['UUID']);
    $stmt->execute();
    $pos = $stmt->get_result();
    $stmt->close();

    // get user role
    if ($pos->num_rows > 0) {
        while ($row = $pos->fetch_assoc()) {
            $_SESSION['role'] = $row['role'];
            $_SESSION['isSubOrg'] = $row['isSubOrg'];
        }
    } else {
        $_SESSION['role'] = 0;
        $_SESSION['isSubOrg'] = 0;
    }
}

if (isset($_SESSION['accountStat'])) {
    if ($_SESSION['accountStat'] === 'active') {
        header('Location: ./Feed.php');
    } elseif ($_SESSION['accountStat'] === 'rejected') {
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
    <title>Complete User Information</title>
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
        <div class="container mt-3">
            <div class="row g-4">
                <div class="col">
                    <div class="card mb-3 glass-default bg-opacity-5 border-0">
                        <div class="row g-0">
                            <div class="col-md-4 d-flex justify-content-center align-items-center">
                                <div class="d-flex justify-content-center align-items-center d-flex flex-column">
                                    <img src="../../Assets/Images/Logo-Layers/Logo.png" class="img-fluid rounded-start"
                                        width="230" height="230" alt="...">
                                    <div class="text-center my-4">
                                        <h3>Account Status</h3>
                                        <div class="alert alert-primary fade show" role="alert" style="width: 260px;"
                                            id="alert">
                                            <img src="" alt="Typing SVG" id="typing">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h4 class="card-title">Almost There! Just One More Step</h4>
                                    <p class="card-text" style="text-align: justify;">
                                        Before proceeding to the main page, please complete the form below to verify
                                        your identity and confirm your status as a registered student of the university.
                                        Once you've filled out the form, click the submit button to continue.
                                    </p>
                                    <hr>
                                    <h5 class="card-title">Form Details</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="perFname" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="perFname" required autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="perLname" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="perLname" required autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="perEmail" class="form-label">Cvsu mail</label>
                                            <input type="email" class="form-control" id="perEmail" required placeholder="Example@cvsu.edu.ph">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="perpassword" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="perpassword" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="perCourse" class="form-label">Course</label>
                                            <select class="form-select" id="perCourse" required>
                                                <option selected disabled hidden value="">Choose...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="perYear" class="form-label">Year Level</label>
                                            <select class="form-select" id="perYear" required>
                                                <option selected disabled hidden value="">Choose...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="perSection" class="form-label">Section</label>
                                            <select class="form-select" id="perSection" required>
                                                <option selected disabled hidden value="">Choose...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" id="CourseCode" value="">
                                        <div class="col-md-4">
                                            <label for="perStudentno" class="form-label">Student Number</label>
                                            <input type="text" class="form-control" id="perStudentno" required maxlength="9">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="perContact" class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" id="perContact" required maxlength="11" placeholder="09xxxxxxxxx">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="perBirthdate" class="form-label">Birthdate</label>
                                            <input type="date" class="form-control" id="perBirthdate" required>
                                        </div>
                                        <input type="hidden" id="perAge" value="">
                                        <div
                                            class="col-md-6 <?php $_SESSION['role'] > 1 ? '' : 'd-none'; ?>">
                                            <label for="perOrg"
                                                class="form-label"><?php echo $_SESSION['isSubOrg'] === 1 ? 'Sub-Organization' : 'Organization'; ?></label>
                                            <select class="form-select" id="perOrg" required>
                                                <?php
$stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 0");
$stmt->execute();
$orgs = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
$stmt->bind_param('s', $_SESSION['UUID']);
$stmt->execute();
$pos = $stmt->get_result();
$stmt->close();
$org_code = "";
if ($pos->num_rows > 0) {
    while ($row = $pos->fetch_assoc()) {
        $org_code = $row['org_code'];
        $isSubOrg = $row['isSubOrg'];
    }
}

if ($orgs->num_rows > 0) {
    echo "<option selected disabled hidden value=''>Choose...</option>";
    while ($row = $orgs->fetch_assoc()) {
        if ($isSubOrg === 1) {
            if ($row['org_code'] === 10001) {
                continue;
            } else {
                echo "<option value='" . $row['org_code'] . "'>" . $row['org_name'] . "</option>";
            }
        } else {
            if (!empty($org_code)) {
                if ($row['org_code'] === $org_code) {
                    echo "<option value='" . $row['org_code'] . "' selected>" . $row['org_name'] . "</option>";
                } else {
                    echo "<option value='" . $row['org_code'] . "' disabled>" . $row['org_name'] . "</option>";
                }
            } else {
                echo "<option value='" . $row['org_code'] . "'>" . $row['org_name'] . "</option>";
            }
        }
    }
}?>
                                            </select>
                                        </div>
                                        <div
                                            class="col-md-6 <?php $_SESSION['role'] > 1 ? '' : 'd-none'; ?>">
                                            <label for="perPosition" class="form-label">Position</label>
                                            <select class="form-select" id="perPosition" required>
                                                <?php
$stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
$stmt->bind_param('s', $_SESSION['UUID']);
$stmt->execute();
$pos = $stmt->get_result();
$stmt->close();

$positions = [
    1 => "President",
    2 => "Vice President for Internal Affairs",
    3 => "Vice President for External Affairs",
    4 => "Secretary"
];

if ($pos->num_rows > 0) {
    while ($row = $pos->fetch_assoc()) {
        $POS = $row['org_position'];
    }
} else {
    $POS = 0;
}

if ($POS > 0) {
    echo "<option selected disabled hidden value=''>Choose...</option>";
    foreach ($positions as $key => $value) {
        if (intval($POS) === $key) {
            echo "<option value='" . $key . "' selected>" . $value . "</option>";
        } else {
            echo "<option value='" . $key . "'disabled>" . $value . "</option>";
        }
    }
} else {
    echo "<option selected disabled hidden value=''>Choose...</option>";
    foreach ($positions as $key => $value) {
        echo "<option value='" . $key . "'>" . $value . "</option>";
    }
}?>
                                            </select>
                                        </div>
                                        <input type="hidden" id="perUUID" value="<?php echo $_SESSION['UUID']; ?>">
                                        <div class="col-md-12 d-flex justify-content-center gap-3">
                                            <button type="button" class="btn btn-secondary w-25 rounded-0 fw-bold"
                                                onclick="window.location.href = './Accesspage.php?pending=logout'">Back</button>
                                            <button type="button" class="btn btn-success w-50 rounded-0 fw-bold"
                                                id="submitForm">Submit</button>
                                        </div>
                                        <small class="text-muted">Note: Once you've submitted, you will be automatically logged out. You can then log back into your account.</small>
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