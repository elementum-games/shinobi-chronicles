<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = API::init(row_lock: false);

try {
    $player = Auth::getUserFromSession($system);
} catch(RuntimeException $e) {
    API::exitWithException($e, $system);
}
# End standard auth

$player->loadData(User::UPDATE_NOTHING);
$system->db->commitTransaction();

$system->setLayoutByName($player->layout);

$notifications = Notifications::getNotifications($system, $player);
$system->layout->renderLegacyNotifications($notifications);
