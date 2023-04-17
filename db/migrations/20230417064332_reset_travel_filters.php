<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ResetTravelFilters extends AbstractMigration
{
    public function up(): void {
        $this->execute("UPDATE `users` SET `filters`=NULL;");
    }
}
