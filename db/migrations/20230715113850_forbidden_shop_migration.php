<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ForbiddenShopMigration extends AbstractMigration
{
    public function up()
    {
        // Insert into MapLocations
        $this->execute("
            INSERT INTO `maps_locations` (`name`, `map_id`, `x`, `y`, `background_image`, `background_color`, `pvp_allowed`, `ai_allowed`, `regen`) VALUES
                ('Ayakashi\'s Abyss', 1, 26, 10, '/images/map/locations/snake.png', '4000c67E', 0, 0, 50);
        ");

        // Insert into Jutsu
        $this->execute("
            INSERT INTO `jutsu` (`name`, `jutsu_type`, `rank`, `power`, `range`, `hand_seals`, `element`, `parent_jutsu`, `purchase_type`, `purchase_cost`, `use_cost`, `use_type`, `target_type`, `cooldown`, `battle_text`, `description`, `effect`, `effect_amount`, `effect_length`) VALUES
                ('Umbral Reave', 'taijutsu', 2, 1.4, 1, '', 'None', 0, 5, 0, 25, 'physical', 'fighter_id', 4, '[player] weaves their chakra into a blade of darkness and launches a relentless assault at [opponent], the final blow leaving only the faintest of cuts before [opponent]&#039;s body collapses moments later.', 'A technique that severs the target&#039;s chakra pathways with a blade forged from shadow.', 'residual_damage', 83, 1),
                ('Phantasmal Spikestorm', 'genjutsu', 2, 1.4, 1, '6-1-9-8-2', 'None', 0, 5, 0, 25, 'projectile', 'tile', 4, 'The battlefield becomes a nightmarish web of intangible spikes, conjured by [player]&#039;s mastery over shadows, each phantom spike sending waves of agonizing pain through [opponent]&#039;s body.', 'Subjects the target to excruciating pain as their body is pierced by an endless array of shadow spikes.', 'residual_damage', 28, 4),
                ('Dark Twin Entanglement', 'ninjutsu', 2, 2.5, 1, '10-5-1-8-11', 'None', 0, 5, 0, 25, 'projectile', 'fighter_id', 6, '[player] creates an army of clones that burst into shadow upon destruction, binding [opponent] with an inescapable darkness.', 'A variant of the Shadow Clone Jutsu used exclusively by the Shadow Manipulator.', 'speed_nerf', 15, 3);
        ");
    }

    public function down()
    {
        // Delete from MapLocations
        $this->execute("
            DELETE FROM `maps_locations` WHERE `name` IN ('Ayakashi''s Abyss');
        ");

        // Delete from Jutsu
        $this->execute("
            DELETE FROM `jutsu` WHERE `name` IN ('Umbral Reave', 'Phantasmal Spikestorm', 'Dark Twin Entanglement');
        ");
    }
}
