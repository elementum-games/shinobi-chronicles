
<?php

function travel(): void {
	global $system;
	global $player;

    if(isset($_GET['travel'])) {
        try {
            $travelManager = new TravelManager($system, $player);

            $direction = $system->db->clean($_GET['travel']);
            $travel_ok = $travelManager->movePlayer($direction);

            if($travel_ok) {
                $system->message("You have moved to {$player->location->displayString()}");
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }

        $system->printMessage();
    }

    require 'templates/travel.php';
}