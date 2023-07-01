<?php

/**
 * @throws RuntimeException
 */
function event() {
    global $system;

    global $player;
    global $self_link;

    $player->getInventory();

    $gifts = [
        1227 => '_yen_ Yen!',
        1228 => '_days_ days of _seal_!',
        1229 => '1 Ancient Kunai!',
        1230 => '_yen_ Yen!',
        1231 => '_ak_ Ancient Kunai!',
        101 => 'Crystal Pendant!',
    ];

    if(isset($_GET['claim'])) {
        $claim = (int)$_GET['claim'];
        $month = date('M', time());
        $day = date('d', time());

        switch($claim) {
            case 1227:
                if($day < 27 && $month != 'Jan') {
                    $system->message("You cannot claim that yet!");
                    break;
                }
                if(in_array($claim, $player->presents_claimed)) {
                    $system->message("You have already claimed this gift!");
                    break;
                }

                $amount = $player->rank_num * 1000;

                $player->addMoney($amount, "Christmas event present");
                $player->presents_claimed[] = 1227;
                $player->updateData();

                $system->message("You received " . str_replace('_yen_', $amount, $gifts[$claim]));
                break;
            case 1228:
                if($day < 28 && $month != 'Jan') {
                    $system->message("You cannot claim that yet!");
                    break;
                }
                if(in_array($claim, $player->presents_claimed)) {
                    $system->message("You have already claimed this gift!");
                    break;
                }

                $seal_level = 2;
                $seal_time = time();
                $seal = 'Four Dragon Seal';
                $days = 3;
                if(isset($player->forbidden_seal['level'])) {
                    $seal_level = $player->forbidden_seal['level'];
                    $seal_time = $player->forbidden_seal['time'];

                    if($seal_level == 1) {
                        $seal = "Twin Sparrow";
                        $days = 7; // Upgrade time to seven days for exising t1 seal
                    }
                }

                $seal_time += $days * 24 * 60 * 60;

                $display = str_replace(["_days_", "_seal_"], [$days, $seal], $gifts[$claim]);

                // Prevent string => array error
                if(!is_array($player->forbidden_seal)) {
                    $player->forbidden_seal = [];
                }

                $player->forbidden_seal['level'] = $seal_level;
                $player->forbidden_seal['time'] = $seal_time;
                $player->presents_claimed[] = $claim;
                $player->updateData();

                $system->message("You received " . $display);

                break;
            case 1229:
                if($day < 29 && $month != 'Jan') {
                    $system->message("You cannot claim that yet!");
                    break;
                }
                if(in_array($claim, $player->presents_claimed)) {
                    $system->message("You have already claimed this gift!");
                    break;
                }

                $player->addPremiumCredits(1, "Christmas event present");
                $player->presents_claimed[] = 1229;
                $player->updateData();

                $system->message("You received " . $gifts[$claim] . "!");

                break;
            case 1230:
                if($day < 30 && $month != 'Jan') {
                    $system->message("You cannot claim that yet!");
                    break;
                }
                if(in_array($claim, $player->presents_claimed)) {
                    $system->message("You have already claimed this gift!");
                    break;
                }


                $amount = $player->rank_num * 2000;

                $player->addMoney($amount, "Christmas event present");
                $player->presents_claimed[] = 1230;
                $player->updateData();

                $system->message("You received " . str_replace('_yen_', $amount, $gifts[$claim]));

                break;
            case 1231:
                if($day < 31 && $month != 'Jan') {
                    $system->message("You cannot claim that yet!");
                    break;
                }
                if(in_array($claim, $player->presents_claimed)) {
                    $system->message("You have already claimed this gift!");
                    break;
                }

                $amount = 10;

                $player->addPremiumCredits($amount, "Christmas event present");
                $player->presents_claimed[] = 1231;
                $player->updateData();

                $system->message("You received " . str_replace('_ak_', $amount, $gifts[$claim]));

                break;
            case 101:
                if($month != 'Jan') {
                    $system->message("You cannot claim this until January 1!");
                    break;
                }
                if(in_array($claim, $player->presents_claimed)) {
                    $system->message("You have already claimed this gift!");
                    break;
                }

                $player->presents_claimed[] = 101;
                $player->updateData();

                $player->getInventory();
                $player->items[17] = new Item(id: 17);
                $player->items[17]->quantity = 1;
                $player->updateInventory();

                $system->message("You have claimed the " . $gifts[101]);

                break;
            default:
                $system->message("Invalid present!");
        }
    }

    if (isset($_GET['exchange'])) {
        try {
            // Change the item ID values based on dev/prod
            // These are placeholder exchange options
            switch ($_GET['exchange']) {
                case "red_yen_small":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['red_lantern_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 1) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['red_lantern_id']]->quantity -= 1;
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 1) {
                        unset($player->items[$system->event_data['red_lantern_id']]);
                    }
                    $player->addMoney("25", "Event");
                    $system->message("You have traded 1 Red Lantern for 25 yen!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "red_yen_medium":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['red_lantern_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 10) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['red_lantern_id']]->quantity -= 10;
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 1) {
                        unset($player->items[$system->event_data['red_lantern_id']]);
                    }
                    $player->addMoney("250", "Event");
                    $system->message("You have traded 10 Red Lanterns for 250 yen!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "red_yen_large":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['red_lantern_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 100) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['red_lantern_id']]->quantity -= 100;
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 1) {
                        unset($player->items[$system->event_data['red_lantern_id']]);
                    }
                    $player->addMoney("2500", "Event");
                    $system->message("You have traded 100 Red Lanterns for 2500 yen!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "red_rep":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['red_lantern_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 50) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['red_lantern_id']]->quantity -= 50;
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 1) {
                        unset($player->items[$system->event_data['red_lantern_id']]);
                    }
                    $player->addRep("1");
                    $system->message("You have traded 50 Red Lanterns for 1 Reputation!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "blue_red":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['blue_lantern_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['blue_lantern_id']]->quantity < 1) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['blue_lantern_id']]->quantity -= 1;
                    if ($player->items[$system->event_data['blue_lantern_id']]->quantity < 1) {
                        unset($player->items[$system->event_data['blue_lantern_id']]);
                    }

                    if ($player->hasItem($system->event_data['red_lantern_id'])) {
                        $player->items[$system->event_data['red_lantern_id']]->quantity += 5;
                    } else {
                        $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['red_lantern_id']}");
                        $player->items[$system->event_data['red_lantern_id']] = Item::fromDb($system->db->fetch($result), 5);
                    }
                    $system->message("You have traded 1 Blue Lantern for 5 Red Lantern!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "violet_red":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['violet_lantern_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['violet_lantern_id']]->quantity < 1) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['violet_lantern_id']]->quantity -= 1;
                    if ($player->items[$system->event_data['violet_lantern_id']]->quantity < 1) {
                        unset($player->items[$system->event_data['violet_lantern_id']]);
                    }

                    if ($player->hasItem($system->event_data['red_lantern_id'])) {
                        $player->items[$system->event_data['red_lantern_id']]->quantity += 20;
                    } else {
                        $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['red_lantern_id']}");
                        $player->items[$system->event_data['red_lantern_id']] = Item::fromDb($system->db->fetch($result), 20);
                    }
                    $system->message("You have traded 1 Violet Lantern for 20 Red Lanterns!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "gold_red":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['gold_lantern_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['gold_lantern_id']]->quantity < 1) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['gold_lantern_id']]->quantity -= 1;
                    if ($player->items[$system->event_data['gold_lantern_id']]->quantity < 1) {
                        unset($player->items[$system->event_data['gold_lantern_id']]);
                    }

                    if ($player->hasItem($system->event_data['red_lantern_id'])) {
                        $player->items[$system->event_data['red_lantern_id']]->quantity += 50;
                    } else {
                        $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['red_lantern_id']}");
                        $player->items[$system->event_data['red_lantern_id']] = Item::fromDb($system->db->fetch($result), 20);
                    }
                    $system->message("You have traded 1 Gold Lantern for 50 Red Lanterns!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "red_shadow":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['red_lantern_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 100) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['red_lantern_id']]->quantity -= 100;
                    if ($player->items[$system->event_data['red_lantern_id']]->quantity < 100) {
                        unset($player->items[$system->event_data['red_lantern_id']]);
                    }
                    if ($player->hasItem($system->event_data['shadow_essence_id'])) {
                        $player->items[$system->event_data['shadow_essence_id']]->quantity += 1;
                    } else {
                        $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['shadow_essence_id']}");
                        $player->items[$system->event_data['shadow_essence_id']] = Item::fromDb($system->db->fetch($result), 1);
                    }
                    $system->message("You have traded 100 Red Lanterns for 1 Shadow Essence!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "shadow_red":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['shadow_essence_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 1) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    $player->items[$system->event_data['shadow_essence_id']]->quantity -= 1;
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 1) {
                        unset($player->items[$system->event_data['shadow_essence_id']]);
                    }
                    if ($player->hasItem($system->event_data['red_lantern_id'])) {
                        $player->items[$system->event_data['red_lantern_id']]->quantity += 100;
                    } else {
                        $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['red_lantern_id']}");
                        $player->items[$system->event_data['red_lantern_id']] = Item::fromDb($system->db->fetch($result), 100);
                    }
                    $system->message("You have traded 1 Shadow Essence for 100 Red Lanterns!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "shadow_sacred_red":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['shadow_essence_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 5) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    if ($player->hasItem($system->event_data['sacred_lantern_red_id'])) {
                        throw new RuntimeException("You already have this item!");
                    }
                    $player->items[$system->event_data['shadow_essence_id']]->quantity -= 5;
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 5) {
                        unset($player->items[$system->event_data['shadow_essence_id']]);
                    }
                    $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['sacred_lantern_red_id']}");
                    $player->items[$system->event_data['sacred_lantern_red_id']] = Item::fromDb($system->db->fetch($result), 1);

                    $system->message("You have traded 5 Shadow Essence for a Sacred Red Lantern!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "shadow_sacred_blue":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['shadow_essence_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 5) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    if ($player->hasItem($system->event_data['sacred_lantern_blue_id'])) {
                        throw new RuntimeException("You already have this item!");
                    }
                    $player->items[$system->event_data['shadow_essence_id']]->quantity -= 5;
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 5) {
                        unset($player->items[$system->event_data['shadow_essence_id']]);
                    }
                    $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['sacred_lantern_blue_id']}");
                    $player->items[$system->event_data['sacred_lantern_blue_id']] = Item::fromDb($system->db->fetch($result), 1);

                    $system->message("You have traded 5 Shadow Essence for a Sacred Blue Lantern!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "shadow_sacred_violet":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['shadow_essence_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 5) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    if ($player->hasItem($system->event_data['sacred_lantern_violet_id'])) {
                        throw new RuntimeException("You already have this item!");
                    }
                    $player->items[$system->event_data['shadow_essence_id']]->quantity -= 5;
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 5) {
                        unset($player->items[$system->event_data['shadow_essence_id']]);
                    }
                    $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['sacred_lantern_violet_id']}");
                    $player->items[$system->event_data['sacred_lantern_violet_id']] = Item::fromDb($system->db->fetch($result), 1);

                    $system->message("You have traded 5 Shadow Essence for a Sacred Violet Lantern!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "shadow_sacred_gold":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['shadow_essence_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 5) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    if ($player->hasItem($system->event_data['sacred_lantern_gold_id'])) {
                        throw new RuntimeException("You already have this item!");
                    }
                    $player->items[$system->event_data['shadow_essence_id']]->quantity -= 5;
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 5) {
                        unset($player->items[$system->event_data['shadow_essence_id']]);
                    }
                    $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['sacred_lantern_gold_id']}");
                    $player->items[$system->event_data['sacred_lantern_gold_id']] = Item::fromDb($system->db->fetch($result), 1);

                    $system->message("You have traded 5 Shadow Essence for a Sacred Gold Lantern!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
                case "shadow_jutsu":
                    $player->getInventory();
                    if (!$player->hasItem($system->event_data['shadow_essence_id'])) {
                        throw new RuntimeException("You do not have this item!");
                    }
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 25) {
                        throw new RuntimeException("You do not have enough of this item!");
                    }
                    if ($player->hasItem($system->event_data['forbidden_jutsu_scroll_id'])) {
                        throw new RuntimeException("You already have this item!");
                    }
                    $player->items[$system->event_data['shadow_essence_id']]->quantity -= 25;
                    if ($player->items[$system->event_data['shadow_essence_id']]->quantity < 25) {
                        unset($player->items[$system->event_data['shadow_essence_id']]);
                    }
                    $result = $system->db->query("SELECT * FROM `items` WHERE `item_id` = {$system->event_data['forbidden_jutsu_scroll_id']}");
                    $player->items[$system->event_data['forbidden_jutsu_scroll_id']] = Item::fromDb($system->db->fetch($result), 1);

                    $system->message("You have traded 25 Shadow Essence for a Forbidden Jutsu Scroll!");
                    $player->updateInventory();
                    $player->updateData();
                    break;
            }
        } catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }

    }

    if($system->message && !$system->message_displayed) {
        $system->printMessage();
    }
    require 'templates/temp_event.php';
}
