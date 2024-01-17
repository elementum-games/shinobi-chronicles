<?php

/*
Make a copy of this as vars.php and change values to the ones for your environment. Then
manually upload to your server, outside of git.
 */

$ENVIRONMENT = System::ENVIRONMENT_DEV; // Use only ENVIRONMENT_DEV or ENVIRONMENT_PROD
$ENABLE_DEV_ONLY_FEATURES = System::ENABLE_DEV_ONLY_FEATURES; // Boolean value
$LOCAL_HOST_CONNECTION = System::LOCAL_HOST; // Boolean value, set to false for running on websites
$REGISTER_OPEN = true;
$SC_OPEN = true;
$USE_NEW_BATTLES = false;
$WAR_ENABLED = true;
$REQUIRE_USER_VERIFICATION = false;

$WEB_URL = 'http://localhost/';

//Host needs to match the DB's host. For docker setup, rename it to "shinobi-chronicles-db".
$HOST = 'localhost';
$USERNAME = 'database_username';
$PASSWORD = 'database_password';
$DATABASE = 'database_name';
