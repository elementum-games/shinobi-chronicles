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
     * @param string $event_name
     * @param string $curreny_name
     * @param int $quantity
     * @throws RuntimeException
     */
    public function exchangeEventCurrency($event_name, $currency_name, $quantity): string
    {
        $this->player->getInventory();
        switch ($event_name) {
            case "festival_of_shadows":
                $yen_per_lantern = LanternEvent::$static_config['yen_per_lantern'];
                $red_lantern_id = LanternEvent::$static_item_ids['red_lantern_id'];
                $blue_lantern_id = LanternEvent::$static_item_ids['blue_lantern_id'];
                $violet_lantern_id = LanternEvent::$static_item_ids['violet_lantern_id'];
                $gold_lantern_id = LanternEvent::$static_item_ids['gold_lantern_id'];
                $shadow_essence_id = LanternEvent::$static_item_ids['shadow_essence_id'];
                switch ($currency_name) {
                    case "all":
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
                        return "Gained " . $yen_gain . "&#165;!";
                    default:
                        throw new RuntimeException("Invalid currency");
                }
            default:
                throw new RuntimeException("Invalid event");
        }
    }

    /**
     * @param string $item_type
     * @param int $item_id
     * @param int $quantity
     * @throws RuntimeException
     */
    public function exchangeForbiddenJutsuScroll($item_type, $item_id): string
    {
        $this->player->getInventory();
        switch ($item_type) {
            case "jutsu":
            default:
                throw new RuntimeException("Invalid event");
        }
    }

    /**
     * @throws RuntimeException
     */
    public function getEventJutsu(): array
    {
        $event_jutsu = array();
        $result = $this->system->db->query(
            "SELECT * FROM `jutsu` WHERE `purchase_type` = '5';"
        );
        while ($row = $this->system->db->fetch($result)) {
            $event_jutsu[$row['jutsu_id']] = Jutsu::fromArray($row['jutsu_id'], $row);
        }
        return $event_jutsu;
    }
}