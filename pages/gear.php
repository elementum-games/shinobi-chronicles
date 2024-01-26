<?php

function gear(): void {
	global $system;
	global $player;

    $player->getInventory();

    $max_equipped_armor = User::BASE_ARMOR_SLOTS;
    $max_equipped_weapons = User::BASE_WEAPON_SLOTS;

	if($player->rank_num >= 3) {
		$max_equipped_armor++;
		$max_equipped_weapons++;
	}
	if($player->forbidden_seal->level != 0) {
		$max_equipped_armor += $player->forbidden_seal->extra_armor_equips;
		$max_equipped_weapons += $player->forbidden_seal->extra_weapon_equips;
	}

    if(isset($_POST['equip_item'])) {
        $view = 'items';
        $items = $_POST['items'];
        $equipped_items = [];

        $equip_ok = true;
        $equipped_armor = 0;
        $equipped_weapons = 0;

        $equipped_weapon_ids = [];
        $equipped_armor_ids = [];

        foreach($items as $id) {
            $id = (int)$id;

            if($player->hasItem($id) && $player->items[$id]->use_type != Item::USE_TYPE_CONSUMABLE) {
                $equipped_items[] = $id;
                if($player->items[$id]->use_type == Item::USE_TYPE_WEAPON) {
                    $equipped_weapons++;
                    $equipped_weapon_ids[] = $id;
                    if($equipped_weapons > $max_equipped_weapons) {
                        $system->message("You can only have " . $max_equipped_weapons . " equipped!");
                        $equip_ok = false;
                    }
                }
                if($player->items[$id]->use_type == Item::USE_TYPE_ARMOR) {
                    $equipped_armor++;
                    $equipped_armor_ids[] = $id;
                    if($equipped_armor > $max_equipped_armor) {
                        $system->message("You can only have " . $max_equipped_armor . " equipped!");
                        $equip_ok = false;
                    }
                }
            }
        }

        if($equip_ok) {
            $player->equipped_items = $equipped_items;
            $player->equipped_armor_ids = $equipped_armor_ids;
            $player->equipped_weapon_ids = $equipped_weapon_ids;
            $system->message("Items equipped!");
        }
    }
    else if(isset($_GET['use_item'])) {
        $item_id = (int)$system->db->clean($_GET['use_item']);
        try {
            if(!$player->hasItem($item_id) or $player->items[$item_id]->use_type != 3) {
                throw new RuntimeException("Invalid item!");
            }

            if($player->health >= $player->max_health * (Battle::MAX_PRE_FIGHT_HEAL_PERCENT / 100)) {
                throw new RuntimeException("Your health is already maxed out!");
            }

            if($player->items[$item_id]->quantity <= 0) {
                throw new RuntimeException("You do not have any more of this item!");
            }

            switch($player->items[$item_id]->effect) {
                case 'heal':
                    $player->items[$item_id]->quantity--;
                    $player->health += ($player->items[$item_id]->effect_amount / 100) * $player->max_health;
                    if($player->health > $player->max_health * (Battle::MAX_PRE_FIGHT_HEAL_PERCENT / 100)) {
                        $player->health = $player->max_health * (Battle::MAX_PRE_FIGHT_HEAL_PERCENT / 100);
                    }
                    $system->message("Restored " . $player->items[$item_id]->effect_amount . "% HP.");
                    break;
                default:
                    break;
            }

            $player->updateInventory();
        } catch(RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }

    $player->updateInventory();

    require 'templates/gear.php';
}
