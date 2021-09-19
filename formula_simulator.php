<?php
session_start();
if(!isset($_SESSION['user_id'])) {
	exit;
}

require("classes.php");
$system = new System();
$system->dbConnect();
$user = new User($_SESSION['user_id']);
$user->loadData();

if($user->staff_level < System::SC_ADMINISTRATOR) {
	exit;
}

$stats = array('ninjutsu_skill', 'taijutsu_skill', 'genjutsu_skill', 'speed', 'cast_speed', 'intelligence', 'willpower');
$scenario_stats = array('offense', 'speed', 'intelligence', 'willpower');

$jutsu_power = 5;
$jutsu_type = 'ninjutsu';

$player1_jutsu['jutsu_type'] = $jutsu_type;
$player2_jutsu['jutsu_type'] = $jutsu_type;

if($_POST['run_simulation'] && $_POST['mode'] == 'vs') {
	$player1 = $_POST['stats1'];
	$player2 = $_POST['stats2'];
	
	$player2['jutsu_power'] = $player1['jutsu_power'];
	
	$damages = calcDamage($player1, $player2);
	
	echo "<div style='width:500px;background-color:#EAEAEA;text-align:center;margin-left:auto;margin-right:auto;
		padding:8px;border:1px solid #000000;border-radius:10px;'>
	Player 1:<br />
	{$damages['player1']['raw_damage']} raw damage<br />
	{$damages['player1']['collision_damage']} post-collision damage<br />
	{$damages['player1']['damage']} final damage<br />";
	
	if($damages['collision_text']) {
		echo "<hr />" . $damages['collision_text'] . "<hr />";
	}
	else {
		echo "<hr />";
	}
	
	echo "Player 2:<br />
	{$damages['player2']['raw_damage']} raw damage<br />
	{$damages['player2']['collision_damage']} post-collision damage<br />
	{$damages['player2']['damage']} final damage<br />
	</div>";
}
else if($_POST['run_simulation'] && $_POST['mode'] == 'scenarios') {
	$base_level = $_POST['base_level'];
	$max_level = $_POST['max_level'];
	$base_skill = $_POST['base_skill'];
	$base_stats = $_POST['base_stats'];
	$base_health = $_POST['base_health'];
	$base_jutsu_power = $_POST['base_jutsu_power'];
	$health_gain = $_POST['health_gain'];
	$skill_gain = $_POST['skill_gain'];
	$stat_gain = $_POST['stat_gain'];
	$jutsu_power_gain = $_POST['jutsu_power_gain'];	
	
	$player1 = array(
		'ninjutsu_skill' => 10,
		'taijutsu_skill' => 10,
		'genjutsu_skill' => 10,
		'speed' => 5,
		'cast_speed' => 5,
		'intelligence' => 5,
		'willpower' => 5,
		'jutsu_power' => $base_jutsu_power
	);
	$player2 = array(
		'ninjutsu_skill' => 10,
		'taijutsu_skill' => 10,
		'genjutsu_skill' => 10,
		'speed' => 5,
		'cast_speed' => 5,
		'intelligence' => 5,
		'willpower' => 5,
		'jutsu_power' => $base_jutsu_power
	);
	
	$player1['jutsu_type'] = 'ninjutsu';
	$player2['jutsu_type'] = 'taijutsu';
	
	// Set jutsu type specific stats to base for rank
	switch($player1['jutsu_type']) {
		case 'ninjutsu':
			$player1['ninjutsu_skill'] = $base_skill;
			$player1['cast_speed'] = $base_stats;
			break;
		case 'taijutsu':
			$player1['taijutsu_skill'] = $base_skill;
			$player1['speed'] = $base_stats;
			break;
		case 'genjutsu':
			$player1['genjutsu_skill'] = $base_skill;
			break;
	}
	switch($player2['jutsu_type']) {
		case 'ninjutsu':
			$player2['ninjutsu_skill'] = $base_skill;
			$player2['cast_speed'] = $base_stats;
			break;
		case 'taijutsu':
			$player2['taijutsu_skill'] = $base_skill;
			$player2['speed'] = $base_stats;
			break;
		case 'genjutsu':
			$player2['genjutsu_skill'] = $base_skill;
			break;
	}
		
	$player1['jutsu_power'] = $base_jutsu_power;
	$player2['jutsu_power'] = $base_jutsu_power;
	
	$damages = array();
	$level = $base_level;
	$health = $base_health;
	
	// Calc damage ranges
	$damage = calcDamage($player1, $player2);
	$damages[$level]['player1'] = $damage['player1']['damage'];
	$damages[$level]['player2'] = $damage['player2']['damage'];
	$damages[$level]['health'] = $health;
	
	for($level = $base_level + 1; $level <= $max_level; $level++) {
		switch($player1['jutsu_type']) {
			case 'ninjutsu':
				$player1['ninjutsu_skill'] += $skill_gain;
				$player1['cast_speed'] += $stat_gain;
				break;
			case 'taijutsu':
				$player1['taijutsu_skill'] += $skill_gain;
				$player1['speed'] += $stat_gain;
				break;
			case 'genjutsu':
				$player1['genjutsu_skill'] += $skill_gain;
				break;
		}
		switch($player2['jutsu_type']) {
			case 'ninjutsu':
				$player2['ninjutsu_skill'] += $skill_gain;
				$player2['cast_speed'] += $stat_gain;
				break;
			case 'taijutsu':
				$player2['taijutsu_skill'] += $skill_gain;
				$player2['speed'] += $stat_gain;
				break;
			case 'genjutsu':
				$player2['genjutsu_skill'] += $skill_gain;
				break;
		}
		
		$health += $health_gain;
		$player1['jutsu_power'] += $jutsu_power_gain;
		$player2['jutsu_power'] += $jutsu_power_gain;
		
		$damage = calcDamage($player1, $player2);
		$damages[$level]['player1'] = $damage['player1']['damage'];
		$damages[$level]['player2'] = $damage['player2']['damage'];
		$damages[$level]['health'] = $health;
		
	}
	
	
	echo "<div style='width:500px;background-color:#EAEAEA;text-align:center;margin-left:auto;margin-right:auto;
		padding:8px;border:1px solid #000000;border-radius:10px;'>";
		foreach($damages as $level => $damage) {
			echo "<span style='display:inline-block;width:" . (70 + (strlen($max_level) * 10)) . "px;'>Level $level:</span>" . 
				$damage['health'] . " HP / " . sprintf("%.1f", $damage['player1']) . " damage (" . round($damage['health'] / $damage['player1'], 1) .
				" turns)<br />";
		}
		echo "Final stats: <br />" .
		$player1['ninjutsu_skill'] . ' nin skill, ' . $player1['taijutsu_skill'] . ' tai skill<br />' .
		$player1['cast_speed'] . ' cast speed, ' . $player1['speed'] . ' speed<br />' .
		$player1['jutsu_power'] . ' jutsu power.';
		
	echo "<br />
	</div>";
}
	
