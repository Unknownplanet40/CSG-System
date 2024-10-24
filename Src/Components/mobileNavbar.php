<div class="b_navbar d-lg-none bg-body bg-blur-5 bg-opacity-25" id="b_Navbar">
        <div class="row h-100 item-parent">
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_Log" onclick="RedirectToLogin()">
                    <svg width="32" height="32">
                        <use xlink:href="#Login"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_Reg" onclick="RedirectToRegister()">
                    <svg width="32" height="32">
                        <use xlink:href="#Register"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3">
                <a class="col-Items" id="b_ItemNav_For" onclick="RedirectToForgot()">
                    <svg width="32" height="32">
                        <use xlink:href="#Lock"></use>
                    </svg>
                </a>
            </div>
            <div class="col-3 dropdown">
                <a class="col-Items" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <!-- <div class="bodymovinanim"></div> -->
                    <svg width="32" height="32">
                        <use xlink:href="#Menu"></use>
                    </svg>
                </a>
                <div class="dropdown-menu me-3 mb-3 shadow dd-menu">
                    <div class="dropdown-header">
                        <div class="hstack gap-1">
                            <img src="https://i.imgur.com/Xd06F5f.jpeg" class="rounded-circle" width="32" height="32">
                            <span class="ms-2 fs-4 fw-bold">CSG</span>
                        </div>
                    </div>
                    <hr class="dropdown-divider">
                    <a class="dropdown-item" onclick="HomeRedirect()">
                        <svg width="18" height="18" class="me-3 my-2">
                            <use xlink:href="#Home"></use>
                        </svg>
                        Homepages
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>