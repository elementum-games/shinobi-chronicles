<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use SC\TestUtils\BattleTestCase;

class BattleActionProcessorAttackTest extends BattleTestCase {
    /**
     * @throws RuntimeException
     */
    public function testDirectionAttackPathMatchesRange() {
        $battle = $this->initBattle();
        $battleField = new BattleField(
            system: $this->createStub(System::class),
            battle: $battle,
        );

        $battleActionProcessor = $this->initActionProcessor($battle, $battleField);

        $attacker = $battle->player1;
        $target = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $attack = $this->initAttack(attacker: $attacker, target: $target);

        $battleActionProcessor->setupDirectionAttack(
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
     * @throws RuntimeException
     */
    public function testDirectionAttackPathEndsAtEdge(): void {
        $battle = $this->initBattle();
        $battleField = new BattleField(
            system: $this->createStub(System::class),
            battle: $battle,
        );
        $battleActionProcessor = $this->initActionProcessor($battle, $battleField);

        $attacker = $battle->player1;
        $target = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $attack = $this->initAttack(attacker: $attacker, target: $target);

        $distance_from_edge = $attack->jutsu->range - 1;
        $battleField->fighter_locations[$attacker->combat_id] = $battleField->max_tile - $distance_from_edge;

        $battleActionProcessor->setupDirectionAttack(
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
     * @throws RuntimeException
     */
    public function testDirectionAttackPathSegmentsHaveCorrectTime() {
        $battle = $this->initBattle();
        $battleField = new BattleField(
            system: $this->createStub(System::class),
            battle: $battle,
        );
        $battleActionProcessor = $this->initActionProcessor($battle, $battleField);

        $attacker = $battle->player1;
        $target = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $attack = $this->initAttack(attacker: $attacker, target: $target);

        $battleActionProcessor->setupDirectionAttack(
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

    /**
     * @throws RuntimeException
     */
    public function testAttackHitsOpponent() {
        $battle = $this->initBattle(
            player1Location: 1,
            player2Location: 3
        );
        $battleField = new BattleField(
            system: $this->createStub(System::class),
            battle: $battle,
        );
        $battleActionProcessor = $this->initActionProcessor($battle, $battleField);

        $fighter1Attack = $this->initAttack(
            attacker: $battle->player1,
            target: new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT),
            range: 3
        );
        $fighter1Attack->path_segments = [
            new AttackPathSegment(0, new BattleFieldTile(2, []), 1000, 1),
            new AttackPathSegment(1, new BattleFieldTile(3, [$battle->player2->combat_id]), 900, 2),
            new AttackPathSegment(2, new BattleFieldTile(4, []), 800, 3),
        ];
        $fighter1Attack->is_path_setup = true;

        $fighter2Attack = $this->initAttack(
            attacker: $battle->player2,
            target: new AttackTileTarget(1),
            range: 3
        );
        $fighter2Attack->path_segments = [
            new AttackPathSegment(0, new BattleFieldTile(3, [$battle->player1->combat_id]), 500, 2),
        ];
        $fighter2Attack->is_path_setup = true;


        /* ASSERT */
        $this->assertEquals(false, $fighter1Attack->are_hits_calculated);
        $this->assertEquals(false, $fighter2Attack->are_hits_calculated);

        $battleActionProcessor->findAttackHits($battle->player1, $fighter1Attack);
        $battleActionProcessor->findAttackHits($battle->player2, $fighter2Attack);

        $this->assertEquals(true, $fighter1Attack->are_hits_calculated);
        $this->assertEquals(true, $fighter2Attack->are_hits_calculated);
        $this->assertCount(1, $fighter1Attack->hits);
        $this->assertCount(1, $fighter2Attack->hits);

        $hit1 = $fighter1Attack->hits[0];
        $hit2 = $fighter2Attack->hits[0];

        $this->assertEquals($battle->player1, $hit1->attacker);
        $this->assertEquals($battle->player2, $hit1->target);
        $this->assertEquals(900, $hit1->raw_damage);

        $this->assertEquals($battle->player2, $hit2->attacker);
        $this->assertEquals($battle->player1, $hit2->target);
        $this->assertEquals(500, $hit2->raw_damage);
    }
}
