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
    if(($_SESSION['username'] !== "admin")) {
        header("location:index.html");
    }
?>

<html>
<?php
    require 'settings.php';
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    }
    if (isset($_SESSION['userid'])) {
        $userid = $_SESSION['userid'];
    }
    $connection = mysqli_connect("$database_host", "$database_username", "$database_password", "$database_name") or die($error_message);
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $remote_ip=$_SERVER['REMOTE_ADDR'];
    }
    date_default_timezone_set($timezone);
    $timestamp=date("Y-m-d H:i:s");

    if (isset($_POST["submit"])) {
		    if (isset($_POST['username'])) {
				$newusername=mysqli_escape_string($connection, $_POST['username']);
				$action="user_created:$newusername";
			}
			if (isset($_POST['password'])) {
				$newpassword=mysqli_escape_string($connection, $_POST['password']);
				$new_encrypted_password=md5($newpassword);
			}
			if (isset($_POST['full_name'])) {
				$newfull_name=mysqli_escape_string($connection, $_POST['full_name']);
			}
			if (isset($_POST['email'])) {
				$email_address=mysqli_escape_string($connection, $_POST['email']);
			}
			if (isset($_POST['user_type'])) {
				$user_type=mysqli_escape_string($connection, $_POST['user_type']);
			}
			if (isset($_POST['pgy'])) {
				$pgy=mysqli_escape_string($connection, $_POST['pgy']);
			}
        // process form
        $newuser_query = "INSERT INTO users (username, password, full_name, email, user_type, pgy) VALUES ('$newusername', '$new_encrypted_password', '$newfull_name', '$email_address', '$user_type', '$pgy')";
    	$sql = mysqli_query($connection, $newuser_query) or die(mysqli_error($connection));
	    $audit = "INSERT INTO audit (username, idusers_fk, ip_address, action, timestamp) VALUES ('$username','$userid','$remote_ip','$action','$timestamp')";
    	$update_audit = mysqli_query($connection, $audit) or die(mysqli_error($connection));
	    echo "<head><link rel=\"stylesheet\" type=\"text/css\" href=\"radvis.css\"></head>\n";
    	echo "<body>\n";
	    echo "<p>User Created.  Thank you.</p>\n";
        //echo "SQL Query: $newuser_query"; //DEBUG
    }
    else {
        // display form
?>

<head><link rel="stylesheet" type="text/css" href="radvis.css"></head>
<body onload="document.create_user.full_name.focus()">
<table bgcolor="#E0E0E0" width="35%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <td ><h2>Create New User</h2></td>
  </tr>
  <tr>
    <td><form name="create_user" method = "post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <table>
			<tr><td>Full Name:</td><td><input name="full_name" tabindex="1" type="text"></td></tr>
			<tr><td>PACS Username:*</td><td><input name="username" tabindex="2" type="text"></td></tr>
			<tr><td>Password: (e.g. radiology)</td><td><input name="password" tabindex="3" type="text"></td></tr>
			<tr><td>Email Address:*</td><td><input name="email" tabindex="4" type="text"></td></tr>
			<tr><td>Status:</td><td><select name="user_type" tabindex="6">
				<option value="" selected></option>
				<option value="resident">resident</option>
				<option value="fellow">fellow</option>
				<option value="staff">staff</option>
				<option value="admin">admin</option></select></td></tr>
			<tr><td>Trainee Level:</td><td><select name="pgy" tabindex="7">
                <option value="" selected></option>
				<option value="pgy-2">PGY-2</option>
				<option value="pgy-3">PGY-3</option>
				<option value="pgy-4">PGY-4</option>
				<option value="pgy-5">PGY-5</option>
				<option value="pgy-6">PGY-6</option>
				<option value="pgy-7">PGY-7</option>
				<option value="pgy-8">PGY-8</option></select>
			<tr><td><input class="button" type="Submit" name="submit" tabindex="8" value="Create New User"></td><td></td></tr>
		</table>
		</form>
	</td>
  </tr>
</table>

<?php
	} //end if
?>

</body>
</html>
