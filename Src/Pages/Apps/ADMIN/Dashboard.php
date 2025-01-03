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
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Datatable/css/datatables.css">
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
                            if ($_SESSION['role'] != 1) {
                                $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = '" . $_SESSION['org_Code'] . "'");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $stmt->close();
                                $org = $result->fetch_assoc();

                                if ($_SESSION['org_position'] == 1) {
                                    echo $org['org_short_name'] . ' President';
                                } elseif ($_SESSION['org_position'] == 2) {
                                    echo $org['org_short_name'] . ' Vice President Internal';
                                } elseif ($_SESSION['org_position'] == 3) {
                                    echo $org['org_short_name'] . ' Vice President External';
                                } else {
                                    echo $org['org_short_name'] . ' Secretary';
                                }
                            } else {
                                echo 'Administrator';
                            }?>
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
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-2">
                    <div class="col" data-bs-toggle="tooltip" data-bs-placement="top" title="Total Users">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">Active Users</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0" id="activeUsers">
                                        0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">CSG Officers</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0" id="csgOfficers">
                                        0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">Daily Logins</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0" id="dailyLogins">
                                        0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0 fw-bold fs-4">Locked Accounts</p>
                            <div class="card-body py-0">
                                <div class="d-flex flex-row justify-content-between">
                                    <p class="fs-1 fw-bold text-truncate mb-0" id="lockedAccounts">
                                        0
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        $(document).ready(function() {
                            function updateData() {
                                $.ajax({
                                    url: '../../../Functions/api/updateDashboardData.php',
                                    type: 'GET',
                                    success: function(data) {
                                        if (data.status == 'success') {
                                            $('#activeUsers').text(data.activeUsers);
                                            $('#csgOfficers').text(data.csguser);
                                            $('#dailyLogins').text(data.dailyLogins);
                                            $('#lockedAccounts').text(data.lockedUsers);
                                        } else {
                                            $('#activeUsers').text('0');
                                            $('#csgOfficers').text('0');
                                            $('#dailyLogins').text('0');
                                            $('#lockedAccounts').text('0');
                                        }
                                    },

                                    error: function() {
                                        $('#activeUsers').text('0');
                                        $('#csgOfficers').text('0');
                                        $('#dailyLogins').text('0');
                                        $('#lockedAccounts').text('0');
                                    }
                                });
                            }
                            updateData();
                            setInterval(() => {
                                updateData()
                            }, 3000);
                        });
                    </script>
                </div>
                <div class="row row-cols-1 row-cols-md-2 g-2 mt-3">
                    <div class="col-md-7 d-flex flex-column justify-content-between">
                        <div class="card bg-body bg-opacity-25 bg-blur-5 rounded-1 mb-2 h-25">
                            <div class="card-body py-4">
                                <h2 class="card-text text-center fw-bold" id="currentDate"></h2>
                                <h4 class="card-text text-center text-muted" id="currentTime"></h4>
                            </div>
                        </div>
                        <div class="card bg-body bg-opacity-25 bg-blur-5 rounded-1 h-75">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Login Chart</h5>
                                <canvas id="monthlyChart">Your browser does not support the canvas element.</canvas>
                                <?php include_once "./MontlyUserData.php"; ?>
                                <script>
                                    let month = [];
                                    let count = [];

                                    month = JSON.parse(
                                        '<?php echo $month; ?>');
                                    count = JSON.parse(
                                        '<?php echo $count; ?>');
                                    monthlist = ["January", "February", "March", "April", "May", "June", "July",
                                        "August", "September", "October", "November", "December"
                                    ];

                                    countlist = [];
                                    for (let i = 0; i < monthlist.length; i++) {
                                        if (month.includes(monthlist[i])) {
                                            countlist.push(count[month.indexOf(monthlist[i])]);
                                        } else {
                                            countlist.push(0);
                                        }
                                    }

                                    let ctx = document.getElementById('monthlyChart').getContext('2d');

                                    let myChart = new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: monthlist,
                                            datasets: [{
                                                label: 'Monthly Logins',
                                                data: countlist,
                                                backgroundColor: [
                                                    'rgba(255, 99, 132, 0.2)',
                                                    'rgba(54, 162, 235, 0.2)',
                                                    'rgba(255, 206, 86, 0.2)',
                                                    'rgba(75, 192, 192, 0.2)',
                                                    'rgba(153, 102, 255, 0.2)',
                                                    'rgba(255, 159, 64, 0.2)',
                                                    'rgba(255, 99, 132, 0.2)',
                                                    'rgba(54, 162, 235, 0.2)',
                                                    'rgba(255, 206, 86, 0.2)',
                                                    'rgba(75, 192, 192, 0.2)',
                                                    'rgba(153, 102, 255, 0.2)',
                                                    'rgba(255, 159, 64, 0.2)'
                                                ],
                                                borderColor: [
                                                    'rgba(255, 99, 132, 1)',
                                                    'rgba(54, 162, 235, 1)',
                                                    'rgba(255, 206, 86, 1)',
                                                    'rgba(75, 192, 192, 1)',
                                                    'rgba(153, 102, 255, 1)',
                                                    'rgba(255, 159, 64, 1)',
                                                    'rgba(255, 99, 132, 1)',
                                                    'rgba(54, 162, 235, 1)',
                                                    'rgba(255, 206, 86, 1)',
                                                    'rgba(75, 192, 192, 1)',
                                                    'rgba(153, 102, 255, 1)',
                                                    'rgba(255, 159, 64, 1)'
                                                ],
                                                borderWidth: 1

                                            }]
                                        },
                                        options: {
                                            scales: {
                                                y: {
                                                    beginAtZero: true
                                                }
                                            },
                                            animation: {
                                                duration: 2000,
                                                easing: 'easeInOutQuart'
                                            },
                                            plugins: {
                                                legend: {
                                                    display: false
                                                }
                                            }
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div>
                            <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="log-tab" data-bs-toggle="tab"
                                        data-bs-target="#userLog" type="button" role="tab" aria-controls="userLog"
                                        aria-selected="true">User Access Log</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="old-tab" data-bs-toggle="tab" data-bs-target="#oldLog"
                                        type="button" role="tab" aria-controls="oldLog" aria-selected="false">old
                                        Log</button>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="userLog" role="tabpanel" aria-labelledby="log-tab">
                                    <div class="card bg-body bg-opacity-25 bg-blur-5 rounded-1">
                                        <div class="card-body">
                                            <h4 class="card- text-uppercase text-bold text-center">User Access Log</h4>
                                            <div class="overflow-auto" style="max-height: 65svh;">
                                                <div class="list-group list-group-flush user-select-none" id="logList">
                                                    <div class="emptyfeed" id="EmptyFeed">
                                                        <div class="card border-0">
                                                            <div class="card-body text-center">
                                                                <img src="../../../../Assets/Images/Loader-v1.gif"
                                                                    alt="Loading" width="50" height="50">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Puking ina mo -->
                                                    <?php include_once "../../../Debug/Users/dispalyData.php"; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="oldLog" role="tabpanel" aria-labelledby="old-tab">
                                    <div class="card bg-body bg-opacity-25 bg-blur-5 rounded-1" style="height: 71vh;">
                                        <div class="card-body">
                                            <ol class="list-group list-group-numbered">
                                                <?php
                                            $dir = scandir("../../../Debug/Users/");
                                            $currentLog = date('Y-m') . '.log';
                                            $counter = 0;
rsort($dir);
foreach ($dir as $file) {
    if ($file != "." && $file != ".." && $file != "UUID.log" && pathinfo($file, PATHINFO_EXTENSION) == 'log') {
        
        $name = pathinfo($file, PATHINFO_FILENAME);
        $date = date('F d, Y', strtotime($name));

        if ($file == $currentLog) {
            echo '<li class="list-group-item"><aclass="text-decoration-none text-muted" disabled>' . $date . '.log</a> <span class="badge bg-primary rounded-pill">';
        } else {
            echo '<li class="list-group-item"><a href="../../../Debug/Users/' . $file . '" class="text-decoration-none" download>' . $date . '.log</a></li>';
        }
        $counter++;
    }
}

if ($counter == 0) {
    echo '<li class="list-group-item">No logs found</li>';
}

?>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-none">
                        <div class="card bg-body bg-opacity-25 bg-blur-5 rounded-1">
                            <div class="card-body">
                                <h4 class="card-title">Audit Logs</h4>
                                <div class="overflow-y-auto overflow-x-hidden" style="max-height: 65vh;">
                                    <table class="table table-hover table-striped table-responsive" id="audit_table">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-nowrap">Action</th>
                                                <th scope="col" class="text-nowrap">Changed Date</th>
                                                <th scope="col" class="text-nowrap">Changed By</th>
                                                <th scope="col" class="text-nowrap">Affected User</th>
                                                <th scope="col" class="text-nowrap">Details</th>
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