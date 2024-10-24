<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/NavbarStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/CustomStyle.css">
    <link rel="stylesheet" href="../../../../Utilities/Stylesheets/BS_DBStyle.css">
    <script defer src="../../../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../../../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
    <script src="../../../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
    <script defer type="module" src="../../../../Utilities/Scripts/BS_DBScript.js"></script>
    <title>Dashboard</title>
</head>

<body>
    <?php include_once "../../../../Assets/Icons/Icon_Assets.php"; ?>
    <?php include_once "../../../Components/Navbar_BS.php"; ?>

    <div class="container-fluid d-flex flex-row p-0">
        <div class="BS-Side d-none d-lg-block border-end glass-10 bg-opacity-50">
            <div class="d-flex flex-column justify-content-between h-100">
                <div class="container text-center my-2">
                    <img src="https://i.imgur.com/Xd06F5f.jpeg" class="rounded-circle img-fluid border border-3 mt-2"
                        alt="Profile Picture" width="84" height="84">
                    <div class="vstack gap-0 mt-1">
                        <p class="lead fw-bold text-truncate mb-0">Name Placeholder</p>
                        <small class="text-secondary">Subtext Placeholder</small>
                    </div>
                </div>
                <div class="container">
                    <ul class="list-group">
                        <li class="list-group-item lg lg-active">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#TestIcon" />
                            </svg>
                            Dashboard
                        </li>
                        <span class="hr-divider-start text-secondary">Storage</span>
                        <li class="list-group-item lg text-truncate">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#TestIcon" />
                            </svg>
                            List of Items
                        </li>
                        <li class="list-group-item lg">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#TestIcon" />
                            </svg>
                            Issued Items
                        </li>
                        <li class="list-group-item lg">
                            <svg class="me-3" width="24" height="24">
                                <use xlink:href="#TestIcon" />
                            </svg>
                            Archived Items
                        </li>
                        <span class="hr-divider-end text-secondary"></span>
                        <li class="list-group-item lg">
                            <svg class="me-2" width="24" height="24">
                                <use xlink:href="#TestIcon" />
                            </svg>
                            Settings
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="BS-Main mt-2">
            <div class="container">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-2">
                    <?php for ($i = 0; $i < 4; $i++) { ?>
                    <div class="col">	
                        <div class="card image1 bg-body bg-opacity-25 bg-blur-10">
                            <p class="ps-3 pt-1 mb-0">Items</p>
                            <div class="card-body py-0">
                                <div class="vstack">
                                    <p class="mb-0">1</p>
                                    <div class="progress" role="progressbar" style="height: 10px;">
                                        <div id="prog" class="bg-primary bg-gradient progress-bar" style="width: 10%;"></div>
                                    </div>
                                    <div class="hstack">
                                        <p class="mb-0">10%</p>
                                        <p class="mb-0 ms-auto">90%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>