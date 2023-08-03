<?php

require_once __DIR__ . '/../event/LanternEvent.php';

class ForbiddenShopManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player) {
        $this->system = $system;
        $this->player = $player;
    }

    /**
     * @param $jutsu_id
     * @return string
     */
    public function buyForbiddenJutsu($jutsu_id): string {
        $this->player->getInventory();

        $forbidden_jutsu = $this->getEventJutsu();

        // Check if jutsu exists
        if(!isset($forbidden_jutsu[$jutsu_id])) {
            throw new RuntimeException("Invalid jutsu!");
        }

        $jutsu = $forbidden_jutsu[$jutsu_id];

        // check if already owned
        if(isset($this->player->jutsu_scrolls[$jutsu_id])) {
            throw new RuntimeException("You already purchased this scroll!");
        }
        // check if already learned
        if($this->player->hasJutsu($jutsu_id)) {
            throw new RuntimeException("You have already learned this jutsu!");
        }

        // Check for money requirement or process exchange
        if($this->player->itemQuantity(LanternEvent::$static_item_ids['forbidden_jutsu_scroll_id']) < $jutsu->purchase_cost) {
            // Does not have scroll or forbidden jutsu/scroll to exchange
            throw new RuntimeException("You do not have enough forbidden jutsu scrolls!");
        }

        // Add to inventory
        $this->player->removeItemById(LanternEvent::$static_item_ids['forbidden_jutsu_scroll_id'], $jutsu->purchase_cost);

        $this->player->jutsu_scrolls[$jutsu_id] = $jutsu;
        $this->player->updateInventory();

        return "You have purchased {$jutsu->name}!";
    }

    /**
     * @throws RuntimeException
     * @return Jutsu[]
     */
    public function getEventJutsu(): array {
        $event_jutsu = array();
        $result = $this->system->db->query(
            "SELECT * FROM `jutsu` WHERE `purchase_type` = " . Jutsu::PURCHASE_TYPE_EVENT_SHOP . ";"
        );
        while ($row = $this->system->db->fetch($result)) {
            $jutsu = Jutsu::fromArray($row['jutsu_id'], $row);
            $jutsu->effect = System::unSlug($jutsu->effect);
            $jutsu->description = html_entity_decode($jutsu->description);
            $event_jutsu[$jutsu->id] = $jutsu;
        }
        return $event_jutsu;
    }

    public function exchangeAllEventCurrency(string $event_key): string {
        $this->player->getInventory();
        switch ($event_key) {
            case "festival_of_shadows":
                $yen_per_lantern = LanternEvent::$static_config['yen_per_lantern'];
                $red_lantern_id = LanternEvent::$static_item_ids['red_lantern_id'];
                $blue_lantern_id = LanternEvent::$static_item_ids['blue_lantern_id'];
                $violet_lantern_id = LanternEvent::$static_item_ids['violet_lantern_id'];
                $gold_lantern_id = LanternEvent::$static_item_ids['gold_lantern_id'];
                $shadow_essence_id = LanternEvent::$static_item_ids['shadow_essence_id'];

                $yen_gain = 0;
                if (isset($this->player->items[$red_lantern_id])) {
                    $num_red = $this->player->items[$red_lantern_id]->quantity;
                    $yen_gain += $num_red * $yen_per_lantern;
                    unset($this->player->items[$red_lantern_id]);
                }
                if (isset($this->player->items[$blue_lantern_id])) {
                    $num_blue = $this->player->items[$blue_lantern_id]->quantity;
                    $yen_gain += $num_blue * $yen_per_lantern * 5;
                    unset($this->player->items[$blue_lantern_id]);
                }
                if (isset($this->player->items[$violet_lantern_id])) {
                    $num_violet = $this->player->items[$violet_lantern_id]->quantity;
                    $yen_gain += $num_violet * $yen_per_lantern * 20;
                    unset($this->player->items[$violet_lantern_id]);
                }
                if (isset($this->player->items[$gold_lantern_id])) {
                    $num_gold = $this->player->items[$gold_lantern_id]->quantity;
                    $yen_gain += $num_gold * $yen_per_lantern * 50;
                    unset($this->player->items[$gold_lantern_id]);
                }
                if (isset($this->player->items[$shadow_essence_id])) {
                    $num_shadow = $this->player->items[$shadow_essence_id]->quantity;
                    $yen_gain += $num_shadow * $yen_per_lantern * 100;
                    unset($this->player->items[$shadow_essence_id]);
                }
                $this->player->addMoney($yen_gain, "Event");
                $this->player->updateInventory();
                $this->player->updateData();

                return "You exchanged all your lanterns and essence, and received &yen;" . $yen_gain . "!";
            default:
                throw new RuntimeException("Invalid event");
        }
    }
}