<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropOldDBVersioning extends AbstractMigration  {
    public function up(): void {
        $this->execute("alter table `system_storage` drop column `database_version`;");
    }

    public function down(): void {
        $this->execute("alter table `system_storage` add column `database_version` varchar(128) NOT NULL after `time`;");
    }
}
