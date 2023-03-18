<?php

namespace SC\TestUtils;

use AttackDirectionTarget;
use AttackTarget;
use BattleAttackV2;
use Fighter;
use SC\Factories\JutsuFactory;

class CollisionScenario {
    // The attack coming from the left
    public BattleAttackV2 $leftAttack;
    // The attack coming from the right
    public BattleAttackV2 $rightAttack;

    public Fighter $left_attack_user;
    public Fighter $right_attack_user;

    public AttackTarget $right_attack_target;
    public AttackTarget $left_attack_target;

    public ?int $expected_left_attack_collision_point;
    public ?int $expected_right_attack_collision_point;

    public int $distance;

    public function __construct(
        int $distance,
        Fighter $leftAttackUser,
        Fighter $rightAttackUser,
        int $leftAttackRange = 6,
        int $rightAttackRange = 6,
        float $leftAttackSpeed = 1,
        float $rightAttackSpeed = 1,
        int $expected_left_attack_collision_point = null,
        int $expected_right_attack_collision_point = null,
        ?AttackTarget $left_attack_target = null,
        ?AttackTarget $right_attack_target = null
    ) {
        $this->leftAttack = $this->createLeftAttack(
            $leftAttackUser->combat_id, $leftAttackRange, $leftAttackSpeed, $left_attack_target
        );
        $this->rightAttack = $this->createRightAttack(
            $rightAttackUser->combat_id, $rightAttackRange, $rightAttackSpeed, $right_attack_target
        );

        $this->left_attack_user = $leftAttackUser;
        $this->right_attack_user = $rightAttackUser;

        $this->distance = $distance;

        $this->expected_right_attack_collision_point = $expected_right_attack_collision_point;
        $this->expected_left_attack_collision_point = $expected_left_attack_collision_point;
    }

    private function createRightAttack(string $attacker_id, int $range, float $speed, ?AttackTarget $target): BattleAttackV2 {
        $jutsu = JutsuFactory::create($range);
        $jutsu->travel_speed = $speed;

        if(!$target) {
            $target = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_LEFT);
        }
        $this->right_attack_target = $target;

        return new BattleAttackV2(
            attacker_id: $attacker_id,
            target: $this->right_attack_target,
            jutsu: $jutsu,
            turn: 1,
            starting_raw_damage: 1000
        );
    }

    private function createLeftAttack(string $attacker_id, int $range, float $speed, ?AttackTarget $target): BattleAttackV2 {
        $jutsu = JutsuFactory::create($range);
        $jutsu->travel_speed = $speed;

        if(!$target) {
            $target = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);
        }
        $this->left_attack_target = $target;

        return new BattleAttackV2(
            attacker_id: $attacker_id,
            target: $this->left_attack_target,
            jutsu: $jutsu,
            turn: 1,
            starting_raw_damage: 1000
        );
    }

    public function getFighterLocations(): array {
        return [
            $this->left_attack_user->combat_id => 0,
            $this->right_attack_user->combat_id => $this->distance + 1
        ];
    }

    public static function testScenarios(Fighter $leftFighter, Fighter $rightFighter): array {
        return [
            // speed 1
            1 => new CollisionScenario(
                distance: 9,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                expected_left_attack_collision_point: 4,
                expected_right_attack_collision_point: 6,
            ),

            2 => new CollisionScenario(
                distance: 8,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                leftAttackSpeed: 2,
                rightAttackSpeed: 1,
                expected_left_attack_collision_point: 5,
                expected_right_attack_collision_point: 7,
            ),
            3 => new CollisionScenario(
                distance: 9,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                rightAttackSpeed: 2,
                expected_left_attack_collision_point: 3,
                expected_right_attack_collision_point: 5,
            ),
            4 => new CollisionScenario(
                distance: 9,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                leftAttackSpeed: 1,
                rightAttackSpeed: 4,
                expected_left_attack_collision_point: 3,
                expected_right_attack_collision_point: 4,
            ),
            5 => new CollisionScenario(
                distance: 12,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                leftAttackRange: 9,
                rightAttackRange: 9,
                leftAttackSpeed: 4,
                rightAttackSpeed: 4,
                expected_left_attack_collision_point: 4,
                expected_right_attack_collision_point: 9,
            ),
            6 => new CollisionScenario(
                distance: 8,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                leftAttackRange: 4,
                rightAttackRange: 6,
                leftAttackSpeed: 1,
                rightAttackSpeed: 1,
                expected_left_attack_collision_point: 4,
                expected_right_attack_collision_point: 5,
            ),

            // TIle vs tile - No collision
            7 => new CollisionScenario(
                distance: 4,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                leftAttackRange: 4,
                rightAttackRange: 4,
                leftAttackSpeed: 1,
                rightAttackSpeed: 1,
                expected_left_attack_collision_point: null,
                expected_right_attack_collision_point: null,
                left_attack_target: new \AttackTileTarget(2),
                right_attack_target: new \AttackTileTarget(3),
            ),

            // Tile vs tile collision
            8 => new CollisionScenario(
                distance: 4,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                leftAttackRange: 4,
                rightAttackRange: 4,
                leftAttackSpeed: 1,
                rightAttackSpeed: 1,
                expected_left_attack_collision_point: 3,
                expected_right_attack_collision_point: 3,
                left_attack_target: new \AttackTileTarget(3),
                right_attack_target: new \AttackTileTarget(3),
            ),

            // Tile vs direction collision
            9 => new CollisionScenario(
                distance: 4,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                leftAttackRange: 4,
                rightAttackRange: 4,
                leftAttackSpeed: 1,
                rightAttackSpeed: 1,
                expected_left_attack_collision_point: 3,
                expected_right_attack_collision_point: 4,
                left_attack_target: new \AttackTileTarget(3),
            ),

            /*
             *   1 2 3 >
             *   < 7 6 5 4 3 2 1
             */
            10 => new CollisionScenario(
                distance: 8,
                leftAttackUser: $leftFighter,
                rightAttackUser: $rightFighter,
                leftAttackRange: 3,
                rightAttackRange: 7,
                leftAttackSpeed: 1,
                rightAttackSpeed: 1,
                expected_left_attack_collision_point: 3,
                expected_right_attack_collision_point: 4,
            ),
        ];
    }
}