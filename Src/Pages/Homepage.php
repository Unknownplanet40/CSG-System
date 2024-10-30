<?php

session_start();

if (!isset($_SESSION['database_status'])) {
    header("location: ../Database/DatabaseStatus.php");
} else {
    if ($_SESSION['database_status'] != 'OK') {
        header("location: ../Database/DatabaseStatus.php");
    }
    include_once '../Database/Config.php';
}

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="This is a Central Student Government Portal">
	<meta name="keywords"
		content="Central Student Government, CSG, Student Government, Central Student Government Portal">
	<meta name="author" content="Unknownplanet40">
	<link rel="shortcut icon" href="../../Assets/Icons/PWA-Icon/MainIcon.png" type="image/x-icon">
	<link rel="manifest" href="manifest.json" crossorigin="use-credentials">
	<link rel="stylesheet" href="../../Utilities/Third-party/Bootstrap/css/bootstrap.css">
	<link rel="stylesheet" href="../../Utilities/Third-party/AOS/css/aos.css">
	<link rel="stylesheet" href="../../Utilities/Stylesheets/CustomStyle.css">
	<link rel="stylesheet" href="../../Utilities/Stylesheets/Homestyle.css">
	<link rel="stylesheet" href="../../Utilities/Stylesheets/NavbarStyle.css">
	<script defer src="../../Utilities/Third-party/Bootstrap/js/bootstrap.bundle.js"></script>
	<script src="https://cdn.lordicon.com/lordicon.js"></script>
	<script src="../../Utilities/Third-party/JQuery/js/jquery.min.js"></script>
	<script src="../../Utilities/Third-party/Sweetalert2/js/sweetalert2.all.min.js"></script>
	<script src="../../Utilities/Scripts/animate-browser-title.js"></script>
	<script defer src="../../Utilities/Scripts/HomeScript.js"></script>
	<title>Homepage</title>
</head>

<?php include_once '../../Assets/Icons/Icon_Assets.php'; ?>

<body class="bg-body">

	<div class="background z-n1">
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
	</div>

	<?php include_once '../Components/Navbar.php'; ?>

	<div class="container">
		<div class="d-flex justify-content-center align-items-center" style="height: 80svh;">
			<div class="modal" id="Sys_Permissions" data-bs-backdrop="static" data-bs-keyboard="false"
				tabindex="-1" data-aos="fade-down">
				<div class="modal-dialog modal-dialog-centered modal-sm">
					<div class="modal-content glass-default bg-opacity-25 border border-1 border-light rounded-1">
						<div class="modal-body">
							<div class="d-flex justify-content-center align-items-center d-none">
								<div class="spinner-border text-primary" role="status">
									<span class="visually-hidden">Loading...</span>
								</div>
							</div>
							<div class="container">
								<div class="row">
									<div class="col">
										<div class="d-flex justify-content-center align-items-center">
											<lord-icon src="https://cdn.lordicon.com/hmzvkifi.json" trigger="loop"
												delay="2500" stroke="bold" state="hover-loading"
												style="width:96px;height:96px">
											</lord-icon>
										</div>
										<div class="d-flex justify-content-center">
											<h5>Permissions</h5>
										</div>
										<div class="d-flex justify-content-center my-3">
											<small class="text-center">Before we start, please allow our system to send
												notifications and access your location for enhance functionality and
												timely updates.</small>
										</div>
										<div class="border border-1 rounded p-3 text-bg-light bg-opacity-50">
											<ol class="list-group list-group-numbered list-group-flush">
												<li
													class="list-group-item d-flex justify-content-between align-items-start bg-transparent">
													<div class="ms-2 me-auto">
														<div class="fw-bold">Notifications</div>
													</div>
													<span class="badge text-bg-danger rounded-pill" id="notification_status">
														<svg width="16" height="16" fill="currentColor" class="d-none"
															id="notification_active">
															<use xlink:href="#Verified"></use>
														</svg>
														<div class="spinner-border spinner-border-sm" role="status"
															id="notification_inactive">
															<span class="visually-hidden">Loading...</span>
														</div>
													</span>
												</li>
												<li
													class="list-group-item d-flex justify-content-between align-items-start bg-transparent">
													<div class="ms-2 me-auto">
														<div class="fw-bold">Location</div>
													</div>
													<span class="badge text-bg-danger rounded-pill"
														id="location_status">
														<svg width="16" height="16" fill="currentColor" class="d-none"
															id="location_active">
															<use xlink:href="#Verified"></use>
														</svg>
														<div class="spinner-border spinner-border-sm" role="status"
															id="location_inactive">
															<span class="visually-hidden">Loading...</span>
														</div>
													</span>
												</li>
											</ol>
											<div class="hstack gap-1 mt-3">
												<button type="button" class="btn btn-sm btn-info ms-auto" id="btn_Prompt">
													Ask
												</button>
												<button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
													close
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="../../Utilities/Third-party/AOS/js/aos.js"></script>
	<script>
		AOS.init();
	</script>
</body>

</html>