<?php

/*
Make a copy of this as vars.php and change values to the ones for your environment. Then
manually upload to your server, outside of git.
 */

$ENVIRONMENT = 'dev';
$web_url = 'http://localhost/';

$SC_OPEN = true;
$register_open = true;

//Host needs to match the DB's host. For docker setup, rename it to "shinobi-chronicles-db".
$host = 'locahost';
$username = 'database_username';
$password = 'database_password';
$database = 'database_name';
