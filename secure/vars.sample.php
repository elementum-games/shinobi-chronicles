<?php
// This is used in other places, System is a requirement
require_once __DIR__ . '/../classes/System.php';

$ENVIRONMENT = System::ENVIRONMENT_DEV; // Use only ENVIRONMENT_DEV or ENVIRONMENT_PROD
$ENABLE_DEV_ONLY_FEATURES = true; // Boolean value
$LOCAL_HOST_CONNECTION = System::LOCAL_HOST; // Boolean value, set to false for running on websites
$register_open = true;
$SC_OPEN = true;
$USE_NEW_BATTLES = false;
$WAR_ENABLED = true;
$REQUIRE_USER_VERIFICATION = false;

$web_url = 'http://localhost/';

//Host needs to match the DB's host. For docker setup, rename it to "shinobi-chronicles-db".
$host = 'localhost';
$username = 'sc_admin';
$password = 'password';
$database = 'shinobi_chronicles';