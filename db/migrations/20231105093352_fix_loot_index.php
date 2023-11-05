<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixLootIndex extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            drop index `loot_id_claimed_village_id_battle_id_index` on `loot`;
            create index `loot_user_id_claimed_village_id_battle_id_index` on `loot`(`user_id`, `claimed_village_id`, `battle_id`);
        ");
    }

    public function down(): void {
        $this->execute("
            drop index `loot_user_id_claimed_village_id_battle_id_index` on `loot`;
            create index `loot_id_claimed_village_id_battle_id_index` on `loot`(`id`, `claimed_village_id`, `battle_id`);
        ");
    }
}
