<?php

/*
File: 		equip.php
Coder:		Levi Meahan
Created:	09/04/2013
Revised:	04/22/2014 by Levi Meahan
Purpose:	Functions for equip where users can equip items and jutsu
Algorithm:	See master_plan.html
 */

function jutsu(): void {
    global $system;

    global $player;

    global $self_link;

    $player->getInventory();

    $max_equipped_jutsu = User::BASE_JUTSU_SLOTS;
    if($player->rank_num >= 3) {
        $max_equipped_jutsu++;
    }
    if($player->rank_num >= 4) {
        $max_equipped_jutsu++;
    }
    if($player->forbidden_seal->level != 0) {
        $max_equipped_jutsu += $player->forbidden_seal->extra_jutsu_equips;
    }

    if(!empty($_POST['equip_jutsu'])) {
        $jutsu = $_POST['jutsu'];
        $equipped_jutsu = [];

        try {
            if ($player->battle_id) {
                throw new RuntimeException("Cannot change jutsu while in battle!");
            }
            $count = 0;
            $jutsu_types = ['ninjutsu', 'taijutsu', 'genjutsu'];
            foreach($jutsu as $jutsu_data) {
                if($count >= $max_equipped_jutsu) {
                    break;
                }

                $jutsu_array = explode('-', $jutsu_data);
                if($jutsu_array[0] == 'none') {
                    continue;
                }

                if(!in_array($jutsu_array[0], $jutsu_types)) {
                    throw new RuntimeException("Invalid jutsu type!");
                }
                if($player->hasJutsu($jutsu_array[1])) {
                    $equipped_jutsu[$count]['id'] = $system->db->clean($jutsu_array[1]);
                    $equipped_jutsu[$count]['type'] = $system->db->clean($jutsu_array[0]);
                    $count++;
                }
            }

            $player->equipped_jutsu = $equipped_jutsu;
            $system->message("Jutsu equipped!");
        }
        catch(RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }

    if(!empty($_GET['learn_jutsu'])) {
        $jutsu_id = (int)$_GET['learn_jutsu'];
        try {
            if ($player->battle_id) {
                throw new RuntimeException("Cannot change jutsu while in battle!");
            }
            if(!isset($player->jutsu_scrolls[$jutsu_id])) {
                throw new RuntimeException("Invalid jutsu!");
            }
            if($player->hasJutsu($jutsu_id)) {
                throw new RuntimeException("You already know that jutsu!");
            }

            // Parent jutsu check
            if($player->jutsu_scrolls[$jutsu_id]->parent_jutsu) {
                $id = $player->jutsu_scrolls[$jutsu_id]->parent_jutsu;
                if(!isset($player->jutsu[$id]) && !$system->isDevEnvironment()) {
                    throw new RuntimeException("You need to learn " . $player->jutsu[$id]->name . " first!");
                }

                if($player->jutsu[$id]->level < 50 && !$system->isDevEnvironment()) {
                    throw new RuntimeException(
                        "You are not skilled enough with " . $player->jutsu[$id]->name .
                        "! (Level " . $player->jutsu[$id]->level . "/50)"
                    );
                }
            }

            $player->jutsu[$jutsu_id] = $player->jutsu_scrolls[$jutsu_id];
            if ($system->isDevEnvironment()) {
                $player->jutsu[$jutsu_id]->setLevel(100, 0);
            } else {
                $player->jutsu[$jutsu_id]->setLevel(1, 0);
            }
            $jutsu_name = $player->jutsu_scrolls[$jutsu_id]->name;

            switch($player->jutsu[$jutsu_id]->jutsu_type) {
                case 'ninjutsu':
                    $player->ninjutsu_ids[] = $jutsu_id;
                    break;
                case 'taijutsu':
                    $player->taijutsu_ids[] = $jutsu_id;
                    break;
                case 'genjutsu':
                    $player->genjutsu_ids[] = $jutsu_id;
                    break;
            }

            unset($player->jutsu_scrolls[$jutsu_id]);
            $system->message("You have learned $jutsu_name!");
        }
        catch(RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }
    else if(!empty($_GET['forget_jutsu'])) {
        if ($player->battle_id) {
            throw new RuntimeException("Cannot change jutsu while in battle!");
        }
        $jutsu_id = (int)$_GET['forget_jutsu'];
        try {
            //Checking if player knows the jutsu he's trying to forget.
            if(!$player->hasJutsu($jutsu_id)) {
                throw new RuntimeException("Invalid Jutsu!");
            }

            //Checking if player has jutsu that depend on the jutsu he's trying to forget.
            $can_forget = userHasChildrenJutsu($jutsu_id, $player);
            if(!$can_forget){
                throw new RuntimeException("You cannot forget the parent of a jutsu you know!");
            }

            if(!empty($_POST['confirm_forget'])) {
                //Forgetting jutsu.
                $jutsu = $player->jutsu[$jutsu_id];

                //refund input verification
                if($jutsu->purchase_type == Jutsu::PURCHASE_TYPE_PURCHASABLE){
                    $message = "You have forgotten {$jutsu->name}";

                    $refund = ceil($jutsu->purchase_cost * Jutsu::REFUND_AMOUNT);
                    if($refund > 0) {
                        $player->addMoney($refund, "Sell jutsu");
                        $message .= " and gained &yen;$refund";
                    }
                    $message .= "!";
                    $player->removeJutsu($jutsu_id);
                }
                elseif($jutsu->purchase_type == Jutsu::PURCHASE_TYPE_EVENT_SHOP) {
                    // TODO: Make this more robust for other event purchase types (possibly add "currency" type to jutsu data)
                    // Forbidden jutsu
                    require_once 'classes/event/LanternEvent.php';
                    require_once 'classes/forbidden_shop/ForbiddenShopManager.php';
                    $forbidden_shop = new ForbiddenShopManager($system, $player);
                    $forbidden_jutsu = $forbidden_shop->getEventJutsu();

                    if(!isset($forbidden_jutsu[$jutsu_id])) {
                        throw new RuntimeException("Invalid forbidden jutsu!");
                    }

                    $player->giveItem(new Item(LanternEvent::$static_item_ids['forbidden_jutsu_scroll_id']), 1);
                    $player->removeJutsu($jutsu_id);

                    $message = "You have forgotten {$jutsu->name}! The scroll you gave to Akuji has reappeared in your inventory.";
                }
                else {
                    throw new RuntimeException("Unexpected jutsu purchase type. If this persists, notify an admin!");
                }

                //css: Overlap caused by css Position property
                $system->message($message);
                $system->printMessage();
            }
            else {
                echo "<table class='table'>
					    <tr>
					        <th>Forget Jutsu</th>
                        </tr>
					    <tr>
					        <td style='text-align:center;'>
						        Are you sure you want to forget {$player->jutsu[$jutsu_id]->name}?
						        <br />
                                <form action='$self_link&forget_jutsu={$jutsu_id}' method='post'>
                                    <input type='hidden' name='confirm_forget' value='1' />
                                    <button style='text-align:center' type='submit'>Forget</button>
                                </form>
					        </td>
					    </tr>
				    </table>";
            }

        }
        catch(RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }

    $child_jutsu = [];
    $child_jutsu_result = $system->db->query("SELECT `name`, `parent_jutsu` FROM `jutsu` WHERE `parent_jutsu` != '0'");
    while($row = $system->db->fetch($child_jutsu_result)) {
        if (array_key_exists($row['parent_jutsu'], $child_jutsu)) {
            array_push($child_jutsu[$row['parent_jutsu']], $row['name']);
        }
        else {
            $child_jutsu[$row['parent_jutsu']] = [array($row['name'])];
        }
    }

    $system->printMessage();
    $player->updateInventory();
    // sort jutsu list by base power
    $jutsu_list = $player->jutsu;
    usort($jutsu_list, function($a, $b) {return $a->base_power < $b->base_power ? 1 : -1;});
    // fix barrier
    foreach ($jutsu_list as &$jutsu) {
        if ($jutsu->use_type == "barrier") {
            $jutsu->effects[0]->effect = "barrier";
        }
        unset($jutsu);
    }

    require 'templates/jutsu_page.php';
}

function userHasChildrenJutsu($id, $player): bool {
    foreach($player->jutsu as $jutsu){
        if($id == $jutsu->parent_jutsu){
            return false;
        }
    }

    return true;
}