// Display form
$display = 'vs';
if($_POST['mode'] == 'scenarios') {
	$display = 'scenarios';
}
echo "<style type='text/css'>
label {
	display: inline-block;
}
</style>

<br />
<div style='text-align:center;'>
<script type='text/javascript' src='http://code.jquery.com/jquery-2.1.0.min.js'></script>
<script type='text/javascript'>
function changeDisplay(display_id) {
	$('.displayDiv').hide();
	$('#' + display_id).show();
}

</script>
<!--VS DISPLAY-->
<div id='vs' class='displayDiv' " . ($display == 'scenarios' ? "style='display:none;'" : '') . ">
	<form action='./formula_simulator.php' method='post'>
	<div style='width:300px;display:inline-block;border:1px solid #000000;border-radius:10px;'>
		Player 1<br />";
		foreach($stats as $stat) {
			echo "<label style='width:110px;'>$stat:</label>
				<input type='text' name='stats1[$stat]' value='{$_POST['stats1'][$stat]}' /><br />";
		}
		echo "<label style='width:110px;'>Jutsu power:</label>
			<input type='text' name='stats1[jutsu_power]' value='{$_POST['stats1']['jutsu_power']}' /><br /> 
		<input type='radio' name='stats1[jutsu_type]' value='ninjutsu' " . 
			($_POST['stats1']['jutsu_type'] == 'ninjutsu' ? "checked='checked'" : '') . "/> Ninjutsu<br />
		<input type='radio' name='stats1[jutsu_type]' value='taijutsu' " . 
			($_POST['stats1']['jutsu_type'] == 'taijutsu' ? "checked='checked'" : '') . "/> Taijutsu<br />
		<input type='radio' name='stats1[jutsu_type]' value='genjutsu' " . 
			($_POST['stats1']['jutsu_type'] == 'genjutsu' ? "checked='checked'" : '') . "/> Genjutsu<br />
	</div>

	<div style='width:300px;display:inline-block;border:1px solid #000000;border-radius:10px;margin-left:20px;'>
		Player 2<br />";
		foreach($stats as $stat) {
			echo "<label style='width:110px;'>$stat:</label>
				<input type='text' name='stats2[$stat]' value='{$_POST['stats2'][$stat]}' /><br />";
		}
		echo "<label style='width:110px;'>Jutsu power:</label>
			<input type='text' name='stats2[jutsu_power]' value='{$_POST['stats2']['jutsu_power']}' /><br /> 
		<input type='radio' name='stats2[jutsu_type]' value='ninjutsu' " . 
			($_POST['stats2']['jutsu_type'] == 'ninjutsu' ? "checked='checked'" : '') . "/> Ninjutsu<br />
		<input type='radio' name='stats2[jutsu_type]' value='taijutsu' " . 
			($_POST['stats2']['jutsu_type'] == 'taijutsu' ? "checked='checked'" : '') . "/> Taijutsu<br />
		<input type='radio' name='stats2[jutsu_type]' value='genjutsu' " . 
			($_POST['stats2']['jutsu_type'] == 'genjutsu' ? "checked='checked'" : '') . "/> Genjutsu<br />
	</div>
	
	<br />
	<br />
	<input type='radio' name='mode' value='vs' onclick='changeDisplay(\"vs\")' checked='checked' /> Versus<br />
	<input type='radio' name='mode' value='scenarios' onclick='changeDisplay(\"scenario\")' /> Scenarios<br />
	<input type='submit' name='run_simulation' value='Run Simulation' />
	</form>
