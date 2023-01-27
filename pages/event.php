<?php

/**
 * @throws Exception
 */
function event() {
    global $system;

    global $player;
    global $self_link;

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

                $amount = $player->rank * 1000;

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


                $amount = $player->rank * 2000;

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
                $player->items[17]['item_id'] = 17;
                $player->items[17]['quantity'] = 1;
                $player->updateInventory();

                $system->message("You have claimed the " . $gifts[101]);

                break;
            default:
                $system->message("Invalid present!");
        }
    }


    if($system->message && !$system->message_displayed) {
        $system->printMessage();
    }
    require 'templates/temp_event.php';
}
