<?php

// START INIT
/** @var System $system */
require __DIR__ . "/../_authenticate_admin.php";

require __DIR__ . "/TestFighter.php";
require __DIR__ . "/calcDamage.php";

/** @var string[] $bloodline_combat_boosts */
require_once __DIR__ . '/../entity_constraints.php';

$rankManager = new RankManager($system);
$rankManager->loadRanks();
// END INIT

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
        $player1 = TestFighter::fromFormData(
            system: $system,
            rankManager: $rankManager,
            fighter_data: [
                'ninjutsu_skill' => 0,
                'taijutsu_skill' => floor($total_stats * 0.33334),
                'genjutsu_skill' => 0,
                'bloodline_skill' => floor($total_stats * 0.33334),
                'speed' => floor($total_stats * 0.33334),
                'cast_speed' => 0,
                'bloodline_boost_1' => 'taijutsu_boost',
                'bloodline_boost_1_power' => 30,
                'bloodline_boost_2' => 'taijutsu_resist',
                'bloodline_boost_2_power' => 10,
            ],
            name: "Player 1"
        );
        $player1_jutsu = new Jutsu(
            id: 1,
            name: 'p1j',
            rank: $player1->rank,
            jutsu_type: JutsuOffenseType::TAIJUTSU,
            base_power: $jutsu_power,
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
            target_type: Jutsu::TARGET_TYPE_TILE,
            use_cost: 0,
            purchase_cost: 0,
            purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            parent_jutsu: 0,
            element: Element::NONE,
            hand_seals: 1
        );
        $player1_jutsu->setLevel(50, 0);

        // This is the one we'll change
        $remaining_stats = $total_stats - $scenario['player2_speed'];
        $scenario['player2_offense'] = floor($remaining_stats / 2);
        $scenario['player2_bloodline_skill'] = floor($remaining_stats / 2);

        $player2 = TestFighter::fromFormData(
            system: $system,
            rankManager: $rankManager,
            fighter_data: [
                'ninjutsu_skill' => 0,
                'taijutsu_skill' => $scenario['player2_offense'],
                'genjutsu_skill' => 0,
                'bloodline_skill' => $scenario['player2_bloodline_skill'],
                'speed' => ceil($scenario['player2_speed']),
                'cast_speed' => 0,
                'bloodline_boost_1' => 'taijutsu_boost',
                'bloodline_boost_1_power' => 30,
                'bloodline_boost_2' => 'taijutsu_resist',
                'bloodline_boost_2_power' => 10,
            ],
            name: "Player 2"
        );
        $player2_jutsu = new Jutsu(
            id: 1,
            name: 'p2j',
            rank: $player2->rank,
            jutsu_type: JutsuOffenseType::TAIJUTSU,
            base_power: $jutsu_power,
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
            target_type: Jutsu::TARGET_TYPE_TILE,
            use_cost: 0,
            purchase_cost: 0,
            purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            parent_jutsu: 0,
            element: Element::NONE,
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
        $player2_damage_ratio = round($scenario['damages']['player2']['damage_dealt'] / $scenario['damages']['player1']['damage_dealt'], 2);

        /*            echo "<div class='scenario'>"
                        . "<label class='playerLabel'>Player 1:</label>"
                        . "<label class='statsLabel'>{$player1->taijutsu_skill} off / {$player1->bloodline_skill} BL / {$player1->speed} speed</label>"
                        . "<br />"
                        . "<label class='playerLabel'>Player 2:</label>"
                        . "<label class='statsLabel'>{$scenario['player2_offense']} off / {$scenario['player2_bloodline_skill']} BL / {$scenario['player2_speed']} speed</label>"
                        . "<br />"
                        . "<b>Speed ratio: {$player2_speed_ratio}x</b><br />"
                        . "<b>Damage: {$player2_damage_ratio}x</b><br />"
                        // . "({$scenario['damages']['player2']['damage_dealt']} vs {$scenario['damages']['player1']['damage_dealt']})</b>"
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
