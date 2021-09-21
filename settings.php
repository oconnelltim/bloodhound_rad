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
//*************************************************************************************
// Global settings
//*************************************************************************************
$timezone = "America/Vancouver";

//*************************************************************************************
// Database settings
//*************************************************************************************
$database_host="localhost"; // Host (name or IP) of MySQL Server
$database_tcp_port="3306"; // TCP port of MySQL server
$database_username="bloodhound_user"; // MySQL username 
$database_password="bloodhound_pw"; // MySQL password 
$database_name="bloodhound"; // Database name 
$error_message = "Database error; please contact the site administrator.";

//*************************************************************************************
// PACS Settings
//*************************************************************************************
$pacsaetitle = “PACSAETITLE”;
$pacsipaddress = “x.x.x.x”;
$pacstcpport = "104";
$myaetitle = “MYAETITLE”;

?>
