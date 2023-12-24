<?php
// Start session
session_start();

// Errors off by default, turn on for dev
ini_set('display_errors', 'Off');
if($system->isDevEnvironment()) {
    ini_set('display_errors', 'On');
}

// Begin page load time
$PAGE_LOAD_START = microtime(true);
$LOGGED_IN = false; // Logged out by default

// Load system
$system = System::LOAD();

// Logout
if(isset($_GET['logout']) && $_GET['logout'] == 1) {
    require "logout.php";
}