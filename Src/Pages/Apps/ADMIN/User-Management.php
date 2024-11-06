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
    <!-- Modal -->
    <div class="modal fade" id="userRole" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="userRoleLabel" aria-hidden="true">
        <div class="modal-dialog text-body">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body bg-blur-10 bg-opacity-10">
                    <input type="hidden" id="userUUID" value="">
                    <div class="hstack gap-1 mb-3">
                        <span class="fw-bold">Additional Information</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="mb-3">
                        <label for="InputOrg" class="form-label">Select Organization</label>
                        <select class="form-select" id="InputOrg">
                            <option selected hidden>Choose...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="InputRole" class="form-label">Select Role</label>
                        <select class="form-select" id="InputRole" disabled>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="InputPosition" class="form-label">Position</label>
                        <select class="form-select" id="InputPosition">
                            <option selected hidden>Choose...</option>
                            <option value="1">President</option>
                            <option value="2">Vice President</option>
                            <option value="3">Secretary</option>
                            <option value="4">Treasurer</option>
                            <option value="5">Auditor</option>
                            <option value="6">PRO</option>
                            <option value="7">Member</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-sm btn-success" id="saveRole">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

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
                        <li class="list-group-item lg lg-active">
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
                            aria-selected="true">New User Approval</button>
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
                        <div class="container my-3 d-flex justify-content-center">
                            <div class="input-group " style="width: 50%;">
                                <input type="search" class="form-control form-control-sm" placeholder="Search User"
                                    id="searchUser">
                            </div>
                        </div>
                        <div class="overflow-x-auto w-100">
                            <table class="table table-striped table-hover table-responsive nowrap w-100" id="NUP_Table">
                                <thead>
                                    <tr>
                                        <th><span class="text-truncate">Full Name</span></th>
                                        <th><span class="text-truncate">Primary Email</span></th>
                                        <th><span class="text-truncate">Contact Number</span></th>
                                        <th><span class="text-truncate">Course</span></th>
                                        <th><span class="text-truncate">Student Number</span></th>
                                        <th><span class="text-truncate">Status</span></th>
                                        <th><span class="text-truncate">Action</span></th>
                                    </tr>
                                </thead>
                                <tbody id="NUP_Table_Data">
                                    <!-- Data will be fetched here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="container d-flex justify-content-end">
                            <nav style="cursor: pointer;">
                                <ul class="pagination pagination-sm" id="NUP_Pagination">
                                </ul>
                            </nav>
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