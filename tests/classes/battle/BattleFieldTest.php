<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use PHPUnit\Framework\TestCase;

use SC\Factories\JutsuFactory;

final class BattleFieldTest extends TestCase {
    private static int $next_int = 1;

    private function initBattle(): Battle {
        $battle = $this->createStub(Battle::class);
        $battle->player1 = $this->createStub(Fighter::class);
        $battle->player1->combat_id = "P:1";

        $battle->player2 = $this->createStub(Fighter::class);
        $battle->player2->combat_id = "P:2";

        $battle->raw_field = json_encode([
            'fighter_locations' => [
                $battle->player1->combat_id => 2,
                $battle->player2->combat_id => 4
            ]
        ]);

        return $battle;
    }

    private function initAttack(Fighter $attacker, AttackTarget $target, Jutsu $jutsu = null): BattleAttack {
        if($jutsu == null) {
            $jutsu = JutsuFactory::create(
                range: 3
            );
        }

        return new BattleAttack(
            attacker_id: $attacker->combat_id,
            target: $target,
            jutsu: $jutsu,
            turn: self::$next_int++,
            starting_raw_damage: 1000
        );
    }

    /**
     * @throws Exception
     */
    public function testDirectionAttackPathMatchesRange() {
        $battle = $this->initBattle();
        $battleField = new BattleField(
            system: $this->createStub(System::class),
            battle: $battle,
        );

        $attacker = $battle->player1;
        $target = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $attack = $this->initAttack(attacker: $attacker, target: $target);

        $battleField->setupDirectionAttack(
            attacker: $attacker,
            attack: $attack,
            target: $target
        );

        /* ASSERT */
        $this->assertEquals(
            expected: $attack->jutsu->range,
            actual: count($attack->path_segments),
            message: 'Segments must equal jutsu range!'
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDirectionAttackPathEndsAtEdge(): void {
        $battle = $this->initBattle();
        $battleField = new BattleField(
            system: $this->createStub(System::class),
            battle: $battle,
        );

        $attacker = $battle->player1;
        $target = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $attack = $this->initAttack(attacker: $attacker, target: $target);

        $distance_from_edge = $attack->jutsu->range - 1;
        $battleField->fighter_locations[$attacker->combat_id] = $battleField->max_tile - $distance_from_edge;

        $battleField->setupDirectionAttack(
            attacker: $attacker,
            attack: $attack,
            target: $target
        );

        /* ASSERT */
        $this->assertEquals(
            expected: $distance_from_edge,
            actual: count($attack->path_segments),
            message: 'Jutsu should not have more segments than distance to edge!'
        );
    }

    /**
     * @throws Exception
     */
    public function testDirectionAttackPathSegmentsHaveCorrectTime() {
        $battle = $this->initBattle();
        $battleField = new BattleField(
            system: $this->createStub(System::class),
            battle: $battle,
        );

        $attacker = $battle->player1;
        $target = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $attack = $this->initAttack(attacker: $attacker, target: $target);

        $battleField->setupDirectionAttack(
            attacker: $attacker,
            attack: $attack,
            target: $target
        );

        /* ASSERT */
        foreach($attack->path_segments as $segment) {
            $distance_from_attacker = $segment->tile->index - $battleField->getFighterLocation($attacker->combat_id);
            $expected_time_elapsed = $distance_from_attacker * $attack->jutsu->travel_speed;

            $this->assertEquals($expected_time_elapsed, $segment->time_arrived);
        }
    }


}
