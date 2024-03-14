<?php
class ForbiddenSeal {
    const ONE_MEGABYTE = 1024 ** 2;
    const SECONDS_IN_DAY = 86400;
    const LOGOUT_LIMIT = System::LOGOUT_LIMIT;

    /** NOTE: No need to duplicate colors, arrays are merged based on seal level **/
    const DEFAULT_NAME_COLORS = [
        'default' => 'normalUser'
    ];
    const T1_NAME_COLORS = [
        'blue' => 'blue',
        'pink' => 'pink',
    ];
    const T2_NAME_COLORS = [
        'orange' => 'orange',
    ];
    const T3_NAME_COLORS = [
        'platinum' => 'platinum',
    ];
    const AVATAR_FILE_SIZES = [
        0 => self::ONE_MEGABYTE,
        1 => 1.5 * self::ONE_MEGABYTE, // Changed from 1 => 1.5 during refactor
        2 => 2 * self::ONE_MEGABYTE,
        3 => 2.5 * self::ONE_MEGABYTE, // Changed from 2 => 2.5 during refactor
    ];
    const AVATAR_SIZES = [
        0 => 125,
        1 => 200,
        2 => 200,
        3 => 200,
    ];
    /** NOTE: No need to duplicate frames, arrays are merged based on seal level **/
    const DEFAULT_AVATAR_FRAMES = [
        'avy_none' => 'none',
        'avy_round' => 'circle',
        'avy_four-point' => 'square',
    ];
    const T1_AVATAR_FRAMES = [
        'avy_three-point' => 'triangle',
        'avy_three-point-inverted' => 'triangle inverted',
        'avy_four-point-90' => 'diamond',
        'avy_four-point-oblique' => 'oblique',
        'avy_five-point' => 'pentagon',
        'avy_six-point' => 'hexagon',
        'avy_six-point-long' => 'crystal',
        'avy_eight-point' => 'octagon',
        'avy_eight-point-wide' => 'octagon wide',
        'avy_nine-point' => 'nonagon',
        'avy_twelve-point' => 'cross',
    ];
    const T2_AVATAR_FRAMES = [];
    const T3_AVATAR_FRAMES = [];
    const JOURNAL_SIZES = [
        0 => 1000,
        1 => 2000,
        2 => 2500,
        3 => 3500,
    ];
    const JOURNAL_IMG_SIZES = [
        0 => [
            'x' => 300,
            'y' => 200,
        ],
        1 => [
            'x' => 500,
            'y' => 500,
        ],
        2 => [
            'x' => 500,
            'y' => 500,
        ],
        3 => [
            'x' => 600,
            'y' => 600,
        ],
    ];
    const JOURNAL_EMBED_YOUTUBE = [
        0 => false,
        1 => false,
        2 => false,
        3 => true,
    ];

    const INBOX_SIZES = [
        0 => Inbox::INBOX_SIZE,
        1 => Inbox::INBOX_SIZE + 25,
        2 => Inbox::INBOX_SIZE + 25,
        3 => Inbox::INBOX_SIZE + 50, // Changed from base +25 => base+50 during refactor
    ];
    const INBOX_MESSAGE_SIZE = [
        0 => Inbox::MAX_MESSAGE_LENGTH,
        1 => Inbox::MAX_MESSAGE_LENGTH * 1.5, // 1500 - same post refactor
        2 => Inbox::MAX_MESSAGE_LENGTH * 1.75, // 1750 - changed +25% during refactor
        3 => Inbox::MAX_MESSAGE_LENGTH * 2, // 2000 - changed +50% during refactor
    ];
    const CHAT_POST_SIZES = [
        0 => 300,
        1 => 400,
        2 => 400,
        3 => 450, // Changed from 400 during refactor
    ];

