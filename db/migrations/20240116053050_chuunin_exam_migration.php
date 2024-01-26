<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChuuninExamMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            INSERT INTO `ai_opponents` (`ai_id`, `rank`, `money`, `name`, `max_health`, `level`, `ninjutsu_skill`, `genjutsu_skill`, `taijutsu_skill`, `cast_speed`, `speed`, `strength`, `intelligence`, `willpower`, `moves`, `shop_jutsu`, `shop_jutsu_priority`, `battle_iq`, `scaling`, `difficulty_level`, `arena_enabled`, `is_patrol`, `avatar_link`) VALUES
            (163, 2, 0, 'Enraged Serpent', 1.00, 25, 0.30, 0.30, 0.30, 0.50, 0.50, 0.00, 0.00, 0.00, '[{\"name\":\"Contrict\",\"battle_text\":\"[opponent] wraps itself tightly around [player], slowly suffocating the life out of its victim.\",\"power\":\"2.5\",\"cooldown\":\"0\",\"jutsu_type\":\"taijutsu\",\"use_type\":\"physical\",\"element\":\"None\",\"effect\":\"evasion_nerf\",\"effect_amount\":\"17\",\"effect_length\":\"1\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"},{\"name\":\"Vemonous Bite\",\"battle_text\":\"[opponent] aggressively launches itself at [player], digging its fangs deep in [player] as a vile substance slowly spreads throughout their body.\",\"power\":\"1\",\"cooldown\":\"3\",\"jutsu_type\":\"ninjutsu\",\"use_type\":\"physical\",\"element\":\"None\",\"effect\":\"residual_damage\",\"effect_amount\":\"42\",\"effect_length\":\"3\",\"effect2\":\"offense_nerf\",\"effect2_amount\":\"17\",\"effect2_length\":\"3\"}]', '', '', 0, 0, 'none', 0, 0, 'images/ai_avatars/Snake.png');
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("");
    }
}
