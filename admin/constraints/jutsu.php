<?php
/* Jutsu */
$jutsu_effects = [
    'none',
    /*'release_genjutsu',
    'residual_damage',
    'ninjutsu_boost',
    'taijutsu_boost',
    'genjutsu_boost',
    'ninjutsu_resist',
    'taijutsu_resist',
    'genjutsu_resist',
    'speed_boost',
    'cast_speed_boost',
    'intelligence_boost',
    'willpower_boost',
    'absorb_chakra',
    'absorb_stamina',
    'drain_chakra',
    'drain_stamina',
    'ninjutsu_nerf',
    'taijutsu_nerf',
    'genjutsu_nerf',
    'cast_speed_nerf',
    'speed_nerf',
    'endurance_nerf',
    'intelligence_nerf',
    'willpower_nerf',*/
];

$ranks = [];
for($x = 1; $x <= System::SC_MAX_RANK; $x++) {
    $ranks[] = $x;
}

return [
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 35,
    ],
    'rank' => [
        'data_type' => 'int',
        'input_type' => 'select',
        'options' => $ranks,
    ],
    'power' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'range' => [
        'data_type' => 'int',
        'input_type' => 'text',
        'min' => 1,
        'max' => 10,
    ],
    // We validate hand seals manually
/*    'hand_seals' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'unique_required' => true,
        'unique_table' => 'jutsu',
        'unique_column' => 'hand_seals',
        'id_column' => 'jutsu_id',
    ],*/
    'element' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'options' => [
            Jutsu::ELEMENT_NONE,
            Jutsu::ELEMENT_FIRE,
            Jutsu::ELEMENT_EARTH,
            Jutsu::ELEMENT_WIND,
            Jutsu::ELEMENT_WATER,
            Jutsu::ELEMENT_LIGHTNING,
        ],
    ],
    'cooldown' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'parent_jutsu' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'purchase_cost' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'use_cost' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'description' => [
        'data_type' => 'string',
        'input_type' => 'text_area',
        'max_length' => 200,
    ],
    'battle_text' => [
        'data_type' => 'string',
        'input_type' => 'text_area',
        'max_length' => 450,
    ],
    'use_type' => [
        'data_type' => 'string',
        'input_type' => 'select',
        'options' => Jutsu::$use_types,
    ],
    'target_type' => [
        'data_type' => 'string',
        'input_type' => 'select',
        'options' => [Jutsu::TARGET_TYPE_FIGHTER_ID, Jutsu::TARGET_TYPE_TILE, Jutsu::TARGET_TYPE_DIRECTION],
    ],
    'jutsu_type' => [
        'data_type' => 'string',
        'input_type' => 'select',
        'options' => [Jutsu::TYPE_NINJUTSU, Jutsu::TYPE_TAIJUTSU, Jutsu::TYPE_GENJUTSU],
    ],
    'purchase_type' => [
        'data_type' => 'int',
        'input_type' => 'select',
        'options' => [
            Jutsu::PURCHASE_TYPE_DEFAULT => 'default',
            Jutsu::PURCHASE_TYPE_PURCHASABLE => 'purchasable',
            Jutsu::PURCHASE_TYPE_NON_PURCHASABLE => 'non-purchasable',
        ],
    ],
    'effect' => [
        'data_type' => 'string',
        'input_type' => 'select',
        'options' => $jutsu_effects,
        'not_required_value' => 'none',
    ],
    'effect_amount' => [
        'data_type' => 'float',
        'input_type' => 'text',
        'required_if' => 'effect',
    ],
    'effect_length' => [
        'data_type' => 'int',
        'input_type' => 'text',
        'required_if' => 'effect',
    ],
];