</div>

<!--SCENARIO DISPLAY-->
<div id='scenario' class='displayDiv'" . ($display == 'vs' ? "style='display:none;'" : '') . ">
	<form action='./formula_simulator.php' method='post'>
	<div style='width:300px;display:inline-block;border:1px solid #000000;border-radius:10px;'>
		Sim details<br />
		Base level: <input type='text' name='base_level' value='{$_POST['base_level']}' /><br />
		Max level: <input type='text' name='max_level' value='{$_POST['max_level']}' /><br />
		Base skill: <input type='text' name='base_skill' value='{$_POST['base_skill']}' /><br />
		Base stats: <input type='text' name='base_stats' value='{$_POST['base_stats']}' /><br />
		Base health: <input type='text' name='base_health' value='{$_POST['base_health']}' /><br />
		Base jutsu power: <input type='text' name='base_jutsu_power' value='{$_POST['base_jutsu_power']}' /><br />
		<br />
		Health gain: <input type='text' name='health_gain' value='{$_POST['health_gain']}' /><br />
		Skill gain: <input type='text' name='skill_gain' value='{$_POST['skill_gain']}' /><br />
		Stat gain: <input type='text' name='stat_gain' value='{$_POST['stat_gain']}' /><br />
		Jutsu power gain: <input type='text' name='jutsu_power_gain' value='{$_POST['jutsu_power_gain']}' /><br />
	</div>
	<br />
	<br />
	<input type='radio' name='mode' value='vs' onclick='changeDisplay(\"vs\")' /> Versus<br />
	<input type='radio' name='mode' value='scenarios' onclick='changeDisplay(\"scenario\")' checked='checked' /> Scenarios<br />
	<input type='submit' name='run_simulation' value='Run Simulation' />
	</form>
</div>
</div>";

