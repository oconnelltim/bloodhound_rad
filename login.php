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
	// Declare some variables we need
	ob_start();
	require 'settings.php';
	$tbl_name="users"; // db table name 
	if (isset($_SERVER['REMOTE_ADDR'])) {
		$remote_ip=$_SERVER['REMOTE_ADDR'];
	}
	date_default_timezone_set($imezone);
	$timestamp=date("Y-m-d H:i:s");
	$action="login";
	$loginfail="login_fail";

	// Connect to server and select databse.
	$connection = mysqli_connect("$database_host", "$database_username", "$database_password", "$database_name") or die($error_message);
	if (!$connection)  {
		die($error_message);
	}

// Define $myusername and $mypassword 
if (isset($_POST['username'])) {
    $username=$_POST['username']; 
}
if (isset($_POST['password'])) {
    $password=$_POST['password']; 
}

// To protect against injection attacks
$username = stripslashes($username);
$password = stripslashes($password);
$username = mysqli_escape_string($connection, $username);
$password = mysqli_escape_string($connection, $password);
$encrypted_password=md5($password);

$sql="SELECT * FROM $tbl_name WHERE username='$username' and password='$encrypted_password'";
$result=mysqli_query($connection, $sql) or die ($error_message);

// Mysql_num_row is counting table row
$count=mysqli_num_rows($result);
// If result matched $username and $password, table row must be 1 row
$row = mysqli_fetch_array($result);
$fullname = $row['full_name']; 
$usertype = $row['user_type'];
$userid = $row['idusers'];

if ($count > 0) {
    session_start();
    $_SESSION['username'] = $username;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['usertype'] = $usertype;
    $_SESSION['userid'] = $userid;
    $audit = "INSERT INTO audit (username, idusers_fk, ip_address, action, timestamp) VALUES ('$username','$userid','$remote_ip','$action','$timestamp')";
    $update_audit = mysqli_query($connection, $audit) or die(mysqli_error($connection));
    header("location:system.php");
}
else {
    echo "<html>\n<body bgcolor=\"#ffffff\">\n<p>Wrong Username or Password - your IP and attempt has been logged</p>\n<a href=\"index.html\">Login</a>\n</body>\n</html>\n";
    $audit = "INSERT INTO audit (username, idusers_fk, ip_address, action, timestamp) VALUES ('$username','$userid','$remote_ip','$loginfail','$timestamp')";
    $update_audit = mysqli_query($connection, $audit) or die(mysqli_error($connection));
}

ob_end_flush();
?>

