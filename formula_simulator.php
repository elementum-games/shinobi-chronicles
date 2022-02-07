<?php

/**
 * @var System $system
 */
require "authenticate_admin.php";

class TestFighter extends Fighter {
    public string $name = 'test';
    public int $rank = 1;
    public int $regen_rate = 20;

    public $id;
    public string $gender = 'Non-binary';
    public int $total_stats;

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

    public function setTotalStats() {
        $this->total_stats = $this->ninjutsu_skill + $this->genjutsu_skill + $this->taijutsu_skill + $this->bloodline_skill +
            $this->cast_speed + $this->speed + $this->intelligence + $this->willpower;
    }

    public function updateInventory() {

    }

    public function updateData() {

    }


}

function fighterFromData(array $fighter_data, string $name): TestFighter {
    global $system;
    global $rankManager;

    $fighter = new TestFighter();
    $fighter->rank = 3;
    $fighter->health = 1000000;
    $fighter->max_health = 1000000;
    $fighter->name = $name;
    $fighter->system = $system;
    $fighter->ninjutsu_skill = (int)$fighter_data['ninjutsu_skill'];
    $fighter->taijutsu_skill = (int)$fighter_data['taijutsu_skill'];
    $fighter->genjutsu_skill = (int)$fighter_data['genjutsu_skill'];
    $fighter->bloodline_skill = (int)$fighter_data['bloodline_skill'];
    $fighter->speed = (int)$fighter_data['speed'];
    $fighter->cast_speed = (int)$fighter_data['cast_speed'];
    $fighter->intelligence = 10;
    $fighter->willpower = 10;
    $fighter->setTotalStats();

    $fighter_bloodline_boosts = [];
    for($i = 1; $i <= 3; $i++) {
        if($fighter_data["bloodline_boost_{$i}"] != 'none') {
            $fighter_bloodline_boosts[] = [
                'effect' => $fighter_data["bloodline_boost_{$i}"],
                'power' => $fighter_data["bloodline_boost_{$i}_power"]
            ];
        }
    }

    if(count($fighter_bloodline_boosts) > 0) {
        $fighter->bloodline_id = 1;
        $fighter->bloodline = new Bloodline([
            'bloodline_id' => 1,
            'name' => 'P1 Bloodline',
            'clan_id' => 1,
            'rank' => $fighter->rank,
            'jutsu' => json_encode([]),
            'passive_boosts' => json_encode([]),
            'combat_boosts' => json_encode($fighter_bloodline_boosts),
        ]);

        $rank = $rankManager->ranks[$fighter->rank];
        $fighter->bloodline->setBoostAmounts(
            $fighter->rank,
            $fighter->ninjutsu_skill, $fighter->taijutsu_skill, $fighter->genjutsu_skill, $fighter->bloodline_skill,
            $rank->base_stats, $fighter->total_stats, $rankManager->statsForRankAndLevel($rank->id, $rank->max_level),
            $fighter->regen_rate
        );
        $fighter->applyBloodlineBoosts();
    }

    return $fighter;
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
    $battle_id = Battle::start(
        system: $system,
        player1: $player1,
        player2: $player2,
        battle_type: Battle::TYPE_SPAR
    );
    $battle = new BattleManager($system, $user, $battle_id, true, false);

    $collision_text = $battle->jutsuCollision(
        player1: $player1,
        player2: $player2,
        player1_damage: $player1_raw_damage,
        player2_damage: $player2_raw_damage,
        player1_jutsu: $player1_jutsu,
        player2_jutsu: $player2_jutsu
    );

    $system->query("DELETE FROM battles WHERE `battle_id`={$battle_id}");

    $player1_collision_damage = $player1_raw_damage;
    $player2_collision_damage = $player2_raw_damage;

    $player1_damage = $player2->calcDamageTaken($player1_collision_damage, $player1_jutsu->jutsu_type);
    $player2_damage = $player1->calcDamageTaken($player2_collision_damage, $player2_jutsu->jutsu_type);

    // Display
    return [
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
}

/** @var string[] $bloodline_combat_boosts */
require_once __DIR__ . '/admin/entity_constraints.php';

$stats = [
    'ninjutsu_skill',
    'taijutsu_skill',
    'genjutsu_skill',
    'bloodline_skill',
    'speed',
    'cast_speed',
];
$scenario_stats = ['offense', 'speed', 'intelligence', 'willpower'];

$jutsu_power = 5;
$jutsu_type = 'ninjutsu';

$player1_jutsu_type = $jutsu_type;
$player2_jutsu_type = $jutsu_type;

$rankManager = new RankManager($system);
$rankManager->loadRanks();

$mode = 'scenarios';
if(($_POST['mode'] ?? '') == 'scenarios' || ($_GET['mode'] ?? '') == 'scenarios') {
    $mode = 'scenarios';
}
if(($_POST['mode'] ?? '') == 'vs' || ($_GET['mode'] ?? '') == 'vs') {
    $mode = 'vs';
}
if(($_POST['mode'] ?? '') == 'speed_graph' || ($_GET['mode'] ?? '') == 'speed_graph') {
    $mode = 'speed_graph';
}

if(isset($_POST['run_simulation']) && $mode == 'vs') {
    $player1_data = $_POST['stats1'];
    $player2_data = $_POST['stats2'];

    $valid_jutsu_types = [
      Jutsu::TYPE_NINJUTSU,
      Jutsu::TYPE_TAIJUTSU,
      Jutsu::TYPE_GENJUTSU,
    ];
    try {
        if(!in_array($player1_data['jutsu_type'], $valid_jutsu_types)) {
            throw new Exception("Invalid jutsu type for player 1!");
        }
        if(!in_array($player2_data['jutsu_type'], $valid_jutsu_types)) {
            throw new Exception("Invalid jutsu type for player 2!");
        }

        $player1 = fighterFromData($player1_data, "Player 1");
        $player1_jutsu = new Jutsu(
            1,
            'p1j',
            $player1->rank,
            $player1_data['jutsu_type'],
            (int)$player1_data['jutsu_power'],
            'none',
            0,
            0,
            'no',
            'nope',
            0,
            Jutsu::USE_TYPE_PROJECTILE,
            0,
            0,
            Jutsu::PURCHASE_TYPE_PURCHASEABLE,
            0,
            Jutsu::ELEMENT_NONE,
            1
        );
        $player1_jutsu->setLevel(50, 0);

        $player2 = fighterFromData($player2_data, "Player 2");
        $player2_jutsu = new Jutsu(
            1,
            'p1j',
            $player2->rank,
            $player2_data['jutsu_type'],
            (int)$player2_data['jutsu_power'],
            'none',
            0,
            0,
            'no',
            'nope',
            0,
            Jutsu::USE_TYPE_PROJECTILE,
            0,
            0,
            Jutsu::PURCHASE_TYPE_PURCHASEABLE,
            0,
            Jutsu::ELEMENT_NONE,
            1
        );
        $player2_jutsu->setLevel(50, 0);


        $damages = calcDamage(
            player1: $player1,
            player2: $player2,
            player1_jutsu: $player1_jutsu,
            player2_jutsu: $player2_jutsu
        );

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
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
else if($mode == 'speed_graph') {
    $jutsu_power = 4;
    $total_stats = 220000;

    try {
        // Nominal is 33.4% / 33.4% / 33.4%
        $scenarios = [
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => 10,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.1,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.2,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.25,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.3,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.4,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.5,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.6,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.7,
                'damages' => null,
            ],
            [
                'player2_offense' => null,
                'player2_bloodline_skill' => null,
                'player2_speed' => $total_stats * 0.8,
                'damages' => null,
            ],
        ];

        foreach($scenarios as $key => &$scenario) {
            $player1 = fighterFromData([
                'ninjutsu_skill' => 10,
                'taijutsu_skill' => floor($total_stats * 0.33334),
                'genjutsu_skill' => 10,
                'bloodline_skill' => floor($total_stats * 0.33334),
                'speed' => floor($total_stats * 0.33334),
                'cast_speed' => 10,
                'bloodline_boost_1' => 'taijutsu_boost',
                'bloodline_boost_1_power' => 30,
                'bloodline_boost_2' => 'taijutsu_resist',
                'bloodline_boost_2_power' => 10,
            ], "Player 1");
            $player1_jutsu = new Jutsu(
                id: 1,
                name: 'p1j',
                rank: $player1->rank,
                jutsu_type: Jutsu::TYPE_TAIJUTSU,
                base_power: $jutsu_power,
                effect: 'none',
                base_effect_amount: 0,
                effect_length: 0,
                description: 'no',
                battle_text: 'nope',
                cooldown: 0,
                use_type: Jutsu::USE_TYPE_PROJECTILE,
                use_cost: 0,
                purchase_cost: 0,
                purchase_type: Jutsu::PURCHASE_TYPE_PURCHASEABLE,
                parent_jutsu: 0,
                element: Jutsu::ELEMENT_NONE,
                hand_seals: 1
            );
            $player1_jutsu->setLevel(50, 0);

            // This is the one we'll change
            $remaining_stats = $total_stats - $scenario['player2_speed'];
            $scenario['player2_offense'] = floor($remaining_stats / 2);
            $scenario['player2_bloodline_skill'] = floor($remaining_stats / 2);

            $player2 = fighterFromData([
                'ninjutsu_skill' => 10,
                'taijutsu_skill' => $scenario['player2_offense'],
                'genjutsu_skill' => 10,
                'bloodline_skill' => $scenario['player2_bloodline_skill'],
                'speed' => ceil($scenario['player2_speed']),
                'cast_speed' => 10,
                'bloodline_boost_1' => 'taijutsu_boost',
                'bloodline_boost_1_power' => 30,
                'bloodline_boost_2' => 'taijutsu_resist',
                'bloodline_boost_2_power' => 10,
            ], "Player 2");
            $player2_jutsu = new Jutsu(
                id: 1,
                name: 'p2j',
                rank: $player2->rank,
                jutsu_type: Jutsu::TYPE_TAIJUTSU,
                base_power: $jutsu_power,
                effect: 'none',
                base_effect_amount: 0,
                effect_length: 0,
                description: 'no',
                battle_text: 'nope',
                cooldown: 0,
                use_type: Jutsu::USE_TYPE_PROJECTILE,
                use_cost: 0,
                purchase_cost: 0,
                purchase_type: Jutsu::PURCHASE_TYPE_PURCHASEABLE,
                parent_jutsu: 0,
                element: Jutsu::ELEMENT_NONE,
                hand_seals: 1
            );
            $player2_jutsu->setLevel(50, 0);

            $scenarios[$key]['damages'] = calcDamage(
                player1: $player1,
                player2: $player2,
                player1_jutsu: $player1_jutsu,
                player2_jutsu: $player2_jutsu
            );
        }
        unset($scenario);

        $label_width = 100;

        echo "
        <style>
            .speedGraphContainer {
                width:500px;
                background-color:#EAEAEA;
                text-align:center;
                margin-left:auto;
                margin-right:auto;
                padding:8px;
                border:1px solid #000000;
                border-radius:10px;
            }
            
            .scenario {
                margin: 5px auto 10px auto;
            }
            
            label {
                display: inline-block;
                text-align: left;
            }
            .playerLabel {
                width: 100px;
            }
            .statsLabel {
                width: 260px;
            }
            
        </style>
        <div class='speedGraphContainer'>
        Evasion DR Ratio: " . BattleManager::SPEED_DAMAGE_REDUCTION_RATIO . "<br />
        Max Evasion DR: " . BattleManager::MAX_EVASION_DAMAGE_REDUCTION . "<br />
        Speed Off Ratio: " . Fighter::SPEED_OFFENSE_RATIO . "<br />";

        echo "<table>
            <tr>
                <th>Player 2 speed ratio</th>
                <th>Player 2 damage ratio</th>
            </tr>";
        foreach($scenarios as $scenario) {
            $player2_speed_ratio = round($scenario['player2_speed'] / $player1->speed, 2);
            $player2_damage_ratio = round($scenario['damages']['player2']['damage'] / $scenario['damages']['player1']['damage'], 2);

/*            echo "<div class='scenario'>"
                . "<label class='playerLabel'>Player 1:</label>"
                . "<label class='statsLabel'>{$player1->taijutsu_skill} off / {$player1->bloodline_skill} BL / {$player1->speed} speed</label>"
                . "<br />"
                . "<label class='playerLabel'>Player 2:</label>"
                . "<label class='statsLabel'>{$scenario['player2_offense']} off / {$scenario['player2_bloodline_skill']} BL / {$scenario['player2_speed']} speed</label>"
                . "<br />"
                . "<b>Speed ratio: {$player2_speed_ratio}x</b><br />"
                . "<b>Damage: {$player2_damage_ratio}x</b><br />"
                // . "({$scenario['damages']['player2']['damage']} vs {$scenario['damages']['player1']['damage']})</b>"
            . "</div>";*/

            echo "<tr>
                <td>{$player2_speed_ratio}</td>
                <td>{$player2_damage_ratio}</td>
            </tr>";
        }
        echo "</table>";

        echo "</div>";
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
else if(isset($_POST['run_simulation']) && $mode == 'scenarios') {
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
?>
<style>
    label {
        display: inline-block;
    }

    .versusFighterInput {
        width:300px;
        display:inline-block;
        border:1px solid #000000;
        border-radius:10px;
    }

    .scenario_input {
        width:500px;
        display:inline-block;
        border:1px solid #000000;
        background: rgba(0,0,0,0.4);
        border-radius:10px;
        padding:5px;
    }
</style>

<br />
<div style='text-align:center;'>
<script type='text/javascript' src='./scripts/jquery-2.1.0.min.js'></script>
<script type='text/javascript'>
    function changeDisplay(display_id) {
        $('.displayDiv').hide();
        $('#' + display_id).show();
    }
</script>

<a href='formula_simulator.php?mode=vs'>VS</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a href='formula_simulator.php?mode=scenarios'>Damage/Level Curve</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a href='formula_simulator.php?mode=speed_graph'>Speed Graph</a>
<br />
<br />

<!--VS DISPLAY-->
<div id='vs' class='displayDiv' <?= ($mode == 'vs' ? '' : "style='display:none;'") ?>>
	<form action='./formula_simulator.php' method='post'>
	<div class='versusFighterInput'>
		Player 1<br />
        <?php foreach($stats as $stat): ?>
            <label style='width:110px;'><?= $stat ?>:</label>
            <input type='text' name='stats1[<?= $stat ?>]' value='<?= $_POST['stats1'][$stat] ?? 10 ?>' /><br />
		<?php endforeach; ?>
		<label style='width:110px;'>Jutsu power:</label>
			<input type='text' name='stats1[jutsu_power]' value='<?= $_POST['stats1']['jutsu_power'] ?? 1 ?>' /><br />
        <label>
            <input type='radio' name='stats1[jutsu_type]' value='ninjutsu'
                <?= ($_POST['stats1']['jutsu_type'] == 'ninjutsu' ? "checked='checked'" : '') ?>
            />
            Ninjutsu
        </label><br />
        <label>
            <input type='radio' name='stats1[jutsu_type]' value='taijutsu'
                <?= ($_POST['stats1']['jutsu_type'] == 'taijutsu' ? "checked='checked'" : '') ?>
            />
            Taijutsu
        </label><br />
		<label>
            <input type='radio' name='stats1[jutsu_type]' value='genjutsu'
                <?= ($_POST['stats1']['jutsu_type'] == 'genjutsu' ? "checked='checked'" : '') ?>
            />
            Genjutsu
        </label><br />
		<br />
		Bloodline boost 1<br />
		<select name='stats1[bloodline_boost_1]'>
            <option value='none'>None</option>
            <?php foreach($bloodline_combat_boosts as $boost): ?>
                <option value='<?= $boost ?>'
                    <?= ($_POST['stats1']['bloodline_boost_1'] == $boost ? "selected='selected'" : '') ?>
                >
                    <?= $boost ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input
            type='number'
            name='stats1[bloodline_boost_1_power]'
            style='width:60px'
            value='<?= $_POST['stats1']['bloodline_boost_1_power'] ?? 0 ?>'
        />
        <br />

        Bloodline boost 2<br />
        <select name='stats1[bloodline_boost_2]'>
            <option value='none'>None</option>
            <?php foreach($bloodline_combat_boosts as $boost): ?>
                <option value='<?= $boost ?>'
                    <?= ($_POST['stats1']['bloodline_boost_2'] == $boost ? "selected='selected'" : '') ?>
                >
                    <?= $boost ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input
            type='number'
            name='stats1[bloodline_boost_2_power]'
            style='width:60px'
            value='<?= $_POST['stats1']['bloodline_boost_2_power'] ?? 0 ?>'
        />
        <br />

        Bloodline boost 3<br />
        <select name='stats1[bloodline_boost_3]'>
            <option value='none'>None</option>
            <?php foreach($bloodline_combat_boosts as $boost): ?>
                <option value='<?= $boost ?>'
                    <?= ($_POST['stats1']['bloodline_boost_3'] == $boost ? "selected='selected'" : '') ?>
                >
                    <?= $boost ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input
            type='number'
            name='stats1[bloodline_boost_3_power]'
            style='width:60px'
            value='<?= $_POST['stats1']['bloodline_boost_3_power'] ?? 0 ?>'
        />
        <br />
	</div>
    <div class='versusFighterInput' style='margin-left: 20px;'>
        Player 2<br />
        <?php foreach($stats as $stat): ?>
            <label style='width:110px;'><?= $stat ?>:</label>
            <input type='text' name='stats2[<?= $stat ?>]' value='<?= $_POST['stats2'][$stat] ?? 10 ?>' /><br />
        <?php endforeach; ?>
        <label style='width:110px;'>Jutsu power:</label>
        <input type='text' name='stats2[jutsu_power]' value='<?= $_POST['stats2']['jutsu_power'] ?? 1 ?>' /><br />
        <label>
            <input type='radio' name='stats2[jutsu_type]' value='ninjutsu'
                <?= ($_POST['stats2']['jutsu_type'] == 'ninjutsu' ? "checked='checked'" : '') ?>
            />
            Ninjutsu
        </label><br />
        <label>
            <input type='radio' name='stats2[jutsu_type]' value='taijutsu'
                <?= ($_POST['stats2']['jutsu_type'] == 'taijutsu' ? "checked='checked'" : '') ?>
            />
            Taijutsu
        </label><br />
        <label>
            <input type='radio' name='stats2[jutsu_type]' value='genjutsu'
                <?= ($_POST['stats2']['jutsu_type'] == 'genjutsu' ? "checked='checked'" : '') ?>
            />
            Genjutsu
        </label><br />
        <br />
        Bloodline boost 1<br />
        <select name='stats2[bloodline_boost_1]'>
            <option value='none'>None</option>
            <?php foreach($bloodline_combat_boosts as $boost): ?>
                <option value='<?= $boost ?>'
                    <?= ($_POST['stats2']['bloodline_boost_1'] == $boost ? "selected='selected'" : '') ?>
                >
                    <?= $boost ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input
            type='number'
            name='stats2[bloodline_boost_1_power]'
            style='width:60px'
            value='<?= $_POST['stats2']['bloodline_boost_1_power'] ?? 0 ?>'
        />
        <br />

        Bloodline boost 2<br />
        <select name='stats2[bloodline_boost_2]'>
            <option value='none'>None</option>
            <?php foreach($bloodline_combat_boosts as $boost): ?>
                <option value='<?= $boost ?>'
                    <?= ($_POST['stats2']['bloodline_boost_2'] == $boost ? "selected='selected'" : '') ?>
                >
                    <?= $boost ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input
            type='number'
            name='stats2[bloodline_boost_2_power]'
            style='width:60px'
            value='<?= $_POST['stats2']['bloodline_boost_2_power'] ?? 0 ?>'
        />
        <br />

        Bloodline boost 3<br />
        <select name='stats2[bloodline_boost_3]'>
            <option value='none'>None</option>
            <?php foreach($bloodline_combat_boosts as $boost): ?>
                <option value='<?= $boost ?>'
                    <?= ($_POST['stats2']['bloodline_boost_3'] == $boost ? "selected='selected'" : '') ?>
                >
                    <?= $boost ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input
            type='number'
            name='stats2[bloodline_boost_3_power]'
            style='width:60px'
            value='<?= $_POST['stats2']['bloodline_boost_3_power'] ?? 0 ?>'
        />
        <br />
    </div>
	<br />
	<input type='radio' name='mode' value='vs' onclick='changeDisplay("vs")' checked='checked' /> Versus<br />
	<input type='radio' name='mode' value='scenarios' onclick='changeDisplay("scenario")' /> Scenarios<br />
	<input type='submit' name='run_simulation' value='Run Simulation' />
	</form>
</div>

<!--SCENARIO DISPLAY-->
<script type='text/javascript'>
    let ranks = <?= json_encode($ranks_prefill_data) ?>;
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
<div id='scenario' class='displayDiv' <?= ($mode == 'scenarios' ? '' : "style='display:none;'") ?>>
    <div id='rank_select'>
        <?php foreach($ranks_prefill_data as $id => $rank): ?>
            <button onClick='prefillRank(<?= $rank['id'] ?>)'><?= $rank['id'] ?>: <?= $rank['name'] ?></button>
        <?php endforeach; ?>
    </div>
	<form action='./formula_simulator.php' method='post'>
        <div class='scenario_input'>
            Sim details<br />
            Base level: <input type='text' id='base_level' name='base_level' value='<?= $_POST['base_level'] ?? "" ?>' /><br />
            Max level: <input type='text' id='max_level' name='max_level' value='<?= $_POST['max_level'] ?? "" ?>' /><br />
            Base stats: <input type='text' id='base_stats' name='base_stats' value='<?= $_POST['base_stats'] ?? "" ?>' /><br />
            Attribute ratio: 
                <select name='attribute_ratio'>
                <?php for($i = 10; $i < 60; $i += 10): ?>
                    <option value='<?= $i ?>'
                        <?= (($_POST['attribute_ratio'] ?? 40) == $i ? "selected='selected'" : "") ?>
                    >
                        <?= $i ?>%
                    </option>
                <?php endfor; ?>
                </select><br />
            Base health: <input type='text' id='base_health' name='base_health' value='<?= $_POST['base_health'] ?? "" ?>' /><br />
            Base jutsu power: <input type='text' id='base_jutsu_power' name='base_jutsu_power' value='<?= $_POST['base_jutsu_power'] ?? "" ?>' /><br />
            <br />
            Max level health: <input type='text' id='max_level_health' name='max_level_health' value='<?= $_POST['max_level_health'] ?? "" ?>' /><br />
            Max level stats: <input type='text' id='max_level_stats' name='max_level_stats' value='<?= $_POST['max_level_stats'] ?? "" ?>' /><br />
            Max level jutsu power: <input type='text' id='max_level_jutsu_power' name='max_level_jutsu_power' value='<?= $_POST['max_level_jutsu_power'] ?? "" ?>' /><br />
            Max level jutsu level: <input type='text' id='max_level_jutsu_level' name='max_level_jutsu_level' 
                value='<?= ($_POST['max_level_jutsu_level'] ?? 50) ?>' /><br />
        </div>
        <br />
        <br />
        <input type='radio' name='mode' value='vs' onclick='changeDisplay("vs")' /> Versus<br />
        <input type='radio' name='mode' value='scenarios' onclick='changeDisplay("scenario")' checked='checked' /> Damage/Level Curve<br />
        <input type='submit' name='run_simulation' value='Run Simulation' />
	</form>
</div>
</div>



