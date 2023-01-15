<?php

$constraints = [];

/* Edit User*/
$constraints['edit_user'] = [
    'email' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 64,
    ],
    'avatar_link' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 120,
    ],
    'gender' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 32,
    ],
    'level' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'rank' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'health' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'max_health' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'stamina' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'max_stamina' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'chakra' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'max_chakra' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'regen_rate' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'exp' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'money' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'premium_credits' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'premium_credits_purchased' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'pvp_wins' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'pvp_losses' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'ai_wins' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'ai_losses' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'ninjutsu_skill' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'taijutsu_skill' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'genjutsu_skill' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'bloodline_skill' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'cast_speed' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'speed' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'intelligence' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'willpower' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'battle_id' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'mission_id' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'location' => [
        'data_type' => 'float',
        'input_type' => 'text',
        'pattern' => '/[0-9]+\.[0-9]+/',
    ],
    'clan_id' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'clan_office' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'team_id' => [
        'data_type' => 'string',
        'input_type' => 'text',
    ],
    'spouse' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'village' => [
        'data_type' => 'string',
        'input_type' => 'radio',
        'options' => ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'],
    ],
];

/* AI */
$constraints['ai'] = [
    'rank' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
    ],
    'max_health' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'level' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'ninjutsu_skill' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'genjutsu_skill' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'taijutsu_skill' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'cast_speed' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'speed' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'intelligence' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'willpower' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'money' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'moves' => [
        'count' => 2,
        'num_required' => 1,
        'variables' => [
            'battle_text' => [
                'data_type' => 'string',
                'input_type' => 'text',
                'max_length' => 375,
            ],
            'power' => [
                'data_type' => 'float',
                'input_type' => 'text',
            ],
            'jutsu_type' => [
                'data_type' => 'string',
                'input_type' => 'text',
                'options' => ['ninjutsu', 'taijutsu', 'genjutsu'],
            ],
        ],
    ],
];

/* Jutsu */
$jutsu_effects = [
    'none',
    'release_genjutsu',
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
    'willpower_nerf',
];
$constraints['jutsu'] = [
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 25,
    ],
    'rank' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'power' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'hand_seals' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'unique_required' => true,
        'unique_table' => 'jutsu',
        'unique_column' => 'hand_seals',
        'id_column' => 'jutsu_id',
    ],
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
        'input_type' => 'text',
        'max_length' => 200,
    ],
    'battle_text' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 450,
    ],
    'use_type' => [
        'data_type' => 'string',
        'input_type' => 'radio',
        'options' => [Jutsu::USE_TYPE_PHYSICAL, Jutsu::USE_TYPE_PROJECTILE, Jutsu::USE_TYPE_BUFF, Jutsu::USE_TYPE_BARRIER],
    ],
    'jutsu_type' => [
        'data_type' => 'string',
        'input_type' => 'radio',
        'options' => [Jutsu::TYPE_NINJUTSU, Jutsu::TYPE_TAIJUTSU, Jutsu::TYPE_GENJUTSU],
    ],
    'purchase_type' => [
        'data_type' => 'int',
        'input_type' => 'radio',
        'options' => [
            Jutsu::PURCHASE_TYPE_DEFAULT => 'default',
            Jutsu::PURCHASE_TYPE_PURCHASEABLE => 'purchasable',
            Jutsu::PURCHASE_TYPE_NON_PURCHASEABLE => 'non-purchasable',
        ],
    ],
    'effect' => [
        'data_type' => 'string',
        'input_type' => 'radio',
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

/* Item  */
$item_effects = [
    'residual_damage',
    'cripple',
    'daze',
    'harden',
    'lighten',
    'cast_speed_boost',
    'heal',
    'diffuse',
    'element',
    'unknown',
];
$constraints['item'] = [
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 45,
    ],
    'rank' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'purchase_cost' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'purchase_type' => [
        'data_type' => 'int',
        'input_type' => 'radio',
        'options' => [1 => 'purchasable', 2 => 'event'],
    ],
    'use_type' => [
        'data_type' => 'int',
        'input_type' => 'radio',
        'options' => [1 => 'weapon', 2 => 'armor', 3 => 'consumable', 4 => 'Special'],
    ],
    'effect' => [
        'data_type' => 'string',
        'input_type' => 'radio',
        'options' => $item_effects,
    ],
    'effect_amount' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'description' => [
        'data_type' => 'string',
        'input_type' => 'text_area',
        'max_length' => 300,
    ],
];

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

