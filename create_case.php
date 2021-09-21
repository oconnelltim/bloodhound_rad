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
<TITLE>Create Rad Tracking Case</TITLE>
</HEAD>

<BODY>
<?php
// TO DO:
// add the SQL || die code to check if the inserts went ok

//*************************************************************************************
// Declare our variables and other logic
//*************************************************************************************
require 'settings.php';
date_default_timezone_set($timezone);
$accession = "";
$username = "";

$array = array();
$output = array();

if (isset($_GET['accession'])) {
    $accession = $_GET['accession'];
}
if (isset($_GET['username'])) {
    $username = $_GET['username'];
}

// DEBUG Commands
//print "<pre>\n";
//var_dump(get_defined_vars());
//print "</pre>\n";

putenv("DCMDICTPATH=/usr/share/libdcmtk2/private.dic:/usr/share/libdcmtk2/dicom.dic:");

//*************************************************************************************
// Main Function
//*************************************************************************************
//DEBUG Commands
//echo "<pre>$command\n";
//echo "$dcmdump\n";
//echo "<pre>\n";
//print_r($array);
if (isset($_POST['submit'])) {
    $date = "";
    $time = "";
    $mrn = "";
    $modality = "";
    $institution = "";
    $exam_description = "";
    $notes = "";
    $patient_name = "";
    $mysql_timestamp = date("Y-m-d H:i:s");
    $user_id = "";
    $last_inserted = "";

    db_connect();

    if (isset($_POST['username'])) {$username = mysql_real_escape_string($_POST['username']);}
    if (isset($_POST['accession'])) {$accession = mysql_real_escape_string($_POST['accession']);}
    if (isset($_POST['exam_description'])) {$exam_description = mysql_real_escape_string($_POST['exam_description']); }
    if (isset($_POST['institution'])) {$institution = mysql_real_escape_string($_POST['institution']); }
    if (isset($_POST['mrn'])) {$mrn = mysql_real_escape_string($_POST['mrn']);}
    if (isset($_POST['date'])) {$date = mysql_real_escape_string($_POST['date']); }
    if (isset($_POST['time'])) {$time = mysql_real_escape_string($_POST['time']); }
    if (isset($_POST['modality'])) {$modality = mysql_real_escape_string($_POST['modality']); }
    if (isset($_POST['notes'])) {$notes = mysql_real_escape_string($_POST['notes']); }
    if (isset($_POST['email_status'])) {$email_status = mysql_real_escape_string($_POST['email_status']); }
    if (isset($_POST['patient_name'])) {$patient_name = mysql_real_escape_string($_POST['patient_name']); }

    // get the user id for this user's insert so we know who owns this case
    $user_id = find_userid($username);

    // do the insert
    $sql = "INSERT into primary_studies (accession, mrn, institution, exam_description, study_date, study_time, modality, email_status, notes, patient_name, idusers_fk, created_dttm, status) VALUES ('$accession', '$mrn', '$institution', '$exam_description', '$date', '$time', '$modality', '$email_status', '$notes', '$patient_name', '$user_id', '$mysql_timestamp', 'active')";
    $result = mysql_query($sql);

    // get the last insert ID so we know what the foreign key will be
    // this may be risky if multiple inserts from different users happen simultaneously
    $last_inserted = mysql_insert_id();

    // now, insert the tracked modalities for this case
    if(isset($_POST['mod_to_track'])) {
        foreach ($_POST['mod_to_track'] as $key => $value) {
            $tmpsql = "INSERT INTO tracked_modalities (modality, idstudies_fk) VALUES ('$value', '$last_inserted')";
            if (isset($tmpsql)) {
                $result2 = mysql_query($tmpsql);
            }
            unset($tmpsql);
        }
    }

    // now, insert the tracked modalities for this case
    if(isset($_POST['bp_to_track'])) {
        foreach ($_POST['bp_to_track'] as $key => $value2) {
            $tmpsql2 = "INSERT INTO tracked_bodyparts (bodypart, idstudies_fk) VALUES ('$value2', '$last_inserted')";
            if (isset($tmpsql2)) {
                $result3 = mysql_query($tmpsql2);
            }
            unset($tmpsql2);
        }
    }

?>
<HTML>

<HEAD><link rel="stylesheet" type="text/css" href="bloodhound.css">
    <TITLE>Rad Case Tracker</TITLE>
</HEAD>

<BODY>
    <h3>Thank you for submitting this case</h3>
    <h3>Case Data Submitted:</h3>
    <!--<br><?php echo "DEBUG: $sql"; ?><br> -->
    <table>
        <tr><td>Accession</td><td><?php echo "$accession"; ?></td></tr>
        <tr><td>MRN</td><td><?php echo "$mrn"; ?></td></tr>
        <tr><td>Patient Name</td><td><?php echo "$patient_name"; ?></td></tr>
        <tr><td>Date</td><td><?php echo "$date"; ?></td></tr>
        <tr><td>Time</td><td><?php echo "$time"; ?></td></tr>
        <tr><td>Modality</td><td><?php echo "$modality"; ?></td></tr>
        <tr><td>Institution</td><td><?php echo "$institution"; ?></td></tr>
        <tr><td>Exam Description</td><td><?php echo "$exam_description"; ?></td></tr>
        <tr><td>Email me?</td><td><?php echo "$email_status"; ?></td></tr>
        <tr><td>Notes</td><td><?php echo "$notes"; ?></td></tr>
        <tr><td>Tracked Modalities:</td><td><?php
    foreach ($_POST['mod_to_track'] as $key => $value) {
        echo "$value<br>";
    }
?>
        <tr><td>Tracked Bodyparts:</td><td><?php
    foreach ($_POST['bp_to_track'] as $key => $value2) {
        echo "$value2<br>";
    }
?>
    </td></tr>
    </table>
    <br>
    <form method="get" action="my_cases.php">
        <input type="hidden" name="username" value="<?php echo $username; ?>">
        <button type="submit">Show my Tracked Cases</button>
    </form>
</BODY>
</HTML>
<?

exit;
}
else {
    run_query(); 
    sort_output();
    sort_clean();
}

