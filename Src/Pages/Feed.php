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

    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
    {
        $stmt->bind_param("s", $_SESSION['UUID']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row['accountStat'] == 'pending') {
            header('Location: ../Functions/api/UserLogout.php?error=005');
        }


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
    data-bs-theme="<?php echo $_SESSION['theme']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css" />
    <link rel="stylesheet" href="../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/MNavbarStyle.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../Assets/Icons/PWA-Icon/MainIcon.png" type="image/x-icon">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/FeedStyle.css">

    <script defer src="../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
    <script src="../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="../../Utilities/Scripts/animate-browser-title.js"></script>
    <script type="module" src="../../Utilities/Scripts/FeedScipt.js"></script>
    <title>Anouncements Feed</title>
    <style>
        .away {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(69, 69, 69, 0.5);
            backdrop-filter: blur(3px);
            z-index: 1020;
        }

        .away::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100px;
            height: 100px;
            background-image: url("../../Assets/Images/Loader-v1.gif");
            background-size: cover;
        }

        .ansscroll {
            max-height: 90svh;
            overflow-y: auto;
        }
    </style>
</head>

<?php include_once '../../Assets/Icons/Icon_Assets.php'; ?>
<?php $_SESSION['useBobbleBG'] == 1 ? include_once '../Components/BGanimation.php' : null; ?>

<body>
    <div class="" id="blurifAway">
        <!-- Chat Room Modal -->
        <div class="modal fade" id="chatRoom" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content bg-transparent border-0">
                    <div class="modal-header border-0 glass-default bg-opacity-25">
                        <div class="hstack gap-2 w-100">
                            <div class="me-auto d-flex align-items-center gap-1">
                                <input type="hidden" id="toUserUUID" value="">
                                <img alt="" width="32" height="32" id="toUserImage" class="rounded-circle mx-2">
                                <div class="p-2 me-2">
                                    <p class="alert-heading fw-bold moved text-wrap" id="toUserFullName">It's a Fuking
                                        name</p>
                                    <small class="moveu" id="toUserEmail">It's a Fuking Email</small>
                                </div>
                            </div>
                            <button type="button" data-bs-dismiss="modal" class="btn-close" aria-label="Close"
                                id="closeChatRoom"></button>
                        </div>
                    </div>
                    <div class="modal-body bg-blur-10 bg-opacity-10" id="messageBody"
                        style="overflow-y: auto; height: 500px;">
                        <div class="text-center text-muted d-none" id="noMessage">
                            <div class="container d-flex justify-content-center">
                                <div class="card border-0 bg-transparent">
                                    <div class="card-body">
                                        <div class="vstack">
                                            <div class="hstack">
                                                <span class="d-flex justify-content-center left-img">
                                                    <img src="https://i.imgur.com/Xd06F5f.jpeg" alt="From User Image"
                                                        height="100"
                                                        class="rounded-circle border border-5 border-trertiary bg-secondary"
                                                        id="fromUserImage">
                                                </span>
                                                <span class="d-flex justify-content-center right-img">
                                                    <img src="../../Assets/Images/Loader-v1.gif" alt="To User Image"
                                                        width="100" height="100"
                                                        class="rounded-circle border border-5 border-trertiary bg-secondary"
                                                        id="toUserImage">
                                                </span>
                                            </div>
                                            <h5 class="fw-bold mt-3">No Conversation Yet</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="messageContainer">
                            <div class="col-12"> <!-- From User Mesage -->
                                <div class="d-flex justify-content-start">
                                    <div class="vstack d-flex align-items-start">
                                        <span class="p-2 rounded-1 text-bg-secondary text-wrap me-5">lorem ipsum dolor
                                            sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                                            labore et dolore magna aliqua.</span>
                                        <small class="text-muted">3:00 PM</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12"> <!-- To User Message -->
                                <div class="d-flex justify-content-end">
                                    <div class="vstack d-flex align-items-end">
                                        <span class="p-2 rounded-1 text-bg-primary text-wrap ms-5">lorem ipsum dolor sit
                                            amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                                            labore et dolore magna aliqua.</span>
                                        <small class="text-secondary">3:00 PM</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 glass-default bg-opacity-25 p-0">
                        <div class="hstack w-100 gap-2">
                            <div class="w-100 position-relative">
                                <textarea class="form-control glass-default border-0" id="messageInput"
                                    placeholder="Type a message" rows="4" style="resize: none;"
                                    maxlength="500"></textarea>
                                <div class="position-absolute bottom-0 end-0 p-0 pe-2">
                                    <small class="text-secondary" id="charCount">0/500</small>
                                </div>
                            </div>
                            <div class="position-relative">
                                <button type="button" class="btn btn-sm btn-primary h-100 my-1
                            " id="sendMessage">
                                    <svg width="24" height="24">
                                        <use xlink:href="#SendIcon"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once '../Components/mobileNavbar_Feed.php'; ?>
        <div class="container-fluid mt-3 con-H">
            <div class="row row-cols-1 row-cols-xl-3 row-cols-lg-3 row-cols-md-2 g-0">
                <div class="order-md-2 col-xl-7 col-lg-7 col-md-7">
                    <div class="container-fluid" id="annsmain">
                        <div class="emptyfeed" id="EmptyFeed">
                            <div class="card border-0 bg-transparent">
                                <div class="card-body text-center">
                                    <img src="../../Assets/Images/Loader-v1.gif" alt="Loading" width="100" height="100">
                                </div>
                            </div>
                        </div>
                        <div class="row anncon g-1 d-none" id="Announcements">
                            <!-- This is where the mother fucking announcements will be displayed -->
                        </div>
                    </div>
                </div>
                <div class="order-md-1 col-xl-2 col-lg-2 d-none d-lg-block">
                    <div class="sidecon d-flex align-items-center">
                        <ul class="list-group list-group-flush feedsidebar user-select-none">
                            <li class="list-group-item border-0 rounded-pill feedsidebaritems feed-slected"
                                id="Feed-btn">
                                <svg width="24" height="24">
                                    <use xlink:href="#Feed"></use>
                                </svg>
                                <span class="ms-2">Feed</span>
                            </li>
                            <li class="list-group-item border-0 rounded-pill feedsidebaritems"
                                onclick="window.location.href='./Profile.php'" id="Profile-btn">
                                <svg width="24" height="24">
                                    <use xlink:href="#Profile"></use>
                                </svg>
                                <span class="ms-2">Profile</span>
                            </li>
                            <li class="list-group-item border-0 rounded-pill feedsidebaritems <?php echo $_SESSION['role'] != 1 ? 'd-none' : ''; ?>"
                                id="Dashboard-btn" onclick="window.location.href='./Apps/ADMIN/Dashboard.php'">
                                <svg width="24" height="24">
                                    <use xlink:href="#Dashboard"></use>
                                </svg>
                                <span class="ms-2">Dashboard</span>
                            </li>
                            <li class="list-group-item border-0 rounded-pill feedsidebaritems d-none"
                                id="Placeholder-btn">
                                <svg width="24" height="24">
                                    <use xlink:href="#TestIcon"></use>
                                </svg>
                                <span class="ms-2">Placeholder</span>
                            </li>
                            <li class="list-group-item border-0 rounded-pill feedsidebaritems" id="Preferences-btn"
                                onclick="window.location.href='./Preference.php'">
                                <svg width="24" height="24">
                                    <use xlink:href="#Settings"></use>
                                </svg>
                                <span class="ms-2">Preferences</span>
                            </li>
                            <li class="list-group-item border-0 rounded-pill feedsidebaritems" id="Logout-Button"
                                data-LogoutLink="<?php echo $logout; ?>">
                                <svg width="24" height="24">
                                    <use xlink:href="#Logout"></use>
                                </svg>
                                <span class="ms-2">Logout</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="order-md-3 col-xl-3 col-lg-3 col-md-5 mb-2">
                    <div class="container-fluid rounded-0 conbox" id="UserBox">
                        <div class="d-flex justify-content-center">
                            <div class="p-2">
                                <img src="
                            <?php
                            if (strpos($_SESSION['ProfileImage'], 'Default-Profile.gif') !== false) {
                                echo '../../Assets/Images/Default-Profile.gif';
                            } else {
                                echo '../../Assets/Images/UserProfiles/' . $_SESSION['ProfileImage'];
                            }?>
                            " width="100" height="100" class="rounded-circle" alt="Profile Picture">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="p-2">
                                <h5 class="fw-bold text-wrap text-center">
                                    <?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?>
                                </h5>
                                <p class="text-secondary text-center">
                                    <?php
                                        $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
