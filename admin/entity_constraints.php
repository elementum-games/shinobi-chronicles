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
        'data_type' => 'string',
        'input_type' => 'text',
        'pattern' => '/[0-9]+\:[0-9]+\:[0-9]+/',
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
    'village_rep' => [
        'date_type' => 'string',
        'input_type' => 'text',
    ],
    'village' => [
        'data_type' => 'string',
        'input_type' => 'radio',
        'options' => ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'],
    ],
];

/* NPC */
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
        'data_type' => 'float',
        'input_type' => 'text',
    ],
    'moves' => [
        'count' => 4,
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
            'use_type' => [
                'data_type' => 'string',
                'input_type' => 'text',
                'options' => [Jutsu::USE_TYPE_MELEE, Jutsu::USE_TYPE_PROJECTILE, Jutsu::USE_TYPE_BUFF, Jutsu::USE_TYPE_BARRIER],
            ],
            'effect' => [
                'data_type' => 'string',
                'input_type' => 'text',
                'options' => ['none', 'residual_damage', 'taijutsu_boost', 'ninjutsu_boost', 'genjutsu_boost', 'speed_boost', 'cast_speed_boost', 'speed_nerf'],
            ],
            'effect_amount' => [
                'data_type' => 'string',
                'input_type' => 'text',
            ],
            'effect_length' => [
                'data_type' => 'string',
                'input_type' => 'text',
            ],
        ],
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
    'yen_boost',
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
        'options' => [
            Item::PURCHASE_TYPE_PURCHASABLE => 'purchasable',
            Item::PURCHASE_TYPE_EVENT => 'event'
        ],
    ],
    'use_type' => [
        'data_type' => 'int',
        'input_type' => 'radio',
        'options' => [
            Item::USE_TYPE_WEAPON => 'weapon',
            Item::USE_TYPE_ARMOR => 'armor',
            Item::USE_TYPE_CONSUMABLE => 'consumable',
            Item::USE_TYPE_SPECIAL => 'Special'
        ],
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
        'field_required' => false, //Todo: make this mandatory in future
    ],
];

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
        'field_required' => false,
    ],
    'boost_amount' => [
        'data_type' => 'float',
        'input_type' => 'text',
        'field_required' => false,
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
    'field_required' => false,
];
$edit_clan_constraints['info'] = [
    'data_type' => 'string',
    'input_type' => 'text',
    'max_length' => 750,
    'field_required' => false,
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
$mission_reward_constraints = [
    'item_id' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'chance' => [
        'data_type' => 'int',
        'input_type' => 'text',
    ],
    'quantity' => [
        'data_type' => 'int',
        'input_type' => 'text',
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
        'options' => [1 => 'Village', 2 => 'Clan', 3 => 'Team', 4 => 'Special', 6 => 'Event'],
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
    'rewards' => [
        'count' => 4,
        'num_required' => 0,
        'variables' => $mission_reward_constraints,
    ],
];

return $constraints;
