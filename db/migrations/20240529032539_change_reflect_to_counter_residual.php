<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeReflectToCounterResidual extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
           UPDATE `jutsu` SET `effect`='counter_residual' WHERE `effect`='reflect';
           UPDATE `jutsu` SET `effect2`='counter_residual' WHERE `effect2`='reflect';
           UPDATE `bloodlines` SET `bloodlines`.`jutsu`=REPLACE(`jutsu`, '\"effect\":\"reflect\"', '\"effect\":\"counter_residual\"');
        ");

    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
           UPDATE `jutsu` SET `effect`='reflect' WHERE `effect`='counter_residual';
           UPDATE `jutsu` SET `effect2`='reflect' WHERE `effect2`='counter_residual';
           UPDATE `bloodlines` SET `bloodlines`.`jutsu`=REPLACE(`jutsu`, '\"effect\":\"counter_residual\"', '\"effect\":\"reflect\"');
        ");
    }
}
