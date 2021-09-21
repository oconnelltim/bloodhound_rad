<?php
    // Copyright (C) 2015, 2016 Tim O'Connell

    /*
    This file is part of Bloodhound.

    Bloodhound is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Bloodhound is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Bloodhound.  If not, see <http://www.gnu.org/licenses/>.
    */

    session_start();
    if(!isset($_SESSION['username'])){
        header("location:index.html");
    }
?>

<html>
<title>Bloodhound <?php echo $_SESSION['username']; ?></title>
<script LANGUAGE="JavaScript">
    function NewUser() {
        frames['right'].window.location="admin_new_user.php";
    }

    function DeleteUser() {
        frames['right'].window.location="admin_delete_user.php";
    }

    function Password() {
        frames['right'].window.location="admin_password.php";
    }
</script>

<frameset cols="100,*">
	<frame name="left" src="home_bar.php" name="home_bar" marginheight="10" marginwidth="5" scrolling="no">
	<frame name="right" src="blank.php" name="studies">
</frameset>

</html>
