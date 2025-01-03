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

?>

<!DOCTYPE html>
<html lang="en"
    data-bs-theme="<?php echo $_SESSION['theme']; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel='stylesheet'
        href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.css' />
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Datatable/css/datatables.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BGaniStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js">
    </script>
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

    <div class="modal fade" id="addEventModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-transparent border-0 rounded-1">
                <div class="modal-header border-0 glass-default bg-opacity-25 rounded-1">
                    <h1 class="modal-title fs-5">Add Event</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body glass-default bg-opacity-25">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-0" id="eventTitle"
                                        placeholder="Event Title" required>
                                    <label for="eventTitle">Event Title</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-0" id="eventLocation"
                                        placeholder="Event Location" required list="venues">
                                    <label for="eventLocation">Event Location</label>
                                    <datalist id="venues">
                                        <?php
                                        foreach ($venues as $venue) {
                                            echo '<option value="' . $venue . '">';
                                        }
                                        ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control rounded-0" id="eventDescription" cols="30" rows="5"
                                        placeholder="Event Description" required></textarea>
                                    <label for="eventDescription">Event Description</label>
                                </div>
                            </div>
                            <div class="col-2 position-relative mb-3">
                                <span
                                    class="position-absolute top-50 start-50 translate-middle text-white rounded-1 p-3 shadow"
                                    id="eventColorPreview"></span>
                            </div>
                            <div class="col-10">
                                <div class="form-floating mb-3">
                                    <select class="form-select rounded-0" id="eventColor" required>
                                        <option value="" selected disabled>Select Event Color</option>
                                        <option value="bs-indigo">Indigo</option>
                                        <option value="bs-purple">Purple</option>
                                        <option value="bs-pink">Pink</option>
                                        <option value="bs-red">Red</option>
                                        <option value="bs-orange">Orange</option>
                                        <option value="bs-yellow">Yellow</option>
                                        <option value="bs-green">Green</option>
                                        <option value="bs-teal">Teal</option>
                                    </select>
                                    <label for="eventColor">Event Color</label>
                                </div>
                                <script>
                                    $('#eventColor').on('change', function() {
                                        $('#eventColorPreview').removeClass().addClass(
                                            'position-absolute top-50 start-50 translate-middle text-white rounded-1 p-3 shadow'
                                        ).css('background-color', 'var(--' + this.value + ')');
                                    });
                                </script>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <!-- jquery datetime picker -->
                                    <input type="text" class="form-control rounded-0" id="eventStart" required
                                        aria-describedby="invalid-START valid-START">
                                    <label for="eventStart">Event Start</label>
                                    <div class="invalid-feedback" id="invalid-START">
                                        Fucking invalid Start Date
                                    </div>
                                    <div class="valid-feedback" id="valid-START">
                                        Fucking valid Start Date
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-0" id="eventEnd" required
                                        aria-describedby="invalid-END valid-END">
                                    <label for="eventEnd">Event End</label>
                                    <div class="invalid-feedback" id="invalid-END">
                                        Fucking invalid End Date
                                    </div>
                                    <div class="valid-feedback" id="valid-END">
                                        Fucking valid End Date
                                    </div>
                                </div>
                            </div>
                            <script>
                                $(function() {
                                    /* let events = [];
                                    $.ajax({
                                        url: '../../../Functions/api/datepickerData.php',
                                        type: 'GET',
                                        success: function(data) {
                                            if (data.status == 'success') {
                                                events = data.data;
                                                console.log(events);
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: 'An error occurred while fetching data 1',
                                                });
                                            }
                                        },
                                        error: function() {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: 'An error occurred while fetching data 2',
                                            });
                                        },
                                    }); */
                                    $('#eventStart').datetimepicker({
                                        format: 'Y-m-d H:i',
                                        timepicker: true,
                                        datepicker: true,
                                        step: 30,
                                        theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                        lang: 'en',
                                        showSecond: true,
                                        scrollMonth: false,
                                        scrollTime: false,
                                        closeOnDateSelect: false,
                                        closeOnTimeSelect: true,
                                        mask: true,
                                        minDate: '<?php echo date('Y-m-d H:i'); ?>',
                                        allowTimes: [
                                            '00:00', '00:30', '01:00', '01:30',
                                            '02:00', '02:30', '03:00', '03:30',
                                            '04:00', '04:30', '05:00', '05:30',
                                            '06:00', '06:30', '07:00', '07:30',
                                            '08:00', '08:30', '09:00', '09:30',
                                            '10:00', '10:30', '11:00', '11:30',
                                            '12:00', '12:30', '13:00', '13:30',
                                            '14:00', '14:30', '15:00', '15:30',
                                            '16:00', '16:30', '17:00', '17:30',
                                            '18:00', '18:30', '19:00', '19:30',
                                            '20:00', '20:30', '21:00', '21:30',
                                            '22:00', '22:30', '23:00', '23:30'
                                        ],
                                        className: 'rounded-1 border-0 shadow',
                                        onShow: function(ct) {
                                            this.setOptions({
                                                minDate: $('#eventStart').val() ? $(
                                                    '#eventStart').val() : false
                                            });
                                        }
                                        /* beforeShowDay: function(date) {
                                            let disabled = false;
                                            for (let i = 0; i < events.length; i++) {
                                                let start = new Date(events[i].start);
                                                let end = new Date(events[i].end);
                                                if (date.getTime() >= start.getTime() &&
                                                    date.getTime() <= end.getTime()) {
                                                    disabled = true;
                                                    break;
                                                }
                                            }
                                            return [!disabled, ''];
                                        }, */
                                    });

                                    $('#eventEnd').datetimepicker({
                                        format: 'Y-m-d H:i',
                                        timepicker: true,
                                        datepicker: true,
                                        step: 30,
                                        theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                        lang: 'en',
                                        showSecond: false,
                                        scrollMonth: false,
                                        closeOnDateSelect: false,
                                        closeOnTimeSelect: true,
                                        mask: true,
                                        minDate: '<?php echo date('Y-m-d H:i'); ?>',
                                        allowTimes: [
                                            '00:00', '00:30', '01:00', '01:30',
                                            '02:00', '02:30', '03:00', '03:30',
                                            '04:00', '04:30', '05:00', '05:30',
                                            '06:00', '06:30', '07:00', '07:30',
                                            '08:00', '08:30', '09:00', '09:30',
                                            '10:00', '10:30', '11:00', '11:30',
                                            '12:00', '12:30', '13:00', '13:30',
                                            '14:00', '14:30', '15:00', '15:30',
                                            '16:00', '16:30', '17:00', '17:30',
                                            '18:00', '18:30', '19:00', '19:30',
                                            '20:00', '20:30', '21:00', '21:30',
                                            '22:00', '22:30', '23:00', '23:30'
                                        ],
                                        className: 'rounded-1 border-0 shadow',
                                        onShow: function(ct) {
                                            this.setOptions({
                                                minDate: $('#eventStart').val() ? $(
                                                    '#eventStart').val() : false
                                            });
                                        }
                                    });

                                    $('#eventStart').on('change', function() {
                                        const selectedDate = new Date($('#eventStart').val());
                                        const currentDate = new Date();
                                        $('#eventEnd').removeClass('is-valid').removeClass('is-invalid')
                                            .val('');

                                        if (selectedDate < currentDate) {
                                            $('#eventStart').addClass('is-invalid');
                                            $('#invalid-START').text(
                                                'Cannot set event start date to past');
                                            setTimeout(() => {
                                                $('#eventStart').removeClass('is-invalid');
                                            }, 3000);
                                        } else {
                                            $('#eventStart').removeClass('is-invalid').addClass(
                                                'is-valid');
                                            const FullDate = new Date(selectedDate.getFullYear(),
                                                selectedDate.getMonth(), selectedDate.getDate(), 23,
                                                59, 59);
                                            $('#valid-START').text('Start Date: ' + selectedDate
                                                .toLocaleString('default', {
                                                    month: 'long'
                                                }) + ' ' + selectedDate.getDate() + ', ' +
                                                selectedDate.getFullYear() + ' ' + selectedDate
                                                .toLocaleString('default', {
                                                    hour: 'numeric',
                                                    minute: 'numeric',
                                                    hour12: true
                                                }));
                                        }
                                    });



                                    $('#eventEnd').on('change', function() {
                                        const selectedDate = new Date($('#eventEnd').val());
                                        const currentDate = new Date();
                                        const startDate = new Date($('#eventStart').val());

                                        if (selectedDate < currentDate) {
                                            $('#eventEnd').addClass('is-invalid');
                                            $('#invalid-END').text('Cannot set event end date to past');
                                            setTimeout(() => {
                                                $('#eventEnd').removeClass('is-invalid');
                                            }, 3000);

                                        } else if (selectedDate < startDate) {
                                            $('#eventEnd').addClass('is-invalid');
                                            $('#invalid-END').text(
                                                'End date must be greater than start date');
                                        } else {
                                            $('#eventEnd').removeClass('is-invalid').addClass(
                                                'is-valid');
                                            const FullDate = new Date(selectedDate.getFullYear(),
                                                selectedDate.getMonth(), selectedDate.getDate(), 23,
                                                59, 59);
                                            $('#valid-END').text('End Date: ' + selectedDate
                                                .toLocaleString('default', {
                                                    month: 'long'
                                                }) + ' ' + selectedDate.getDate() + ', ' +
                                                selectedDate.getFullYear() + ' ' + selectedDate
                                                .toLocaleString('default', {
                                                    hour: 'numeric',
                                                    minute: 'numeric',
                                                    hour12: true
                                                }));
                                        }
                                    });

                                });
                            </script>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 glass-default bg-opacity-25">
                    <input type="hidden" id="eventID">
                    <button type="button" class="btn btn-sm btn-outline-success rounded-0 d-none" id="EditEvent">Edit
                        Event</button>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-0" id="addEvent">Add
                        Event</button>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-0" id="ResetEvent">Reset
                        Fields</button>
                    <script>
                        $('#ResetEvent').on('click', function() {
                            $('#eventTitle').val('');
                            $('#eventLocation').val('');
                            $('#eventDescription').val('');
                            $('#eventColor').val('');
                            $('#eventColorPreview').removeClass().addClass(
                                'position-absolute top-50 start-50 translate-middle text-white rounded-1 p-3 shadow'
                            ).css('background-color', '');
                            $('#eventStart').val('');
                            $('#eventEnd').val('');
                            $('#eventID').val('');
                            $('#EditEvent').addClass('d-none');
                            $('#addEvent').removeClass('d-none');

                            $('#eventStart').removeClass('is-valid').removeClass('is-invalid');
                            $('#eventEnd').removeClass('is-valid').removeClass('is-invalid');
                            $('#eventColor').removeClass('is-valid').removeClass('is-invalid');
                        });
                    </script>
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
                <div class="card glass-default bg-opacity-10 border-0 p-3">
                    <div class="card-body">
                        <h5 class="card-title">Calendar Events</h5>
                    </div>
                    <table class="table table-hover table-striped table-responsive" id="EventTable">
                        <thead>
                            <tr>
                                <th scope="col" class="text-nowrap">ID</th>
                                <th scope="col" class="text-nowrap">Created By</th>
                                <th scope="col" class="text-nowrap">Title</th>
                                <th scope="col" class="text-nowrap">Desc</th>
                                <th scope="col" class="text-nowrap">Location</th>
                                <th scope="col" class="text-nowrap">Color</th>
                                <th scope="col" class="text-nowrap">Start/End</th>
                                <th scope="col" class="text-nowrap">Status</th>
                                <th scope="col" class="text-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("#EventTable").on("draw.dt", function() {
                const tooltipTriggerList = document.querySelectorAll(
                    '[data-bs-toggle="tooltip"]'
                );
                const tooltipList = [...tooltipTriggerList].map(
                    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
                );
                const popoverTriggerList = document.querySelectorAll(
                    '[data-bs-toggle="popover"]'
                );
                const popoverList = [...popoverTriggerList].map(
                    (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
                );
            });

            let eventURL = '../../../Functions/api/getCalEvents.php';

            $('#EventTable').DataTable({
                responsive: true,
                autoWidth: false,
                processing: true,
                pageLength: 5,
                order: [
                    [2, "desc"]
                ],
                layout: {
                    topStart: function() {
                        return $(
                            '<select id="utype" class="form-select form-select-sm border-success rounded-0"><option value="active">Events</option><option value="archived">Archived</option></select><button class="btn btn-sm btn-outline-success rounded-0 text-nowrap" data-bs-toggle="modal" data-bs-target="#addEventModal">Add Event</button>'
                        );
                    },
                    topEnd: {
                        search: {
                            placeholder: "Search Events",
                            className: "form-control form-control-sm",
                        },
                    },
                    bottomStart: {
                        info: true,
                    },
                },
                language: {
                    emptyTable: "No Events found.",
                    zeroRecords: "No matching Events found.",
                    info: "Showing _START_ to _END_ of _TOTAL_ Events",
                    infoEmpty: "Showing 0 to 0 of 0 Events",
                    infoFiltered: "(filtered from _MAX_ total Events)",
                    search: "_INPUT_",
                    searchPlaceholder: "Search Events",
                    lengthMenu: "Show _MENU_",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous",
                    },
                },
                columnDefs: [{
                        targets: [0],
                        visible: false,
                    },
                    {
                        targets: [2, 3, 5, 6, 7, -1],
                        orderable: false,
                    },
                ],
                ajax: {
                    url: eventURL,
                    type: 'GET',
                    dataSrc: 'data',
                    beforeSend: function() {
                        $('#EventTable').addClass('bg-opacity-50');
                    },

                    complete: function() {
                        $('#EventTable').removeClass('bg-opacity-50');
                    },

                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while fetching data',
                        });
                    },
                },

                columns: [{
                        data: 'id',
                    },
                    {
                        data: 'attendees',
                    },
                    {
                        data: 'title',
                        render: function(data, type, row) {
                            if (row.raw.eventType == 'Birthday') {
                                return "Birthday"
                            } else {
                                return row.title;
                            }
                        },
                    },
                    {
                        data: 'body',
                        render: function(data, type, row) {
                            let desc = data.length > 10 ?
                                data.substr(0, 10) + '...' :
                                data;
                            return `<span data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" data-bs-html="true" data-bs-title="${data}">${desc}</span>`;
                        },
                    },
                    {
                        data: 'location',
                        render: function(data, type, row) {
                            if (data == false) {
                                return "";
                            } else {
                                data.length > 10 ? data.substr(0, 10) + '...' : data;
                                return `<span data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" data-bs-html="true" data-bs-title="${data}">${data}</span>`;
                            }
                        },
                    },
                    {
                        data: 'backgroundColor',
                        render: function(data, type, row) {
                            return `<div style="width: 20px; height: 20px; background-color: var(--${data});" class="rounded-circle"></div>`;
                        },
                    },
                    {
                        data: 'start',
                        render: function(data, type, row) {
                            if (row.raw.eventType == 'Birthday') {
                                let start = new Date(data);
                                let startMonth = start.toLocaleString('default', {
                                    month: 'long'
                                });
                                let startDate = start.getDate();
                                let startYear = start.getFullYear();
                                return `${startMonth} ${startDate}, ${startYear}`;
                            } else {
                                if (new Date(row.start).getDate() == new Date(row.end)
                                    .getDate()) {
                                    let start = new Date(data);
                                    let startMonth = start.toLocaleString('default', {
                                        month: 'short'
                                    });
                                    let startDate = start.getDate();
                                    let startYear = start.getFullYear();
                                    let startTime = start.toLocaleString('default', {
                                        hour: 'numeric',
                                        minute: 'numeric',
                                        hour12: true
                                    });
                                    let end = new Date(row.end);
                                    let endTime = end.toLocaleString('default', {
                                        hour: 'numeric',
                                        minute: 'numeric',
                                        hour12: true
                                    });
                                    return `<span data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" data-bs-html="true" data-bs-title="${startMonth} ${startDate}, ${startYear}<br>Start: ${startTime}<br>End: ${endTime}">${startMonth} ${startDate} ${startTime} - ${endTime}</span>`;
                                } else {
                                    let start = new Date(data);
                                    let startMonth = start.toLocaleString('default', {
                                        month: 'short'
                                    });
                                    let startDate = start.getDate();
                                    let startYear = start.getFullYear();
                                    let startTime = start.toLocaleString('default', {
                                        hour: 'numeric',
                                        minute: 'numeric',
                                        hour12: true
                                    });
                                    let end = new Date(row.end);
                                    let endMonth = end.toLocaleString('default', {
                                        month: 'short'
                                    });
                                    let endDate = end.getDate();
                                    let endYear = end.getFullYear();
                                    let endTime = end.toLocaleString('default', {
                                        hour: 'numeric',
                                        minute: 'numeric',
                                        hour12: true
                                    });
                                    return `<span data-bs-toggle="tooltip" data-bs-placement="left" data-bs-trigger="hover" data-bs-html="true" data-bs-title="Start: ${startMonth} ${startDate}, ${startYear} ${startTime}<br>End: ${endMonth} ${endDate}, ${endYear} ${endTime}">${startMonth} ${startDate} to ${endMonth} ${endDate}</span>`;
                                }
                            }
                        },
                    },
                    {
                        data: 'raw.isEnded',
                        render: function(data, type, row) {
                            if (data == 1) {
                                return `<span class="badge bg-danger">Ended</span>`;
                            } else {
                                if (row.raw.eventType == 'Birthday') {
                                    return `<span class="badge bg-success">Birthday</span>`;
                                } else {
                                    let start = new Date(row.start);
                                    let end = new Date(row.end);
                                    let today = new Date();
                                    if (today > end) {
                                        return `<span class="badge bg-danger">Ended</span>`;
                                    } else if (today < start) {
                                        return `<span class="badge bg-success">Upcoming</span>`;
                                    } else {
                                        return `<span class="badge bg-warning">Ongoing</span>`;
                                    }
                                }
                            }
                        },
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            if (row.raw.eventType != 'Birthday') {
                                if (row.raw.isDeleted == 1) {
                                    return `<button class="btn btn-sm btn-outline-secondary border-0 bg-transparent text-secondary fs-5" id="restoreEvent" data-bs-toggle="tooltip" data-bs-placement="left" title="Restore Event"><i class="bi bi-arrow-clockwise"></i></button>`;
                                } else {
                                    return `<button class="btn btn-sm btn-outline-primary border-0 bg-transparent text-primary fs-5" id="editEvent"><i class="bi bi-pencil-fill"></i></button>
                            <button class="btn btn-sm btn-outline-danger border-0 bg-transparent text-danger fs-5" id="deleteEvent"><i class="bi bi-trash-fill"></i></button>`;
                                }
                            } else {
                                return '';
                            }
                        },
                    },
                ],

                initComplete: function() {
                    $("#EventTable").on('click', '#editEvent', function() {
                        let data = $('#EventTable').DataTable().row($(this).parents('tr'))
                            .data();
                        $('#eventID').val(data.id);
                        $('#eventTitle').val(data.title);
                        $('#eventLocation').val(data.location);
                        $('#eventDescription').val(data.body);
                        $('#eventColor').val(data.backgroundColor).trigger('change');
                        // covert Start and End Date to fit the datetimepicker
                        let start = new Date(data.start);
                        let end = new Date(data.end);
                        $('#eventStart').val(start.getFullYear() + '-' + ('0' + (start
                                    .getMonth() + 1)).slice(-2) + '-' + ('0' + start
                                    .getDate()).slice(-2) + ' ' + ('0' + start.getHours())
                                .slice(-2) + ':' + ('0' + start.getMinutes()).slice(-2))
                            .trigger('change');
                        $('#eventEnd').val(end.getFullYear() + '-' + ('0' + (end
                                .getMonth() + 1)).slice(-2) + '-' + ('0' + end
                                .getDate())
                            .slice(-2) + ' ' + ('0' + end.getHours()).slice(-2) + ':' +
                            ('0' + end.getMinutes()).slice(-2)).trigger('change');
                        $('#addEvent').addClass('d-none');
                        $('#EditEvent').removeClass('d-none');
                        $('#addEventModal').modal('show');

                    });

                    $("#EventTable").on('click', '#deleteEvent', function() {
                        let data = $('#EventTable').DataTable().row($(this).parents('tr'))
                            .data();
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'No, cancel!',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '../../../Functions/api/DeleteEvent.php',
                                    type: 'POST',
                                    data: {
                                        id: data.id,
                                        action: 'delete',
                                    },
                                    success: function(data) {
                                        if (data.status == 'success') {
                                            $('#EventTable').DataTable()
                                                .ajax.reload();
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: data
                                                    .message,
                                            });
                                        }
                                    },
                                    error: function() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'An error occurred while deleting event',
                                        });
                                    },
                                });
                            }
                        });
                    });

                    $("#EventTable").on('click', '#restoreEvent', function() {
                        let data = $('#EventTable').DataTable().row($(this).parents('tr'))
                            .data();

                        $.ajax({
                            url: '../../../Functions/api/DeleteEvent.php',
                            type: 'POST',
                            data: {
                                id: data.id,
                                action: 'restore',
                            },
                            success: function(data) {
                                if (data.status == 'success') {
                                    $('#EventTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message,
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while restoring event',
                                });
                            },
                        });
                    });

                    $('#utype').on('change', function() {
                        let value = $(this).val();
                        if (value == 'active') {
                            eventURL = '../../../Functions/api/getCalEvents.php';
                        } else {
                            eventURL = '../../../Functions/api/getCalEvents.php?archived=1';
                        }
                        $('#EventTable').DataTable().ajax.url(eventURL).load();
                    });
                },
            });

            $('#addEvent').on('click', function() {
                let title = $('#eventTitle').val();
                let location = $('#eventLocation').val();
                let description = $('#eventDescription').val();
                let color = $('#eventColor').val();
                let start = $('#eventStart').val();
                let end = $('#eventEnd').val();

                if (title == '' || location == '' || description == '' || color == '' || start == '' ||
                    end == '') {

                    const fields = ['eventTitle', 'eventLocation', 'eventDescription', 'eventColor',
                        'eventStart', 'eventEnd'
                    ];
                    let isValid = true;

                    fields.forEach(field => {
                        if ($(`#${field}`).val() == '') {
                            $(`#${field}`).addClass('is-invalid');
                            if (field == 'eventStart') {
                                $('#invalid-START').text('Please select a valid start date');
                            } else if (field == 'eventEnd') {
                                $('#invalid-END').text('Please select a valid end date');
                            }
                            setTimeout(() => {
                                $(`#${field}`).removeClass('is-invalid');
                            }, 3000);
                            isValid = false;
                        } else {
                            $(`#${field}`).removeClass('is-invalid').addClass('is-valid');
                            if (field == 'eventStart') {
                                $('#valid-START').text('');
                            } else if (field == 'eventEnd') {
                                $('#valid-END').text('');
                            }
                        }
                    });

                    if (!isValid) {
                        return;
                    }
                } else {
                    $.ajax({
                        url: '../../../Functions/api/postEvent.php',
                        type: 'POST',
                        data: {
                            UUID: UUID,
                            title: title,
                            location: location,
                            description: description,
                            color: color,
                            start: start,
                            end: end,
                        },
                        success: function(data) {
                            if (data.status == 'success') {
                                $('#EventTable').DataTable().ajax.reload();
                                $('#ResetEvent').trigger('click');
                                $('#addEventModal').modal('hide');
                            } else {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter',
                                            Swal.stopTimer)
                                        toast.addEventListener('mouseleave',
                                            Swal.resumeTimer)
                                    }
                                }).fire({
                                    icon: 'error',
                                    text: data.message,
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while adding event',
                            });
                        },
                    });
                }
            });

            $('#EditEvent').on('click', function() {
                let id = $('#eventID').val();
                let title = $('#eventTitle').val();
                let location = $('#eventLocation').val();
                let description = $('#eventDescription').val();
                let color = $('#eventColor').val();
                let start = $('#eventStart').val();
                let end = $('#eventEnd').val();

                if (title == '' || location == '' || description == '' || color == '' || start == '' ||
                    end == '') {

                    const fields = ['eventTitle', 'eventLocation', 'eventDescription', 'eventColor',
                        'eventStart', 'eventEnd'
                    ];
                    let isValid = true;

                    fields.forEach(field => {
                        if ($(`#${field}`).val() == '') {
                            $(`#${field}`).addClass('is-invalid');
                            if (field == 'eventStart') {
                                $('#invalid-START').text('Please select a valid start date');
                            } else if (field == 'eventEnd') {
                                $('#invalid-END').text('Please select a valid end date');
                            }
                            setTimeout(() => {
                                $(`#${field}`).removeClass('is-invalid');
                            }, 3000);
                            isValid = false;
                        } else {
                            $(`#${field}`).removeClass('is-invalid').addClass('is-valid');
                            if (field == 'eventStart') {
                                $('#valid-START').text('');
                            } else if (field == 'eventEnd') {
                                $('#valid-END').text('');
                            }
                        }
                    });

                    if (!isValid) {
                        return;
                    }
                } else {
                    $.ajax({
                        url: '../../../Functions/api/EditEvent.php',
                        type: 'POST',
                        data: {
                            id: id,
                            UUID: UUID,
                            title: title,
                            location: location,
                            description: description,
                            color: color,
                            start: start,
                            end: end,
                        },
                        success: function(data) {
                            if (data.status == 'success') {
                                $('#EventTable').DataTable().ajax.reload();
                                $('#ResetEvent').trigger('click');
                                $('#addEventModal').modal('hide');
                            } else {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter',
                                            Swal.stopTimer)
                                        toast.addEventListener('mouseleave',
                                            Swal.resumeTimer)
                                    }
                                }).fire({
                                    icon: 'error',
                                    text: data.message,
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while editing event',
                            });
                        },
                    });
                }
            });
        });
    </script>
</body>

</html>