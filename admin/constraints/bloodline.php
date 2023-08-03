<?php

/* Bloodline */
$bloodline_combat_boosts = [
    'ninjutsu_boost',
    'genjutsu_boost',
    'taijutsu_boost',
    'ninjutsu_resist',
    'genjutsu_resist',
    'taijutsu_resist',
    'speed_boost',
    'cast_speed_boost',
    'endurance_boost',
    'intelligence_boost',
    'willpower_boost',
    'heal',
];
$passive_boosts = [
    'scout_range',
    'stealth',
    'regen',
];

$jutsu_constraints = require 'admin/constraints/jutsu.php';

$constraints = [
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 100,
    ],
    'clan_id' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'rank' => [
        'data_type' => 'int',
        'input_type' => 'select',
        'options' => [5 => 'Admin', 4 => 'Lesser', 3 => 'Common', 2 => 'Elite', 1 => 'Legendary'],
    ],
    'village' => [
        'data_type' => 'string',
        'input_type' => 'select',
        'options' => ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'],
    ],
    'passive_boosts' => [
        'count' => 3,
        'num_required' => 0,
        'variables' => [
            'power' => [
                'data_type' => 'int',
                'input_type' => 'text',
            ],
            'effect' => [
                'data_type' => 'string',
                'input_type' => 'select',
                'options' => $passive_boosts,
            ],
        ],
    ],
    'combat_boosts' => [
        'count' => 3,
        'num_required' => 0,
        'variables' => [
            'power' => [
                'data_type' => 'float',
                'input_type' => 'text',
            ],
            'effect' => [
                'data_type' => 'string',
                'input_type' => 'select',
                'options' => $bloodline_combat_boosts,
            ],
        ],
    ],
    'jutsu' => [
        'count' => 3,
        'num_required' => 1,
        'variables' => $jutsu_constraints,
    ],
];
$constraints['jutsu']['variables']['hand_seals']['unique_required'] = false;

return $constraints;

