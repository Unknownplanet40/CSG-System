<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['UUID'])) {
    header('Location: ./Feed.php'); // if the user is already logged in, redirect to the homepage
    echo "<script>localStorage.setItem('currentUser', '" . Hash('sha256', $_SESSION['student_Number']) . "');" . "</script>";
    echo "<script>localStorage.setItem('activesession', 'An account is already logged into this browser. For security reasons, please log out of the current session or use a different browser, incognito mode, or guest mode to sign in with another account.');</script>";
} else {
    echo "<script>localStorage.removeItem('currentUser');</script>";
    echo "<script>localStorage.removeItem('activesession');</script>";
}

if (isset($_GET['autoLogin']) && $_GET['autoLogin'] == 'true') {
    echo "<script>localStorage.setItem('studentnum', '" . $_GET['studentnum'] . "');</script>";
    echo "<script>localStorage.setItem('password', '" . $_GET['password'] . "');</script>";
} else {
    echo "<script>localStorage.removeItem('studentnum');</script>";
    echo "<script>localStorage.removeItem('password');</script>";
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../Utilities/Third-party/Sweetalert2/css/sweetalert2.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/MNavbarStyle.css">
    <link rel="stylesheet" href="../../Utilities/Stylesheets/Accessstyle.css">
    <link rel="shortcut icon" href="../../Assets/Icons/PWA-Icon/MainIcon.png" type="image/x-icon">
    <script src="../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../Utilities/Scripts/animate-browser-title.js"></script>
    <script defer type="module" src="../../Utilities/Scripts/AccessScript.js"></script>
    <script>
        function HomeRedirect() {
            window.location.href = './Homepage.php';
        }

        function RedirectToLogin() {
            localStorage.setItem('current-form', 'Login');
        }

        function RedirectToRegister() {
            localStorage.setItem('current-form', 'Register');
        }

        function RedirectToForgot() {
            localStorage.setItem('current-form', 'Forgot');
        }
    </script>
    <title>Welcome!</title>
</head>

<?php include_once '../../Assets/Icons/Icon_Assets.php'; ?>

<body>
    <?php
    if (isset($_GET['error'])) {
        if ($_GET['error'] == '001') {
            echo "<script>localStorage.setItem('error', 'Cannot access the page without logging in');</script>";
            unset($_GET['error']);
        } elseif ($_GET['error'] == '002') {
            echo "<script>localStorage.setItem('error', 'Session expired. Please log in again');</script>";
            unset($_GET['error']);
        } elseif ($_GET['error'] == '003') {
            echo "<script>localStorage.setItem('error', 'Please insure that your the only one logged in on this account');</script>";
            unset($_GET['error']);
        } elseif ($_GET['error'] == '004') {
            echo "<script>localStorage.setItem('error', 'You have Encountered an E-K404 Error Code. Please Contact the Administrator');</script>";
            unset($_GET['error']);
        } else {
            echo "<script>localStorage.removeItem('error');</script>";
        }
    } else {
        echo "<script>localStorage.removeItem('error');</script>";
    }
?>
    <?php //include_once "../Components/BGanimation.php";?>
    <div class="loader-container" id="loader">
        <div class="row g-4">
            <!-- version 1 -->
            <div class="col-md-12 d-flex justify-content-center">
                <span class="loader-v1-default"></span>
            </div>
            <!-- version 2 -->
            <div class="col-md-12 d-flex justify-content-center d-none">
                <span class="loader-v2"></span>
            </div>
            <!-- version 3 -->
            <div class="col-md-12 d-flex justify-content-center d-none">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <!-- Loading Label -->
            <div class="col-md-12 d-flex justify-content-center d-none" id="noForm">
                <h5 class="text-muted text-center" id="noForm-label">No form selected</h5>
            </div>
        </div>
    </div>
    <div class="container-fluid con-H d-none" id="main-content">
        <div class="row d-flex justify-content-center row-cols-1 row-cols-md-2 g-2">
            <div class="col-md-3 d-none d-lg-block">
                <ul class="list-group list-group-flush sticky-top pt-5" style="cursor: pointer;">
                    <li class="list-group-item rounded selected list-hover border-0 d-inline-block text-truncate"
                        style="max-width: 220px;" onclick="RedirectToLogin()" id="side-login">
                        <svg width="16" height="16" class="me-3">
                            <use xlink:href="#Login" />
                        </svg>
                        Login
                    </li>
                    <li class="list-group-item rounded list-hover border-0 d-inline-block text-truncate"
                        style="max-width: 220px;" onclick="RedirectToRegister()" id="side-register">
                        <svg width="16" height="16" class="me-3">
                            <use xlink:href="#Register" />
                        </svg>
                        Register
                    </li>
                    <li class="list-group-item rounded list-hover border-0 d-inline-block text-truncate btm-line"
                        style="max-width: 220px;" id="side-forgot" onclick="RedirectToForgot()">
                        <svg width="16" height="16" class="me-3">
                            <use xlink:href="#Lock" />
                        </svg>
                        Forgot Password
                    </li>
                    <hr>
                    <li class="list-group-item rounded list-hover border-0 d-inline-block text-truncate"
                        onclick="HomeRedirect()" style="max-width: 220px;">
                        <svg width="16" height="16" class="me-3">
                            <use xlink:href="#Home" />
                        </svg>
                        Homepage
                    </li>
                    <li class="list-group-item rounded list-hover border-0 d-inline-block text-truncate d-none"
                        style="max-width: 220px;">
                        <svg width="16" height="16" class="me-3">
                            <use xlink:href="#Help" />
                        </svg>
                        Help
                    </li>
                </ul>
            </div>
            <div class="col-md-9 pt-5">
                <div>
                    <h3 class="fw-bold my-3">
                        <div class="hstack gap-3">
                            <span><img src="../../Assets/Icons/PWA-Icon/Icon-x96.jpeg" alt="csg logo" width="48"></span>
                            <span>Central Student Government</span>
                        </div>
                    </h3>
                </div>
                <div class="row row-cols-1 row-cols-md-2 g-2">
                    <!-- Login Form Start -->
                    <div class="col-md-6 mb-3 d-none" id="Login-container">
                        <h5 class="text-start fw-bold my-5">
                            <svg width="18" height="18" class="mx-2">
                                <use xlink:href="#Login" />
                            </svg>
                            Login your account
                        </h5>
                        <div class="d-flex justify-content-center">
                            <div class="container">
                                <div class="mb-3">
                                    <label for="Login-stdnum" class="form-label fs-6">Student Number</label>
                                    <input type="text" class="form-control rounded-0 mx-1" id="Login-stdnum"
                                        maxlength="9" aria-describedby="stdnumHelp Login-btn">
                                    <div id="stdnumHelp" class="invalid-feedback">For validation</div>
                                </div>
                                <div class="mb-5">
                                    <label for="Login-password" class="form-label fs-6">Password</label>
                                    <div class="eyeforaneye pe-5">
                                        <input type="password" class="form-control rounded-0 mx-1" id="Login-password"
                                            aria-describedby="passwordHelp Login-btn">
                                        <div id="passwordHelp" class="invalid-feedback">For validation</div>
                                        <span class="pass-eye py-2 px-3 rounded-0" id="eyecon">
                                            <svg width="22" height="22">
                                                <use xlink:href="#PassShow" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-secondary gradient-btn btn-sm rounded-0 py-2"
                                        id="Login-btn"><strong role="status" id="Login-btn-label">Login</strong>
                                        <div class="d-none d-flex justify-content-center" id="Login-btn-loader">
                                            <strong role="status">Loading...</strong>
                                            <div class="spinner-border spinner-border-sm ms-auto mt-1"
                                                aria-hidden="true"></div>
                                        </div>
                                    </button>
                                    <small class="text-muted text-center d-none" id="log-btn-lbl"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Login Form End -->
                    <!-- Register Form Start -->
                    <div class="col-md-6 mb-3" id="Register-container">
                        <h5 class="text-start fw-bold my-5">
                            <svg width="18" height="18" class="mx-2">
                                <use xlink:href="#Register" />
                            </svg>
                            Register your account
                        </h5>
                        <div class="d-flex justify-content-center">
                            <div class="container">
                                <div class="mb-3">
                                    <label for="Reg-stdnum" class="form-label fs-6">Student Number</label>
                                    <input type="text" class="form-control rounded-0 mx-1" id="Reg-stdnum"
                                        aria-describedby="stdnumHelp-Reg Reg-btn">
                                    <div id="stdnumHelp-Reg" class="invalid-feedback">For validation</div>
                                </div>
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="Reg-firstname" class="form-label fs-6">First Name</label>
                                            <input type="text" class="form-control rounded-0 mx-1" id="Reg-firstname"
                                                aria-describedby="firstnameHelp-Reg Reg-btn">
                                            <div id="firstnameHelp-Reg" class="invalid-feedback">For validation</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Reg-lastname" class="form-label fs-6">Last Name</label>
                                            <input type="text" class="form-control rounded-0 mx-1" id="Reg-lastname"
                                                aria-describedby="lastnameHelp-Reg Reg-btn">
                                            <div id="lastnameHelp-Reg" class="invalid-feedback">For validation</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="Reg-course" class="form-label fs-6">Course</label>
                                            <select class="form-select rounded-0 mx-1" id="Reg-course"
                                                aria-describedby="courseHelp-Reg Reg-btn">
                                            </select>
                                            <div id="courseHelp-Reg" class="invalid-feedback">For validation</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="Reg-year" class="form-label fs-6">Year Level</label>
                                            <select class="form-select rounded-0 mx-1" id="Reg-year"
                                                aria-describedby="yearHelp-Reg Reg-btn">
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="Reg-section" class="form-label fs-6">Section</label>
                                            <select class="form-select rounded-0 mx-1" id="Reg-section"
                                                aria-describedby="sectionHelp-Reg Reg-btn">
                                            </select>
                                        </div>

                                        <script>
                                            $(document).ready(function() {
                                                var course = $("#Reg-course");

                                                $("#Reg-year").empty().prop("disabled", true);
                                                $("#Reg-section").empty().prop("disabled", true);

                                                $.ajax({
                                                    url: '../Functions/api/getAcadData.php',
                                                    type: 'POST',
                                                    data: {
                                                        CourseID: null,
                                                        Action: "Get-Course"
                                                    },

                                                    success: function(res) {
                                                        if (res.status === "success") {
                                                            course.empty();
                                                            course.append(
                                                                `<option selected hidden>Choose...</option>`
                                                            );
                                                            res.data.forEach((courses) => {
                                                                course.append(
                                                                    `<option value="${courses.ShortName}">${courses.CourseName}</option>`
                                                                );
                                                            });
                                                        } else {
                                                            console.error(
                                                                'Failed to fetch courses:', res
                                                                .message || 'Unknown error');
                                                        }
                                                    },
                                                    error: function(xhr, status, error) {
                                                        console.error('AJAX Error:', error);
                                                    }
                                                });

                                                $("#Reg-course").change(function() {
                                                    var year = $("#Reg-year");
                                                    year.prop("disabled", false);
                                                    $("#Reg-section").empty().prop("disabled", true);
                                                    $.ajax({
                                                        url: '../Functions/api/getAcadData.php',
                                                        type: 'POST',
                                                        data: {
                                                            CourseID: $("#Reg-course").val(),
                                                            Action: "Get-Year"
                                                        },

                                                        success: function(res) {
                                                            if (res.status === "success") {
                                                                year.empty();
                                                                year.append(
                                                                    `<option selected hidden>Choose...</option>`
                                                                );
                                                                res.data.forEach((
                                                                    courses) => {
                                                                    year.append(
                                                                        `<option value="${courses.Year}">${courses.CourseName}</option>`
                                                                    );
                                                                });
                                                            } else {
                                                                console.error(
                                                                    'Failed to fetch courses:',
                                                                    res.message ||
                                                                    'Unknown error');
                                                            }
                                                        },
                                                        error: function(xhr, status, error) {
                                                            console.error('AJAX Error:',
                                                                error);
                                                        }
                                                    });
                                                });

                                                $("#Reg-year").change(function() {
                                                    var section = $("#Reg-section");
                                                    var yearlvl = $("#Reg-year").val();
                                                    section.prop("disabled", false);
                                                    $.ajax({
                                                        url: '../Functions/api/getAcadData.php',
                                                        type: 'POST',
                                                        data: {
                                                            CourseID: $("#Reg-course").val(),
                                                            Action: "Get-Section",
                                                            YearLevel: yearlvl
                                                        },

                                                        success: function(res) {
                                                            if (res.status === "success") {
                                                                section.empty();
                                                                section.append(
                                                                    `<option selected hidden>Choose...</option>`
                                                                );
                                                                res.data.forEach((
                                                                    courses) => {
                                                                    section.append(
                                                                        `<option value="${courses.CourseID}">${courses.Section}</option>`
                                                                    );
                                                                });
                                                            } else {
                                                                console.error(
                                                                    'Failed to fetch courses:',
                                                                    res.message ||
                                                                    'Unknown error');
                                                            }
                                                        },
                                                        error: function(xhr, status, error) {
                                                            console.error('AJAX Error:',
                                                                error);
                                                        }
                                                    });
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="Reg-email" class="form-label fs-6">Primary Email</label>
                                    <input type="text" class="form-control rounded-0 mx-1" id="Reg-email"
                                        aria-describedby="emailHelp-Reg Reg-btn">
                                    <div id="emailHelp-Reg" class="invalid-feedback">For validation</div>
                                </div>
                                <div class="mb-3">
                                    <label for="Reg-phone" class="form-label fs-6">Phone Number</label>
                                    <input type="text" class="form-control rounded-0 mx-1" id="Reg-phone"
                                        aria-describedby="phoneHelp-Reg Reg-btn">
                                    <div id="phoneHelp-Reg" class="invalid-feedback">For validation</div>
                                </div>
                                <div class="mb-5">
                                    <label for="Reg-password" class="form-label fs-6">Password</label>
                                    <div class="eyeforaneye pe-5">
                                        <input type="password" class="form-control rounded-0 mx-1" id="Reg-password"
                                            aria-describedby="passwordHelp-Reg Reg-btn">
                                        <div id="passwordHelp-Reg" class="invalid-feedback">For validation</div>
                                        <span class="pass-eye py-2 px-3 rounded-0" id="eyecon-input2">
                                            <svg width="22" height="22">
                                                <use xlink:href="#PassShow" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-grid gap-2">
                                    <small>Please provide the required information to register</small>
                                    <button type="button" class="btn btn-success btn-sm rounded-0 py-2" tabindex="6"
                                        id="Reg-btn">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Register Form End -->
                    <!-- Forgot Password Form Start -->
                    <!-- Step 1 -->
                    <div class="col-md-6 mb-3 d-none" id="Forgot-Step1-container">
                        <h5 class="text-start fw-bold my-5">
                            <svg width="18" height="18" class="mx-2">
                                <use xlink:href="#Lock" />
                            </svg>
                            Forgot Password Step 1
                        </h5>
                        <div class="d-flex justify-content-center">
                            <div class="container">
                                <div class="mb-3">
                                    <label for="fps1-studnum" class="form-label fs-6">Student Number</label>
                                    <input type="text" class="form-control rounded-0 mx-1" id="fps1-studnum"
                                        aria-describedby="studnumHelp FpS1-btn" maxlength="9" data-isvalid="false">
                                    <div id="studnumHelp" class="invalid-feedback">For validation</div>
                                </div>
                                <div class="mb-5">
                                    <label for="fps1-email" class="form-label fs-6">Primary Email</label>
                                    <input type="text" class="form-control rounded-0 mx-1" id="fps1-email"
                                        aria-describedby="emailHelp FpS1-btn" data-isvalid="false">
                                    <div id="emailHelp" class="invalid-feedback">For validation</div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-sm rounded-0 py-2" id="FpS1-btn">
                                        <strong role="status" id="FpS1-btn-label">Next</strong>
                                        <div class="d-none d-flex justify-content-center" id="FpS1-btn-loader">
                                            <strong role="status">Loading...</strong>
                                            <div class="spinner-border spinner-border-sm ms-auto mt-1"
                                                aria-hidden="true"></div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="col-md-6 mb-3 d-none" id="Forgot-Step2-container">
                        <h5 class="text-start fw-bold my-5">
                            <svg width="18" height="18" class="mx-2">
                                <use xlink:href="#Lock" />
                            </svg>
                            Forgot Password Step 2
                        </h5>
                        <div class="d-flex justify-content-center">
                            <div class="container">
                                <div class="mb-3">
                                    <label for="ResetToken" class="form-label fs-6">Verification Code (<small
                                            class="text-danger" id="otpTimer"></small>)</label>
                                    <div class="hstack gap-3">
                                        <input type="text" class="form-control rounded-0 mx-1" id="ResetToken"
                                            aria-describedby="otpHelp FpS2-btn">
                                        <div id="otpHelp" class="invalid-feedback">For validation</div>
                                    </div>
                                    <button class="btn btn-link btn-sm rounded-0" id="resendOTP" disabled>Resend
                                        Code</button>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-sm rounded-0 py-2" id="FpS2-btn">
                                        <strong role="status" id="FpS2-btn-label">Verify Token and Proceed</strong>
                                        <div class="d-none d-flex justify-content-center" id="FpS2-btn-loader">
                                            <strong role="status">Loading...</strong>
                                            <div class="spinner-border spinner-border-sm ms-auto mt-1"
                                                aria-hidden="true"></div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="col-md-6 mb-3 d-none" id="Forgot-Step3-container">
                        <h5 class="text-start fw-bold my-5">
                            <svg width="18" height="18" class="mx-2">
                                <use xlink:href="#Lock" />
                            </svg>
                            Forgot Password Final Step
                        </h5>
                        <div class="d-flex justify-content-center">
                            <div class="container">
                                <div class="mb-3">
                                    <label for="fpS3-password" class="form-label fs-6">Password</label>
                                    <div class="eyeforaneye pe-5">
                                        <input type="password" class="form-control rounded-0 mx-1" id="fpS3-password"
                                            aria-describedby="passwordHelp1 FpS3-btn">
                                        <span class="pass-eye py-2 px-3 rounded-0" id="eyecon-input3">
                                            <svg width="22" height="22">
                                                <use xlink:href="#PassShow" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-5">
                                    <label for="fpS3-cpassword" class="form-label fs-6">Confirm Password</label>
                                    <div class="eyeforaneye pe-5">
                                        <input type="password" class="form-control rounded-0 mx-1" id="fpS3-cpassword"
                                            aria-describedby="cpasswordHelp FpS3-btn">
                                        <span class="pass-eye py-2 px-3 rounded-0" id="eyecon-input4">
                                            <svg width="22" height="22">
                                                <use xlink:href="#PassShow" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-sm rounded-0 py-2" id="fpS3-btn">
                                        <strong role="status" id="FpS3-btn-label">Change Password</strong>
                                        <div class="d-none d-flex justify-content-center" id="FpS3-btn-loader">
                                            <strong role="status">Loading...</strong>
                                            <div class="spinner-border spinner-border-sm ms-auto mt-1"
                                                aria-hidden="true"></div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Forgot Password Form End -->
                    <div class=" col-md-6">
                        <h5 class="text-start fw-bold my-5">
                            <svg width="18" height="18" class="mx-2">
                                <use xlink:href="#Question" />
                            </svg>
                            Guide
                        </h5>
                        <div>
                            <ul>
                                <li class="text-start mb-3"><strong>Creating Account</strong>
                                    <ul>
                                        <li><small>Provide the requested information to register</small>
                                        </li>
                                        <li><small>Make sure to enter working email address</small></li>
                                        <li><small>Enter the information in the required format</small>
                                        </li>
                                        <li><small>Only organization members are allowed to register</small>
                                    </ul>
                                </li>
                                <li class="text-start"><strong>Trouble logging into your
                                        account?</strong>
                                    <ul>
                                        <li><small>Account must be activated</small></li>
                                        <li><small>Make sure that no account is currently logged in on this
                                                browser</small>
                                            <span class="text-secondary" style="font-size: 0.8rem;">(Use guest mode or a
                                                different browser)</span>
                                        </li>
                                        <li><small>Make sure to enter valid student number and
                                                password</small></li>
                                        <li><small>Information must match our record</small></li>
                                        <li><small>Forgot your password? <a href="#" class="text-decoration-none"
                                                    onclick="RedirectToForgot()">Click here to recover.</small>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once '../Components/mobileNavbar.php'; ?>
</body>

</html>