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
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Froalaeditor/css/froala_editor.pkgd.min.css">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Froalaeditor/css/themes/dark.css">

    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/AB_DBStyle.css">

    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script defer src="../../../../Utilities/Third-party/Datatable/js/datatables.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
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
                        <div class="card glass-default bg-opacity-25 mb-3">
                            <div class="card-body">
                                <h4 class="text-center fw-bold text-uppercase">Activity Proposal</h4>
                                <input type="hidden" id="ID" value="">
                                <input type="hidden" id="OrgCode" value="">
                                <input type="hidden" id="Created_By" value="">
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
                                            <label for="ActivityTitle" class="form-label">Activity Title</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityTitle">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityDateVenue" class="form-label">Activity Date and
                                                Venue</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityDateVenue">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityHead" class="form-label">Activity Head</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityHead">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="ActivityObjective" class="form-label">Activity Objective</label>
                                            <textarea class="form-control rounded-1" id="ActivityObjective"
                                                placeholder="Type the objective of the activity here"></textarea>
                                            <div class="form-text">Use bullet points or a numbered list</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityTarget" class="form-label">Activity Target
                                                Participants</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityTarget">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ActivityMechanics" class="form-label">Activity Mechanics</label>
                                            <input type="text" class="form-control rounded-0" id="ActivityMechanics">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="ActivityBudget" class="form-label">Activity Budget
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
                        <div class="card glass-default bg-opacity-25">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../../../Utilities/Third-party/Froalaeditor/js/froala_editor.pkgd.min.js"></script>
    <script>
        function getAP_Documents() {
            $.ajax({
                type: 'GET',
                url: '../../../Functions/api/getPreActivityProposal.php',
                success: function(response) {
                    if (response.status == 'success') {
                        $('#PreviousDocuments').empty();
                        if (response.data.length > 0) {
                            response.data.forEach(doc => {
                                var link = "../../../../" + doc.file_path;
                                $('#PreviousDocuments').append(`
                            <tr>
                                <td><a href="${link}" target="_blank" title="${doc.act_title}" class="text-decoration-none"><i class="fa fa-file-pdf-o"></i> View</a></td>
                                <td>${doc.date_Created}</td>
                                <td><a id="EditDocument_${doc.id}" style="cursor: pointer;" class="text-decoration-none"><i class="fa fa-edit"></i> Edit</a></td>
                            </tr>
                        `);

                                $(`#EditDocument_${doc.id}`).on('click', function() {
                                    $('#AdminName').val(doc.admin_name);
                                    $('#LetterTo').val(doc.dear_title);
                                    letterBodyEditor.html.set(doc.LetterBody);
                                    $('#ActivityTitle').val(doc.act_title);
                                    $('#ActivityDateVenue').val(doc.act_date_ven);
                                    $('#ActivityHead').val(doc.act_head);
                                    activityObjectiveEditor.html.set(doc.act_obj);
                                    $('#ActivityTarget').val(doc.act_participate);
                                    $('#ActivityMechanics').val(doc.act_mech);
                                    activityBudgetEditor.html.set(doc.act_budget);
                                    $('#ActivitySourceFunds').val(doc.act_funds);
                                    $('#ActivityOutcomes').val(doc.act_expectOut);
                                    $('#ID').val(doc.ID);
                                    $('#OrgCode').val(doc.org_code);
                                    $('#Created_By').val(doc.Created_By);
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
        getAP_Documents();
        var letterBodyEditor = new FroalaEditor('#LetterBody', {
            toolbarButtons: {
                'moreText': {
                    'buttons': ['bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript',
                        'fontFamily', 'fontSize', 'textColor', 'backgroundColor', 'clearFormatting'
                    ]
                },
                'moreParagraph': {
                    'buttons': ['alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'alignJustify',
                        'formatOL', 'formatUL', 'paragraphFormat', 'paragraphStyle', 'lineHeight'
                    ]
                },
                'moreRich': {
                    'buttons': ['insertLink', 'insertTable', 'insertHR']
                },
                'moreMisc': {
                    'buttons': ['undo', 'redo', 'spellChecker', 'selectAll']
                }
            },
            height: 350,
            imageUpload: false,
            imagePaste: false,
            imageAllowedTypes: [],
            charCounterCount: true,
            charCounterMax: 10000,
            toolbarSticky: true,
            quickInsertButtons: ['table', 'ul', 'ol', 'hr'],
            <?php echo $_SESSION['theme'] == 'dark' ? "theme: 'dark'," : ""; ?>
        });

        var activityObjectiveEditor = new FroalaEditor('#ActivityObjective', {
            toolbarButtons: {
                'moreText': {
                    'buttons': ['bold', 'italic', 'underline']
                },
                'moreParagraph': {
                    'buttons': ['formatOLSimple', 'formatOL', 'formatUL']
                },
                'moreRich': {
                    'buttons': ['insertLink', 'insertHR']
                },
                'moreMisc': {
                    'buttons': ['undo', 'redo', 'spellChecker', 'selectAll']
                }
            },
            height: 150,
            imageUpload: false,
            imagePaste: false,
            imageAllowedTypes: [],
            charCounterCount: true,
            charCounterMax: 5000,
            toolbarSticky: true,
            quickInsertButtons: ['ul', 'ol', 'hr'],
            <?php echo $_SESSION['theme'] == 'dark' ? "theme: 'dark'," : ""; ?>
        });

        FroalaEditor.DefineIcon('setBorder', {
            NAME: 'plus',
            SVG_KEY: 'add'
        });

        FroalaEditor.RegisterCommand('setBorder', {
            title: 'Set Border',
            focus: false,
            undo: true,
            refreshAfterCallback: true,
            callback: function() {
                this.selection.save();
                const tables = this.$el.get(0).getElementsByTagName('table');
                const cells = this.$el.get(0).getElementsByTagName('td');

                Array.from(cells).forEach(cell => {
                    cell.style.border = '1px solid black';
                });

                Array.from(tables).forEach(table => {
                    table.style.border = '1px solid black';
                    table.style.borderCollapse = 'collapse';
                });
                this.selection.restore();
            }
        });



        var activityBudgetEditor = new FroalaEditor('#ActivityBudget', {
            toolbarButtons: {
                'moreText': {
                    'buttons': ['bold', 'italic', 'underline', 'fontSize']
                },
                'moreParagraph': {
                    'buttons': ['formatOLSimple', 'formatOL', 'formatUL']
                },
                'moreRich': {
                    'buttons': ['insertLink', 'insertTable', 'setBorder']
                },
                'moreMisc': {
                    'buttons': ['undo', 'redo', 'spellChecker', 'selectAll']
                }
            },
            height: 150,
            imageUpload: false,
            imagePaste: false,
            imageAllowedTypes: [],
            charCounterCount: true,
            charCounterMax: 5000,
            toolbarSticky: true,
            tableStyles: {
                'table': 'table-border'
            },

            quickInsertButtons: ['table', 'ul', 'ol', 'hr'],
            <?php echo $_SESSION['theme'] == 'dark' ? "theme: 'dark'," : ""; ?>
        });

        $('#ClearFields').on('click', function() {
            $('#AdminName').val('');
            $('#LetterTo').val('');
            letterBodyEditor.html.set('');
            $('#ActivityTitle').val('');
            $('#ActivityDateVenue').val('');
            activityObjectiveEditor.html.set('');
            $('#ActivityTarget').val('');
            $('#ActivityMechanics').val('');
            activityBudgetEditor.html.set('');
            $('#ActivitySourceFunds').val('');
            $('#ActivityOutcomes').val('');
            $('#ActivityHead').val('');
            $('#PrintPreview').addClass('d-none');
            $('#ID').val('');
        });

        $('#GenerateLetter').on('click', function() {
            var ID = $('#ID').val();
            var OrgCode = $('#OrgCode').val();
            var Created_By = $('#Created_By').val();
            var AdminName = $('#AdminName').val();
            var LetterTo = $('#LetterTo').val();
            var LetterBody = letterBodyEditor.html.get();
            var ActivityTitle = $('#ActivityTitle').val();
            var ActivityDateVenue = $('#ActivityDateVenue').val();
            var ActivityObjective = activityObjectiveEditor.html.get();
            var ActivityTarget = $('#ActivityTarget').val();
            var ActivityMechanics = $('#ActivityMechanics').val();
            var ActivityBudget = activityBudgetEditor.html.get();
            var ActivityHead = $('#ActivityHead').val();
            var ActivitySourceFunds = $('#ActivitySourceFunds').val();
            var ActivityOutcomes = $('#ActivityOutcomes').val();

            if (AdminName == '' || LetterTo == '' || LetterBody == '' || ActivityTitle == '' ||
                ActivityDateVenue == '' || ActivityObjective == '' || ActivityTarget == '' ||
                ActivityMechanics == '' || ActivityBudget == '' || ActivityHead == '' || ActivitySourceFunds ==
                '' || ActivityOutcomes == '') {
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

            var data = {
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
                ActivityOutcomes: ActivityOutcomes
            };

            console.log(data);

            $.ajax({
                type: 'POST',
                url: '../../../Functions/api/postActivityProposal.php',
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
                                getAP_Documents();
                                $('#PrintPreview').removeClass('d-none');
                                $('#PrintPreview').on('click', function() {
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