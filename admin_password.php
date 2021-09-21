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
<?php
// declare and get some variables
require 'settings.php';
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}
if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
}
if (isset($_SERVER['REMOTE_ADDR'])) {
    $remote_ip=$_SERVER['REMOTE_ADDR'];
}
$connection = mysqli_connect("$database_host", "$database_username", "$database_password", "$database_name") or die(mysqli_error($connection));
$result=mysqli_query($connection, "SELECT * FROM users where username='$username'");
date_default_timezone_set($timezone);
$timestamp=date("Y-m-d H:i:s");
$action="password_changed";

$myrow = mysqli_fetch_array($result);
$dbusername=$myrow["username"];
$dbfull_name=$myrow["full_name"];
$dbpassword=$myrow["password"];

if (isset($_POST['password'])) {
    $newpassword=mysqli_escape_string($connection, $_POST['password']);
}
$new_encrypted_password=md5($newpassword);


if (isset($_POST["submit"])) {
// process form
	$sql = mysqli_query($connection, "UPDATE users SET password='$new_encrypted_password' WHERE username='$username'") or die(mysqli_error($connection));
	$audit = "INSERT INTO audit (username, idusers_fk, ip_address, action, timestamp) VALUES ('$username','$userid','$remote_ip','$action','$timestamp')";
	$update_audit = mysqli_query($connection, $audit) or die(mysqli_error($connection));
	echo "<head><link rel=\"stylesheet\" type=\"text/css\" href=\"radvis.css\"></head>\n";	
	echo "<body>\n";
	echo "<p>Password Updated.  Thank you.</p>\n";
}
else {
// display form
?>
    <head><link rel="stylesheet" type="text/css" href="radvis.css"></head>
    <body>
        <table bgcolor="#E0E0E0" width="65%" border="0" cellpadding="0" cellspacing="0">
            <tr><td><h2>Change Password</h2></td></tr>
            <tr>
                <td><form method = "post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <p><br>
                        Full Name: <?php echo $dbfull_name; ?></p>
                    <p>Username: <?php echo $dbusername; ?></p>
                    <p>Password:
                    <input name="password" type="text">
                    </p>
                    <p>
                        <input type="Submit" name="submit" value="Update Password">
                    </p>
                </form></td>
            </tr>
        </table>

<div align="left"></div>

<?php
} //end if
?>

</body>
</html>
