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

	$ramen_choices['vegetable'] = [
        'cost' => $player->rank_num * 5,
        'health_amount' => $health[$player->rank_num] * 0.1,
        'label' => 'Vegetable'
    ];
    $ramen_choices['pork'] = [
        'cost' => $player->rank_num * 25,
        'health_amount' => $health[$player->rank_num] * 0.5,
        'label' => 'Pork'
    ];
    $ramen_choices['deluxe'] = [
        'cost' => $player->rank_num * 50,
        'health_amount' => $health[$player->rank_num] * 1,
        'label' => 'Deluxe'
    ];

	if(isset($_GET['heal'])) {
		try {
			$heal = $system->clean($_GET['heal']);
			if(!isset($ramen_choices[$heal])) {
				throw new Exception("Invalid choice!");
			}
			if($player->getMoney() < $ramen_choices[$heal]['cost']) {
				throw new Exception("You do not have enough money!");
			}
          	if($player->health >= $player->max_health) {
				throw new Exception("Your health is already maxed out!");
			}
			$player->subtractMoney($ramen_choices[$heal]['cost'], "Purchased {$heal} health");
			$player->health += $ramen_choices[$heal]['health_amount'];
			if($player->health > $player->max_health) {
				$player->health = $player->max_health;
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}

    
    require 'templates/ramen_shop.php';
}
