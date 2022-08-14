<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace SC\TestUtils;

use AttackTarget;
use Battle;
use BattleActionProcessor;
use BattleAttack;
use BattleEffectsManager;
use Fighter;
use Jutsu;
use PHPUnit\Framework\TestCase;
use SC\Factories\JutsuFactory;
use System;

class BattleTestCase extends TestCase {
    private static int $next_int = 1;

    protected function initBattle(int $player1Location = 2, int $player2Location = 4): Battle {
        $battle = $this->createStub(Battle::class);
        $battle->player1 = $this->createStub(Fighter::class);
        $battle->player1->id = 1;
        $battle->player1->combat_id = Battle::combatId(Battle::TEAM1, $battle->player1);

        $battle->player2 = $this->createStub(Fighter::class);
        $battle->player2->id = 1;
        $battle->player2->combat_id = Battle::combatId(Battle::TEAM2, $battle->player2);

        $battle->method('getFighter')
               ->will(
                   $this->returnValueMap([
                       [$battle->player1->combat_id, $battle->player1],
                       [$battle->player2->combat_id, $battle->player2],
                   ])
               );

        $battle->raw_field = json_encode([
            'fighter_locations' => [
                $battle->player1->combat_id => $player1Location,
                $battle->player2->combat_id => $player2Location,
            ],
        ]);

        return $battle;
    }

    protected function initAttack(Fighter $attacker, AttackTarget $target, int $range = 3): BattleAttack {
        return $this->initAttackWithJutsu(
            $attacker,
            $target,
            JutsuFactory::create(
                range: $range
            )
        );
    }

    protected function initAttackWithJutsu(Fighter $attacker, AttackTarget $target, Jutsu $jutsu): BattleAttack {
        return new BattleAttack(
            attacker_id: $attacker->combat_id,
            target: $target,
            jutsu: $jutsu,
            turn: self::$next_int++,
            starting_raw_damage: 1000
        );
    }

    protected function initActionProcessor($battle, $battleField): BattleActionProcessor {
        return new BattleActionProcessor(
            system: $this->createStub(System::class),
            battle: $battle,
            field: $battleField,
            effects: $this->createStub(BattleEffectsManager::class),
            debug_closure: function ($category, $label, $contents) {
                echo "\r\nDEBUG ($label)\r\n$contents\r\n";
            },
            default_attacks: []
        );
    }
}