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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Froala/css/froala_editor.min.css">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Froala/css/froala_style.min.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
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
                    <div class="col-md-8 z-2">
                        <div class="card glass-default bg-opacity-25 h-100">
                            <div class="card-body">
                                <h4 class="text-center fw-bold text-uppercase">Activity Proposal</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="AdminName" class="form-label">Campus Admin</label>
                                            <input type="text" class="form-control" id="AdminName">
                                            <div class="form-text">Name of the Campus Admin</div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <textarea id="LetterBody" class="form-control" placeholder="Type your letter here"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card glass-default bg-opacity-25 h-100">
                            <div class="card-body">
                                <h5 class="text-center fw-bold text-uppercase">Previous Documents</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/froala_editor.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/plugins/tables.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/plugins/urls.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/plugins/lists.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/plugins/font_size.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/plugins/block_styles.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/plugins/char_counter.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/plugins/entities.min.js"></script>
    <script src="../../../../Utilities/Third-party/Froala/js/plugins/fullscreen.min.js"></script>
    <script>
      $(function(){
        $('#LetterBody').editable({
            inlineMode: false,
            alwaysBlank: true,
            height: 300,
            
            buttons: $.merge(['fullscreen'], $.Editable.DEFAULTS.buttons),
            maxCharacters: 10000,
        }).on('editable.maxCharNumberExceeded', function (e, editor) {
            Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 1000,
                timerProgressBar: false,
            }).fire({
                icon: 'error',
                title: 'Maximum Character Limit Reached'
            });
        });
      });
  </script>
</body>

</html>