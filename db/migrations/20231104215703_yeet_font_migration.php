<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class YeetFontMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Yeet the Font
            DELETE FROM `map_locations` WHERE `name` = 'Font of Vitality';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Unyeet the Font
            INSERT INTO `maps_locations` (`location_id`, `name`, `map_id`, `x`, `y`, `background_image`, `background_color`, `pvp_allowed`, `ai_allowed`, `regen`) VALUES
            (2, 'Font of Vitality', 1, 32, 9, '/images/map/locations/fontofvitality.png', '38FF007E', 1, 1, 100);
        ");
    }
}
