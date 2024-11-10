<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    require_once '../../../Database/Config.php';
    require_once '../../../Debug/GenLog.php';
}

if ($_SESSION['role'] != 1) {
    header('Location: ../../../Pages/Feed.php');
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
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/NavbarStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    </script>
    <script defer type="module" src="../../../../Utilities/Scripts/UM_DBScript.js"></script>
    <title>Dashboard</title>
</head>

<body>
    <?php include_once "../../../../Assets/Icons/Icon_Assets.php"; ?>
    <?php include_once "../../../Components/Navbar_AB.php";?>
    <?php $_SESSION['useBobbleBG'] == 1 ? include_once "../../../Components/BGanimation.php" : null;?>

    <div class="container-fluid d-flex flex-row p-0">
        <!-- Modal -->
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
                                            <option value="2">Vice President</option>
                                            <option value="3">Secretary</option>
                                            <option value="4">Treasurer</option>
                                            <option value="5">Auditor</option>
                                            <option value="6">PRO</option>
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
                            <div class="accordion-item">
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
                                        <li class="list-group-item lg my-2 lg-active"
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
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-body active" id="NUP-tab" data-bs-toggle="tab"
                            data-bs-target="#NUP-tab-pane" type="button" role="tab" aria-controls="NUP-tab-pane"
                            aria-selected="true">Create New Account</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-secondary" id="user-tab" data-bs-toggle="tab"
                            data-bs-target="#user-tab-pane" type="button" role="tab" aria-controls="user-tab-pane"
                            aria-selected="false">Officer Account</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-secondary" id="admin-tab" data-bs-toggle="tab"
                            data-bs-target="#admin-tab-pane" type="button" role="tab" aria-controls="admin-tab-pane"
                            aria-selected="false">Admins Account</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="NUP-tab-pane" role="tabpanel" tabindex="0">
                        <div class="row m-5 pt-5 g-4">
                            <div class="col-md-12 d-flex justify-content-center">
                                <div class="form-floating w-50">
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
                                        <option value="2">Vice President</option>
                                        <option value="3">Secretary</option>
                                        <option value="4">Treasurer</option>
                                        <option value="5">Auditor</option>
                                        <option value="6">PRO</option>
                                    </select>
                                    <label for="Pos_Select">User Position</label>
                                </div>
                            </div>
                            <script>
                                $('#Role_Select').change(function () {
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
                                        title="Print Temporary Credentials (Expire within 5 minutes)" disabled>Print</button>
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
                    <div class="tab-pane fade" id="user-tab-pane" role="tabpanel" tabindex="0">...</div>
                    <div class="tab-pane fade" id="admin-tab-pane" role="tabpanel" tabindex="0">...</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>