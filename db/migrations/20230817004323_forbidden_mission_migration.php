<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ForbiddenMissionMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Modify missions table
        $this->execute("
            ALTER TABLE `missions`
                ADD `custom_start_location` varchar(50) DEFAULT NULL;
        ");

        // Insert into missions table
        $this->execute("
            INSERT INTO `missions` (`mission_id`, `name`, `rank`, `mission_type`, `stages`, `money`, `rewards`, `custom_start_location`) VALUES
                (151, 'Gather Scroll Materials', 1, 7, '[{\"action_type\":\"travel\",\"action_data\":\"0\",\"location_radius\":\"20\",\"count\":\"0\",\"description\":\"Acting as the servant of a madman is beneath you, but such is the price of power. Head to [action_data] and obtain the necessary materials.\"},{\"action_type\":\"travel\",\"action_data\":\"0\",\"location_radius\":\"20\",\"count\":\"0\",\"description\":\"Acting as the servant of a madman is beneath you, but such is the price of power. Head to [action_data] and obtain the necessary materials.\"}]', 0, '[{\"item_id\":\"131\",\"chance\":\"100\",\"quantity\":\"2\"}]', '26:10:1'),
                (152, 'Quell Lingering Spirits', 2, 7, '[{\"action_type\":\"travel\",\"action_data\":\"0\",\"location_radius\":\"1\",\"count\":\"0\",\"description\":\"To man the darkest chapters of history are but forgotten tales, while the spirits bear eternal witness. Head to [action_data].\"},{\"action_type\":\"combat\",\"action_data\":\"25\",\"location_radius\":\"0\",\"count\":\"0\",\"description\":\"Quell the spirits.\"},{\"action_type\":\"combat\",\"action_data\":\"25\",\"location_radius\":\"0\",\"count\":\"0\",\"description\":\"Quell the spirits.\"}]', 0, '[{\"item_id\":\"131\",\"chance\":\"100\",\"quantity\":\"3\"}]', '26:10:1'),
                (153, 'Eliminate Cultist Scouts', 3, 7, '[{\"action_type\":\"search\",\"action_data\":\"0\",\"location_radius\":\"2\",\"count\":\"0\",\"description\":\"The mysterious group has taken an interest in Akuji&#039;s whereabouts. Search the surrounding area within [location_radius] squares.\"},{\"action_type\":\"combat\",\"action_data\":\"26\",\"location_radius\":\"0\",\"count\":\"0\",\"description\":\"Eliminate the Mysterious Shinobi.\"},{\"action_type\":\"search\",\"action_data\":\"0\",\"location_radius\":\"2\",\"count\":\"0\",\"description\":\"The mysterious group has taken an interest in Akuji&#039;s whereabouts. Search the surrounding area within [location_radius] squares.\"},{\"action_type\":\"combat\",\"action_data\":\"26\",\"location_radius\":\"0\",\"count\":\"0\",\"description\":\"Eliminate the Mysterious Shinobi.\"}]', 0, '[{\"item_id\":\"131\",\"chance\":\"100\",\"quantity\":\"4\"}]', '26:10:1'),
                (154, 'Investigate Ritual Site', 4, 7, '[{\"action_type\":\"travel\",\"action_data\":\"10:1:1\",\"location_radius\":\"0\",\"count\":\"0\",\"description\":\"Head to ritual site at [action_data], where the Shadow Manipulator was defeated during the Fesitval of Shadows.\"},{\"action_type\":\"search\",\"action_data\":\"0\",\"location_radius\":\"1\",\"count\":\"0\",\"description\":\"Search the surounding 1 squares and draw out the remaining shadow using the relic given to you by Akuji.\"},{\"action_type\":\"combat\",\"action_data\":\"27\",\"location_radius\":\"0\",\"count\":\"0\",\"description\":\"Defeat the Shadow Oni!\"}]', 0, '[{\"item_id\":\"131\",\"chance\":\"100\",\"quantity\":\"5\"}]', '26:10:1');
        ");

        // Insert into items table
        $this->execute("
            INSERT INTO `items` (`item_id`, `name`, `description`, `rank`, `purchase_type`, `purchase_cost`, `use_type`, `effect`, `effect_amount`) VALUES
                (132, 'Ayakashi&#039;s Favor', 'Faction Currency', 0, 2, 0, 5, 'unknown', 0);
        ");

        // Commit Transaction
        $this->execute("COMMIT");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Modify missions table
        $this->execute("
            ALTER TABLE `missions`
                DROP `custom_start_location`;
        ");

        // Delete from missions table
        $this->execute("
            DELETE FROM `missions`
                WHERE `mission_id` IN (151, 152, 153, 154);
        ");

        // Delete from items table
        $this->execute("
            DELETE FROM `items`
                WHERE `item_id` IN (131, 132);
        ");

        // Commit Transaction
        $this->execute("COMMIT");
    }
}
