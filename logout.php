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

require 'settings.php';
date_default_timezone_set($timezone);
$timestamp=date("Y-m-d H:i:s");
$action="logout";
if (isset($_SERVER['REMOTE_ADDR'])) {
    $remote_ip=$_SERVER['REMOTE_ADDR'];
}

session_start();
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}
if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
}

//check to make sure the session variable is registered
if(isset($_SESSION['username'])) {

    // Connect to server and select databse.
    $connection = mysqli_connect("$database_host", "$database_username", "$database_password", "$database_name") or die($error_message);
    $audit = "INSERT INTO audit (username, idusers_fk, ip_address, action, timestamp) VALUES ('$username','$userid','$remote_ip','$action','$timestamp')";
    $update_audit = mysqli_query($connection, $audit);

    //session variable is registered, the user is ready to logout
    session_unset();
    session_destroy();
    header('Window-target: _parent');
    header('Location: index.html');
}
else {
    //the session variable isn't registered, the user shouldn't even be on this page
    header( "Location: index.html" );
}
?>