    const REGEN_BOOST = [
        0 => 0,
        1 => 10,
        2 => 20,
        3 => 30,
    ];
    const STAT_TRANSFER_BOOST = [
        0 => 0,
        1 => 5,
        2 => 10,
        3 => 20,
    ];
    const EXTRA_STAT_TRANSFER_PER_AK = [
        0 => 0,
        1 => 50,
        2 => 100,
        3 => 150,
    ];
    const FREE_STAT_TRANSFER_BONUS = [
        0 => 0,
        1 => 25,
        2 => 50,
        3 => 100,
    ];
    const STAT_TRAINING_BUFFS = [
        0 => [
            'long' => [
                'time' => 1,
                'gains' => 1,
            ],
            'extended' => [
                'time' => 1,
                'gains' => 1,
            ],
        ],
        1 => [
            'long' => [
                'time' => 1,
                'gains' => 1,
            ],
            'extended' => [
                'time' => 1,
                'gains' => 1,
            ],
        ],
        2 => [
            'long' => [
                'time' => 1.5,
                'gains' => 2,
            ],
            'extended' => [
                'time' => 1.5,
                'gains' => 2,
            ],
        ],
        3=> [
            'long' => [
                'time' => 1.5,
                'gains' => 2.2,
            ],
            'extended' => [
                'time' => 2,
                'gains' => 3,
            ],
        ],
    ];
    const BONUS_PVE_REP = [
        0 => 0,
        1 => 0,
        2 => 1,
        3 => 1,
    ];
    const BONUS_EQUIPS = [
        0 => [
            'jutsu' => 0,
            'weapon' => 0,
            'armor' => 0,
        ],
        1 => [
            'jutsu' => 1,
            'weapon' => 1,
            'armor' => 1,
        ],
        2 => [
            'jutsu' => 1,
            'weapon' => 1,
            'armor' => 1,
        ],
        3 => [
            'jutsu' => 1,
            'weapon' => 1,
            'armor' => 1,
        ],
    ];
    const MAX_BATTlE_HISTORY = [
        0 => 0,
        1 => 10,
        2 => 20,
        3 => 50,
    ];

    public static int $STAFF_SEAL_LEVEL = 2;
    public static array $forbidden_seal_names = [
        0 => '',
        1 => 'Twin Sparrow Seal',
        2 => 'Four Dragon Seal',
        3 => 'Eight Deities Seal',
    ];

    public function __construct(
        public System $system,
        public int $level,
        public string $name,
        public ?int $seal_end_time,
        public int $seal_time_remaining,
        public int $logout_timer,

        // Player customizations
        public array $name_colors,
        public int $avatar_size,
        public int $avatar_filesize,
        public array $avatar_styles,

        // Social benefits
        public int $inbox_size,
        public int $journal_size,
        public int $journal_image_x,
        public int $journal_image_y,
        public bool $journal_youtube_embed,
        public int $chat_post_size,
        public int $pm_size,

        // Training/Transfer & Regen Boosts
        public int $regen_boost,
        public int $stat_transfer_boost,
        public int $extra_stat_transfer_points_per_ak,
        public int $free_transfer_bonus,
        public float $long_training_time,
        public float $long_training_gains,
        public float $extended_training_time,
        public float $extended_training_gains,

        // Misc. benefits
        public int $bonus_pve_reputation,
        public int $extra_jutsu_equips,
        public int $extra_armor_equips,
        public int $extra_weapon_equips,
        public int $max_battle_history_view
    ){}

