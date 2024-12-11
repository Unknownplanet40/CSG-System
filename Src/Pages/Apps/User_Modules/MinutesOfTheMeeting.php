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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/summernote/summernote-bs5.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="../../../../Utilities/Third-party/summernote/summernote-bs5.js"></script>
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
        <div class="BS-Main mt-2">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card glass-default bg-opacity-25 mb-3 rounded-1">
                            <div class="card-body">
                                <h4 class="text-center fw-bold text-uppercase">Minutes of the Meeting</h4>
                                <input type="hidden" id="ID" value="">
                                <input type="hidden" id="OrgCode" value="">
                                <input type="hidden" id="Created_By" value="">
                                <input type="hidden" id="taskID" value="">
                                <input type="radio" id="isFromTask" hidden value="false">
                                <input type="hidden" id="taskOrgCode" value="">

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="MM-DATE" class="form-label">Date</label>
                                            <input type="date" class="form-control rounded-0" id="MM-DATE"
                                                value="<?php echo date('Y-m-d'); ?>"
                                                min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="MM-TIMESTARTED" class="form-label">Time Started</label>
                                            <input type="time" class="form-control rounded-0" id="MM-TIMESTARTED"
                                                value="<?php echo date('H:i'); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="MM-LOC" class="form-label">Location</label>
                                            <input type="text" class="form-control rounded-0" id="MM-LOC"
                                                placeholder="e.g. Conference Room" required>
                                        </div>
                                    </div>
                                    <div
                                        class="<?php echo $_SESSION['role'] == 1 ? 'col-md-6' : 'col-md-12'; ?>">
                                        <div class="mb-3">
                                            <label for="MM-PRESIDER" class="form-label">Presider</label>
                                            <input type="text" class="form-control rounded-0" id="MM-PRESIDER"
                                                placeholder="e.g. Mr. John Doe" required>
                                        </div>
                                    </div>
                                    <div
                                        class="<?php echo $_SESSION['role'] == 1 ? 'col-md-6' : 'd-none'; ?>">
                                        <div class="mb-3">
                                            <label for="MM-ORG" class="form-label">Organization</label>
                                            <select class="form-select rounded-0" id="MM-ORG" required>
                                                <option value="" selected disabled hidden>Select Organization</option>
                                                <?php $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 0");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