$constraints['bloodline'] = [
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
        'input_type' => 'radio',
        'options' => [5 => 'Admin', 4 => 'Lesser', 3 => 'Common', 2 => 'Elite', 1 => 'Legendary'],
    ],
    'village' => [
        'data_type' => 'string',
        'input_type' => 'radio',
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
                'input_type' => 'radio',
                'options' => $passive_boosts,
            ],
            'remove' => [
                'special' => 'remove',
            ],
        ],
    ],
    'combat_boosts' => [
        'count' => 3,
        'num_required' => 0,
        'variables' => [
            'power' => [
                'data_type' => 'int',
                'input_type' => 'text',
            ],
            'effect' => [
                'data_type' => 'string',
                'input_type' => 'radio',
                'options' => $bloodline_combat_boosts,
            ],
            'remove' => [
                'special' => 'remove',
            ],
        ],
    ],
    'jutsu' => [
        'count' => 3,
        'num_required' => 1,
        'variables' => $constraints['jutsu'],
    ],
];
$constraints['bloodline']['jutsu']['variables']['hand_seals']['unique_required'] = false;

/* Rank  */
$constraints['rank'] = [
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 64,
    ],
    'base_level' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'max_level' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'base_stats' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'stats_per_level' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'health_gain' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'pool_gain' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'stat_cap' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
];

/* Clan */
$constraints['create_clan'] = [
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 50,
    ],
    'boost' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 40,
    ],
    'boost_amount' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'village' => [
        'data_type' => 'string',
        'input_type' => 'radio',
        'options' => ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'],
    ],
    'bloodline_only' => [
        'data_type' => 'int',
        'input_type' => 'radio',
        'options' => [0 => 'No', 1 => 'Yes'],
    ],
];

$edit_clan_constraints = $constraints['create_clan'];
$edit_clan_constraints['points'] = [
    'data_type' => 'int',
    'input_type' => 'text',
];
$edit_clan_constraints['leader'] = [
    'data_type' => 'int',
    'input_type' => 'text',
];
$edit_clan_constraints['elder_1'] = [
    'data_type' => 'int',
    'input_type' => 'text',
];
$edit_clan_constraints['elder_2'] = [
    'data_type' => 'int',
    'input_type' => 'text',
];
$edit_clan_constraints['challenge_1'] = [
    'data_type' => 'string',
    'input_type' => 'text',
];
$edit_clan_constraints['motto'] = [
    'data_type' => 'string',
    'input_type' => 'text',
    'max_length' => 175,
];
$edit_clan_constraints['info'] = [
    'data_type' => 'string',
    'input_type' => 'text',
    'max_length' => 750,
];

$constraints['edit_clan'] = $edit_clan_constraints;

/* Team */
$constraints['team'] = [
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 50,
    ],
    'village' => [
        'data_type' => 'string',
        'input_type' => 'radio',
        'options' => ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'],
    ],
    'type' => [
        'data_type' => 'int',
        'input_type' => 'radio',
        'options' => [1 => 'Shinobi', 2 => 'ANBU'],
    ],
    'boost' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 40,
    ],
    'boost_amount' => [
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'points' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'monthly_points' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'leader' => [
        'data_type' => 'string',
        'input_type' => 'text',
    ],
    'mission_id' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'logo' => [
        'data_type' => 'string',
        'input_type' => 'text',
    ],
];

/* Mission */
$mission_stage_constraints = [
    'action_type' => [
        'data_type' => 'string',
        'input_type' => 'radio',
        'options' => ['travel', 'search', 'combat'],
    ],
    'action_data' => [
        'data_type' => 'string',
        'input_type' => 'text',
    ],
    'location_radius' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'count' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'description' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 300,
    ],
];
$constraints['mission'] = [
    'name' => [
        'data_type' => 'string',
        'input_type' => 'text',
        'max_length' => 50,
    ],
    'rank' => [
        'data_type' => 'int',
        'input_type' => 'radio',
        'options' => [1 => 'D-Rank', 2 => 'C-Rank', 3 => 'B-Rank', 4 => 'A-Rank', 5 => 'S-Rank'],
    ],
    'mission_type' => [
        'data_type' => 'int',
        'input_type' => 'radio',
        'options' => [1 => 'Village', 2 => 'Clan', 3 => 'Team', 4 => 'Special', 5 => 'Survival'],
    ],
    'money' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'stages' => [
        'count' => 4,
        'num_required' => 1,
        'variables' => $mission_stage_constraints,
    ],
];

return $constraints;