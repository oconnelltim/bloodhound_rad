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
?>
<HTML>

<HEAD><link rel="stylesheet" type="text/css" href="bloodhound.css">
<TITLE>New Tracked Radiology Cases</TITLE>
    <script src="sorttable.js"></script>
</HEAD>

<BODY>
    <H2>New Tracked Radiology Cases for User: <?php if (isset($_GET['username'])) {echo $_GET['username'];} ?></H2>
<br><br>

<?php
//*************************************************************************************
// Declare our variables and other logic
//*************************************************************************************
require 'settings.php';
date_default_timezone_set($timezone);
$username = "";
$study_id = "";
$username_post = "";
$user_id = "";
$new_study_id = "";

//print "<pre>\n";
//var_dump(get_defined_vars());
//print "</pre>\n";
if (isset($_GET['username'])) {
    $username = $_GET['username'];
}
if (isset($_GET['idstudies'])) {
    $study_id = $_GET['idstudies'];
}
if (isset($_GET['idusers_fk'])) {
    $user_id = $_GET['idusers_fk'];
}
if (isset($_POST['study_to_changestatus'])) {
    $new_study_id = $_POST['study_to_changestatus'];
}
if (isset($_POST['username_post'])) {
    $username_post = $_POST['username_post'];
}

//*************************************************************************************
// Main Function
//*************************************************************************************
if (isset($_POST['submit'])) {
    db_connect();
    if (isset($study_id)) {
        $sql_to_update = "UPDATE new_studies SET status='viewed' WHERE idnewstudies=$new_study_id";
        $result = mysql_query($sql_to_update);
        echo "<head><link rel=\"stylesheet\" type=\"text/css\" href=\"bloodhound.css\"></head>\n";
        echo "<body>\n";
        echo "Study $new_study_id has been updated as viewed.\n";
?>
<br><br>
    <form method="get" action="my_cases.php">
        <input type="hidden" name="username" value="<?php echo $username_post; ?>">
        <button type="submit">Back to my tracked studies</button>
    </form>
<?
    }
    else {
        echo "<head><link rel=\"stylesheet\" type=\"text/css\" href=\"bloodhound.css\"></head>\n";
        echo "<body>\n";
        echo "Error: No Study ID supplied to delete tracked study. Please contact technical support.\n";
    }
}
else {
    db_connect();
//    echo "<pre>DEBUG: username: $username</pre>\n";
    $user_id = find_userid($username);
//    echo "<pre>DEBUG: USER ID: $user_id</pre>\n";
    draw_table();
}

//*************************************************************************************
// The functions
//*************************************************************************************
function db_connect() {
    global $error_message, $database_host, $database_tcp_port, $database_username, $database_password, $database_name;
    $db = @mysql_connect("$database_host:$database_tcp_port",$database_username,$database_password) or die($error_message);
    @mysql_select_db($database_name, $db) or die($error_message);
}

function find_userid($username) {
    $sql = "SELECT idusers FROM users where username='$username'";
//    echo "<pre>DEBUG: $sql</pre>\n";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        while($row = mysql_fetch_assoc($result)) {
            $user_id = $row["idusers"];
        }
    } else {
        echo "FATAL ERROR: user does not exist in database";
        exit;
    }
    return $user_id;
}

function draw_table() {
    global $user_id, $study_id;

    $sql = "SELECT * FROM new_studies WHERE idusers_fk='$user_id' AND idstudies_fk='$study_id' ORDER by created_dttm DESC";
//    echo "<pre>DEBUG: $sql</pre>\n";
    $result = mysql_query($sql);

    if ($myrow = mysql_fetch_array($result)) {
        echo "<table class=\"sortable\" border = 1>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Date</th><th>Time</th><th>Institution</th><th>Modality</th><th>Patient Name</th><th>Exam Description</th><th>Accession</th><th>Status</th><th>Mark as Viewed</th>\n";
        echo "\t</tr>\n";
        do {
            echo "\t<tr>\n";
            echo "\t\t<td>" . substr_replace(substr_replace($myrow["study_date"], '-', 4, 0), '-', 7, 0) . "</td>";
            echo "<td>" . substr_replace(substr_replace(substr($myrow["study_time"], 0, 6), ':', 2, 0), ':', 5, 0) . "</td>";
            echo "<td>" . $myrow["institution"] . "</td>";
            echo "<td>" . $myrow["modality"] . "</td>";
            echo "<td>" . preg_replace("/\^/", " ", preg_replace("/\^/", ",", $myrow["patient_name"], 1)) . "</td>";
            echo "<td>" . $myrow["exam_description"] . "</td>";
            echo "<td>" . $myrow["accession"] . "</td>";
            echo "<td>" . $myrow["status"] . "</td>";
            echo "<td><form name=\"update_case\" method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">";
            echo "<input type=\"hidden\" name=\"study_to_changestatus\" value=\"" . $myrow["idnewstudies"] . "\">";
            echo "<input type=\"hidden\" name=\"username_post\" value=\"" . $_GET['username'] . "\">";
            echo "<input type=\"Submit\" name=\"submit\" value=\"Mark as Viewed\"></form></td>\n";
            echo "\t</tr>\n";
        } while ($myrow = mysql_fetch_array($result));
        echo "</table>\n<br>\n";
        echo "<form method=\"get\" action=\"my_cases.php\">\n";
        echo "\t<input type=\"hidden\" name=\"username\" value=\"" . $_GET['username'] . "\">\n";
        echo "\t<button type=\"submit\">Back to My Tracked Cases</button>\n";
        echo "</form>\n";
    }
    else {
        echo "<p>Sorry, no cases were found!</p>\n";
    }
}

?>
</BODY>
</HTML>
