<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = API::init();

try {
    $player = Auth::getUserFromSession($system);
} catch(Exception $e) {
    API::exitWithException($e, $system);
}
# End standard auth

$player->loadData(User::UPDATE_NOTHING);
$system->commitTransaction();

Notifications::displayNotifications($system, $player, true);
