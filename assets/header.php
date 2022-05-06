<?php
    $img = "user.svg";
    if(isset($user)) {
        $img = $user->profilePic;
    }
?>

<header>
    <a href="./" id="header-logo">VISULY</a>
    <div id="header-search">
        <form action="search.php" autocomplete="off">
            <input type="text" placeholder="Search" name="search" id="header-searchbar" maxlength="2048">
            <button type="submit" id="header-search-submit">search<i class="fa fa-search"></i></button>
        </form>
    </div>
    <div id="header-user">
        <div id="header-user-img">
            <img src="files/img/users/<?php echo $img?>" alt="User" id="header-user-icon" width="30" height="30">
        </div>
    </div>

    <div id="header-dropdown" class="invert-image-dark">
        <a href="user.php">
            <div>
                <div class="header-dropdown-image">
                    <img src="files/img/users/user.svg" alt="user">
                </div>
                <div class="header-dropdown-text">
                    <p>Profile</p>
                </div>
            </div>
        </a>
        <a href="settings.php">
            <div>
                <div class="header-dropdown-image">
                    <img src="files/img/settings.svg" alt="settings">
                </div>
                <div class="header-dropdown-text">
                    <p>Settings</p>
                </div>
            </div>
        </a>
        <a href="logout.php">
            <div>
                <div class="header-dropdown-image">
                    <img src="files/img/logout.svg" alt="logout">
                </div>
                <div class="header-dropdown-text txt-red">
                    <p>Log out</p>
                </div>
            </div>
        </a>
    </div>

    <!-- header script -->
    <script>
        let headerDropDownIsDisplayed = false;

        document.getElementById("header-user-icon").addEventListener("click", () => {
            document.getElementById("header-dropdown").style.display = (headerDropDownIsDisplayed) ? "none" : "block";
            headerDropDownIsDisplayed = !headerDropDownIsDisplayed;
        });
    </script>
</header>