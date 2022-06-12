<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use PHPUnit\Framework\TestCase;

final class BattleFieldTest extends TestCase {
    public function testCanInit() {
        $system = $this->createStub(System::class);
        $battle = $this->createStub(Battle::class);

        $fighter1 = $this->createStub(Fighter::class);
        $fighter1->combat_id = "P:1";

        $fighter2 = $this->createStub(Fighter::class);
        $fighter2->combat_id = "P:2";

        $battle->raw_field = json_encode([
            'fighter_locations' => [
                $fighter1->combat_id => 2,
                $fighter2->combat_id => 4
            ]
        ]);

        $this->assertInstanceOf(BattleField::class, new BattleField($system, $battle));
    }


}