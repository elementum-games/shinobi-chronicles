<?php

/*
Make a copy of this as vars.php and change values to the ones for your environment. Then
manually upload to your server, outside of git.
 */

// This may be called outside the index, System is required for some of the default settings
require_once __DIR__ . '/../classes/SystemV2.php';

$ENVIRONMENT = System::ENVIRONMENT_DEV; // Use only ENVIRONMENT_DEV or ENVIRONMENT_PROD
$ENABLE_DEV_ONLY_FEATURES = System::DEV_ONLY_FEATURES_DEFAULT; // Boolean value
$LOCAL_HOST_CONNECTION = System::LOCAL_HOST; // Boolean value, set to false for running on websites
$REGISTER_OPEN = true;
$SC_OPEN = true;
$USE_NEW_BATTLES = false;
$WAR_ENABLED = true;
$REQUIRE_USER_VERIFICATION = false;

$WEB_URL = 'http://localhost/';

//Host needs to match the DB's host. For docker setup, rename it to "shinobi-chronicles-db".
$host = 'localhost';
$username = 'database_username';
$password = 'database_password';
$database = 'database_name';
