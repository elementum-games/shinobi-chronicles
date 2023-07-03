<?php

class AchievementProgress {
    public function __construct(
        public ?int $id,
        public string $achievement_id,
        public int $user_id,
        public array $progress_data
    ) {}

    public static function fromDb($db_row): AchievementProgress {
        return new AchievementProgress(
            id: $db_row['id'],
            achievement_id: $db_row['achievement_id'],
            user_id: $db_row['user_id'],
            progress_data: json_decode($db_row['progress_data'], true),
        );
    }
}