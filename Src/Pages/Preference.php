<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    require_once '../Database/Config.php';
    require_once '../Debug/GenLog.php';
}

if (!isset($_SESSION['UUID'])) {
    header('Location: ../Pages/Accesspage.php?error=001');
} else {
    echo '<script>var UUID = "' . $_SESSION['UUID'] . '";</script>';
    $logPath = "../Debug/Users/UUID.log";
}

if (isset($_SESSION['accountStat'])) {
    if ($_SESSION['accountStat'] === 'pending') {
        header('Location: ./WaitingArea.php');
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
$logout = '../Functions/api/UserLogout.php';
?>


<!DOCTYPE html>
<html lang="en"
    data-bs-theme="<?php echo $_SESSION['theme']; ?>"
    id="webPreference">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css" />
    <link rel="stylesheet" href="../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/MNavbarStyle.css">
    <link rel="stylesheet" href="../../Utilities/Third-party/AOS/css/aos.css">
    <link rel="shortcut icon" href="../../Assets/Icons/PWA-Icon/MainIcon.png" type="image/x-icon">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/FeedStyle.css">
    <script defer src="../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
    <script src="../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="../../Utilities/Scripts/animate-browser-title.js"></script>
    <script type="module" src="../../Utilities/Scripts/FeedScipt.js"></script>
    <title>Preference</title>
</head>

<?php include_once '../../Assets/Icons/Icon_Assets.php'; ?>
<?php include_once '../Components/BGanimation.php' ?>

<body>
    <div class="" id="blurifAway">
        <?php include_once '../Components/mobileNavbar.php'; ?>
        <div class="container-fluid mt-3 con-H">
            <div class="row row-cols-1 row-cols-xl-3 row-cols-lg-3 row-cols-md-2 g-0">
                <div class="order-md-2 col-xl-7 col-lg-7 col-md-7">
                    <h4 class="text-uppercase fw-bolder ">Preference</h4>
                    <div class="container-fluid mt-3">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent"
                                id="theme">
                                <div class="row">
                                    <div class="col-md-4">
                                        <svg width="32" height="32">
                                            <use xlink:href="#TestIcon"></use>
                                        </svg>
                                        <span class="ms-3 fs-5">Theme</span>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-sm-6 mb-3 mb-sm-0">
                                                <div class="card h-100">
                                                    <img src="../../Assets/Images/Samples/LightMode_Sample.png"
                                                        class="card-img-top" alt="...">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">Light mode</h5>
                                                        <div class="vstack gap-1">
                                                            <button href="#"
                                                                class="btn btn-sm btn-outline-success fw-bold text-uppercase"
                                                                id="light-mode">Select</button>
                                                            <small class="text-secondary d-none"
                                                                id="islight-Label">Currently selected</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="card h-100">
                                                    <img src="../../Assets/Images/Samples/Darkmode_Sample.png"
                                                        class="card-img-top" alt="...">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">Dark mode</h5>
                                                        <div class="vstack gap-1">
                                                            <button href="#"
                                                                class="btn btn-sm btn-outline-success fw-bold text-uppercase"
                                                                id="dark-mode">Select</button>
                                                            <small class="text-secondary d-none"
                                                                id="isdark-Label">Currently selected</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent"
                                id="background">
                                <div class="row">
                                    <div class="col-md-4">
                                        <svg width="32" height="32">
                                            <use xlink:href="#TestIcon"></use>
                                        </svg>
                                        <span class="ms-3 fs-5">Background</span>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-sm-6 mb-3 mb-sm-0">
                                                <div class="card h-100">
                                                    <img src="../../Assets/Images/Samples/LightMode_Sample.png"
                                                        class="card-img-top" alt="...">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">Plain Background</h5>
                                                        <div class="vstack gap-1">
                                                            <button href="#"
                                                                class="btn btn-sm btn-outline-success fw-bold text-uppercase"
                                                                id="plain-bg">Select</button>
                                                            <small class="text-secondary d-none">Currently
                                                                selected</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="card h-100">
                                                    <img src="../../Assets/Images/Samples/AnimatedBalls_Sample.png"
                                                        class="card-img-top" alt="...">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">Animated Balls</h5>
                                                        <div class="vstack gap-1">
                                                            <button href="#"
                                                                class="btn btn-sm btn-outline-success fw-bold text-uppercase"
                                                                id="bubbles-bg">Select</button>
                                                            <small class="text-danger">(Experimental feature)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="order-md-1 col-xl-2 col-lg-2 d-none d-lg-block">
                    <?php include_once './MPSB.php'; ?>
                </div>
                <div class="order-md-3 col-xl-3 col-lg-3 col-md-5 mb-2">
                    <div class="container">
                        <h4 class="text-uppercase fw-bolder">Description</h4>
                        <p class="text-muted" style="text-align: justify;" id="desc"></p>
                    </div>
                </div>
            </div>
        </div>
        <script src="../../Utilities/Third-party/AOS/js/aos.js"></script>
        <script>
            AOS.init();
        </script>

        <script>
            $(document).ready(function() {
                $("#theme").hover(function() {
                    $("#desc").text(
                        "Choose between Light Mode and Dark Mode to suit your viewing preference. Light Mode offers a classic, bright interface with high contrast and readability in well-lit settings. Dark Mode provides a darker interface that reduces eye strain in low-light environments and gives a sleek, modern look. Switch easily between modes to enhance your comfort and personalize your experience."
                    );
                }, function() {
                    $("#desc").text("");
                });

                $("#background").hover(function() {
                    $("#desc").text(
                        "Select a background style that fits your aesthetic. Choose Plain Background for a simple, clean look that keeps the focus on the content. Or go for Animated Balls Background to add a touch of liveliness, with subtle animations for a dynamic and engaging visual experience. Switch anytime to match your mood or style preference."
                    );
                }, function() {
                    $("#desc").text("");
                });

                var theme =
                    "<?php echo $_SESSION['theme']; ?>";
                var background =
                    "<?php echo  $_SESSION['useBobbleBG']; ?>";

                if (theme == "dark") {
                    $("#dark-mode").removeClass("btn-outline-success").addClass("btn-secondary").text(
                        "Selected").attr("disabled", true);
                    $("#light-mode").removeClass("btn-secondary").addClass("btn-outline-success").text("Select")
                        .attr("disabled", false);
                } else {
                    $("#dark-mode").removeClass("btn-secondary").addClass("btn-outline-success").text("Select")
                        .attr("disabled", false);
                    $("#light-mode").removeClass("btn-outline-success").addClass("btn-secondary").text(
                        "Selected").attr("disabled", true);
                }

                if (background == "0") {
                    $("#plain-bg").removeClass("btn-outline-success").addClass("btn-secondary").text("Selected")
                        .attr("disabled", true);
                    $("#bubbles-bg").removeClass("btn-secondary").addClass("btn-outline-success").text("Select")
                        .attr("disabled", false);
                    $("#UrBalls").addClass("d-none");
                } else {
                    $("#plain-bg").removeClass("btn-secondary").addClass("btn-outline-success").text("Select")
                        .attr("disabled", false);
                    $("#bubbles-bg").removeClass("btn-outline-success").addClass("btn-secondary").text(
                        "Selected").attr("disabled", true);
                    $("#UrBalls").removeClass("d-none");
                }

                function setTheme(themes) {
                    if (themes == "dark") {
                        $.ajax({
                            url: "../Functions/api/changeTheme.php",
                            method: "POST",
                            data: {
                                theme: themes
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    $("#webPreference").attr("data-bs-theme", themes);
                                    $("#dark-mode").removeClass("btn-outline-success").addClass(
                                            "btn-secondary")
                                        .text("Selected").attr("disabled", true);
                                    $("#light-mode").removeClass("btn-secondary").addClass(
                                            "btn-outline-success")
                                        .text("Select").attr("disabled", false);
                                } else {
                                    console.log(response.message);
                                }
                            },

                            error: function(xhr, status, error) {
                                console.log(xhr.responseText);
                            }
                        });

                    } else {
                        $.ajax({
                            url: "../Functions/api/changeTheme.php",
                            method: "POST",
                            data: {
                                theme: "auto"
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    $("#webPreference").attr("data-bs-theme", "auto");
                                    $("#dark-mode").removeClass("btn-secondary").addClass(
                                            "btn-outline-success")
                                        .text("Select").attr("disabled", false);
                                    $("#light-mode").removeClass("btn-outline-success").addClass(
                                            "btn-secondary")
                                        .text("Selected").attr("disabled", true);
                                } else {
                                    console.log(response.message);
                                }
                            },

                            error: function(xhr, status, error) {
                                console.log(xhr.responseText);
                            }
                        });
                    }
                }

                function setBackground(bg) {
                    if (bg == "0") {
                        $.ajax({
                            url: "../Functions/api/changeTheme.php",
                            method: "POST",
                            data: {
                                Background: bg
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    $("#plain-bg").removeClass("btn-outline-success").addClass(
                                            "btn-secondary")
                                        .text("Selected").attr("disabled", true);
                                    $("#bubbles-bg").removeClass("btn-secondary").addClass(
                                            "btn-outline-success")
                                        .text("Select").attr("disabled", false);
                                    $("#UrBalls").addClass("d-none");
                                } else {
                                    console.log(response.message);
                                }
                            },

                            error: function(xhr, status, error) {
                                console.log(xhr.responseText);
                            }
                        });

                    } else {
                        $.ajax({
                            url: "../Functions/api/changeTheme.php",
                            method: "POST",
                            data: {
                                Background: "1"
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    $("#plain-bg").removeClass("btn-secondary").addClass(
                                            "btn-outline-success")
                                        .text("Select").attr("disabled", false);
                                    $("#bubbles-bg").removeClass("btn-outline-success").addClass(
                                            "btn-secondary")
                                        .text("Selected").attr("disabled", true);
                                    $("#UrBalls").removeClass("d-none");
                                } else {
                                    console.log(response.message);
                                }
                            },

                            error: function(xhr, status, error) {
                                console.log(xhr.responseText);
                            }
                        });
                    }
                }

                $("#dark-mode").click(function() {
                    setTheme("dark");
                });

                $("#light-mode").click(function() {
                    setTheme("auto");
                });

                $("#plain-bg").click(function() {
                    setBackground("0");
                });

                $("#bubbles-bg").click(function() {
                    setBackground("1");
                });
            });
        </script>
</body>

</html>