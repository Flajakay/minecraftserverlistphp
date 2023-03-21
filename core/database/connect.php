<?php
// Connection parameters
$DatabaseServer = "localhost";
$DatabaseUser   = "root";
$DatabasePass   = "root";
$DatabaseName   = "serverlist";

// Connecting to the database
$database = new mysqli($DatabaseServer, $DatabaseUser, $DatabasePass, $DatabaseName);

?>