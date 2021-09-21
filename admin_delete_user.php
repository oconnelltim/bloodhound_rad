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
// declare some variables
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
$connection = mysqli_connect("$database_host", "$database_username", "$database_password", "$database_name") or die($error_message);
$timestamp=date("Y-m-d H:i:s");
$result = mysqli_query($connection, "SELECT * FROM users");

if (isset($_POST["submit"])) {
	if (isset($_POST['user_to_del'])) {
		$user_to_del=mysqli_escape_string($connection, $_POST['user_to_del']);
		$action="user_deleted:$user_to_del";
	}
	else {
		die ("<html><body><h3>ERROR! No user_id passed to delete</h3></body></html>");
	}
    $deluser_query = "DELETE FROM users WHERE username='$user_to_del'";
	$sql = mysqli_query($connection, $deluser_query) or die(mysqli_error($connection));
	$audit = "INSERT INTO audit (username, idusers_fk, ip_address, action, timestamp) VALUES ('$username','$userid','$remote_ip','$action','$timestamp')";
	$update_audit = mysqli_query($connection, $audit) or die(mysqli_error($connection));
  	echo "<head><link rel=\"stylesheet\" type=\"text/css\" href=\"radvis.css\"></head>\n";
	echo "<body>\n";
	echo "Username $user_to_del has been deleted.\n";
    //echo "SQL Query: $deluser_query"; //DEBUG
}
else {
	if ($myrow = mysqli_fetch_array($result)) {
		echo "<head><link rel=\"stylesheet\" type=\"text/css\" href=\"radvis.css\"></head>\n";
		echo "<body>\n";
		echo "<table class=\"result\" border = 1>\n";
		echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>User Type</th><th>Trainee Year</th><th>Click to Delete</th></tr>\n";
		do {
			echo "<tr><td>" . $myrow["idusers"] . "</td><td>" . $myrow["username"]. "</td><td>" . $myrow["full_name"] . "</td><td>" . $myrow["email"] . "</td><td>" . $myrow["user_type"] . "</td><td>" . $myrow["pgy"] . "</td><td><form name=\"del_user\" method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\"><input type=\"hidden\" name=\"user_to_del\" value=\"" . $myrow["username"] . "\"><input type=\"Submit\" name=\"submit\" value=\"Delete\"></form></td></tr>\n";
		} while ($myrow = mysqli_fetch_array($result));
		echo "</table>\n";
	}
    else {
	    echo "<p>Sorry, no records were found!</p>\n";
	}
}
?>
</body>
</html>