$stmt->bind_param("s", $_SESSION['UUID']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['org_code'] != null) {
    $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = ?");
    $stmt->bind_param("i", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $org = $result->fetch_assoc();
    $stmt->close();

    if ($row['org_position'] == 1) {
        echo $org['org_short_name'] . " President";
    } elseif ($row['org_position'] == 2) {
        echo $org['org_short_name'] . " Vice President Internal";
    } elseif ($row['org_position'] == 3) {
        echo $org['org_short_name'] . " Vice President External";
    } else {
        echo $org['org_short_name'] . " Secretary";
    }
} else {
    echo "Administrator";
}

?>
                                </p>
                            </div>
                        </div>
                        <div class="my-3 d-flex justify-content-center searchbox">
                            <input type="search" class="form-control rounded-0" placeholder="Find People"
                                id="SearchUser">
                            <div class="vstack gap-2 mt-5 p-2 floatingResult rounded rounded-top-0 shadow d-none"
                                id="searchResult">
                                <div class="text-center">
                                    <div class="spinner-grow text-info" role="status">
                                        <span class="visually-hidden">Its Mother Fucking Loading</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="container conusers border py-3 rounded-1" id="userLoading">
                            <div class="card border-0 bg-transparent">
                                <div class="card-body text-center">
                                    <img src="../../Assets/Images/Loader-v1.gif" alt="Loading" width="48" height="48">
                                </div>
                            </div>
                        </div>
                        <div class="container-fluid conusers py-3 rounded-1 d-none" id="userContainers"></div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script>
            AOS.init();
        </script>
</body>

</html>