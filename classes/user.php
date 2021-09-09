<?php

/*	Class:		User
	Purpose:	Fetch user data and load into class variables.
*/
class User {
    const MIN_NAME_LENGTH = 2;

	public $system;
	
	// Loaded in construct
	public $id; // 'P' + user_id
	public $user_id;
	public $user_name;
	public $username_changes;
	public $blacklist;
	public $original_blacklist;
	
	// Loaded in loadData
	public $current_ip;
	public $last_ip;
	public $email;
	public $failed_logins;
	public $global_message_viewed;

	public $gender;
	public $village;
	public $level;
	public $rank;
	public $health;
	public $max_health;
	public $stamina;
	public $max_stamina;
	public $chakra;
	public $max_chakra;
	public $exp;
	public $staff_level;
	public $bloodline_id;
	public $bloodline_name;
	public $clan;
	public $village_location;
	public $in_village;
	public $location; // x.y
	public $x;
	public $y;

	public $train_type;
	public $train_gain;
	public $train_time;
	
	public $money;

	public $pvp_wins;
	public $pvp_losses;
	public $ai_wins;
	public $ai_losses;

	public $monthly_pvp;
	
	public $ninjutsu_skill;
	public $genjutsu_skill;
	public $taijutsu_skill;
	
	public $cast_speed;
	public $speed;
	public $intelligence;
	public $willpower;
	
	public $max_skill;
	public $max_gens;
	
	public $jutsu;
	public $ninjutsu_ids;
	public $genjutsu_ids;
	public $taijutsu_ids;
	
	public $equipped_jutsu;
	public $equipped_items;
	
	public $ban_type;
	public $ban_expire;
	public $journal_ban;
	public $avatar_ban;
	public $song_ban;
	
	public $layout;


	
	// Combat boosts
	public $ninjutsu_boost;
	public $taijutsu_boost;
	public $genjutsu_boost;
	
	public $cast_speed_boost;
	public $speed_boost;
	public $intelligence_boost;
	public $willpower_boost;
	
	public $ninjutsu_resist;
	public $taijutsu_resist;
	public $genjutsu_resist;
	
	public $defense_boost;
	
	// Combat nerfs
	public $ninjutsu_nerf;
	public $taijutsu_nerf;
	public $genjutsu_nerf;
	
	public $cast_speed_nerf;
	public $speed_nerf;
	public $intelligence_nerf;
	public $willpower_nerf;

    // Team
    public $team_invite;

	// Internal class variables
	public $inventory_loaded;
	
