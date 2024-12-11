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
        <div class="BS-Main mt-5">
            <div class="container">
                <div class="row m-5 pt-5 g-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control form-control-sm rounded-0" id="UUID_Input"
                                placeholder="Unique User Identifier" readonly>
                            <label for="UUID_Input">Unique User Identifier</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control form-control-sm rounded-0" id="UUID_CvSU_Mail"
                                placeholder="CvSU Email" required>
                            <label for="UUID_CvSU_Mail">CvSU Email</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select form-select-sm rounded-0" id="Role_Select">
                                <option selected hidden disabled value="null">Select Role</option>
                                <?php if ($_SESSION['role'] == 1) { ?>
                                <option value="1">Administrator</option>
                                <option value="2">CSG Officer</option>
                                <option value="3">Officer (Sub-Organization)</option>
                                <?php } elseif ($_SESSION['role'] == 2) { ?>
                                <option value="2">CSG Officer</option>
                                <option selected value="3">Officer (Sub-Organization)</option>
                                <?php } else { ?>
                                <option selected value="3">Officer (Sub-Organization)</option>
                                <?php } ?>
                            </select>
                            <label for="Role_Select">User Role</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select form-select-sm rounded-0" id="Pos_Select" required>
                            $('#Pos_Select').html('<option selected disabled value="null">Select your Role first</option>');
                            </select>
                            <label for="Pos_Select">User Position</label>
                        </div>
                    </div>
                    <script>
                        $('#Role_Select').change(function() {
                            var role = $('#Role_Select').val();
                            if (role == 1) {
                                $('#Pos_Select').prop('disabled', true);
                            } else {
                                $('#Pos_Select').prop('disabled', false);
                            }
                            
                            var currentPos = "<?php echo $_SESSION['org_position'] ?? ''; ?>";

                            if (role == 1) {
                                $('#Pos_Select').html('<option hidden disabled value="null">Administrator</option>');
                            } else if (role == 2) {
                                if (currentPos == "") {
                                    $('#Pos_Select').html('<option selected hidden disabled value="null">Select CSG Position</option>');
                                    $('#Pos_Select').append('<option value="1">President</option>');
                                    $('#Pos_Select').append('<option value="2">Vice President for Internal Affairs</option>');
                                    $('#Pos_Select').append('<option value="3">Vice President for External Affairs</option>');
                                    $('#Pos_Select').append('<option value="4">Secretary</option>');
                                } else {
                                    if (currentPos == "1") {
                                    $('#Pos_Select').html('<option disabled value="1">President</option>');
                                    $('#Pos_Select').append('<option value="2">Vice President for Internal Affairs</option>');
                                    $('#Pos_Select').append('<option value="3">Vice President for External Affairs</option>');
                                    $('#Pos_Select').append('<option value="4">Secretary</option>');
                                } else if (currentPos == "2") {
                                    $('#Pos_Select').html('<option value="1">President</option>');
                                    $('#Pos_Select').append('<option disabled value="2">Vice President for Internal Affairs</option>');
                                    $('#Pos_Select').append('<option value="3">Vice President for External Affairs</option>');
                                    $('#Pos_Select').append('<option value="4">Secretary</option>');
                                } else if (currentPos == "3") {
                                    $('#Pos_Select').html('<option value="1">President</option>');
                                    $('#Pos_Select').append('<option value="2">Vice President for Internal Affairs</option>');
                                    $('#Pos_Select').append('<option disabled value="3">Vice President for External Affairs</option>');
                                    $('#Pos_Select').append('<option value="4">Secretary</option>');
                                } else {
                                    $('#Pos_Select').html('<option value="1">President</option>');
                                    $('#Pos_Select').append('<option value="2">Vice President for Internal Affairs</option>');
                                    $('#Pos_Select').append('<option value="3">Vice President for External Affairs</option>');
                                    $('#Pos_Select').append('<option disabled value="4">Secretary</option>');
                                }
                                } 
                            } else {
                                $('#Pos_Select').html('<option selected hidden disabled value="null">Select Sub-Org Position</option>');
                                $('#Pos_Select').append('<option value="1">President</option>');
                                $('#Pos_Select').append('<option value="2">Vice President for Internal Affairs</option>');
                                $('#Pos_Select').append('<option value="3">Vice President for External Affairs</option>');
                                $('#Pos_Select').append('<option value="4">Secretary</option>');
                            }
                        });
                    </script>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control form-control-sm rounded-0" id="TempMail_Input"
                                placeholder="Temporary Email" required>
                            <label for="TempMail_Input">Temporary Email</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control form-control-sm rounded-0" id="TempPass_Input"
                                placeholder="Temporary Password" required>
                            <label for="TempPass_Input">Temporary Password</label>
                        </div>
                    </div>
                    <div class="col-md-6 hstack gap-2">
                        <button class="btn btn-success rounded-0 w-50" id="CreateAccount_Btn">Create
                            Account</button>
                        <div class="hstack gap-2 w-50">
                            <button class="btn btn-success rounded-0 ms-auto" id="GenTemp_Btn" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Generate Temporary Credentials">Generate</button>
                            <button class="btn btn-secondary rounded-0 d-none" id="print_Btn" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Print Temporary Credentials (Expire within 5 minutes)"
                                disabled>Print</button>
                        </div>
                    </div>
                    <div class="col-md-6 py-2">
                        <p class="text-danger" id="Error_Msg"></p>
                        <script>
                            setTimeout(function() {
                                $('#Error_Msg').text('');
                            }, 3000);
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function uuidv4() {
            return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function(c) {
                var r = (Math.random() * 16) | 0,
                    v = c == "x" ? r : (r & 0x3) | 0x8;
                return v.toString(16);
            });
        }

        var userTempMail = "";
        var userTempPass = "";

        $(document).ready(function() {
            $("#GenTemp_Btn").click(function() {
                var uuid = uuidv4();
                var TempMail = $("#TempMail_Input").val();
                var TempPass = $("#TempPass_Input").val();

                var TempMail =
                    Math.random().toString(36).substring(2, 10) + "@cvsu.temp.com";
                var TempPass = Math.random().toString(36).substring(2, 10) + "Aa1!";

                $("#UUID_Input").val(uuid);
                $("#TempMail_Input").val(TempMail);
                $("#TempPass_Input").val(TempPass);

                $("#print_Btn").prop("disabled", false);
            });

            $("#print_Btn").click(function() {
                if (userTempMail == "" || userTempPass == "") {
                    $("#Error_Msg").text("Please generate temporary credentials first");
                    return;
                } else if (userTempMail == "Expired" || userTempPass == "Expired") {
                    $("#Error_Msg").text("Temporary account has expired. Please generate a new one.");
                    return;
                }

                window.location.href =
                    `../../../Functions/TempAccount_Gen.php?Email=${userTempMail}&Password=${userTempPass}`;
            });

            $("#CreateAccount_Btn").click(function() {
                var UUID = $("#UUID_Input").val();
                var CvSU_Mail = $("#UUID_CvSU_Mail").val();
                var Role = $("#Role_Select").val();
                var Pos = $("#Pos_Select").val();
                var TempMail = $("#TempMail_Input").val();
                var TempPass = $("#TempPass_Input").val();


                if (UUID == "" || CvSU_Mail == "" || TempMail == "" || TempPass == "") {
                    $("#Error_Msg").text("Please fill up all fields");
                    return;
                }

                if (Role == "null" || Pos == "null") {
                    $("#Error_Msg").text("Please select a role and position");
                    return;
                }

                if (CvSU_Mail.includes("@cvsu.edu.ph") == false) {
                    $("#Error_Msg").text("Invalid CvSU Email");
                    return;
                }

                var data = {
                    action: "create",
                    uuid: UUID,
                    role: Role,
                    pos: Pos,
                    email: TempMail,
                    password: TempPass,
                    accounts: {
                        role: Role,
                        pos: Pos,
                    },
                };

                $.ajax({
                    type: "POST",
                    url: "../../../Functions/api/postTempAccount.php",
                    data: data,
                    beforeSend: function() {
                        $("#CreateAccount_Btn").prop("disabled", true);
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            didOpen: (toast) => {
                                Swal.showLoading()
                            }
                        }).fire({
                            icon: 'info',
                            title: 'Creating Account...'
                        });
                    },
                    success: function(response) {
                        if (response.stat === "success") {
                            userTempMail = TempMail;
                            userTempPass = TempPass;
                            $.ajax({
                                type: "GET",
                                url: `../../../Functions/TempAccount_Gen.php?Email=${TempMail}&Password=${TempPass}&CvSU_Mail=${CvSU_Mail}`,
                                success: function(response) {
                                    if (response.stat === "success") {
                                        Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                        }).fire({
                                            icon: 'success',
                                            title: response.message
                                        }).then((result) => {
                                            $("#UUID_Input").val("");
                                            $("#Role_Select").val(
                                                "null");
                                            $("#Pos_Select").val("null")
                                                .attr("disabled",
                                                    false);
                                            $("#TempMail_Input").val(
                                                "");
                                            $("#TempPass_Input").val(
                                                "");
                                            $("#print_Btn").removeClass(
                                                "d-none");
                                        });
                                    } else {
                                        $("#Error_Msg").text(response.message);
                                    }
                                },
                            });
                            setTimeout(function() {
                                $("#print_Btn").prop("disabled", false);
                                userTempMail = "Expired";
                                userTempPass = "Expired";
                                $("#Error_Msg").text(
                                    "Temporary account has expired. Please generate a new one."
                                );
                            }, 300000);
                        } else {
                            $("#Error_Msg").text(response.msg);
                        }
                    },
                });

            });
        });
    </script>
</body>

</html>