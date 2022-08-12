<?php

namespace SC\TestUtils;

use AttackDirectionTarget;
use BattleAttack;
use Fighter;
use SC\Factories\JutsuFactory;

class CollisionScenario {
    // The attack coming from the left
    public BattleAttack $leftAttack;
    // The attack coming from the right
    public BattleAttack $rightAttack;

    public AttackDirectionTarget $rightAttackTarget;
    public AttackDirectionTarget $leftAttackTarget;

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
    ) {
        $this->leftAttack = $this->createLeftAttack(
            $leftAttackUser->combat_id, $leftAttackRange, $leftAttackSpeed
        );
        $this->rightAttack = $this->createRightAttack($rightAttackUser->combat_id, $rightAttackRange, $rightAttackSpeed);
        $this->distance = $distance;

        $this->expected_right_attack_collision_point = $expected_right_attack_collision_point;
        $this->expected_left_attack_collision_point = $expected_left_attack_collision_point;
    }

    private function createRightAttack(string $attacker_id, int $range, float $speed): BattleAttack {
        $jutsu = JutsuFactory::create($range);
        $jutsu->travel_speed = $speed;

        $this->rightAttackTarget = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_LEFT);

        return new BattleAttack(
            $attacker_id,
            $this->rightAttackTarget,
            $jutsu,
            1,
            1000
        );
    }

    private function createLeftAttack(string $attacker_id, int $range, float $speed): BattleAttack {
        $jutsu = JutsuFactory::create($range);
        $jutsu->travel_speed = $speed;

        $this->leftAttackTarget = new AttackDirectionTarget(AttackDirectionTarget::DIRECTION_RIGHT);

        return new BattleAttack(
            $attacker_id,
            $this->leftAttackTarget,
            $jutsu,
            1,
            1000
        );
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
        ];
    }
}