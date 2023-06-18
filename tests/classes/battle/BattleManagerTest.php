<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use SC\Factories\JutsuFactory;
use SC\TestUtils\BattleTestCase;

class BattleManagerTest extends BattleTestCase {
    /**
     * @throws RuntimeException
     */
    public function testCollectPlayerAction_Attack() {
        // SETUP
        $system = $this->createStub(System::class);
        $system->method('query')
               ->will($this->returnValue([]));

        $battle = $this->initBattle();

        $battle->method('isAttackPhase')
               ->will($this->returnValue(true));

        $player = new User($system, (int)$battle->player1->id);
        $player->combat_id = $battle->player1->combat_id;
        $battle->player1 = $player;

        $jutsu = JutsuFactory::create();
        $jutsu->target_type = Jutsu::TARGET_TYPE_TILE;
        $jutsu->setLevel(1, 0);

        $player->jutsu = [
            $jutsu->id => $jutsu
        ];

        $player->chakra = 100;

        $battleManager = new BattleManagerV2(
            system: $system,
            player: $player,
            battle: $battle,
            default_attacks: [],
            spectate: false,
        );

        $FORM_DATA = [
            'jutsu_category' => 'ninjutsu',
            'jutsu_id' => 1,
            'hand_seals' => explode('-', $jutsu->hand_seals),
            'weapon_id' => 0,
            'target_tile' => 0,
            'submit_attack' => true,
        ];

        // RUN
        $fighter_action = $battleManager->collectPlayerAction($FORM_DATA);

        // ASSERT
        $this->assertInstanceOf(FighterAttackAction::class, $fighter_action);
    }

}