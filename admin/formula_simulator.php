<?php

/**
 * @var System $system
 */
require "admin/_authenticate_admin.php";

class TestFighter extends Fighter {
    public string $name = 'test';
    public int $rank = 1;

    public $id;

    public function getAvatarSize(): int {
        return 125;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getInventory() {
    }

    public function hasItem(int $item_id): bool {
        return true;
    }

    public function hasEquippedJutsu(int $jutsu_id): bool {
        return true;
    }

    public function useJutsu(Jutsu $jutsu) {

    }

    public function updateInventory() {

    }

    public function updateData() {

    }

    public function hasJutsu(int $jutsu_id): bool {
        // TODO: Implement hasJutsu() method.
        return true;
    }
}

/**
 * @param Fighter $player1
 * @param Fighter $player2
 * @param Jutsu   $player1_jutsu
 * @param Jutsu   $player2_jutsu
 * @return array
 * @throws Exception
 */
function calcDamage(Fighter $player1, Fighter $player2, Jutsu $player1_jutsu, Jutsu $player2_jutsu): array {
    global $system;
    global $user;

    $player1_raw_damage = $player1->calcDamage($player1_jutsu, true);
    $player2_raw_damage = $player2->calcDamage($player2_jutsu, true);

    // Collision
    $battle_id = Battle::start($system, $player1, $player2, Battle::TYPE_SPAR);
    $battle = new BattleManager($system, $user, $battle_id, true, false);
    $collision_text = $battle->jutsuCollision(
        $player1, $player2,
        $player1_raw_damage, $player2_raw_damage,
        $player1_jutsu, $player2_jutsu
    );

    $system->query("DELETE FROM battles WHERE `battle_id`={$battle_id}");

    $player1_collision_damage = $player1_raw_damage;
    $player2_collision_damage = $player2_raw_damage;

    $player1_damage = $player2->calcDamageTaken($player1_collision_damage, $player1_jutsu->jutsu_type);
    $player2_damage = $player1->calcDamageTaken($player2_collision_damage, $player2_jutsu->jutsu_type);

    // Display
    $damages = [
        'player1' => [
            'raw_damage' => $player1_raw_damage,
            'collision_damage' => $player1_collision_damage,
            'damage' => $player1_damage,
        ],
        'player2' => [
            'raw_damage' => $player2_raw_damage,
            'collision_damage' => $player2_collision_damage,
            'damage' => $player2_damage,
        ],
        'collision_text' => $collision_text,
    ];
    return $damages;
}

$stats = [
    'ninjutsu_skill',
    'taijutsu_skill',
    'genjutsu_skill',
    'speed',
    'cast_speed',
    'intelligence',
    'willpower'
];
$scenario_stats = ['offense', 'speed', 'intelligence', 'willpower'];

$jutsu_power = 5;
$jutsu_type = 'ninjutsu';

$player1_jutsu_type = $jutsu_type;
$player2_jutsu_type = $jutsu_type;

$mode = 'scenarios';
if(($_POST['mode'] ?? '') == 'scenarios' || ($_GET['mode'] ?? '') == 'scenarios') {
    $mode = 'scenarios';
}
if(($_POST['mode'] ?? '') == 'vs' || ($_GET['mode'] ?? '') == 'vs') {
    $mode = 'vs';
}

/* if($_POST['run_simulation'] && $mode == 'vs') {
   $player1_data = $_POST['stats1'];
    $player2_data = $_POST['stats2'];

    $player2_jutsu->power = $player1_jutsu->power;

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
else */
if($_POST['run_simulation'] && $mode == 'scenarios') {
    $base_level = $_POST['base_level'];
    $max_level = $_POST['max_level'];
    
    $base_stats = $_POST['base_stats'];
    $original_attribute_ratio = round($_POST['attribute_ratio'] / 100, 2);
    $base_health = $_POST['base_health'];
    $base_jutsu_power = $_POST['base_jutsu_power'];
    
    $max_level_health = $_POST['max_level_health'];
    $max_level_stats = $_POST['max_level_stats'];
    $max_level_jutsu_power = $_POST['max_level_jutsu_power'];
    $max_level_jutsu_level = $_POST['max_level_jutsu_level'];

    $num_levels = $max_level - $base_level;

    $health_gain = ($max_level_health - $base_health) / $num_levels;
    $stat_gain = ($max_level_stats - $base_stats) / $num_levels;
    $jutsu_power_gain = round(($max_level_jutsu_power - $base_jutsu_power) / $num_levels, 3);

    $base_jutsu_level = 20;
    $jutsu_level_gain = ($max_level_jutsu_level - $base_jutsu_level) / $num_levels;

    $player1 = new TestFighter();
    $player1->name = "Player 1";
    $player1->system = $system;
    $player1->ninjutsu_skill = 10;
    $player1->taijutsu_skill = 10;
    $player1->genjutsu_skill = 10;
    $player1->speed = 10;
    $player1->cast_speed = 10;
    $player1->intelligence = 10;
    $player1->willpower = 10;
    $player1_jutsu = new Jutsu(
        id: 1,
        name: 'p1j',
        rank: $player1->rank,
        jutsu_type: Jutsu::TYPE_NINJUTSU,
        base_power: $base_jutsu_power,
        effect: 'none',
        base_effect_amount: 0,
        effect_length: 0,
        description: 'no',
        battle_text: 'nope',
        cooldown: 0,
        use_type: Jutsu::USE_TYPE_PROJECTILE,
        target_type: Jutsu::TARGET_TYPE_DIRECTION,
        use_cost: 0,
        purchase_cost: 0,
        purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
        parent_jutsu: 0,
        element: Jutsu::ELEMENT_NONE,
        hand_seals: 1
    );
    $player1_jutsu->setLevel($base_jutsu_level, 0);

    $player2 = new TestFighter();
    $player2->system = $system;
    $player2->name = "Player 2";
    $player2->ninjutsu_skill = 10;
    $player2->taijutsu_skill = 10;
    $player2->genjutsu_skill = 10;
    $player2->speed = 10;
    $player2->cast_speed = 10;
    $player2->intelligence = 10;
    $player2->willpower = 10;
    $player2_jutsu = new Jutsu(
        id: 2,
        name: 'p2j',
        rank: $player2->rank,
        jutsu_type: Jutsu::TYPE_TAIJUTSU,
        base_power: $base_jutsu_power,
        effect: 'none',
        base_effect_amount: 0,
        effect_length: 0,
        description: 'no',
        battle_text: 'nope',
        cooldown: 0,
        use_type: Jutsu::USE_TYPE_MELEE,
        target_type: Jutsu::TARGET_TYPE_FIGHTER_ID,
        use_cost: 0,
        purchase_cost: 0,
        purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
        parent_jutsu: 0,
        element: Jutsu::ELEMENT_NONE,
        hand_seals: 2
    );
    $player2_jutsu->setLevel($base_jutsu_level, 0);

    $total_ratio = 1 + $original_attribute_ratio;

    $skill_ratio = 1 / $total_ratio;
    $attribute_ratio = $original_attribute_ratio / $total_ratio;

    // Set jutsu type specific stats to base for rank
    switch($player1_jutsu->jutsu_type) {
        case Jutsu::TYPE_NINJUTSU:
            $player1->ninjutsu_skill = $base_stats * $skill_ratio;
            $player1->cast_speed = $base_stats * $attribute_ratio;
            break;
        case Jutsu::TYPE_TAIJUTSU:
            $player1->taijutsu_skill = $base_stats * $skill_ratio;
            $player1->speed = $base_stats * $attribute_ratio;
            break;
        case Jutsu::TYPE_GENJUTSU:
            $player1->genjutsu_skill = $base_stats;
            break;
    }
    switch($player2_jutsu->jutsu_type) {
        case Jutsu::TYPE_NINJUTSU:
            $player2->ninjutsu_skill = $base_stats * $skill_ratio;
            $player2->cast_speed = $base_stats * $attribute_ratio;
            break;
        case Jutsu::TYPE_TAIJUTSU:
            $player2->taijutsu_skill = $base_stats * $skill_ratio;
            $player2->speed = $base_stats * $attribute_ratio;
            break;
        case Jutsu::TYPE_GENJUTSU:
            $player2->genjutsu_skill = $base_stats;
            break;
    }

    $damages = [];
    $level = $base_level;
    $health = $base_health;

    $player1->max_health = $health;
    $player1->health = $player1->max_health;

    $player2->max_health = $health;
    $player2->health = $player2->max_health;

    // Calc damage ranges
    $damage = calcDamage($player1, $player2, $player1_jutsu, $player2_jutsu);
    $damages[$level]['player1'] = $damage['player1']['damage'];
    $damages[$level]['player2'] = $damage['player2']['damage'];
    $damages[$level]['health'] = $health;

    for($level = $base_level + 1; $level <= $max_level; $level++) {
        switch($player1_jutsu->jutsu_type) {
            case Jutsu::TYPE_NINJUTSU:
                $player1->ninjutsu_skill += round($stat_gain * $skill_ratio, 1);
                $player1->cast_speed += round($stat_gain * $attribute_ratio, 1);
                break;
            case Jutsu::TYPE_TAIJUTSU:
                $player1->taijutsu_skill += round($stat_gain * $skill_ratio, 1);
                $player1->speed += round($stat_gain * $attribute_ratio, 1);
                break;
            case Jutsu::TYPE_GENJUTSU:
                $player1->genjutsu_skill += round($stat_gain, 1);
                break;
        }
        switch($player2_jutsu->jutsu_type) {
            case Jutsu::TYPE_NINJUTSU:
                $player2->ninjutsu_skill += round($stat_gain * $skill_ratio, 1);
                $player2->cast_speed += round($stat_gain * $attribute_ratio, 1);
                break;
            case Jutsu::TYPE_TAIJUTSU:
                $player2->taijutsu_skill += round($stat_gain * $skill_ratio, 1);
                $player2->speed += round($stat_gain * $attribute_ratio, 1);
                break;
            case Jutsu::TYPE_GENJUTSU:
                $player2->genjutsu_skill += round($stat_gain, 1);
                break;
        }

        $health += $health_gain;

        $player1->max_health = $health;
        $player1->health = $player1->max_health;

        $player2->max_health = $health;
        $player2->health = $player2->max_health;

        $player1_jutsu->base_power += $jutsu_power_gain;
        $player2_jutsu->base_power += $jutsu_power_gain;

        $player1_jutsu->setLevel($player1_jutsu->level + $jutsu_level_gain, 0);
        $player2_jutsu->setLevel($player2_jutsu->level + $jutsu_level_gain, 0);

        $damage = calcDamage($player1, $player2, $player1_jutsu, $player2_jutsu);
        $damages[$level]['player1'] = $damage['player1']['damage'];
        $damages[$level]['player2'] = $damage['player2']['damage'];
        $damages[$level]['health'] = $health;

    }

    $label_width = 120;
    echo "<style>
        label {
            display: inline-block;
            text-align: left;
        }
    </style>";
    echo "<div style='width:500px;background-color:#EAEAEA;text-align:center;margin-left:auto;margin-right:auto;
		padding:8px;border:1px solid #000000;border-radius:10px;'>
		<label style='width:{$label_width}px;'>Health gain:</label>
        <label style='width:80px;'>" . round($health_gain, 2) . "</label><br />
        
        <label style='width:{$label_width}px;'>Stats gain:</label>
        <label style='width:80px;'>" . round($stat_gain, 2) . "</label><br />
        
        <label style='width:{$label_width}px;'>Jutsu power gain:</label>
        <label style='width:80px;'>" . round($jutsu_power_gain, 2) . "</label><br />
        <br />";

    $label_width = (70 + (strlen($max_level) * 10));
    foreach($damages as $level => $damage) {
        echo "<label style='width:{$label_width}px;'>Level $level:</label>" .
            "<label style='width:" . round($label_width * 2.4) . "px;'>" .
            round($damage['health'], 1) . " HP / " .
            sprintf("%.1f", $damage['player1']) . " damage" .
            "</label>" .
            "<label style='width:{$label_width}px'>(" . round($damage['health'] / $damage['player1'], 1) .
            " turns)</label><br />";
    }
    echo "<br />Final stats: <br />" .
        round($player1->ninjutsu_skill, 1) . ' nin skill, ' . round($player1->taijutsu_skill, 1) . ' tai skill<br />' .
        round($player1->cast_speed, 1) . ' cast speed, ' . round($player1->speed, 1) . ' speed<br />' .
        round($player1_jutsu->power, 1) . ' jutsu power.';

    echo "<br />
	</div>";
}


$rankManager = new RankManager($system);
$rankManager->loadRanks();
$ranks_prefill_data = array_map(function($rank) use ($rankManager) {
    return [
        'id' => $rank->id,
        'name' => $rank->name,
        'base_level' => $rank->base_level,
        'max_level' => $rank->max_level,
        'base_stats' => $rank->base_stats,
        'base_health' => $rankManager->healthForRankAndLevel($rank->id, $rank->base_level),
        'base_jutsu_power' => (float)$rank->id,
        'max_level_health' => $rankManager->healthForRankAndLevel($rank->id, $rank->max_level),
        'max_level_stats' => $rankManager->statsForRankAndLevel($rank->id, $rank->max_level),
        'max_level_jutsu_power' => $rank->id + 0.9,
    ];
}, $rankManager->ranks);

// Display form
echo "<style>
label {
	display: inline-block;
}
</style>

<br />
<div style='text-align:center;'>
<script type='text/javascript' src='../scripts/jquery-2.1.0.min.js'></script>
<script type='text/javascript'>
    function changeDisplay(display_id) {
        $('.displayDiv').hide();
        $('#' + display_id).show();
    }
</script>

<a href='formula_simulator.php?mode=vs'>VS</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a href='formula_simulator.php?mode=scenarios'>Damage/Level Curve</a>
<br />
<br />

<!--VS DISPLAY-->
<div id='vs' class='displayDiv' " . ($mode == 'scenarios' ? "style='display:none;'" : '') . ">
	<form action='formula_simulator.php' method='post'>
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
/** @noinspection JSCheckFunctionSignatures */
/** @noinspection JSUnnecessarySemicolon */
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
<style>
    .scenario_input {
        width:500px;
        display:inline-block;
        border:1px solid #000000;
        background: rgba(0,0,0,0.4);
        border-radius:10px;
        padding:5px;
    }
</style>
<script type='text/javascript'>
    let ranks = " . json_encode($ranks_prefill_data) . ";
    function prefillRank(id) {
        if(typeof ranks[id] === 'undefined') {
            return;
        }
        
        rank = ranks[id];
        
        let fields = [
            'base_level',
            'max_level',
            'base_stats',
            'base_health',
            'base_jutsu_power',
            'max_level_health',
            'max_level_stats',
            'max_level_jutsu_power',
        ]
        fields.forEach(field => {  
            document.getElementById(field).value = rank[field];
        })
    }
</script>
<div id='scenario' class='displayDiv' " . ($mode == 'vs' ? "style='display:none;'" : '') . ">
    <div id='rank_select'>";
        foreach($ranks_prefill_data as $id => $rank) {
            echo "<button onClick='prefillRank({$rank['id']})'>{$rank['id']}: {$rank['name']}</button>";
        }
    echo "</div>
	<form action='formula_simulator.php' method='post'>
        <div class='scenario_input'>
            Sim details<br />
            Base level: <input type='text' id='base_level' name='base_level' value='{$_POST['base_level']}' /><br />
            Max level: <input type='text' id='max_level' name='max_level' value='{$_POST['max_level']}' /><br />
            Base stats: <input type='text' id='base_stats' name='base_stats' value='{$_POST['base_stats']}' /><br />
            Attribute ratio: 
                <select name='attribute_ratio''>";
                    for($i = 10; $i < 60; $i += 10) {
                        echo "<option value='{$i}' " . (($_POST['attribute_ratio'] ?? 40) == $i ? "selected='selected'" : "") . ">{$i}%</option>";
                    }
                echo "</select><br />
            Base health: <input type='text' id='base_health' name='base_health' value='{$_POST['base_health']}' /><br />
            Base jutsu power: <input type='text' id='base_jutsu_power' name='base_jutsu_power' value='{$_POST['base_jutsu_power']}' /><br />
            <br />
            Max level health: <input type='text' id='max_level_health' name='max_level_health' value='{$_POST['max_level_health']}' /><br />
            Max level stats: <input type='text' id='max_level_stats' name='max_level_stats' value='{$_POST['max_level_stats']}' /><br />
            Max level jutsu power: <input type='text' id='max_level_jutsu_power' name='max_level_jutsu_power' value='{$_POST['max_level_jutsu_power']}' /><br />
            Max level jutsu level: <input type='text' id='max_level_jutsu_level' name='max_level_jutsu_level' 
                value='" . ($_POST['max_level_jutsu_level'] ?? 50) . "' /><br />
        </div>
        <br />
        <br />
        <input type='radio' name='mode' value='vs' onclick='changeDisplay(\"vs\")' /> Versus<br />
        <input type='radio' name='mode' value='scenarios' onclick='changeDisplay(\"scenario\")' checked='checked' /> Damage/Level Curve<br />
        <input type='submit' name='run_simulation' value='Run Simulation' />
	</form>
</div>
</div>";



