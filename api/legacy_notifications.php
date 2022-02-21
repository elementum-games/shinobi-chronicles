<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = new System();

try {
    $player = Auth::getUserFromSession($system);
} catch(Exception $e) {
    API::exitWithError($e->getMessage());
}
# End standard auth

$player->loadData(User::UPDATE_NOTHING);

Notifications::displayNotifications($system, $player, true);