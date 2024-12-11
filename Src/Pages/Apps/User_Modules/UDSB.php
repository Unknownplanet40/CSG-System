<?php
$current_page = basename($_SERVER['PHP_SELF']);
$Dashboard = "../ADMIN/Dashboard.php";
$OfficerDashboard = "./Dashboard.php";
$DocumentHeader = "./DocumentHeader.php";
$Modules = "#Modules";
$ActivityProposal = "./ActivityProposal.php";
$ExcuseLetter = "./ExcuseLetter.php";
$MinutesOfTheMeeting = "./MinutesOfTheMeeting.php";
$OfficeMemorandum = "./OfficeMemorandum.php";
$ProjectProposal = "./ProjectProposal.php";
$Feed = "../../Feed.php";
$Preference = "../../Preference.php";
$enableDocumentHeader = false;
$createAccount = "./CreateAccount.php";

if ($_SESSION['role'] == 1) {
    $enableDocumentHeader = true;
} elseif ($_SESSION['role'] != 1) {
    if ($_SESSION['org_position'] == 1 || $_SESSION['org_position'] == 2 || $_SESSION['org_position'] == 3) {
        $enableDocumentHeader = true;
    }
}


?>

<div class="d-flex flex-column justify-content-between h-100">
    <div class="container text-center my-2">
        <?php if ($_SESSION['ProfileImage'] == "Default-Profile.gif") {?>
        <img src="../../../../Assets/Images/Default-Profile.gif" class="rounded-circle img-fluid border border-3 mt-2"
            alt="Profile Picture" width="84" height="84">
        <?php } else {?>
        <img src="../../../../Assets/Images/UserProfiles/<?php echo $_SESSION['ProfileImage']?>"
            class="rounded-circle img-fluid border border-3 mt-2" alt="Profile Picture" width="84" height="84">
        <?php }?>
        <div class="vstack gap-0 mt-1">
            <p class="lead fw-bold text-truncate mb-0">
                <?php echo $_SESSION['FirstName'] . ' ' . $_SESSION['LastName']; ?>
            </p>
            <small class="text-secondary text-uppercase fw-bold">
                <?php

                            if ($_SESSION['role'] != 1) {
                                $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = '" . $_SESSION['org_Code'] . "'");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $stmt->close();
                                $org = $result->fetch_assoc();

                                if ($_SESSION['org_position'] == 1) {
                                    echo $org['org_short_name'] . ' President';
                                } elseif ($_SESSION['org_position'] == 2) {
                                    echo $org['org_short_name'] . ' Vice President Internal';
                                } elseif ($_SESSION['org_position'] == 3) {
                                    echo $org['org_short_name'] . ' Vice President External';
                                } else {
                                    echo $org['org_short_name'] . ' Secretary';
                                }

                                $stmt = $conn->prepare("SELECT isSubOrg FROM userpositions WHERE UUID = '" . $_SESSION['UUID'] . "'");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $stmt->close();
                                $isSubOrg = $result->fetch_assoc();
                            } else {
                                echo 'Administrator';
                            }?>
            </small>
        </div>
    </div>

    <div class="container">
        <ul class="list-group">
            <li class="list-group-item lg my-2 <?php echo ($_SESSION['role'] == 1) ? '' : (($_SESSION['org_position'] == 1 || $_SESSION['org_position'] == 2 || $_SESSION['org_position'] == 3) ? '' : 'd-none'); ?>
            <?php echo $isSubOrg['isSubOrg'] == 1 ? 'd-none' : ''; ?>"
                onclick="window.location.href = '../ADMIN/Dashboard.php'">
                <svg class="me-3" width="24" height="24">
                    <use xlink:href="#Dashboard" />
                </svg>
                Dashboard
            </li>
            <span
                class="hr-divider-start text-secondary <?php echo $isSubOrg['isSubOrg'] == 1 ? 'd-none' : ''; ?>"></span>
            <li class="list-group-item lg <?php echo $current_page == 'Dashboard.php' ? 'lg-active' : ''; ?>"
                <?php echo $current_page != 'Dashboard.php' ? "onclick='window.location.href = \"$OfficerDashboard\"'" : ''; ?>>
                <svg class="me-3" width="24" height="24">
                    <use xlink:href="#Dashboard" />
                </svg>
                Records
            </li>
            <li class="list-group-item lg my-2 <?php echo $current_page == 'DocumentHeader.php' ? 'lg-active' : ''; ?> <?php echo $enableDocumentHeader ? '' : 'd-none'; ?>"
                <?php echo $current_page != 'DocumentHeader.php' ? "onclick='window.location.href = \"$DocumentHeader\"'" : ''; ?>>
                <svg class="me-3" width="24" height="24">
                    <use xlink:href="#DocHeader" />
                </svg>
                Document Header
            </li>
            <li data-bs-toggle="modal" data-bs-target="#CreateUSERACCOUNT" <?php echo $current_page != 'CreateAccount.php' ? "onclick='window.location.href = \"$createAccount\"'" : ''; ?>
                class="list-group-item lg my-2 <?php echo $current_page == 'CreateAccount.php' ? 'lg-active' : ''; ?> <?php echo $enableDocumentHeader ? '' : 'd-none'; ?> <?php echo ($_SESSION['role'] == 1) ? '' : (($_SESSION['org_position'] == 1 ) ? '' : 'd-none'); ?>">
                <svg class="me-3" width="24" height="24">
                    <use xlink:href="#ManageAct" />
                </svg>
                Create User
            </li>
            <span class="hr-divider-start text-secondary d-none"></span>
            <div class="accordion accordion-flush" id="Modules_Accord">
                <div class="accordion-item bg-transparent border-0">
                    <li class="list-group-item lg <?php echo $current_page == 'ActivityProposal.php' || $current_page == 'ExcuseLetter.php' || $current_page == 'MinutesOfTheMeeting.php' || $current_page == 'OfficeMemorandum.php' || $current_page == 'ProjectProposal.php' ? 'collapsed' : ''; ?>"
                        type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"
                        aria-expanded="false" aria-controls="flush-collapseOne">
                        <svg class="me-3" width="24" height="24">
                            <use xlink:href="#Folder" />
                        </svg>
                        Documents
                    </li>
                    <div id="flush-collapseOne" class="accordion-collapse collapse <?php echo $current_page == 'ActivityProposal.php' || $current_page == 'ExcuseLetter.php' || $current_page == 'MinutesOfTheMeeting.php' || $current_page == 'OfficeMemorandum.php' || $current_page == 'ProjectProposal.php' ? 'show' : ''; ?>
                    " data-bs-parent="#Modules_Accord">
                        <div class="accordion-body">
                            <li class="list-group-item lg mb-2 <?php echo $current_page == 'ActivityProposal.php' ? 'lg-active' : ''; ?>"
                                <?php echo $current_page != 'ActivityProposal.php' ? "onclick='window.location.href = \"$ActivityProposal\"'" : ''; ?>>
                                <svg class="me-3" width="24" height="24">
                                    <use xlink:href="#Documents" />
                                </svg>
                                Activity Proposal
                            </li>
                            <li class="list-group-item lg mb-2 <?php echo $current_page == 'ExcuseLetter.php' ? 'lg-active' : ''; ?>"
                                <?php echo $current_page != 'ExcuseLetter.php' ? "onclick='window.location.href = \"$ExcuseLetter\"'" : ''; ?>>
                                <svg class="me-3" width="24" height="24">
                                    <use xlink:href="#Documents" />
                                </svg>
                                Excuse Letter
                            </li>
                            <li class="list-group-item lg my-2 <?php echo $current_page == 'MinutesOfTheMeeting.php' ? 'lg-active' : ''; ?>"
                                <?php echo $current_page != 'MinutesOfTheMeeting.php' ? "onclick='window.location.href = \"$MinutesOfTheMeeting\"'" : ''; ?>
                                title="Minutes of the Meeting">
                                <svg class="me-3" width="24" height="24">
                                    <use xlink:href="#Documents" />
                                </svg>
                                Minutes of the Meeting
                            </li>
                            <li class="list-group-item lg my-2 <?php echo $current_page == 'OfficeMemorandum.php' ? 'lg-active' : ''; ?>"
                                <?php echo $current_page != 'OfficeMemorandum.php' ? "onclick='window.location.href = \"$OfficeMemorandum\"'" : ''; ?>
                                title="Office Memorandum">
                                <svg class="me-3" width="24" height="24">
                                    <use xlink:href="#Documents" />
                                </svg>
                                Office Memorandum
                            </li>
                            <li class="list-group-item lg my-2 <?php echo $current_page == 'ProjectProposal.php' ? 'lg-active' : ''; ?>"
                                <?php echo $current_page != 'ProjectProposal.php' ? "onclick='window.location.href = \"$ProjectProposal\"'" : ''; ?>>
                                <svg class="me-3" width="24" height="24">
                                    <use xlink:href="#Documents" />
                                </svg>
                                Project Proposal
                            </li>
                        </div>
                    </div>
                </div>
            </div>
            <span class="hr-divider-end text-secondary"></span>
            <li class="list-group-item lg my-2" onclick="window.location.href = '../../Feed.php'" title="Feed">
                <svg class="me-2" width="24" height="24">
                    <use xlink:href="#Feed" />
                </svg>
                News Feed
            </li>
            <li class="list-group-item lg my-2" onclick="window.location.href = '../../Preference.php'"
                title="Messages">
                <svg class="me-2" width="24" height="24">
                    <use xlink:href="#Setting" />
                </svg>
                Preferences
            </li>
            <li class="list-group-item lg text-danger my-2"
                onclick="window.location.href = '../../../Functions/api/UserLogout.php'" title="Logout">
                <svg class="me-2" width="24" height="24">
                    <use xlink:href="#Logout" />
                </svg>
                Logout
            </li>
        </ul>
    </div>
</div>