    public function checkExpiration(): void {
        if($this->level > 0 && time() > $this->seal_end_time) {
            // Todo: This is for display purposes only. Make this a notification
            $this->system->message("Your {$this->name} has expired!");
        }
    }
    public function addSeal(int $seal_level, int $days_to_add): void {
        // Add time
        if($seal_level == $this->level) {
            $this->seal_end_time += $days_to_add * self::SECONDS_IN_DAY;
            $this->seal_time_remaining += $days_to_add * self::SECONDS_IN_DAY;
        }
        // Overwrite seal
        else {
            $this->level = $seal_level;
            $this->name = self::$forbidden_seal_names[$seal_level];
            $this->seal_end_time = time() + ($days_to_add * self::SECONDS_IN_DAY);
            $this->seal_time_remaining = $days_to_add * self::SECONDS_IN_DAY;
        }
    }
    public function dbEncode(): string {
        return match($this->level) {
            0 => "",
            default => json_encode(array('level' => $this->level, 'time' => $this->seal_end_time))
        };
    }
    public static function getDimensionDisplay(int $val_1, ?int $val_2 = null): string {
        if(is_null($val_2)) {
            $val_2 = $val_1;
        }
        return $val_1 . 'x' . $val_2;
    }
    public static function getAvyFrames(int $seal_level): array {
        $frames = self::DEFAULT_AVATAR_FRAMES;
        if($seal_level >= 1) {
            $frames = array_merge($frames, self::T1_AVATAR_FRAMES);
        }
        if($seal_level >= 2) {
            $frames = array_merge($frames, self::T2_AVATAR_FRAMES);
        }
        if($seal_level >= 3) {
            $frames = array_merge($frames, self::T3_AVATAR_FRAMES);
        }
        return $frames;
    }
    public static function getSealLevelNameColors(int $seal_level): array {
        $name_colors = self::DEFAULT_NAME_COLORS;
        if($seal_level >= 1) {
            $name_colors = array_merge($name_colors, self::T1_NAME_COLORS);
        }
        if($seal_level >= 2) {
            $name_colors = array_merge($name_colors, self::T2_NAME_COLORS);
        }
        if($seal_level >= 3) {
            $name_colors = array_merge($name_colors, self::T3_NAME_COLORS);
        }
        return $name_colors;
    }

    public static function fromDb(System $system, int $seal_level, ?int $seal_end_time): ForbiddenSeal {
        if(!isset(self::$forbidden_seal_names[$seal_level])) {
            throw new RuntimeException("Invalid seal level!");
        }

        // Seal expired
        if(!is_null($seal_end_time) && $seal_end_time < time()) {
            $seal_level = 0;
        }

        return new ForbiddenSeal(
            system: $system,
            level: $seal_level,
            name: self::$forbidden_seal_names[$seal_level],
            seal_end_time: $seal_end_time,
            seal_time_remaining: $seal_end_time - time(),
            logout_timer: self::LOGOUT_LIMIT, // TODO: Fix this to actually work

            name_colors: self::getSealLevelNameColors(seal_level: $seal_level),
            avatar_size: self::AVATAR_SIZES[$seal_level],
            avatar_filesize: self::AVATAR_FILE_SIZES[$seal_level],
            avatar_styles: self::getAvyFrames(seal_level: $seal_level),

            inbox_size: self::INBOX_SIZES[$seal_level],
            journal_size: self::JOURNAL_SIZES[$seal_level],
            journal_image_x: self::JOURNAL_IMG_SIZES[$seal_level]['x'],
            journal_image_y: self::JOURNAL_IMG_SIZES[$seal_level]['y'],
            journal_youtube_embed: self::JOURNAL_EMBED_YOUTUBE[$seal_level],
            chat_post_size: self::CHAT_POST_SIZES[$seal_level],
            pm_size: self::INBOX_MESSAGE_SIZE[$seal_level],

            regen_boost: self::REGEN_BOOST[$seal_level],
            stat_transfer_boost: self::STAT_TRANSFER_BOOST[$seal_level],
            extra_stat_transfer_points_per_ak: self::EXTRA_STAT_TRANSFER_PER_AK[$seal_level],
            free_transfer_bonus: self::FREE_STAT_TRANSFER_BONUS[$seal_level],
            long_training_time: self::STAT_TRAINING_BUFFS[$seal_level]['long']['time'],
            long_training_gains: self::STAT_TRAINING_BUFFS[$seal_level]['long']['gains'],
            extended_training_time: self::STAT_TRAINING_BUFFS[$seal_level]['extended']['time'],
            extended_training_gains: self::STAT_TRAINING_BUFFS[$seal_level]['extended']['gains'],

            bonus_pve_reputation: self::BONUS_PVE_REP[$seal_level],
            extra_jutsu_equips: self::BONUS_EQUIPS[$seal_level]['jutsu'],
            extra_armor_equips: self::BONUS_EQUIPS[$seal_level]['armor'],
            extra_weapon_equips: self::BONUS_EQUIPS[$seal_level]['weapon'],
            max_battle_history_view: self::MAX_BATTlE_HISTORY[$seal_level]
        );
    }
}