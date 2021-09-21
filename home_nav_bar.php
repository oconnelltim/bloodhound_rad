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
	<head>
		<link rel="stylesheet" type="text/css" href="radvis.css">
	</head>
	<body>
		<div id="bar">
		<table width="100%">

<?php 
	// get the variables we need for displaying this page
	if (isset($_SESSION['usertype'])) {
		$usertype = $_SESSION['usertype'];
	}
	if (isset($_SESSION['username'])) {
		$username = $_SESSION['username'];
	}

	// now depending on what type of user it is, display the appropriate buttons
    if ($usertype == "admin") {
		echo "<tr><td></td><td align=\"right\">\n";
		echo "User: $username\n";
		echo "<input type=\"button\" class=\"btn\" value=\"Logout\" onclick=\"parent.Logout()\">\n";
		echo "</td></tr>\n";
    }
    else if (($usertype == "superuser")) {
		echo "<tr><td>";
		echo "<input type=\"button\" class=\"btn\" style=\"width: 60px\" value=\"Home\" onclick=\"parent.Home()\">&nbsp;";
		echo "<input type=\"button\" class=\"btn\" style=\"width: 110px\" value=\”Unused\” onclick=\"parent.Unused()\”>\n”;
		echo "<td align=\"right\">\n";
		echo "User: $username\n";
		echo "<input type=\"button\" class=\"btn\" value=\"Logout\" onclick=\"parent.Logout()\">\n";
		echo "</td></tr>\n";
    }
    else if ($usertype == "radiologist") {
		echo "User: $username\n";
		echo "<input type=\"button\" class=\"btn\" value=\"Logout\" onclick=\"parent.Logout()\">\n";
		echo "</td></tr>\n";
	} 
?>
		</table>
		</div>
	</body>
</html>
