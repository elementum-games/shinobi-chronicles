<?php /** @noinspection PhpTypedPropertyMightBeUninitializedInspection */

require __DIR__ . '/../../vendor/autoload.php';

use SC\TestUtils\CollisionScenario;

function runSimulation(): void {
    try {
        $system = new System();

        $leftAttackUser = new User($system, 123);
        $leftAttackUser->combat_id = Battle::combatId(Battle::TEAM1, $leftAttackUser);

        $rightAttackUser = new User($system, 234);
        $rightAttackUser->combat_id = Battle::combatId(Battle::TEAM2, $rightAttackUser);

        /** @var CollisionScenario[] $scenarios */
        $scenarios = CollisionScenario::testScenarios($leftAttackUser, $rightAttackUser);

        foreach($scenarios as $index => $scenario) {
            $battle = new Battle($system, $leftAttackUser, 1);
            $battle->raw_field = json_encode([
                'fighter_locations' => $scenario->getFighterLocations()
            ]);

            $field = new BattleField($system, $battle);

            $actionProcessor = new BattleActionProcessor(
                $system,
                $battle,
                $field,
                new BattleEffectsManager($system, [], []),
                function() {},
                []
            );

            $actionProcessor->setAttackPath($leftAttackUser, $scenario->leftAttack);
            $actionProcessor->setAttackPath($rightAttackUser, $scenario->rightAttack);

            $collisions = BattleActionProcessor::findCollisions($scenario->leftAttack, $scenario->rightAttack, function() {});

            renderResults($index, $scenario, $collisions);
        }
    } catch (Throwable $e) {
        echo $e->getMessage();
    }
}

/**
 * @param CollisionScenario $scenario
 * @param AttackCollision[] $collisions
 * @return void
 */
function renderResults(int $scenarioNum, CollisionScenario $scenario, array $collisions): void {
    $tile_width = 30;

    $total_width = $tile_width * $scenario->distance;

    $left_attack_width = count($scenario->leftAttack->path_segments) * $tile_width;
    $right_attack_width = count($scenario->rightAttack->path_segments) * $tile_width;

    $right_attack_collision_points = [];
    $left_attack_collision_points = [];

    foreach($collisions as $collision) {
        if($collision->attack1->id == $scenario->rightAttack->id) {
            $right_attack_collision_points[$collision->attack1_collision_point] = $collision->attack1_collision_point;
            $left_attack_collision_points[$collision->attack2_collision_point] = $collision->attack2_collision_point;
        }
        else {
            $right_attack_collision_points[$collision->attack2_collision_point] = $collision->attack2_collision_point;
            $left_attack_collision_points[$collision->attack1_collision_point] = $collision->attack1_collision_point;
        }
    }

    ?>
    <style>
        .container {
            border: 1px solid blue;
            background: #dadada;
        }
        .scenarioLabel {
            margin-bottom: 4px;
        }

        .leftAttack {
            display:flex;
            background:rgba(0,0,255,0.2);
        }
        .rightAttack {
            display: flex;
            flex-direction: row-reverse;
            background: rgba(0,255,0,0.2);
        }


        .tile {
            display:inline-flex;
            position: relative;
            justify-content: center;
            text-align: center;
        }
        .index {
            position: absolute;
            font-size: 10px;
            bottom: 2px;
            right: 2px;
        }
    </style>

    <div style='margin:20px 10px;'>
        <h4 class='scenarioLabel'>CollisionScenario <?= $scenarioNum ?></h4>
        <div class='container' style='width:<?= ($total_width + 1) ?>px;'>
            <div class='leftAttack' style='width:<?= $left_attack_width ?>px;'>
                <?php foreach($scenario->leftAttack->path_segments as $segment): ?>
                    <div class='tile' style='
                            width:<?= $tile_width ?>px;
                            height:<?= $tile_width ?>px;
                    <?= isset($left_attack_collision_points[$segment->tile->index]) ? "background:gold;" : "" ?>
                            '>
                        <?= $segment->time_arrived ?>
                        <span class='index'><?= $segment->tile->index ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class='rightAttack' style='width: <?= $right_attack_width ?>px;margin-left: <?= ($total_width - $right_attack_width) ?>px;'>
                <?php foreach($scenario->rightAttack->path_segments as $segment): ?>
                    <div class='tile' style='
                            width:<?= $tile_width ?>px;
                            height:<?= $tile_width ?>px;
                    <?= isset($right_attack_collision_points[$segment->tile->index]) ? "background:gold;" : "" ?>
                            '>
                        <?= $segment->time_arrived ?>
                        <span class='index'><?= $segment->tile->index ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

?>

<html lang="en">
    <body style='background: #e0e0e0;'>
        <?php runSimulation(); ?>
    </body>
</html>

