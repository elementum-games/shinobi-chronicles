<?php

class Route {
    const NOT_IN_VILLAGE = 0;
    const IN_VILLAGE_OKAY = 1;
    const ONLY_IN_VILLAGE = 2;

    const MENU_USER = 'user';
    const MENU_ACTIVITY = 'activity';
    const MENU_VILLAGE = 'village';
    const MENU_CONDITIONAL = 'conditional';
    const MENU_NONE = 'none';

    public function __construct(
        public string $file_name,
        public string $title,
        public string $function_name,
        public string $menu,

        public ?int $battle_type = null,
        public ?int $min_rank = null,

        public bool $ajax_ok = false,
        public bool $battle_ok = true,
        public int $village_ok = Route::IN_VILLAGE_OKAY,
        public bool $survival_mission_ok = true,

        public ?Closure $user_check = null,
    ) {}
}