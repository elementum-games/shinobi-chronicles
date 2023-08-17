<?php

require_once __DIR__ . '/../event/LanternEvent.php';
require_once __DIR__ . '/../forbidden_shop/ForbiddenShopManager.php';

class ForbiddenShopApiPresenter {

    /**
     * @throws RuntimeException
     */
    public static function eventDataResponse()
    {
        return [
            'lanternEvent' => [
                'eventKey' => 'festival_of_shadows',
                'red_lantern_id' => LanternEvent::$static_item_ids['red_lantern_id'],
                'blue_lantern_id' => LanternEvent::$static_item_ids['blue_lantern_id'],
                'violet_lantern_id' => LanternEvent::$static_item_ids['violet_lantern_id'],
                'gold_lantern_id' => LanternEvent::$static_item_ids['gold_lantern_id'],
                'shadow_essence_id' => LanternEvent::$static_item_ids['shadow_essence_id'],
                'forbidden_jutsu_scroll_id' => LanternEvent::$static_item_ids['forbidden_jutsu_scroll_id'],
                'yen_per_lantern' => LanternEvent::$static_config['yen_per_lantern'],
                'red_lanterns_per_blue' => LanternEvent::$static_config['red_lanterns_per_blue'],
                'red_lanterns_per_violet' => LanternEvent::$static_config['red_lanterns_per_violet'],
                'red_lanterns_per_gold' => LanternEvent::$static_config['red_lanterns_per_gold'],
                'red_lanterns_per_shadow' => LanternEvent::$static_config['red_lanterns_per_shadow'],
            ],
        ];
    }
    /**
     * @throws RuntimeException
     */
    public static function exchangeDataResponse()
    {
        return [
            'exchangeData' => [
                'ayakashiFavor' => ForbiddenShopManager::AYAKASHI_FAVOR,
                'favorExchange' => ForbiddenShopManager::FAVOR_EXCHANGE,
                'factionMissions' => ForbiddenShopManager::FACTION_MISSIONS
            ],
        ];
    }
    /**
     * @throws RuntimeException
     */
    public static function eventJutsuResponse(ForbiddenShopManager $forbiddenShopManager)
    {
        return array_map(
            function (Jutsu $jutsu) {
                return [
                    'id' => $jutsu->id,
                    'name' => $jutsu->name,
                    'jutsuType' => $jutsu->jutsu_type,
                    'description' => html_entity_decode($jutsu->description, ENT_QUOTES),
                    'power' => $jutsu->base_power,
                    'cooldown' => $jutsu->cooldown,
                    'effect' => $jutsu->effect,
                    'effectAmount' => $jutsu->effect_amount,
                    'effectDuration' => $jutsu->effect_length,
                ];
            },
            array_values($forbiddenShopManager->getEventJutsu()) // strip jutsu ID keys so it's a real array in JS
        );
    }
}