function jutsuCollision(&$player, &$opponent, &$player_damage, &$opponent_damage, $player_jutsu, $opponent_jutsu) {
	$collision_text = '';
		
	if($player_jutsu['element'] && $opponent_jutsu['element']) {
		// Fire > Wind > Lightning > Earth > Water > Fire
		if($player_jutsu['element'] == 'fire') {
			if($opponent_jutsu['element'] == 'wind') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'water') {
				$player_damage *= 0.8;
			}
		}
		else if($player_jutsu['element'] == 'wind') {
			if($opponent_jutsu['element'] == 'lightning') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'fire') {
				$player_damage *= 0.8;
			}
		}
		else if($player_jutsu['element'] == 'lightning') {
			if($opponent_jutsu['element'] == 'earth') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'wind') {
				$player_damage *= 0.8;
			}
		}
		else if($player_jutsu['element'] == 'earth') {
			if($opponent_jutsu['element'] == 'water') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'lightning') {
				$player_damage *= 0.8;
			}
		}
		else if($player_jutsu['element'] == 'water') {
			if($opponent_jutsu['element'] == 'fire') {
				$opponent_damage *= 0.8;
			}
			else if($opponent_jutsu['element'] == 'earth') {
				$player_damage *= 0.8;
			}
		}
		
	}	
	
	if($player_jutsu['jutsu_type'] == 'genjutsu' or $opponent_jutsu['jutsu_type'] == 'genjutsu') {
		return false;
	}
	
	$player_min_damage = $player_damage * 0.5;
	if($player_min_damage < 1) {
		$player_min_damage = 1;
	}
	
	$opponent_min_damage = $opponent_damage * 0.5;
	if($opponent_min_damage < 1) {
		$opponent_min_damage = 1;
	}
	
	// Apply buffs/nerfs
	$player_speed = 50 + ($player['speed'] * 0.5);
	if($player_speed <= 0) {
		$player_speed = 1;
	}
	$player_cast_speed = 50 + ($player['cast_speed'] * 0.6);
	if($player_cast_speed <= 0) {
		$player_cast_speed = 1;
	}
	
	$opponent_speed = 50 + ($opponent['speed'] * 0.5);
	if($opponent_speed <= 0) {
		$opponent_speed = 1;
	}
	$opponent_cast_speed = 50 + ($opponent['cast_speed'] * 0.6);
	if($opponent_cast_speed <= 0) {
		$opponent_cast_speed = 1;
	}	
	
	
	// Ratios for damage reduction
	$speed_ratio = 0.8;
	$cast_speed_ratio = 0.8;
	$max_damage_reduction = 0.5;
	
	if($player_jutsu['jutsu_type'] == 'ninjutsu') {
		// Nin vs Nin
		if($opponent_jutsu['jutsu_type'] == 'ninjutsu') {			
			$player_damage -= $player_min_damage;
			$opponent_damage -= $opponent_min_damage;
			
			$collision_result = $player_damage - $opponent_damage;
			if($collision_result >= 0) {
				$damage_reduction = round(1 - ($player_min_damage + $collision_result) / ($player_damage + $player_min_damage), 2);
				
				$player_damage = $player_min_damage + $collision_result;
				$opponent_damage = $opponent_min_damage;
				
				$collision_text .= "[player]'s jutsu clashed with [opponent]'s jutsu";
				if($damage_reduction >= 1) {
					$collision_text .= ", losing " . ($damage_reduction * 100) . "% damage before breaking through!";
				}
				else {
					$collision_text .= " and broke through!";
				}
			}
			else {
				$damage_reduction = round(1 - ($opponent_min_damage + abs($collision_result)) / ($opponent_damage + $opponent_min_damage), 2);
				
				$opponent_damage = $opponent_min_damage + abs($collision_result);
				$player_damage = $player_min_damage;
				
				$collision_text .= "[opponent]'s jutsu clashed with [player]'s jutsu";
				if($damage_reduction >= 1) {
					$collision_text .= ", losing " . ($damage_reduction * 100) . "% damage before breaking through!";
				}
				else {
					$collision_text .= " and broke through!";
				}
			}				
		}
		// Nin vs Tai
		else if($opponent_jutsu['jutsu_type'] == 'taijutsu') {
			if($player_cast_speed >= $opponent_speed) {
				$damage_reduction = ($player_cast_speed / $opponent_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				
				if($damage_reduction >= 0.01) {
					$opponent_damage *= 1 - $damage_reduction;
					$collision_text .= "[player] cast [gender2] jutsu before [opponent] attacked, negating " . ($damage_reduction * 100) . 
					"% of [opponent]'s damage!";	
				}
			}
			else {
				$damage_reduction = ($opponent_speed / $player_cast_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				
				if($damage_reduction >= 0.01) {
					$player_damage *= 1 - $damage_reduction;
					$collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";
				}
			}
			
		}
	}
	// Taijutsu clash
	else if($player_jutsu['jutsu_type'] == 'taijutsu') {
		// Tai vs Tai
		if($opponent_jutsu['jutsu_type'] == 'taijutsu') {
			if($player_speed >= $opponent_speed) {
				$damage_reduction = ($player_speed / $opponent_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				
				if($damage_reduction >= 0.01) {
					$opponent_damage *= 1 - $damage_reduction;
					$collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";	
				}
			}
			else {
				$damage_reduction = ($opponent_speed / $player_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $speed_ratio, 2);	
				
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				
				if($damage_reduction >= 0.01) {
					$player_damage *= 1 - $damage_reduction;
					$collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";	
				}
			}
		}
		else if($opponent_jutsu['jutsu_type'] == 'ninjutsu') {
			if($player_speed >= $opponent_cast_speed) {
				$damage_reduction = ($player_speed / $opponent_cast_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				
				if($damage_reduction >= 0.01) {
					$opponent_damage *= 1 - $damage_reduction;
					$collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";	
				}
			}
			else {
				$damage_reduction = ($opponent_cast_speed / $player_speed) - 1.0;
				$damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
				if($damage_reduction > $max_damage_reduction) {
					$damage_reduction = $max_damage_reduction;
				}
				
				if($damage_reduction >= 0.01) {
					$player_damage *= 1 - $damage_reduction;
					$collision_text .= "[opponent] cast their jutsu before [player] attacked, negating " . ($damage_reduction * 100) . 
						"% of [player]'s damage!";		
				}
			}
		}
	}

	// Parse text
	$player_name = 'Player 1';
	$opponent_name = 'Player 2';
		
	$collision_text = str_replace(
		array('[player]', '[opponent]'),
		array($player_name, $opponent_name),
		$collision_text);
	
	return $collision_text;
}

function calcDamage($player1, $player2) {
	switch($player1['jutsu_type']) {
		case 'ninjutsu':
		case 'taijutsu':
		case 'genjutsu':
			break;
		default:
			$player1['jutsu_type'] = 'ninjutsu';
			break;
	}
	switch($player2['jutsu_type']) {
		case 'ninjutsu':
		case 'taijutsu':
		case 'genjutsu':
			break;
		default:
			$player2['jutsu_type'] = 'ninjutsu';
			break;
	}
	
	
	$player1_jutsu = array(
		'jutsu_type' => $player1['jutsu_type'],
		'power' => $player1['jutsu_power']
	);
	$player2_jutsu = array(
		'jutsu_type' => $player2['jutsu_type'],
		'power' => $player2['jutsu_power']
	);
	
	$player1_offense = 35 + ($player1[$player1_jutsu['jutsu_type'] . '_skill'] * 0.10);
	$player2_offense = 35 + ($player2[$player2_jutsu['jutsu_type'] . '_skill'] * 0.10);
	
	$min = 25;
	$max = 45;
	
	$rand = (int)(($min + $max) / 2);
	
	$player1_raw_damage = round($player1_offense * $player1_jutsu['power'] * $rand, 2);
	$player2_raw_damage = round($player2_offense * $player2_jutsu['power'] * $rand, 2);
	
	$player1_damage = $player1_raw_damage;
	$player2_damage = $player2_raw_damage;
	
	// Collision
	$collision_text = '';
	$collision_text = jutsuCollision($player1, $player2, $player1_damage, $player2_damage, $player1_jutsu, $player2_jutsu);

	$player1_collision_damage = $player1_damage;
	$player2_collision_damage = $player2_damage;
	
	$player1_defense = 50;
	$player2_defense = 50;
	
	
	$player1_defense += System::diminishing_returns($player1[$player2_jutsu['jutsu_type'] . '_skill'] * 0.01, 50);
	$player2_defense += System::diminishing_returns($player2[$player1_jutsu['jutsu_type'] . '_skill'] * 0.01, 50);
	
	// Offense
	$player1_damage = round($player1_damage / $player2_defense, 1);
	$player2_damage = round($player2_damage / $player1_defense, 1);
	
	
	// Display
	$damages = array(
		'player1' => array(
			'raw_damage' => $player1_raw_damage,
			'collision_damage' => $player1_collision_damage,
			'damage' => $player1_damage
		),
		'player2' => array(
			'raw_damage' => $player2_raw_damage,
			'collision_damage' => $player2_collision_damage,
			'damage' => $player2_damage
		),
		'collision_text' => $collision_text
	);
	return $damages;
}