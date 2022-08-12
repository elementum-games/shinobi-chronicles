<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use PHPUnit\Framework\TestCase;
use SC\Factories\JutsuFactory;
use SC\TestUtils\CollisionScenario;

class BattleActionProcessorAttackTest extends TestCase {
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
                $battle->player2->combat_id => 4,
            ],
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

    private function initActionProcessor($battle, $battleField): BattleActionProcessor {
        return new BattleActionProcessor(
            $this->createStub(System::class),
            $battle,
            $battleField,
            $this->createStub(BattleEffectsManager::class),
            function () {
            },
            []
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
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    public function testFindNextTileCollisionPointWorksWithEvenCollision() {
        $fighterA = $this->createStub(Fighter::class);
        $fighterA->combat_id = "P:1";

        $fighterB = $this->createStub(Fighter::class);
        $fighterB->combat_id = "P:2";

        $fighterAAttackTarget = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $fighterAAttack = $this->initAttack(
            attacker: $fighterA,
            target: $fighterAAttackTarget
        );

        $fighterBAttackTarget = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_LEFT);
        $fighterBAttack = $this->initAttack(
            attacker: $fighterB,
            target: $fighterBAttackTarget
        );

        /*
           0 1 2 3 4 5
           A - X >
               < X - B
         */
        $tiles = [
            0 => new BattleFieldTile(0, []),
            1 => new BattleFieldTile(1, []),
            2 => new BattleFieldTile(2, []),
            3 => new BattleFieldTile(3, []),
            4 => new BattleFieldTile(4, []),
            5 => new BattleFieldTile(5, []),
        ];

        $fighterAAttack->addPathSegment($tiles[1], 100, 1);
        $fighterAAttack->addPathSegment($tiles[2], 100, 2);
        $fighterAAttack->addPathSegment($tiles[3], 100, 3);

        $fighterBAttack->addPathSegment($tiles[4], 100, 1);
        $fighterBAttack->addPathSegment($tiles[3], 100, 2);
        $fighterBAttack->addPathSegment($tiles[2], 100, 3);

        $segments_by_tile_and_attack = [
            1 => [
                $fighterAAttack->id => new TileAttackSegment($fighterAAttack, $fighterAAttack->path_segments[0]),
            ],
            2 => [
                $fighterAAttack->id => new TileAttackSegment($fighterAAttack, $fighterAAttack->path_segments[1]),
                $fighterBAttack->id => new TileAttackSegment($fighterBAttack, $fighterBAttack->path_segments[2]),
            ],
            3 => [
                $fighterAAttack->id => new TileAttackSegment($fighterAAttack, $fighterAAttack->path_segments[2]),
                $fighterBAttack->id => new TileAttackSegment($fighterBAttack, $fighterBAttack->path_segments[1]),
            ],
            4 => [
                $fighterBAttack->id => new TileAttackSegment($fighterBAttack, $fighterBAttack->path_segments[0]),
            ],
        ];

        $attack1_collision_point = BattleActionProcessor::findNextTileCollisionPoint(
            attack: $fighterAAttack,
            other_attack: $fighterBAttack,
            segments_by_tile_and_attack: $segments_by_tile_and_attack
        );
        $attack2_collision_point = BattleActionProcessor::findNextTileCollisionPoint(
            attack: $fighterBAttack,
            other_attack: $fighterAAttack,
            segments_by_tile_and_attack: $segments_by_tile_and_attack
        );

        $this->assertEquals(2, $attack1_collision_point);
        $this->assertEquals(3, $attack2_collision_point);
    }

    /**
     * @throws Exception
     */
    public function testFindNextTileCollisionPointWorksWithOddCollision() {
        $fighterA = $this->createStub(Fighter::class);
        $fighterA->combat_id = "P:1";

        $fighterB = $this->createStub(Fighter::class);
        $fighterB->combat_id = "P:2";

        $fighterAAttackTarget = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $fighterAAttack = $this->initAttack(
            attacker: $fighterA,
            target: $fighterAAttackTarget
        );

        $fighterBAttackTarget = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_LEFT);
        $fighterBAttack = $this->initAttack(
            attacker: $fighterB,
            target: $fighterBAttackTarget
        );

        /*
           0 1 2 3 4
           A - X >
             < X - B
         */
        $tiles = [
            0 => new BattleFieldTile(0, []),
            1 => new BattleFieldTile(1, []),
            2 => new BattleFieldTile(2, []),
            3 => new BattleFieldTile(3, []),
            4 => new BattleFieldTile(4, []),
        ];

        $fighterAAttack->addPathSegment($tiles[1], 100, 1);
        $fighterAAttack->addPathSegment($tiles[2], 100, 2);
        $fighterAAttack->addPathSegment($tiles[3], 100, 3);

        $fighterBAttack->addPathSegment($tiles[3], 100, 1);
        $fighterBAttack->addPathSegment($tiles[2], 100, 2);
        $fighterBAttack->addPathSegment($tiles[1], 100, 3);

        $segments_by_tile_and_attack = [
            1 => [
                $fighterAAttack->id => new TileAttackSegment($fighterAAttack, $fighterAAttack->path_segments[0]),
                $fighterBAttack->id => new TileAttackSegment($fighterBAttack, $fighterBAttack->path_segments[2]),
            ],
            2 => [
                $fighterAAttack->id => new TileAttackSegment($fighterAAttack, $fighterAAttack->path_segments[1]),
                $fighterBAttack->id => new TileAttackSegment($fighterBAttack, $fighterBAttack->path_segments[1]),
            ],
            3 => [
                $fighterAAttack->id => new TileAttackSegment($fighterAAttack, $fighterAAttack->path_segments[2]),
                $fighterBAttack->id => new TileAttackSegment($fighterBAttack, $fighterBAttack->path_segments[0]),
            ],
        ];

        $attack1_collision_point = BattleActionProcessor::findNextTileCollisionPoint(
            attack: $fighterAAttack,
            other_attack: $fighterBAttack,
            segments_by_tile_and_attack: $segments_by_tile_and_attack
        );
        $attack2_collision_point = BattleActionProcessor::findNextTileCollisionPoint(
            attack: $fighterBAttack,
            other_attack: $fighterAAttack,
            segments_by_tile_and_attack: $segments_by_tile_and_attack
        );

        $this->assertEquals(1, $attack1_collision_point, "Attack 1 Collision point");
        $this->assertEquals(3, $attack2_collision_point, "Attack 2 Collision point");
    }

    private function runFindCollisionsScenario() {

    }

    /**
     * @throws Exception
     */
    public function testFindCollisions() {
        $battle = $this->initBattle();

        $rightAttackUser = $battle->player1;
        $leftAttackUser = $battle->player2;

        $scenarios = CollisionScenario::testScenarios($battle->player1, $battle->player2);

        $debug_closure = function ($category, $label, $contents) {
            /*
            echo "\r\nDEBUG ($label)\r\n";
            print_r($contents);
            echo "\r\n";*/
        };

        foreach($scenarios as $index => $scenario) {
            $battle->raw_field = json_encode([
                'fighter_locations' => [
                    $leftAttackUser->combat_id => 0,
                    $rightAttackUser->combat_id => $scenario->distance + 1,
                ]
            ]);

            $field = new BattleField($this->createStub(System::class), $battle);

            $actionProcessor = $this->initActionProcessor($battle, $field);

            $actionProcessor->setupDirectionAttack($leftAttackUser, $scenario->leftAttack, $scenario->leftAttackTarget);
            $actionProcessor->setupDirectionAttack($rightAttackUser, $scenario->rightAttack, $scenario->rightAttackTarget);

            $collisions = BattleActionProcessor::findCollisions($scenario->leftAttack, $scenario->rightAttack, $debug_closure);

            $this->assertCount(1, $collisions, "Scenario $index collision count");

            $collision_id = array_key_first($collisions);
            $collision = $collisions[$collision_id];

            /*
            $debug_closure('', 'collision', [
                'id' => $collision->id,
                'attack1' => $collision->attack1->id,
                'attack2' => $collision->attack2->id,
                'attack1_collision_point' => $collision->attack1_collision_point,
                'attack2_collision_point' => $collision->attack2_collision_point,
                'attack1_segment' => $collision->attack1_segment,
                'attack2_segment' => $collision->attack2_segment,
                'time_occurred' => $collision->time_occurred,
            ]);
            */

            $this->assertEquals(
                expected: $scenario->expected_left_attack_collision_point,
                actual: $collision->attack1_collision_point,
                message: "Scenario $index left attack collision point"
            );
            $this->assertEquals(
                expected: $scenario->expected_right_attack_collision_point,
                actual: $collision->attack2_collision_point,
                message: "Scenario $index right attack collision point"
            );
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testCollisionIdIsDeterministic(): void {
        $attack1 = new BattleAttack(
            attacker_id: "U:123",
            target: new AttackTileTarget(1),
            jutsu: JutsuFactory::create(),
            turn: 1,
            starting_raw_damage: 1
        );
        $attack2 = new BattleAttack(
            attacker_id: "U:124",
            target: new AttackTileTarget(1),
            jutsu: JutsuFactory::create(),
            turn: 1,
            starting_raw_damage: 1
        );

        $this->assertEquals(
            BattleActionProcessor::collisionId($attack1, $attack2),
            BattleActionProcessor::collisionId($attack2, $attack1)
        );
    }
}
