<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class YenGainUpdate extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        // Rank 1 AI
        $this->execute("ALTER TABLE `ai_opponents` CHANGE `money` `money` FLOAT(10,2) NOT NULL;");
        $this->execute("UPDATE `ai_opponents` SET `money`=0.25 WHERE `ai_id`=1");
        $this->execute("UPDATE `ai_opponents` SET `money`=0.75 WHERE `ai_id`=2");
        $this->execute("UPDATE `ai_opponents` SET `money`=1 WHERE `ai_id`=3");
    }
    public function down(): void
    {
        // Rank 1 AI
        $this->execute("ALTER TABLE `ai_opponents` CHANGE `money` `money` INT(11) NOT NULL;");
        $this->execute("UPDATE `ai_opponents` SET `money`=20 WHERE `ai_id`=1");
        $this->execute("UPDATE `ai_opponents` SET `money`=40 WHERE `ai_id`=2");
        $this->execute("UPDATE `ai_opponents` SET `money`=50 WHERE `ai_id`=3");
    }
}
