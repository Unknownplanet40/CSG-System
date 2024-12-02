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

if ($_SESSION['org_Code'] == null) {
    $LeftLogoSrc = " ";
    $RightLogoSrc = " ";
    $FirstLine = " ";
    $SecondLine = " ";
    $ThirdLine = " ";
    $FourthLine = " ";
    $FifthLine = " ";
    $SixthLine = " ";
} else {
    $stmt = $conn->prepare("SELECT * FROM orgdocumetheader WHERE org_code = '" . $_SESSION['org_Code'] . "'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row != null) {
        $LeftLogoSrc = "../" . $row['left_Image'];
        $RightLogoSrc = "../" . $row['right_Image'];
        $FirstLine = $row['firstLine'];
        $SecondLine = $row['secondLine'];
        $ThirdLine = $row['thirdLine'];
        $FourthLine = $row['fourthLine'];
        $FifthLine = $row['fifthLine'];
        $SixthLine = $row['sixthLine'];
    } else {
        $stmt = $conn->prepare("SELECT org_name FROM sysorganizations WHERE org_code = '" . $_SESSION['org_Code'] . "'");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $ORG_NAME = ucwords($row['org_name']);

        $LeftLogoSrc = "../../../../Assets/Images/pdf-Resource/L_Logo.png";
        $RightLogoSrc = "../../../../Assets/Images/pdf-Resource/R_Logo.png";
        $FirstLine = "Republic of the Philippines";
        $SecondLine = "Cavite State University";
        $ThirdLine = "Imus, Cavite";
        $FourthLine = "Student Development Services";
        $FifthLine = $ORG_NAME;
        $SixthLine = "csg.syste@cvsu.edu.ph";
    }
}
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
    <script defer type="module" src="../../../../Utilities/Scripts/DH_UMScript.js"></script>
    <title>Document Header</title>
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
                <div class="card text-bg-light border-0">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-2 d-flex justify-content-center align-items-center">
                                <img src="<?php echo $LeftLogoSrc; ?>"
                                    style="margin-right: -25rem; margin-top: -1.8rem;" id="LeftLogo" alt="" height="92">
                            </div>
                            <div class="col-8 mx-auto">
                                <h6 id="fline" class="text-center">
                                    <?php echo $FirstLine; ?>
                                </h6>
                                <h4 id="sline" class="text-center fw-bold">
                                    <?php echo $SecondLine; ?>
                                </h4>
                                <h6 id="tline" class="text-center">
                                    <?php echo $ThirdLine; ?>
                                </h6>
                                <h6 id="frline" class="text-center">
                                    <?php echo $FourthLine; ?>
                                </h6>
                                <h4 id="siline" class="text-center text-nowrap fw-bold">
                                    <?php echo $FifthLine; ?>
                                </h4>
                                <h6 id="seline" class="text-center text-center fw-bold">
                                    <?php echo $SixthLine; ?>
                                </h6>
                            </div>
                            <div class="col-2 d-flex justify-content-center align-items-center">
                                <img src="<?php echo $RightLogoSrc; ?>"
                                    style="margin-left: -25rem; margin-top: -1.8rem;" id="RightLogo" alt="" height="92">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-5">
                    <div class="col-6">
                        <label for="LeftImage" class="form-label">Left Logo Image</label>
                        <div class="input-group mb-3">
                            <button class="btn btn-sm btn-outline-success" type="button" id="DLLeftImage"
                                data-bs-toggle="tooltip" data-bs-placement="bottom"
                                title="Download the Template">Template</button>
                            <input type="file" class="form-control" id="LeftImage" accept="image/png">
                        </div>
                    </div>
                    <div class="col-6">
                        <label for="RightImage" class="form-label">Right Logo Image</label>
                        <div class="input-group mb-3">
                            <button class="btn btn-sm btn-outline-success" type="button" id="DLRightImage"
                                data-bs-toggle="tooltip" data-bs-placement="bottom"
                                title="Download the Template">Template</button>
                            <input type="file" class="form-control" id="RightImage" accept="image/png">
                        </div>
                    </div>
                    <div class="col-4">
                        <label for="FirstLine" class="form-label">First Line</label>
                        <input type="text" class="form-control" id="FirstLine"
                            placeholder="<?php echo $FirstLine; ?>"
                            value="<?php echo $FirstLine; ?>">
                    </div>
                    <div class="col-4">
                        <label for="SecondLine" class="form-label">Second Line</label>
                        <input type="text" class="form-control" id="SecondLine"
                            placeholder="<?php echo $SecondLine; ?>"
                            value="<?php echo $SecondLine; ?>">
                    </div>
                    <div class="col-4">
                        <label for="ThirdLine" class="form-label">Third Line</label>
                        <input type="text" class="form-control" id="ThirdLine"
                            placeholder="<?php echo $ThirdLine; ?>"
                            value="<?php echo $ThirdLine; ?>">
                    </div>
                    <div class="col-4">
                        <label for="FourthLine" class="form-label">Fourth Line</label>
                        <input type="text" class="form-control" id="FourthLine"
                            placeholder="<?php echo $FourthLine; ?>"
                            value="<?php echo $FourthLine; ?>">
                    </div>
                    <div class="col-4">
                        <label for="FifthLine" class="form-label">Fifth Line</label>
                        <input type="text" class="form-control" id="FifthLine"
                            placeholder="<?php echo $FifthLine; ?>"
                            value="<?php echo $FifthLine; ?>">
                    </div>
                    <div class="col-4">
                        <label for="SixthLine" class="form-label">Sixth Line</label>
                        <input type="text" class="form-control" id="SixthLine"
                            placeholder="<?php echo $SixthLine; ?>"
                            value="<?php echo $SixthLine; ?>">
                    </div>
                    <div class="col-12 d-flex justify-content-center align-items-center mt-5">
                        <select
                            class="form-select w-25 me-3 <?php echo $_SESSION['role'] != 1 ? 'd-none' : ''; ?>"
                            id="OrgSelect">
                            <option value="0" hidden selected>Select Organization</option>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM sysorganizations");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['org_code'] . '">' . ucwords($row['org_name']) . '</option>';
    }
} else {
    echo '<option value="0" disabled>No Organization Found</option>';
}?>
                        </select>
                        <button class="btn btn-sm btn-success w-25" id="SaveDocHeader">Save</button>
                    </div>
                </div>
                <script>
                    $(document).ready(function() {

                        $("#OrgSelect").change(function() {
                            var OrgCode = $(this).val();
                            $.ajax({
                                type: "GET",
                                url: "../../../Functions/api/GetOrgDocHeader.php",
                                data: {
                                    OrgCode: OrgCode
                                },
                                success: function(response) {
                                    if (response.status == "success") {
                                        if (response.data.length > 0) {
                                            var data = response.data[0];
                                            $("#LeftLogo").attr("src", "../" + data
                                                .left_Image);
                                            $("#RightLogo").attr("src", "../" + data
                                                .right_Image);
                                            $("#FirstLine").val(data.firstLine);
                                            $("#SecondLine").val(data.secondLine);
                                            $("#ThirdLine").val(data.thirdLine);
                                            $("#FourthLine").val(data.fourthLine);
                                            $("#FifthLine").val(data.fifthLine);
                                            $("#SixthLine").val(data.sixthLine);

                                            $("#fline").text(data.firstLine);
                                            $("#sline").text(data.secondLine);
                                            $("#tline").text(data.thirdLine);
                                            $("#frline").text(data.fourthLine);
                                            $("#siline").text(data.fifthLine);
                                            $("#seline").text(data.sixthLine);
                                        } else {
                                            $("#LeftLogo").attr("src",
                                                "../../../../Assets/Images/pdf-Resource/L_Logo.png"
                                            );
                                            $("#RightLogo").attr("src",
                                                "../../../../Assets/Images/pdf-Resource/R_Logo.png"
                                            );
                                            $("#FirstLine").val(
                                                "Republic of the Philippines");
                                            $("#SecondLine").val("Cavite State University");
                                            $("#ThirdLine").val("Imus, Cavite");
                                            $("#FourthLine").val(
                                                "Student Development Services");
                                            $("#FifthLine").val("Not yet Configured");
                                            $("#SixthLine").val("Orgaization Email");

                                            $("#fline").text("Republic of the Philippines");
                                            $("#sline").text("Cavite State University");
                                            $("#tline").text("Imus, Cavite");
                                            $("#frline").text(
                                                "Student Development Services");
                                            $("#siline").text("Not yet Configured");
                                            $("#seline").text("Orgaization Email");
                                        }
                                    } else {
                                        Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                        }).fire({
                                            icon: 'error',
                                            title: 'Something went wrong'
                                        });
                                        console.log(response);
                                    }
                                },

                                error: function() {
                                    Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                    }).fire({
                                        icon: 'error',
                                        title: 'Something went wrong'
                                    });
                                    console.log(response);
                                }
                            });
                        });

                        $("#LeftImage").change(function() {
                            var file = this.files[0];
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                $("#LeftLogo").attr("src", e.target.result);
                            }
                            reader.readAsDataURL(file);
                        });

                        $("#RightImage").change(function() {
                            var file = this.files[0];
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                $("#RightLogo").attr("src", e.target.result);
                            }
                            reader.readAsDataURL(file);
                        });

                        $("#FirstLine").on('input', function() {
                            var text = $(this).val() || "Republic of the Philippines";
                            $("#fline").text(text);
                        });

                        $("#SecondLine").on('input', function() {
                            var text = $(this).val() || "Cavite State University";
                            $("#sline").text(text);
                        });

                        $("#ThirdLine").on('input', function() {
                            var text = $(this).val() || "Imus, Cavite";
                            $("#tline").text(text);
                        });

                        $("#FourthLine").on('input', function() {
                            var text = $(this).val() || "Student Development Services";
                            $("#frline").text(text);
                        });

                        $("#FifthLine").on('input', function() {
                            var text = $(this).val() ||
                                "<?php echo $FifthLine; ?>";
                            $("#siline").text(text);
                        });

                        $("#SixthLine").on('input', function() {
                            var text = $(this).val() ||
                                "<?php echo $SixthLine; ?>";
                            $("#seline").text(text);
                        });

                        $("#DLLeftImage").click(function() {
                            var link = document.createElement('a');
                            link.href = '../../../../Assets/Images/pdf-Resource/L_Template.png';
                            link.download = 'LeftLogo-Template.png';
                            link.click();
                        });

                        $("#DLRightImage").click(function() {
                            var link = document.createElement('a');
                            link.href = '../../../../Assets/Images/pdf-Resource/R_Template.png';
                            link.download = 'RightLogo-Template.png';
                            link.click();
                        });

                        $("#SaveDocHeader").click(function() {
                            var LeftLogo = $("#LeftImage").prop('files')[0];
                            var RightLogo = $("#RightImage").prop('files')[0];
                            var FirstLine = $("#FirstLine").val();
                            var SecondLine = $("#SecondLine").val();
                            var ThirdLine = $("#ThirdLine").val();
                            var FourthLine = $("#FourthLine").val();
                            var FifthLine = $("#FifthLine").val();
                            var SixthLine = $("#SixthLine").val();
                            <?php echo $_SESSION['role'] == 1 ? 'var OrgCode = $("#OrgSelect").val();' : ''; ?>

                            if (FirstLine == "" || SecondLine ==
                                "" || ThirdLine == "" || FourthLine == "" || FifthLine == "" ||
                                SixthLine ==
                                "" <?php echo $_SESSION['role'] == 1 ? '|| OrgCode == 0' : ''; ?>
                            ) {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                }).fire({
                                    icon: 'error',
                                    title: 'Please fill up all fields'
                                });
                                return;
                            }

                            if (($("#LeftLogo").attr("src") ==
                                    "../../../../Assets/Images/pdf-Resource/L_Logo.png") ||
                                ($("#RightLogo").attr("src") ==
                                    "../../../../Assets/Images/pdf-Resource/R_Logo.png")) {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                }).fire({
                                    icon: 'info',
                                    title: 'Please upload Left and Right Logo'
                                });
                                return;
                            }

                            if (LeftLogo || RightLogo) {
                                if (LeftLogo) {
                                    var img = new Image();
                                    img.src = URL.createObjectURL(LeftLogo);
                                    img.onload = function() {
                                        if (img.width != 162 || img.height != 84) {
                                            Swal.mixin({
                                                toast: true,
                                                position: 'top-end',
                                                showConfirmButton: false,
                                                timer: 3000,
                                                timerProgressBar: true,
                                            }).fire({
                                                icon: 'info',
                                                title: 'Left Logo must be 162 x 84'
                                            });
                                            return;
                                        }
                                    }

                                    if (!["image/png"].includes(LeftLogo.type)) {
                                        Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                        }).fire({
                                            icon: 'info',
                                            title: 'Left Logo must be PNG'
                                        });
                                        return;
                                    }
                                }

                                if (RightLogo) {
                                    var img = new Image();
                                    img.src = URL.createObjectURL(RightLogo);
                                    img.onload = function() {
                                        if (img.width != 468 || img.height != 225) {
                                            Swal.mixin({
                                                toast: true,
                                                position: 'top-end',
                                                showConfirmButton: false,
                                                timer: 3000,
                                                timerProgressBar: true,
                                            }).fire({
                                                icon: 'info',
                                                title: 'Right Logo must be 468 x 225'
                                            });
                                            return;
                                        }
                                    }

                                    if (!["image/png"].includes(RightLogo.type)) {
                                        Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                        }).fire({
                                            icon: 'info',
                                            title: 'Right Logo must be PNG'
                                        });
                                        return;
                                    }
                                }
                            }

                            function validateInput(input, isEmail = false) {
                                if (!input.trim()) {
                                    return false;
                                }
                                if (isEmail) {
                                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input);
                                }
                                return input.length >= 2 && input.length <= 100;
                            }

                            if (!validateInput(FirstLine) || !validateInput(SecondLine) || !
                                validateInput(ThirdLine) || !validateInput(FourthLine) || !
                                validateInput(FifthLine) || !validateInput(SixthLine, true)) {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                }).fire({
                                    icon: 'error',
                                    title: 'Please fill up all fields'
                                });
                                return;
                            }

                            $("#SaveDocHeader").attr("disabled", true);

                            var formData = new FormData();
                            if ($("#LeftImage").prop('files')[0]) {
                                formData.append('LeftLogo', $("#LeftImage").prop('files')[0]);
                            }
                            if ($("#RightImage").prop('files')[0]) {
                                formData.append('RightLogo', $("#RightImage").prop('files')[0]);
                            }
                            formData.append('FirstLine', FirstLine);
                            formData.append('SecondLine', SecondLine);
                            formData.append('ThirdLine', ThirdLine);
                            formData.append('FourthLine', FourthLine);
                            formData.append('FifthLine', FifthLine);
                            formData.append('SixthLine', SixthLine);
                            <?php echo $_SESSION['role'] == 1 ? 'formData.append("OrgCode", OrgCode);' : ''; ?>

                            $.ajax({
                                type: "POST",
                                url: "../../../Functions/api/SaveDocHeader.php",
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    if (response.status == "success") {
                                        Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                        }).fire({
                                            icon: 'success',
                                            title: 'Document Header Saved'
                                        });
                                        $("#SaveDocHeader").text("Changes Saved");
                                        setTimeout(() => {
                                            $("#SaveDocHeader").attr("disabled",
                                                false);
                                            $("#SaveDocHeader").text("Save");
                                        }, 1500);
                                    } else {
                                        Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                        }).fire({
                                            icon: 'error',
                                            title: 'Opps! Something went wrong'
                                        });
                                        console.log(response);
                                    }
                                },

                                error: function() {
                                    Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                    }).fire({
                                        icon: 'error',
                                        title: 'Opps! Something went wrong'
                                    });
                                },
                            });
                        });
                    });
                </script>
            </div>
        </div>
    </div>
    </div>
</body>

</html>