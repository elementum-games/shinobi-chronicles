<?php
class RouteV2 {
    const ROUTE_PAGE_KEY = "page"; // Reserved key for navigating between pages (i.e. disallows use of ?id=profile&page=send_money)
    const LOCATION_ACCESS_INCLUSIVE = true; // only given locations are allowed, default
    const LOCATION_ACCESS_EXCLUSIVE = false; // all but given locations are allowed

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

        public ?int $battle_type,
        public ?int $min_rank,

        public bool $battle_ok,
        public bool $survival_mission_ok,
        public bool $challenge_lock_ok,

        public ?Closure $user_check,
        public bool $dev_only,

        public array $allowed_location_types
    ){}

    public static function load(
        // Required members
        string $file_name,
        string $title,
        string $function_name,

        // Optional members
        string $menu = self::MENU_NONE,
        ?int $battle_type = null,
        ?int $min_rank = null,
        bool $battle_ok = null,
        bool $survival_mission_ok = null,
        bool $challenge_lock_ok = null,
        ?Closure $user_check = null,
        bool $dev_only = null,
        array $allowed_location_types = []
    ): RouteV2 {
        return new RouteV2(
            file_name: $file_name,
            title: $title,
            function_name: $function_name,
            
            menu: $menu,
            battle_type: $battle_type,
            min_rank: $min_rank,
            battle_ok: $battle_ok,
            survival_mission_ok: $survival_mission_ok,
            challenge_lock_ok: $challenge_lock_ok,
            user_check: $user_check,
            dev_only: $dev_only,
            allowed_location_types: $allowed_location_types
        );
    }
}