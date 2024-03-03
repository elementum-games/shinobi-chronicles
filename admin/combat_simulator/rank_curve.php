<?php

/** @var System $system */
require __DIR__ . "/../_authenticate_admin.php";

require __DIR__ . "/TestFighter.php";
require __DIR__ . "/calcDamage.php";

/** @var string[] $bloodline_combat_boosts */
require_once __DIR__ . '/../entity_constraints.php';

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

if(!empty($_POST['run_simulation'])) {
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

    $player1 = TestFighter::fromFormData(
        system: $system,
        rankManager: $rankManager,
        fighter_data: [
            'ninjutsu_skill' => 0,
            'taijutsu_skill' => 0,
            'genjutsu_skill' => 0,
            'bloodline_skill' => 0,
            'speed' => 0,
            'cast_speed' => 0,
        ],
        name: "Player 1"
    );
    $player1_jutsu = new Jutsu(
        id: 1,
        name: 'p1j',
        rank: $player1->rank,
        jutsu_type: JutsuOffenseType::NINJUTSU,
        base_power: $base_jutsu_power,
        range: 1,
        effect_1: 'none',
        base_effect_amount_1: 0,
        effect_length_1: 0,
        effect_2: 'none',
        base_effect_amount_2: 0,
        effect_length_2: 0,
        description: 'no',
        battle_text: 'nope',
        cooldown: 0,
        use_type: Jutsu::USE_TYPE_PROJECTILE,
        target_type: Jutsu::TARGET_TYPE_DIRECTION,
        use_cost: 0,
        purchase_cost: 0,
        purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
        parent_jutsu: 0,
        element: Element::NONE,
        hand_seals: 1
    );
    $player1_jutsu->setLevel($base_jutsu_level, 0);
    $player1->jutsu[$player1_jutsu->id] = $player1_jutsu;

    $player2 = TestFighter::fromFormData(
        system: $system,
        rankManager: $rankManager,
        fighter_data: [
            'ninjutsu_skill' => 0,
            'taijutsu_skill' => 0,
            'genjutsu_skill' => 0,
            'bloodline_skill' => 0,
            'speed' => 0,
            'cast_speed' => 0,
        ],
        name: "Player 2"
    );
    $player2_jutsu = new Jutsu(
        id: 2,
        name: 'p2j',
        rank: $player2->rank,
        jutsu_type: JutsuOffenseType::TAIJUTSU,
        base_power: $base_jutsu_power,
        range: 1,
        effect_1: 'none',
        base_effect_amount_1: 0,
        effect_length_1: 0,
        effect_2: 'none',
        base_effect_amount_2: 0,
        effect_length_2: 0,
        description: 'no',
        battle_text: 'nope',
        cooldown: 0,
        use_type: Jutsu::USE_TYPE_MELEE,
        target_type: Jutsu::TARGET_TYPE_FIGHTER_ID,
        use_cost: 0,
        purchase_cost: 0,
        purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
        parent_jutsu: 0,
        element: Element::NONE,
        hand_seals: 2
    );
    $player2_jutsu->setLevel($base_jutsu_level, 0);
    $player2->jutsu[$player2_jutsu->id] = $player2_jutsu;

    $total_ratio = 1 + $original_attribute_ratio;

    $skill_ratio = 1 / $total_ratio;
    $attribute_ratio = $original_attribute_ratio / $total_ratio;

    // Set jutsu type specific stats to base for rank
    switch($player1_jutsu->jutsu_type) {
        case JutsuOffenseType::NINJUTSU:
            $player1->ninjutsu_skill = $base_stats * $skill_ratio;
            $player1->cast_speed = $base_stats * $attribute_ratio;
            break;
        case JutsuOffenseType::TAIJUTSU:
            $player1->taijutsu_skill = $base_stats * $skill_ratio;
            $player1->speed = $base_stats * $attribute_ratio;
            break;
        case JutsuOffenseType::GENJUTSU:
            $player1->genjutsu_skill = $base_stats;
            break;
    }
    switch($player2_jutsu->jutsu_type) {
        case JutsuOffenseType::NINJUTSU:
            $player2->ninjutsu_skill = $base_stats * $skill_ratio;
            $player2->cast_speed = $base_stats * $attribute_ratio;
            break;
        case JutsuOffenseType::TAIJUTSU:
            $player2->taijutsu_skill = $base_stats * $skill_ratio;
            $player2->speed = $base_stats * $attribute_ratio;
            break;
        case JutsuOffenseType::GENJUTSU:
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
    $damage = calcDamage(
        player1: $player1,
        player2: $player2,
        player1_jutsu: $player1_jutsu,
        player2_jutsu: $player2_jutsu,
        player2_effects: [],
        player1_effects: []
    );
    $damages[$level]['player1'] = $damage['player1']['damage_dealt'];
    $damages[$level]['player2'] = $damage['player2']['damage_dealt'];
    $damages[$level]['health'] = $health;

    for($level = $base_level + 1; $level <= $max_level; $level++) {
        switch($player1_jutsu->jutsu_type) {
            case JutsuOffenseType::NINJUTSU:
                $player1->ninjutsu_skill += round($stat_gain * $skill_ratio, 1);
                $player1->cast_speed += round($stat_gain * $attribute_ratio, 1);
                break;
            case JutsuOffenseType::TAIJUTSU:
                $player1->taijutsu_skill += round($stat_gain * $skill_ratio, 1);
                $player1->speed += round($stat_gain * $attribute_ratio, 1);
                break;
            case JutsuOffenseType::GENJUTSU:
                $player1->genjutsu_skill += round($stat_gain, 1);
                break;
        }
        switch($player2_jutsu->jutsu_type) {
            case JutsuOffenseType::NINJUTSU:
                $player2->ninjutsu_skill += round($stat_gain * $skill_ratio, 1);
                $player2->cast_speed += round($stat_gain * $attribute_ratio, 1);
                break;
            case JutsuOffenseType::TAIJUTSU:
                $player2->taijutsu_skill += round($stat_gain * $skill_ratio, 1);
                $player2->speed += round($stat_gain * $attribute_ratio, 1);
                break;
            case JutsuOffenseType::GENJUTSU:
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

        $damage = calcDamage(
            player1: $player1,
            player2: $player2,
            player1_jutsu: $player1_jutsu,
            player2_jutsu: $player2_jutsu,
            player1_effects: [],
            player2_effects: []
        );
        $damages[$level]['player1'] = $damage['player1']['damage_dealt'];
        $damages[$level]['player2'] = $damage['player2']['damage_dealt'];
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

?>

<style>
    label {
        display: inline-block;
    }

    .scenario_input {
        width:500px;
        display:inline-block;
        border:1px solid #000000;
        background: rgba(0,0,0,0.4);
        border-radius:10px;
        padding:5px;
    }

    #scenario {
        display: flex;
        flex-direction: column;
        align-items: center;

        margin-top: 15px;
    }
    #scenario form input[type='submit'] {
        display: block;
        margin: 8px auto auto;
    }
</style>

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
<div id='scenario'>
    <div id='rank_select'>
        <?php foreach($ranks_prefill_data as $id => $rank): ?>
            <button onClick='prefillRank(<?= $rank['id'] ?>)'><?= $rank['id'] ?>: <?= $rank['name'] ?></button>
        <?php endforeach; ?>
    </div>
    <form action='rank_curve.php' method='post'>
        <div class='scenario_input'>
            <b>Sim details</b><br />
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
        <input type='submit' name='run_simulation' value='Run Simulation' />
    </form>
</div>
