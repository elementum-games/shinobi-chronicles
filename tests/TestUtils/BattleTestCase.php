<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace SC\TestUtils;

use AttackTarget;
use BattleV2;
use BattleActionProcessor;
use BattleAttackV2;
use BattleEffectsManagerV2;
use Fighter;
use Jutsu;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use SC\Factories\JutsuFactory;
use System;

class BattleTestCase extends TestCase {
    private static int $next_int = 1;

    /**
     * @param int $player1Location
     * @param int $player2Location
     * @return BattleV2|Stub
     */
    protected function initBattle(int $player1Location = 2, int $player2Location = 4): Stub|BattleV2 {
        $battle = $this->createStub(BattleV2::class);
        $battle->battle_id = self::$next_int++;

        $battle->player1 = $this->createStub(Fighter::class);
        $battle->player1->id = 1;
        $battle->player1->combat_id = BattleV2::combatId(BattleV2::TEAM1, $battle->player1);

        $battle->player2 = $this->createStub(Fighter::class);
        $battle->player2->id = 1;
        $battle->player2->combat_id = BattleV2::combatId(BattleV2::TEAM2, $battle->player2);

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

        $battle->raw_active_effects = json_encode([]);
        $battle->raw_active_genjutsu = json_encode([]);

        return $battle;
    }

    protected function initAttack(Fighter $attacker, AttackTarget $target, int $range = 3): BattleAttackV2 {
        return $this->initAttackWithJutsu(
            $attacker,
            $target,
            JutsuFactory::create(
                range: $range
            )
        );
    }

    protected function initAttackWithJutsu(Fighter $attacker, AttackTarget $target, Jutsu $jutsu): BattleAttackV2 {
        return new BattleAttackV2(
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
            effects: $this->createStub(BattleEffectsManagerV2::class),
            debug_closure: function ($category, $label, $contents) {
                echo "\r\nDEBUG ($label)\r\n$contents\r\n";
            },
            default_attacks: []
        );
    }
}