	/* function __construct(user_id, system)
		Creates instance of the User class. Sanitizes and checks user id to ensure user exists.
		-Paramaters-
		@user_id:	Id of the user, used to select and update data from database
	*/
    public function __construct($user_id) {
		global $system;
		$this->system =& $system;
		if(!$user_id) {
			$this->system->error("Invalid user id!");
			return false;
		}
		$this->user_id = $this->system->clean($user_id);
		$this->id = 'P' . $this->user_id;
			
		$result = $this->system->query("SELECT `user_id`, `user_name`, `ban_type`, `ban_expire`, `journal_ban`, `avatar_ban`, `song_ban`, `last_login`, 
			`forbidden_seal`, `staff_level`, `username_changes` 
			FROM `users` WHERE `user_id`='$this->user_id' LIMIT 1");
		if($this->system->db_num_rows == 0) {
			$this->system->error("User does not exist!");
			return false;
		}
		
		$result = $this->system->db_fetch($result);
		
		$this->user_name = $result['user_name'];
		$this->username_changes = $result['username_changes'];
		
		$this->staff_level = $result['staff_level'];
		
		$this->ban_type = $result['ban_type'];
		$this->ban_expire = $result['ban_expire'];
		$this->journal_ban = $result['journal_ban'];
		$this->avatar_ban = $result['avatar_ban'];
		$this->song_ban = $result['song_ban'];
		
		$this->last_login = $result['last_login'];
		
		$this->forbidden_seal = $result['forbidden_seal'];
		
		if($this->ban_type && $this->ban_expire <= time()) {
			$this->system->message("Your " . $this->ban_type . " ban has ended.");
			$this->ban_type = '';
			
			$this->system->query("UPDATE `users` SET `ban_type`='', `ban_expire`='0' WHERE `user_id`='$this->user_id' LIMIT 1");
		}
	
		$this->inventory_loaded = false;
	}
	
	/* function loadData()
		Loads user data from the database into class members
		-Paramaters-
		Update (1 = regen, 2 = training)
	*/
	public function loadData($UPDATE = 2, $remote_view = false) {
		$result = $this->system->query("SELECT * FROM `users` WHERE `user_id`='$this->user_id' LIMIT 1");
		$user_data = $this->system->db_fetch($result);
		
		$this->current_ip = $user_data['current_ip'];
		$this->last_ip = $user_data['last_ip'];
		// IP stuff
		if(!$remote_view && $this->current_ip != $_SERVER['REMOTE_ADDR']) {
			$this->last_ip = $this->current_ip;
			$this->current_ip = $_SERVER['REMOTE_ADDR'];
		}
		$this->email = $user_data['email'];
		
		$this->global_message_viewed = $user_data['global_message_viewed'];
		
		$this->last_update = $user_data['last_update'];
		$this->last_active = $user_data['last_active'];
		$this->failed_logins = $user_data['failed_logins'];
		$this->avatar_link = $user_data['avatar_link'];
		$this->profile_song = $user_data['profile_song'];
		
		$this->log_actions = $user_data['log_actions'];
		
		// Message blacklist
		$this->blacklist = array();
		$result = $this->system->query("SELECT `blocked_ids` FROM `blacklist` WHERE `user_id`='$this->user_id' LIMIT 1");
		if($this->system->db_num_rows != 0) {
			$blacklist = $this->system->db_fetch($result);
			$this->blacklist = json_decode($blacklist['blocked_ids'], true);
			$this->original_blacklist = $this->blacklist;
		}
		else {
			$blacklist_json = json_encode($this->blacklist);
			$this->system->query("INSERT INTO `blacklist` (`user_id`, `blocked_ids`) VALUES ('{$this->user_id}', '{$blacklist_json}')");
		}

		// Rank stuff
		$this->rank = $user_data['rank'];
		$rank_data = $this->system->query("SELECT * FROM `ranks` WHERE `rank_id`='$this->rank'");
		if($this->system->db_num_rows == 0) {
			$this->system->message("Invalid rank!");
			$this->system->printMessage("Invalid rank!");
		}
		else {
			$rank_data = $this->system->db_fetch($rank_data);
			$this->rank_name = $rank_data['name'];
			$this->base_level = $rank_data['base_level'];
			$this->max_level = $rank_data['max_level'];
			$this->base_stats = $rank_data['base_stats'];
			$this->stats_per_level = $rank_data['stats_per_level'];
			$this->health_gain = $rank_data['health_gain'];
			$this->pool_gain = $rank_data['pool_gain'];
			$this->stat_cap = $rank_data['stat_cap'];
			
			$this->exp_per_level = $this->stats_per_level * 10;
			
			$this->stats_max_level = $this->base_stats + ($this->stats_per_level * ($this->max_level - $this->base_level));
		}
				
		$this->gender = $user_data['gender'];
		$this->village = $user_data['village'];
		$this->level = $user_data['level'];
		$this->health = $user_data['health'];
		$this->max_health = $user_data['max_health'];
		$this->stamina = $user_data['stamina'];
		$this->max_stamina = $user_data['max_stamina'];
		$this->chakra = $user_data['chakra'];
		$this->max_chakra = $user_data['max_chakra'];
		
		if($this->health > $this->max_health) {
			$this->health = $this->max_health;
		}
		if($this->chakra > $this->max_chakra) {
			$this->chakra = $this->max_chakra;
		}
		if($this->stamina > $this->max_stamina) {
			$this->stamina = $this->max_stamina;
		}
		
		$this->regen_rate = $user_data['regen_rate'];
		$this->regen_boost = 0;
		
		
		$this->battle_id = $user_data['battle_id'];
		$this->challenge = $user_data['challenge'];
		
		$this->mission_id = $user_data['mission_id'];
		if($this->mission_id) {
			$this->mission_stage = json_decode($user_data['mission_stage'], true);
		}
		
		$this->last_ai = $user_data['last_ai'];
		$this->last_pvp = $user_data['last_pvp'];
		$this->last_death = $user_data['last_death'];
		
		$this->layout = $user_data['layout'];
		
		$this->exp = $user_data['exp'];
		$this->bloodline_id = $user_data['bloodline_id'];
		$this->bloodline_name = $user_data['bloodline_name'];
		$this->location = $user_data['location']; // x.y
		$location = explode(".", $this->location);
		$this->x = $location[0];
		$this->y = $location[1];
		
		$this->train_type = $user_data['train_type'];
		$this->train_gain = $user_data['train_gain'];
		$this->train_time = $user_data['train_time'];
		
		$this->money = $user_data['money'];
		$this->premium_credits = $user_data['premium_credits'];
		$this->premium_credits_purchased = $user_data['premium_credits_purchased'];

		$this->pvp_wins = $user_data['pvp_wins'];
		$this->pvp_losses = $user_data['pvp_losses'];
		$this->ai_wins = $user_data['ai_wins'];
		$this->ai_losses = $user_data['ai_losses'];
		$this->monthly_pvp = $user_data['monthly_pvp'];
		
		$this->ninjutsu_skill = $user_data['ninjutsu_skill'];
		$this->genjutsu_skill = $user_data['genjutsu_skill'];
		$this->taijutsu_skill = $user_data['taijutsu_skill'];
		
		$this->bloodline_skill = $user_data['bloodline_skill'];
		
		$this->cast_speed = $user_data['cast_speed'];
		$this->speed = $user_data['speed'];
		$this->intelligence = $user_data['intelligence'];
		$this->willpower = $user_data['willpower'];
		
		$this->total_stats = $this->ninjutsu_skill + $this->genjutsu_skill + $this->taijutsu_skill + $this->bloodline_skill +
			$this->cast_speed + $this->speed + $this->intelligence + $this->willpower;
		
		$this->ninjutsu_boost = 0;
		$this->genjutsu_boost = 0;
		$this->taijutsu_boost = 0;
		
		$this->cast_speed_boost = 0;
		$this->speed_boost = 0;
		$this->intelligence_boost = 0;
		$this->willpower_boost = 0;
		
		$this->defense_boost = 0;
		
		$this->ninjutsu_resist = 0;
		$this->taijutsu_resist = 0;
		$this->genjutsu_resist = 0;
		
		// Combat nerfs
		$this->ninjutsu_nerf = 0;
		$this->taijutsu_nerf = 0;
		$this->genjutsu_nerf = 0;
		
		$this->cast_speed_nerf = 0;
		$this->speed_nerf = 0;
		$this->intelligence_nerf = 0;
		$this->willpower_nerf = 0;
		
		$this->scout_range = $this->rank - 1;
		$this->stealth = 0;
		
		$this->village_changes = $user_data['village_changes'];
		$this->clan_changes = $user_data['clan_changes'];
		
		// Village
		$result = $this->system->query("SELECT `location` FROM `villages` WHERE `name`='{$this->village}' LIMIT 1");
		if($this->system->db_num_rows != 0) {
			$result = $this->system->db_fetch($result);
			$this->village_location = $result['location'];
			if($this->location == $this->village_location) {
				$this->in_village = true;
			}
		}
		else {
			$this->in_village = false;
		}
		
		// Clan
		$this->clan = $user_data['clan_id'];
		if($this->clan) {
			$result = $this->system->query("SELECT * FROM `clans` WHERE `clan_id`='$this->clan' LIMIT 1");
			if($this->system->db_num_rows == 0) {
				$this->clan = false;
			}
			else {
				$clan_data = $this->system->db_fetch($result);
				$this->clan = array(
					'id' => $clan_data['clan_id'],
					'name' => $clan_data['name'],
					'village' => $clan_data['village'],
					'bloodline_only' => $clan_data['bloodline_only'],
					'boost' => $clan_data['boost'],
					'boost_amount' => $clan_data['boost_amount'],
					'points' => $clan_data['points'],
					'leader' => $clan_data['leader'],
					'elder_1' => $clan_data['elder_1'],
					'elder_2' => $clan_data['elder_2'],
					'challenge_1' => $clan_data['challenge_1'],
					'logo' => $clan_data['logo'],
					'motto' => $clan_data['motto'],
					'info' => $clan_data['info'],
				);
				
				$this->clan_office = $user_data['clan_office'];
			}
		}
		
		// Team
		$this->team = $user_data['team_id'];
        $this->team_invite = null;
		if($this->team) {
			// Invite stuff
			if(substr($this->team, 0, 7) == 'invite:') {
				$this->team_invite = (int)substr($this->team, 7);
				$this->team = false;
			}
			// Player team stuff
			else {		
				$result = $this->system->query("SELECT * FROM `teams` WHERE `team_id`='$this->team' LIMIT 1");
				if($this->system->db_num_rows == 0) {
					$this->team = array();
					$this->team['id'] = 0;
				}
				else {
					$data = $this->system->db_fetch($result);
					$this->team = array(
						'id' => $data['team_id'],
						'name' => $data['name'],
						'village' => $data['village'],
						'type' => $data['type'],
						'boost' => $data['boost'],
						'boost_amount' => $data['boost_amount'],
						'points' => $data['points'],
						'monthly_points' => $data['monthly_points'],
						'leader' => $data['leader'],
						'members' => json_decode($data['members'], true),
						'mission_id' => $data['mission_id'],
						'mission_stage' => json_decode($data['mission_stage'], true),
						'logo' => $data['logo']
					);
					
					// Same square boost
					$result = $this->system->query("SELECT COUNT(`user_id`) as `count` FROM `users` 
						WHERE `team_id`='{$this->team['id']}' AND `location`='$this->location' AND `last_active` > UNIX_TIMESTAMP() - 120");
					$location_count = $this->system->db_fetch($result)['count'];
					
					$this->defense_boost += (($location_count - 1) * 0.1);
				}
			}
		}
		
		// Bloodline
		if($this->bloodline_id) {
			$this->bloodline = new Bloodline($this->bloodline_id, $this->user_id);
			
			// Each ratio operates on assumption of 5 BLP
			
			// Set ratios
			foreach($this->bloodline->passive_boosts as $id=>$boost) {
					$boost['power'] = floor($boost['power'] / 5);
					
					$bloodline_skill = $this->bloodline_skill + 100;
					
					switch($boost['effect']) {
						case 'regen':
							$regen_ratio = ($this->bloodline_skill / $this->stats_max_level);
							if($regen_ratio > 1) {
								$regen_ratio = 1;
							}
							$regen_ratio *= 0.075;
							$this->bloodline->passive_boosts[$id]['power'] = 
								round($boost['power'] * $regen_ratio, 2);
							break;
							
						case 'scout_range':
						case 'stealth':
							$boost_amount = 0;
							if($bloodline_skill < ($this->base_stats * 0.4) && $this->rank > 2) {
								if($this->rank > 3 && $bloodline_skill > 750) {
									$boost_amount = 1;
								}
								$this->bloodline->passive_boosts[$id]['progress'] = round($bloodline_skill / ($this->base_stats * 0.4), 2) * 100;
							}
							else {
								$boost_amount = $this->rank - 2;
								
								$extra_boost = $bloodline_skill / ($this->stats_max_level * 0.5);
								if($extra_boost > 0.99) {
									$boost_amount += 1;
								}
								else {
									$this->bloodline->passive_boosts[$id]['progress'] = round($extra_boost, 2) * 100;
								}
								
							}
							$this->bloodline->passive_boosts[$id]['effect_amount'] = $boost_amount;
							
							break;
					
					}
					
					
				}
						
			$ratios = array(
				'offense_boost'=> 0.04, 'defense_boost'=> 0.04, 
				'speed_boost' => 0.08, 'mental_boost' => 0.1, 'heal' => 0.04);
			// (boosts are 50% at 1:2 offense:blskill)
			
			foreach($this->bloodline->combat_boosts as $id=>$boost) {
				$boost['power'] = floor($boost['power'] / 5);
				
				$bloodline_skill = $this->bloodline_skill + 10;
				
				switch($boost['effect']) {
					case 'ninjutsu_boost':
					case 'genjutsu_boost':
					case 'taijutsu_boost':
						$boost_type = explode('_', $boost['effect'])[0];
						$skill_ratio = round($this->{$boost_type . '_skill'} / $bloodline_skill, 3);
						if($skill_ratio > 1.0) {
							$skill_ratio = 1.0;
						}
						else if($skill_ratio < 0.55) {
							$skill_ratio = 0.55;
						}
					
						$this->bloodline->combat_boosts[$id]['power'] = 
							round($boost['power'] * $skill_ratio * $ratios['offense_boost'], 3);
						break;
						
					case 'ninjutsu_resist':
					case 'genjutsu_resist':
					case 'taijutsu_resist':
						$boost_type = explode('_', $boost['effect'])[0];
						$skill_ratio = round($this->{$boost_type . '_skill'} / $bloodline_skill, 3);
						if($skill_ratio > 1.0) {
							$skill_ratio = 1.0;
						}
						else if($skill_ratio < 0.55) {
							$skill_ratio = 0.55;
						}
					
						// Est. jutsu power
						$skill = $bloodline_skill + $this->{$boost_type . '_skill'};
						$jutsu_power = $this->rank;
						$jutsu_power += round($skill / $this->stats_max_level, 3);
						if($jutsu_power > $this->rank + 1) {
							$jutsu_power = $this->rank + 1;
						}
						
						$multiplier = $jutsu_power;
					
						$this->bloodline->combat_boosts[$id]['power'] = 
							round($boost['power'] * $multiplier * $skill_ratio * $ratios['defense_boost'], 3);
						break;
						
					case 'speed_boost':
					case 'cast_speed_boost':
						$this->bloodline->combat_boosts[$id]['power'] = round($boost['power'] * $ratios['speed_boost'], 3);
						break;
						
					case 'intelligence_boost':
					case 'willpower_boost':
						$this->bloodline->combat_boosts[$id]['power'] = round($boost['power'] * $ratios['mental_boost'], 3);
						break;
						
					// (NEEDS TESTING/ADJUSTMENT)
					case 'heal':
						// Est. jutsu power
						$skill = $bloodline_skill;
						$jutsu_power = $this->rank;
						$jutsu_power += round($skill / $this->stats_max_level, 3);
						if($jutsu_power > $this->rank + 1) {
							$jutsu_power = $this->rank + 1;
						}
						$stat_multiplier = 35 * $jutsu_power; /* est jutsu power */
						
						// Defensive power
						$defense = 50 + ($this->total_stats * 0.01);
						
						$this->bloodline->combat_boosts[$id]['power'] = 
							round($boost['power'] * $stat_multiplier * $ratios['heal'] / $defense, 3);
						$this->bloodline->combat_boosts[$id]['divider'] = $defense;
						
						break;
				}
			}
			
			// Debug info
			if($this->system->debug['bloodline']) {
				foreach($this->bloodline->passive_boosts as $id=>$boost) {
					echo "Boost: " . $boost['effect'] . " : " . $boost['power'] . "<br />";
				}
				foreach($this->bloodline->combat_boosts as $id=>$boost) {
					echo "Boost: " . $boost['effect'] . " : " . $boost['power'] . "<br />";
				}
			}
					
			// Regen/scan range effects
			if(!empty($this->bloodline->passive_boosts)) {					
				foreach($this->bloodline->passive_boosts as $id => $effect) {
					switch($effect['effect']) {
						case 'scout_range':
							$this->scout_range += $effect['effect_amount'];
							break;
						case 'stealth':
							$this->stealth += $effect['effect_amount'];
							break;
						case 'regen':
							$this->bloodline->passive_boosts[$id]['effect_amount'] = floor($this->regen_rate * $effect['power']);
							$this->regen_boost += $this->bloodline->passive_boosts[$id]['effect_amount'];
							break;
						default:
							break;
					}
				}
			}
		}
		
		// Forbidden seal
		if($this->forbidden_seal) {
			$this->forbidden_seal = json_decode($user_data['forbidden_seal'], true);

			if($this->forbidden_seal['time'] < time() && $UPDATE >= 2 && !(!$this->forbidden_seal['level'] && $this->forbidden_seal['color'])) {
				$this->system->message("Your Forbidden Seal has receded.");
				$this->forbidden_seal = false;
			}
			// Regen boost
			else {
				if($this->forbidden_seal['level'] == 1) {
					$this->regen_boost += $this->regen_rate * 0.1;
				}
				else if($this->forbidden_seal['level'] == 2) {
					$this->regen_boost += $this->regen_rate * 0.2;
					$this->scout_range++;
				}
			
			}
		}

		//In Village Regen
		if($this->in_village){
			$this->regen_boost += 20 + $this->regen_rate;
		}
		
		// Elements
		$this->elements = $user_data['elements'];
		if($this->elements) {
			$this->elements = json_decode($this->elements, true);
		}
		
		// Regen/time-based events
		$time_difference = time() - $this->last_update;
		if($time_difference > 60 && $UPDATE >= 1) {
			$minutes = floor($time_difference / 60);

			
			$regen_amount = $minutes * $this->regen_rate;	
			$regen_amount += $this->regen_boost;
			
			
			// In-battle decrease
			if($this->battle_id or isset($_SESSION['ai_id'])) {
				$regen_amount -= round($regen_amount * 0.7, 1);
			}
			
			$this->health += $regen_amount;
			$this->chakra += $regen_amount;
			$this->stamina += $regen_amount;
			
			if($this->health > $this->max_health) {
				$this->health = $this->max_health;
			}
			if($this->chakra > $this->max_chakra) {
				$this->chakra = $this->max_chakra;
			}
			if($this->stamina > $this->max_stamina) {
				$this->stamina = $this->max_stamina;
			}		
		
			$this->last_update += $minutes * 60;
		}
		
		// Check training
        $display = '';
		if($this->train_time && $UPDATE >= 2) {
			if($this->train_time < time()) {
				// Jutsu training
				if(strpos($this->train_type, 'jutsu:') !== false) {
					require("variables.php");
					
					$gain = 5;
					if($TRAIN_BOOST) {
						$gain += $TRAIN_BOOST;
					}
					if($this->jutsu[$jutsu_id]['level'] + $gain > 100) {
						$gain = 100 - $this->jutsu[$jutsu_id]['level'];
					}
					
					$this->getInventory();
					$jutsu_id = $this->train_gain;
					if($this->checkInventory($jutsu_id, 'jutsu')) {
						if($this->jutsu[$jutsu_id]['level'] < 100) {

							$new_level = $this->jutsu[$jutsu_id]['level'] + $gain;

							if($new_level > 100) {
								$this->jutsu[$jutsu_id]['level'] = 100;
							}
							else {
								$this->jutsu[$jutsu_id]['level'] += $gain;
							}
							$message = $this->jutsu[$jutsu_id]['name'] . " has increased to level " . 
								$this->jutsu[$jutsu_id]['level'] . '.';
							
							$jutsu_skill_type = $this->jutsu[$jutsu_id]['jutsu_type'] . '_skill';
							if($this->total_stats < $this->stat_cap) {
								$this->{$jutsu_skill_type}++;
								$this->exp += 10;
								$message .= ' You have gained 1 ' . ucwords(str_replace('_', ' ', $jutsu_skill_type)) . 
									' and 10 experience.';
							}
								
							$this->system->message($message);

							if(! $this->ban_type) {
								$this->updateInventory();
							}
						}
					}
					
					$this->train_time = 0;
				}
				// Skill/attribute training
				else {
					// Check caps				
					$gain = $this->train_gain;
					
					$total_stats = $this->total_stats + $gain;

					if($total_stats > $this->stat_cap) {
						$gain -= $total_stats - $this->stat_cap;
						if($gain < 0) {
							$gain = 0;
						}
					}
					
					
					$this->{$this->train_type} += $gain;
					$this->exp += $gain * 10;
					
					$this->train_time = 0;
					$this->system->message("You have gained " . $gain . " " . ucwords(str_replace('_', ' ', $this->train_type)) . 
						" and " . ($gain * 10) . " experience.");
				}
			}
			else {
				require_once("functions.php");
				if(strpos($this->train_type, 'jutsu:') !== false) {
					$train_type = str_replace('jutsu:', '', $this->train_type);
					$display .= "<p class='trainingNotification'>Training: " . ucwords(str_replace('_', ' ', $train_type)) . "<br />" .
					"<span id='trainingTimer'>" . timeRemaining($this->train_time - time(), 'short', false, true) . " remaining</span></p>";
					$display .= "<script type='text/javascript'>
					var train_time = " . ($this->train_time - time()) . ";
					</script>";
				}
				else  {
					$display .= "<p class='trainingNotification'>Training: " . ucwords(str_replace('_', ' ', $this->train_type)) . "<br />" .
						"<span id='trainingTimer'>" . timeRemaining($this->train_time - time(), 'short', false, true) . " remaining</span></p>";
					$display .= "<script type='text/javascript'>
					var train_time = " . ($this->train_time - time()) . ";
					</script>";
				}
			}
		}
		
		return $display;
	}

	/* function getInventory()
	* Loads user jutsu, items, and equipped jutsu/bl jutsu/items
	*	-Paramaters-
	*/
	public function getInventory() {
		// Vars
		$max_equipped_jutsu = 3;	
		
		// Query user owned inventory
		$result = $this->system->query("SELECT * FROM `user_inventory` WHERE `user_id` = '{$this->user_id}'");
		
		$player_jutsu = array();
		$player_items = array();
		$player_bloodline_jutsu = array();
		$player_equipped_jutsu = array();
		$player_equipped_items = array();
		
		// Decode JSON of inventory into variables
		if($this->system->db_num_rows > 0) {
			$user_inventory = $this->system->db_fetch($result);
			$player_jutsu = json_decode($user_inventory['jutsu']);
			$player_items = json_decode($user_inventory['items']);
			$bloodline_jutsu = json_decode($user_inventory['bloodline_jutsu']);
			$equipped_jutsu = json_decode($user_inventory['equipped_jutsu']);
			$equipped_items = json_decode($user_inventory['equipped_items']);
		}
		else {
			$this->system->query("INSERT INTO `user_inventory` (`user_id`) VALUES ('{$this->user_id}')");
		}
		
		// Assemble query strings and fetch data of jutsu/items user owns from jutsu/item tables
		$player_jutsu_string = '';	
		
		if($player_jutsu) {
			$player_jutsu_array = $player_jutsu;
			$player_jutsu = array();
			foreach($player_jutsu_array as $jutsu) {
				if(!is_numeric($jutsu->jutsu_id)) {
					continue;
				}
				$player_jutsu[$jutsu->jutsu_id] = $jutsu;
				$player_jutsu_string .= $jutsu->jutsu_id . ',';
			}
			$player_jutsu_string = substr($player_jutsu_string, 0, strlen($player_jutsu_string) - 1);
			
			$this->jutsu = array();
			
			$result = $this->system->query(
				"SELECT * FROM `jutsu` WHERE `jutsu_id` IN ({$player_jutsu_string}) 
				AND `purchase_type` != '1' AND `rank` <= '{$this->rank}'");
			if($this->system->db_num_rows > 0) {
				while($jutsu = $this->system->db_fetch($result)) {
					if($player_jutsu[$jutsu['jutsu_id']]->level == 0) {
						$this->jutsu_scrolls[$jutsu['jutsu_id']] = $jutsu;
						$this->jutsu_scrolls[$jutsu['jutsu_id']]['level'] = $player_jutsu[$jutsu['jutsu_id']]->level;
						$this->jutsu_scrolls[$jutsu['jutsu_id']]['exp'] = $player_jutsu[$jutsu['jutsu_id']]->exp;
						continue;
					}
					$this->jutsu[$jutsu['jutsu_id']] = $jutsu;
					$this->jutsu[$jutsu['jutsu_id']]['level'] = $player_jutsu[$jutsu['jutsu_id']]->level;
					$this->jutsu[$jutsu['jutsu_id']]['exp'] = $player_jutsu[$jutsu['jutsu_id']]->exp;
					
					$this->jutsu[$jutsu['jutsu_id']]['power'] *= 1 + round($this->jutsu[$jutsu['jutsu_id']]['level'] * 0.003, 2);
					if($this->jutsu[$jutsu['jutsu_id']]['effect'] && $this->jutsu[$jutsu['jutsu_id']]['effect'] != 'none') {
						$this->jutsu[$jutsu['jutsu_id']]['effect_amount'] *= 1 + round($this->jutsu[$jutsu['jutsu_id']]['level'] * 0.002, 3);
					}
					
				
					switch($jutsu['jutsu_type']) {
						case 'ninjutsu':
							$this->ninjutsu_ids[] = $jutsu['jutsu_id'];
							break;
						case 'genjutsu':
							$this->genjutsu_ids[] = $jutsu['jutsu_id'];
							break;
						case 'taijutsu':
							$this->taijutsu_ids[] = $jutsu['jutsu_id'];
							break;
					}
				}				
			}
		}
		else {
			$this->jutsu = array();
		}
		
		$this->equipped_jutsu = array();
		if($equipped_jutsu) {
			$count = 0;
			foreach($equipped_jutsu as $jutsu) {
				if($this->checkInventory($jutsu->id, 'jutsu')) {
					$this->equipped_jutsu[$count]['id'] = $jutsu->id;
					$this->equipped_jutsu[$count]['type'] = $jutsu->type;
					$count++;
				}
			}
		}
		else {
			$this->equipped_jutsu = array();
		}
					
		if($player_items) {
			$player_items_array = $player_items;
			$player_items = array();
			foreach($player_items_array as $item) {
				if(!is_numeric($item->item_id)) {
					continue;
				}
				$player_items[$item->item_id] = $item;
				$player_items_string .= $item->item_id . ',';
			}
			$player_items_string = substr($player_items_string, 0, strlen($player_items_string) - 1);
			
			$this->items = array();
			
			$result = $this->system->query("SELECT * FROM `items` WHERE `item_id` IN ({$player_items_string})");
			if($this->system->db_num_rows > 0) {
				while($item = $this->system->db_fetch($result)) {
					$this->items[$item['item_id']] = $item;
					$this->items[$item['item_id']]['quantity'] = $player_items[$item['item_id']]->quantity;
				}
				
			}
			else {
				$this->items = array();
			}
		}
		else {
			$this->items = array();
		}
		
		$this->equipped_items = array();
		$this->equipped_weapons = array();
		$this->equipped_armor = array();
		if($equipped_items) {
			foreach($equipped_items as $item_id) {
				if($this->checkInventory($item_id, 'item')) {
					$this->equipped_items[] = $item_id;
					if($this->items[$item_id]['use_type'] == 1) {
						$this->equipped_weapons[] = $item_id;
					}
					else if($this->items[$item_id]['use_type'] == 2) {
						$this->equipped_armor[] = $item_id;
					}
				}
			}
		}
			
		// Temp number fix inside
		if($this->bloodline_id) {
			if(!empty($this->bloodline->combat_boosts)) {
				$bloodline_skill = 100 + $this->bloodline_skill;
						
				foreach($this->bloodline->combat_boosts as $id => $effect) {
					$this->bloodline->combat_boosts[$id]['effect_amount'] = round($effect['power'] * $bloodline_skill, 3);
				}
			}	
			
			// Apply bloodline passive combat boosts
			$this->bloodline_offense_boosts = array();
			$this->bloodline_defense_boosts = array();
			foreach($this->bloodline->combat_boosts as $id => $effect) {
				switch($effect['effect']) {		
					// Nin/Tai/Gen boost applied in User::calcDamage()
					case 'ninjutsu_boost':
					case 'taijutsu_boost':
					case 'genjutsu_boost':
						$x = count($this->bloodline_offense_boosts);
						$this->bloodline_offense_boosts[$x]['effect'] = $effect['effect'];
						$this->bloodline_offense_boosts[$x]['effect_amount'] = $effect['effect_amount'];
						break;
					
					case 'ninjutsu_resist':
					case 'genjutsu_resist':
					case 'taijutsu_resist':
						$x = count($this->bloodline_defense_boosts);
						$this->bloodline_defense_boosts[$x]['effect'] = $effect['effect'];
						$this->bloodline_defense_boosts[$x]['effect_amount'] = $effect['effect_amount'];
						break;
					
					case 'cast_speed_boost':
						$this->cast_speed_boost += $effect['effect_amount'];
						break;
					case 'speed_boost':
						$this->speed_boost += $effect['effect_amount'];
						break;
					case 'intelligence_boost':
						$this->intelligence_boost += $effect['effect_amount'];
						break;
					case 'willpower_boost':
						$this->willpower_boost += $effect['effect_amount'];
						break;
				}
			}
			
			/* <b>bloodline_jutsu</b> (Below is JSON encoded into text column)
			--jutsu_id
			--level
			--exp
			*/	
		
		}
		
		$this->inventory_loaded = true;
	}
	
	/* function checkInventory()
	*	Checks user inventory, returns true if the item/jutsu is owned, false if it isn't.
		-Paramaters-
		@item_id: Id of the item/jutsu to be checked for
		@inventory_type (jutsu, item): Type of thing to check for, either item or jutsu
	*/
	public function checkInventory($item_id, $inventory_type = 'jutsu') {
		if(!$item_id) {
			return false;
		}
			
		if($inventory_type == 'jutsu') {
			if(isset($this->jutsu[$item_id])) {
				return true;
			}
		}
		else if($inventory_type == 'item') {
			if(isset($this->items[$item_id])) {
				return true;
			}
		}
		
		return false;
	}
	
	/* function useJutsu
		pool check, calc exp, etc */
	public function useJutsu($jutsu, $jutsu_type = 'equipped_jutsu') {
		switch($jutsu['jutsu_type']) {
			case 'ninjutsu':
			case 'genjutsu':
				$energy_type = 'chakra';
				break;
			case 'taijutsu':
				$energy_type = 'stamina';
				break;
			default:
				return false;
				break;
		}
		
		if($this->{$energy_type} < $jutsu['use_cost']) {
			$this->system->message("You do not have enough $energy_type!");
			return false;
		}
		
		switch($jutsu_type) {
			case 'equipped_jutsu':
				// Element check
				if($jutsu['element'] && $jutsu['element'] != 'None') {
					if($this->elements) {
						if(array_search($jutsu['element'], $this->elements) === false) {
							$this->system->message("You do not possess the elemental chakra for this jutsu!");
							return false;
						}
					}
					else {
						$this->system->message("You do not possess the elemental chakra for this jutsu!");
						return false;
					}
				}
				
				if($this->jutsu[$jutsu['jutsu_id']]['level'] < 100) {
					$this->jutsu[$jutsu['jutsu_id']]['exp'] += round(1000 / ($this->jutsu[$jutsu['jutsu_id']]['level'] * 0.9));
				
					if($this->jutsu[$jutsu['jutsu_id']]['exp'] >= 1000) {
						$this->jutsu[$jutsu['jutsu_id']]['exp'] = 0;
						$this->jutsu[$jutsu['jutsu_id']]['level']++;
						$this->system->message($jutsu['name'] . " has increased to level " . $this->jutsu[$jutsu['jutsu_id']]['level'] . ".");
					}
				}
				
				$this->{$energy_type} -= $jutsu['use_cost'];
				break;
			case 'bloodline_jutsu':
				if($this->bloodline->jutsu[$jutsu['jutsu_id']]['level'] < 100) {
					$this->bloodline->jutsu[$jutsu['jutsu_id']]['exp'] += round(500 / ($this->bloodline->jutsu[$jutsu['jutsu_id']]['level'] * 0.9));
				
					if($this->bloodline->jutsu[$jutsu['jutsu_id']]['exp'] >= 1000) {
						$this->bloodline->jutsu[$jutsu['jutsu_id']]['exp'] = 0;
						$this->bloodline->jutsu[$jutsu['jutsu_id']]['level']++;
						$this->system->message($jutsu['name'] . " has increased to level " . $this->bloodline->jutsu[$jutsu['jutsu_id']]['level'] . ".");
					}
				}
				
				$this->{$energy_type} -= $jutsu['use_cost'];
				break;
			case 'default_jutsu':
				$this->{$energy_type} -= $jutsu['use_cost'];
				break;
				
			default:
				$this->system->message("Invalid jutsu type!");
				return false;
				break;
		}
		
		return true;
	}
	
	/* function calcDamage() CONTAINS TEMP NUMBER FIX
	*	Calculates raw damage based on player stats and jutsu or item strength
		-Paramaters-
		@attack: Copy of the attack data.
		@attack_type (default_jutsu, equipped_jutsu, item, bloodline_jutsu,): 
			Type of thing to check for, either item or jutsu
	*/
	public function calcDamage($attack, $attack_type = 'default_jutsu') {
		switch($attack_type) {
			case 'default_jutsu':
			case 'equipped_jutsu':
				$offense = 35 + ($this->{$attack['jutsu_type'] . '_skill'} * 0.10);
				break;
			case 'bloodline_jutsu':
				$offense = 35 + ($this->{$attack['jutsu_type'] . '_skill'} * 0.08) + ($this->bloodline_skill * 0.08);
				break;
			default:
				throw new Exception("Invalid jutsu type!");
				break;
		}
		$offense_boost = 0;		
		
		if(!empty($this->bloodline_offense_boosts)) {
			foreach($this->bloodline_offense_boosts as $id => $boost) {
				$boost_type = explode('_', $boost['effect'])[0];
				if($boost_type != $attack['jutsu_type']) {
					continue;
				}
				
				if($attack_type == 'bloodline_jutsu') {
					$multiplier = 0.5;
				}
				else {
					$multiplier = 1.0;
				}
			
				$effect_amount = round($boost['effect_amount'] * $multiplier, 2);
				$offense_boost += $effect_amount;
			}
		}
			
		if($this->system->debug['damage'])  {
			echo "Off: $offense +($offense_boost) -> " . ($offense + $offense_boost) . "<br />";
		}
		
		$offense += $offense_boost;
		$offense = round($offense, 2);
		if($offense < 0) {
			$offense = 0;
		}
		
		// TEMP FIX
		if($offense > 900) {
			$extra_offense = $offense - 900;
			$extra_offense *= 0.6;
			$offense = $extra_offense + 900;
		}
		
		$min = 25;
		$max = 45;
		$rand = (int)(($min + $max) / 2);
		// $rand = mt_rand($min, $max);
		
		$damage = $offense * $attack['power'] * $rand;
		
		// Add non-BL damage boosts
		$damage_boost = $this->{$attack['jutsu_type'] . '_boost'} - $this->{$attack['jutsu_type'] . '_nerf'};		
		if($this->system->debug['damage']) {
			echo 'Damage/boost: ' . $damage . ' / ' . $damage_boost . '<br />';
		}
		$damage = round($damage + $damage_boost, 2);
		if($damage < 0) {
			$damage = 0;
		}
		
		return $damage;
	}
	
	/* function calcDamageTaken()
	*	Calculates final damage taken based on player stats and attack type
		-Paramaters-
		@raw_damage: Raw damage dealt before defense
		@defense_type (ninjutsu, taijutsu, genjutsu, weapon): 
			Type of thing to check for, either item or jutsu
	*/
	public function calcDamageTaken($raw_damage, $defense_type) {
		$defense = 50 * (1 + $this->defense_boost);
		
		if($defense <= 0) {
			$defense = 1;
		}
		
		if(!empty($this->bloodline_defense_boosts)) {
			$boost_amount = 0;
			foreach($this->bloodline_defense_boosts as $id => $boost) {
				$boost_type = explode('_', $boost['effect'])[0];
				if($boost_type != $defense_type) {
					continue;
				}

				$boost_amount = $boost['effect_amount'] * 35;
				if($raw_damage < $boost_amount) {
					$this->bloodline_defense_boosts[$id]['effect_amount'] -= ($raw_damage / 35);
					$raw_damage = 0;
				}
				else {	
					$raw_damage -= $boost_amount;
					unset($this->bloodline_defense_boosts[$id]);
				}
			}
		}
		
		switch($defense_type) {
			case 'ninjutsu':
				$defense += diminishing_returns($this->ninjutsu_skill * 0.03, 50);
				$raw_damage -= $this->ninjutsu_resist;
				break;
			case 'genjutsu':
				$defense += diminishing_returns($this->genjutsu_skill * 0.03, 50);
				$raw_damage -= $this->genjutsu_resist;
				break;
			case 'taijutsu':
				$defense += diminishing_returns($this->taijutsu_skill * 0.03, 50);
				$raw_damage -= $this->taijutsu_resist;
				break;
		}	
		
		$damage = round($raw_damage / $defense, 2);	
		if($damage < 0.0) {
			$damage = 0;
		}
		return $damage;
	}
	
	/* function updateData()
		Updates user data from class members into database
		-Paramaters-
	*/
	public function updateData() {
		$this->location = $this->x . '.' . $this->y;
		
		$query = "UPDATE `users` SET
		`current_ip` = '$this->current_ip',
		`last_ip` = '$this->last_ip',
		`failed_logins` = '$this->failed_logins',
		`last_login` = '$this->last_login',
		`last_update` = '$this->last_update',
		`last_active` = '" . time() . "',
		`avatar_link` = '$this->avatar_link',
		`profile_song` = '$this->profile_song',
		`global_message_viewed` = '$this->global_message_viewed',
		
		`gender` = '$this->gender',
		`village` = '$this->village',
		`level` = '$this->level',
		`rank` = '$this->rank',
		`health` = '$this->health',
		`max_health` = '$this->max_health',
		`stamina` = '$this->stamina',
		`max_stamina` = '$this->max_stamina',
		`chakra` = '$this->chakra',
		`max_chakra` = '$this->max_chakra',
		`regen_rate` = '$this->regen_rate',
		
		`stealth` = '$this->stealth',

		`exp` = '$this->exp',
		`bloodline_id` = '$this->bloodline_id',
		`bloodline_name` = '$this->bloodline_name',";
		if($this->clan) {
			$query .= "`clan_id` = '{$this->clan['id']}',
			`clan_office`='{$this->clan_office}',";
		}
		
		if($this->team) {
			$query .= "`team_id` = '{$this->team['id']}',";
		}
		if($this->team_invite) {
			"`team_id` = 'invite:{$this->team_invite}',";
		}
		
		
		$query .= "`battle_id` = '$this->battle_id',
		`challenge` = '$this->challenge',
		`location` = '$this->location',";
		if($this->mission_id) {
			if(is_array($this->mission_stage)) {
				$this->mission_stage = json_encode($this->mission_stage);
			}
			$query .= "`mission_id`='$this->mission_id',
			`mission_stage`='$this->mission_stage',";
		}
		else {
			$query .= "`mission_id`=0,";
		}
		
		$query .= "`last_ai` = '$this->last_ai',
		`last_pvp` = '$this->last_pvp',
		`last_death` = '$this->last_death',";
		
		$forbidden_seal = $this->forbidden_seal;
		if(is_array($forbidden_seal)) {
			$forbidden_seal = json_encode($forbidden_seal);
		}
		
		$elements = $this->elements;
		if(is_array($elements)) {
			$elements = json_encode($this->elements);
		}
		
		
		$query .= "`forbidden_seal`='$forbidden_seal',
		`train_type` = '$this->train_type',
		`train_gain` = '$this->train_gain',
		`train_time` = '$this->train_time',
		
		`money` = '$this->money',
		`premium_credits` = '$this->premium_credits',

		`pvp_wins` = '$this->pvp_wins',
		`pvp_losses` = '$this->pvp_losses',
		`ai_wins` = '$this->ai_wins',
		`ai_losses` = '$this->ai_losses',
		`monthly_pvp` = '$this->monthly_pvp',
		
		`elements` = '$elements',
		
		`ninjutsu_skill` = '$this->ninjutsu_skill',
		`genjutsu_skill` = '$this->genjutsu_skill',
		`taijutsu_skill` = '$this->taijutsu_skill',
		`bloodline_skill` = '$this->bloodline_skill',
		
		`cast_speed` = '$this->cast_speed',
		`speed` = '$this->speed',
		`intelligence` = '$this->intelligence',
		`willpower` = '$this->willpower',
		
		`village_changes` = '$this->village_changes',
		`clan_changes` = '$this->clan_changes'
		WHERE `user_id` = '{$this->user_id}' LIMIT 1";
		$this->system->query($query);

		// Update Blacklist
		if(count($this->blacklist) != count($this->original_blacklist)) {
			$blacklist_json = json_encode($this->blacklist);
			$this->system->query("UPDATE `blacklist` SET `blocked_ids`='{$blacklist_json}' WHERE `user_id`='{$this->user_id}' LIMIT 1");
		}
		
	}

	/* function updateInventory()
		Updates user inventory from class members into database
		-Paramaters-
	*/
	public function updateInventory() {
		if(!$this->inventory_loaded) {
			$this->system->error("Called update without fetching inventory!");
			return false;
		}
		
		$player_jutsu = array();
		$player_items = array();
		$player_bloodline_jutsu = array();
		$player_equipped_jutsu = array();
		$player_equipped_items = array();
		
		$jutsu_count = 0;
		$item_count = 0;
		
		if($this->jutsu && !empty($this->jutsu)) {
			foreach($this->jutsu as $jutsu) {
				$player_jutsu[$jutsu_count]['jutsu_id'] = $jutsu['jutsu_id'];
				$player_jutsu[$jutsu_count]['level'] = $jutsu['level'];
				$player_jutsu[$jutsu_count]['exp'] = $jutsu['exp'];
				$jutsu_count++;
			}
		}
		
		if($this->jutsu_scrolls && !empty($this->jutsu_scrolls)) {
			foreach($this->jutsu_scrolls as $jutsu) {
				$player_jutsu[$jutsu_count]['jutsu_id'] = $jutsu['jutsu_id'];
				$player_jutsu[$jutsu_count]['level'] = $jutsu['level'];
				$player_jutsu[$jutsu_count]['exp'] = $jutsu['exp'];
				$jutsu_count++;
			}
		}
		
		if($this->items && !empty($this->items)) {
			foreach($this->items as $item) {
				$player_items[$item_count]['item_id'] = $item['item_id'];
				$player_items[$item_count]['quantity'] = $item['quantity'];
				$item_count++;
			}
		}
		
		$player_jutsu_json = json_encode($player_jutsu);
		$player_items_json = json_encode($player_items);
		$player_equipped_jutsu_json = json_encode($this->equipped_jutsu);
		$player_equipped_items_json = json_encode($this->equipped_items);
		
		$this->system->query("UPDATE `user_inventory` SET 
			`jutsu` = '{$player_jutsu_json}',
			`items` = '{$player_items_json}',
			`equipped_jutsu` = '{$player_equipped_jutsu_json}',
			`equipped_items` = '{$player_equipped_items_json}'
			WHERE `user_id` = '{$this->user_id}' LIMIT 1");
		
		
		if($this->bloodline_id && !empty($this->bloodline->jutsu)) {
			$jutsu_count = 0;
			foreach($this->bloodline->jutsu as $jutsu) {
				if($jutsu['rank'] > $this->rank) {
					continue;
				}
				$bloodline_jutsu[$jutsu_count]['jutsu_id'] = $jutsu['jutsu_id'];
				$bloodline_jutsu[$jutsu_count]['level'] = $jutsu['level'];
				$bloodline_jutsu[$jutsu_count]['exp'] = $jutsu['exp'];
				$jutsu_count++;
			}
			
			$bloodline_jutsu_json = json_encode($bloodline_jutsu);
			
			$this->system->query("UPDATE `user_bloodlines` SET `jutsu` = '{$bloodline_jutsu_json}'
				WHERE `user_id` = '{$this->user_id}' LIMIT 1");
		}
		
		// strip down to id, level, exp
		// Json encode
		// run query
	}
	
}