while ($row = $result->fetch_assoc()) {
    if ($_SESSION['org_Code'] == $row['org_code']) {
        echo '<option value="' . $row['org_code'] . '" selected>' . $row['org_name'] . '</option>';
    } else {
        echo '<option value="' . $row['org_code'] . '">' . $row['org_name'] . '</option>';
    }
}?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="MM-ATTENDEES" class="form-label">Attendees</label>
                                            <textarea class="form-control rounded-0 SN-TA" id="MM-ATTENDEES" rows="3"
                                                placeholder="e.g. Mr. John Doe, Ms. Jane Doe" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="MM-ABSENTEES" class="form-label">Absentees</label>
                                            <textarea class="form-control rounded-0 SN-TA" id="MM-ABSENTEES" rows="3"
                                                placeholder="e.g. Mr. John Doe, Ms. Jane Doe" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="MM-AGENDA" class="form-label">Agenda</label>
                                            <textarea class="form-control rounded-0 SN-TA" id="MM-AGENDA" rows="3"
                                                placeholder="e.g. 1. Call to Order 2. Approval of Minutes"
                                                required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="MM-COMMENCEMENT" class="form-label">Commencement</label>
                                            <textarea class="form-control rounded-0 SN-TA" id="MM-COMMENCEMENT" rows="3"
                                                placeholder="e.g. The meeting was called to order at 8:00 AM"
                                                required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="MM-TIMEADJOURNED" class="form-label">Time Adjourned</label>
                                            <input type="time" class="form-control rounded-0" id="MM-TIMEADJOURNED"
                                                value="<?php echo date('H:i'); ?>"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="MM-DOCS" class="form-label">Documentations</label>
                                            <input type="file" class="form-control rounded-0 rounded-0" id="MM-DOCS"
                                                accept=".png, .jpg, .jpeg" multiple required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="MM-SIGNATURE" class="form-label">Signature</label>
                                            <textarea class="form-control rounded-0 rounded-0 SN-TA" id="MM-SIGNATURE"
                                                rows="3" placeholder="e.g. Mr. John Doe, Ms. Jane Doe"
                                                required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="hstack gap-3 justify-content-center">
                                            <button class="btn btn-sm btn-secondary rounded-0 d-none"
                                                id="MM-Print">Print Minutes</button>
                                            <button class="btn btn-sm btn-success rounded-0 w-25"
                                                id="MM-SAVE">Save</button>
                                            <button class="btn btn-sm btn-outline-danger rounded-0"
                                                id="MM-CANCEL">Cancel</button>
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
                                        if (localStorage.getItem('taskID_MM') != null) {
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
                                                        if ($('#Constituents-tab').hasClass(
                                                                'active')) {
                                                            $('#Cons-Cancel').click();
                                                        } else {
                                                            $('#Off-Cancel').click();
                                                        }
                                                        $('#ClearFields').click();
                                                        $('#TaskIDis').text(localStorage.getItem(
                                                            'taskID_MM'));
                                                        $('#taskID').val(localStorage.getItem(
                                                            'taskID_MM'));
                                                        $('#taskOrgCode').val(localStorage.getItem(
                                                            'orgCODE_MM'));
                                                        $('#isFromTask').val('true');
                                                        $('#TasksMessage').text(
                                                            'You are currently editing a task');
                                                    }
                                                });
                                            } else {
                                                $('#ClearFields').click();
                                                $('#TaskIDis').text(localStorage.getItem('taskID_MM'));
                                                $('#taskID').val(localStorage.getItem('taskID_MM'));
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
                                                localStorage.removeItem('taskID_MM');
                                                localStorage.removeItem('orgCODE_MM');
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
                                                    'No tasks found. reload to load the last task.'
                                                );
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
        $(document).ready(function() {

            function getMM_Documents() {
                $.ajax({
                    url: '../../../Functions/api/getMinuteMeeting.php',
                    type: 'GET',
                    success: function(data) {
                        if (data.status == 'success') {
                            $('#PreviousDocuments').empty();
                            if (data.data.length == 0) {
                                $('#PreviousDocuments').html(
                                    '<tr><td colspan="3" class="text-center">No File yet</td></tr>'
                                );
                            } else {
                                const tooltipList = [];
                                data.data.forEach(function(item) {
                                    var link = "../../../../" + item.file_path;
                                    var date = new Date(item.DateCreated);
                                    var month = date.toLocaleString('default', {
                                        month: 'short'
                                    });
                                    var day = date.getDate();
                                    var year = date.getFullYear();
                                    var time = date.toLocaleTimeString('en-US', {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                    $('#PreviousDocuments').append(`<tr>
                                         <td><a href="${link}" target="_blank" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" data-bs-trigger="hover"
                                          data-bs-title="Minute Meetings / ${item.DateCreated} / ${item.Created_By}" class="text-decoration-none texr-truncate"
                                            style="max-width: 130px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                         ><i class="fa fa-file-pdf-o"></i> Minute Meetings</a></td>
                                         <td>${month} ${day}, ${year}</td>
                                         <td class="text-center"><a id="EditDocument_${item.ID}" style="cursor: pointer;" class="text-decoration-none"><i class="fa fa-edit text-primary"></i></a></td>
                                         </tr>`);

                                    const tooltipTriggerList = document
                                        .querySelectorAll('[data-bs-toggle="tooltip"]');
                                    tooltipTriggerList.forEach(el => new bootstrap
                                        .Tooltip(el));

                                    $(`#EditDocument_${item.ID}`).click(function() {
                                        $('#ID').val(item.ID);
                                        $('#OrgCode').val(item.org_code);
                                        $('#Created_By').val(item.Created_By);
                                        $('#taskID').val("");
                                        $('#isFromTask').val("false");
                                        $('#taskOrgCode').val("");
                                        $('#TasksMessage').text(
                                            'No tasks found');
                                        $('#TaskIDis').text('N/A');

                                        $('#MM-DATE').val(item.MMdate);
                                        $('#MM-TIMESTARTED').val(item.MMTimeStart);
                                        $('#MM-LOC').val(item.MMLocation);
                                        $('#MM-PRESIDER').val(item.MMPresider);
                                        $('#MM-ATTENDEES').summernote('code', item
                                            .MMAttendees);
                                        $('#MM-ABSENTEES').summernote('code', item
                                            .MMAbsentees);
                                        $('#MM-AGENDA').summernote('code', item
                                            .MMAgenda);
                                        $('#MM-COMMENCEMENT').summernote('code',
                                            item.MMCommencement);
                                        $('#MM-TIMEADJOURNED').val(item.MMTimeend);
                                        $('#MM-SIGNATURE').summernote('code', item
                                            .MMsignature);
                                        $('#MM-DOCS').val('');
                                        $('#MM-SAVE').text('Update');

                                    });
                                });
                            }

                        } else {
                            $('#PreviousDocuments').html(
                                '<tr><td colspan="3" class="text-center">No documents found</td></tr>'
                            );
                        }
                    },

                    error: function() {
                        $('#PreviousDocuments').html(
                            '<tr><td colspan="3" class="text-center">No documents found</td></tr>'
                        );
                    }
                });
            }

            getMM_Documents();


            if (localStorage.getItem('taskID_MM') != null) {
                $('#taskID').val(localStorage.getItem('taskID_MM'));
                $('#isFromTask').val('true');
                $('#TasksMessage').text('You are currently editing a task');
                $('#TaskIDis').text(localStorage.getItem('taskID_MM'));
                $('#taskOrgCode').val(localStorage.getItem('orgCODE_MM'));
                //localStorage.removeItem('taskID');
            } else {
                $('#taskID').val('');
                $('#isFromTask').val('false');
                $('#RefreshTasks').addClass('d-none');
                $('#ClearTasks').addClass('d-none');
            }


            $('.SN-TA').summernote({
                height: 300,
                tabsize: 2,
                focus: true,
                styleTags: ['p', 'h6', 'h5', 'h4', 'h3', 'h2', 'h1'],
                fontNames: ['Helvetica'],
                fontNamesIgnoreCheck: ['Helvetica'],
                fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '36', '48'],
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear', 'italic', 'strikethrough']],
                    ['para', ['ul', 'ol', 'paragraph', 'table', 'codeview']],
                    ['misc', ['undo', 'redo']]
                ]
            });
            $('.note-editable').css('font-size', '14px');
            $('.note-editable').css('font-family', 'Helvetica');
            $('.note-editable').css('line-height', '1.0');

            $('#MM-SAVE').click(function() {
                var MM_DATE = $('#MM-DATE').val();
                var MM_TIMESTARTED = $('#MM-TIMESTARTED').val();
                var MM_LOC = $('#MM-LOC').val();
                var MM_PRESIDER = $('#MM-PRESIDER').val();
                var MM_ATTENDEES = $('#MM-ATTENDEES').val();
                var MM_ABSENTEES = $('#MM-ABSENTEES').val();
                var MM_AGENDA = $('#MM-AGENDA').val();
                var MM_COMMENCEMENT = $('#MM-COMMENCEMENT').val();
                var MM_TIMEADJOURNED = $('#MM-TIMEADJOURNED').val();
                var MM_DOCS = $('#MM-DOCS').prop('files');
                var MM_SIGNATURE = $('#MM-SIGNATURE').val();
                var ID = $('#ID').val();
                var OrgCode = $('#OrgCode').val();
                var Created_By = $('#Created_By').val();
                var taskID = $('#taskID').val();
                var isFromTask = $('#isFromTask').val();
                var taskOrgCode = $('#taskOrgCode').val();

                if (MM_DATE == '' || MM_TIMESTARTED == '' || MM_LOC == '' || MM_PRESIDER == '' ||
                    MM_ATTENDEES == '' || MM_AGENDA == '' || MM_COMMENCEMENT ==
                    '' || MM_TIMEADJOURNED == '' || MM_SIGNATURE == '') {
                    Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {

                        }
                    }).fire({
                        icon: 'info',
                        title: 'Please fill up all the fields'
                    });
                    return;
                }

                if (MM_DOCS.length == 0) {
                    Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {

                        }
                    }).fire({
                        icon: 'info',
                        title: 'Please attach at least one document'
                    });
                }

                var formData = new FormData();
                formData.append('MM_DATE', MM_DATE);
                formData.append('MM_TIMESTARTED', MM_TIMESTARTED);
                formData.append('MM_LOC', MM_LOC);
                formData.append('MM_PRESIDER', MM_PRESIDER);
                formData.append('MM_ATTENDEES', MM_ATTENDEES);
                formData.append('MM_ABSENTEES', MM_ABSENTEES);
                formData.append('MM_AGENDA', MM_AGENDA);
                formData.append('MM_COMMENCEMENT', MM_COMMENCEMENT);
                formData.append('MM_TIMEADJOURNED', MM_TIMEADJOURNED);
                for (var i = 0; i < MM_DOCS.length; i++) {
                    formData.append('MM_DOCS[]', MM_DOCS[i]);
                }
                formData.append('MM_SIGNATURE', MM_SIGNATURE);
                formData.append('ID', ID);
                formData.append('OrgCode', OrgCode);
                formData.append('Created_By', Created_By);
                formData.append('taskID', taskID);
                formData.append('isFromTask', isFromTask);
                formData.append('taskOrgCode', taskOrgCode);

                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    didOpen: (toast) => {
                        Swal.showLoading();
                    }
                }).fire({
                    icon: 'info',
                    title: 'Saving Minutes of the Meeting'
                });

                $.ajax({
                    url: '../../../Functions/api/postMinutesOfTheMeeting.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        if (data.status == 'success') {
                            Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    localStorage.removeItem('taskID_MM');
                                    localStorage.removeItem('orgCODE_MM');
                                    $('#taskID').val('');
                                    $('#isFromTask').val('false');
                                    $('#TasksMessage').text(
                                        'No tasks found');
                                    $('#TaskIDis').text('N/A');
                                    getMM_Documents();
                                    $('#MM-CANCEL').click();

                                    $('#MM-Print').removeClass('d-none');
                                    $('#MM-Print').off('click').on('click',
                                        function() {
                                            window.open("../../../../" +
                                                data.filepaths, '_blank'
                                            );
                                        });

                                }
                            }).fire({
                                icon: 'success',
                                title: 'Minutes of the Meeting Saved'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occured while saving the Minutes of the Meeting',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    }
                });
            });

            $('#MM-CANCEL').click(function() {
                $('#ID').val('');
                $('#OrgCode').val('');
                $('#Created_By').val('');
                $('#taskID').val('');
                $('#isFromTask').val('false');
                $('#taskOrgCode').val('');
                $('#MM-DATE').val(
                    '<?php echo date('Y-m-d'); ?>'
                );
                $('#MM-TIMESTARTED').val(
                    '<?php echo date('H:i'); ?>'
                );
                $('#MM-LOC').val('');
                $('#MM-PRESIDER').val('');
                $('#MM-ATTENDEES').summernote('code', '');
                $('#MM-ABSENTEES').summernote('code', '');
                $('#MM-AGENDA').summernote('code', '');
                $('#MM-COMMENCEMENT').summernote('code', '');
                $('#MM-TIMEADJOURNED').val(
                    '<?php echo date('H:i'); ?>'
                );
                $('#MM-DOCS').val('');
                $('#MM-SIGNATURE').summernote('code', '');
                $('#MM-SAVE').text('Save');
            });
        });
    </script>
</body>

</html>