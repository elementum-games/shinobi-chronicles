<?php

class Route {
    const LOCATION_ACCESS_INCLUSIVE = true; // only given locations are allowed, default
    const LOCATION_ACCESS_EXCLUSIVE = false; // all but given locations are allowed

    const MENU_USER = 'user';
    const MENU_ACTIVITY = 'activity';
    const MENU_VILLAGE = 'village';
    const MENU_CONDITIONAL = 'conditional';
    const MENU_NONE = 'none';

    public string $file_name;
    public string $title;
    public string $function_name;
    public string $menu;

    public ?int $battle_type = null;
    public ?int $min_rank = null;

    public bool $battle_ok = true;
    public bool $survival_mission_ok = true;
    public bool $challenge_lock_ok = true;

    public ?Closure $user_check = null;
    public bool $dev_only = false;

    public array $allowed_location_types = [];

    public function __construct(
        string $file_name,
        string $title,
        string $function_name,
        string $menu,
        ?int $battle_type = null,
        ?int $min_rank = null,
        bool $battle_ok = true,
        bool $survival_mission_ok = true,
        bool $challenge_lock_ok = true,
        ?Closure $user_check = null,
        bool $dev_only = false,
        array $allowed_location_types = [],
        bool $location_access_mode = self::LOCATION_ACCESS_INCLUSIVE,
    ) {
        $this->file_name = $file_name;
        $this->title = $title;
        $this->function_name = $function_name;
        $this->menu = $menu;
        $this->battle_type = $battle_type;
        $this->min_rank = $min_rank;
        $this->battle_ok = $battle_ok;
        $this->survival_mission_ok = $survival_mission_ok;
        $this->challenge_lock_ok = $challenge_lock_ok;
        $this->user_check = $user_check;
        $this->dev_only = $dev_only;

        // default access to all locations
        $this->allowed_location_types = [
            TravelManager::LOCATION_TYPE_DEFAULT => true,
            TravelManager::LOCATION_TYPE_HOME_VILLAGE => true,
            TravelManager::LOCATION_TYPE_ALLY_VILLAGE => true,
            TravelManager::LOCATION_TYPE_ENEMY_VILLAGE => true,
            TravelManager::LOCATION_TYPE_ABYSS => true,
            TravelManager::LOCATION_TYPE_COLOSSEUM => true,
            TravelManager::LOCATION_TYPE_TOWN => true,
            TravelManager::LOCATION_TYPE_CASTLE => true,
        ];

        if (count($allowed_location_types) > 0) {
            if ($location_access_mode == self::LOCATION_ACCESS_INCLUSIVE) {
                foreach ($this->allowed_location_types as $key => $value) {
                    $this->allowed_location_types[$key] = false;
                }
                foreach ($allowed_location_types as $location) {
                    $this->allowed_location_types[$location] = true;
                }
            } else {
                foreach ($allowed_location_types as $location) {
                    $this->allowed_location_types[$location] = false;
                }
            }
        }
    }
}