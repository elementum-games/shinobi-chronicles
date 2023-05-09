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

    $rank_names = RankManager::fetchNames($system);

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
                    throw new Exception("Invalid jutsu type!");
                }
                if($player->hasJutsu($jutsu_array[1])) {
                    $equipped_jutsu[$count]['id'] = $system->clean($jutsu_array[1]);
                    $equipped_jutsu[$count]['type'] = $system->clean($jutsu_array[0]);
                    $count++;
                }
            }

            $player->equipped_jutsu = $equipped_jutsu;
            $system->message("Jutsu equipped!");
        } catch(Exception $e) {
            $system->message($e->getMessage());
        }
    }

    if(!empty($_GET['learn_jutsu'])) {
        $jutsu_id = (int)$_GET['learn_jutsu'];
        try {
            if(!isset($player->jutsu_scrolls[$jutsu_id])) {
                throw new Exception("Invalid jutsu!");
            }
            if($player->hasJutsu($jutsu_id)) {
                throw new Exception("You already know that jutsu!");
            }

            // Parent jutsu check
            if($player->jutsu_scrolls[$jutsu_id]->parent_jutsu) {
                $id = $player->jutsu_scrolls[$jutsu_id]->parent_jutsu;
                if(!isset($player->jutsu[$id])) {
                    throw new Exception("You need to learn " . $player->jutsu[$id]->name . " first!");
                }

                if($player->jutsu[$id]->level < 50) {
                    throw new Exception(
                        "You are not skilled enough with " . $player->jutsu[$id]->name .
                        "! (Level " . $player->jutsu[$id]->level . "/50)"
                    );
                }
            }

            $player->jutsu[$jutsu_id] = $player->jutsu_scrolls[$jutsu_id];
            $player->jutsu[$jutsu_id]->setLevel(1, 0);
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
        } catch(Exception $e) {
            $system->message($e->getMessage());
        }
    }
    else if(!empty($_GET['forget_jutsu'])) {
        $jutsu_id = (int)$_GET['forget_jutsu'];
        try {
            //Checking if player knows the jutsu he's trying to forget.
            if(!$player->hasJutsu($jutsu_id)) {
                throw new Exception("Invalid Jutsu!");
            }

            //Checking if player has jutsu that depend on the jutsu he's trying to forget.
            $can_forget = userHasChildrenJutsu($jutsu_id, $player);
            if(!$can_forget){
                throw new Exception("You cannot forget the parent of a jutsu you know!");
            }

            if(!empty($_POST['confirm_forget'])) {
                //Forgetting jutsu.
                $jutsu_name = $player->jutsu[$jutsu_id]->name;

                //refund input verification
                $refund = ($player->jutsu[$jutsu_id]->purchase_cost * 0.1); //10% Refund
                $refund = intval(round($refund)); //round and then convert Float=>Int
                if($refund > 0 && gettype($refund) == "integer"){
                    $player->addMoney($refund, "Sell jutsu");
                }

                $player->removeJutsu($jutsu_id);

                //css: Overlap caused by css Position property
                $system->message("You have forgotten $jutsu_name!<br>You were refunded ¥{$refund}");
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

        } catch(Exception $e) {
            $system->message($e->getMessage());
        }
    }

    $child_jutsu = [];
    $child_jutsu_result = $system->query("SELECT `name`, `parent_jutsu` FROM `jutsu` WHERE `parent_jutsu` != '0'");
    while($row = $system->db_fetch($child_jutsu_result)) {
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
