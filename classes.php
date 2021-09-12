<?php
/*

File: 		classes.php

Coder:		Levi Meahan

Created:	02/21/2012

Revised:	08/13/2014 by Levi Meahan

Purpose:	Contains declarations for all OOP classes. Documentation included with each class or in documentation.html

*/

require("./classes/user.php");

require("./classes/system.php");

/* 	Class:		AI
	Purpose:	Contains all information for a specific AI, functions for selecting move, calculated damage dealt and 
				received, etc
*/
class AI {	
	public $id;
	public $ai_id;
	public $name;
	public $max_health;
	public $level;
	public $gender;
	
	public $ninjutsu_offense;
	public $genjutsu_offense;
	public $taijutsu_offense;
	public $ninjutsu_defense;
	public $genjutsu_defense;
	public $taijutsu_defense;
	
	public $speed;
	public $strength;
	public $intelligence;
	public $willpower;

	public $money;
	
	public $moves;
	
	public $current_move;
	
	/* function __construct(ai_id)
	Creates instance of the AI class. Sanitizes and checks AI id to ensure AI exists.
	-Parameters-
	@ai_id:	Id of the AI, used to select and update data from database
	*/
	public function __construct($ai_id) {
		global $system;
		$this->system =& $system;
		if(!$ai_id) {
			$system->error("Invalid AI opponent!");
			return false;
		}
		$this->ai_id = $system->clean($ai_id);
		$this->id = 'A' . $this->ai_id;
		
		
		$result = $system->query("SELECT `ai_id`, `name` FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
		if(mysqli_num_rows($result) == 0) {
			$system->error("AI does not exist!");
			return false;
		}
		
		$result = $this->system->db_fetch($result);
		
		$this->name = $result['name'];
		
		if(!isset($_SESSION['ai_logic'])) {
			$_SESSION['ai_logic'] = array();
			$_SESSION['ai_logic']['special_move_used'] = false;
		}
	}
	
	/* function loadData()
		Loads AI data from the database into class members
		-Parameters-
	*/
	public function loadData() {
		$result = $this->system->query("SELECT * FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
		$ai_data = $this->system->db_fetch($result);
		
		$this->rank = $ai_data['rank'];
		$this->max_health = $ai_data['max_health'];
		if(isset($_SESSION['ai_health'])) {
			$this->health = $_SESSION['ai_health'];
		}
		else {
			$this->health = $this->max_health;
			$_SESSION['ai_health'] = $this->health;
		}
		
		$this->gender = "Male";
		
		$this->level = $ai_data['level'];
		
		$this->ninjutsu_skill = $ai_data['ninjutsu_skill'];
		$this->genjutsu_skill = $ai_data['genjutsu_skill'];
		$this->taijutsu_skill = $ai_data['taijutsu_skill'];
		
		$this->cast_speed = $ai_data['cast_speed'];
		
		$this->speed = $ai_data['speed'];
		$this->strength = $ai_data['strength'];
		$this->intelligence = $ai_data['intelligence'];
		$this->willpower = $ai_data['willpower'];
	
		$attributes = array('cast_speed', 'speed', 'strength', 'intelligence', 'willpower');
		foreach($attributes as $attribute) {
			if($this->{$attribute} <= 0) {
				$this->{$attribute} = 1;
			}
		}
	
		$this->money = $ai_data['money'];
	
		$moves = json_decode($ai_data['moves']);
		
		$count = 0;
		foreach($moves as $move) {
			$this->moves[$count]['battle_text'] = $move->battle_text;
			$this->moves[$count]['power'] = $move->power;
			$this->moves[$count]['jutsu_type'] = $move->jutsu_type;
			if($move->jutsu_type == 'genjutsu') {
				$this->moves[$count]['effect'] = 'residual_damage';
				$this->moves[$count]['effect_amount'] = 30;
				$this->moves[$count]['effect_length'] = 3;
			}
			$count++;
		}
                
                $jutsuTypes = ['ninjutsu', 'taijutsu'];
                $aiType = rand(0, 1);
                $result = $this->system->query("SELECT `battle_text`, `power`, `jutsu_type` FROM `jutsu` WHERE `rank` = '{$this->rank}' AND `jutsu_type` = '{$jutsuTypes[$aiType]}' AND `purchase_type` != '1' AND `purchase_type` != '3' LIMIT 5");
                while ($row = $this->system->db_fetch($result)) {
                    $moveArr = [];
                    foreach($row as $type => $data) {
                        if($type == 'battle_text') {
                            $search = ['[player]', '[opponent]', '[gender]', '[gender2]'];
                            $replace = ['opponent1', 'player1', 'he', 'his'];
                            $data = str_replace($search, $replace, $data);
                            $data = str_replace(['player1', 'opponent1'], ['[player]', '[opponent]'], $data);
                        }
                        $moveArr[$type] = $data;
                    }
                    $this->moves[] = $moveArr;
                }
	}

	/* function chooseMove()
	*/
	public function chooseMove() {
		if(!$_SESSION['ai_logic']['special_move_used'] && $this->moves[1]) {
			$this->current_move =& $this->moves[1];
			$_SESSION['ai_logic']['special_move_used'] = true;
		}
		else {
                    $randMove = rand(1, (count($this->moves) - 1));
                    $this->current_move =& $this->moves[$randMove];
		}
		
		return $this->current_move;
	}
	
	/* function calcDamage() CONTAINS TEMP FIX
	*	Calculates raw damage based on AI stats and jutsu or item strength
		-Parameters-
		@attack: Copy of the attack data.
		@attack_type (default_jutsu, equipped_jutsu, item, bloodline_jutsu,): 
			Type of thing to check for, either item or jutsu
	*/
	public function calcDamage($attack, $attack_type = 'default_jutsu') {
		switch($attack_type) {
			case 'default_jutsu':
				break;
			case 'equipped_jutsu':
				break;
			default:
				throw new Exception("Invalid jutsu type!");
				break;
		}
		$offense_skill = $attack['jutsu_type'] . '_skill';
		$offense_boost = 0;
		if(isset($this->{$attack['jutsu_type'] . '_nerf'})) {
			// echo "Nerf: " . $this->{$attack['jutsu_type'] . '_nerf'} . "<br />";
			$offense_boost -= $this->{$attack['jutsu_type'] . '_nerf'};
		}
		
		// TEMP FIX (should be 0.10)
		$offense = (35 + $this->{$offense_skill} * 0.09);
		$offense += $offense_boost;	
		
		$min = 20;
		$max = 35;
		$rand = (int)(($min + $max) / 2);
		// $rand = mt_rand($min, $max);
		
		$damage = round($offense * $attack['power'] * $rand, 2);
		
		return $damage;
	}
	
	/* function calcDamageTaken()
	*	Calculates final damage taken based on AI stats and attack type
		-Parameters-
		@raw_damage: Raw damage dealt before defense
		@defense_type (ninjutsu, taijutsu, genjutsu, weapon): 
			Type of thing to check for, either item or jutsu
	*/
	public function calcDamageTaken($raw_damage, $defense_type) {		
		$defense = 50;
		
		switch($defense_type) {
			case 'ninjutsu':
				$defense += diminishing_returns($this->ninjutsu_skill * 0.03, 40);
				break;
			case 'genjutsu':
				$defense += diminishing_returns($this->genjutsu_skill * 0.03, 40);
				break;
			case 'taijutsu':
				$defense += diminishing_returns($this->taijutsu_skill * 0.03, 40);
				break;
		}	
		
		$damage = round($raw_damage / $defense, 2);
		if($damage < 0) {
			$damage = 0;
		}
		return $damage;
	}
	
	public function updateData() {
		$_SESSION['ai_health'] = $this->health;
	}
}

/* Class:		Bloodline
*/
class Bloodline {
    const SKILL_REDUCTION_ON_CHANGE = 0.5;

	public $bloodline_id;
	public $id;
	public $name;
	public $clan_id;
	public $rank;
	
	public $passive_boosts;
	public $combat_boosts;
	public $jutsu;
	
	public function __construct($bloodline_id, $user_id = false) {
		global $system;
		$this->system =& $system;
		if(!$bloodline_id) {
			$system->error("Invalid bloodline id!");
			return false;
		}
		$this->bloodline_id = $system->clean($bloodline_id);
		// $this->id = 'BL' . $this->user_id;
			
		$result = $system->query("SELECT * FROM `bloodlines` WHERE `bloodline_id`='$this->bloodline_id' LIMIT 1");
		if(mysqli_num_rows($result) == 0) {
			$system->error("Bloodline does not exist!");
			return false;
		}
		
		$bloodline_data = mysqli_fetch_assoc($result);
		
		$this->name = $bloodline_data['name'];
		$this->clan_id = $bloodline_data['clan_id'];
		$this->rank = $bloodline_data['rank'];
		
		$this->passive_boosts = $bloodline_data['passive_boosts'];
		$this->combat_boosts = $bloodline_data['combat_boosts'];
		$this->jutsu = $bloodline_data['jutsu'];
		if($this->jutsu) {
			$this->jutsu = json_decode($bloodline_data['jutsu'], true);
		}
		
		// Load user-related BL data if relevant
		if($user_id) {
			$user_id = (int)$user_id;
			$result = $system->query("SELECT * FROM `user_bloodlines` WHERE `user_id`=$user_id LIMIT 1");
			if(mysqli_num_rows($result) == 0) {
				$this->system->message("Invalid user bloodline data!");
				$this->system->printMessage();
				return false;
			}
			
			$user_bloodline = mysqli_fetch_assoc($result);
			$this->name = $user_bloodline['name'];
			
			if($user_bloodline['jutsu']) {
				$base_jutsu = $this->jutsu;
				$user_jutsu = json_decode($user_bloodline['jutsu'], true);
				$this->jutsu = array();
				
				if(is_array($user_jutsu)) {
					foreach($user_jutsu as $jutsu) {	
						$this->jutsu[$jutsu['jutsu_id']] = $base_jutsu[$jutsu['jutsu_id']];
						$this->jutsu[$jutsu['jutsu_id']]['jutsu_id'] = $jutsu['jutsu_id'];
						$this->jutsu[$jutsu['jutsu_id']]['level'] = $jutsu['level'];
						$this->jutsu[$jutsu['jutsu_id']]['exp'] = $jutsu['exp'];
						
						$this->jutsu[$jutsu['jutsu_id']]['power'] *= 1 + round($this->jutsu[$jutsu['jutsu_id']]['level'] * 0.005, 2);
						if($this->jutsu[$jutsu['jutsu_id']]['effect'] && $this->jutsu[$jutsu['jutsu_id']]['effect'] != 'none') {
							$this->jutsu[$jutsu['jutsu_id']]['effect_amount'] *= 1 + round($this->jutsu[$jutsu['jutsu_id']]['level'] * 0.002, 3);
						}
					}
				}
			}
			else {
				$this->jutsu = array();
			}
		}
		
		
		if($this->passive_boosts) {
			$this->passive_boosts = json_decode($this->passive_boosts, true);
			//var_dump($this->passive_boosts);
		}
		if($this->combat_boosts) {
			$this->combat_boosts = json_decode($this->combat_boosts, true);
		}
		
	}
}

/* Class:		Mission
*/
class Mission {
	public $mission_id;
	public $name;
	public $rank;
	public $mission_type;
	public $stages;
	public $money;
	
	private $system;
	
	public function __construct($mission_id, &$player = false, &$team = false) {
		global $system;
		$this->system = $system;
		$result = $this->system->query("SELECT * FROM `missions` WHERE `mission_id`='$mission_id' LIMIT 1");
		if($this->system->db_num_rows == 0) {
			return false;
		}
		
		$mission_data = $this->system->db_fetch($result);
		
		$this->player = $player;
		$this->team = $team;
		
		$this->mission_id = $mission_data['mission_id'];
		$this->name = $mission_data['name'];
		$this->rank = $mission_data['rank'];
		$this->mission_type = $mission_data['mission_type'];
		$this->money = $mission_data['money'];
		
		// Unset team if normal mission
		if($this->mission_type != 3) {
			unset($this->team);
			$this->team = false;
		}
		
		$stages = json_decode($mission_data['stages'], true);
		foreach($stages as $id => $stage) {
			$this->stages[($id + 1)] = $stage;
			$this->stages[($id + 1)]['stage_id'] = ($id + 1);
		}
				
		if($this->player && $this->player->mission_id) {
			$this->current_stage = $this->player->mission_stage;
		}
		else {
			if($this->team) {
				$this->nextTeamStage(1);
			}
			else {
				$this->nextStage(1);
			}
		}
	}
	
	public function nextStage($stage_id) {
		global $villages;
		
		// Check for multi-count, stop stage ID
		$new_stage = true;
		if($this->current_stage['count_needed']) {
			$this->current_stage['count']++;
			if($this->current_stage['count'] < $this->current_stage['count_needed']) {
				$stage_id--;
				$new_stage = false;
				$this->current_stage['description'] = $this->stages[$stage_id]['description'];
			}
		}		
		
		// Return signal for mission complete
		if($stage_id > count($this->stages) + 1) {
			return 2;
		}
		// Set to completion stage if all stages have been completed
		if($stage_id > count($this->stages)) {
			$this->current_stage = array(
				'stage_id' => $stage_id + 1,
				'action_type' => 'travel',
				'action_data' => $this->player->village_location,
				'description' => 'Report back to the village to complete the mission.'
			);
			$this->player->mission_stage = $this->current_stage;
			return 1;
		}
		
		// Load new stage data
		if($new_stage) {
			$this->current_stage = $this->stages[$stage_id];
			if($this->current_stage['count'] > 1) {
				$this->current_stage['count_needed'] = $this->current_stage['count'];
				$this->current_stage['count'] = 0;
			}
			else {
				$this->current_stage['count'] = 0;
			}
		}

		if($this->current_stage['action_type'] == 'travel' || $this->current_stage['action_type'] == 'search') {
			for($i = 0; $i < 3; $i++) {
				$location = $this->rollLocation($this->player->village_location);
				if(!isset($villages[$location]) || $location == $this->player->village_location) {
					break;
				}
			}
			
			$this->current_stage['action_data'] = $location;
			
		}
		
		$search_array = array('[action_data]', '[location_radius]');
		$replace_array = array($this->current_stage['action_data'], $this->current_stage['location_radius']);
		
		$this->current_stage['description'] = str_replace($search_array, $replace_array, $this->current_stage['description']);
		
		$this->player->mission_stage = $this->current_stage;
		return 1;
	}
	
	public function nextTeamStage($stage_id) {
		global $villages;
		
		// Return signal for mission complete
		if($stage_id > count($this->stages) + 1) {
			return 2;
		}
		
		// Check for old stage
		$old_stage = false;
		if($this->player->mission_stage['stage_id'] < $this->team['mission_stage']['stage_id']) {
			$old_stage = true;
		}
		
		// Check multi counts, block stage id
		$new_stage = true;
		if($this->team['mission_stage']['count_needed'] && !$old_stage) {
			$this->team['mission_stage']['count']++;
			if($this->team['mission_stage']['count'] < $this->team['mission_stage']['count_needed']) {
				$stage_id--;
				$new_stage = false;
				$mission_stage = json_encode($this->team['mission_stage']);
				$this->system->query("UPDATE `teams` SET `mission_stage`='$mission_stage' WHERE `team_id`={$this->team['id']} LIMIT 1");
			}
		}	
				
		// Set to completion stage if all stages have been completed
		if($stage_id > count($this->stages)) {
			$this->current_stage = array(
				'stage_id' => $stage_id + 1,
				'action_type' => 'travel',
				'action_data' => $this->player->village_location,
				'description' => 'Report back to the village to complete the mission.'
			);
			$this->player->mission_stage = $this->current_stage;
			return 1;
		}
		
		// Clear mission if it was cancelled
		if($new_stage && !$this->team['mission_id']) {
			echo 'cancelled';
			$this->player->mission_id = 0;
			return 1;
		}
		
		// Load new stage data	
		$this->current_stage = $this->stages[$stage_id];
		if($new_stage) {
			if($this->current_stage['count'] > 1) {
				$this->current_stage['count_needed'] = $this->current_stage['count'];
				$this->current_stage['count'] = 0;
			}
			else {
				$this->current_stage['count'] = 0;
				$this->current_stage['count_needed'] = 0;
			}
			
			$this->team['mission_stage']['stage_id'] = $stage_id;
			$this->team['mission_stage']['count'] = $this->current_stage['count'];
			$this->team['mission_stage']['count_needed'] = $this->current_stage['count_needed'];
			
			$mission_stage = json_encode($this->team['mission_stage']);
			
			$this->system->query("UPDATE `teams` SET `mission_stage`='$mission_stage' WHERE `team_id`='{$this->team['id']}' LIMIT 1");
		}

		if($this->current_stage['action_type'] == 'travel' || $this->current_stage['action_type'] == 'search') {
			for($i = 0; $i < 3; $i++) {
				$location = $this->rollLocation($this->player->village_location);
				if(!isset($villages[$location]) || $location == $this->player->village_location) {
					break;
				}
			}
			
			$this->current_stage['action_data'] = $location;		
		}
		
		$search_array = array('[action_data]', '[location_radius]');
		$replace_array = array($this->current_stage['action_data'], $this->current_stage['location_radius']);
		$this->current_stage['description'] = str_replace($search_array, $replace_array, $this->current_stage['description']);
		
		$this->player->mission_stage = $this->current_stage;
		return 1;
	}
	
	public function rollLocation($starting_location) {
		global $villages;
		
		$starting_location = explode('.', $starting_location);
			
		$max = $this->current_stage['location_radius'] * 2;
		$x = mt_rand(0, $max) - $this->current_stage['location_radius'];
		$y = mt_rand(0, $max) - $this->current_stage['location_radius'];
		if($x == 0 && $y == 0) {
			$x++;
		}
		
		$x += $starting_location[0];
		$y += $starting_location[1];
		
		if($x < 1) {
			$x = 1;
		}
		if($y < 1) {
			$y = 1;
		}
		
		if($x > SystemFunctions::MAP_SIZE_X) {
			$x = SystemFunctions::MAP_SIZE_X;
		}
		if($y > SystemFunctions::MAP_SIZE_Y) {
			$y = SystemFunctions::MAP_SIZE_Y;
		}
		
		return $x . '.' . $y;
	}

    /**
     * @param $player
     * @param $mission_id
     * @return Mission
     * @throws Exception
     */
	public static function start($player, $mission_id) {
        if($player->mission_id) {
            throw new Exception("You are already on a mission!");
        }

        $fight_timer = 20;
        if($player->last_ai > time() - $fight_timer) {
            throw new Exception("Please wait " . ($player->last_ai - (time() - $fight_timer)) . " more seconds!");
        }

        $mission = new Mission($mission_id, $player);

        $player->mission_id = $mission_id;

        return $mission;
    }
}

function diminishing_returns($val, $scale) {
    if($val < 0)
        return -diminishing_returns(-$val, $scale);
    $mult = $val / $scale;
    $trinum = (sqrt(8.0 * $mult + 1.0) - 1.0) / 2.0;
    return $trinum * $scale;
}