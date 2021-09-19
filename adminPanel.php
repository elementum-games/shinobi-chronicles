<?php
/* 
File: 		admin_panel.php
Coder:		Levi Meahan
Created:	11/14/2013
Revised:	12/03/2013 by Levi Meahan
Purpose:	Function for admin panel where user and content data can be submitted and edited
Algorithm:	See master_plan.html
*/
function adminPanel() {
    global $system;
    global $player;
    global $self_link;
    global $id;
    global $RANK_NAMES;
    // Staff level check
    if($player->staff_level < System::SC_ADMINISTRATOR) {
        return false;
    }
    /* $pattern = '/[0-9]+\.[0-9]+/';
                    if(!preg_match($pattern, $location)) {
                        throw new Exception("Invalid location!");
                    }
        */
    // Menu
    echo "<table class='table'><tr><th>Menu</th></tr>
	<tr><td style='text-align:center;'>
		<a href='$self_link&page=create_ai'>Create AI</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=create_jutsu'>Create Jutsu</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=create_item'>Create Item</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=create_rank'>Create Rank</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=create_bloodline'>Create Bloodline</a>
	</td></tr>
	<tr><td style='text-align:center;'>
		<a href='$self_link&page=create_clan'>Create Clan</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=create_mission'>Create Mission</a>
	</td></tr>
	<tr><td style='text-align:center;'>
		<a href='$self_link&page=edit_ai'>Edit AI</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=edit_jutsu'>Edit Jutsu</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=edit_item'>Edit Item</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=edit_rank'>Edit Rank</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=edit_bloodline'>Edit Bloodline</a>	
	</td></tr>
	<tr><td style='text-align:center;'>
		<a href='$self_link&page=edit_clan'>Edit Clan</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=edit_team'>Edit Team</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=edit_mission'>Edit Mission</a>
	</td></tr>	
	<tr><td style='text-align:center;'>
		<a href='$self_link&page=edit_user'>Edit user</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=activate_user'>Activate user</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=delete_user'>Delete user</a> &nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='$self_link&page=give_bloodline'>Give Bloodline</a>
	</tr></td>";
    echo "</table>";
    // Variable sets
    {    /* Edit User*/
        $edit_user_variables = [
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
            'village' => [
                'data_type' => 'string',
                'input_type' => 'radio',
                'options' => ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'],
            ],
        ];
    }
    {    /* AI */
        $ai_variables = [
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
                        'max_length' => 300,
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
    }
    {    /* Jutsu */
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
        $jutsu_variables = [
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
    }
    {    /* Item  */
        $item_effects = [
            'residual_damage',
            'cripple',
            'daze',
            'harden',
            'lighten',
            'heal',
            'diffuse',
            'element',
        ];
        $item_variables = [
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
                'options' => [1 => 'weapon', 2 => 'armor', 3 => 'consumable'],
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
        ];
    }
    {    /* Bloodline */
        $combat_boosts = [
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
        $bloodline_variables = [
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
                        'options' => $combat_boosts,
                    ],
                    'remove' => [
                        'special' => 'remove',
                    ],
                ],
            ],
            'jutsu' => [
                'count' => 3,
                'num_required' => 1,
                'variables' => $jutsu_variables,
            ],
        ];
        $bloodline_variables['jutsu']['variables']['hand_seals']['unique_required'] = false;
    }
    {    /* Rank  */
        $rank_variables = [
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
    }
    {    /* Clan */
        $create_clan_variables = [
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
        $edit_clan_variables = $create_clan_variables;
        $edit_clan_variables['points'] = [
            'data_type' => 'int',
            'input_type' => 'text',
        ];
        $edit_clan_variables['leader'] = [
            'data_type' => 'int',
            'input_type' => 'text',
        ];
        $edit_clan_variables['elder_1'] = [
            'data_type' => 'int',
            'input_type' => 'text',
        ];
        $edit_clan_variables['elder_2'] = [
            'data_type' => 'int',
            'input_type' => 'text',
        ];
        $edit_clan_variables['challenge_1'] = [
            'data_type' => 'string',
            'input_type' => 'text',
        ];
        $edit_clan_variables['motto'] = [
            'data_type' => 'string',
            'input_type' => 'text',
            'max_length' => 175,
        ];
        $edit_clan_variables['info'] = [
            'data_type' => 'string',
            'input_type' => 'text',
            'max_length' => 750,
        ];
    }
    {    /* Team */
        $team_variables = [
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
    }
    {    /* Mission */
        $mission_stage_variables = [
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
        $mission_variables = [
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
                'variables' => $mission_stage_variables,
            ],
        ];
    }
    // Create AI
    if($_GET['page'] == 'create_ai') {
        /* Variables
        -ai_id
        -rank
        -name
        -max_health
        -ninjutsu_skill
        -genjutsu_skill
        -taijutsu_skill
        -cast_speed
        -speed
        -strength
        -endurance
        -intelligence
        -willpower
        -moves(json encoded text): [battle_text, power, jutsu_type] */
        /* Variables */
        $variables =& $ai_variables;
        $error = false;
        $data = [];
        if($_POST['ai_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `ai_opponents` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message("AI created!");
                }
                else {
                    throw new Exception("Error creating AI!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create AI</th></tr>
		<tr><td>
		<form action='$self_link&page=create_ai' method='post'>
		<style type='text/css'>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='ai_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create jutsu
    else if($_GET['page'] == 'create_jutsu') {
        /* Variables
        -jutsu_id
        -name
        -jutsu_type (ninjutsu, genjutsu, taijutsu)
        -rank (student, genin, etc.)
        -power
        -element (poison, fire, etc.)
        -purchase_type (1 = default, 2 = purchasable, 3 = non-purchasable)
        -purchase_cost
        -use_cost
        -offense (ninjutsu, genjutsu, taijutsu)
        -general (strength, intel, will, etc.)
        -second_general (str, int, will, etc)
        -description
        -battle_text
        -effect
        -effect_amount
        -effect_length */
        /* Variables */
        $variables =& $jutsu_variables;
        $error = false;
        $data = [];
        if($_POST['jutsu_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                // Hand seals hack
                $query = "INSERT INTO `jutsu` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message("Jutsu created!");
                }
                else {
                    throw new Exception("Error creating jutsu!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            foreach($variables as $var_name => $variable) {
                if(isset($_POST[$var_name])) {
                    $data[$var_name] = htmlspecialchars($_POST[$var_name], ENT_QUOTES);
                }
                else {
                    $data[$var_name] = '';
                }
            }
        }
        else {
            foreach($variables as $var_name => $variable) {
                $data[$var_name] = '';
            }
        }
        echo "<table class='table'><tr><th>Create Jutsu</th></tr>
		<tr><td>
		<form action='$self_link&page=create_jutsu' method='post'>
		<style type='text/css'>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='jutsu_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create item
    else if($_GET['page'] == 'create_item') {
        /* Variables
            -item_id
            -name
            -rank
            -purchase_type(1 = purchasable, 2 = event)
            -purchase_cost
            -use_type (1 = weapon, 2 = armor, 3 = consumable)
            -effect
            -effect_amount */
        $table_name = 'items';
        /* Variables */
        $variables =& $item_variables;
        $error = false;
        $data = [];
        if($_POST['item_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message("Item created!");
                }
                else {
                    throw new Exception("Error creating item!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            foreach($variables as $var_name => $variable) {
                if(isset($_POST[$var_name])) {
                    $data[$var_name] = htmlspecialchars($_POST[$var_name], ENT_QUOTES);
                }
                else {
                    $data[$var_name] = '';
                }
            }
        }
        else {
            foreach($variables as $var_name => $variable) {
                $data[$var_name] = '';
            }
        }
        echo "<table class='table'><tr><th>Create Item</th></tr>
		<tr><td>
		<form action='$self_link&page=create_item' method='post'>
		<style type='text/css'>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='item_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create Bloodline
    else if($_GET['page'] == 'create_bloodline') {
        $table_name = 'bloodlines';
        $content_name = 'bloodline';
        /* Variables */
        $variables =& $bloodline_variables;
        $error = false;
        $data = [];
        if($_POST[$content_name . '_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new Exception("Error creating " . $content_name . "!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='$self_link&page=create_" . $content_name . "' method='post'>
		<style type='text/css'>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create rank
    else if($_GET['page'] == 'create_rank') {
        $table_name = 'ranks';
        $content_name = 'rank';
        /* Variables */
        $variables =& $rank_variables;
        $error = false;
        $data = [];
        if($_POST[$content_name . '_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new Exception("Error creating " . $content_name . "!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='$self_link&page=create_" . $content_name . "' method='post'>
		<style type='text/css'>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create Clan
    else if($_GET['page'] == 'create_clan') {
        $table_name = 'clans';
        $content_name = 'clan';
        /* Variables */
        $variables =& $create_clan_variables;
        $error = false;
        $data = [];
        if($_POST[$content_name . '_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new Exception("Error creating " . $content_name . "!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='$self_link&page=create_" . $content_name . "' method='post'>
		<style type='text/css'>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Create Clan
    else if($_GET['page'] == 'create_mission') {
        $table_name = 'missions';
        $content_name = 'mission';
        /* Variables */
        $variables =& $mission_variables;
        $error = false;
        $data = [];
        if($_POST[$content_name . '_data']) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                foreach($data as $name => $var) {
                    $column_names .= "`$name`";
                    $column_data .= "'$var'";
                    if($count < count($data)) {
                        $column_names .= ', ';
                        $column_data .= ', ';
                    }
                    $count++;
                }
                $query = "INSERT INTO `$table_name` ($column_names) VALUES ($column_data)";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . " created!");
                }
                else {
                    throw new Exception("Error creating " . $content_name . "!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $error = true;
            }
            $system->printMessage();
        }
        if($error) {
            formPreloadData($variables, $data);
        }
        else {
            formPreloadData($variables, $data, false);
        }
        echo "<table class='table'><tr><th>Create " . ucwords(str_replace('_', ' ', $content_name)) . "</th></tr>
		<tr><td>
		<form action='$self_link&page=create_" . $content_name . "' method='post'>
		<style type='text/css'>
		label {
			display:inline-block;
			width:120px;
		}
		</style>";
        displayFormFields($variables, $data);
        echo "<br />
		<input type='submit' name='" . $content_name . "_data' value='Create' />
		</form>
		</td></tr></table>";
    }
    // Edit AI
    else if($_GET['page'] == 'edit_ai') {
        /* Variables */
        $variables =& $ai_variables;
        $select_ai = true;
        // Validate AI id
        if($_POST['ai_id']) {
            $ai_id = (int)$system->clean($_POST['ai_id']);
            $result = $system->query("SELECT * FROM `ai_opponents` WHERE `ai_id`='$ai_id'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid AI!");
                $system->printMessage();
            }
            else {
                $ai_data = $system->db_fetch($result);
                $select_ai = false;
            }
        }
        // POST submit edited data
        if($_POST['ai_data'] && !$select_ai) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `ai_opponents` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `ai_id`='$ai_id'";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message("AI " . $data['name'] . " has been edited!");
                    $select_ai = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_ai = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($ai_data && !$select_ai) {
            $data =& $ai_data;
            echo "<table class='table'><tr><th>Edit AI (" . stripslashes($ai_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_ai' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            displayFormFields($variables, $data);
            echo "<br />
			<input type='hidden' name='ai_id' value='{$ai_data['ai_id']}' />
			<input type='submit' name='ai_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_ai) {
            $result = $system->query("SELECT `ai_id`, `name` FROM `ai_opponents`");
            echo "<table class='table'><tr><th>Select AI</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_ai' method='post'>
			<select name='ai_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='{$row['ai_id']}'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit jutsu
    else if($_GET['page'] == 'edit_jutsu') {
        $select_jutsu = true;
        /* Variables */
        $variables =& $jutsu_variables;
        // Validate jutsu id
        if($_POST['jutsu_id']) {
            $jutsu_id = (int)$system->clean($_POST['jutsu_id']);
            $result = $system->query("SELECT * FROM `jutsu` WHERE `jutsu_id`='$jutsu_id'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid Jutsu!");
                $system->printMessage();
            }
            else {
                $jutsu_data = $system->db_fetch($result);
                $select_jutsu = false;
            }
        }
        // POST submit edited data
        if($_POST['jutsu_data'] && !$select_jutsu) {
            try {
                $content_id = $jutsu_id;
                $data = [];
                validateFormData($variables, $data, $content_id);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `jutsu` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `jutsu_id`='{$jutsu_data['jutsu_id']}'";
                //echo $query;
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message("Jutsu edited!");
                    $select_jutsu = true;
                }
                else {
                    throw new Exception("Error editing jutsu!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        // Form for editing data
        if($jutsu_data && !$select_jutsu) {
            $data =& $jutsu_data;
            echo "<table class='table'><tr><th>Edit Jutsu (" . stripslashes($jutsu_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_jutsu' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>
			<label>Jutsu ID:</label> $jutsu_id<br />";
            displayFormFields($variables, $data);
            echo "<br />
			<input type='hidden' name='jutsu_id' value='{$jutsu_data['jutsu_id']}' />
			<input type='submit' name='jutsu_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_jutsu) {
            $jutsu_array = [];
            $result = $system->query("SELECT `jutsu_id`, `name`, `jutsu_type`, `power`, `effect`, `effect_amount`, `effect_length`,
				`purchase_cost`, `element`, `rank` 
				FROM `jutsu` ORDER BY `rank` ASC, `purchase_cost` ASC"
            );
            while($row = $system->db_fetch($result)) {
                $jutsu_array[$row['jutsu_id']] = $row;
            }
            echo "<table class='table'><tr><th colspan='3'>Select Jutsu</th></tr>
			<tr>
				<th>Ninjutsu</th>
				<th>Taijutsu</th>
				<th>Genjutsu</th>
			</tr>
			<tr>
			<!--NINJUTSU-->
			<td>		
				<form action='$self_link&page=edit_jutsu' method='post'>
				<select name='jutsu_id'>";
            foreach($jutsu_array as $id => $jutsu) {
                if($jutsu['jutsu_type'] != 'ninjutsu') {
                    continue;
                }
                echo "<option value='$id'>" . stripslashes($jutsu['name']) . "</option>";
            }
            echo "</select>
				<input type='submit' value='Select' />
				</form>
			</td>
			<!--TAIJUTSU-->
			<td>
			<form action='$self_link&page=edit_jutsu' method='post'>
			<select name='jutsu_id'>";
            foreach($jutsu_array as $id => $jutsu) {
                if($jutsu['jutsu_type'] != 'taijutsu') {
                    continue;
                }
                echo "<option value='$id'>" . stripslashes($jutsu['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td>
			<!--GENJUTSU-->
			<td>
			<form action='$self_link&page=edit_jutsu' method='post'>
			<select name='jutsu_id'>";
            foreach($jutsu_array as $id => $jutsu) {
                if($jutsu['jutsu_type'] != 'genjutsu') {
                    continue;
                }
                echo "<option value='$id'>" . stripslashes($jutsu['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
            $jutsu_type = 'ninjutsu';
            if($_GET['jutsu_type']) {
                switch($_GET['jutsu_type']) {
                    case 'ninjutsu':
                        $jutsu_type = 'ninjutsu';
                        break;
                    case 'taijutsu':
                        $jutsu_type = 'taijutsu';
                        break;
                    case 'genjutsu':
                        $jutsu_type = 'genjutsu';
                        break;
                }
            }
            $style = "style='text-decoration:none;'";
            // Filter links
            echo "<p style='text-align:center;margin-bottom:0px;'>
				<a href='$self_link&page=edit_jutsu&jutsu_type=ninjutsu' " .
                ($jutsu_type == 'ninjutsu' ? $style : "") . ">Ninjutsu</a> |
				<a href='$self_link&page=edit_jutsu&jutsu_type=taijutsu' " .
                ($jutsu_type == 'taijutsu' ? $style : "") . ">Taijutsu</a> |
				<a href='$self_link&page=edit_jutsu&jutsu_type=genjutsu' " .
                ($jutsu_type == 'genjutsu' ? $style : "") . ">Genjutsu</a>
			</p>";
            // Show lists
            echo "<table class='table' style='margin-top:15px;'><tr>
				<th style='width:25%;'>Name</th>
				<th style='width:8%;'>Power</th>
				<th style='width:30%;'>Effect</th>
				<th style='width:18%;'>Element</th>
				<th style='width:19%;'>Cost</th>
			</tr>";
            echo "<tr><th colspan='5'>" . $RANK_NAMES[1] . "</th></tr>";
            $current_rank = 1;
            foreach($jutsu_array as $id => $jutsu) {
                if($jutsu['jutsu_type'] != $jutsu_type) {
                    continue;
                }
                if($jutsu['rank'] > $current_rank) {
                    $current_rank = $jutsu['rank'];
                    echo "<tr><th colspan='5'>" . $RANK_NAMES[$current_rank] . "</th></tr>";
                }
                echo "<tr>
					<td>" . $jutsu['name'] . "</td>
					<td>" . $jutsu['power'] . "</td>
					<td>" . ucwords(str_replace('_', ' ', $jutsu['effect'])) . ($jutsu['effect'] == 'none' ? '' :
                        " (" . $jutsu['effect_amount'] . "% / " . $jutsu['effect_length'] . ")") . "</td>
					<td>" . ucwords($jutsu['element']) . "</td>
					<td>&yen;" . $jutsu['purchase_cost'] . "</td>
				</tr>";
            }
            echo "</table>";
        }
    }
    // Edit item
    else if($_GET['page'] == 'edit_item') {
        $select_item = true;
        $table_name = 'items';
        /* Variables */
        $variables =& $item_variables;
        // Validate item id
        if($_POST['item_id']) {
            $item_id = (int)$system->clean($_POST['item_id']);
            $result = $system->query("SELECT * FROM `$table_name` WHERE `item_id`='$item_id'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid item!");
                $system->printMessage();
            }
            else {
                $item_data = $system->db_fetch($result);
                $select_item = false;
            }
        }
        // POST submit edited data
        if($_POST['item_data'] && !$select_item) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `item_id`='{$item_data['item_id']}'";
                //echo $query;
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message("Item edited!");
                    $select_item = true;
                }
                else {
                    throw new Exception("Error editing item!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        // Form for editing data
        if($item_data && !$select_item) {
            $data =& $item_data;
            echo "<table class='table'><tr><th>Edit Item (" . stripslashes($item_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_item' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            foreach($variables as $var_name => $variable) {
                if($variable['input_type'] == 'text') {
                    echo "<label for='$var_name'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
					<input type='text' name='$var_name' value='" . stripslashes($data[$var_name]) . "' /><br />";
                }
                else if($variable['input_type'] == 'radio' && !empty($variable['options'])) {
                    echo "<label for='$var_name' style='margin-top:5px;'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
					<p style='padding-left:10px;margin-top:5px;'>";
                    $count = 1;
                    foreach($variable['options'] as $id => $option) {
                        if($variable['data_type'] == 'int' || $variable['data_type'] == 'float') {
                            echo "<input type='radio' name='$var_name' value='$count' " .
                                ($data[$var_name] == $count ? "checked='checked'" : '') .
                                " />" . ucwords(str_replace("_", " ", $option));
                            $count++;
                        }
                        else if($variable['data_type'] == 'string') {
                            echo "<input type='radio' name='$var_name' value='$option' " .
                                ($data[$var_name] == $option ? "checked='checked'" : '') .
                                " />" . ucwords(str_replace("_", " ", $option));
                        }
                        echo "<br />";
                    }
                    echo "</p>";
                }
                else {
                    echo "Coming soon!<br />";
                }
            }
            echo "<br />
			<input type='hidden' name='item_id' value='{$item_data['item_id']}' />
			<input type='submit' name='item_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_item) {
            $result = $system->query("SELECT `item_id`, `name`, `effect`, `effect_amount`, `use_type`, `purchase_cost` 
				FROM `$table_name`"
            );
            $item_array = [];
            while($row = $system->db_fetch($result)) {
                $item_array[$row['item_id']] = $row;
            }
            echo "<table class='table'><tr><th>Select Item</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_item' method='post'>
			<select name='item_id'>";
            foreach($item_array as $id => $item) {
                echo "<option value='$id'>" . stripslashes($item['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
            $item_type = 1;
            if($_GET['item_type']) {
                switch($_GET['item_type']) {
                    case 'weapon':
                        $item_type = 1;
                        break;
                    case 'armor':
                        $item_type = 2;
                        break;
                    case 'consumable':
                        $item_type = 3;
                        break;
                }
            }
            $style = "style='text-decoration:none;'";
            // Filter links
            echo "<p style='text-align:center;margin-bottom:0px;'>
				<a href='$self_link&page=edit_item&item_type=weapon' " .
                ($item_type == 1 ? $style : "") . ">Weapons</a> |
				<a href='$self_link&page=edit_item&item_type=armor' " .
                ($item_type == 2 ? $style : "") . ">Armor</a> |
				<a href='$self_link&page=edit_item&item_type=consumable' " .
                ($item_type == 3 ? $style : "") . ">Consumables</a>
			</p>";
            // Show lists
            echo "<table class='table' style='margin-top:15px;'><tr>
				<th style='width:25%;'>Name</th>
				<th style='width:10%;'>Power</th>
				<th style='width:25%;'>Effect</th>
				<th style='width:20%;'>Cost</th>
			</tr>";
            foreach($item_array as $id => $item) {
                if($item['use_type'] != $item_type) {
                    continue;
                }
                echo "<tr>
					<td>" . $item['name'] . "</td>
					<td>" . $item['effect_amount'] . "</td>
					<td>" . ucwords(str_replace('_', ' ', $item['effect'])) . "</td>
					<td>&yen;" . $item['purchase_cost'] . "</td>
				</tr>";
            }
            echo "</table>";
        }
    }
    // Edit Bloodline
    else if($_GET['page'] == 'edit_bloodline') {
        $table_name = 'bloodlines';
        $content_name = 'bloodline';
        /* Variables */
        $variables =& $bloodline_variables;
        $select_content = true;
        // Validate AI id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit AI
    else if($_GET['page'] == 'edit_rank') {
        $table_name = 'ranks';
        $content_name = 'rank';
        /* Variables */
        $variables =& $rank_variables;
        $select_content = true;
        // Validate content id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit Clan
    else if($_GET['page'] == 'edit_clan') {
        $table_name = 'clans';
        $content_name = 'clan';
        /* Variables */
        $variables =& $edit_clan_variables;
        $select_content = true;
        // Validate AI id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>
			<label>Clan ID:</label> " . $content_data['clan_id'] . "<br />";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit Team
    else if($_GET['page'] == 'edit_team') {
        $table_name = 'teams';
        $content_name = 'team';
        /* Variables */
        $variables =& $team_variables;
        $select_content = true;
        // Validate AI id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>
			<label>Team ID:</label> " . $content_data['team_id'] . "<br />";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    // Edit Mission
    else if($_GET['page'] == 'edit_mission') {
        $table_name = 'missions';
        $content_name = 'mission';
        /* Variables */
        $variables =& $mission_variables;
        $select_content = true;
        // Validate content id
        if($_POST[$content_name . '_id']) {
            $content_id = (int)$system->clean($_POST[$content_name . '_id']);
            $result = $system->query("SELECT * FROM `{$table_name}` WHERE `{$content_name}_id`='$content_id'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid $content_name!");
                $system->printMessage();
            }
            else {
                $content_data = $system->db_fetch($result);
                $select_content = false;
            }
        }
        // POST submit edited data
        if($_POST[$content_name . '_data'] && !$select_content) {
            try {
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `$table_name` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `{$content_name}_id`='$content_id'";
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message(ucwords($content_name) . ' ' . $data['name'] . " has been edited!");
                    $select_content = true;
                }
                else {
                    throw new Exception("Error editing " . $data['name'] . "! (Or data is the same)");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
                $select_content = false;
            }
            $system->printMessage();
        }
        // Form for editing data
        if($content_data && !$select_content) {
            echo "<table class='table'><tr><th>Edit " . $content_name . " (" . stripslashes($content_data['name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>
			<label>Mission ID:</label> " . $content_data['mission_id'] . "<br />";
            displayFormFields($variables, $content_data);
            echo "<br />
			<input type='hidden' name='{$content_name}_id' value='" . $content_data[$content_name . '_id'] . "' />
			<input type='submit' name='{$content_name}_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_content) {
            $result = $system->query("SELECT `{$content_name}_id`, `name` FROM `$table_name`");
            echo "<table class='table'><tr><th>Select $content_name</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_{$content_name}' method='post'>
			<select name='{$content_name}_id'>";
            while($row = $system->db_fetch($result)) {
                echo "<option value='" . $row[$content_name . '_id'] . "'>" . stripslashes($row['name']) . "</option>";
            }
            echo "</select>
			<input type='submit' value='Select' />
			</form>
			</td></tr></table>";
        }
    }
    /* USER ADMINISTRATION PAGES */
    else if($_GET['page'] == 'edit_user') {
        $select_user = true;
        /* Variables
            -email
            -avatar_link
            -gender
            -level
            -rank
            -health
            -max_health
            -stamina
            -max_stamina
            -chakra
            -max_chakra
            -regen_rate
            -exp
            -money
            -pvp_wins
            -pvp_losses
            -ai_wins
            -ai_losses
            -ninjutsu_skill
            -genjutsu_skill
            -taijutsu_skill
            -bloodline_skill
            -cast_speed
            -speed
            -strength
            -endurance
            -intelligence
            -willpower
            -battle_id
            -location(x.y)
            -awake
            -village
            -staff_level
            */
        /* Variables */
        $variables =& $edit_user_variables;
        if($player->staff_level >= System::SC_HEAD_ADMINISTRATOR) {
            $variables['staff_level'] = [
                'data_type' => 'int',
                'input_type' => 'radio',
                'options' => [0 => 'normal_user', System::SC_MODERATOR => 'moderator', System::SC_HEAD_MODERATOR => 'head moderator',
                    System::SC_ADMINISTRATOR => 'administrator', System::SC_HEAD_ADMINISTRATOR => 'head administrator'],
            ];
        }
        // Validate user name
        if($_GET['user_name']) {
            $user_name = $system->clean($_GET['user_name']);
            $result = $system->query("SELECT * FROM `users` WHERE `user_name`='$user_name'");
            if($system->db_num_rows == 0) {
                $system->message("Invalid user!");
                $system->printMessage();
            }
            else {
                $user_data = $system->db_fetch($result);
                $select_user = false;
            }
        }
        // POST submit edited data
        if($_POST['user_data'] && !$select_user) {
            try {
                // Load form data
                $data = [];
                validateFormData($variables, $data);
                // Insert into database
                $column_names = '';
                $column_data = '';
                $count = 1;
                $query = "UPDATE `users` SET ";
                foreach($data as $name => $var) {
                    $query .= "`$name` = '$var'";
                    if($count < count($data)) {
                        $query .= ', ';
                    }
                    $count++;
                }
                $query .= "WHERE `user_id`='{$user_data['user_id']}'";
                // echo $query;
                $system->query($query);
                if($system->db_affected_rows == 1) {
                    $system->message("User edited!");
                    $select_user = true;
                    if($user_data['user_id'] == $player->user_id) {
                        $player->loadData();
                    }
                }
                else {
                    throw new Exception("Error editing user!");
                }
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        // Form for editing data
        if($user_data && !$select_user) {
            $data =& $user_data;
            echo "<table class='table'><tr><th>Edit User (" . stripslashes($data['user_name']) . ")</th></tr>
			<tr><td>
			<form action='$self_link&page=edit_user&user_name={$data['user_name']}' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width:120px;
			}
			</style>";
            displayFormFields($variables, $data);
            echo "<br />
			<input type='hidden' name='user_name' value='{$data['user_name']}' />
			<input type='submit' name='user_data' value='Edit' />
			</form>
			</td></tr></table>";
        }
        // Show form for selecting ID
        if($select_user) {
            echo "<table class='table'><tr><th>Edit User</th></tr>
			<tr><td style='text-align:center;'>
			<form action='$self_link&page=edit_user' method='get'>
			<b>Username</b><br />
			<input type='hidden' name='page' value='edit_user' />
			<input type='hidden' name='id' value='{$_GET['id']}'' />
			<input type='text' name='user_name' /><br />
			<input type='submit' value='Edit' />
			</form>
			</td></tr></table>";
        }
    }
    // Activate user
    else if($_GET['page'] == 'activate_user') {
        if($_POST['activate']) {
            $activate = $system->clean($_POST['activate']);
            $system->query("UPDATE `users` SET `user_verified`='1' WHERE `user_name`='$activate' LIMIT 1");
            if($system->db_affected_rows == 1) {
                $system->message("User activated!");
            }
            else {
                $system->message("Error activating user! (Invalid username, or user has already been activated)");
            }
            $system->printMessage();
        }
        echo "<table class='table'><tr><th>Activate User</th></tr>
		<tr><td style='text-align:center;'>
			<form action='$self_link&page=activate_user' method='post'>
			<b>-Username-</b><br />
			<input type='text' name='activate' /><br />
			<input type='submit' value='Activate' />
			</form>
		</td></tr></table>";
    }
    // Delete user
    else if($_GET['page'] == 'delete_user') {
        $select_user = true;
        if($_POST['user_name']) {
            $user_name = $system->clean($_POST['user_name']);
            try {
                $result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
                if($system->db_num_rows == 0) {
                    throw new Exception("Invalid user!");
                }
                $result = $system->db_fetch($result);
                $user_id = $result['user_id'];
                $user_name = $result['user_name'];
                if($result['staff_level'] >= System::SC_ADMINISTRATOR && $player->staff_level < System::SC_HEAD_ADMINISTRATOR) {
                    throw new Exception("You cannot delete other admins!");
                }
                if(!isset($_POST['confirm'])) {
                    echo "<table class='table'><tr><th>Delete User</th></tr>
					<tr><td style='text-align:center;'>
						<form action='$self_link&page=delete_user' method='post'>
						Are you sure you want to delete <b>$user_name</b>?<br />
						<input type='hidden' name='user_name' value='$user_name' />
						<input type='hidden' name='confirm' value='1' />
						<input type='submit' name='Confirm Deletion' />
						</form>
					</td></tr></table>";
                    $select_user = false;
                    throw new Exception('');
                }
                // Success, delete
                $system->query("DELETE FROM `users` WHERE `user_id`='$user_id' LIMIT 1");
                $system->query("DELETE FROM `user_inventory` WHERE `user_id`='$user_id' LIMIT 1");
                $system->query("DELETE FROM `user_bloodlines` WHERE `user_id`='$user_id' LIMIT 1");
                $system->message("User <b>$user_name</b> deleted.");
                // */
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
        }
        if($select_user) {
            $system->printMessage();
            echo "<table class='table'><tr><th>Delete User</th></tr>
			<tr><td style='text-align:center;'>
				<form action='$self_link&page=delete_user' method='post'>
				<b>Username</b><br />
				<input type='text' name='user_name' /><br />
				<input type='submit' name='Delete' />
				</form>
			</td></tr></table>";
        }
    }
    // Give bloodline
    else if($_GET['page'] == 'give_bloodline') {
        // Fetch BL list
        $result = $system->query("SELECT `bloodline_id`, `name` FROM `bloodlines`");
        if($system->db_num_rows == 0) {
            $system->message("No bloodlines in database!");
            $system->printMessage();
            return false;
        }
        $bloodlines = [];
        while($row = $system->db_fetch($result)) {
            $bloodlines[$row['bloodline_id']]['name'] = $row['name'];
        }
        if($_POST['give_bloodline']) {
            $bloodline_id = (int)$system->clean($_POST['bloodline_id']);
            $user_name = $system->clean($_POST['user_name']);
            try {
                if(!isset($bloodlines[$bloodline_id])) {
                    throw new Exception("Invalid bloodline!");
                }
                $result = $system->query("SELECT `user_id` FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
                if($system->db_num_rows == 0) {
                    throw new Exception("User does not exist!");
                }
                $result = $system->db_fetch($result);
                $user_id = $result['user_id'];
                $status = giveBloodline($bloodline_id, $user_id);
            } catch(Exception $e) {
                $system->message($e->getMessage());
            }
            $system->printMessage();
        }
        echo "<table class='table'><tr><th>Give Bloodline</th></tr>
		<tr><td>
		<form action='$self_link&page=give_bloodline' method='post'>
		<b>Bloodline</b><br />
		<select name='bloodline_id'>";
        foreach($bloodlines as $id => $bloodline) {
            echo "<option value='" . $id . "'>" . stripslashes($bloodline['name']) . "</option>";
        }
        echo "</select><br />
		<b>Username</b><br />
		<input type='text' name='user_name' /><br />
		<input type='submit' name='give_bloodline' value='Select' />
		</form>
		</td></tr></table>";
    }
}

function formPreloadData($variables, &$data, $post = true, $post_array = false) {
    if($post_array == false) {
        $post_array = $_POST;
    }
    foreach($variables as $var_name => $variable) {
        if(isset($variable['count']) or is_array(reset($variable))) {
            if(isset($variable['count'])) {
                $data_array = [];
                for($i = 0; $i < $variable['count']; $i++) {
                    $data_array[$i] = [];
                    formPreloadData($variable['variables'], $data_array[$i], $post, $post_array[$var_name][$i]);
                }
                $data[$var_name] = json_encode($data_array);
            }
            else {
            }
        }
        else {
            if(isset($post_array[$var_name]) && $post) {
                $data[$var_name] = htmlspecialchars($post_array[$var_name], ENT_QUOTES);
            }
            else {
                $data[$var_name] = '';
            }
        }
    }
}

// Throws exception if any validation error
function validateFormData($variables, &$data, $content_id = null) {
    global $system;
    foreach($variables as $var_name => $variable) {
        if(isset($_POST[$var_name])) {
            if(isset($variable['count']) or is_array(reset($variable))) {
                // Validate a set number of exact same variables
                if(isset($variable['count'])) {
                    $data_array = [];
                    $count = 0;
                    for($i = 0; $i < $variable['count']; $i++) {
                        $data_array[$count] = [];
                        foreach($variable['variables'] as $name => $var) {
                            if($var['special'] == 'remove' and !empty($_POST[$var_name][$i][$name])) {
                                $data_array[$count] = [];
                                break;
                            }
                            if(empty($_POST[$var_name][$i][$name])) {
                                continue;
                            }
                            else {
                                validateVariable($name, $_POST[$var_name][$i][$name], $var, $variables, $data_array[$count], $content_id);
                            }
                        }
                        if(empty($data_array[$count])) {
                            unset($data_array[$count]);
                        }
                        else {
                            $count++;
                        }
                    }
                    if(!isset($variable['num_required'])) {
                        $variable['num_required'] = $variable['count'];
                    }
                    if($count < $variable['num_required']) {
                        throw new Exception("Invalid $var_name! (needs at least " . $variable['num_required'] . ")");
                    }
                    $data[$var_name] = json_encode($data_array);
                }
                else {
                }
            }
            else {
                validateVariable($var_name, $_POST[$var_name], $variable, $variables, $data, $content_id);
            }
        }
        else {
            throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
        }
    }
}

function validateVariable($var_name, $input, $variable, &$variables, &$data, $content_id = null) {
    global $system;
    // Skip variable if it is not required
    if($variable['required_if']) {
        $req_var = $variable['required_if'];
        // If variable false/not set, continue
        if(empty($data[$req_var]) && empty($_POST[$req_var])) {
            return true;
        }
        // If variable is set and value matches not required key
        if(!empty($data[$req_var]) && $data[$req_var] == $variables[$req_var]['not_required_value']) {
            return true;
        }
        if(!empty($_POST[$req_var]) && $_POST[$req_var] == $variables[$req_var]['not_required_value']) {
            return true;
        }
    }
    // Check for special remove variable
    if($variable['special'] == 'remove') {
        return true;
    }
    $data[$var_name] = $system->clean($input);
    // Check for entry
    if(strlen($data[$var_name]) < 1) {
        throw new Exception("Please enter " . ucwords(str_replace("_", " ", $var_name)) . "!");
    }
    // Check numeric variables
    if($variable['data_type'] != 'string') {
        if(!is_numeric($data[$var_name])) {
            throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
        }
    }
    // Check variable matches restricted possibles list, if any
    if(!empty($variable['options'])) {
        if($variable['data_type'] == 'string') {
            if(array_search($data[$var_name], $variable['options']) === false) {
                throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
            }
        }
        else {
            if(!isset($variable['options'][$data[$var_name]])) {
                throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
            }
        }
    }
    // Check max length
    if(isset($variable['max_length'])) {
        if(strlen($data[$var_name]) > $variable['max_length']) {
            throw new Exception(ucwords(str_replace("_", " ", $var_name)) .
                " is too long! (" . strlen($data[$var_name]) . "/" . $variable['max_length'] . " chars)"
            );
        }
    }
    // Check pattern
    if(isset($variable['pattern'])) {
        if(!preg_match($variable['pattern'], $data[$var_name])) {
            throw new Exception("Invalid " . ucwords(str_replace("_", " ", $var_name)) . "!");
        }
    }
    // Check for uniqueness
    if(isset($variable['unique_required']) && $variable['unique_required'] == true) {
        if($content_id) {
            $query = "SELECT `{$variable['unique_column']}` FROM `{$variable['unique_table']}` 
				WHERE `{$variable['unique_column']}` = '" . $data[$var_name] . "' and `{$variable['id_column']}` != '$content_id' LIMIT 1";
        }
        else {
            $query = "SELECT `{$variable['unique_column']}` FROM `{$variable['unique_table']}` 
				WHERE `{$variable['unique_column']}` = '" . $data[$var_name] . "' LIMIT 1";
        }
        $result = $system->query($query);
        if($system->db_num_rows > 0) {
            throw new Exception("'" . ucwords(str_replace("_", " ", $var_name)) . "' needs to be unique, the value '" . $data[$var_name] . "' is already taken!");
        }
    }
}

function displayFormFields($variables, $data, $input_name_prefix = '') {
    foreach($variables as $var_name => $variable) {
        // Variable is an array of sub-variables
        if(isset($variable['count']) or is_array(reset($variable))) {
            // Display a set number of exact same variables
            if(isset($variable['count'])) {
                echo "<label for='{$var_name}'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>" .
                    (isset($variable['num_required']) ? "<i>(" . $variable['num_required'] . " required)</i>" : "") .
                    "<div style='margin-left:20px;margin-top:0px;'>";
                $data_vars = json_decode($data[$var_name], true);
                for($i = 0; $i < $variable['count']; $i++) {
                    $name = $var_name . '[' . $i . ']';
                    echo "<span style='display:block;margin-top:10px;font-weight:bold;'>#" . ($i + 1) .
                        ": <button onclick='$(\"#" . $var_name . '_' . $i . "\").toggle();return false;'>Show/Hide</button></span>";
                    echo "<div id='" . $var_name . '_' . $i . "'" .
                        (count($variable['variables']) > 4 ? " style='display:none;'" : '') . ">";
                    displayFormFields($variable['variables'], $data_vars[$i], $name);
                    echo "</div>";
                }
                if($variable['deselect']) {
                    $name = $var_name;
                    if($input_name_prefix) {
                        $name = $input_name_prefix . '[' . $name . ']';
                    }
                    echo "<br />
					<input type='radio' name='name' value='none' />None<br />";
                }
                echo "</div>";
            }
            // Display unique data structure based on array key names
            else {
                echo "<label for='$var_name'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
				<p style='margin-left:20px;margin-top:0px;'>";
                $data_vars = json_decode($data[$var_name], true);
                displayFormFields($variable, $data_vars, $var_name);
                if($variable['deselect']) {
                    $name = $var_name;
                    if($input_name_prefix) {
                        $name = $input_name_prefix . '[' . $name . ']';
                    }
                    echo "<br />
					<input type='radio' name='name' value='none' />None<br />";
                }
                echo "</p>";
            }
        }
        else {
            displayVariable($var_name, $variable, $data[$var_name], $input_name_prefix);
        }
    }
    return true;
}

function displayVariable($var_name, $variable, $current_value, $input_name_prefix = '') {
    global $system;
    // Set input name
    $name = $var_name;
    if($input_name_prefix) {
        $name = $input_name_prefix . '[' . $name . ']';
    }
    if($variable['input_type'] == 'text') {
        echo "<label for='$name'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
		<input type='text' name='$name' value='" . stripslashes($current_value) . "' /><br />";
    }
    else if($variable['input_type'] == 'radio' && !empty($variable['options'])) {
        echo "<label for='$name' style='margin-top:5px;'>" . ucwords(str_replace("_", " ", $var_name)) . ":</label>
		<p style='padding-left:10px;margin-top:5px;'>";
        $count = 1;
        foreach($variable['options'] as $id => $option) {
            if($variable['data_type'] == 'int' || $variable['data_type'] == 'float') {
                echo "<input type='radio' name='$name' value='$id' " .
                    ($current_value == $id ? "checked='checked'" : '') .
                    " />" . ucwords(str_replace("_", " ", $option));
                $count++;
            }
            else if($variable['data_type'] == 'string') {
                echo "<input type='radio' name='$name' value='$option' " .
                    ($current_value == $option ? "checked='checked'" : '') .
                    " />" . ucwords(str_replace("_", " ", $option));
            }
            echo "<br />";
        }
        echo "</p>";
    }
    else if($variable['special'] == 'remove') {
        echo "<label for='$name' style='margin-top:5px;'>Remove:</label>
		<p style='padding-left:10px;margin-top:5px;'>
			<input type='checkbox' name='$name' value='1' />";
    }
    else {
        echo "Coming soon!<br />";
    }
    return true;
}

function giveBloodline($bloodline_id, $user_id, $display = true) {
    global $system;
    $result = $system->query("SELECT * FROM `bloodlines` WHERE `bloodline_id` = '$bloodline_id' LIMIT 1");
    if($system->db_num_rows == 0) {
        throw new Exception("Invalid bloodline!");
    }
    $bloodline = $system->db_fetch($result);
    $user_bloodline['bloodline_id'] = $bloodline['bloodline_id'];
    $user_bloodline['name'] = $bloodline['name'];
    $user_bloodline['passive_boosts'] = $bloodline['passive_boosts'];
    $user_bloodline['combat_boosts'] = $bloodline['combat_boosts'];
    $user_bloodline['jutsu'] = $bloodline['jutsu'];
    // 5000 bl skill -> 20 power = 1 increment of BL effect
    // Heal: 1 increment = 100 heal
    $effects = [
        // Passive boosts
        'scout_range' => [
            'multiplier' => 0.00004,
        ],
        'stealth' => [
            'multiplier' => 0.00004,
        ],
        'regen' => [
            'multiplier' => 0.0001,
        ],
        // Combat boosts
        'heal' => [
            'multiplier' => 0.001,
        ],
        'ninjutsu_boost' => [
            'multiplier' => 0.01,
        ],
        'taijutsu_boost' => [
            'multiplier' => 0.01,
        ],
        'genjutsu_boost' => [
            'multiplier' => 0.01,
        ],
        'ninjutsu_resist' => [
            'multiplier' => 0.01,
        ],
        'taijutsu_resist' => [
            'multiplier' => 0.01,
        ],
        'genjutsu_resist' => [
            'multiplier' => 0.01,
        ],
        'speed_boost' => [
            'multiplier' => 0.001,
        ],
        'cast_speed_boost' => [
            'multiplier' => 0.001,
        ],
        'endurance_boost' => [
            'multiplier' => 0.001,
        ],
        'intelligence_boost' => [
            'multiplier' => 0.001,
        ],
        'willpower_boost' => [
            'multiplier' => 0.001,
        ],
    ];
    if($user_bloodline['passive_boosts']) {
        $user_bloodline['passive_boosts'] = json_decode($user_bloodline['passive_boosts'], true);
        foreach($user_bloodline['passive_boosts'] as $id => $boost) {
            if(!isset($effects[$boost['effect']])) {
            }
            else {
                $user_bloodline['passive_boosts'][$id]['power'] = round($boost['power'] * $effects[$boost['effect']]['multiplier'], 6);
            }
        }
        $user_bloodline['passive_boosts'] = json_encode($user_bloodline['passive_boosts']);
    }
    if($user_bloodline['combat_boosts']) {
        $user_bloodline['combat_boosts'] = json_decode($user_bloodline['combat_boosts'], true);
        foreach($user_bloodline['combat_boosts'] as $id => $boost) {
            if(!isset($effects[$boost['effect']])) {
            }
            else {
                $user_bloodline['combat_boosts'][$id]['power'] = round($boost['power'] * $effects[$boost['effect']]['multiplier'], 6);
            }
        }
        $user_bloodline['combat_boosts'] = json_encode($user_bloodline['combat_boosts']);
    }
    // move ids (level & exp -> 0)
    $user_bloodline['jutsu'] = false;
    $result = $system->query("SELECT `bloodline_id` FROM `user_bloodlines` WHERE `user_id`='$user_id' LIMIT 1");
    // Insert new row
    if($system->db_num_rows == 0) {
        $query = "INSERT INTO `user_bloodlines` (`user_id`, `bloodline_id`, `name`, `passive_boosts`, `combat_boosts`, `jutsu`)
			VALUES ('$user_id', '$bloodline_id', '{$user_bloodline['name']}', '{$user_bloodline['passive_boosts']}', 
			'{$user_bloodline['combat_boosts']}', '{$user_bloodline['jutsu']}')";
    }
    // Update existing row
    else {
        $query = "UPDATE `user_bloodlines` SET
			`bloodline_id` = '$bloodline_id',
			`name` = '{$user_bloodline['name']}',
			`passive_boosts` = '{$user_bloodline['passive_boosts']}',
			`combat_boosts` = '{$user_bloodline['combat_boosts']}',
			`jutsu` = '{$user_bloodline['jutsu']}'
			WHERE `user_id`='$user_id' LIMIT 1";
    }
    $system->query($query);
    if($system->db_affected_rows == 1) {
        if($display) {
            $system->message("Bloodline given!");
        }
        $result = $system->query("SELECT `exp`, `bloodline_skill` FROM `users` WHERE `user_id`='$user_id' LIMIT 1");
        $result = $system->db_fetch($result);
        $new_exp = $result['exp'];
        $new_bloodline_skill = $result['bloodline_skill'];
        if($result['bloodline_skill'] > 10) {
            $bloodline_skill_reduction = ($result['bloodline_skill'] - 10) * Bloodline::SKILL_REDUCTION_ON_CHANGE;
            $new_exp -= $bloodline_skill_reduction * 10;
            $new_bloodline_skill -= $bloodline_skill_reduction;
        }

        $query = "UPDATE `users` SET 
            `bloodline_id`='$bloodline_id', 
            `bloodline_name`='{$bloodline['name']}', 
            `bloodline_skill`='{$new_bloodline_skill}',
            `exp`='{$new_exp}'
			WHERE `user_id`='$user_id' LIMIT 1";

        $system->query($query);
        if($user_id == $_SESSION['user_id']) {
            global $player;
            $player->bloodline_id = $bloodline_id;
            $player->bloodline_name = $bloodline['name'];
            $player->exp = $new_exp;
            $player->bloodline_skill = $new_bloodline_skill;
        }
    }
    else {
        throw new Exception("Error giving bloodline! (Or user already has this BL)");
    }
    if($display) {
        $system->printMessage();
    }
    return true;
}
