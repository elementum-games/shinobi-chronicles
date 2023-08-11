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
        // Locked out accounts
       $this->execute("ALTER TABLE `users`
            ADD COLUMN `login_attempt_time` INT NOT NULL DEFAULT 0 AFTER `failed_logins`,
            ADD COLUMN `last_malicious_ip` TEXT AFTER `login_attempt_time`
        ");
        // NPC stuff
        $this->execute("ALTER TABLE `ai_opponents` CHANGE `money` `money_multiplier` SMALLINT(3) NOT NULL;");
        // Rank 1 AI
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=1 WHERE `ai_id`=1");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=2");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=3");
        // Rank 2 AI
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=1 WHERE `ai_id`=4");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=1 WHERE `ai_id`=13");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=5");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=6");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=10");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=24");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=4 WHERE `ai_id`=11");
        // Rank 3 AI
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=1 WHERE `ai_id`=7");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=1 WHERE `ai_id`=25");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=26");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=12");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=27");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=28");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=29");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=8");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=33");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=34");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=35");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=36");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=9");
        // Rank 4 AI
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=1 WHERE `ai_id`=14");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=1 WHERE `ai_id`=15");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=16");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=17");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=21");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=30");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=2 WHERE `ai_id`=22");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=23");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=31");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=32");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=3 WHERE `ai_id`=18");
        $this->execute("UPDATE `ai_opponents` SET `money_multiplier`=4 WHERE `ai_id`=130");
    }
    public function down(): void
    {
        // Locked out accounts
        $this->execute("ALTER TABLE `users`
            DROP COLUMN `login_attempt_time`,
            DROP COLUMN `last_malicious_ip`
        ");
        // NPC stuff
        // Rank 1 AI
        $this->execute("ALTER TABLE `ai_opponents` CHANGE `money_multiplier` `money` INT(11) NOT NULL;");
        $this->execute("UPDATE `ai_opponents` SET `money`=20 WHERE `ai_id`=1");
        $this->execute("UPDATE `ai_opponents` SET `money`=40 WHERE `ai_id`=2");
        $this->execute("UPDATE `ai_opponents` SET `money`=45 WHERE `ai_id`=3");
        // Rank 2 AI
        $this->execute("UPDATE `ai_opponents` SET `money`=50 WHERE `ai_id`=4");
        $this->execute("UPDATE `ai_opponents` SET `money`=75 WHERE `ai_id`=13");
        $this->execute("UPDATE `ai_opponents` SET `money`=95 WHERE `ai_id`=5");
        $this->execute("UPDATE `ai_opponents` SET `money`=110 WHERE `ai_id`=6");
        $this->execute("UPDATE `ai_opponents` SET `money`=120 WHERE `ai_id`=10");
        $this->execute("UPDATE `ai_opponents` SET `money`=130 WHERE `ai_id`=24");
        $this->execute("UPDATE `ai_opponents` SET `money`=170 WHERE `ai_id`=11");
        // Rank 3 AI
        $this->execute("UPDATE `ai_opponents` SET `money`=150 WHERE `ai_id`=7");
        $this->execute("UPDATE `ai_opponents` SET `money`=140 WHERE `ai_id`=25");
        $this->execute("UPDATE `ai_opponents` SET `money`=180 WHERE `ai_id`=26");
        $this->execute("UPDATE `ai_opponents` SET `money`=190 WHERE `ai_id`=12");
        $this->execute("UPDATE `ai_opponents` SET `money`=195 WHERE `ai_id`=27");
        $this->execute("UPDATE `ai_opponents` SET `money`=200 WHERE `ai_id`=28");
        $this->execute("UPDATE `ai_opponents` SET `money`=205 WHERE `ai_id`=29");
        $this->execute("UPDATE `ai_opponents` SET `money`=210 WHERE `ai_id`=8");
        $this->execute("UPDATE `ai_opponents` SET `money`=215 WHERE `ai_id`=33");
        $this->execute("UPDATE `ai_opponents` SET `money`=220 WHERE `ai_id`=34");
        $this->execute("UPDATE `ai_opponents` SET `money`=225 WHERE `ai_id`=35");
        $this->execute("UPDATE `ai_opponents` SET `money`=230 WHERE `ai_id`=36");
        $this->execute("UPDATE `ai_opponents` SET `money`=240 WHERE `ai_id`=9");
        // Rank 4 AI
        $this->execute("UPDATE `ai_opponents` SET `money`=250 WHERE `ai_id`=14");
        $this->execute("UPDATE `ai_opponents` SET `money`=265 WHERE `ai_id`=15");
        $this->execute("UPDATE `ai_opponents` SET `money`=270 WHERE `ai_id`=16");
        $this->execute("UPDATE `ai_opponents` SET `money`=275 WHERE `ai_id`=17");
        $this->execute("UPDATE `ai_opponents` SET `money`=280 WHERE `ai_id`=21");
        $this->execute("UPDATE `ai_opponents` SET `money`=290 WHERE `ai_id`=30");
        $this->execute("UPDATE `ai_opponents` SET `money`=295 WHERE `ai_id`=22");
        $this->execute("UPDATE `ai_opponents` SET `money`=300 WHERE `ai_id`=23");
        $this->execute("UPDATE `ai_opponents` SET `money`=315 WHERE `ai_id`=31");
        $this->execute("UPDATE `ai_opponents` SET `money`=330 WHERE `ai_id`=32");
        $this->execute("UPDATE `ai_opponents` SET `money`=350 WHERE `ai_id`=18");
        $this->execute("UPDATE `ai_opponents` SET `money`=340 WHERE `ai_id`=130");
    }
}