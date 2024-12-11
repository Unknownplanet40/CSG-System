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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/summernote/summernote-bs5.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script src="../../../../Utilities/Third-party/summernote/summernote-bs5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer type="module" src="../../../../Utilities/Scripts/BS_DBScript.js"></script>
    <title>Excuse Letter</title>
    <style>
        .nav-tabs .nav-link.active,
        .nav-tabs .nav-item.show .nav-link {
            color: var(--bs-body-color) !important;
            background-color: var(--bs-success-border-subtle) !important;
            font-weight: bold;
            text-transform: uppercase;
        }

        .nav-tabs .nav-link {
            color: rgba(var(--bs-body-color-rgb), 0.5) !important;
            font-weight: bold;
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
                <div class="row">
                    <div class="col-md-8">
                        <div class="card glass-default bg-opacity-25 mb-3 rounded-1">
                            <div class="card-body">
                                <h4 class="text-center fw-bold text-uppercase">Excuse Letter</h4>
                                <input type="hidden" id="ID" value="">
                                <input type="hidden" id="OrgCode" value="">
                                <input type="hidden" id="Created_By" value="">
                                <input type="hidden" id="taskID" value="">
                                <input type="radio" id="isFromTask" hidden value="false">
                                <input type="hidden" id="taskOrgCode" value="">
                                <div class="row g-3">
                                    <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="Constituents-tab" data-bs-toggle="tab"
                                                data-bs-target="#Constituents-tab-pane" type="button" role="tab"
                                                aria-controls="Constituents-tab-pane"
                                                aria-selected="true">Constituents</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="Officers-tab" data-bs-toggle="tab"
                                                data-bs-target="#Officers-tab-pane" type="button" role="tab"
                                                aria-controls="Officers-tab-pane" aria-selected="false">Organization
                                                Officers</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="Constituents-tab-pane"
                                            role="tabpanel" aria-labelledby="Constituents-tab">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Cons-LetterTo" class="form-label">Letter To</label>
                                                        <input type="text" class="form-control rounded-0"
                                                            id="Cons-LetterTo" placeholder="e.g. Dr. Juan Dela Cruz">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Cons-position" class="form-label">Position</label>
                                                        <input type="text" class="form-control rounded-0"
                                                            id="Cons-position" placeholder="e.g. Dean">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Cons-Dear" class="form-label">Dear</label>
                                                        <input type="text" class="form-control rounded-0" id="Cons-Dear"
                                                            placeholder="e.g. Sir/Madam">
                                                    </div>
                                                </div>
                                                <div
                                                    class="col-md-6 <?php echo $_SESSION['role'] == 1 ? '' : 'd-none'; ?>">
                                                    <div class="mb-3">
                                                        <label for="OrgCode_new" class="form-label">Organization</label>
                                                        <select id="OrgCode_new" class="form-select rounded-0">
                                                            <option value="0" selected hidden>Choose...</option>
                                                            <?php $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 0");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['org_code'] . '">' . $row['org_name'] . '</option>';
}?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="Cons-Participant"
                                                            class="form-label">Participants</label>
                                                        <input type="text" class="form-control rounded-0"
                                                            id="Cons-Participant"
                                                            placeholder="Enter Participants here...">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Cons-DateStart" class="form-label">Date
                                                            Start</label>
                                                        <input type="text" class="form-control rounded-0"
                                                            id="Cons-DateStart"
                                                            min="<?php echo date('Y-m-d'); ?>"
                                                            value="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                </div>
                                                <script>
                                                    $('#Cons-DateStart').datepicker({
                                                        dateFormat: 'yy-mm-dd',
                                                        minDate: '<?php echo date('Y-m-d'); ?>',
                                                        beforeShowDay: function(date) {
                                                            const dateString = $.datepicker.formatDate(
                                                                'yy-mm-dd', date);
                                                            const disabledRanges = [{
                                                                    start: '2024-12-07',
                                                                    end: '2024-12-10'
                                                                },
                                                                {
                                                                    start: '2024-12-14',
                                                                    end: '2024-12-15'
                                                                },
                                                                {
                                                                    start: '2024-12-21',
                                                                    end: '2024-12-25'
                                                                },
                                                                {
                                                                    start: '2024-12-28',
                                                                    end: '2024-12-30'
                                                                },
                                                            ];
                                                            for (const range of disabledRanges) {
                                                                if (dateString >= range.start &&
                                                                    dateString <= range.end) {
                                                                    return [false];
                                                                }
                                                            }
                                                            return [true];
                                                        }
                                                    });
                                                </script>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Cons-StartTime" class="form-label">Start
                                                            Time</label>
                                                        <input type="time" class="form-control rounded-0"
                                                            id="Cons-StartTime"
                                                            value="<?php echo date('H:i'); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Cons-DateEnd" class="form-label">Date End</label>
                                                        <input type="date" class="form-control rounded-0"
                                                            id="Cons-DateEnd"
                                                            value="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Cons-EndTime" class="form-label">End Time</label>
                                                        <input type="time" class="form-control rounded-0"
                                                            id="Cons-EndTime"
                                                            value="<?php echo date('H:i'); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="Cons-eventReason"
                                                            class="form-label">Event/Activity</label>
                                                        <textarea class="form-control rounded-0" id="Cons-eventReason"
                                                            rows="3"
                                                            placeholder="Enter Event or Activity here..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="Cons-reason" class="form-label">Reason</label>
                                                        <textarea class="form-control rounded-0 txtarea"
                                                            id="Cons-reason" rows="3"
                                                            placeholder="You may adjust the content of the letter, provided that all the important details are included."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="Cons-Recommending" class="form-label">Recommending
                                                            Approval of</label>
                                                        <textarea class="form-control rounded-0 txtarea"
                                                            id="Cons-Recommending" rows="3"
                                                            placeholder="Enter Recommending Approval of here..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 d-flex justify-content-center">
                                                    <div class="hstack gap-3">
                                                        <button class="btn btn-sm btn-secondary rounded-0 d-none"
                                                            id="Cons-Print">Print Document</button>
                                                        <button class="btn btn-sm btn-success rounded-0"
                                                            id="Cons-Create">Create Excuse Letter</button>
                                                        <button class="btn btn-sm btn-danger rounded-0"
                                                            id="Cons-Cancel">Cancel</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="Officers-tab-pane" role="tabpanel"
                                            aria-labelledby="Officers-tab">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Off-LetterTo" class="form-label">Letter To</label>
                                                        <input type="text" class="form-control rounded-0"
                                                            id="Off-LetterTo" placeholder="e.g. Dr. Juan Dela Cruz">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Off-position" class="form-label">Position</label>
                                                        <input type="text" class="form-control rounded-0"
                                                            id="Off-position" placeholder="e.g. Dean">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Off-Dear" class="form-label">Dear</label>
                                                        <input type="text" class="form-control rounded-0" id="Off-Dear"
                                                            placeholder="e.g. Sir/Madam">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Off-Organizer" class="form-label">Organized
                                                            By</label>
                                                        <input type="text" class="form-control rounded-0"
                                                            id="Off-Organizer" placeholder="e.g. PUP">
                                                    </div>
                                                </div>
                                                <div class="col-md-12 ">
                                                    <div class="mb-3">
                                                        <label for="OrgCode_new-off"
                                                            class="form-label">Organization</label>
                                                        <select id="OrgCode_new-off" class="form-select rounded-0">
                                                            <option value="0" selected hidden>Choose...</option>
                                                            <?php $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 0");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['org_code'] . '">' . $row['org_name'] . '</option>';
}?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="Off-Participant"
                                                            class="form-label">Participants</label>
                                                        <textarea class="form-control rounded-0 txtarea"
                                                            id="Off-Participant"
                                                            placeholder="Enter Participants here..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Off-DateStart" class="form-label
                                                            ">Date Start</label>
                                                        <input type="date" class="form-control rounded-0"
                                                            id="Off-DateStart"
                                                            value="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Off-StartTime" class="form-label">Start
                                                            Time</label>
                                                        <input type="time" class="form-control rounded-0"
                                                            id="Off-StartTime"
                                                            value="<?php echo date('H:i'); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Off-DateEnd" class="form-label">Date End</label>
                                                        <input type="date" class="form-control rounded-0"
                                                            id="Off-DateEnd"
                                                            value="<?php echo date('Y-m-d'); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="Off-EndTime" class="form-label">End Time</label>
                                                        <input type="time" class="form-control rounded-0"
                                                            id="Off-EndTime"
                                                            value="<?php echo date('H:i'); ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="Off-eventReason"
                                                            class="form-label">Event/Activity</label>
                                                        <textarea class="form-control rounded-0" id="Off-eventReason"
                                                            rows="3"
                                                            placeholder="Enter Event or Activity here..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="Off-reason" class="form-label">Reason</label>
                                                        <textarea class="form-control rounded-0 txtarea" id="Off-reason"
                                                            rows="3"
                                                            placeholder="You may adjust the content of the letter, provided that all the important details are included."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="Off-Recommending" class="form-label
                                                            ">Recommending Approval of</label>
                                                        <textarea class="form-control rounded-0 txtarea"
                                                            id="Off-Recommending" rows="3"
                                                            placeholder="Enter Recommending Approval of here..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 d-flex justify-content-center">
                                                    <div class="hstack gap-3">
                                                        <button class="btn btn-sm btn-secondary rounded-0 d-none"
                                                            id="Off-Print">Print Document</button>
                                                        <button class="btn btn-sm btn-success rounded-0"
                                                            id="Off-Create">Create Excuse Letter</button>
                                                        <button class="btn btn-sm btn-danger rounded-0"
                                                            id="Off-Cancel">Cancel</button>
                                                    </div>
                                                </div>
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
                                        if (localStorage.getItem('taskID_EL') != null) {
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
                                                        if ($('#Constituents-tab').hasClass('active')) {
                                                            $('#Cons-Cancel').click();
                                                        } else {
                                                            $('#Off-Cancel').click();
                                                        }
                                                        $('#ClearFields').click();
                                                        $('#TaskIDis').text(localStorage.getItem(
                                                            'taskID_EL'));
                                                        $('#taskID').val(localStorage.getItem(
                                                            'taskID_EL'));
                                                        $('#taskOrgCode').val(localStorage.getItem(
                                                            'orgCODE_EL'));
                                                        $('#isFromTask').val('true');
                                                        $('#TasksMessage').text(
                                                            'You are currently editing a task');
                                                    }
                                                });
                                            } else {
                                                $('#ClearFields').click();
                                                $('#TaskIDis').text(localStorage.getItem('taskID_EL'));
                                                $('#taskID').val(localStorage.getItem('taskID_EL'));
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
                                                localStorage.removeItem('taskID_EL');
                                                localStorage.removeItem('orgCODE_EL');
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
            <script>
                $(document).ready(function() {
                    function getEL_Documents() {
                        $.ajax({
                            url: '../../../Functions/api/getExcuseLetter.php',
                            type: 'GET',
                            data: {
                                docType: 'Constituents-ExcuseLetter'
                            },
                            success: function(data) {
                                if (data.status == 'success') {
                                    if (data.data.length === 0) {
                                        $('#PreviousDocuments').html(
                                            '<tr><td colspan="3" class="text-center">No Files yet</td></tr>'
                                        );
                                        return;
                                    }

                                    $('#PreviousDocuments').empty();
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
                                          data-bs-title="${item.Event} / ${item.DateCreated} / ${item.Created_By} / ${item.excuseLetterType} " class="text-decoration-none texr-truncate"
                                            style="max-width: 130px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                         ><i class="fa fa-file-pdf-o"></i> ${item.Event}</a></td>
                                         <td>${month} ${day}, ${year}</td>
                                         <td class="text-center"><a id="EditDocument_${item.ID}" style="cursor: pointer;" class="text-decoration-none"><i class="fa fa-edit text-primary"></i></a></td>
                                         </tr>`);

                                        const tooltipTriggerList = document
                                            .querySelectorAll('[data-bs-toggle="tooltip"]');
                                        tooltipTriggerList.forEach(el => new bootstrap
                                            .Tooltip(el));

                                        $(`#EditDocument_${item.ID}`).click(function() {
                                            if (item.excuseLetterType ==
                                                'Excuse Letter for Constituents') {
                                                $('#Constituents-tab').tab('show');
                                                $('#Cons-LetterTo').val(item
                                                    .letterTo);
                                                $('#Cons-position').val(item
                                                    .Postition);
                                                $('#Cons-Dear').val(item.Dear);
                                                $('#Cons-Participant').val(item
                                                    .participants);
                                                $('#Cons-DateStart').val(item
                                                    .dateStart);
                                                $('#Cons-StartTime').val(item
                                                    .timeStart);
                                                $('#Cons-DateEnd').val(item
                                                    .dateEnd);
                                                $('#Cons-EndTime').val(item
                                                    .timeEnd);
                                                $('#Cons-eventReason').val(item
                                                    .Event);
                                                $('#Cons-reason').summernote('code',
                                                    item.Reason);
                                                $('#Cons-Recommending').summernote(
                                                    'code', item.RecommSig);
                                                $('#OrgCode_new').attr('disabled',
                                                    'disabled');
                                            } else {
                                                $('#Officers-tab').tab('show');

                                                $('#Off-LetterTo').val(item
                                                    .letterTo);
                                                $('#Off-position').val(item
                                                    .Postition);
                                                $('#Off-Dear').val(item.Dear);
                                                $('#Off-Participant').summernote(
                                                    'code', item.participants);
                                                $('#Off-DateStart').val(item
                                                    .dateStart);
                                                $('#Off-StartTime').val(item
                                                    .timeStart);
                                                $('#Off-DateEnd').val(item
                                                    .dateEnd);
                                                $('#Off-EndTime').val(item
                                                    .timeEnd);
                                                $('#Off-eventReason').val(item
                                                    .Event);
                                                $('#Off-reason').summernote('code',
                                                    item.Reason);
                                                $('#Off-Recommending').summernote(
                                                    'code', item.RecommSig);
                                                $('#OrgCode_new-off').attr(
                                                    'disabled', 'disabled');
                                            }

                                            $('#ID').val(item.ID);
                                            $('#OrgCode').val(item.org_code);
                                            $('#Created_By').val(item.Created_By);
                                            $('#taskID').val("");
                                            $('#isFromTask').val("false");
                                            $('#taskOrgCode').val("");
                                            $('#TasksMessage').text(
                                                'No tasks found');
                                            $('#TaskIDis').text('N/A');

                                        });
                                    });
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

                    if (localStorage.getItem('taskID_EL') != null) {
                        $('#taskID').val(localStorage.getItem('taskID_EL'));
                        $('#isFromTask').val('true');
                        $('#TasksMessage').text('You are currently editing a task');
                        $('#TaskIDis').text(localStorage.getItem('taskID_EL'));
                        $('#taskOrgCode').val(localStorage.getItem('orgCODE_EL'));
                        //localStorage.removeItem('taskID');
                    } else {
                        $('#taskID').val('');
                        $('#isFromTask').val('false');
                        $('#RefreshTasks').addClass('d-none');
                        $('#ClearTasks').addClass('d-none');
                    }

                    getEL_Documents();

                    $('.txtarea').summernote({
                        placeholder: 'You may adjust the content of the letter, provided that all the important details are included.',
                        tabsize: 2,
                        height: 200,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'underline', 'clear', 'italic', 'strikethrough',
                                'superscript', 'subscript'
                            ]],
                            ['fontname', ['fontsize', 'fontname', 'height']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['view', ['help', 'codeview']],
                        ]
                    });

                    $('.note-editable').css('font-family', 'Helvetica');
                    $('.note-editable').css('line-height', '1.0');

                    $('#Off-Participant').summernote('code',
                        '<table class="table table-bordered"><tbody><tr><td><i>Officer Name</i></td><td><i>Officer Position</i></td></tr><tr><td><i>You can add more</i></td><td><i>&nbsp;table as needed</i></td></tr></tbody></table>'
                    );

                    $('#Cons-Create').click(function() {
                        var LetterTo = $('#Cons-LetterTo').val();
                        var position = $('#Cons-position').val();
                        var Dear = $('#Cons-Dear').val();
                        var Participants = $('#Cons-Participant').val();
                        var DateStart = $('#Cons-DateStart').val();
                        var StartTime = $('#Cons-StartTime').val();
                        var DateEnd = $('#Cons-DateEnd').val();
                        var EndTime = $('#Cons-EndTime').val();
                        var eventReason = $('#Cons-eventReason').val();
                        var reason = $('#Cons-reason').val();
                        var Recommending = $('#Cons-Recommending').val();
                        var ID = $('#ID').val() || '';
                        var OrgCode = $('#OrgCode').val();
                        if (!OrgCode) {
                            <?php if ($_SESSION['role'] == 1): ?>
                            OrgCode = $("#OrgCode_new").val();
                            <?php else: ?>
                            OrgCode =
                                '<?php echo $_SESSION['org_Code']; ?>';
                            <?php endif; ?>
                        }
                        var Created_By = $('#Created_By').val() || '';
                        var taskID = $('#taskID').val() || '';
                        var isFromTask = $('#isFromTask').val() || false;
                        var taskOrgCode = $('#taskOrgCode').val() || '';

                        if (Participants == "" || DateStart == "" || StartTime == "" || DateEnd == "" ||
                            EndTime == "" || eventReason == "" || reason == "" || Recommending == "" ||
                            LetterTo == "" || position == "" || Dear == "") {
                            Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            }).fire({
                                icon: 'info',
                                title: 'All fields are required'
                            });
                            return;
                        }

                        reason = reason.replace(/background-color: var\(--bs-card-bg\);/g, '');
                        Recommending = Recommending.replace(/background-color: var\(--bs-card-bg\);/g,
                            '');

                        $.ajax({
                            url: '../../../Functions/api/postExcuseLetter.php',
                            type: 'POST',
                            data: {
                                docType: 'Constituents-ExcuseLetter',
                                LetterTo: LetterTo,
                                position: position,
                                dear: Dear,
                                Participants: Participants,
                                DateStart: DateStart,
                                StartTime: StartTime,
                                DateEnd: DateEnd,
                                EndTime: EndTime,
                                eventReason: eventReason,
                                reason: reason,
                                Recommending: Recommending,
                                ID: ID,
                                OrgCode: $('#taskOrgCode').val() || OrgCode,
                                Created_By: Created_By,
                                taskID: taskID,
                                isFromTask: isFromTask
                            },
                            success: function(data) {
                                if (data.status == 'success') {
                                    Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                    }).fire({
                                        icon: 'success',
                                        title: data.message
                                    }).then(() => {
                                        getEL_Documents();
                                        $('#Cons-Cancel').click();
                                        localStorage.removeItem('taskID_EL');
                                        localStorage.removeItem('orgCODE_EL');
                                        $('#TasksMessage').text('No tasks found');
                                        $('#TaskIDis').text('N/A');
                                        $("#Cons-Print").off('click').on('click',
                                            function() {
                                                window.open("../../../../" +
                                                    data.file_path, '_blank'
                                                );
                                            });
                                        $('#Cons-Print').removeClass('d-none');
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: data.message,
                                    });
                                }
                            },

                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                });
                            }
                        });
                    });

                    $('#Off-Create').click(function() {
                        var LetterTo = $('#Off-LetterTo').val();
                        var position = $('#Off-position').val();
                        var Dear = $('#Off-Dear').val();
                        var Organizer = $('#Off-Organizer').val();
                        var Participants = $('#Off-Participant').val();
                        var DateStart = $('#Off-DateStart').val();
                        var StartTime = $('#Off-StartTime').val();
                        var DateEnd = $('#Off-DateEnd').val();
                        var EndTime = $('#Off-EndTime').val();
                        var eventReason = $('#Off-eventReason').val();
                        var reason = $('#Off-reason').val();
                        var Recommending = $('#Off-Recommending').val();
                        var ID = $('#ID').val() || '';
                        var OrgCode = $('#OrgCode').val();
                        if (!OrgCode) {
                            <?php if ($_SESSION['role'] == 1): ?>
                            OrgCode = $("#OrgCode_new-off").val();
                            <?php else: ?>
                            OrgCode =
                                '<?php echo $_SESSION['org_Code']; ?>';
                            <?php endif; ?>
                        }
                        var Created_By = $('#Created_By').val() || '';
                        var taskID = $('#taskID').val() || '';
                        var isFromTask = $('#isFromTask').val() || false;
                        var taskOrgCode = $('#taskOrgCode').val() || '';

                        if (Participants == "" || DateStart == "" || StartTime == "" || DateEnd == "" ||
                            EndTime == "" || eventReason == "" || reason == "" || Recommending == "" ||
                            LetterTo == "" || position == "" || Dear == "" || Organizer == "" ||
                            OrgCode == "") {
                            Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            }).fire({
                                icon: 'info',
                                title: 'All fields are required'
                            });
                            return;
                        }

                        reason = reason.replace(/background-color: var\(--bs-card-bg\);/g, '');
                        Recommending = Recommending.replace(/background-color: var\(--bs-card-bg\);/g,
                            '');

                        Participants = Participants.replace(
                            /table class="table table-bordered"><tbody>/g,
                            'table class="table table-bordered" style="border: 1px solid black; border-collapse: collapse;"><tbody>'
                        );
                        Participants = Participants.replace(/<td>/g,
                            '<td style="border: 1px solid black;">');

                        $.ajax({
                            url: '../../../Functions/api/postExcuseLetter.php',
                            type: 'POST',
                            data: {
                                docType: 'Officers-ExcuseLetter',
                                LetterTo: LetterTo,
                                position: position,
                                dear: Dear,
                                Organizer: Organizer,
                                Participants: Participants,
                                DateStart: DateStart,
                                StartTime: StartTime,
                                DateEnd: DateEnd,
                                EndTime: EndTime,
                                eventReason: eventReason,
                                reason: reason,
                                Recommending: Recommending,
                                ID: ID,
                                OrgCode: $('#taskOrgCode').val() || OrgCode,
                                Created_By: Created_By,
                                taskID: taskID,
                                isFromTask: isFromTask
                            },
                            success: function(data) {
                                if (data.status == 'success') {
                                    Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                    }).fire({
                                        icon: 'success',
                                        title: data.message
                                    }).then(() => {
                                        getEL_Documents();
                                        //$('#Off-Cancel').click();
                                        localStorage.removeItem('taskID_EL');
                                        localStorage.removeItem('orgCODE_EL');
                                        $('#TasksMessage').text('No tasks found');
                                        $('#TaskIDis').text('N/A');
                                        $("#Off-Print").off('click').on('click',
                                            function() {
                                                window.open("../../../../" +
                                                    data.file_path, '_blank'
                                                );
                                            });
                                        $('#Off-Print').removeClass('d-none');
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: data.message,
                                    });
                                }
                            },

                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                });
                            }
                        });
                    });

                    $('#Cons-Cancel').click(function() {
                        $('#Cons-LetterTo').val('');
                        $('#Cons-position').val('');
                        $('#Cons-Dear').val('');
                        $('#Cons-Participant').val('');
                        $('#Cons-DateStart').val(
                            '<?php echo date('Y-m-d'); ?>'
                        );
                        $('#Cons-StartTime').val(
                            '<?php echo date('H:i'); ?>'
                        );
                        $('#Cons-DateEnd').val(
                            '<?php echo date('Y-m-d'); ?>'
                        );
                        $('#Cons-EndTime').val(
                            '<?php echo date('H:i'); ?>'
                        );
                        $('#Cons-eventReason').val('');
                        $('#Cons-reason').summernote('code', '');
                        $('#Cons-Recommending').summernote('code', '');
                        $('#ID').val('');
                        $('#OrgCode').val('');
                        $('#Created_By').val('');
                        $('#taskID').val('');
                        $('#isFromTask').val('false');
                        $('#OrgCode_new').removeAttr('disabled');
                    });

                    $('#Off-Cancel').click(function() {
                        $('#Off-LetterTo').val('');
                        $('#Off-position').val('');
                        $('#Off-Dear').val('');
                        $('#Off-Participant').summernote('code', '');
                        $('#Off-DateStart').val(
                            '<?php echo date('Y-m-d'); ?>'
                        );
                        $('#Off-StartTime').val(
                            '<?php echo date('H:i'); ?>'
                        );
                        $('#Off-DateEnd').val(
                            '<?php echo date('Y-m-d'); ?>'
                        );
                        $('#Off-EndTime').val(
                            '<?php echo date('H:i'); ?>'
                        );
                        $('#Off-eventReason').val('');
                        $('#Off-reason').summernote('code', '');
                        $('#Off-Recommending').summernote('code', '');
                        $('#ID').val('');
                        $('#OrgCode').val('');
                        $('#Created_By').val('');
                        $('#taskID').val('');
                        $('#isFromTask').val('false');
                        $('#OrgCode_new-off').removeAttr('disabled');
                    });
                });
            </script>
</body>

</html>