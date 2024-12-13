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
    if ($pos->num_rows > 0) {
        while ($row = $pos->fetch_assoc()) {
            $_SESSION['role'] = $row['role'];
            $_SESSION['isSubOrg'] = $row['isSubOrg'];
            echo "<script>let TemporaryRole = " . $_SESSION['role'] . "</script>";
        }
    } else {
        $_SESSION['role'] = 0;
        $_SESSION['isSubOrg'] = 0;
        echo "<script>let TemporaryRole = 0</script>";
    }
}

if (isset($_SESSION['accountStat'])) {
    if ($_SESSION['accountStat'] === 'active') {
        session_unset();
        session_destroy();
        header('Location: "./Accesspage.php"');
    } elseif ($_SESSION['accountStat'] === 'rejected') {
        session_unset();
        session_destroy();
        header('Location: "./Accesspage.php?error=006"');
    }
}

/*
$inactive = 1800; // 30 minutes inactivity
if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];

    if ($session_life > $inactive) {
        writeLog($logPath, "WARN", $_SESSION['UUID'], "Session Timeout", $_SERVER['REMOTE_ADDR'], "User Logged Out");
        header('Location: ../Functions/api/UserLogout.php?error=002');
    }
}

$_SESSION['last_activity'] = time();
*/

function Organization_OPTION($conn)
{
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
                    }
                } else {
                    echo "<option value='" . $row['org_code'] . "'>" . $row['org_name'] . "</option>";
                }
            }
        }
    } else {
        echo "<option selected disabled value=''>No Organization Available</option>";
    }
}

