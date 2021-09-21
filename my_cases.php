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
<TITLE>My Tracked Radiology Cases</TITLE>
    <script src="sorttable.js"></script>
</HEAD>

<BODY>
    <H2>My Tracked Radiology Cases for User: <?php if (isset($_GET['username'])) {echo $_GET['username'];} ?></H2>
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

//print "<pre>\n";
//var_dump(get_defined_vars());
//print "</pre>\n";
if (isset($_GET['username'])) {
    $username = $_GET['username'];
}
if (isset($_POST['study_to_del'])) {
    $study_id = $_POST['study_to_del'];
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
        $sql_to_del = "DELETE FROM primary_studies WHERE idstudies='$study_id'";
        $result = mysql_query($sql_to_del);
        $sql_to_del2 = "DELETE FROM tracked_modalities WHERE idstudies_fk='$study_id'";
        $result2 = mysql_query($sql_to_del2);
        echo "<head><link rel=\"stylesheet\" type=\"text/css\" href=\"bloodhound.css\"></head>\n";
        echo "<body>\n";
        echo "Study $study_id has been deleted and will no longer be tracked.\n";
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
    global $user_id;
    // First, run a query to get all the primary studies
    $sql = "SELECT * FROM primary_studies WHERE idusers_fk='$user_id' ORDER by created_dttm DESC";
//    echo "<pre>DEBUG: $sql</pre>\n";
    $result = mysql_query($sql);

    if ($myrow = mysql_fetch_array($result)) {
        echo "<table class=\"sortable\" border = 1>\n";
        echo "\t<tr>\n";
        echo "\t\t<th>Date</th><th>Time</th><th>Institution</th><th>Modality</th><th>Patient Name</th><th>Exam Description</th><th>Accession</th><th>Tracked<br>Modalities</th><th>Email me?</th><th>Notes</th><th>Date/Time Created</th><th>Delete</th><th>Unviewed New<br>Studies Available?</th><th>Old Additional<br>Studies</th>\n";
        echo "\t</tr>\n";
        do {
            // for each tracked primary study select the modalities we're tracking
            $sql2 = "SELECT * FROM tracked_modalities WHERE idstudies_fk='" . $myrow["idstudies"] . "'";
            $result2 = mysql_query($sql2);
            // for each tracked primary study, select if there are new studies that the user hasn't viewed
            $sql3 = "SELECT idnewstudies FROM new_studies WHERE idstudies_fk='" . $myrow["idstudies"] . "' AND status='new'";
            $result3 = mysql_query($sql3);
            // for each tracked primary study, select if there are new studies that the user has already viewed
            $sql4 = "SELECT idnewstudies FROM new_studies WHERE idstudies_fk='" . $myrow["idstudies"] . "' AND status='viewed'";
            $result4 = mysql_query($sql4);

            echo "\t<tr>\n";
            echo "\t\t<td>" . $myrow["study_date"]. "</td>";
            echo "<td>" . $myrow["study_time"] . "</td>";
            echo "<td>" . $myrow["institution"] . "</td>";
            echo "<td>" . $myrow["modality"] . "</td>";
            echo "<td>" . $myrow["patient_name"] . "</td>";
            echo "<td>" . $myrow["exam_description"] . "</td>";
            echo "<td>" . $myrow["accession"] . "</td>";
            echo "<td>";
            while ($myrow2 = mysql_fetch_array($result2)) {
                echo $myrow2["modality"] . "<br>";
            }
            echo "</td>";
            echo "<td>" . $myrow["email_status"] . "</td>";
            echo "<td>" . $myrow["notes"] . "</td>";
            echo "<td>" . $myrow["created_dttm"] . "</td>";
            echo "<td><form name=\"del_case\" method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">";
            echo "<input type=\"hidden\" name=\"study_to_del\" value=\"" . $myrow["idstudies"] . "\">";
            echo "<input type=\"hidden\" name=\"username_post\" value=\"" . $_GET['username'] . "\">";
            echo "<input type=\"Submit\" name=\"submit\" value=\"Delete\"></form></td>";
            if (mysql_num_rows($result3) > 0) {
                    echo "<td align=\"center\"><form method=\"get\" action=\"new_cases.php\">";
                    echo "<input type=\"hidden\" name=\"username\" value=\"" . $_GET['username'] . "\">";
                    echo "<input type=\"hidden\" name=\"idstudies\" value=\"" . $myrow["idstudies"] . "\">";
                    echo "<input type=\"hidden\" name=\"idusers_fk\" value=\"" . $myrow["idusers_fk"] ."\">";
                    echo "<button type=\"submit\">View New Cases</button></form></td>\n";
            }
            else {
                echo "<td></td>\n";
            }
            if (mysql_num_rows($result4) > 0) {
                    echo "<td><form method=\"get\" action=\"new_cases.php\">";
                    echo "<input type=\"hidden\" name=\"username\" value=\"" . $_GET['username'] . "\">";
                    echo "<input type=\"hidden\" name=\"idstudies\" value=\"" . $myrow["idstudies"] . "\">";
                    echo "<input type=\"hidden\" name=\"idusers_fk\" value=\"" . $myrow["idusers_fk"] ."\">";
                    echo "<button type=\"submit\">View Additional Cases</button></form></td>\n";
            }
            else {
                echo "<td></td>\n";
            }
            echo "\t</tr>\n";
            unset($myrow2);
            unset($result2);
            unset($result3);
        } while ($myrow = mysql_fetch_array($result));
        echo "</table>\n";
    }
    else {
        echo "<p>Sorry, no cases were found!</p>\n";
    }
}

?>
</BODY>
</HTML>
