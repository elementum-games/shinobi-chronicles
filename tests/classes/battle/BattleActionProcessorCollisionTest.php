<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use PHPUnit\Framework\TestCase;
use SC\Factories\JutsuFactory;
use SC\TestUtils\CollisionScenario;

class BattleActionProcessorCollisionTest extends TestCase {
    private static int $next_int = 1;

    private function initBattle(int $player1Location = 2, int $player2Location = 4): Battle {
        $battle = $this->createStub(Battle::class);
        $battle->player1 = $this->createStub(Fighter::class);
        $battle->player1->combat_id = "P:1";

        $battle->player2 = $this->createStub(Fighter::class);
        $battle->player2->combat_id = "P:2";

        $battle->method('getFighter')
               ->will($this->returnValueMap([
                   [$battle->player1->combat_id, $battle->player1],
                   [$battle->player2->combat_id, $battle->player2],
               ]));

        $battle->raw_field = json_encode([
            'fighter_locations' => [
                $battle->player1->combat_id => $player1Location,
                $battle->player2->combat_id => $player2Location,
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

    /**
     *
     * @throws Exception
     */
    public function testFindCollisions() {
        $battle = $this->initBattle();

        $rightAttackUser = $battle->player1;
        $leftAttackUser = $battle->player2;

        // Open /tests/_manual/test_collision_points.php in your browser for a visualization of these test cases
        $scenarios = CollisionScenario::testScenarios($battle->player1, $battle->player2);

        $debug_closure = function ($category, $label, $contents) {
            // echo "\r\nDEBUG ($label)\r\n" . $contents . "\r\n";
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

            $debug_closure('', 'collision', print_r($collision->toArray(), true));

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
     * @throws Exception
     */
    public function testProcessCollisionsWeakensCollidingAttack() {
        /*
         * 1 2 3 4 5 6
         *   A - - >
         *   < - - B
         *
         * Player 1 has higher speed and player 2's damage should thus be reduced
         */
        $battle = $this->initBattle(
            player1Location: 2,
            player2Location: 5
        );

        $battle->player1->speed = 100;
        $battle->player1->cast_speed = 100;

        $battle->player2->speed = 50;
        $battle->player2->cast_speed = 50;

        $this->assertEquals($battle->getFighter($battle->player1->combat_id), $battle->player1);
        $this->assertEquals($battle->getFighter($battle->player2->combat_id), $battle->player2);

        $battleField = new BattleField($this->createStub(System::class), $battle);

        $leftFighter = $battle->player1;
        $rightFighter = $battle->player2;

        $leftFighterAttackTarget = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        $leftFighterAttack = $this->initAttack(
            attacker: $leftFighter,
            target: $leftFighterAttackTarget,
            jutsu: JutsuFactory::create(range: 3),
        );

        $rightFighterAttackTarget = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_LEFT);
        $rightFighterAttack = $this->initAttack(
            attacker: $rightFighter,
            target: $rightFighterAttackTarget,
            jutsu: JutsuFactory::create(range: 3),
        );

        $ORIGINAL_DAMAGE = 100;

        $leftFighterAttack->addPathSegment(new BattleFieldTile(3, []), $ORIGINAL_DAMAGE, 1); // 0
        $leftFighterAttack->addPathSegment(new BattleFieldTile(4, []), $ORIGINAL_DAMAGE, 2); // 1
        $leftFighterAttack->addPathSegment(new BattleFieldTile(5, []), $ORIGINAL_DAMAGE, 3); // 2

        $rightFighterAttack->addPathSegment(new BattleFieldTile(4, []), $ORIGINAL_DAMAGE, 1); // 0
        $rightFighterAttack->addPathSegment(new BattleFieldTile(3, []), $ORIGINAL_DAMAGE, 2); // 1
        $rightFighterAttack->addPathSegment(new BattleFieldTile(2, []), $ORIGINAL_DAMAGE, 3); // 2

        $actionProcessor = $this->initActionProcessor($battle, $battleField);

        $collisions = [
            new AttackCollision(
                BattleActionProcessor::collisionId($leftFighterAttack, $rightFighterAttack),
                $leftFighterAttack, $rightFighterAttack,
                3,
                3,
                $leftFighterAttack->path_segments[0],
                $rightFighterAttack->path_segments[1],
                2
            )
        ];
        $actionProcessor->processCollisions($collisions);

        $this->assertEquals($ORIGINAL_DAMAGE, $leftFighterAttack->path_segments[0]->raw_damage);
        $this->assertEquals($ORIGINAL_DAMAGE, $leftFighterAttack->path_segments[1]->raw_damage);
        $this->assertEquals($ORIGINAL_DAMAGE, $leftFighterAttack->path_segments[2]->raw_damage);

        $this->assertEquals($ORIGINAL_DAMAGE, $rightFighterAttack->path_segments[0]->raw_damage);
        $this->assertLessThan($ORIGINAL_DAMAGE, $rightFighterAttack->path_segments[1]->raw_damage);
        $this->assertLessThan($ORIGINAL_DAMAGE, $rightFighterAttack->path_segments[2]->raw_damage);
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
