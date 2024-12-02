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
    <!-- <link rel="stylesheet" href="../../../../Utilities/Third-party/Froalaeditor/css/froala_editor.pkgd.min.css"> -->
    <link rel="stylesheet" href="../../../../Utilities/Third-party/summernote/summernote-bs5.css">
    <!-- <link rel="stylesheet" href="../../../../Utilities/Third-party/Froalaeditor/css/themes/dark.css"> -->

    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="../../../../Utilities/Third-party/summernote/summernote-bs5.js"></script>
    <script defer type="module" src="../../../../Utilities/Scripts/BS_DBScript.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <title>Dashboard</title>
    <style>
        .table-border {
            border: 1px solid #000;
        }
    </style>
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
                        <div class="card glass-default bg-opacity-25 mb-3 rounded-1">
                            <div class="card-body">
                                <h4 class="text-center fw-bold text-uppercase">Project Proposal</h4>
                                <input type="hidden" id="ID" value="">
                                <input type="hidden" id="OrgCode" value="">
                                <input type="hidden" id="Created_By" value="">
                                <input type="hidden" id="taskID" value="">
                                <input type="radio" id="isFromTask" hidden>
                                <input type="hidden" id="taskOrgCode" value="">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="AdminName" class="form-label">Name of Campus
                                                Administrator</label>
                                            <input type="text" class="form-control rounded-0" id="AdminName">
                                            <div class="form-text">Name of the Campus Administrator</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="AdminLname" class="form-label">Dear
                                                <small>(Dr./Mr./Ms./Mrs.)</small></label>
                                            <input type="text" class="form-control rounded-0" id="LetterTo">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="Letter Body" class="form-label">Letter Body (Content)</label>
                                            <textarea id="LetterBody" class="form-control rounded-1" cols="30" rows="10"
                                                placeholder="Type your letter here"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityTitle" class="form-label">Project Title</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityTitle">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityDateVenue" class="form-label">Project Date and
                                                Venue</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityDateVenue">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityHead" class="form-label">Project Head</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityHead">
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-6 <?php echo $_SESSION['role'] == 1 ? '' : 'd-none'; ?>">
                                        <div class="mb-3">
                                            <label for="OrgCode_new" class="form-label">Organization</label>
                                            <select id="OrgCode_new" class="form-select rounded-0">
                                                <option value="0" selected hidden>Choose...</option>
                                                <?php
                                                    $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 0");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['org_code'] . '">' . $row['org_name'] . '</option>';
}
?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="ActivityObjective" class="form-label">Project Objective</label>
                                            <textarea class="form-control rounded-1" id="ActivityObjective"
                                                placeholder="Type the objective of the activity here"></textarea>
                                            <div class="form-text">Use bullet points or a numbered list</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityTarget" class="form-label">Project Target
                                                Participants</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityTarget">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityMechanics" class="form-label">Project Mechanics</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityMechanics">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="ActivityBudget" class="form-label">Project Budget
                                                Requirement</label>
                                            <textarea class="form-control rounded-1" id="ActivityBudget"
                                                placeholder="Type the budget requirement here"></textarea>
                                            <div class="form-text">Please provide a detailed budget requirement (Tables
                                                are allowed)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivitySourceFunds" class="form-label">Source of Funds</label>
                                            <input type="text" class="form-control rounded-0" id="ActivitySourceFunds">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityOutcomes" class="form-label">Expected Outputs</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityOutcomes">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="ActivitySignature" class="form-label">Signature</label>
                                            <textarea class="form-control rounded-1" id="ActivitySignature"
                                                placeholder="Type the signature template here"></textarea>
                                            <div class="form-text">Please provide a Name and Position for the signature
                                                template</div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="hstack gap-3">
                                            <div class="mb-3 d-flex justify-content-center">
                                                <button class="btn btn-sm btn-outline-secondary rounded-1 d-none"
                                                    id="PrintPreview">Print
                                                    Preview</button>
                                            </div>
                                            <div class="mb-3 d-flex justify-content-center">
                                                <button class="btn btn-sm btn-outline-success rounded-1"
                                                    id="GenerateLetter">Generate
                                                    Letter</button>
                                            </div>
                                            <div class="mb-3 d-flex justify-content-center">
                                                <button class="btn btn-sm btn-outline-danger rounded-1"
                                                    id="ClearFields">Clear
                                                    Fields</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card glass-default bg-opacity-25 rounded-1">
                            <div class="card-body">
                                <h5 class="text-center fw-bold text-uppercase">Previous Documents</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless table-striped">
                                        <thead>
                                            <tr>
                                                <th>Document</th>
                                                <th>Date</th>
                                                <th>Edit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="PreviousDocuments">
                                            <tr>
                                                <td colspan="3" class="text-center">Loading...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card glass-default bg-opacity-25 rounded-1 mt-5">
                            <div class="card-body">
                                <h5 class="text-center fw-bold text-uppercase">Task Status</h5>
                                <p class="text-center" id="TasksMessage">No tasks found</p>
                                <p>Task ID: <span id="TaskIDis">N/A</span></p>
                                <div class="hstack gap-2">
                                    <a href="javascript:void(0)" id="RefreshTasks"
                                        class="btn btn-sm btn-outline-secondary rounded-0 w-100">Refresh</a>
                                    <a href="javascript:void(0)" id="ClearTasks"
                                        class="btn btn-sm btn-outline-danger rounded-0 w-100">Clear Task</a>
                                </div>
                                <script>
                                    $('#RefreshTasks').on('click', function() {
                                        if (localStorage.getItem('taskID_PP') != null) {
                                            if ($('#ID').val() != "") {
                                                Swal.fire({
                                                    icon: 'warning',
                                                    title: 'Are you sure?',
                                                    text: 'You have edited a document. Do you want to continue?',
                                                    showCancelButton: true,
                                                    confirmButtonText: 'Yes',
                                                    cancelButtonText: 'No',
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $('#ClearFields').click();
                                                        $('#TaskIDis').text(localStorage.getItem(
                                                            'taskID_PP'));
                                                        $('#taskID').val(localStorage.getItem(
                                                            'taskID_PP'));
                                                        $('#taskOrgCode').val(localStorage.getItem(
                                                            'taskOrgCode_PP'));
                                                        $('#isFromTask').val('true');
                                                        $('#TasksMessage').text(
                                                            'You are currently editing a task');
                                                    }
                                                });
                                            } else {
                                                $('#ClearFields').click();
                                                $('#TaskIDis').text(localStorage.getItem('taskID_PP'));
                                                $('#taskID').val(localStorage.getItem('taskID_PP'));
                                                $('#taskOrgCode').val(localStorage.getItem('taskOrgCode_PP'));
                                                $('#isFromTask').val('true');
                                                $('#TasksMessage').text('You are currently editing a task');
                                            }
                                        }
                                    });

                                    $('#ClearTasks').on('click', function() {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Confirm Clear Task',
                                            text: 'Would you like to clear this task permanently or continue working on it later?',
                                            showCancelButton: true,
                                            confirmButtonText: 'Clear Task',
                                            cancelButtonText: 'Continue Later',
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                localStorage.removeItem('taskID_PP');
                                                localStorage.removeItem('taskOrgCode_PP');
                                                $('#taskID').val('');
                                                $('#isFromTask').val('false');
                                                $('#TasksMessage').text(
                                                    'Task has been cleared. You can recover it from the task list.'
                                                );
                                                $('#TaskIDis').text('N/A');
                                            } else {
                                                $('#taskID').val('');
                                                $('#isFromTask').val('false');
                                                $('#TasksMessage').text(
                                                    'No tasks found. reload to load the last task.');
                                                $('#TaskIDis').text('N/A');
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function getAP_Documents() {
            var ACTSIG =
                '<div style="text-align: center;"><span style="font-size: 18px;"><span style="font-weight: bolder;">PLEASE CHSNGE THIS BEFORE SUBMITTING</span></span></div><div style="text-align: center;"><span style="font-size: 18px;"><span style="font-weight: bolder;"><br></span></span></div><div style="text-align: center;">Prepared by</div><div style="text-align: center;"><br></div><div style="text-align: center;"><span style="font-weight: bolder;">NAME</span></div><div style="text-align: center;">ORG Secretary</div><div style="text-align: center;"><br></div><div style="text-align: center;">Checked by</div><div style="text-align: center;"><br></div><div style="text-align: center;"><span style="font-weight: bolder;">NAME</span></div><div style="text-align: center;">ORG President</div>';
            $('#ActivitySignature').val(ACTSIG);

            $.ajax({
                type: 'GET',
                url: '../../../Functions/api/getPreProjectProposal.php',
                success: function(response) {
                    if (response.status == 'success') {
                        $('#PreviousDocuments').empty();
                        if (response.data.length > 0) {
                            response.data.forEach(doc => {
                                var link = "../../../../" + doc.file_path;
                                $('#PreviousDocuments').append(`
                            <tr>
                                <td><a href="${link}" target="_blank" title="${doc.act_title} / ${doc.date_Created}" class="text-decoration-none"><i class="fa fa-file-pdf-o"></i> View</a></td>
                                <td>${doc.date_Created}</td>
                                <td><a id="EditDocument_${doc.id}" style="cursor: pointer;" class="text-decoration-none"><i class="fa fa-edit"></i> Edit</a></td>
                            </tr>
                        `);

                                $(document).on('click', `#EditDocument_${doc.id}`, function() {
                                    if ($('#taskID').val() != "") {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Are you sure?',
                                            text: 'You have unsaved changes. Do you want to continue?',
                                            showCancelButton: true,
                                            confirmButtonText: 'Yes',
                                            cancelButtonText: 'No',
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                if (doc.act_signature == null) {
                                                    doc.act_signature = ACTSIG;
                                                }

                                                $('#AdminName').val(doc.admin_name);
                                                $('#LetterTo').val(doc.dear_title);
                                                $('#LetterBody').summernote('code',
                                                    doc.LetterBody);
                                                $('#ActivityTitle').val(doc
                                                    .act_title);
                                                $('#ActivityDateVenue').val(doc
                                                    .act_date_ven);
                                                $('#ActivityHead').val(doc
                                                    .act_head);
                                                $('#ActivityObjective').summernote(
                                                    'code', doc.act_obj);
                                                $('#ActivityTarget').val(doc
                                                    .act_participate);
                                                $('#ActivityMechanics').val(doc
                                                    .act_mech);
                                                $('#ActivityBudget').summernote(
                                                    'code', doc.act_budget);
                                                $('#ActivitySourceFunds').val(doc
                                                    .act_funds);
                                                $('#ActivityOutcomes').val(doc
                                                    .act_expectOut);
                                                $('#ActivitySignature').summernote(
                                                    'code', doc.act_signature);
                                                $('#ID').val(doc.ID);
                                                $('#OrgCode').val(doc.org_code);
                                                $('#Created_By').val(doc
                                                    .Created_By);
                                                $('#taskID').val("");
                                                $('#isFromTask').val('false');
                                                $('#TasksMessage').text(
                                                    'No tasks found. Click reload to load the last task.'
                                                );
                                                $('#TaskIDis').text('N/A');
                                                $('#taskOrgCode').val('');
                                            }
                                        });
                                    } else {
                                        if (doc.act_signature == null) {
                                            doc.act_signature = ACTSIG;
                                        }

                                        $('#AdminName').val(doc.admin_name);
                                        $('#LetterTo').val(doc.dear_title);
                                        $('#LetterBody').summernote('code',
                                            doc.LetterBody);
                                        $('#ActivityTitle').val(doc
                                            .act_title);
                                        $('#ActivityDateVenue').val(doc
                                            .act_date_ven);
                                        $('#ActivityHead').val(doc
                                            .act_head);
                                        $('#ActivityObjective').summernote(
                                            'code', doc.act_obj);
                                        $('#ActivityTarget').val(doc
                                            .act_participate);
                                        $('#ActivityMechanics').val(doc
                                            .act_mech);
                                        $('#ActivityBudget').summernote(
                                            'code', doc.act_budget);
                                        $('#ActivitySourceFunds').val(doc
                                            .act_funds);
                                        $('#ActivityOutcomes').val(doc
                                            .act_expectOut);
                                        $('#ActivitySignature').summernote(
                                            'code', doc.act_signature);
                                        $('#ID').val(doc.ID);
                                        $('#OrgCode').val(doc.org_code);
                                        $('#Created_By').val(doc
                                            .Created_By);
                                        $('#taskID').val("");
                                        $('#isFromTask').val('false');
                                        if (localStorage.getItem('taskID_PP') == null) {
                                            $('#TasksMessage').text('No tasks found.');
                                        } else {
                                            $('#TasksMessage').text(
                                                'No tasks found. Click reload to load the last task.'
                                                );
                                        }

                                        $('#TaskIDis').text('N/A');
                                    }
                                });
                            });
                        } else {
                            $('#PreviousDocuments').empty();
                            $('#PreviousDocuments').append(`
                            <tr>
                                <td colspan="3" class="text-center">No documents found</td>
                            </tr>
                        `);
                        }
                    } else {
                        $('#PreviousDocuments').empty();
                        $('#PreviousDocuments').append(`
                        <tr>
                            <td colspan="3" class="text-center">Nothing to show</td>
                        </tr>
                    `);
                    }
                },
                error: function(xhr, status, error) {
                    $('#PreviousDocuments').empty();
                    $('#PreviousDocuments').append(`
                    <tr>
                        <td colspan="3" class="text-center">Not available</td>
                    </tr>
                `);
                    console.error(xhr.responseText);
                }
            });
        }

        $(document).ready(function() {

            if (localStorage.getItem('taskID_PP') != null) {
                $('#taskID').val(localStorage.getItem('taskID_PP'));
                $('#taskOrgCode').val(localStorage.getItem('taskOrgCode_PP'));
                $('#isFromTask').val('true');
                $('#TasksMessage').text('You are currently editing a task');
                $('#TaskIDis').text(localStorage.getItem('taskID_PP'));
                //localStorage.removeItem('taskID');
            } else {
                $('#taskID').val('');
                $('#isFromTask').val('false');
                $('#RefreshTasks').addClass('d-none');
                $('#ClearTasks').addClass('d-none');
            }

            getAP_Documents();

            $('#LetterBody').summernote({
                placeholder: 'Type your letter here',
                tabsize: 2,
                width: 755,
                height: 350,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear', 'fontsize', 'fontname']],
                    ['para', ['paragraph', 'ul', 'ol', 'height', 'hr']],
                    ['undo', ['undo']],
                    ['redo', ['redo']],
                ],
                maxCharCount: 8000,
            });

            $('#ActivityObjective').summernote({
                placeholder: 'Type the objective of the activity here',
                tabsize: 2,
                width: 755,
                height: 350,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear', 'fontsize', 'fontname']],
                    ['para', ['paragraph', 'ul', 'ol', 'height', 'hr']],
                    ['undo', ['undo']],
                    ['redo', ['redo']],
                ],
                maxCharCount: 8000,
            });

            $('#ActivityBudget').summernote({
                placeholder: 'Type the budget requirement here',
                tabsize: 2,
                width: 755,
                height: 350,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear', 'fontsize', 'fontname']],
                    ['para', ['paragraph', 'ul', 'ol', 'height', 'hr']],
                    ['insert', ['table']],
                    ['undo', ['undo']],
                    ['redo', ['redo']],
                ],
                callbacks: {
                    onInit: function() {
                        $('#ActivityBudget').summernote('code', $('#ActivityBudget').summernote(
                            'code').replace(/<table/g,
                            '<table style="border: 1px solid black;"'));
                    },
                    onChange: function(contents, $editable) {
                        $editable.find('table').css('border', '1px solid black');
                        $editable.find('td, th').css('border', '1px solid black');
                    }
                },
                maxCharCount: 8000,
            });

            $('#ActivitySignature').summernote({
                placeholder: 'Type your letter here. You can use @ to mention the name of the person',
                tabsize: 2,
                width: 755,
                height: 350,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear', 'fontsize']],
                    ['para', ['paragraph', 'ul', 'ol', 'height', 'hr']],
                    ['undo', ['undo']],
                    ['redo', ['redo']],
                ],
                maxCharCount: 8000,
                hint: {
                    mention: [
                        '<?php echo $_SESSION['FullName']; ?>',
                        'ORG Secretary', 'ORG President'
                    ],
                    match: /\B@(\w*)$/,
                    search: function(keyword, callback) {
                        callback($.grep(this.mention, function(item) {
                            return item.indexOf(keyword) == 0;
                        }));
                    },
                    content: function(item) {
                        return item;
                    }
                }
            });

            $('.note-editable').css('font-family', 'Helvetica');
            $('.note-editable').css('line-height', '1.0');
        });

        $('#ClearFields').on('click', function() {
            $('#AdminName').val('');
            $('#LetterTo').val('');
            $('#LetterBody').summernote('code', '');
            $('#ActivityTitle').val('');
            $('#ActivityDateVenue').val('');
            $('#ActivityObjective').summernote('code', '');
            $('#ActivityTarget').val('');
            $('#ActivityMechanics').val('');
            $('#ActivityBudget').summernote('code', '');
            $('#ActivitySourceFunds').val('');
            $('#ActivityOutcomes').val('');
            $('#ActivityHead').val('');
            $('#ActivitySignature').summernote('code', '');
            $('#PrintPreview').addClass('d-none');
            $('#ID').val('');
            $('#OrgCode').val('');
            $('#Created_By').val('');
            $('#taskID').val('');
            $('#isFromTask').val('false');
        });

        $('#GenerateLetter').on('click', function() {
            var TaskID = $('#taskID').val() || '';
            var isFromTask = $('#isFromTask').val() || 'false';
            var ID = $('#ID').val();
            var OrgCode = $('#OrgCode').val() || $('#OrgCode_new').val();
            var Created_By = $('#Created_By').val();
            var AdminName = $('#AdminName').val();
            var LetterTo = $('#LetterTo').val();
            var LetterBody = $('#LetterBody').summernote('code');
            var ActivityTitle = $('#ActivityTitle').val();
            var ActivityDateVenue = $('#ActivityDateVenue').val();
            var ActivityObjective = $('#ActivityObjective').summernote('code');
            var ActivityTarget = $('#ActivityTarget').val();
            var ActivityMechanics = $('#ActivityMechanics').val();
            var ActivityBudget = $('#ActivityBudget').summernote('code');
            var ActivityHead = $('#ActivityHead').val();
            var ActivitySourceFunds = $('#ActivitySourceFunds').val();
            var ActivityOutcomes = $('#ActivityOutcomes').val();
            var ActivitySignature = $('#ActivitySignature').summernote('code');

            if (AdminName == '' || LetterTo == '' || LetterBody == '' || ActivityTitle == '' ||
                ActivityDateVenue == '' || ActivityObjective == '' || ActivityTarget == '' ||
                ActivityMechanics == '' || ActivityBudget == '' || ActivityHead == '' || ActivitySourceFunds ==
                '' || ActivityOutcomes == '' || ActivitySignature == '') {
                Swal.mixin({
                    toast: true,
                    position: 'top',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                }).fire({
                    icon: 'error',
                    title: 'Please fill up all fields'
                });
                return;
            }

            LetterBody = LetterBody.replace(new RegExp('style="background-color:\\s*var\\(--bs-card-bg\\);?"',
                'g'), '');
            ActivityObjective = ActivityObjective.replace(new RegExp(
                'style="background-color:\\s*var\\(--bs-card-bg\\);?"', 'g'), '');
            ActivityBudget = ActivityBudget.replace(new RegExp(
                'style="background-color:\\s*var\\(--bs-card-bg\\);?"', 'g'), '');
            ActivitySignature = ActivitySignature.replace(new RegExp(
                'style="background-color:\\s*var\\(--bs-card-bg\\);\\s*color:\\s*var\\(--bs-body-color\\);"',
                'g'), '');
            ActivitySignature = ActivitySignature.replace(new RegExp(
                'style="background-color:\\s*var\\(--bs-card-bg\\);?"', 'g'), '');

            var data = {
                TaskID: TaskID,
                isFromTask: isFromTask,
                ID: ID,
                OrgCode: OrgCode,
                Created_By: Created_By,
                AdminName: AdminName,
                LetterTo: LetterTo,
                LetterBody: LetterBody,
                ActivityHead: ActivityHead,
                ActivityTitle: ActivityTitle,
                ActivityDateVenue: ActivityDateVenue,
                ActivityObjective: ActivityObjective,
                ActivityTarget: ActivityTarget,
                ActivityMechanics: ActivityMechanics,
                ActivityBudget: ActivityBudget,
                ActivitySourceFunds: ActivitySourceFunds,
                ActivityOutcomes: ActivityOutcomes,
                ActivitySignature: ActivitySignature
            };

            console.log(data);

            $.ajax({
                type: 'POST',
                url: '../../../Functions/api/postProjectProposal.php',
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        Swal.mixin({
                            toast: true,
                            position: 'top',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        }).fire({
                            icon: 'success',
                            title: response.message
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                //$('#ClearFields').click();
                                localStorage.removeItem('taskID_AP');
                                localStorage.removeItem('taskOrgCode_AP');
                                getAP_Documents();
                                $('#PrintPreview').removeClass('d-none');
                                $('#PrintPreview').off('click').on('click', function() {
                                    window.open("../../../../" + response.path,
                                        '_blank');
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    </script>
</body>

</html>