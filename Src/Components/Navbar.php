<?php
$home = $_SERVER['REQUEST_URI'];
$link1 = "#";
$link2 = "#";
$link3 = "#";
$link4 = "#";
?>

<nav>
    <ul class="sidebars bg-body bg-opacity-25 bg-blur-5 d-lg-none">
        <li onclick="HideSidebars()">
            <a style="cursor: pointer;">
                <svg width="26" height="26">
                    <use xlink:href="#Close" />
                </svg>
            </a>
        </li>
        <li>
            <a href="<?php echo $link1; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Place 1
            </a>
        </li>
        <li>
            <a href="<?php echo $link2; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Place 2
            </a>
        </li>
        <li>
            <a href="<?php echo $link3; ?>" onclick="RedirectToRegister()">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#Register" />
                </svg>
                Register
            </a>
        </li>
        <li>
            <a href="<?php echo $link4; ?>" onclick="RedirectToLogin()">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#Login" />
                </svg>
                Login
            </a>
        </li>
    </ul>
    <ul class="mt-3 bg-body bg-opacity-25 bg-blur-5 rounded shadow">
        <li class="LogoMain">
            <a href="<?php echo $home; ?>">
                <span></span>
                <!-- <svg width="32" height="32">
                        <use xlink:href="#TestIcon" />
                    </svg> -->
            </a>
        </li>
        <li class="hideLabel">
            <span></span>
            <a href="<?php echo $link1; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Placeholder 1
            </a>
        </li>
        <li class="hideLabel">
            <a href="<?php echo $link2; ?>">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#TestIcon" />
                </svg>
                Placeholder 2
            </a>
        </li>
        <li class="hideLabel">
            <a href="<?php echo $link3; ?>" onclick="RedirectToRegister()">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#Register" />
                </svg>
                Register
            </a>
        </li>
        <li class="hideLabel">
            <a href="<?php echo $link4; ?>" onclick="RedirectToLogin()">
                <svg width="20" height="20" class="mx-2">
                    <use xlink:href="#Login" />
                </svg>
                Login
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

            function RedirectToRegister() {
                localStorage.setItem('current-form', 'Register');
                window.location.href = '../Pages/Accesspage.php';
            }

            function RedirectToLogin() {
                localStorage.setItem('current-form', 'Login');
                window.location.href = '../Pages/Accesspage.php';
            }
        </script>
    </ul>
</nav>