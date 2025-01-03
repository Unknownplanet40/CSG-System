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

$stmt = $conn->prepare("SELECT * FROM sysvenue");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$venues = [];

while ($row = $result->fetch_assoc()) {
    $venues[] = $row['ven_Name'];
}
echo '<script>var availableVenues = ' . json_encode($venues) . ';</script>';

$stmt = $conn->prepare("SELECT title FROM sysevents WHERE type = 'Event' AND isDeleted = 0 AND isEnded = 0");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row['title'];
}
echo '<script>var availableEvents = ' . json_encode($events) . ';</script>';

// org members

$stmt = $conn->prepare("SELECT * FROM usercredentials WHERE accountStat = 'active'");
$stmt->execute();
$result = $stmt->get_result();
$members = [];

while ($row = $result->fetch_assoc()) {
    if ($row['UUID'] == $_SESSION['UUID']) {
        continue;
    }

    if ($_SESSION['role'] != 1) {
        $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ? AND status = ? AND org_code = ?");
        $stmt->bind_param("sss", $row['UUID'], 'active', $_SESSION['org_Code']);
        $stmt->execute();
        $pos = $stmt->get_result();

        if ($pos->num_rows > 0) {
            $members[] = $row['First_Name'] . " " . $row['Last_Name'];
        }
        $stmt->close();
    } else {
        $members[] = $row['First_Name'] . " " . $row['Last_Name'];
    }
}
$stmt->close();
echo '<script>var availableMembers = ' . json_encode($members) . ';</script>';

