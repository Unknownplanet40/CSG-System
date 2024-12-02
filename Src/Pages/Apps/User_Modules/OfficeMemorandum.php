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


$stmt = $conn->prepare("SELECT OM_No FROM officememorandomdocuments ORDER BY ID DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $OMNo = intval($row['OM_No']);
    $OMNo++;
} else {
    $OMNo = 1;
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
                                <h4 class="text-center fw-bold text-uppercase">Office Memorandum</h4>
                                <input type="hidden" id="ID" value="">
                                <input type="hidden" id="OrgCode" value="">
                                <input type="hidden" id="Created_By" value="">
                                <input type="hidden" id="taskID" value="">
                                <input type="radio" id="isFromTask" hidden value="false">
                                <input type="hidden" id="taskOrgCode" value="">

                                <div class="row mt-3 g-3">
                                    <div class="col-md-4">
                                        <label for="OM-No" class="form-label">Office Memorandum No.</label>
                                        <div class="hstack gap-3">
                                            <input type="text" class="form-control w-50" id="OM-No" placeholder="OM No."
                                                value="<?php echo $OMNo; ?>">
                                            <p class="form-label">
                                                <?php echo date('Y') - 1; ?>
                                                -
                                                <?php echo date('Y'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="OM-Date" class="form-label">Office Memorandum Date</label>
                                        <input type="date" class="form-control" id="OM-Date"
                                            value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="OM-TO" class="form-label">TO</label>
                                                <input type="text" class="form-control" id="OM-TO"
                                                    placeholder="e.g. Mr. John Doe">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="OM-TO-Position" class="form-label">Position</label>
                                                <input type="text" class="form-control" id="OM-TO-Position"
                                                    placeholder="e.g. Chief of Office">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="OM-FROM" class="form-label">FROM</label>
                                                <input type="text" class="form-control" id="OM-FROM"
                                                    placeholder="e.g. Mr. John Doe">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="OM-FROM-Position" class="form-label">Position</label>
                                                <input type="text" class="form-control" id="OM-FROM-Position"
                                                    placeholder="e.g. Chief of Office">
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="<?php echo $_SESSION['role'] == 1 ? 'col-md-6' : 'col-md-12'; ?>">
                                        <label for="OM-Subject" class="form-label">Subject</label>
                                        <input type="text" class="form-control" id="OM-Subject"
                                            placeholder="e.g. Office Memorandum Subject">
                                    </div>
                                    <div
                                        class="<?php echo $_SESSION['role'] == 1 ? 'col-md-6' : 'd-none'; ?>">
                                        <div class="mb-3">
                                            <label for="OM-ORG" class="form-label">Organization</label>
                                            <select class="form-select rounded-0" id="OM-ORG" required>
                                                <option value=<?php echo $_SESSION['role'] != 1 ? $_SESSION['org_Code'] : ''; ?>
                                                    selected disabled hidden>Select Organization</option>
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
                                        <label for="OM-Content" class="form-label">Content</label>
                                        <textarea class="form-control OM-textarea" id="OM-Content" rows="5"
                                            placeholder="e.g. Office Memorandum Content"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="OM-Signature" class="form-label">Signature</label>
                                        <textarea class="form-control OM-textarea" id="OM-Signature" rows="2"
                                            placeholder="e.g. Mr. John Doe"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                            <button class="btn btn-sm btn-success rounded-0 w-25"
                                                id="OM-Submit">Submit</button>
                                            <button class="btn btn-sm btn-danger rounded-0 w-25"
                                                id="OM-Clear">Clear</button>
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
                                        if (localStorage.getItem('taskID_OM') != null) {
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
                                                            'taskID_OM'));
                                                        $('#taskID').val(localStorage.getItem(
                                                            'taskID_OM'));
                                                        $('#taskOrgCode').val(localStorage.getItem(
                                                            'orgCODE_OM'));
                                                        $('#isFromTask').val('true');
                                                        $('#TasksMessage').text(
                                                            'You are currently editing a task');
                                                    }
                                                });
                                            } else {
                                                $('#ClearFields').click();
                                                $('#TaskIDis').text(localStorage.getItem('taskID_OM'));
                                                $('#taskID').val(localStorage.getItem('taskID_OM'));
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
                                                localStorage.removeItem('taskID_OM');
                                                localStorage.removeItem('orgCODE_OM');
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
        <script>
            $(document).ready(function() {
                $('.OM-textarea').summernote({
                    placeholder: 'Office Memorandum Content',
                    tabsize: 2,
                    height: 200,
                    disableResizeEditor: true,
                    fontNames: ['Helvetica'],
                    dialogsInBody: true,
                    dialogsFade: true,
                    disableDragAndDrop: true,
                    addDefaultFonts: false,
                    backgColor: 'transparent',
                    foreColor: 'black',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['fontname', ['fontname', 'fontsize']],
                        ['redo', ['redo']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['undo', ['undo']],
                        ['redo', ['redo']],
                        ['help', ['help']]
                    ]
                });

                $('.note-editable').css('font-size', '14px');
                $('.note-editable').css('font-family', 'Helvetica');
                $('.note-editable').css('line-height', '1.0');

                function getOM_Documents() {
                    $.ajax({
                        url: '../../../Functions/api/getOfficeMemorandum.php',
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
                                          data-bs-title="${item.OM_Sub} / ${item.DateCreated} / ${item.Created_By}" class="text-decoration-none texr-truncate"
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
                                            $('#OrgCode').val(item.org_Code);
                                            $('#Created_By').val(item.Created_By);
                                            $('#taskID').val("");
                                            $('#isFromTask').val("false");
                                            $('#taskOrgCode').val("");
                                            $('#TasksMessage').text(
                                                'No tasks found');
                                            $('#TaskIDis').text('N/A');

                                            $('#OM-No').val(item.OM_No);
                                            var formattedDate = new Date(item
                                                .OM_Date).toISOString().split(
                                                'T')[0];
                                            $('#OM-Date').val(formattedDate);
                                            $('#OM-TO').val(item.OM_To);
                                            $('#OM-TO-Position').val(item
                                                .OM_To_Position);
                                            $('#OM-FROM').val(item.OM_From);
                                            $('#OM-FROM-Position').val(item
                                                .OM_From_Position);
                                            $('#OM-Subject').val(item.OM_Sub);
                                            $('#OM-Content').summernote('code', item
                                                .OM_Body);
                                            $('#OM-Signature').summernote('code',
                                                item.OM_Signature);
                                            if ($('#OM-ORG').length) {
                                                $('#OM-ORG').val(item.org_Code);
                                            }

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

                getOM_Documents();

                if (localStorage.getItem('taskID_OM') != null) {
                    $('#taskID').val(localStorage.getItem('taskID_OM'));
                    $('#isFromTask').val('true');
                    $('#TasksMessage').text('You are currently editing a task');
                    $('#TaskIDis').text(localStorage.getItem('taskID_OM'));
                    $('#taskOrgCode').val(localStorage.getItem('orgCODE_OM'));
                    //localStorage.removeItem('taskID');
                } else {
                    $('#taskID').val('');
                    $('#isFromTask').val('false');
                    $('#RefreshTasks').addClass('d-none');
                    $('#ClearTasks').addClass('d-none');
                }


                $('#OM-Submit').click(function() {
                    var OMNo = $('#OM-No').val();
                    var OMDate = $('#OM-Date').val();
                    var OMTO = $('#OM-TO').val();
                    var OMTOPosition = $('#OM-TO-Position').val();
                    var OMFROM = $('#OM-FROM').val();
                    var OMFROMPosition = $('#OM-FROM-Position').val();
                    var OMSubject = $('#OM-Subject').val();
                    var OMContent = $('#OM-Content').summernote('code');
                    var OMSignature = $('#OM-Signature').summernote('code');
                    var OMORG = $('#OM-ORG').val();

                    var ID = $('#ID').val() || '';
                    var Created_By = $('#Created_By').val() || '';
                    var taskID = $('#taskID').val() || '';
                    var isFromTask = $('#isFromTask').val() || false;
                    var taskOrgCode = $('#taskOrgCode').val() || '';

                    var patterns = [
                        /background-color: var\(--bs-card-bg\); color: var\(--bs-body-color\);/g,
                        /background-color: var\(--bs-card-bg\);/g,
                        /color: rgb\(222, 226, 230\);/g
                    ];

                    var search = /font-size: 14px;/g;
                    var replace = 'font-size: 11px;';

                    function filterAndRemove(input, patterns) {
                        var output = input;
                        for (var i = 0; i < patterns.length; i++) {
                            output = output.replace(patterns[i],
                                ''); // Replace matched patterns with an empty string
                        }
                        return output;
                    }

                    function updateCssProperty(input, search, replace) {
                        return input.replace(search, replace); // Replace font-size as needed
                    }

                    if (typeof OMContent === 'string' && typeof OMSignature === 'string') {
                        OMContent = filterAndRemove(OMContent, patterns);
                        OMContent = updateCssProperty(OMContent, search, replace);

                        OMSignature = filterAndRemove(OMSignature, patterns);
                        OMSignature = updateCssProperty(OMSignature, search, replace);
                    } else {
                        console.error('OMContent or OMSignature is not properly initialized.');
                    }

                    var data = {
                        OMNo: OMNo,
                        OMDate: OMDate,
                        OMTO: OMTO,
                        OMTOPosition: OMTOPosition,
                        OMFROM: OMFROM,
                        OMFROMPosition: OMFROMPosition,
                        OMSubject: OMSubject,
                        OMContent: OMContent,
                        OMSignature: OMSignature,
                        OMORG: taskOrgCode != '' ? taskOrgCode : OMORG,
                        ID: ID,
                        Created_By: Created_By,
                        taskID: taskID,
                        isFromTask: isFromTask
                    };

                    if (OMNo == '' || OMDate == '' || OMTO == '' || OMTOPosition == '' || OMFROM ==
                        '' || OMFROMPosition == '' || OMSubject == '' || OMContent == '' ||
                        OMSignature == '' || OMORG == '') {
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'bg-dark text-body rounded-0',
                            },
                        }).fire({
                            icon: 'info',
                            title: 'Please fill up all fields'
                        });
                        return;
                    }

                    $.ajax({
                        url: '../../../Functions/api/postOfficeMemorandum.php',
                        type: 'POST',
                        data: data,
                        success: function(response) {
                            // if nothing is returned
                            if (response == '') {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 6000,
                                    timerProgressBar: true,
                                    customClass: {
                                        popup: 'bg-dark text-body rounded-0',
                                    },
                                }).fire({
                                    icon: 'warning',
                                    title: 'No response from server'
                                });
                                return;
                            }

                            if (response.status == 'success') {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 1000,
                                    timerProgressBar: true,
                                    customClass: {
                                        popup: 'bg-dark text-body rounded-0',
                                    },
                                }).fire({
                                    icon: 'success',
                                    title: response.message
                                }).then((result) => {
                                    getOM_Documents();
                                    $('#OM-Clear').click();
                                    localStorage.removeItem('taskID_OM');
                                    localStorage.removeItem('orgCODE_OM');
                                });
                            } else {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 1000,
                                    timerProgressBar: true,
                                    customClass: {
                                        popup: 'bg-dark text-body rounded-0',
                                    },
                                }).fire({
                                    icon: 'error',
                                    title: response.message
                                });
                            }
                        },

                        error: function(response) {
                            Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 6000,
                                timerProgressBar: true,
                                customClass: {
                                    popup: 'bg-dark text-body rounded-0',
                                },
                            }).fire({
                                icon: 'error',
                                title: 'Something went wrong : (' + response
                                    .status + ')'
                            });
                        }
                    });
                });

                $('#OM-Clear').click(function() {
                    $('#ID').val('');
                    $('#OrgCode').val('');
                    $('#Created_By').val('');
                    $('#taskID').val('');
                    $('#isFromTask').val('false');
                    $('#taskOrgCode').val('');
                    $('#TasksMessage').text('No tasks found');
                    $('#TaskIDis').text('N/A');

                    $('#OM-No').val('<?php echo $OMNo; ?>');
                    $('#OM-Date').val('<?php echo date('Y-m-d'); ?>');
                    $('#OM-TO').val('');
                    $('#OM-TO-Position').val('');
                    $('#OM-FROM').val('');
                    $('#OM-FROM-Position').val('');
                    $('#OM-Subject').val('');
                    $('#OM-Content').summernote('code', '');
                    $('#OM-Signature').summernote('code', '');
                    if ($('#OM-ORG').length) {
                        $('#OM-ORG').val('<?php echo $_SESSION['role'] != 1 ? $_SESSION['org_Code'] : ''; ?>');
                    }
                });
            });
        </script>
</body>

</html>