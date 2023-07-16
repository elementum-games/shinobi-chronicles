<?php

require_once __DIR__ . '/../event/LanternEvent.php';

class ForbiddenShopApiPresenter {
    /**
     * @throws RuntimeException
     */
    public static function exchangeEventCurrencyResponse(ForbiddenShopManager $forbiddenShopManager, $event_name, $currency_name, $quantity)
    {
        return [
            'message' => $forbiddenShopManager->exchangeEventCurrency($event_name, $currency_name, $quantity),
        ];
    }
    /**
     * @throws RuntimeException
     */
    public static function exchangeForbiddenJutsuScrollResponse(ForbiddenShopManager $forbiddenShopManager, $item_type, $item_name)
    {
        return [
            'message' => $forbiddenShopManager->exchangeForbiddenJutsuScroll($item_type, $item_name),
        ];
    }
    /**
     * @throws RuntimeException
     */
    public static function eventDataResponse()
    {
        return [
            'lanternEvent' => [
                'red_lantern_id' => LanternEvent::$static_item_ids['red_lantern_id'],
                'blue_lantern_id' => LanternEvent::$static_item_ids['blue_lantern_id'],
                'violet_lantern_id' => LanternEvent::$static_item_ids['violet_lantern_id'],
                'gold_lantern_id' => LanternEvent::$static_item_ids['gold_lantern_id'],
                'shadow_essence_id' => LanternEvent::$static_item_ids['shadow_essence_id'],
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
    public static function eventJutsuResponse(ForbiddenShopManager $forbiddenShopManager)
    {
        return array_map(
            function (Jutsu $jutsu) {
                return [
                    'jutsu_id' => $jutsu->id,
                    'name' => $jutsu->name,
                    'jutsu_type' => $jutsu->jutsu_type,
                    'description' => $jutsu->description,
                    'power' => $jutsu->base_power,
                    'cooldown' => $jutsu->cooldown,
                    'effect' => $jutsu->effect,
                    'effect_amount' => $jutsu->effect_amount,
                    'effect_duration' => $jutsu->effect_length,
                ];
            },
            $forbiddenShopManager->getEventJutsu()
        );
    }
}
