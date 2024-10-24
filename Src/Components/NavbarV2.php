<?php
$home = $_SERVER['REQUEST_URI'];
$starter = "../Pages/Homepage.php";
$link1 = "#";
$link2 = "#";
$link3 = "#";
$link4 = "#";
?>

<nav class="mt-3">
    <ul class="sidebars bg-body bg-opacity-25 bg-blur-10 d-lg-none" style="cursor: pointer;">
        <li onclick="HideSidebars()">
            <a>
                <svg width="26" height="26">
                    <use xlink:href="#Close" />
                </svg>
            </a>
        </li>
        <li class="list-hover border-0 selected" id="sidev2-login" onclick="RedirectToLogin()">
            <svg width="16" height="16" class="mx-1">
                <use xlink:href="#Login" />
            </svg>
            Login
        </li>
        <li class="list-hover border-0 " id="sidev2-register" onclick="RedirectToRegister()">
            <svg width="16" height="16" class="mx-1">
                <use xlink:href="#Register" />
            </svg>
            Register
        </li>
        <li class="list-hover border-0" id="sidev2-forgot" onclick="RedirectToForgot()">
            <svg width="16" height="16" class="mx-1">
                <use xlink:href="#Lock" />
            </svg>
            Forgot Password
        </li>
        <li class="list-hover border-0" onclick="HomeRedirect()">
            <svg width="16" height="16" class="mx-1">
                <use xlink:href="#Home" />
            </svg>
            Homepages
        </li>
        <li class="list-hover border-0 d-none">
            <svg width="16" height="16" class="mx-1">
                <use xlink:href="#Help" />
            </svg>
            Help
        </li>
    </ul>
    <ul class="bg-body bg-opacity-25 bg-blur-5 rounded shadow">
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
        <li class="hideLabel d-none">
            <a href="<?php echo $link2; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Place 2
            </a>
        </li>
        <li class="hideLabel d-none">
            <a href="<?php echo $link3; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Place 3
            </a>
        </li>
        <li class="hideLabel d-none">
            <a href="<?php echo $link4; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Place 4
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