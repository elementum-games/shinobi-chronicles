<?php

namespace SC\Factories;

use Jutsu;

class JutsuFactory {
    public static int $id = 0;

    public static function create(
        int $range = 2
    ): Jutsu {
        $jutsu_id = self::$id++;

        return new Jutsu(
            id: $jutsu_id,
            name: "Jutsu" . $jutsu_id,
            rank: 1,
            jutsu_type: 'ninjutsu',
            base_power: 1,
            range: $range,
            effect_1: 'none',
            base_effect_amount_1: 0,
            effect_length_1: 0,
            effect_2: 'none',
            base_effect_amount_2: 0,
            effect_length_2: 0,
            description: "This is the description for jutsu " . $jutsu_id,
            battle_text: "This is the battle text for jutsu " . $jutsu_id,
            cooldown: 1,
            use_type: Jutsu::USE_TYPE_PROJECTILE,
            target_type: Jutsu::TARGET_TYPE_TILE,
            use_cost: 10,
            purchase_cost: 100,
            purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            parent_jutsu: null,
            element: Element::NONE,
            hand_seals: '1-2-10'
        );
    }
}