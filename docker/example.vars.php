<?php

/*
Make a copy of this as vars.php and change values to the ones for your environment. Then
manually upload to your server, outside of git.
 */

$ENVIRONMENT = '__ENV__';
$web_url = '__URL__';

$SC_OPEN = true;
$register_open = true;

//Host needs to match the DB's host. For docker setup, rename it to "shinobi-chronicles-db".
$host = '__DB_HOST__';
$username = '__DB_USER__';
$password = '__DB_PASSWORD__';
$database = '__DB_NAME__';
