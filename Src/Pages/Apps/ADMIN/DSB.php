<?php
$current_page = basename($_SERVER['PHP_SELF']);

$Dashboard = "./Dashboard.php";
$CourseSection = "./CourseSection.php";
$Organizations = "./Organizations.php";
$UserManagement = "./User-Management.php";
$Feed = "../../Feed.php";
$Preference = "../../Preference.php";
$SystemReport = "./SystemReport.php";
$Analytics = "./SystemAnalytics.php";




?>


<ul class="list-group">
    <li class="list-group-item lg my-2 <?php echo ($current_page == 'Dashboard.php') ? 'lg-active' : ''; ?>"
        <?php echo ($current_page != 'Dashboard.php') ? 'onclick="window.location.href = \'' . $Dashboard . '\'"' : ''; ?>>
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#Dashboard" />
                            </svg>
                            Dashboard
                        </li>
                        <span class="hr-divider-start text-secondary d-none"></span>
                        <li class="list-group-item lg d-none">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#TestIcon" />
                            </svg>
                            Placeholder
                        </li>
                        <span class="hr-divider-start text-secondary d-none"></span>
                        <div class="accordion accordion-flush" id="Modules_Accord">
                            <div class="accordion-item bg-transparent border-0">
                                <li class="list-group-item my-2 lg collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#flush-collapseOne" aria-expanded="false"
                                    aria-controls="flush-collapseOne">
                                    <svg class="me-3" width="24" height="24">
                                        <use xlink:href="#TestIcon" />
                                    </svg>
                                    Modules
                                </li>
                                <div id="flush-collapseOne" class="accordion-collapse collapse <?php echo ($current_page == 'CourseSection.php' || $current_page == 'Organizations.php' || $current_page == 'User-Management.php') ? 'show' : ''; ?>"
                                    data-bs-parent="#Modules_Accord">
                                    <div class="accordion-body">
                                        <li class="list-group-item lg my-2 text-truncate <?php echo ($current_page == 'CourseSection.php') ? 'lg-active' : ''; ?>"
                                            <?php echo ($current_page != 'CourseSection.php') ? 'onclick="window.location.href = \'' . $CourseSection . '\'"' : ''; ?>>
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#Acad" />
                                            </svg>
                                            Courses & Sections
                                        </li>
                                        <li class="list-group-item lg my-2 text-truncate <?php echo ($current_page == 'Organizations.php') ? 'lg-active' : ''; ?>"
                                            <?php echo ($current_page != 'Organizations.php') ? 'onclick="window.location.href = \'' . $Organizations . '\'"' : ''; ?>>
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#Organic" />
                                            </svg>
                                            Organizations
                                        </li>
                                        <li class="list-group-item lg my-2 text-truncate <?php echo ($current_page == 'User-Management.php') ? 'lg-active' : ''; ?> "
                                            <?php echo ($current_page != 'User-Management.php') ? 'onclick="window.location.href = \'' . $UserManagement . '\'"' : ''; ?>
                                            title="User Management">
                                            <svg class="me-3" width="24" height="24">
                                                <use xlink:href="#ManageAct" />
                                            </svg>
                                            User Management
                                        </li>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="hr-divider-end text-secondary"></span>
                        <li class="list-group-item lg my-2 <?php echo ($current_page == 'Feed.php') ? 'lg-active' : ''; ?>"
                            <?php echo ($current_page != 'Feed.php') ? 'onclick="window.location.href = \'' . $Feed . '\'"' : ''; ?>
                            title="Feed">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Feed" />
                            </svg>
                            News Feed
                        </li>
                        <li class="list-group-item lg my-2 <?php echo ($current_page == 'Preference.php') ? 'lg-active' : ''; ?>"
                            <?php echo ($current_page != 'Preference.php') ? 'onclick="window.location.href = \'' . $Preference . '\'"' : ''; ?>
                            title="Messages">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Setting" />
                            </svg>
                            Settings
                        </li>
                        <li class="list-group-item lg my-2 <?php echo ($current_page == 'SystemAnalytics.php') ? 'lg-active' : ''; ?>"
                            <?php echo ($current_page != 'SystemAnalytics.php') ? 'onclick="window.location.href = \'' . $Analytics . '\'"' : ''; ?>
                            title="Messages">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Analytics" />
                            </svg>
                            Analytics
                        </li>
                        <li class="list-group-item lg my-2 <?php echo ($current_page == 'SystemReport.php') ? 'lg-active' : ''; ?>"
                            <?php echo ($current_page != 'SystemReport.php') ? 'onclick="window.location.href = \'' . $SystemReport . '\'"' : ''; ?>
                            title="Messages">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Report" />
                            </svg>
                            Report
                        </li>
                        <li class="list-group-item lg text-danger my-2 mb-4"
                            onclick="window.location.href = '../../../Functions/api/UserLogout.php'" title="Logout">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#Logout" />
                            </svg>
                            Logout
                        </li>
                    </ul>