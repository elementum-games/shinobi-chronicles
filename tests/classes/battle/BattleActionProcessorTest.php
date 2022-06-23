<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace SC\classes\battle;

use AttackTileTarget;
use BattleActionProcessor;
use BattleAttack;
use PHPUnit\Framework\TestCase;
use SC\Factories\JutsuFactory;

class BattleActionProcessorTest extends TestCase {
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
