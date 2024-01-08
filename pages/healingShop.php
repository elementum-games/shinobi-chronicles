<?php
/*
File: 		healingShop.php
Coder:		Levi Meahan
Created:	10/05/2013
Revised:	10/05/2013 by Levi Meahan
Purpose:	Functions for healing (ramen shop)
Algorithm:	See master_plan.html
*/
function healingShop() {
	global $system;
	global $player;
	global $self_link;


	$rankManager = new RankManager($system);
	$rankManager->loadRanks();

	$health[1] = $rankManager->healthForRankAndLevel(1, $rankManager->ranks[1]->max_level);
	$health[2] = $rankManager->healthForRankAndLevel(2, $rankManager->ranks[2]->max_level);
	$health[3] = $rankManager->healthForRankAndLevel(3, $rankManager->ranks[3]->max_level);
	$health[4] = $rankManager->healthForRankAndLevel(4, $rankManager->ranks[4]->max_level);
	// $health[5] = $rankManager->healthForRankAndLevel(5, $rankManager->ranks[5]->max_level);

    $result = $system->db->query("SELECT * FROM `maps_locations` WHERE `name` = 'Underground Colosseum'");
    $location_result = $system->db->fetch($result);
    $arena_coords = new TravelCoords($location_result['x'], $location_result['y'], 1);

    $ramen_choices['vegetable'] = [
        'cost' => !$player->location->equals($player->village_location) ? $player->rank_num * 5 * 5 : $player->rank_num * 5,
        'health_amount' => $health[$player->rank_num] * 0.1,
        'label' => 'Vegetable'
    ];
    $ramen_choices['pork'] = [
        'cost' => !$player->location->equals($player->village_location) ? $player->rank_num * 25 * 5 : $player->rank_num * 25,
        'health_amount' => $health[$player->rank_num] * 0.5,
        'label' => 'Pork'
    ];
    $ramen_choices['deluxe'] = [
        'cost' => !$player->location->equals($player->village_location) ? $player->rank_num * 50 * 5 : $player->rank_num * 50,
        'health_amount' => $health[$player->rank_num] * 1,
        'label' => 'Deluxe'
    ];

    if ($system->isDevEnvironment()) {
        $ramen_choices['vegetable']['cost'] = 0;
        $ramen_choices['pork']['cost'] = 0;
        $ramen_choices['deluxe']['cost'] = 0;
    }

	if(isset($_GET['heal'])) {
		try {
			$heal = $system->db->clean($_GET['heal']);
			if(!isset($ramen_choices[$heal])) {
				throw new RuntimeException("Invalid choice!");
			}
			if($player->getMoney() < $ramen_choices[$heal]['cost']) {
				throw new RuntimeException("You do not have enough money!");
			}
          	if($player->health >= $player->max_health) {
				throw new RuntimeException("Your health is already maxed out!");
			}
            if (!$system->isDevEnvironment()) {
                $player->subtractMoney($ramen_choices[$heal]['cost'], "Purchased {$heal} health");
            }
			$player->health += $ramen_choices[$heal]['health_amount'];
			if($player->health > $player->max_health) {
				$player->health = $player->max_health;
			}
		} catch (RuntimeException $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}


    require 'templates/ramen_shop.php';
}
