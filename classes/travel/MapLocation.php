<?php

class MapLocation {

    public int $location_id = 0;
    public string $name;
    public int $map_id;
    public int $x;
    public int $y;
    public string $background_image;
    public string $background_color;
    public string $objective_image = "";
    public string $action_url = "";
    public string $action_message = "";
    public ?int $objective_health = null;
    public ?int $objective_max_health = null;
    public ?string $objective_type = null;
    public int $pvp_allowed;
    public int $ai_allowed;
    public int $regen;
    public function __construct(array $location_data) {
        foreach ($location_data as $key => $value) {
            $this->$key = $value;
        }
    }
}