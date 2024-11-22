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

if ($_SESSION['role'] != 1) {
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
                                    <script>
                                        var ctx = document.getElementById('myChart').getContext('2d');
                                        var myChart = new Chart(ctx, {
                                            type: 'radar',
                                            data: {
                                                labels: <?php echo json_encode($labels); ?> ,
                                                datasets: [{
                                                    label: 'Event Count',
                                                    data: <?php echo json_encode($values); ?> ,
                                                    backgroundColor: <?php echo json_encode($RandomBGcolor); ?> ,
                                                    borderColor: <?php echo json_encode($RandomBRcolor); ?> ,
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                scales: {
                                                    r: {
                                                        beginAtZero: true,
                                                        suggestedMax: <?php echo max($values); ?>
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: false,
                                                        position: 'top',
                                                    }
                                                }
                                            }
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php
                                        usort($data, function ($a, $b) {
                                            return $b['count'] - $a['count'];
                                        });
foreach ($data as $row) {?>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
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
                                            onclick="window.location.href='./SystemReport.php#SystemReport-1'">View more
                                            Details</button>
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
                                            href="./SystemReport.php?status=active"><small>View More</small></a>
                                    </div>
                                    <canvas id="User"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Preferred Theme</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="./SystemReport.php?status=active"><small>View More</small></a>
                                    </div>
                                    <canvas id="Theme" style="width: 100%; height: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-default bg-opacity-25 border-0 h-100 rounded-1">
                                <div class="card-body">
                                    <div class="hstack gap-2">
                                        <p class="fw-bold">Device Used</p>
                                        <a class="text-decoration-none text-success ms-auto"
                                            href="./SystemReport.php?status=active"><small>View More</small></a>
                                    </div>
                                    <canvas id="Device"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once "./Chart-data-2.php"; ?>
    <?php include_once "./Chart-data-3.php"; ?>
    <?php include_once "./Chart-data-4.php"; ?>
    <script>
        var ctx = document.getElementById('User').getContext('2d');
        var ctx2 = document.getElementById('Theme').getContext('2d');
        var ctx3 = document.getElementById('Device').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels); ?> ,
                datasets: [{
                    label: 'User Count',
                    data: <?php echo json_encode($values); ?> ,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    }
                }
            }
        });

        var myChart2 = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels_theme); ?> ,
                datasets: [{
                    label: 'Theme Count',
                    data: <?php echo json_encode($values_theme); ?> ,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                        position: 'bottom',
                    }
                }
            }
        });

        var myChart3 = new Chart(ctx3, {
            type: 'polarArea',
            data: {
                labels: <?php echo json_encode($labels_device); ?> ,
                datasets: [{
                    label: 'Device Count',
                    data: <?php echo json_encode($values_device); ?> ,
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        suggestedMax: <?php echo max($values_device); ?>
                    }
                }
            }
        });
    </script>
</body>

</html>