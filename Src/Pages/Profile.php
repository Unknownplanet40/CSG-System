<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    require_once '../Database/Config.php';
}

if (!isset($_SESSION['UUID'])) {
    header('Location: ../Pages/Accesspage.php?error=001');
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

$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../Utilities/Third-party/AOS/css/aos.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/NavbarStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/ProfileStyle.css">
    <link rel="shortcut icon" href="../../Assets/Icons/PWA-Icon/MainIcon.png" type="image/x-icon">
    <script defer src="../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="../../Utilities/Scripts/animate-browser-title.js"></script>
    <script type="module" src="../../Utilities/Scripts/ProfileScript.js"></script>
    <title>Profile</title>
</head>

<?php include_once '../../Assets/Icons/Icon_Assets.php'; ?>

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
                                                            <img src="../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']?>"
                                                                alt="" width="42" height="42" class="rounded-circle">
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
                                            <div class="form-floating">
                                                <textarea class="form-control border-0" placeholder="Create a Post"
                                                    style="white-space: pre-wrap;" id="post-details"
                                                    style="height: 100px" maxlength="500" required rows="10"></textarea>
                                                <label for="post-details">What's on your mind?</label>
                                            </div>

                                            <hr>
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
                                                    <small>
                                                        <cite>
                                                            <small class="text-muted" id="charCount">0/500</small>
                                                            <input id="USER_UUID" type="hidden"
                                                                value="<?php echo $_SESSION['UUID']; ?>">
                                                        </cite>
                                                    </small>
                                                    <button class="btn btn-sm btn-outline-secondary rounded-1 my-1 ms-4"
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

    <div class="modal fade" id="ProfileModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content glass-default border-0 bg-opacity-25 rounded-1">
                <div class="modal-body">
                    <div class="container">
                        <div class="row g-2 row-cols-1 row-cols-md-2">
                            <div class="col-md-4 d-flex justify-content-center align-items-center">
                                <img src="../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']?>"
                                    class="object-fit-cover border rounded-circle img-fluid" style="height: 192px;"
                                    alt="<?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?> Profile Image">
                            </div>
                            <div class="col-md-8">
                                <div class="ratio ratio-16x9 rounded-3 bg-body bg-opacity-10 bg-blur-3">
                                    <img src="../../Assets/Images/Default-Cover.png"
                                        class="object-fit-contain border-0 rounded-3 p-0">
                                </div>
                            </div>
                        </div>
                        <div class="hstack gap-1 mb-2">
                            <button class="btn btn-sm text-light fw-bold rounded-1 border-0 me-auto ms-5">
                                <svg width="24" height="24">
                                    <use xlink:href="#changeProf" />
                                </svg>
                                <span>Update Profile</span>
                                <input type="file" class="d-none" id="change-profile" accept="image/*">
                            </button>
                            <button class="btn btn-sm text-light fw-bold rounded-1 border-0 me-5">
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
                                        Name
                                        <input type="text"
                                            class="form-control border-0 rounded-0 text-end w-50 bg-transparent"
                                            value="<?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?>">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Role
                                        <?php
                                            if ($_SESSION['role'] == 1) {
                                                $role = 'Administator';
                                            } elseif ($_SESSION['role'] == 2) {
                                                $role = "CSG Officer";
                                            } else {
                                                $role = 'Officer';
                                            } ?>
                                        <select class="form-select border-0 rounded-0 text-end w-50 bg-transparent"
                                            aria-label="Default select example">
                                            <option selected hidden
                                                value="<?php echo $_SESSION['role']; ?>">
                                                <?php echo $role; ?>
                                            <option class="text-center" value="1">ADMINISTRATOR</option>
                                            <option class="text-center" value="2">CSG Officer</option>
                                            <option class="text-center" value="3">Officer</option>
                                        </select>
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Organization
                                        <select class="form-select border-0 rounded-0 text-end w-50 bg-transparent">
                                            <?php if ($_SESSION['role'] != 1) {
                                                $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
                                                $stmt->bind_param("s", $_SESSION['UUID']);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                $stmt->close();

                                                if ($result->num_rows > 0) {
                                                    $org = $result->fetch_assoc();
                                                }

                                                $stmt = $conn->prepare("SELECT * FROM organizations");
                                                $stmt->execute();
                                                $orgs = $stmt->get_result();
                                                $stmt->close();

                                                if ($orgs->num_rows > 0) {
                                                    while ($organization = $orgs->fetch_assoc()) {
                                                        if (!empty($org) && $org['org_code'] == $organization['org_code']) {
                                                            echo '<option selected value="' . $organization['org_code'] . '">' . $organization['org_name'] . '</option>';
                                                        } else {
                                                            echo '<option value="' . $organization['org_code'] . '">' . $organization['org_name'] . '</option>';
                                                        }
                                                    }
                                                } else {
                                                    echo '<option selected hidden>No Organization</option>';
                                                    echo '<option disabled class="text-center text-secondary">No Organization Available</option>';
                                                }
                                            } else {
                                                echo '<option selected hidden>No Organization</option>';
                                                echo '<option disabled class="text-center text-secondary">You are an Administrator</option>';
                                            } ?>
                                        </select>
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Student Number
                                        <input type="text" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="<?php echo $_SESSION['student_Number']; ?>">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Email
                                        <input type="email" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="<?php echo $_SESSION['PrimaryEmail']; ?>">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Contact Number
                                        <input type="text" class="form-control border-0 text-end w-50 bg-transparent"
                                            value="" placeholder="09xxxxxxxxx">
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-bottom-0 bg-body bg-opacity-10 bg-blur-3">
                                        Address
                                        <textarea class="form-control border-0 text-end w-50 bg-transparent"
                                            placeholder="1234 Main St" rows="3"></textarea>
                                    </li>
                                </ul>
                                <div class="hstack gap-1 mt-3">
                                    <button class="btn btn-sm btn-primary rounded-0 border-0 me-auto">
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
                                    <button class="btn btn-sm btn-danger rounded-0 ms-3">
                                        <svg width="18" height="18">
                                            <use xlink:href="#AccDel" />
                                        </svg>
                                        <span>Delete Account</span>
                                    </button>
                                </div>
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
                    <img src="../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']?>"
                        alt="" class="img-fluid border rounded-circle mt-lg-3" width="128" height="128">
                    <h5 class="fw-bold mt-2 text-truncate">
                        <?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?>
                    </h5>
                    <p class="text-secondary text-uppercase fw-bold">
                        <?php
                            if ($_SESSION['role'] == 1) {
                                $role = 'Administrator';
                            } elseif ($_SESSION['role'] == 2) {
                                $role = "CSG Officer";
                            } else {
                                $role = 'Officer';
                            }
echo $role;
?>
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
                    <img src="" class="object-fit-cover rounded-5 rounded-top-0" alt="Cover Image" id="coverImage">
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
                                            A list item
                                            <span>asdsdsd</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            A list item
                                            <span>asdsdsd</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            A list item
                                            <span>asdsdsd</span>
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
                                            A list item
                                            <span>asdsdsd</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            A list item
                                            <span>asdsdsd</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            A list item
                                            <span>asdsdsd</span>
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
                        <div class="hstack gap-3 justify-content-center">
                            <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="Create New Post"
                                class="me-md-auto">
                                <button class="btn btn-sm btn-outline-primary rounded-1 border-0" data-bs-toggle="modal"
                                    data-bs-target="#NewPostModal">
                                    <svg width="30" height="30">
                                        <use xlink:href="#CreatePost" />
                                    </svg>
                                    <span class="d-sm-none">New Post</span>
                                </button>
                            </span>
                            <button class="btn btn-sm btn-outline-primary rounded-1">Button 1</button>
                            <button class="btn btn-sm btn-outline-secondary rounded-1 border-0"
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
    <div class="container">
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
        AOS.init();
    </script>
</body>

</html>