function Course_OPTION($conn)
{
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
            }
        }
    } else {
        echo "<option selected disabled hidden value=''>Choose...</option>";
        foreach ($positions as $key => $value) {
            echo "<option value='" . $key . "'>" . $value . "</option>";
        }
    }
}
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
    <style>
        .no-ann-box {
            position: relative;
        }

        .no-ann {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            opacity: 0;
            /* Start all images with opacity 0 */
            animation: fade 800ms ease-in-out forwards;
        }

        @keyframes fade {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        .no-ann:nth-child(2) {
            z-index: 2;
            animation-delay: 800ms;
        }

        .no-ann:nth-child(3) {
            z-index: 3;
            animation-delay: 1600ms;
        }

        .no-ann:nth-child(4) {
            z-index: 4;
            animation-delay: 2400ms;
        }
    </style>
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
                            <div class="col-md-4 d-flex justify-content-center align-items-center border">
                                <div class="d-flex justify-content-center align-items-center d-flex flex-column">
                                    <div class="no-ann-box" style="margin-bottom: 7rem; margin-top: 7rem;">
                                        <img src="../../Assets/Images/Logo-Layers/layer1.png" alt="Layer 1" width="230"
                                            height="230" class="rounded-circle no-ann" />
                                        <img src="../../Assets/Images/Logo-Layers/layer2.png" alt="Layer 2" width="230"
                                            height="230" class="rounded-circle no-ann" />
                                        <img src="../../Assets/Images/Logo-Layers/layer3.png" alt="Layer 3" width="230"
                                            height="230" class="rounded-circle no-ann" />
                                        <img src="../../Assets/Images/Logo-Layers/layer4.png" alt="Layer 4" width="230"
                                            height="230" class="rounded-circle no-ann" />
                                    </div>
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
                                            <input type="text" class="form-control rounded-0 rounded-0" id="perFname"
                                                placeholder="e.g. Juan" required autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="perLname" class="form-label">Last Name</label>
                                            <input type="text" class="form-control rounded-0" id="perLname" required
                                                placeholder="e.g. Dela Cruz" autofocus>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="perEmail" class="form-label">Cvsu mail</label>
                                            <input type="email" class="form-control rounded-0" id="perEmail" required
                                                placeholder="Example@cvsu.edu.ph">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="perpassword" class="form-label">Password</label>
                                            <input type="password" class="form-control rounded-0" id="perpassword"
                                                required>
                                        </div>
                                        <div
                                            class="col-md-4 <?php echo $_SESSION['role'] == 1 ? 'd-none' : ''; ?>">
                                            <label for="perCourse" class="form-label">Course</label>
                                            <select class="form-select rounded-0" id="perCourse" required>
                                                <option selected disabled hidden value="">Choose...</option>
                                            </select>
                                        </div>
                                        <div
                                            class="col-md-3 <?php echo $_SESSION['role'] == 1 ? 'd-none' : ''; ?>">
                                            <label for="perYear" class="form-label">Year Level</label>
                                            <select class="form-select rounded-0" id="perYear" required>
                                                <option selected disabled>Waiting for Course...</option>
                                            </select>
                                        </div>
                                        <div
                                            class="col-md-3 <?php echo $_SESSION['role'] == 1 ? 'd-none' : ''; ?>">
                                            <label for="perSection" class="form-label">Section</label>
                                            <select class="form-select rounded-0" id="perSection" required>
                                                <option selected disabled>Waiting for Year Level...</option>
                                            </select>
                                        </div>
                                        <div
                                            class="col-md-2 <?php echo $_SESSION['role'] == 1 ? 'd-none' : ''; ?>">
                                            <label for="perirreg" class="form-label">Irregular</label>
                                            <select class="form-select rounded-0" id="perirreg" required>
                                                <option value="0" selected>No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                            <script>
                                                document.getElementById('perirreg').addEventListener('change',
                                                    function() {
                                                        const fields = ['perSection', 'perYear', 'perCourse'];
                                                        fields.forEach(field => {
                                                            const element = document.getElementById(field);
                                                            if (this.value === '1') {
                                                                element.disabled = true;
                                                                element.selectedIndex = 0;
                                                                element.required = false;
                                                            } else {
                                                                element.disabled = false;
                                                                element.required = true;
                                                            }
                                                        });

                                                    });
                                            </script>
                                        </div>
                                        <input type="hidden" id="CourseCode" value="">
                                        <div class="col-md-4" data-bs-toggle="tooltip" data-bs-placement="left"
                                            data-bs-html="true" data-bs-trigger="hover focus"
                                            data-bs-title="Please be Reminded that you will use this Student Number to Log in to the System.">
                                            <label for="perStudentno" class="form-label">Student Number</label>
                                            <input type="text" class="form-control rounded-0" id="perStudentno" required
                                                placeholder="20xxxxxxx" maxlength="9">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="perContact" class="form-label">Contact Number</label>
                                            <input type="text" class="form-control rounded-0" id="perContact" required
                                                maxlength="11" placeholder="09xxxxxxxxx">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="perBirthdate" class="form-label">Birthdate</label>
                                            <input type="date" class="form-control rounded-0" id="perBirthdate"
                                                required>
                                        </div>
                                        <input type="hidden" id="perAge" value="">
                                        <div
                                            class="col-md-6 <?php echo $_SESSION['role'] == 1 ? 'd-none' : ''; ?>">
                                            <label for="perOrg"
                                                class="form-label"><?php echo $_SESSION['isSubOrg'] === 1 ? 'Sub-Organization' : 'Organization'; ?></label>
                                            <select class="form-select rounded-0" id="perOrg" required>
                                                <?php Organization_OPTION($conn); ?>
                                            </select>
                                        </div>
                                        <div
                                            class="col-md-6 <?php echo $_SESSION['role'] == 1 ? 'd-none' : ''; ?>">
                                            <label for="perPosition" class="form-label">Position</label>
                                            <select class="form-select rounded-0" id="perPosition" required>
                                                <?php Course_OPTION($conn); ?>
                                            </select>
                                        </div>
                                        <input type="hidden" id="perUUID"
                                            value="<?php echo $_SESSION['UUID']; ?>">
                                        <div class="col-md-12 d-flex justify-content-center gap-3">
                                            <button type="button" class="btn btn-secondary w-25 rounded-0 fw-bold"
                                                onclick="window.location.href = './Accesspage.php?pending=logout'">Cancel</button>
                                            <button type="button" class="btn btn-success w-50 rounded-0 fw-bold"
                                                id="submitForm">Submit</button>
                                        </div>
                                        <small class="text-muted">Note: Once you've submitted, you will be automatically
                                            logged out. You can then log back into your account.</small>
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