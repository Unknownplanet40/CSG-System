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

$stmt1 = $conn->prepare("SELECT SUM(file_Size) FROM activityproposaldocuments");
$stmt1->execute();
$stmt1->bind_result($fileSize1);
$stmt1->fetch();
$stmt1->close();

$stmt2 = $conn->prepare("SELECT SUM(file_Size) FROM excuseletterdocuments");
$stmt2->execute();
$stmt2->bind_result($fileSize2);
$stmt2->fetch();
$stmt2->close();

$stmt3 = $conn->prepare("SELECT SUM(file_Size) FROM minutemeetingdocuments");
$stmt3->execute();
$stmt3->bind_result($fileSize3);
$stmt3->fetch();
$stmt3->close();

$stmt4 = $conn->prepare("SELECT SUM(file_Size) FROM officememorandomdocuments");
$stmt4->execute();
$stmt4->bind_result($fileSize4);
$stmt4->fetch();
$stmt4->close();

$stmt5 = $conn->prepare("SELECT SUM(file_Size) FROM projectproposaldocuments");
$stmt5->execute();
$stmt5->bind_result($fileSize5);
$stmt5->fetch();
$stmt5->close();

$fileSize = $fileSize1 + $fileSize2 + $fileSize3 + $fileSize4 + $fileSize5;

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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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
                    <h3 class="fw-bold text-uppercase">Analytics</h3>
                </div>
                <div class="container-fluid">
                    <div class="row my-3 g-3">
                        <div class="col-md-8">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <canvas id="myChart"></canvas>
                                    <?php include_once "./Chart-data-1.php" ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-default bg-opacity-25 border-0 rounded-1">
                                <div class="card-body">
                                    <div class="vstack gap-3">
                                        <ul class="list-group list-group-flush">
                                            <?php
                                        usort($data, function ($a, $b) {
                                            return $b['count'] - $a['count'];
                                        });
foreach ($data as $row) {?>
                                            <li
                                                class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">
                                                        <?php echo $row['eventType']; ?>
                                                    </div>
                                                </div>
                                                <span><?php echo $row['count']; ?></span>
                                            </li>
                                            <?php }?>
                                            <li
                                                class="list-group-item d-flex justify-content-between align-items-start border-0 border-top border-3">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">Total Events</div>
                                                </div>
                                                <span><?php echo $total; ?></span>
                                            </li>
                                        </ul>
                                        <div class="d-flex align-items-end mt-3">
                                            <button class="btn btn-sm btn-outline-success ms-auto"
                                                onclick="window.location.href='./SystemReport.php#SystemReport-1'">
                                                View more Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body border m-3 rounded-1">
                                    <p class="fw-bold">Storage Usage</p>
                                    <h5 class="text-success">
                                        <?php echo $formattedFileSize; ?>
                                        /
                                        <?php echo round($storage / 1024 / 1024 / 1024 / 1024, 2); ?>
                                        TB
                                    </h5>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: <?php echo $percentage; ?>%;"
                                            aria-valuenow="<?php echo $percentage; ?>"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Account Status</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="./SystemReport.php#SystemReport-2"><small>View More</small></a>
                                    </div>
                                    <canvas id="User"></canvas>
                                    <?php include_once "./Chart-data-2.php"; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Preferred Theme</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="./SystemReport.php"><small>View More</small></a>
                                    </div>
                                    <div class="hstack h-100">
                                        <canvas id="Theme" style="width: 100%; height: 100%;"></canvas>
                                    </div>
                                    <?php include_once "./Chart-data-3.php"; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-default bg-opacity-10 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Sessions by OS</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="./SystemReport.php#SystemReport-3"><small>View More</small></a>
                                    </div>
                                    <div id="Device"></div>
                                    <?php include_once "./Chart-data-4.php"; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Task Assigned by Organization</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="../User_Modules/Dashboard.php"><small>View More</small></a>
                                    </div>
                                    <canvas id="task"></canvas>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1"
                                style="max-height: 600px;">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Course Enrolled</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="./CourseSection.php"><small>View More</small></a>
                                    </div>
                                    <canvas id="Course"></canvas>
                                    <?php include_once "./Chart-data-6.php"; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Generated Activity Proposals by Organization</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="../User_Modules/Dashboard.php"><small>View More</small></a>
                                    </div>
                                    <canvas id="Organization"></canvas>
                                    <?php include_once "./Chart-data-5.php"; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Generated Excuse Letters by Organization</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="../User_Modules/Dashboard.php"><small>View More</small></a>
                                    </div>
                                    <canvas id="EQLetter"></canvas>
                                    <?php include_once "./Chart-data-8.php"; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Generated Meeting Minutes by Organization</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="../User_Modules/Dashboard.php"><small>View More</small></a>
                                    </div>
                                    <canvas id="MMLetter"></canvas>
                                    <?php include_once "./Chart-data-9.php"; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Generated Office Memorandum by Organization</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="../User_Modules/Dashboard.php"><small>View More</small></a>
                                    </div>
                                    <canvas id="OMLetter"></canvas>
                                    <?php include_once "./Chart-data-10.php"; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Generated Project Proposals by Organization</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="../User_Modules/Dashboard.php"><small>View More</small></a>
                                    </div>
                                    <canvas id="PPLetter"></canvas>
                                    <?php include_once "./Chart-data-11.php"; ?>
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