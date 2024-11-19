<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    require_once '../../../Database/Config.php';
    require_once '../../../Debug/GenLog.php';
}

if ($_SESSION['role'] != 1) {
    header('Location: ../../../Pages/Feed.php');
} else {
    echo '<script>var UUID = "' . $_SESSION['UUID'] . '";</script>';
}

if (!isset($_SESSION['UUID'])) {
    header('Location: ../../Accesspage.php?error=001');
} else {
    $logPath = "../../../Debug/Users/UUID.log";
}

function uuidv4($conn)
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    $checkUUID = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
    $stmt->bind_param('s', $checkUUID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        return uuidv4($conn);
    } else {
        return $checkUUID;
    }

    return $checkUUID;
}

$inactive = 1800; // 30 minutes inactivity
if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];

    if ($session_life > $inactive) {
        writeLog($logPath, "WARN", $_SESSION['UUID'], "Session Timeout", $_SERVER['REMOTE_ADDR'], "Session Timeout");
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
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Datatable/css/datatables.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/NavbarStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script defer type="module" src="../../../../Utilities/Scripts/UM_DBScript.js"></script>
    <title>Dashboard</title>
</head>

<style>
    .nav-tabs .nav-link.active,
    .nav-tabs .nav-item.show .nav-link {
        color: var(--bs-body-color) !important;
        background-color: var(--bs-success-border-subtle) !important;
        font-weight: bold;
        text-transform: uppercase;
    }
</style>

<body>
    <?php include_once "../../../../Assets/Icons/Icon_Assets.php"; ?>
    <?php $_SESSION['useBobbleBG'] == 1 ? include_once "../../../Components/BGanimation.php" : null;?>

    <div class="bg-dark bg-opacity-75 bg-blur z-3 position-fixed top-0 start-0 w-100 h-100 d-md-none">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="card border-0 bg-transparent">
                <div class="card-body text-center">
                    <img src="../../../../Assets/Images/Loader-v1.gif" alt="Loading" width="100" height="100">
                    <br>
                    <h3 class="text-white mt-3">You can't access this page on This Viewport</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid d-flex flex-row p-0 d-none d-lg-flex">
        <div class="modal" id="MultipleAccounts_Modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content bg-transparent border-0 rounded-1">
                    <div class="modal-body glass-default bg-opacity-10">
                        <div class="hstack">
                            <h4 class="modal-title text-center text-light">Create Multiple Accounts</h4>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" id="cococlose"
                                aria-label="Close"></button>
                        </div>
                        <small class="text-white">Please note: You may create between 2 and 10 accounts.</small>
                        <div class="row text-center mt-3">
                            <div class="col fw-bolder text-light text-uppercase">Role</div>
                            <div class="col fw-bolder text-light text-uppercase">Position</div>
                        </div>
                        <ol class="list-group list-group-numbered list-group-flush bg-transparent mb-3">
                            <?php for ($i = 0; $i < 10; $i++) { ?>
                            <li class="list-group-item d-none d-flex bg-transparent text-light"
                                data-MA="<?php echo $i; ?>"
                                id="MultipleAccounts_<?php echo $i; ?>">
                                <div class="row w-100 ms-2">
                                    <div class="col">
                                        <select class="form-select form-select-sm rounded-1"
                                            id="Role_Select_<?php echo $i; ?>">
                                            <option selected hidden disabled value="null">Select Role</option>
                                            <option value="1">Administrator</option>
                                            <option value="2">CSG Officer</option>
                                            <option value="3">Officer (Sub-Organization)</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <select class="form-select form-select-sm rounded-0"
                                            id="Pos_Select_<?php echo $i; ?>"
                                            disabled>
                                            <option selected hidden disabled value="null">Select Position</option>
                                            <option value="1">President</option>
                                            <option value="2">Vice President Internal</option>
                                            <option value="3">Vice President External</option>
                                            <option value="4">Secretary</option>
                                        </select>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                        </ol>
                        <small class="text-white">Temporary Email and Password will be generated automatically.</small>
                        <div class="d-flex justify-content-center my-3">
                            <button type="button" class="btn btn-success rounded-0" id="CreateMultiple_Btn">Create
                                Accounts</button>
                            <a class="btn btn-secondary rounded-0 ms-2 d-none" id="DL_zip_Btn" download
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Zip file will be deleted after closing the modal">Download Zip</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="UserEdModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content bg-transparent border-0 rounded-1">
                    <div class="modal-body glass-default bg-opacity-10">
                        <div class="hstack">
                            <h4 class="modal-title text-center text-light">Edit User Details</h4>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" id="cococlose"
                                aria-label="Close"></button>
                        </div>
                        <input type="hidden" id="Edituser_ID">
                        <div class="container mt-3 text-light">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="inputFName" class="form-label">First Name</label>
                                    <input type="text" class="form-control form-control-sm rounded-1" id="inputFName"
                                        pattern="[A-Za-z]{1,}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputLName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control form-control-sm rounded-1" id="inputLName"
                                        pattern="[A-Za-z]{1,}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputStudNum" class="form-label">Student Number</label>
                                    <input type="text" class="form-control form-control-sm rounded-1" id="inputStudNum"
                                        pattern="[0-9]{9}" required disabled>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm rounded-1" id="inputEmail"
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputContact" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control form-control-sm rounded-1" id="inputContact"
                                        pattern="[0-9]{11}" required>
                                    <script>
                                        $("#inputContact").keypress(function(e) {
                                            if (e.which < 48 || e.which > 57) {
                                                e.preventDefault();
                                            }
                                        });
                                    </script>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputOrg" class="form-label">Organization</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputOrg" required>
                                        <?php
                                            $stmt = $conn->prepare("SELECT * FROM sysorganizations");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['org_code'] . '" data-org="' . $row['org_short_name'] . '">' . $row['org_name'] . '</option>';
}
?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputPosition" class="form-label">Position</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputPosition" required>
                                        <option selected hidden disabled value="null">Select Position</option>
                                        <option value="1">President</option>
                                        <option value="2">Vice President Internal</option>
                                        <option value="3">Vice President External</option>
                                        <option value="4">Secretary</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputCourse" class="form-label">Course</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputCourse" data-old=""
                                        required>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputYear" class="form-label">Year</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputYear" required
                                        data-old="">
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputsection" class="form-label">Section</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputsection" required>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputpassword" class="form-label">New Password (Optional)</label>
                                    <div class="hstack">
                                        <input type="password" class="form-control form-control-sm rounded-1"
                                            id="inputpassword" required>
                                        <button class="btn btn-sm btn-secondary bg-transparent border-0 rounded-0"
                                            id="showPass" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Show Password">
                                            <svg width="24" height="24">
                                                <use xlink:href="#PassShow" id="UserPassShow" />
                                            </svg>
                                        </button>
                                        <script>
                                            $('#showPass').click(function() {
                                                var pass = $('#inputpassword');
                                                var icon = $('#UserPassShow');
                                                if (pass.attr('type') == 'password') {
                                                    pass.attr('type', 'text');
                                                    icon.attr('xlink:href', '#PassHide');
                                                } else {
                                                    pass.attr('type', 'password');
                                                    icon.attr('xlink:href', '#PassShow');
                                                }
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="hstack gap-2">
                                        <button id="userSC" class="btn btn-sm btn-success rounded-0">Save
                                            Changes</button>
                                        <button id="userRes" class="btn btn-sm btn-secondary rounded-0 d-none"
                                            disabled>Restore Account</button>
                                        <button id="userDel" class="btn btn-sm btn-danger rounded-0">Delete
                                            Account</button>
                                        <small class="badge bg-danger rounded-1 text-light ms-auto"
                                            id="userSCMsg"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="AdminEdModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content bg-transparent border-0 rounded-1">
                    <div class="modal-body glass-default bg-opacity-10">
                        <div class="hstack">
                            <h4 class="modal-title text-center text-light">Edit User Details</h4>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" id="cococlose"
                                aria-label="Close"></button>
                        </div>
                        <input type="hidden" id="Editadmin_ID">
                        <div class="container mt-3 text-light">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="inputFName_admin" class="form-label">First Name</label>
                                    <input type="text" class="form-control form-control-sm rounded-1"
                                        id="inputFName_admin" pattern="[A-Za-z]{1,}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputLName_admin" class="form-label">Last Name</label>
                                    <input type="text" class="form-control form-control-sm rounded-1"
                                        id="inputLName_admin" pattern="[A-Za-z]{1,}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputStudNum_admin" class="form-label">Student Number</label>
                                    <input type="text" class="form-control form-control-sm rounded-1"
                                        id="inputStudNum_admin" pattern="[0-9]{9}" required disabled>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputEmail_admin" class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm rounded-1"
                                        id="inputEmail_admin" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputContact_admin" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control form-control-sm rounded-1"
                                        id="inputContact_admin" pattern="[0-9]{11}" required>
                                    <script>
                                        $("#inputContact").keypress(function(e) {
                                            if (e.which < 48 || e.which > 57) {
                                                e.preventDefault();
                                            }
                                        });
                                    </script>
                                </div>
                                <div class="col-md-4 d-none">
                                    <label for="inputOrg_admin" class="form-label">Organization</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputOrg_admin" required>
                                        <?php
$stmt = $conn->prepare("SELECT * FROM sysorganizations");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['org_code'] . '" data-org="' . $row['org_short_name'] . '">' . $row['org_name'] . '</option>';
}
?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-none">
                                    <label for="inputPosition_admin" class="form-label">Position</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputPosition_admin"
                                        required>
                                        <option selected hidden disabled value="null">Select Position</option>
                                        <option value="1">President</option>
                                        <option value="2">Vice President Internal</option>
                                        <option value="3">Vice President External</option>
                                        <option value="4">Secretary</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-none">
                                    <label for="inputCourse_admin" class="form-label">Course</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputCourse_admin"
                                        data-old="" required>
                                    </select>
                                </div>
                                <div class="col-md-4 d-none">
                                    <label for="inputYear_admin" class="form-label">Year</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputYear_admin" required
                                        data-old="">
                                    </select>
                                </div>
                                <div class="col-md-4 d-none">
                                    <label for="inputsection_admin" class="form-label">Section</label>
                                    <select class="form-select form-select-sm rounded-1" id="inputsection_admin"
                                        required>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputpassword" class="form-label">New Password (Optional)</label>
                                    <div class="hstack">
                                        <input type="password_admin" class="form-control form-control-sm rounded-1"
                                            id="inputpassword_admin" required>
                                        <button class="btn btn-sm btn-secondary bg-transparent border-0 rounded-0"
                                            id="showPass_admin" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Show Password">
                                            <svg width="24" height="24">
                                                <use xlink:href="#PassShow" id="UserPassShow_admin" />
                                            </svg>
                                        </button>
                                        <script>
                                            $('#showPass').click(function() {
                                                var pass = $('#inputpassword_admin');
                                                var icon = $('#UserPassShow_admin');
                                                if (pass.attr('type') == 'password') {
                                                    pass.attr('type', 'text');
                                                    icon.attr('xlink:href', '#PassHide');
                                                } else {
                                                    pass.attr('type', 'password');
                                                    icon.attr('xlink:href', '#PassShow');
                                                }
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="hstack gap-2">
                                        <button id="adminSC" class="btn btn-sm btn-success rounded-0">Save
                                            Changes</button>
                                        <button id="adminRes" class="btn btn-sm btn-secondary rounded-0 d-none"
                                            disabled>Restore Account</button>
                                        <button id="adminDel" class="btn btn-sm btn-danger rounded-0">Delete
                                            Account</button>
                                        <small class="badge bg-danger rounded-1 text-light ms-auto"
                                            id="adminSCMsg"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="BS-Side d-none d-lg-block border-end glass-10 bg-opacity-50">
            <div class="d-flex flex-column justify-content-between h-100">
                <div class="container text-center my-2">
                    <?php
                        if ($_SESSION['ProfileImage'] == 'Default-Profile.gif') {?>
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
                        <small class="text-secondary text-uppercase">
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
                        <li class="list-group-item lg" onclick="window.location.href = './Dashboard.php'"
                            title="Dashboard">
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
                        <div class="accordion accordion-flush" id="Modules_Accord">
                            <div class="accordion-item bg-transparent">
                                <li class="list-group-item my-2 lg" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#flush-collapseOne" aria-expanded="false"
                                    aria-controls="flush-collapseOne">
                                    <svg class="me-3" width="24" height="24">
                                        <use xlink:href="#TestIcon" />
                                    </svg>
                                    Modules
                                </li>
                                <div id="flush-collapseOne" class="accordion-collapse collapse show"
                                    data-bs-parent="#Modules_Accord">
                                    <div class="accordion-body">
                                        <li class="list-group-item lg my-2 text-truncate" onclick="window.location.href = './CourseSection.php'">
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#Acad" />
                                            </svg>
                                            Courses & Sections
                                        </li>
                                        <li class="list-group-item lg my-2" onclick="window.location.href = './Organizations.php'">
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#Organic" />
                                            </svg>
                                            Organizations
                                        </li>
                                        <li class="list-group-item lg my-2 lg-active" title="User Management">
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
                        <li class="list-group-item lg" onclick="window.location.href = '../../Feed.php'" title="Feed">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Feed" />
                            </svg>
                            News Feed
                        </li>
                        <li class="list-group-item lg" onclick="window.location.href = '../../Preference.php'"
                            title="Messages">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Setting" />
                            </svg>
                            Settings
                        </li>
                        <li class="list-group-item lg my-2" onclick="window.location.href = './SystemReport.php'"
                            title="Messages">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Report" />
                            </svg>
                            Report
                        </li>
                        <li class="list-group-item lg text-danger"
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
                <ul class="nav nav-tabs nav-fill border-0 bg-transparent
                " id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-body active" id="NUP-tab" data-bs-toggle="tab"
                            data-bs-target="#NUP-tab-pane" type="button" role="tab" aria-controls="NUP-tab-pane"
                            aria-selected="true">Create <span class="d-none d-md-inline">Account</span></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-body" id="user-tab" data-bs-toggle="tab"
                            data-bs-target="#user-tab-pane" type="button" role="tab" aria-controls="user-tab-pane"
                            aria-selected="false">Officers <span class="d-none d-md-inline">Account</span></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-body" id="admin-tab" data-bs-toggle="tab"
                            data-bs-target="#admin-tab-pane" type="button" role="tab" aria-controls="admin-tab-pane"
                            aria-selected="false">Admins <span class="d-none d-md-inline">Account</span></button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="NUP-tab-pane" role="tabpanel" tabindex="0">
                        <div class="row m-5 pt-5 g-4">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-sm rounded-0" id="UUID_Input"
                                        placeholder="Unique User Identifier" readonly>
                                    <label for="UUID_Input">Unique User Identifier</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select form-select-sm rounded-0" id="Role_Select">
                                        <option selected hidden disabled value="null">Select Role</option>
                                        <option value="1">Administrator</option>
                                        <option value="2">CSG Officer</option>
                                        <option value="3">Officer (Sub-Organization)</option>
                                    </select>
                                    <label for="Role_Select">User Role</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select form-select-sm rounded-0" id="Pos_Select" required>
                                        <option selected hidden disabled value="null">Select Role</option>
                                        <option value="1">President</option>
                                        <option value="2">Vice President Internal</option>
                                        <option value="3">Vice President External</option>
                                        <option value="4">Secretary</option>
                                    </select>
                                    <label for="Pos_Select">User Position</label>
                                </div>
                            </div>
                            <script>
                                $('#Role_Select').change(function() {
                                    var role = $('#Role_Select').val();
                                    if (role == 1) {
                                        $('#Pos_Select').prop('disabled', true);
                                    } else {
                                        $('#Pos_Select').prop('disabled', false);
                                    }
                                });
                            </script>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-sm rounded-0"
                                        id="TempMail_Input" placeholder="Temporary Email" required>
                                    <label for="TempMail_Input">Temporary Email</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-sm rounded-0"
                                        id="TempPass_Input" placeholder="Temporary Password" required>
                                    <label for="TempPass_Input">Temporary Password</label>
                                </div>
                            </div>
                            <div class="col-md-6 hstack gap-2">
                                <button class="btn btn-success rounded-0 w-50" id="CreateAccount_Btn">Create
                                    Account</button>
                                <div class="hstack gap-2 w-50">
                                    <button class="btn btn-success rounded-0 ms-auto" id="GenTemp_Btn"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Generate Temporary Credentials">Generate</button>
                                    <button class="btn btn-secondary rounded-0 d-none" id="print_Btn"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Print Temporary Credentials (Expire within 5 minutes)"
                                        disabled>Print</button>
                                </div>
                            </div>
                            <div class="col-md-6 py-2">
                                <p class="text-danger" id="Error_Msg"></p>
                            </div>
                            <div class="col-md-12 mt-0 ps-3">
                                <p class="text-secondary">want to create multiple accounts? <a
                                        class="text-decoration-none text-primary" data-bs-toggle="modal"
                                        data-bs-target="#MultipleAccounts_Modal" style="cursor: pointer;">Click Here</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="user-tab-pane" role="tabpanel" tabindex="0">
                        <div class="container my-5">
                            <table class="table table-hover table-striped table-responsive" id="UserTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-nowrap">UUID</th>
                                        <th scope="col" class="text-nowrap">Full Name</th>
                                        <th scope="col" class="text-nowrap">Student Number</th>
                                        <th scope="col" class="text-nowrap">Email</th>
                                        <th scope="col" class="text-nowrap">Contact Number</th>
                                        <th scope="col" class="text-nowrap">Course</th>
                                        <th scope="col" class="text-nowrap">Organization</th>
                                        <th scope="col" class="text-nowrap">Status</th>
                                        <th scope="col" class="text-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="UserTable_Body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="admin-tab-pane" role="tabpanel" tabindex="0">
                        <div class="container my-5">
                            <table class="table table-hover table-striped table-responsive" id="AdminTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-nowrap">UUID</th>
                                        <th scope="col" class="text-nowrap">Full Name</th>
                                        <th scope="col" class="text-nowrap">Student Number</th>
                                        <th scope="col" class="text-nowrap">Email</th>
                                        <th scope="col" class="text-nowrap">Contact Number</th>
                                        <th scope="col" class="text-nowrap">Course</th>
                                        <th scope="col" class="text-nowrap">Status</th>
                                        <th scope="col" class="text-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="AdminTable_Body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>