?>
<DIV id="main">
        <H2>Create Case</H2>
        <H3>Enter in the details to create a case for tracking</h3>
        <br>

        <FORM name="input" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <table class="invis">
        <tr><td>Username:</td><td><input name="username" type="text" size="35" value="<?php echo $username; ?>" /></td></tr>
        <tr><td>Date of Study:</td><td><input name="date" type="text" size="35" value="<?php echo $array[1]["0008,0020"]; ?>" /></td></tr>
        <tr><td>Time of Study:</td><td><input name="time" type="text" size="35" value="<?php echo $array[1]["0008,0030"]; ?>" /></td></tr>
        <tr><td>Institution:</td><td><input name="institution" type="text" size="35" value="<?php echo $array[1]["0008,0080"]; ?>" /></td></tr>
        <tr><td>Modality:</td><td><input name="modality" type="text" size="35" value="<?php echo $array[1]["0008,0061"]; ?>" /></td></tr>
        <tr><td>Accession:</td><td><input name="accession" type="text" size="35" value="<?php echo $array[1]["0008,0050"]; ?>" /></td></tr>
		<tr><td>MRN:</td><td><input name="mrn" type="text" size="35" value="<?php echo $array[1]["0010,0020"]; ?>" /></td></tr>
		<tr><td>Exam Description:</td><td><input name="exam_description" type="text" size="35" value="<?php echo $array[1]["0008,1030"]; ?>" /></td></tr>
		<tr><td>Patient Name</td><td><input name="patient_name" type="text" size="35" value="<?php echo $array[1]["0010,0010"]; ?>" /></td></tr>
        <tr><td>Email me about this case</td><td><select name="email_status"><option value="send">Yes</option><option value="dont_send">No</option></select>
		<tr><td>Notes</td><td><textarea rows="5" cols="35" name="notes" wrap="soft"></textarea></td>

        <tr>
            <td class="title">Future Study Modalities<br>to Track:</td>
            <td>
                <input type="checkbox" name="mod_to_track[]" value="CR">CR<br/>
                <input type="checkbox" name="mod_to_track[]" value="CT">CT<br/>
                <input type="checkbox" name="mod_to_track[]" value="MR">MR<br/>
                <input type="checkbox" name="mod_to_track[]" value="NM">NM<br/>
                <input type="checkbox" name="mod_to_track[]" value="US">US<br/>
                <input type="checkbox" name="mod_to_track[]" value="XA">XA<br/>
                <input type="checkbox" name="mod_to_track[]" value="RF">RF<br/>
            </td>
            <td></td><td></td>
