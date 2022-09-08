<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use PHPUnit\Framework\TestCase;

class BattleLogTest extends TestCase {

    public function testAddingContents() {
        $fighter1 = $this->createStub(Fighter::class);
        $fighter1->combat_id = "U:1";

        $fighter2 = $this->createStub(Fighter::class);
        $fighter2->combat_id = "U:2";

        $log = new BattleLog(1, 1, '', []);

        $fighter1_action_text = "Does the thing";
        $log->addFighterActionDescription($fighter1, $fighter1_action_text);

        $fighter2_action_text = "Does another thing";
        $log->addFighterActionDescription($fighter2, $fighter2_action_text);

        $this->assertEquals(
            $fighter1_action_text,
            $log->fighter_action_logs[$fighter1->combat_id]->action_description
        );
        $this->assertEquals(
            $fighter2_action_text,
            $log->fighter_action_logs[$fighter2->combat_id]->action_description
        );
    }
}