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

if ($_SESSION['role'] > 3) {
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

if ($_SESSION['role'] == 1) {
    $stmt1 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM activityproposaldocuments");

    $stmt2 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM excuseletterdocuments");

    $stmt3 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM minutemeetingdocuments");

    $stmt4 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM officememorandomdocuments");

    $stmt5 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM projectproposaldocuments");

    $Latest = $conn->prepare("SELECT act_title, file_path, date_Created, file_Size, org_code FROM activityproposaldocuments ORDER BY date_Created LIMIT 1");
    $latestExcuse = $conn->prepare("SELECT Event, file_path, DateCreated, file_Size, org_code FROM excuseletterdocuments ORDER BY DateCreated LIMIT 1");
    $latestMeeting = $conn->prepare("SELECT file_path, DateCreated, file_Size, org_code FROM minutemeetingdocuments ORDER BY DateCreated LIMIT 1");
    $latestOffice = $conn->prepare("SELECT OM_Sub, file_path, DateCreated, file_Size, org_code FROM officememorandomdocuments ORDER BY DateCreated LIMIT 1");
    $latestProject = $conn->prepare("SELECT act_title, file_path, date_Created, file_Size, org_code FROM projectproposaldocuments ORDER BY date_Created LIMIT 1");
} else {
    $stmt1 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM activityproposaldocuments WHERE org_code = ? GROUP BY org_code");
    $stmt1->bind_param("s", $_SESSION['org_Code']);

    $stmt2 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM excuseletterdocuments WHERE org_code = ? GROUP BY org_code");
    $stmt2->bind_param("s", $_SESSION['org_Code']);

    $stmt3 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM minutemeetingdocuments WHERE org_code = ? GROUP BY org_code");
    $stmt3->bind_param("s", $_SESSION['org_Code']);

    $stmt4 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM officememorandomdocuments WHERE org_code = ? GROUP BY org_code");
    $stmt4->bind_param("s", $_SESSION['org_Code']);

    $stmt5 = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM projectproposaldocuments WHERE org_code = ? GROUP BY org_code");
    $stmt5->bind_param("s", $_SESSION['org_Code']);

    $Latest = $conn->prepare("SELECT act_title, file_path, date_Created, file_Size, org_code FROM activityproposaldocuments WHERE org_code = ? ORDER BY date_Created LIMIT 1");
    $Latest->bind_param("s", $_SESSION['org_Code']);

    $latestExcuse = $conn->prepare("SELECT Event, file_path, DateCreated, file_Size, org_code FROM excuseletterdocuments WHERE org_code = ? ORDER BY DateCreated LIMIT 1");
    $latestExcuse->bind_param("s", $_SESSION['org_Code']);

    $latestMeeting = $conn->prepare("SELECT file_path, DateCreated, file_Size, org_code FROM minutemeetingdocuments WHERE org_code = ? ORDER BY DateCreated LIMIT 1");
    $latestMeeting->bind_param("s", $_SESSION['org_Code']);

    $latestOffice = $conn->prepare("SELECT OM_Sub, file_path, DateCreated, file_Size, org_code FROM officememorandomdocuments WHERE org_code = ? ORDER BY DateCreated LIMIT 1");
    $latestOffice->bind_param("s", $_SESSION['org_Code']);

    $latestProject = $conn->prepare("SELECT act_title, file_path, date_Created, file_Size, org_code FROM projectproposaldocuments WHERE org_code = ? ORDER BY date_Created LIMIT 1");
    $latestProject->bind_param("s", $_SESSION['org_Code']);
}
$stmt1->execute();
$stmt1->bind_result($fileSize, $actFileCount);
$stmt1->fetch();
$stmt1->close();

$stmt2->execute();
$stmt2->bind_result($excuseFileSize, $excuseFileCount);
$stmt2->fetch();
$stmt2->close();

$stmt3->execute();
$stmt3->bind_result($meetingFileSize, $meetingFileCount);
$stmt3->fetch();
$stmt3->close();

$stmt4->execute();
$stmt4->bind_result($officeFileSize, $officeFileCount);
$stmt4->fetch();
$stmt4->close();

$stmt5->execute();
$stmt5->bind_result($projectFileSize, $projectFileCount);
$stmt5->fetch();
$stmt5->close();

$Latest->execute();
$Latest->bind_result($act_title, $file_path, $date_Created, $file_Size, $org_code);
$Latest->fetch();
$Latest->close();

$latestExcuse->execute();
$latestExcuse->bind_result($act_title, $file_path, $date_Created, $file_Size, $org_code);
$latestExcuse->fetch();
$latestExcuse->close();

$latestMeeting->execute();
$latestMeeting->bind_result($file_path, $date_Created, $file_Size, $org_code);
$latestMeeting->fetch();
$latestMeeting->close();

$latestOffice->execute();
$latestOffice->bind_result($act_title, $file_path, $date_Created, $file_Size, $org_code);
$latestOffice->fetch();
$latestOffice->close();

$fileSize = $fileSize + $excuseFileSize + $meetingFileSize + $officeFileSize + $projectFileSize;
$fileCount = $actFileCount + $excuseFileCount + $meetingFileCount + $officeFileCount + $projectFileCount;

if ($fileSize !== null) {
    if ($fileSize < 1024) {
        $Size = $fileSize;
        $SizeFormat = "B";
        $formattedFileSize = $fileSize . " B";
    } elseif ($fileSize < 1048576) {
        $Size = round($fileSize / 1024, 2);
        $SizeFormat = "KB";
        $formattedFileSize = $Size . " KB";
    } elseif ($fileSize < 1073741824) {
        $Size = round($fileSize / 1024 / 1024, 2);
        $SizeFormat = "MB";
        $formattedFileSize = $Size . " MB";
    } else {
        $Size = round($fileSize / 1024 / 1024 / 1024, 2);
        $SizeFormat = "GB";
        $formattedFileSize = $Size . " GB";
    }
} else {
    $Size = 0;
    $SizeFormat = "KB";
    $formattedFileSize = "0 KB";
}

$storage = disk_total_space("/");
$percentage = round(($fileSize / $storage) * 100, 2);


if ($fileCount == null) {
    $fileCount = 0;
}

$DocPath = str_replace("C:\\xampp\\htdocs\\CSG-System\\", "", $file_path);
$DocPath = str_replace("\\", "/", $DocPath);
$realDocPath = "../../../../" . $DocPath;
?>

<!DOCTYPE html>
<html lang="en"
    data-bs-theme="<?php echo $_SESSION['theme']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Datatable/css/datatables.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer type="module" src="../../../../Utilities/Scripts/BS_DBScript.js"></script>
    <script src="../../../../Utilities/Scripts/ODBScript.js"></script>
    <title>Dashboard</title>
    <style>
        .nav-tabs .nav-link.active,
        .nav-tabs .nav-item.show .nav-link {
            color: var(--bs-body-color) !important;
            background-color: var(--bs-success-border-subtle) !important;
            font-weight: bold;
            text-transform: uppercase;
        }

        .nav-tabs .nav-link {
            color: rgba(var(--bs-body-color-rgb), 0.5) !important;
            font-weight: bold;
        }
    </style>
</head>
<?php include_once "../../../../Assets/Icons/Icon_Assets.php"; ?>
<?php $_SESSION['useBobbleBG'] == 1 ? include_once "../../../Components/BGanimation.php" : null;?>

<body>
    <div class="modal" id="taskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content glass-default bg-opacity-10 border-0">
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5 tex-body">Create Task</h1>
                    <input type="hidden" id="taskID" name="taskID">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm">
                        <div class="mb-3">
                            <label for="taskTitle" class="form-label">Task Title</label>
                            <input type="text" class="form-control text-capitalize" id="taskTitle" name="taskTitle"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="taskDesc" class="form-label">Task Description</label>
                            <textarea class="form-control" id="taskDesc" name="taskDesc" rows="3" required
                                placeholder="Task Description"></textarea>
                        </div>
                        <!-- Only for Admin -->
                        <div
                            class="mb-3 <?php echo $_SESSION['role'] == 1 ? "" : "d-none"; ?>">
                            <label for="taskAssigned" class="form-label">Assigned To</label>
                            <select class="form-select text-capitalize" id="taskAssigned" name="taskAssigned" required>
                                <option value="" selected hidden>Select Organization</option>
                                <?php
                                    $stmt = $conn->prepare("SELECT org_code, org_name, org_short_name FROM sysorganizations WHERE stat = 0");
$stmt->execute();
$stmt->bind_result($org_code, $org_name, $org_short_name);
while ($stmt->fetch()) {
    echo "<option value='$org_code'>$org_short_name - $org_name</option>";
}
$stmt->close();?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskType" class="form-label">Document Type</label>
                            <select class="form-select" id="taskType" name="taskType" required>
                                <option value="" selected hidden>Select Document Type</option>
                                <option value="Activity Proposal">Activity Proposal</option>
                                <option value="Excuse Letter">Excuse Letter</option>
                                <option value="Meeting Minutes">Meeting Minutes</option>
                                <option value="Office Memorandum">Office Memorandum</option>
                                <option value="Project Proposal">Project Proposal</otption>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskStatus" class="form-label">Task Status</label>
                            <select class="form-select" id="taskStatus" name="taskStatus" required>
                                <option value="" selected hidden>Select Task Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Ongoing" disabled>Ongoing</option>
                                <option value="Completed" disabled>Completed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskDue" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="taskDue" name="taskDue" required
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <div class="vstack gap-2">
                        <button type="button" class="btn btn-sm btn-success" id="taskSubmit">Create Task</button>
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        <div class="BS-Side d-none d-lg-block border-end glass-10 bg-opacity-50">
            <?php include_once "./UDSB.php"; ?>
        </div>
        <div class="BS-Main mt-2">
            <div class="container">
                <div class="row g-3">
                    <div class="col-4">
                        <div class="card bg-opacity-25 glass-default rounded-0 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Generated Files</h5>
                                <h1 class="card-text">
                                    <?php echo $fileCount; ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card bg-opacity-25 glass-default rounded-0 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Storage Usage
                                    <small>(<?php echo $percentage; ?>%)</small>
                                </h5>
                                <h3 class="card-text">
                                    <?php echo $formattedFileSize; ?>
                                    /
                                    <?php echo round($storage / 1024 / 1024 / 1024 / 1024, 2); ?>
                                    TB
                                </h3>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: <?php echo $percentage; ?>%;"
                                        aria-valuenow="<?php echo $percentage; ?>"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card bg-opacity-25 glass-default rounded-0 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Current Date</h5>
                                <h1 class="card-text fw-bold">
                                    <?php echo date('M d, Y'); ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container mt-3">
                <ul class="nav nav-tabs d-flex justify-content-center" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link <?php echo $_SESSION['org_position'] == 4 ? "active" : ""; ?>"
                            id="Task-tab" data-bs-toggle="tab" data-bs-target="#Task-tab-pane" type="button" role="tab"
                            aria-controls="Task-tab-pane" aria-selected="false">Task</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link <?php echo $_SESSION['org_position'] == 4 ? "" : "active"; ?> position-relative"
                            id="home-tab" data-bs-toggle="tab" data-bs-target="#APDoc-tab-pane" type="button" role="tab"
                            aria-controls="APDoc-tab-pane" aria-selected="true">Activity Proposal
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success z-1 <?php echo $actFileCount == 0 ? "d-none" : ""; ?>">
                                <?php echo $actFileCount; ?>
                                <span class="visually-hidden">unread messages</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link position-relative" id="ELDoc-tab" data-bs-toggle="tab"
                            data-bs-target="#ELDoc-tab-pane" type="button" role="tab" aria-controls="ELDoc-tab-pane"
                            aria-selected="false">Excuse
                            Letter
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success z-1 <?php echo $excuseFileCount == 0 ? "d-none" : ""; ?>">
                                <?php echo $excuseFileCount; ?>
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link position-relative" id="MMDoc-tab" data-bs-toggle="tab"
                            data-bs-target="#MMDoc-tab-pane" type="button" role="tab" aria-controls="MMDoc-tab-pane"
                            aria-selected="false">Meeting Minutes
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success z-1 <?php echo $meetingFileCount == 0 ? "d-none" : ""; ?>">
                                <?php echo $meetingFileCount; ?>
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link position-relative" id="OMDoc-tab" data-bs-toggle="tab"
                            data-bs-target="#OMDoc-tab-pane" type="button" role="tab" aria-controls="OMDoc-tab-pane"
                            aria-selected="false">
                            Office Memorandum
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success z-1 <?php echo $officeFileCount == 0 ? "d-none" : ""; ?>">
                                <?php echo $officeFileCount; ?>
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link position-relative" id="PPDoc-tab" data-bs-toggle="tab"
                            data-bs-target="#PPDoc-tab-pane" type="button" role="tab" aria-controls="PPDoc-tab-pane"
                            aria-selected="false">
                            Project Proposal
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success z-1 <?php echo $projectFileCount == 0 ? "d-none" : ""; ?>">
                                <?php echo $projectFileCount; ?>
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade <?php echo $_SESSION['org_position'] == 4 ? "show active" : ""; ?>"
                        id="Task-tab-pane" role="tabpanel" aria-labelledby="Task-tab" tabindex="0">
                        <div class="card glass-default border-0">
                            <div class="card-body">
                                <div class="hstack gap-3">
                                    <h5 class="card-title">Task Documents</h5>
                                    <div
                                        class="ms-auto <?php echo ($_SESSION['role'] == 1 || ($_SESSION['role'] == 2 && $_SESSION['org_position'] != 4)) || ($_SESSION['role'] == 3 && $_SESSION['org_position'] != 4) ? "" : "d-none"; ?>">
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                            data-bs-target="#taskModal">Create Task</button>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-hover table-striped table-responsive my-3" id="TaskDocTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-nowrap">ID</th>
                                        <th scope="col" class="text-nowrap">Posted By</th>
                                        <th scope="col" class="text-nowrap">Task Title</th>
                                        <th scope="col" class="text-nowrap">Task Description</th>
                                        <th scope="col" class="text-nowrap">Doc Type</th>
                                        <th scope="col" class="text-nowrap">Org</th>
                                        <th scope="col" class="text-nowrap">Task Status</th>
                                        <th scope="col" class="text-nowrap">Date Created</th>
                                        <th scope="col" class="text-nowrap">Due Date</th>
                                        <th scope="col" class="text-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade <?php echo $_SESSION['org_position'] == 4 ? "" : "show active"; ?>"
                        id="APDoc-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                        <div class="card glass-default bg-opacity-10 border-0">
                            <div class="card-body">
                                <h5 class="card-title">Activity Proposal Documents</h5>
                                <table class="table table-hover table-striped table-responsive" id="APDocTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap">ID</th>
                                            <th scope="col" class="text-nowrap">activity title</th>
                                            <th scope="col" class="text-nowrap">To</th>
                                            <th scope="col" class="text-nowrap">From</th>
                                            <th scope="col" class="text-nowrap">File Size</th>
                                            <th scope="col" class="text-nowrap">Date Submitted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ELDoc-tab-pane" role="tabpanel" aria-labelledby="ELDoc-tab"
                        tabindex="0">
                        <div class="card glass-default bg-opacity-10 border-0">
                            <div class="card-body">
                                <h5 class="card-title">Excuse Letter Documents</h5>
                            </div>
                            <table class="table table-hover table-striped table-responsive" id="ELDocTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-nowrap">ID</th>
                                        <th scope="col" class="text-nowrap">Event</th>
                                        <th scope="col" class="text-nowrap">Excuse Letter</th>
                                        <th scope="col" class="text-nowrap">File Size</th>
                                        <th scope="col" class="text-nowrap">Created By</th>
                                        <th scope="col" class="text-nowrap">Date Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="MMDoc-tab-pane" role="tabpanel" aria-labelledby="MMDoc-tab"
                        tabindex="0">
                        <div class="card glass-default bg-opacity-10 border-0">
                            <div class="card-body">
                                <h5 class="card-title">Meeting Minutes Documents</h5>
                            </div>
                            <table class="table table-hover table-striped table-responsive" id="MMDocTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-nowrap">ID</th>
                                        <th scope="col" class="text-nowrap">Presider</th>
                                        <th scope="col" class="text-nowrap">Location</th>
                                        <th scope="col" class="text-nowrap">Date of Meeting</th>
                                        <th scope="col" class="text-nowrap">File Size</th>
                                        <th scope="col" class="text-nowrap">Created By</th>
                                        <th scope="col" class="text-nowrap">Date Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="OMDoc-tab-pane" role="tabpanel" aria-labelledby="OMDoc-tab"
                        tabindex="0">
                        <div class="card glass-default bg-opacity-10 border-0">
                            <div class="card-body">
                                <h5 class="card-title">Office Memorandum Documents</h5>
                            </div>
                            <table class="table table-hover table-striped table-responsive" id="OMDocTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-nowrap">ID</th>
                                        <th scope="col" class="text-nowrap">Subject</th>
                                        <th scope="col" class="text-nowrap">To</th>
                                        <th scope="col" class="text-nowrap">Created By</th>
                                        <th scope="col" class="text-nowrap">File Size</th>
                                        <th scope="col" class="text-nowrap">Date Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="PPDoc-tab-pane" role="tabpanel" aria-labelledby="PPDoc-tab"
                        tabindex="0">
                        <div class="card glass-default bg-opacity-10 border-0">
                            <div class="card-body">
                                <h5 class="card-title">Project Proposal Documents</h5>
                            </div>
                            <table class="table table-hover table-striped table-responsive" id="PPDocTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-nowrap">ID</th>
                                        <th scope="col" class="text-nowrap">activity title</th>
                                        <th scope="col" class="text-nowrap">To</th>
                                        <th scope="col" class="text-nowrap">From</th>
                                        <th scope="col" class="text-nowrap">File Size</th>
                                        <th scope="col" class="text-nowrap">Date Submitted</th>
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
    <script>
        $("#taskSubmit").click(function() {
            var taskTitle = $("#taskTitle").val();
            var taskDesc = $("#taskDesc").val();
            var taskAssigned = $("#taskAssigned").val();
            var taskType = $("#taskType").val();
            var taskStatus = $("#taskStatus").val();
            var taskDue = $("#taskDue").val();

            if (taskTitle == "" || taskDesc == "" || taskType == "" || taskStatus == "" || taskDue == "") {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        var emptyField = $("input, textarea, select").filter(function() {
                            return $(this).val() == "";
                        }).first();
                        emptyField.focus();
                        emptyField.addClass('is-invalid');
                        setTimeout(() => {
                            emptyField.removeClass('is-invalid');
                        }, 2000);
                    }
                }).fire({
                    title: 'All fields are required',
                    icon: 'info'
                });
                return;
            }

            if (taskAssigned == "" &&
                <?php echo $_SESSION['role']; ?>
                ==
                1) {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        var emptyField = $("input, textarea, select").filter(function() {
                            return $(this).val() == "";
                        }).first();
                        emptyField.focus();
                        emptyField.addClass('is-invalid');
                        setTimeout(() => {
                            emptyField.removeClass('is-invalid');
                        }, 2000);
                    }
                }).fire({
                    title: 'All fields are required',
                    icon: 'info'
                });
                return;
            }

            if (taskAssigned == "" &&
                <?php echo $_SESSION['role']; ?>
                !=
                1) {
                taskAssigned =
                    "<?php echo $_SESSION['org_Code']; ?>";
            }

            data = {
                taskTitle: taskTitle,
                taskDesc: taskDesc,
                taskAssigned: taskAssigned,
                taskType: taskType,
                taskStatus: taskStatus,
                taskDue: taskDue
            };

            $.ajax({
                url: "../../../Functions/api/CreateTask.php",
                type: "POST",
                data: data,
                success: function(response) {
                    if (response.status == "success") {
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        }).fire({
                            title: 'Successfully Created Task',
                            icon: 'success'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#TaskDocTable').DataTable().ajax.reload();
                                $('#taskModal').modal('hide');
                            }
                        });
                    } else {
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        }).fire({
                            title: 'Failed to Create Task',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    }).fire({
                        title: 'Something went wrong while creating task',
                        icon: 'error'
                    });
                }
            });
        });
    </script>
</body>

</html>