<!--            <td class="title">Future Study Bodyparts<br>to Track:</td>
            <td>
                <input type="checkbox" name="bp_to_track[]" value="Abdomen">Abdomen<br/>
                <input type="checkbox" name="bp_to_track[]" value="Body Angio">Body Angio<br/>
                <input type="checkbox" name="bp_to_track[]" value="Chest">Chest<br/>
                <input type="checkbox" name="bp_to_track[]" value="Extremity">Extremity<br/>
                <input type="checkbox" name="bp_to_track[]" value="Head">Head<br/>
                <input type="checkbox" name="bp_to_track[]" value="Heart">Heart<br/>
                <input type="checkbox" name="bp_to_track[]" value="Head and Neck">Head and Neck<br/>
                <input type="checkbox" name="bp_to_track[]" value="Neck">Neck<br/>
                <input type="checkbox" name="bp_to_track[]" value="Spine">Spine<br/>
            </td> -->
        </tr>
		<!--<style type="text/css">
		select { float:left; }
		ul { height: 200px; overflow: auto; width: 200px; border: 1px solid #000; float:left;}
		ul { list-style-type: none; margin: 0; padding: 0; overflow-x: hidden; }
		label { display: block; color: WindowText; background-color: Window; margin: 0; padding: 0; width: 100%; }
		label:hover { background-color: Highlight; color: HighlightText; }
		</style> -->

<!--		<ul>
			<li><label for="any"><input type="checkbox" name="body_part" id="any">Any</label></li>
			<li><label for="skull"><input type="checkbox" name="body_part" id="skull">Skull</label></li>
			<li><label for="cspine"><input type="checkbox" name="body_part" id="cspine">C-spine</label></li>
			<li><label for="tspine"><input type="checkbox" name="body_part" id="tspine">T-spine</label></li>
			<li><label for="lspine"><input type="checkbox" name="body_part" id="lspine">L-spine</label></li>
			<li><label for="sspine"><input type="checkbox" name="body_part" id="sspine">S-spine</label></li>
			<li><label for="coccyx"><input type="checkbox" name="body_part" id="coccyx">Coccyx</label></li>
			<li><label for="chest"><input type="checkbox" name="body_part" id="chest">Chest</label></li>
			<li><label for="clavicle"><input type="checkbox" name="body_part" id="clavicle">Clavicle</label></li>
			<li><label for="breast"><input type="checkbox" name="body_part" id="breast">Breast</label></li>
			<li><label for="abdomen"><input type="checkbox" name="body_part" id="abdomen">Abdomen</label></li>
			<li><label for="pelvis"><input type="checkbox" name="body_part" id="pelvis">Pelvis</label></li>
			<li><label for="hip"><input type="checkbox" name="body_part" id="hip">Hip</label></li>
			<li><label for="shoulder"><input type="checkbox" name="body_part" id="shoulder">Shoulder</label></li>
			<li><label for="elbow"><input type="checkbox" name="body_part" id="elbow">Elbow</label></li>
			<li><label for="knee"><input type="checkbox" name="body_part" id="knee">Knee</label></li>
			<li><label for="ankle"><input type="checkbox" name="body_part" id="ankle">Ankle</label></li>
			<li><label for="hand"><input type="checkbox" name="body_part" id="hand">Hand</label></li>
			<li><label for="foot"><input type="checkbox" name="body_part" id="foot">Foot</label></li>
			<li><label for="extremity"><input type="checkbox" name="body_part" id="extremity">Extremity</label></li>
			<li><label for="head"><input type="checkbox" name="body_part" id="head">Head</label></li>
			<li><label for="heart"><input type="checkbox" name="body_part" id="heart">Heart</label></li>
			<li><label for="neck"><input type="checkbox" name="body_part" id="neck">Neck</label></li>
			<li><label for="leg"><input type="checkbox" name="body_part" id="leg">Leg</label></li>
			<li><label for="arm"><input type="checkbox" name="body_part" id="arm">Arm</label></li>
			<li><label for="jaw"><input type="checkbox" name="body_part" id="jaw">Jaw</label></li>
			</ul> -->
        <tr><td><input type="Submit" name="submit" value="Submit New Case" /></td><td></td></tr>
        </table>
        </FORM>

    <br>
    <form method="get" action="my_cases.php">
        <input type="hidden" name="username" value="<?php echo $username; ?>">
        <button type="submit">Find my other tracked cases</button>
    </form>
<?

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

function run_query() {
    global $username, $accession, $output, $pacsaetitle, $myaetitle, $pacstcpport, $pacsipaddress;

   if (($accession) && ($username)) {
        // If we have an accession number, run a C-FIND to get the associated case information. why is study instance UID important? series description included? is series number important? studyrelatedseries removed. studyrelatedinstances?
        $command = "findscu -aet $myaetitle -aec $pacsaetitle -S -k 0008,0052=\"STUDY\" -k 0008,0020 -k 0010,0010 -k 0010,0020 -k 0020,000d -k 0020,0010 -k 0008,0050=\"$accession\" -k 0008,0030 -k 0008,0061 -k 0008,0080 -k 0008,103e -k 0020,0011 -k 0020,1208 -k 0008,1030 -k 0008,0016 $pacsipaddress $pacstcpport 2>&1";
        // In command: study, study date, patientID(mrn), studyinstanceUID?, studyID?, accession, study time, modalitiesinstudy, seriesdescription?, series number, studyrelatedinstances?, study description, SOPclassUID
        //echo $command . "\n";
        $dcmdump = shell_exec($command);
        $output = split("[\n|\r]", $dcmdump);
        //echo "<pre>$dcmdump\n"; //DEBUG
        //print_r($output); //DEBUG
        // split() is deprecated - I need to replace this with preg_split
    } 
    else {
        echo "<pre>username: $username     accession: $accession</pre>\n";
        echo "Case File form attempted to be called without an Accession number and/or a username.  Please re-try.\n";
        echo "</BODY>\n</HTML>\n";
    exit;
    }
}

function sort_output() {
        global $output, $array;
        // Turn the array into a well-sorted perl-esque hash (here, an array of arrays)
        foreach ($output as $key => $value) {
            if ((preg_match ("/^W\:\sFind\sResponse\:/", $value)) > 0) {
                preg_match ("/(\d+)/", $value, $response_array);
            }
            elseif ((preg_match ("/^W\:\s\(.+\).+\[.+\]/", $value)) > 0) {
                preg_match ("/\((.*?)\).+\[(.*?)\]/", $value, $extract);
                $array[$response_array[1]][$extract[1]] = $extract[2];
            }
            elseif ((preg_match ("/^W\:\s\(.+\).+\(.+\)/", $value)) > 0) {
                preg_match ("/\((.*?)\).+\((.*?)\)/", $value, $extract);
                $array[$response_array[1]][$extract[1]] = $extract[2];
            }
        }
    //print_r($array);
}

function sort_clean()  {
    global $array;

    $key = "";
    $value = "";
    $iKey = "";
    $iValue = "";

    // next we can clean up the dates, times, and names to be human-readable:
    foreach($array as $key => $value)  {
        foreach ($value as $iKey => $iValue) {
        # This is to put dashes into the exam dates
            if ((preg_match ("/0008\,0020/", $iKey)) > 0) {
                $iValue = substr_replace($iValue, '-', 4, 0);
                $iValue = substr_replace($iValue, '-', 7, 0);
                $array[$key][$iKey] = $iValue;
            }
            # This is to put colons into the times
            if ((preg_match ("/0008\,0030/", $iKey)) > 0) {
                $iValue = substr_replace($iValue, ':', 2, 0);
                $iValue = substr_replace($iValue, ' ', 5);
                $array[$key][$iKey] = $iValue;
            }
            # This is to clean up the name
            if ((preg_match ("/0010\,0010/", $iKey)) > 0) {
                $iValue = preg_replace("/\^/",",",$iValue,1);
                $iValue = preg_replace("/\^/","",$iValue);
                $array[$key][$iKey] = $iValue;
            }
        }
    }
}
?>

</BODY>
</HTML>
