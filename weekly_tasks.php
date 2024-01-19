<?php

// Don't allow users to trigger these tasks from the web
if(php_sapi_name() !== "cli") {
   echo "Invalid environment!";
   exit;
}

require_once __DIR__ . "/classes/SystemV2.php";
require_once __DIR__ . "/classes/Clan.php";

$system = System::initialize();


