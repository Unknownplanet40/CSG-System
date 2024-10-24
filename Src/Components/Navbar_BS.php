<?php
$home = $_SERVER['REQUEST_URI'];
$starter = "../Pages/Homepage.php";
$link1 = "#";
$link2 = "#";
$link3 = "#";
$link4 = "#";
?>

<nav>
    <ul class="sidebars bg-body bg-opacity-25 bg-blur-10 d-lg-none" style="cursor: pointer;">
        <li onclick="HideSidebars()">
            <a>
                <svg width="26" height="26">
                    <use xlink:href="#Close" />
                </svg>
            </a>
        </li>
        <li class="list-hover border-0 selected RHBS">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#TestIcon" />
            </svg>
            Place 1
        </li>
        <li class="list-hover border-0 RHBS">
            <svg width="24" height="24" class="mx-3">
                <use xlink:href="#TestIcon" />
            </svg>
            Place 1
        </li>
    </ul>
    <ul class="mt-3 bg-body bg-opacity-25 bg-blur-10 rounded shadow d-lg-none">
        <li class="LogoMain">
            <a href="<?php echo $home; ?>">
                <span></span>
                <!-- <svg width="32" height="32">
                        <use xlink:href="#TestIcon" />
                    </svg> -->
            </a>
        </li>
        <li class="hideLabel d-none">
            <a href="<?php echo $link1; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Place 1
            </a>
        </li>
        <li class="d-lg-none" onclick="ShowSidebars()">
            <a style="cursor: pointer;">
                <svg width="26" height="26">
                    <use xlink:href="#Menu" />
                </svg>
            </a>
        </li>
        <script>
            function ShowSidebars() {
                $(".sidebars").css("display", "flex");
            }

            function HideSidebars() {
                $(".sidebars").css("display", "none");
            }
        </script>
    </ul>
</nav>
<script>
    function HomeRedirect() {
        window.location.href = "<?php echo $starter; ?>";
    }

    function RedirectToLogin() {
        localStorage.setItem('current-form', 'Login');
    }

    function RedirectToRegister() {
        localStorage.setItem('current-form', 'Register');
    }

    function RedirectToForgot() {
        localStorage.setItem('current-form', 'Forgot');
    }
</script>