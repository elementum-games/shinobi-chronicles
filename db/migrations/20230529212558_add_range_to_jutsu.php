<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRangeToJutsu extends AbstractMigration {
    public function up(): void {
        $this->execute("ALTER TABLE `jutsu` ADD COLUMN `range` TINYINT NOT NULL DEFAULT 1 AFTER `power`;");
    }

    public function down(): void {
        $this->execute("ALTER TABLE `jutsu` DROP COLUMN `range`;");
    }
}
