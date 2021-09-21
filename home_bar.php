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
<head><link rel="stylesheet" type="text/css" href="radvis.css"></head>
<body>
<table width="100%">
<?php //Below is the code to check for the admin user and to allow them to see the 'admin' button
$username = $_SESSION['username'];
if ($username == "admin") {
	echo "<tr><td align=\"left\"><input type=\"button\" style=\"width: 87px\" class=\"btn\" value=\"New User\" onclick=\"parent.NewUser()\"></td></tr>";
	echo "<tr><td align=\"left\"><input type=\"button\" style=\"width: 87px\" class=\"btn\" value=\"Delete User\" onclick=\"parent.DeleteUser()\"></td></tr>";
	echo "<tr><td align=\"left\"><input type=\"button\" style=\"width: 87px\" class=\"btn\" value=\"Password\" onclick=\"parent.Password()\"></td></tr>";
} else { //display the other buttons
?>	
	<tr><td align="left"><input type="button" style="width: 87px" class="btn" value="Password" onclick="parent.Password()"></td></tr>
<?php 
  } //end the else condition
?>  
</table>
</body>
</html>
