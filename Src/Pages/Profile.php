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
        writeLog($logPath, "WARN", $_SESSION['UUID'], "Session Timeout", $_SERVER['REMOTE_ADDR'], "Session Timeout");
        header('Location: ../Functions/api/UserLogout.php?error=002');
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
    <link rel="stylesheet" href="../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../Utilities/Third-party/AOS/css/aos.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/NavbarStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/ProfileStyle.css">
    <link rel="shortcut icon" href="../../Assets/Icons/PWA-Icon/MainIcon.png" type="image/x-icon">
    <script defer src="../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="../../Utilities/Scripts/animate-browser-title.js"></script>
    <script type="module" src="../../Utilities/Scripts/ProfileScript.js"></script>

    <title>Profile</title>
</head>

<?php include_once '../../Assets/Icons/Icon_Assets.php'; ?>
<?php $_SESSION['useBobbleBG'] == 1 ? include_once '../Components/BGanimation.php' : null; ?>

<body>
    <div class="modal" id="NewPostModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body" id="CreatePost">
                    <div class="row">
                        <div class="col-12">
                            <div class="row g-2 my-2" data-aos="fade-down">
                                <div class="col-12">
                                    <div class="container-fluid" data-aos="fade-up" data-aos-delay="200">
                                        <div class="alert bg-body rounded border shadow" role="alert">
                                            <div class="hstack gap-0">
                                                <div>
                                                    <div class="hstack gap-1 mb-1">
                                                        <div class="shadow rounded-circle">
                                                            <?php
                                                                if ($_SESSION['ProfileImage'] == "Default-Profile.gif") {
                                                                    ?>
                                                            <img src="../../Assets/Images/Default-Profile.gif" alt=""
                                                                width="42" height="42" class="rounded-circle">
                                                            <?php
                                                                } else {?>
                                                            <img src="../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']?>" id="change-profile"
                                                                alt="" width="42" height="42" class="rounded-circle">
                                                            <?php }?>
                                                        </div>
                                                        <div class="p-2">
                                                            <p class="alert-heading text-truncate fw-bold moved"
                                                                style="max-width: 315px;">
                                                                <?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?>
                                                            </p>
                                                            <small
                                                                class="moveu"><?php echo date('F d, Y h:i A'); ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ms-auto me-1 p-1">
                                                    <div class="btn-group">
                                                        <input type="hidden" id="input-priority" value="0">
                                                        <span type="button"
                                                            class="btn btn-danger dropdown-toggle btn-sm bg-transparent border-0 text-body"
                                                            data-bs-toggle="dropdown" aria-expanded="false"
                                                            id="priority">
                                                            <svg width="24" height="24" id="in-T" class=""
                                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                                data-bs-trigger="hover"
                                                                data-bs-title="Please select a priority level">
                                                                <use xlink:href="#Prio" />
                                                            </svg>
                                                            <svg width="24" height="24" id="in-P-Low" class="d-none"
                                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                                data-bs-trigger="hover" data-bs-title="Low Priority">
                                                                <use xlink:href="#LowPriority" />
                                                            </svg>
                                                            <svg width="24" height="24" id="in-P-Norm" class="d-none"
                                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                                data-bs-trigger="hover" data-bs-title="Normal Priority">
                                                                <use xlink:href="#NormPriority" />
                                                            </svg>
                                                            <svg width="24" height="24" id="in-P-High" class="d-none"
                                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                                data-bs-trigger="hover" data-bs-title="High Priority">
                                                                <use xlink:href="#HighPriority" />
                                                            </svg>
                                                        </span>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" style="cursor: pointer;"
                                                                    id="LP">
                                                                    <svg width="24" height="24">
                                                                        <use xlink:href="#LowPriority" />
                                                                    </svg>
                                                                    Low Priority
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" style="cursor: pointer;"
                                                                    id="NP">
                                                                    <svg width="24" height="24">
                                                                        <use xlink:href="#NormPriority" />
                                                                    </svg>
                                                                    Normal Priority
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" style="cursor: pointer;"
                                                                    id="HP">
                                                                    <svg width="24" height="24">
                                                                        <use xlink:href="#HighPriority" />
                                                                    </svg>
                                                                    High Priority
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-floating d-none">
                                                <textarea class="form-control border-0" placeholder="Create a Post"
                                                    style="white-space: pre-wrap;" id="post-details"
                                                    style="height: 100px" maxlength="500" required rows="10"></textarea>
                                                <label for="post-details">What's on your mind?</label>
                                            </div>
                                            <div id="editor" class="form-control border-0 rounded-1 vh-50"
                                                style="height: 300px; overflow-y: auto;">
                                            </div>
                                            <hr>
                                            <small class="text-muted">Reminder: Once posted, your announcement is
                                                permanent and visible to everyone. It cannot be edited, only deleted if
                                                necessary—make sure your message is final before posting!</small>
                                            <div class="hstack gap-1">
                                                <div>
                                                    <button class="btn btn-sm btn-primary rounded-1 border-0 my-1 px-5"
                                                        id="SubmitPost">
                                                        <svg width="24" height="24">
                                                            <use xlink:href="#CreatePost" />
                                                        </svg>
                                                        <span class="d-sm-none ms-2">Create Post</span>
                                                    </button>
                                                </div>
                                                <div class="ms-auto blockquote-footer my-1">
                                                    <small class="">
                                                        <cite>
                                                            <small class="text-muted" id="charCount">0/500</small>
                                                            <input id="USER_UUID" type="hidden"
                                                                value="<?php echo $_SESSION['UUID']; ?>">
                                                        </cite>
                                                    </small>
                                                    <button
                                                        class="btn btn-sm btn-outline-danger border-0 rounded-1 my-1"
                                                        id="ClearPost">
                                                        <svg width="24" height="24">
                                                            <use xlink:href="#Trash" />
                                                        </svg>
                                                        <span class="d-sm-none ms-2">Clear</span>
                                                    </button>
                                                    <button
                                                        class="btn btn-sm btn-outline-secondary border-0 rounded-1 my-1"
                                                        data-bs-dismiss="modal" id="closePostModal">
                                                        <svg width="24" height="24">
                                                            <use xlink:href="#Close" />
                                                        </svg>
                                                        <span class="d-sm-none ms-2">Close</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="ProfileModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content glass-default border-0 bg-opacity-25 rounded-1">
                <div class="modal-body">
                    <div class="container">
                        <div class="row g-2 row-cols-1 row-cols-md-2">
                            <div class="col-md-12 d-flex justify-content-center align-items-center">
                                <?php if ($_SESSION['ProfileImage'] == "Default-Profile.gif") { ?>
                                <img src="../../Assets/Images/Default-Profile.gif" id="profileImage"
                                    class="img-fluid border-3 rounded-circle" width="192" height="192"
                                    alt="<?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?> Profile Image">
                                <?php } else { ?>
                                <img src="../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']; ?>"
                                    class="img-fluid border rounded-circle" width="192" height="192" 
                                    alt="<?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?> Profile Image">
                                <?php } ?>
                            </div>
                            <div class="col-md-8 d-none">
                                <div class="ratio ratio-16x9 rounded-3 bg-body bg-opacity-10 bg-blur-3">
                                    <img src="../../Assets/Images/orgAssets/orgCover/Default-CSG-Cover.jpg"
                                        alt="Cover Image" class="object-fit-cover border-0 rounded-3 p-0">
                                </div>
                            </div>
                        </div>
                        <div class="hstack gap-1 mb-2">
                            <button class="btn btn-sm text-light fw-bold rounded-1 border-0 me-auto ms-5"
                                id="changeProfimg">
                                <svg width="24" height="24">
                                    <use xlink:href="#changeProf" />
                                </svg>
                                <span>Update Profile</span>
                            </button>
                            <input type="file" class="d-none" id="change-profile" accept="image/*">
                            <button class="btn btn-sm text-light fw-bold rounded-1 border-0 me-5 d-none">
                                <svg width="24" height="24">
                                    <use xlink:href="#changeCov" />
                                </svg>
                                <span>Update Cover</span>
                                <input type="file" class="d-none" id="change-cover" accept="image/*">
                            </button>
                        </div>
                        <div class="card border-0 rounded-0 bg-transparent">
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        First Name
                                        <input type="text" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="<?php echo $_SESSION['FirstName']; ?>"
                                            id="FirstName">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Last Name
                                        <input type="text" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="<?php echo $_SESSION['LastName']; ?>"
                                            id="LastName">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Student Number
                                        <input type="text" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="<?php echo $_SESSION['student_Number']; ?>"
                                            id="studentNumber">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Course & Section
                                        <input type="text" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="BSIT-4B" id="course" data-original-value="BSIT-4B">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Email
                                        <input type="email" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="<?php echo $_SESSION['PrimaryEmail']; ?>"
                                            id="email"
                                            data-original-value="<?php echo $_SESSION['PrimaryEmail']; ?>">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Contact Number
                                        <input type="text" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="<?php echo $_SESSION['contactNumber']; ?>"
                                            id="contactNumber" maxlength="11"
                                            data-original-value="<?php echo $_SESSION['contactNumber']; ?>"
                                            pattern="[0-9]{11}" placeholder="09xxxxxxxxx">
                                    </li>
                                </ul>
                                <div class="hstack gap-1 mt-3">
                                    <button class="btn btn-sm btn-primary rounded-0 border-0 me-auto" id="SaveChanges">
                                        <svg width="18" height="18">
                                            <use xlink:href="#AccSav" />
                                        </svg>
                                        <span>Save Changes</span>
                                    </button>
                                    <button class="btn btn-sm btn-secondary rounded-0 border-0" data-bs-dismiss="modal">
                                        <svg width="18" height="18">
                                            <use xlink:href="#Close" />
                                        </svg>
                                        <span>Close</span>
                                    </button>
                                    <button class="btn btn-sm btn-danger rounded-0 ms-3 d-none" id="DeleteAccount">
                                        <svg width="18" height="18">
                                            <use xlink:href="#AccDel" />
                                        </svg>
                                        <span>Delete Account</span>
                                    </button>
                                </div>
                                <script>
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row g-0 row-cols-1 row-cols-md-2">
            <div class="col-md-3 text-center order-2 order-md-1 profcover">
                <div class="profile h-100">
                    <!--spacer for profile picture and name and role-->
                    <div class="spacer d-none d-lg-block"></div>
                    <style>
                        .spacer {
                            height: 192px;
                            width: 100%;
                        }
                    </style>
                    <?php if ($_SESSION['ProfileImage'] == "Default-Profile.gif") {?>
                    <img src="../../Assets/Images/Default-Profile.gif" alt=""
                        class="img-fluid border rounded-circle mt-lg-3" width="128" height="128">
                    <?php } else {?>
                    <img src="../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']?>"
                        alt="" class="img-fluid border rounded-circle mt-lg-3" width="128" height="128">
                    <?php }?>

                    <h5 class="fw-bold mt-2 text-truncate">
                        <?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?>
                    </h5>
                    <p class="text-secondary text-uppercase fw-bold">
                        <?php $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
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
} ?>
                    </p>
                    <div class="d-flex justify-content-center">
                        <div class="hstack gap-2">
                            <button class="btn btn-sm btn-outline-secondary rounded-1 border-0 me-auto "
                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Back to Feed"
                                onclick="window.location.href='./Feed.php'">
                                <svg width="28" height="28">
                                    <use xlink:href="#Back" />
                                </svg>
                                <span class="d-sm-none">Back</span>
                            </button>
                            <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit Profile">
                                <button class="btn btn-sm btn-outline-primary rounded-1 border-0" data-bs-toggle="modal"
                                    data-bs-target="#ProfileModal">
                                    <svg width="28" height="28">
                                        <use xlink:href="#ManageAct" />
                                    </svg>
                                    <span class="d-sm-none">Edit Profile</span>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9 order-1 border-0 bg-transparent">
                <div class="converpic ratio ratio-16x9 border-0 bg-opacity-10 bg-blur-3">
                    <img src="" class="object-fit-cover rounded-5 rounded-top-0 d-none" alt="Cover Image"
                        id="coverImage">
                    <img src="../../Assets/Images/orgAssets/orgCover/Default-CSG-Cover.jpg"
                        class="object-fit-cover rounded-5 rounded-top-0" alt="Cover Image">
                </div>
            </div>
        </div>
    </div>
    <div class="container my-5">
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <div class="col-md-6">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Course & Section
                                            <span><?php echo $_SESSION['course_code'] ? $_SESSION['course_code'] : 'No Course'; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Organization
                                            <span>
                                                <?php
                                                    try {
                                                        $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
                                                        $stmt->bind_param("s", $_SESSION['UUID']);
                                                        $stmt->execute();
                                                        $userPositionResult = $stmt->get_result();
                                                        $stmt->close();

                                                        if ($userPositionResult->num_rows > 0) {
                                                            $userPosition = $userPositionResult->fetch_assoc();

                                                            $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
                                                            $stmt->bind_param("s", $userPosition['org_code']);
                                                            $stmt->execute();
                                                            $orgResult = $stmt->get_result();
                                                            $stmt->close();

                                                            if ($orgResult->num_rows > 0) {
                                                                $org = $orgResult->fetch_assoc();
                                                                echo $org['org_name'];
                                                            } else {
                                                                echo 'No Organization';
                                                            }
                                                        } else {
                                                            echo 'No Organization';
                                                        }
                                                    } catch (Exception $e) {
                                                        error_log("Database error: " . $e->getMessage());
                                                        echo 'Error retrieving organization';
                                                    }?>
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Position
                                            <span>
                                                <?php
    try {
        $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
        $stmt->bind_param("s", $_SESSION['UUID']);
        $stmt->execute();
        $userPositionResult = $stmt->get_result();
        $stmt->close();

        if ($userPositionResult->num_rows > 0) {
            $userPosition = $userPositionResult->fetch_assoc();
            if ($userPosition['org_position'] == 1) {
                echo ' President';
            } elseif ($userPosition['org_position'] == 2) {
                echo ' Vice President for Internal Affairs';
            } elseif ($userPosition['org_position'] == 3) {
                echo ' Vice President for External Affairs';
            } elseif ($userPosition['org_position'] == 4) {
                echo ' Secretary';
            } else {
                echo 'No Position';
            }
        } else {
            echo 'No Position';
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo 'Error retrieving position';
    }
?>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow h-100">
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Cvsu Email
                                            <span
                                                class="text-break text-truncate d-inline-block w-50"><?php echo isset($_SESSION['PrimaryEmail']) ? htmlspecialchars($_SESSION['PrimaryEmail']) : 'No email available'; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Contact Number
                                            <span><?php echo "0" . $_SESSION['contactNumber']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Student Number
                                            <span><?php echo $_SESSION['student_Number']; ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card border-0 bg-transparent">
                    <div class="card-body">
                        <div class="hstack gap-3">
                            <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create New Post"
                                class="me-md-auto <?php echo $_SESSION['role'] > 2 ? 'd-none' : ''; ?>">
                                <button class="btn btn-sm btn-outline-primary rounded-1 border-0" data-bs-toggle="modal"
                                    data-bs-target="#NewPostModal">
                                    <svg width="30" height="30">
                                        <use xlink:href="#CreatePost" />
                                    </svg>
                                    <span class="d-sm-none">New Post</span>
                                </button>
                            </span>
                            <button class="btn btn-sm btn-outline-secondary rounded-1 border-0 ms-auto"
                                onclick="window.location.href='../Functions/api/UserLogout.php'">
                                <!-- logout -->
                                <svg width="30" height="30">
                                    <use xlink:href="#Logout" />
                                </svg>
                                <span class="d-sm-none">Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div
        class="container <?php echo $_SESSION['role'] > 2 ? 'd-none' : ''; ?>">
        <div class="text-center mt-3">
            <h1>Your Posts</h1>
            <div class="hstack gap-3">
                <button
                    class="btn btn-sm btn-outline-secondary bg-transparent text-body fw-bold rounded-1 border-0 pbtn"
                    id="defPri">All</button>
                <button
                    class="btn btn-sm btn-outline-secondary bg-transparent text-body fw-bold rounded-1 border-0 pbtn"
                    id="LowPri">
                    <span class="hstack gap-2 text-success">
                        <svg width="24" height="24">
                            <use xlink:href="#LowPriority" />
                        </svg>
                        <span class="d-none d-sm-block">Low Priority</span>
                    </span>
                </button>
                <button
                    class="btn btn-sm btn-outline-secondary bg-transparent text-body fw-bold rounded-1 border-0 pbtn"
                    id="NormPri">
                    <span class="hstack gap-2 text-warning">
                        <svg width="24" height="24">
                            <use xlink:href="#NormPriority" />
                        </svg>
                        <span class="d-none d-sm-block">Normal Priority</span>
                    </span>
                </button>
                <button
                    class="btn btn-sm btn-outline-secondary bg-transparent text-body fw-bold rounded-1 border-0 pbtn"
                    id="HighPri">
                    <span class="hstack gap-2 text-danger">
                        <svg width="24" height="24">
                            <use xlink:href="#HighPriority" />
                        </svg>
                        <span class="d-none d-sm-block">High Priority</span>
                    </span>
                </button>
                <button
                    class="btn btn-sm btn-outline-secondary bg-transparent text-body fw-bold rounded-1 border-0 ms-auto pbtn"
                    id="delPost">
                    <span class="hstack gap-2 text-info">
                        <svg width="24" height="24">
                            <use xlink:href="#Trash" />
                        </svg>
                        <span class="d-none d-sm-block">Deleted Posts</span>
                    </span>
                </button>
            </div>
        </div>
        <div class="row mt-5 anncon" id="Announcements">
        </div>
    </div>
    <script src="../../Utilities/Third-party/AOS/js/aos.js"></script>
    <script>
        $(document).ready(function() {
            $("#changeProfimg").click(function() {
                $("#change-profile").click();
            });

            $("#change-profile").change(function() {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("#profileImage").attr("src", e.target.result);
                };
                reader.readAsDataURL(file);
            });
        });

        $('#SaveChanges').click(function() {
            var FirstName = $('#FirstName').val();
            var LastName = $('#LastName').val();
            var studentNumber = $('#studentNumber').val();
            var course = $('#course').val();
            var email = $('#email').val();
            var contactNumber = $('#contactNumber').val();
            var UUID =
                "<?php echo $_SESSION['UUID']; ?>";
            var profileImage = $('#change-profile').prop('files')[0];

            if (FirstName == '' || LastName == '' || studentNumber == '' || course == '' || email == '' ||
                contactNumber == '') {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                }).fire({
                    icon: 'error',
                    title: 'Please fill up all fields'
                });
                return;
            }

            if (!/^[A-Z]+-\d[A-Z]$/.test(course)) {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                }).fire({
                    icon: 'error',
                    title: 'Invalid course format',
                    text: 'Please follow the format: BSIT-4B'
                });
                return;
            }

            if (!/^09\d{9}$/.test(contactNumber)) {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                }).fire({
                    icon: 'error',
                    title: 'Invalid contact number',
                    text: 'Please follow the format: 09xxxxxxxxx'
                });
                return;
            }

            if (!/^\d{9}$/.test(studentNumber)) {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                }).fire({
                    icon: 'error',
                    title: 'Invalid student number',
                    text: 'Please follow the format: 2019-12345'
                });
                return;
            }

            if (!/^[a-zA-Z0-9._%+-]+@cvsu\.edu\.ph$/.test(email)) {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                }).fire({
                    icon: 'error',
                    title: 'Invalid email',
                    text: 'Please use your cvsu email'
                });
                return;
            }

            var formData = new FormData();
            formData.append('UUID', UUID);
            formData.append('FirstName', FirstName);
            formData.append('LastName', LastName);
            formData.append('studentNumber', studentNumber);
            formData.append('course', course);
            formData.append('email', email);
            formData.append('contactNumber', contactNumber);
            if (profileImage) {
                formData.append('profileImage', profileImage);
            }

            $.ajax({
                url: '../Functions/api/UpdateProfile.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        }).fire({
                            icon: 'success',
                            title: 'Profile updated successfully'
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                $('#ProfileModal').modal('hide');
                                $('#FirstName').val(FirstName);
                                $('#LastName').val(LastName);
                                $('#studentNumber').val(studentNumber);
                                $('#course').val(course);
                                $('#email').val(email);
                                $('#contactNumber').val(contactNumber);
                                $('#profileImage').attr('src',
                                    '../../Assets/Images/UserProfiles/' + response
                                    .profileImage);
                                $('#change-profile').val('').attr('src', '../../Assets/Images/UserProfiles/' +
                                    response.profileImage); 
                                    
                            }
                        });
                    } else {
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        }).fire({
                            icon: 'error',
                            title: response.message
                        });
                    }
                },
            });
        });

        const toolbarOptions = [
            [{
                'header': [1, 2, 3, 4, 5, 6, false]
            }],
            ['bold', 'italic', 'underline', 'strike'], // toggled buttons
            [{
                'header': 1
            }, {
                'header': 2
            }], // custom button values
            [{
                'size': ['small', false, 'large', 'huge']
            }],
            ['blockquote', 'code-block'],
            [{
                'list': 'ordered'
            }, {
                'list': 'bullet'
            }, {
                'list': 'check'
            }],
            [{
                'script': 'sub'
            }, {
                'script': 'super'
            }], // superscript/subscript
            [{
                'indent': '-1'
            }, {
                'indent': '+1'
            }], // outdent/indent
            [{
                'direction': 'rtl'
            }], // text direction
            [{
                'align': [false, 'center', 'right', 'justify']
            }],
        ];
        AOS.init();
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Create a Post'
        });

        // add rounded in ql-toolbar
        $('.ql-toolbar').addClass('border-0 mb-1');

        document.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }, true);

        document.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            swal.fire({
                title: 'Warning!',
                text: 'Dropping files is not allowed!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return false;
        }, true);

        let postDetails = '';
        quill.on('text-change', function() {
            // max of 500 characters
            if (quill.getLength() >= 500) {
                quill.deleteText(499, quill.getLength());
                $('#charCount').removeClass('text-muted').addClass('text-danger');
            } else {
                $('#charCount').removeClass('text-danger').addClass('text-muted');
            }

            $('#charCount').text(quill.getLength() + '/500');


            postDetails = quill.root.innerHTML;
            document.getElementById('post-details').value = postDetails;
        });
        document.getElementById('ClearPost').addEventListener('click', function() {
            quill.root.innerHTML = '';
            $('#post-details').val('');
        });
    </script>
</body>

</html>