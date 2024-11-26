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
    $stmt = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM activityproposaldocuments GROUP BY org_code");
    $Latest = $conn->prepare("SELECT act_title, file_path, date_Created, file_Size, org_code FROM activityproposaldocuments ORDER BY date_Created LIMIT 1");
} else {
    $stmt = $conn->prepare("SELECT SUM(file_Size), COUNT(*) FROM activityproposaldocuments WHERE org_code = ? GROUP BY org_code");
    $stmt->bind_param("s", $_SESSION['org_Code']);
    $Latest = $conn->prepare("SELECT act_title, file_path, date_Created, file_Size, org_code FROM activityproposaldocuments WHERE org_code = ? ORDER BY date_Created LIMIT 1");
    $Latest->bind_param("s", $_SESSION['org_Code']);
}
$stmt->execute();
$stmt->bind_result($fileSize, $fileCount);
$stmt->fetch();
$stmt->close();

$Latest->execute();
$Latest->bind_result($act_title, $file_path, $date_Created, $file_Size, $org_code);
$Latest->fetch();
$Latest->close();


if ($fileSize !== null) {
    if ($fileSize < 1024) {
        $Size = $fileSize;
        $SizeFormat = "B";
        $formattedFileSize = $fileSize . " B";
    } else if ($fileSize < 1048576) {
        $Size = round($fileSize / 1024, 2);
        $SizeFormat = "KB";
        $formattedFileSize = $Size . " KB";
    } else if ($fileSize < 1073741824) {
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
                                <h1 class="card-text"><?php echo $fileCount; ?></h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card bg-opacity-25 glass-default rounded-0 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Storage Usage <small>(<?php echo $percentage; ?>%)</small></h5>
                                <h3 class="card-text"><?php echo $formattedFileSize; ?> / <?php echo round($storage / 1024 / 1024 / 1024 / 1024, 2); ?> GB</h3>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card bg-opacity-25 glass-default rounded-0 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Latest Generated Files</h5>
                                <p class="card-text"><i class="fa fa-file"></i><span class="ms-2" onclick="window.open('<?php echo $realDocPath; ?>', '_blank');" style="cursor: pointer;"><?php echo $act_title; ?></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>