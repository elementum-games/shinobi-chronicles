<?php

require_once __DIR__ . '/../classes/RankManager.php';

function store() {
	global $system;

	global $player;
	global $self_link;

    $RANK_NAMES = RankManager::fetchNames($system);

	$store_name = '';
	if($player->rank_num == 1) {
		$store_name = 'Academy';
	}

	$player->getInventory();

	$max_consumables = User::MAX_CONSUMABLES;

	if(!empty($_GET['view'])) {
		$view = $_GET['view'];
	}
	else {
		$view = 'jutsu';
	}

	// Load jutsu/items
	if($view == 'jutsu') {
		$shop_jutsu = array();
		$result = $system->db->query(
            "SELECT * FROM `jutsu` WHERE `purchase_type` = '2' AND `rank` <= '$player->rank_num' ORDER BY `rank` ASC, `purchase_cost` ASC"
        );
		while($row = $system->db->fetch($result)) {
			// Reputation jutsu discount benefit
			if($player->reputation->benefits[UserReputation::BENEFIT_JUTSU_SCROLL_DISCOUNT]) {
				$row['purchase_cost'] = floor($row['purchase_cost'] * (1-UserReputation::ITEM_SHOP_DISCOUNT_RATE/100));
			}

			$shop_jutsu[$row['jutsu_id']] = $row;
		}
	}
	else {
        /** @var Item[] $shop_items */
		$shop_items = array();
		$result = $system->db->query("
            SELECT * FROM `items`
            WHERE `purchase_type` = " . Item::PURCHASE_TYPE_PURCHASABLE . " 
            AND `rank` <= '$player->rank_num' 
            -- Disable purchasing weapons for now
            AND `use_type` != " . Item::USE_TYPE_WEAPON . "
            ORDER BY `rank` ASC, `purchase_cost` ASC
        ");
		while($row = $system->db->fetch($result)) {
			$item = Item::fromDb($row);

			// Reputation discount benefit consumables
			if($item->use_type == Item::USE_TYPE_CONSUMABLE && $player->reputation->benefits[UserReputation::BENEFIT_CONSUMABLE_DISCOUNT]) {
				$item->purchase_cost = floor($item->purchase_cost * (1-UserReputation::ITEM_SHOP_DISCOUNT_RATE/100));
			}
			// Reputation discount benefit gear
			if(($item->use_type == Item::USE_TYPE_ARMOR || $item->use_type == Item::USE_TYPE_WEAPON) && $player->reputation->benefits[UserReputation::BENEFIT_GEAR_DISCOUNT]) {
				$item->purchase_cost = floor($item->purchase_cost * (1-UserReputation::ITEM_SHOP_DISCOUNT_RATE/100));
			}

			// Insert item into shop array
			$shop_items[$row['item_id']] = $item;
		}
	}

	if(isset($_GET['purchase_item'])) {
		// Use type of 3, okay to purchase more, increment quantity
		// Use type of 1-2, only okay to purchase one
		$item_id = $system->db->clean($_GET['purchase_item']);
		try {
			// Check if item exists
			if(!isset($shop_items[$item_id])) {
				throw new RuntimeException("Invalid item!");
			}

			// check if already owned
			if($player->hasItem($item_id) && $shop_items[$item_id]->use_type != Item::USE_TYPE_CONSUMABLE) {
				throw new RuntimeException("You already own this item!");
			}

			if (isset($_GET['max'])) { // Code for handling buying bulk
				$max_missing = ($player->hasItem($item_id)) ? $max_consumables - $player->items[$item_id]->quantity : $max_consumables;

				if($player->items[$item_id]->quantity >= $max_consumables) {
					throw new RuntimeException("Your supply of this item is already full!");
				}

				if ($player->getMoney() < $shop_items[$item_id]->purchase_cost * $max_missing) {
					throw new RuntimeException("You do not have enough money to buy the max amount!");
				}

				$player->subtractMoney(
                    $shop_items[$item_id]->purchase_cost * $max_missing,
                    "Purchased {$max_missing} of item #{$item_id}"
                );
                $player->giveItem(item: $shop_items[$item_id], quantity: $max_missing);

			} else { //code for handling single purchases
                // Check for money requirement
                if($player->getMoney() < $shop_items[$item_id]->purchase_cost) {
                    throw new RuntimeException("You do not have enough money!");
                }

                // Check for max consumables
                if($player->hasItem($item_id) && $shop_items[$item_id]->use_type == 3) {
                    if($player->items[$item_id]->quantity >= $max_consumables) {
                        throw new RuntimeException("Your supply of this item is already full!");
                    }
                }

                // Add to inventory or increment quantity
                $player->subtractMoney($shop_items[$item_id]->purchase_cost, "Purchased item #{$item_id}");

                $player->giveItem($shop_items[$item_id], 1);
			}
			$system->message("Item purchased!");
		}
        catch (RuntimeException $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_GET['purchase_jutsu'])) {
		$jutsu_id = $system->db->clean($_GET['purchase_jutsu']);
		try {
			// Check if jutsu exists
			if(!isset($shop_jutsu[$jutsu_id])) {
				throw new RuntimeException("Invalid jutsu!");
			}

			// check if already owned
			if(isset($player->jutsu_scrolls[$jutsu_id])) {
				throw new RuntimeException("You already purchased this scroll!");
			}
			// check if already learned
			if($player->hasJutsu($jutsu_id)) {
				throw new RuntimeException("You have already learned this jutsu!");
            }

			// Check for money requirement
			if($player->getMoney() < $shop_jutsu[$jutsu_id]['purchase_cost'] && !$system->isDevEnvironment()) {
				throw new RuntimeException("You do not have enough money!");
			}

			// Parent jutsu check
			if($shop_jutsu[$jutsu_id]['parent_jutsu']) {
				$id = $shop_jutsu[$jutsu_id]['parent_jutsu'];
				if(!isset($player->jutsu[$id]) && !$system->isDevEnvironment()) {
                    throw new RuntimeException("You need to learn " . $shop_jutsu[$id]['name'] . " first!");
				}
			}

			// Element check
			if($shop_jutsu[$jutsu_id]['element'] != 'None') {
				if(!$player->elements or !in_array($shop_jutsu[$jutsu_id]['element'], $player->elements)) {
					throw new RuntimeException("You do not have the elemental chakra for this jutsu!");
				}
			}


			// Add to inventory
            if (!$system->isDevEnvironment()) {
                $player->subtractMoney($shop_jutsu[$jutsu_id]['purchase_cost'], "Purchased jutsu #{$jutsu_id}");
            }

			$player->jutsu_scrolls[$jutsu_id] = Jutsu::fromArray($jutsu_id, $shop_jutsu[$jutsu_id]);

			$system->message("Jutsu purchased!");
		} catch (RuntimeException $e) {
			$system->message($e->getMessage());
		}
	}

	$player->updateInventory();

    $jutsu_to_view = null;
    if(!empty($_GET['view_jutsu'])) {
        $jutsu_list = false;
        $jutsu_id_to_view = (int)$system->db->clean($_GET['view_jutsu']);
        if(isset($shop_jutsu[$jutsu_id_to_view])) {
            $jutsu_to_view = $shop_jutsu[$jutsu_id_to_view];
            $child_jutsu_result = $system->db->query("SELECT `name` FROM `jutsu` WHERE `parent_jutsu`='$jutsu_id_to_view'");
            $jutsu_to_view['child_jutsu_names'] = [];
            while($child_jutsu = $system->db->fetch($child_jutsu_result)) {
                $jutsu_to_view['child_jutsu_names'][] = $child_jutsu['name'];
            }
        }
        else {
            $jutsu_id_to_view = null;
            $system->message("Invalid jutsu!");
        }
    }

    $jutsu_type_to_view = null;
    if(!empty($_GET['jutsu_type'])) {
        $jutsu_type_to_view = $_GET['jutsu_type'];
        switch($jutsu_type_to_view) {
            case 'ninjutsu':
            case 'taijutsu':
            case 'genjutsu':
                break;
            default:
                $jutsu_type_to_view = '';
                break;
        }
    }
    else {
        if($player->ninjutsu_skill > $player->taijutsu_skill && $player->ninjutsu_skill > $player->genjutsu_skill) {
            $jutsu_type_to_view = 'ninjutsu';
        }
        else if($player->taijutsu_skill > $player->genjutsu_skill && $player->taijutsu_skill > $player->ninjutsu_skill) {
            $jutsu_type_to_view = 'taijutsu';
        }
        else if($player->genjutsu_skill > $player->taijutsu_skill && $player->genjutsu_skill > $player->ninjutsu_skill) {
            $jutsu_type_to_view = 'genjutsu';
        }
    }

    require 'templates/store.php';
}