function getOrgs($conn)
{

    if ($_SESSION['role'] == 1) {
        $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 0");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        echo '<option value="NULL" selected hidden>Select Organization</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['org_code'] . '">' . $row['org_name'] . '</option>';
        }
    } else {
        $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 0");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            if ($row['org_code'] == $_SESSION['org_Code']) {
                echo '<option value="' . $row['org_code'] . '" selected>' . $row['org_name'] . '</option>';
            }
        }
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
    <link rel="stylesheet" href="../../../../Utilities/Third-party/summernote/summernote-bs5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js">
    </script>
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
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label for="CON-LetterTo-FN" class="form-label">Recipient's First
                                                        Name</label>
                                                    <input type="text" class="form-control" id="CON-LetterTo-FN"
                                                        placeholder="Recipient's First Name" required
                                                        aria-describedby="CON-LetterTo-FN-err">
                                                    <div class="invalid-feedback" id="CON-LetterTo-FN-err"></div>

                                                </div>
                                                <div class="col-md-6">
                                                    <label for="CON-LetterTo-LN" class="form-label">Recipient's Last
                                                        Name</label>
                                                    <input type="text" class="form-control" id="CON-LetterTo-LN"
                                                        placeholder="Recipient's Last Name" required
                                                        aria-describedby="CON-LetterTo-LN-err">
                                                    <div class="invalid-feedback" id="CON-LetterTo-LN-err"></div>
                                                </div>
                                                <div
                                                    class="<?php echo $_SESSION['role'] == 1 ? 'col-md-4' : 'col-md-6'; ?>">
                                                    <label for="CON-position" class="form-label">Position</label>
                                                    <input type="text" class="form-control" id="CON-position"
                                                        placeholder="Recipient's Position" required
                                                        aria-describedby="CON-position-err">
                                                    <div class="invalid-feedback" id="CON-position-err"></div>
                                                    <div class="form-text">e.g. Dean, Director, etc.</div>
                                                </div>
                                                <div
                                                    class="<?php echo $_SESSION['role'] == 1 ? 'col-md-2' : 'col-md-6'; ?>">
                                                    <label for="CON-honorifics" class="form-label">Honorifics</label>
                                                    <input type="text" class="form-control" id="CON-honorifics"
                                                        placeholder="Honorifics" required
                                                        aria-describedby="CON-honorifics-err">
                                                    <div class="invalid-feedback text-nowrap" id="CON-honorifics-err">
                                                    </div>
                                                    <div class="form-text text-nowrap">e.g. Mr., Ms., Dr., etc.</div>
                                                </div>
                                                <div
                                                    class="col-md-6 <?php echo $_SESSION['role'] == 1 ? '' : 'd-none'; ?>">
                                                    <label for="CON-Organization"
                                                        class="form-label">Organization</label>
                                                    <select class="form-select" id="CON-Organization" required
                                                        aria-describedby="CON-Organization-err">
                                                        <?php getOrgs($conn); ?>
                                                    </select>
                                                    <div class="invalid-feedback text-center" id="CON-Organization-err">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="CON-participant" class="form-label">Participant</label>
                                                    <input type="text" class="form-control" id="CON-participant"
                                                        placeholder="Participant's Name" required
                                                        aria-describedby="CON-participant-err">
                                                    <div class="invalid-feedback text-center" id="CON-participant-err">
                                                    </div>
                                                    <div class="form-text">You can add a participant if you can't find
                                                        them. Just type it in, separated by a comma. (,)</div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="CON-Event" class="form-label">Event/Activity</label>
                                                    <input type="text" class="form-control" id="CON-Event"
                                                        placeholder="Event/Activity" required
                                                        aria-describedby="CON-Event-err">
                                                    <div class="invalid-feedback" id="CON-Event-err"></div>
                                                    <div class="form-text">If you can't find the event, type it in.
                                                    </div>
                                                    <script>
                                                        $(function() {
                                                            $("#CON-Event").autocomplete({
                                                                source: availableEvents,
                                                                minLength: 0,
                                                                autoFocus: true,
                                                                delay: 100,
                                                                select: function(event, ui) {
                                                                    event.preventDefault();
                                                                    var currentValue = $(this)
                                                                        .val();
                                                                    var newValue = currentValue ?
                                                                        currentValue + ", " + ui
                                                                        .item.value : ui.item.value;
                                                                    $(this).val(newValue);
                                                                }
                                                            });

                                                            $("#CON-Event").on("focus click", function() {
                                                                $(this).autocomplete("search", "");
                                                            });

                                                            $("#CON-Event").autocomplete("instance")
                                                                ._renderMenu = function(ul, items) {
                                                                    var that = this;
                                                                    items.forEach(function(item) {
                                                                        that._renderItemData(ul, item);
                                                                    });
                                                                    $(ul).addClass("ui-autocomplete-open");
                                                                };
                                                            $("#CON-participant").autocomplete({
                                                                source: availableMembers,
                                                                minLength: 0,
                                                                autoFocus: true,
                                                                delay: 100,
                                                                select: function(event, ui) {
                                                                    event.preventDefault();
                                                                    var currentValue = $(this)
                                                                        .val();
                                                                    var newValue = currentValue ?
                                                                        currentValue + ", " + ui
                                                                        .item.value : ui.item.value;
                                                                    $(this).val(newValue);
                                                                }
                                                            });
                                                            $("#CON-participant").on("focus click",
                                                                function() {
                                                                    $(this).autocomplete("search", "");
                                                                });
                                                            $("#CON-participant").autocomplete("instance")
                                                                ._renderMenu = function(ul, items) {
                                                                    var that = this;
                                                                    items.forEach(function(item) {
                                                                        that._renderItemData(ul, item);
                                                                    });
                                                                    $(ul).addClass("ui-autocomplete-open");
                                                                };

                                                        });
                                                    </script>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="CON-Date" class="form-label">Date</label>
                                                    <input type="text" class="form-control" id="CON-Date"
                                                        placeholder="Date" required aria-describedby="CON-Date-err">
                                                    <div class="invalid-feedback" id="CON-Date-err"></div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="CON-SchedStart" class="form-label">Start Time</label>
                                                    <input type="text" class="form-control" id="CON-SchedStart"
                                                        placeholder="Start Time" required
                                                        aria-describedby="CON-SchedStart-err">
                                                    <div class="invalid-feedback" id="CON-SchedStart-err"></div>
                                                    <div class="form-text text-nowrap">Please note that the time is in
                                                        24-hour format.</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="CON-SchedEnd" class="form-label">End Time</label>
                                                    <input type="text" class="form-control" id="CON-SchedEnd"
                                                        placeholder="End Time" required
                                                        aria-describedby="CON-SchedEnd-err">
                                                    <div class="invalid-feedback" id="CON-SchedEnd-err"></div>
                                                    <script>
                                                        $(function() {
                                                            $("#CON-Date").datetimepicker({
                                                                format: 'F j, Y',
                                                                timepicker: false,
                                                                scrollMonth: true,
                                                                theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                                                formatTime: 'h:i A',
                                                                mask: true,
                                                                validateOnBlur: true,
                                                                allowBlank: false,
                                                                closeOnDateSelect: true,
                                                                closeOnInputClick: true,
                                                                minDate: '<?php echo date('Y-m-d'); ?>',
                                                            });

                                                            $("#CON-SchedStart").datetimepicker({
                                                                format: 'g:i A', // 'g' ensures 12-hour without leading zero
                                                                datepicker: false,
                                                                step: 30,
                                                                theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                                                mask: false, // Turn off masking for debugging
                                                                validateOnBlur: false, // Prevent automatic adjustment
                                                                allowBlank: false,
                                                                closeOnDateSelect: true,
                                                                closeOnInputClick: true,
                                                            });

                                                            $("#CON-SchedEnd").datetimepicker({
                                                                format: 'g:i A',
                                                                datepicker: false,
                                                                step: 30, // Match step size for consistency
                                                                theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                                                mask: false,
                                                                validateOnBlur: false,
                                                                allowBlank: false,
                                                                closeOnDateSelect: true,
                                                                closeOnInputClick: true,
                                                            });

                                                            var currentdate = new Date();
                                                            var datetime = currentdate.getFullYear() + "-" + (
                                                                    currentdate.getMonth() + 1) + "-" +
                                                                currentdate.getDate() + " " + currentdate
                                                                .getHours() + ":" + currentdate.getMinutes();
                                                            $("#CON-Date").val(currentdate.toLocaleDateString(
                                                                'en-US', {
                                                                    month: 'long',
                                                                    day: 'numeric',
                                                                    year: 'numeric'
                                                                }));
                                                        });
                                                    </script>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="CON-Reason" class="form-label">Reasons</label>
                                                    <textarea class="form-control summernote" id="CON-Reason"
                                                        placeholder="Reasons" required
                                                        aria-describedby="CON-Reason-err"></textarea>
                                                    <div class="invalid-feedback" id="CON-Reason-err"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="CON-Signature" class="form-label">Signatures</label>
                                                    <textarea class="form-control summernote is-invalid"
                                                        id="CON-Signature" placeholder="Signatures" required
                                                        aria-describedby="CON-Signature-err"></textarea>
                                                    <div class="invalid-feedback" id="CON-Signature-err"></div>
                                                </div>
                                                <div class="col-md-12 my-3">
                                                    <div class="hstack gap-3">
                                                        <button
                                                            class="btn btn-sm btn-success w-50 rounded-0 fw-bold text-uppercase"
                                                            id="CON-Submit">Submit</button>
                                                        <button
                                                            class="btn btn-sm btn-secondary w-50 rounded-0 fw-bold text-uppercase"
                                                            id="Cons-Cancel">Cancel</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="Officers-tab-pane" role="tabpanel"
                                            aria-labelledby="Officers-tab">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label for="OFF-LetterTo-FN" class="form-label">Recipient's First
                                                        Name</label>
                                                    <input type="text" class="form-control" id="OFF-LetterTo-FN"
                                                        required placeholder="Recipient's First Name"
                                                        aria-describedby="OFF-LetterTo-FN-err">
                                                    <div class="invalid-feedback" id="OFF-LetterTo-FN-err"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="OFF-LetterTo-LN" class="form-label">Recipient's Last
                                                        Name</label>
                                                    <input type="text" class="form-control" id="OFF-LetterTo-LN"
                                                        required placeholder="Recipient's Last Name"
                                                        aria-describedby="OFF-LetterTo-LN-err">
                                                    <div class="invalid-feedback" id="OFF-LetterTo-LN-err"></div>
                                                </div>
                                                <div
                                                    class="<?php echo $_SESSION['role'] == 1 ? 'col-md-4' : 'col-md-6'; ?>">
                                                    <label for="OFF-position" class="form-label">Position</label>
                                                    <input type="text" class="form-control" id="OFF-position" required
                                                        placeholder="Recipient's Position"
                                                        aria-describedby="OFF-position-err">
                                                    <div class="invalid-feedback" id="OFF-position-err"></div>
                                                    <div class="form-text">e.g. Dean, Director, etc.</div>
                                                </div>
                                                <div
                                                    class="<?php echo $_SESSION['role'] == 1 ? 'col-md-2' : 'col-md-6'; ?>">
                                                    <label for="OFF-honorifics" class="form-label">Honorifics</label>
                                                    <input type="text" class="form-control" id="OFF-honorifics" required
                                                        placeholder="Honorifics" aria-describedby="OFF-honorifics-err">
                                                    <div class="invalid-feedback" id="OFF-honorifics-err"></div>
                                                    <div class="form-text text-nowrap">e.g. Mr., Ms., Dr., etc.</div>
                                                </div>
                                                <div
                                                    class="col-md-6 <?php echo $_SESSION['role'] == 1 ? '' : 'd-none'; ?>">
                                                    <label for="OFF-Organization"
                                                        class="form-label">Organization</label>
                                                    <select class="form-select" id="OFF-Organization" required
                                                        aria-describedby="OFF-Organization-err">
                                                        <?php getOrgs($conn); ?>
                                                    </select>
                                                    <div class="invalid-feedback text-center" id="OFF-Organization-err">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="OFF-Organizer" class="form-label">Organizer</label>
                                                    <input type="text" class="form-control" id="OFF-Organizer" required
                                                        placeholder="Organizer" aria-describedby="OFF-Organizer-err">
                                                    <div class="invalid-feedback" id="OFF-Organizer-err"></div>
                                                    <div class="form-text">e.g. CSG, Org, etc.</div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="OFF-participant" class="form-label">Participant</label>
                                                    <textarea class="form-control summernote" id="OFF-participant"
                                                        required placeholder="Participant's Name"
                                                        aria-describedby="OFF-participant-err">
                                                    </textarea>
                                                    <div class="invalid-feedback text-center" id="OFF-participant-err">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="OFF-Event" class="form-label">Event/Activity</label>
                                                    <input type="text" class="form-control" id="OFF-Event" required
                                                        placeholder="Event/Activity" aria-describedby="OFF-Event-err">
                                                    <div class="invalid-feedback" id="OFF-Event-err"></div>
                                                    <div class="form-text">If you can't find the event, type it in.
                                                    </div>
                                                    <script>
                                                        $(function() {
                                                            $("#OFF-Event").autocomplete({
                                                                source: availableEvents,
                                                                minLength: 0,
                                                                autoFocus: true,
                                                                delay: 100,
                                                                select: function(event, ui) {
                                                                    event.preventDefault();
                                                                    var currentValue = $(this)
                                                                        .val();
                                                                    var newValue = currentValue ?
                                                                        currentValue + ", " + ui
                                                                        .item.value : ui.item.value;
                                                                    $(this).val(newValue);
                                                                }
                                                            });

                                                            $("#OFF-Event").on("focus click", function() {
                                                                $(this).autocomplete("search", "");
                                                            });

                                                            $("#OFF-Event").autocomplete("instance")
                                                                ._renderMenu = function(ul, items) {
                                                                    var that = this;
                                                                    items.forEach(function(item) {
                                                                        that._renderItemData(ul, item);
                                                                    });
                                                                    $(ul).addClass("ui-autocomplete-open");
                                                                };
                                                        });
                                                    </script>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="OFF-Date" class="form-label">Date</label>
                                                    <input type="text" class="form-control" id="OFF-Date" required
                                                        placeholder="Date" aria-describedby="OFF-Date-err">
                                                    <div class="invalid-feedback" id="OFF-Date-err"></div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="OFF-SchedStart" class="form-label
                                                        ">Start Time</label>
                                                    <input type="text" class="form-control" id="OFF-SchedStart" required
                                                        placeholder="Start Time" aria-describedby="OFF-SchedStart-err">
                                                    <div class="invalid-feedback" id="OFF-SchedStart-err"></div>
                                                    <div class="form-text text-nowrap">Please note that the time is in
                                                        24-hour format.</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="OFF-SchedEnd" class="form-label">End Time</label>
                                                    <input type="text" class="form-control" id="OFF-SchedEnd" required
                                                        placeholder="End Time" aria-describedby="OFF-SchedEnd-err">
                                                    <div class="invalid-feedback" id="OFF-SchedEnd-err"></div>
                                                </div>
                                                <script>
                                                    $(function() {
                                                        $("#OFF-Date").datetimepicker({
                                                            format: 'F j, Y',
                                                            timepicker: false,
                                                            scrollMonth: true,
                                                            theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                                            formatTime: 'h:i A',
                                                            mask: true,
                                                            validateOnBlur: true,
                                                            allowBlank: false,
                                                            closeOnDateSelect: true,
                                                            closeOnInputClick: true,
                                                            minDate: '<?php echo date('Y-m-d'); ?>',
                                                        });

                                                        $("#OFF-SchedStart").datetimepicker({
                                                            format: 'g:i A',
                                                            datepicker: false,
                                                            step: 30,
                                                            theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                                            mask: false,
                                                            validateOnBlur: false,
                                                            allowBlank: false,
                                                            closeOnDateSelect: true,
                                                            closeOnInputClick: true,
                                                        });

                                                        $("#OFF-SchedEnd").datetimepicker({
                                                            format: 'g:i A',
                                                            datepicker: false,
                                                            step: 30,
                                                            theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                                            mask: false,
                                                            validateOnBlur: false,
                                                            allowBlank: false,
                                                            closeOnDateSelect: true,
                                                            closeOnInputClick: true,
                                                        });

                                                        var currentdate = new Date();
                                                        var datetime = currentdate.getFullYear() + "-" + (
                                                                currentdate.getMonth() + 1) + "-" +
                                                            currentdate.getDate() + " " + currentdate
                                                            .getHours() + ":" + currentdate.getMinutes();
                                                        $("#OFF-Date").val(currentdate.toLocaleDateString(
                                                            'en-US', {
                                                                month: 'long',
                                                                day: 'numeric',
                                                                year: 'numeric'
                                                            }));
                                                    });
                                                </script>
                                                <div class="col-md-12">
                                                    <label for="OFF-Reason" class="form-label">Reasons</label>
                                                    <textarea class="form-control summernote" id="OFF-Reason" required
                                                        placeholder="Reasons"
                                                        aria-describedby="OFF-Reason-err"></textarea>
                                                    <div class="invalid-feedback" id="OFF-Reason-err"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="OFF-Signature" class="form-label">Signatures</label>
                                                    <textarea class="form-control summernote" id="OFF-Signature"
                                                        required placeholder="Signatures"
                                                        aria-describedby="OFF-Signature-err"></textarea>
                                                    <div class="invalid-feedback" id="OFF-Signature-err"></div>
                                                </div>
                                                <div class="col-md-12 my-3">
                                                    <div class="hstack gap-3">
                                                        <button
                                                            class="btn btn-sm btn-success w-50 rounded-0 fw-bold text-uppercase"
                                                            id="OFF-Submit">Submit</button>
                                                        <button
                                                            class="btn btn-sm btn-secondary w-50 rounded-0 fw-bold text-uppercase"
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
                                <h5 class="text-center fw-bold text-uppercase">Requested Documents</h5>
                                <p class="text-center" id="TasksMessage">No tasks found</p>
                                <p>Task ID: <span id="TaskIDis">N/A</span></p>
                                <div class="hstack gap-2">
                                    <a href="javascript:void(0)" id="RefreshTasks"
                                        class="btn btn-sm btn-outline-secondary rounded-0 w-100">Refresh</a>
                                    <a href="javascript:void(0)" id="ClearTasks"
                                        class="btn btn-sm btn-outline-danger rounded-0 w-100">Clear</a>
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
                                                        if ($('#Constituents-tab').hasClass(
                                                                'active')) {
                                                            $('#Cons-Cancel').click();
                                                        } else {
                                                            $('#Off-Cancel').click();
                                                        }
                                                        $('#Cons-Cancel').click();
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
                                                $('#Cons-Cancel').click();
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
            <script>
                $(".summernote").summernote({
                    placeholder: 'You may adjust the content of the letter, provided that all the important details are included.',
                    tabsize: 2,
                    height: 350,
                    dialogsInBody: true,
                    disableResizeEditor: true,
                    fontNames: ['Helvetica'],
                    defaultFontName: 'Helvetica',
                    disableDragAndDrop: true,
                    disableResizeImage: true,
                    dialogsFade: true,
                    toolbar: [
                        ['table', ['table']],
                        ['font', ['bold', 'underline', 'clear', 'italic', 'strikethrough']],
                        ['fontname', ['fontsize', 'fontname']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['view', ['help', 'codeview', 'undo', 'redo']],
                    ],
                    maxCharCount: 8000,
                    callbacks: {
                        onKeydown: function(e) {
                            var t = e.currentTarget.innerText;
                            if (t.length >= 8000) {
                                if (e.keyCode != 8) {
                                    e.preventDefault();
                                    Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                    }).fire({
                                        icon: 'warning',
                                        title: 'You have reached the maximum character limit.'
                                    });
                                }
                            }
                        },
                    }
                });

                $(document).ready(function() {
                    function removeInvalidClassAndError(inputId) {
                        $(inputId).on('keyup change', function() {
                            if ($(inputId).hasClass('is-invalid')) {
                                $(inputId).removeClass('is-invalid');
                                $(inputId + '-err').text('');
                            }
                        });
                    }

                    function removeInvalidClassAndErrorForSummernote(inputId) {
                        $(inputId).on('summernote.change', function() {
                            if ($(inputId).hasClass('is-invalid')) {
                                $(inputId).removeClass('is-invalid');
                                $(inputId + '-err').text('');
                            }
                        });
                    }

                    function removeInvalidClassAndErrorForAutocomplete(inputId) {
                        $(inputId).on('autocompleteselect', function() {
                            if ($(inputId).hasClass('is-invalid')) {
                                $(inputId).removeClass('is-invalid');
                                $(inputId + '-err').text('');
                            }
                        });
                    }
                    const inputIds = [
                        '#CON-LetterTo-FN',
                        '#CON-LetterTo-LN',
                        '#CON-position',
                        '#CON-honorifics',
                        '#CON-Organization',
                        '#CON-participant',
                        '#CON-Event',
                        '#CON-Date',
                        '#CON-SchedStart',
                        '#CON-SchedEnd',
                        '#CON-Reason',
                        '#CON-Signature'
                    ];

                    const inputIdsOFF = [
                        '#OFF-LetterTo-FN',
                        '#OFF-LetterTo-LN',
                        '#OFF-position',
                        '#OFF-honorifics',
                        '#OFF-Organization',
                        '#OFF-Organizer',
                        '#OFF-participant',
                        '#OFF-Event',
                        '#OFF-Date',
                        '#OFF-SchedStart',
                        '#OFF-SchedEnd',
                        '#OFF-Reason',
                        '#OFF-Signature'
                    ];

                    inputIds.forEach(removeInvalidClassAndError);
                    removeInvalidClassAndErrorForSummernote('#CON-Reason');
                    removeInvalidClassAndErrorForSummernote('#CON-Signature');
                    removeInvalidClassAndErrorForAutocomplete('#CON-participant');
                    removeInvalidClassAndErrorForAutocomplete('#CON-Event');

                    inputIdsOFF.forEach(removeInvalidClassAndError);
                    removeInvalidClassAndErrorForSummernote('#OFF-Reason');
                    removeInvalidClassAndErrorForSummernote('#OFF-Signature');
                    removeInvalidClassAndErrorForAutocomplete('#OFF-participant');
                    removeInvalidClassAndErrorForAutocomplete('#OFF-Event');

                    $("#OFF-participant").summernote(
                        "code",
                        '<table class="table table-bordered"><tbody><tr><td>[participants name]</td><td><b>[participants positions]</b></td></tr><tr><td><br></td><td><br></td></tr><tr><td><br></td><td><br></td></tr></tbody></table>'
                    );

                    $('.note-editable').css('font-family', 'Helvetica');
                    $('.note-editable').css('line-height', '1.0');

                    if (localStorage.getItem('taskID_EL') != null) {
                        $('#taskID').val(localStorage.getItem('taskID_EL'));
                        $('#taskOrgCode').val(localStorage.getItem('taskOrgCode_EL'));
                        $('#isFromTask').val('true');
                        $('#TasksMessage').text('You are currently editing a task');
                        $('#TaskIDis').text(localStorage.getItem('taskID_EL'));

                        $.ajax({
                            url: '../../../../Functions/api/GetTaskDetails.php',
                            type: 'POST',
                            data: {
                                taskID: localStorage.getItem('taskID_EL')
                            },
                            success: function(response) {
                                if (response.status == 'success') {
                                    const data = response.data;
                                }
                            }
                        });
                    } else {
                        $('#taskID').val('');
                        $('#isFromTask').val('false');
                        $('#RefreshTasks').addClass('d-none');
                        $('#ClearTasks').addClass('d-none');
                    }

                    $('#Cons-Cancel').on('click', function() {
                        $('#OFF-Cancel').click();
                        $('#ID').val('');
                        $('#OrgCode').val('');
                        $('#Created_By').val('');
                        $('#taskID').val('');
                        $('#isFromTask').val('false');
                        $('#taskOrgCode').val('');
                        $('#TasksMessage').text('No tasks found');
                        $('#TaskIDis').text('N/A');
                        $('#CON-LetterTo-FN').val('');
                        $('#CON-LetterTo-LN').val('');
                        $('#CON-position').val('');
                        $('#CON-honorifics').val('');
                        $('#CON-Organization').val('NULL');
                        $('#CON-participant').val('');
                        $('#CON-Event').val('');
                        $('#CON-Date').val('');
                        $('#CON-SchedStart').val('');
                        $('#CON-SchedEnd').val('');
                        $('#CON-Reason').summernote('code', '');
                        $('#CON-Signature').summernote('code', '');
                        $('#PreviousDocuments').html(
                            '<tr><td colspan="3" class="text-center">Loading...</td></tr>');
                        $('#TasksMessage').text('No tasks found');
                        $('#RefreshTasks').addClass('d-none');
                        $('#ClearTasks').addClass('d-none');
                    });

                    $('#Off-Cancel').on('click', function() {
                        $('#Cons-Cancel').click();
                        $('#ID').val('');
                        $('#OrgCode').val('');
                        $('#Created_By').val('');
                        $('#taskID').val('');
                        $('#isFromTask').val('false');
                        $('#taskOrgCode').val('');
                        $('#TasksMessage').text('No tasks found');
                        $('#TaskIDis').text('N/A');
                        $('#OFF-LetterTo-FN').val('');
                        $('#OFF-LetterTo-LN').val('');
                        $('#OFF-position').val('');
                        $('#OFF-honorifics').val('');
                        $('#OFF-Organization').val('NULL');
                        $('#OFF-Organizer').val('');
                        $('#OFF-participant').summernote('code', '<table class="table table-bordered"><tbody><tr><td>[participants name]</td><td><b>[participants positions]</b></td></tr><tr><td><br></td><td><br></td></tr><tr><td><br></td><td><br></td></tr></tbody></table>');
                        $('#OFF-Event').val('');
                        $('#OFF-Date').val('');
                        $('#OFF-SchedStart').val('');
                        $('#OFF-SchedEnd').val('');
                        $('#OFF-Reason').summernote('code', '');
                        $('#OFF-Signature').summernote('code', '');
                        $('#PreviousDocuments').html(
                            '<tr><td colspan="3" class="text-center">Loading...</td></tr>');
                        $('#TasksMessage').text('No tasks found');
                        $('#RefreshTasks').addClass('d-none');
                        $('#ClearTasks').addClass('d-none');
                    });


                    $('#CON-Submit').on('click', function() {
                        var LetterToFN = $('#CON-LetterTo-FN').val();
                        var LetterToLN = $('#CON-LetterTo-LN').val();
                        var position = $('#CON-position').val();
                        var honorifics = $('#CON-honorifics').val();
                        var orgCode = $('#CON-Organization').val() == 'NULL' ? $('#OrgCode').val() : $(
                            '#CON-Organization').val();
                        var participant = $('#CON-participant').val();
                        var event = $('#CON-Event').val();
                        var date = $('#CON-Date').val();
                        var start = $('#CON-SchedStart').val();
                        var end = $('#CON-SchedEnd').val();
                        var reason = $('#CON-Reason').summernote('code');
                        var signature = $('#CON-Signature').summernote('code');
                        var taskID = $('#taskID').val();
                        var isFromTask = $('#isFromTask').val();
                        var taskOrgCode = $('#taskOrgCode').val();

                        if (LetterToFN == "" || LetterToLN == "" || position == "" || honorifics ==
                            "" ||
                            orgCode ==
                            "NULL" || participant == "" || event == "" || date == "" || start == "" ||
                            end ==
                            "" ||
                            reason == "" || signature == "") {
                            const fields = [{
                                    id: '#CON-LetterTo-FN',
                                    value: LetterToFN
                                },
                                {
                                    id: '#CON-LetterTo-LN',
                                    value: LetterToLN
                                },
                                {
                                    id: '#CON-position',
                                    value: position
                                },
                                {
                                    id: '#CON-honorifics',
                                    value: honorifics
                                },
                                {
                                    id: '#CON-Organization',
                                    value: orgCode,
                                    invalidValue: 'NULL'
                                },
                                {
                                    id: '#CON-participant',
                                    value: participant
                                },
                                {
                                    id: '#CON-Event',
                                    value: event
                                },
                                {
                                    id: '#CON-Date',
                                    value: date
                                },
                                {
                                    id: '#CON-SchedStart',
                                    value: start
                                },
                                {
                                    id: '#CON-SchedEnd',
                                    value: end
                                },
                                {
                                    id: '#CON-Reason',
                                    value: $('#CON-Reason').summernote('isEmpty') ? '' : 'filled'
                                },
                                {
                                    id: '#CON-Signature',
                                    value: $('#CON-Signature').summernote('isEmpty') ? '' : 'filled'
                                }
                            ];

                            let isValid = true;
                            fields.forEach(field => {
                                if (field.value === "" || field.value === field.invalidValue) {
                                    $(field.id).addClass('is-invalid');
                                    $(field.id + '-err').text('This field is required');
                                    if (isValid) {
                                        $('html, body').animate({
                                            scrollTop: $(field.id).offset().top - 100
                                        }, 100);
                                    }
                                    isValid = false;
                                }
                            });

                            if (!isValid) return;


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

                            if (typeof LetterBody === 'string' && typeof ActivityObjective ===
                                'string' &&
                                typeof ActivityBudget === 'string' && typeof ActivitySignature ===
                                'string') {
                                $('#CON-Reason').summernote('code', filterAndRemove(LetterBody,
                                    patterns));
                                $('#CON-Signature').summernote('code', filterAndRemove(
                                    ActivitySignature, patterns));

                                $('#CON-Reason').summernote('code', updateCssProperty($('#CON-Reason')
                                    .summernote(
                                        'code'), search, replace));
                                $('#CON-Signature').summernote('code', updateCssProperty($(
                                    '#CON-Signature').summernote(
                                    'code'), search, replace));
                            } else {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                }).fire({
                                    icon: 'error',
                                    title: 'Something went wrong. while we were trying to process the document.'
                                });
                            }
                            return;
                        }

                        if ($('#ID').val() != "") {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Finish Editing',
                                text: 'Would you like to finish editing this document?',
                                showCancelButton: true,
                                confirmButtonText: 'Yes',
                                cancelButtonText: 'No',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    submitExcuseLetter(LetterToFN, LetterToLN, position,
                                        honorifics, orgCode, participant, event, date,
                                        start, end, reason,
                                        signature, taskID, isFromTask, taskOrgCode);
                                }
                            });
                        } else {
                            if ($('#isFromTask').val() == 'true') {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Finilize Task',
                                    text: 'Would you like to finalize this task?',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes',
                                    cancelButtonText: 'No',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        submitExcuseLetter(LetterToFN, LetterToLN, position,
                                            honorifics, orgCode, participant, event, date,
                                            start, end, reason,
                                            signature, taskID, isFromTask, taskOrgCode);
                                    }
                                });
                            } else {
                                submitExcuseLetter(LetterToFN, LetterToLN, position, honorifics,
                                    orgCode, participant, event, date, start, end, reason,
                                    signature, taskID, isFromTask, taskOrgCode);
                            }
                        }
                    });

                    $('#OFF-Submit').on('click', function() {
                        var LetterToFN = $('#OFF-LetterTo-FN').val();
                        var LetterToLN = $('#OFF-LetterTo-LN').val();
                        var position = $('#OFF-position').val();
                        var honorifics = $('#OFF-honorifics').val();
                        var orgCode = $('#OFF-Organization').val() == 'NULL' ? $('#OrgCode').val() : $(
                            '#OFF-Organization').val();
                        var organizer = $('#OFF-Organizer').val();
                        var participant = $('#OFF-participant').summernote('code');
                        var event = $('#OFF-Event').val();
                        var date = $('#OFF-Date').val();
                        var start = $('#OFF-SchedStart').val();
                        var end = $('#OFF-SchedEnd').val();
                        var reason = $('#OFF-Reason').summernote('code');
                        var signature = $('#OFF-Signature').summernote('code');
                        var taskID = $('#taskID').val();
                        var isFromTask = $('#isFromTask').val();
                        var taskOrgCode = $('#taskOrgCode').val();

                        if (LetterToFN == "" || LetterToLN == "" || position == "" || honorifics ==
                            "" ||
                            orgCode ==
                            "NULL" || organizer == "" || participant == "" || event == "" || date ==
                            "" ||
                            start == "" || end == "" || reason == "" || signature == "") {
                            const fields = [{
                                    id: '#OFF-LetterTo-FN',
                                    value: LetterToFN
                                },
                                {
                                    id: '#OFF-LetterTo-LN',
                                    value: LetterToLN
                                },
                                {
                                    id: '#OFF-position',
                                    value: position
                                },
                                {
                                    id: '#OFF-honorifics',
                                    value: honorifics
                                },
                                {
                                    id: '#OFF-Organization',
                                    value: orgCode,
                                    invalidValue: 'NULL'
                                },
                                {
                                    id: '#OFF-Organizer',
                                    value: organizer
                                },
                                {
                                    id: '#OFF-participant',
                                    value: $('#OFF-participant').summernote('isEmpty') ? '' : 'filled'
                                },
                                {
                                    id: '#OFF-Event',
                                    value: event
                                },
                                {
                                    id: '#OFF-Date',
                                    value: date
                                },
                                {
                                    id: '#OFF-SchedStart',
                                    value: start
                                },
                                {
                                    id: '#OFF-SchedEnd',
                                    value: end
                                },
                                {
                                    id: '#OFF-Reason',
                                    value: $('#OFF-Reason').summernote('isEmpty') ? '' : 'filled'
                                },
                                {
                                    id: '#OFF-Signature',
                                    value: $('#OFF-Signature').summernote('isEmpty') ? '' : 'filled'
                                }
                            ];
                            return;
                        }

                        if ($('#ID').val() != "") {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Finish Editing',
                                text: 'Would you like to finish editing this document?',
                                showCancelButton: true,
                                confirmButtonText: 'Yes',
                                cancelButtonText: 'No',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    OFFsubmitExcuseLetter(LetterToFN, LetterToLN, position,
                                        honorifics, orgCode, organizer, participant,
                                        event, date, start, end, reason,
                                        signature, taskID, isFromTask, taskOrgCode);
                                }
                            });
                        } else {
                            if ($('#isFromTask').val() == 'true') {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Finilize Task',
                                    text: 'Would you like to finalize this task?',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes',
                                    cancelButtonText: 'No',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        OFFsubmitExcuseLetter(LetterToFN, LetterToLN, position,
                                            honorifics, orgCode, organizer, participant,
                                            event, date, start, end, reason,
                                            signature, taskID, isFromTask, taskOrgCode);
                                    }
                                });
                            } else {
                                OFFsubmitExcuseLetter(LetterToFN, LetterToLN, position, honorifics,
                                    orgCode, organizer, participant, event, date, start, end,
                                    reason, signature, taskID, isFromTask, taskOrgCode);
                            }
                        }

                    });

                    function loadPreviousDocuments() {
                        $.ajax({
                            url: '../../../Functions/api/getExcuseLetter.php',
                            type: 'GET',
                            success: function(response) {
                                if (response.status == 'success') {
                                    if (response.data.length > 0) {
                                        $('#PreviousDocuments').empty();
                                        response.data.forEach(doc => {
                                            var link = "../../../../" + doc.file_path;
                                            var date = new Date(doc.DateCreated);
                                            var formattedDate = date.toLocaleDateString(
                                                'en-US', {
                                                    month: 'short',
                                                    day: 'numeric',
                                                    year: 'numeric'
                                                });
                                            $('#PreviousDocuments').append(`
                                             <tr> 
                                             <td><a href="${link}" target="_blank" title="${doc.excuseLetterType} / ${formattedDate}" class="text-decoration-none"><i class="fa fa-file-pdf-o"></i> View</a></td>
                                             <td>${formattedDate}</td>
                                             <td><a id="EditDocument_${doc.ID}" style="cursor: pointer;" class="text-decoration-none"><i class="fa fa-edit"></i> Edit</a></td>
                                             </tr>`);

                                            $(`#EditDocument_${doc.ID}`).on('click',
                                                function() {
                                                    $('#ID').val(doc.ID);
                                                    $('#OrgCode').val(doc.OrgCode);
                                                    $('#Created_By').val(doc
                                                        .Created_By);
                                                    $('#taskID').val('');
                                                    $('#isFromTask').val('false');
                                                    $('#taskOrgCode').val('');
                                                    $('#TasksMessage').text(
                                                        'No tasks found');
                                                    $('#TaskIDis').text('N/A');

                                                    if (doc.excuseLetterType ==
                                                        'Excuse Letter for Constituents'
                                                    ) {
                                                        const constituentTab =
                                                            new bootstrap.Tab(document
                                                                .querySelector(
                                                                    '#Constituents-tab')
                                                            );
                                                        constituentTab.show();
                                                        $('#CON-LetterTo-FN').val(doc
                                                            .fname);
                                                        $('#CON-LetterTo-LN').val(doc
                                                            .lname);
                                                        $('#CON-position').val(doc
                                                            .Postition);
                                                        $('#CON-honorifics').val(doc
                                                            .honor);
                                                        $('#OrgCode').val(doc.org_code);
                                                        $('#CON-participant').val(doc
                                                            .participants);
                                                        $('#CON-Event').val(doc.Event);
                                                        $('#CON-Date').val(new Date(doc
                                                                .dateStart)
                                                            .toLocaleDateString(
                                                                'en-US', {
                                                                    month: 'long',
                                                                    day: 'numeric',
                                                                    year: 'numeric'
                                                                }));
                                                        $('#CON-SchedStart').val(
                                                            new Date(
                                                                '1970/01/01 ' + doc
                                                                .timeStart)
                                                            .toLocaleTimeString(
                                                                [], {
                                                                    hour: '2-digit',
                                                                    minute: '2-digit'
                                                                }));
                                                        $('#CON-SchedEnd').val(new Date(
                                                                '1970/01/01 ' + doc
                                                                .timeEnd)
                                                            .toLocaleTimeString(
                                                                [], {
                                                                    hour: '2-digit',
                                                                    minute: '2-digit'
                                                                }));
                                                        $('#CON-Reason').summernote(
                                                            'code',
                                                            doc.Reason);
                                                        $('#CON-Signature').summernote(
                                                            'code', doc.RecommSig);
                                                        $('html, body').animate({
                                                            scrollTop: 0
                                                        }, 100);
                                                    } else {
                                                        const officerTab = new bootstrap
                                                            .Tab(document.querySelector(
                                                                '#Officers-tab'));
                                                        officerTab.show();
                                                        $('#OFF-LetterTo-FN').val(doc
                                                            .fname);
                                                        $('#OFF-LetterTo-LN').val(doc
                                                            .lname);
                                                        $('#OFF-position').val(doc
                                                            .Postition);
                                                        $('#OFF-honorifics').val(doc
                                                            .honor);
                                                        $('#OrgCode').val(doc.org_code);
                                                        $('#OFF-Organizer').val(doc
                                                            .organizer);
                                                        $('#OFF-participant').summernote(
                                                            'code', doc
                                                            .participants);
                                                        $('#OFF-Event').val(doc.Event);
                                                        $('#OFF-Date').val(new Date(doc
                                                                .dateStart)
                                                            .toLocaleDateString(
                                                                'en-US', {
                                                                    month: 'long',
                                                                    day: 'numeric',
                                                                    year: 'numeric'
                                                                }));
                                                        $('#OFF-SchedStart').val(
                                                            new Date(
                                                                '1970/01/01 ' + doc
                                                                .timeStart)
                                                            .toLocaleTimeString(
                                                                [], {
                                                                    hour: '2-digit',
                                                                    minute: '2-digit'
                                                                }));
                                                        $('#OFF-SchedEnd').val(new Date(
                                                                '1970/01/01 ' + doc
                                                                .timeEnd)
                                                            .toLocaleTimeString(
                                                                [], {
                                                                    hour: '2-digit',
                                                                    minute: '2-digit'
                                                                }));
                                                        $('#OFF-Reason').summernote(
                                                            'code',
                                                            doc.Reason);
                                                        $('#OFF-Signature').summernote(
                                                            'code', doc.RecommSig);
                                                        $('html, body').animate({
                                                            scrollTop: 0
                                                        }, 100);

                                                    }
                                                });

                                        });
                                    } else {
                                        $('#PreviousDocuments').html(
                                            '<tr><td colspan="3" class="text-center">No previous documents found</td></tr>'
                                        );
                                    }
                                } else {
                                    $('#PreviousDocuments').html(
                                        '<tr><td colspan="3" class="text-center">No previous documents found</td></tr>'
                                    );
                                }
                            }
                        });
                    }

                    loadPreviousDocuments();

                    function submitExcuseLetter(LetterToFN, LetterToLN, position, honorifics, orgCode,
                        participant, event, date, start, end, reason, signature, taskID,
                        isFromTask, taskOrgCode) {
                        $.ajax({
                            url: '../../../Functions/api/postExcuseLetter.php',
                            type: 'POST',
                            data: {
                                docType: 'Constituents-ExcuseLetter',
                                LetterTo: LetterToFN + " " + LetterToLN,
                                position: position,
                                dear: honorifics + " " + LetterToLN,
                                Participants: participant,
                                DateStart: new Date(date).toISOString().split('T')[0],
                                StartTime: new Date('1970/01/01 ' + start).toTimeString().split(' ')[0],
                                DateEnd: new Date(date).toISOString().split('T')[0],
                                EndTime: new Date('1970/01/01 ' + end).toTimeString().split(' ')[0],
                                eventReason: event,
                                reason: reason,
                                Recommending: signature,
                                ID: $('#ID').val(),
                                OrgCode: $('#CON-Organization').val() == 'NULL' ? $('#OrgCode').val() :
                                    $('#CON-Organization').val(),
                                Created_By: $('#Created_By').val(),
                                taskID: taskID,
                                isFromTask: isFromTask,
                                HONORIFICS: honorifics,
                                FIRSTNAME: LetterToFN,
                                LASTNAME: LetterToLN,
                            },

                            beforeSend: function() {
                                $('#CON-Submit').attr('disabled', 'disabled');
                                Swal.mixin({
                                    toast: true,
                                    position: 'top',
                                    showConfirmButton: false,
                                    didOpen: (toast) => {
                                        Swal.showLoading();
                                    }
                                }).fire({
                                    icon: 'info',
                                    title: 'Processing Data'
                                });
                            },

                            success: function(data) {
                                Swal.close();
                                if (data.status == "success") {
                                    $('html, body').animate({
                                        scrollTop: 0
                                    }, 100);
                                    $('#Cons-Cancel').click();
                                    loadPreviousDocuments();
                                    $('#CON-Submit').removeAttr('disabled');
                                } else {
                                    Swal.mixin({
                                        toast: true,
                                        position: 'top',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                    }).fire({
                                        icon: 'error',
                                        title: data.message
                                    });
                                    $('#CON-Submit').removeAttr('disabled');
                                }
                            },

                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'An error occurred while processing the document.',
                                    text: 'Please try again later.',
                                });
                                $('#CON-Submit').removeAttr('disabled');
                            }
                        });
                    }

                    function OFFsubmitExcuseLetter(LetterToFN, LetterToLN, position, honorifics, orgCode,
                        organizer, participant, event, date, start, end, reason, signature, taskID,
                        isFromTask, taskOrgCode) {
                        $.ajax({
                            url: '../../../Functions/api/postExcuseLetter.php',
                            type: 'POST',
                            data: {
                                docType: 'Office-ExcuseLetter',
                                LetterTo: LetterToFN + " " + LetterToLN,
                                position: position,
                                dear: honorifics + " " + LetterToLN,
                                Organizer: organizer,
                                Participants: participant,
                                DateStart: new Date(date).toISOString().split('T')[0],
                                StartTime: new Date('1970/01/01 ' + start).toTimeString().split(' ')[0],
                                DateEnd: new Date(date).toISOString().split('T')[0],
                                EndTime: new Date('1970/01/01 ' + end).toTimeString().split(' ')[0],
                                eventReason: event,
                                reason: reason,
                                Recommending: signature,
                                ID: $('#ID').val(),
                                OrgCode: $('#OFF-Organization').val() == 'NULL' ? $('#OrgCode').val() :
                                    $('#OFF-Organization').val(),
                                Created_By: $('#Created_By').val(),
                                taskID: taskID,
                                isFromTask: isFromTask,
                                HONORIFICS: honorifics,
                                FIRSTNAME: LetterToFN,
                                LASTNAME: LetterToLN,
                                ORGANIZER: organizer
                            },

                            beforeSend: function() {
                                $('#OFF-Submit').attr('disabled', 'disabled');
                                Swal.mixin({
                                    toast: true,
                                    position: 'top',
                                    showConfirmButton: false,
                                    didOpen: (toast) => {
                                        Swal.showLoading();
                                    }
                                }).fire({
                                    icon: 'info',
                                    title: 'Processing Data'
                                });
                            },

                            success: function(data) {
                                Swal.close();
                                if (data.status == "success") {
                                    $('html, body').animate({
                                        scrollTop: 0
                                    }, 100);
                                    $('#Off-Cancel').click();
                                    loadPreviousDocuments();
                                    $('#OFF-Submit').removeAttr('disabled');
                                } else {
                                    Swal.mixin({
                                        toast: true,
                                        position: 'top',
                                        showConfirmButton: false,
                                        timer: 3000
                                    }).fire({
                                        icon: 'error',
                                        title: data.message
                                    });
                                    $('#OFF-Submit').removeAttr('disabled');
                                }
                            },
                        });
                    }
                });
            </script>
</body>

</html>