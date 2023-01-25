<?php

class Travel {

    const TRAVEL_DELAY_MOVEMENT = 600; // milliseconds
    const TRAVEL_DELAY_PVP = 5; // seconds
    const TRAVEL_DELAY_AI = 10; // seconds
    const TRAVEL_DELAY_DEATH = 15; // seconds
    const HOME_VILLAGE_COLOR = 'FFEF30';

    public function __construct() {
    }

    public static function getNewMovementValues(string $direction, int $current_x, int $current_y): array {
        $new_x = $current_x;
        $new_y = $current_y;
        switch($direction) {
            case 'move_north':
                $new_y = $current_y - 1;
                break;
            case 'move_south':
                $new_y = $current_y + 1;
                break;
            case 'move_west':
                $new_x = $current_x - 1;
                break;
            case 'move_east':
                $new_x = $current_x + 1;
                break;
            case 'move_northwest':
                $new_x = $current_x - 1;
                $new_y = $current_y - 1;
                break;
            case 'move_northeast':
                $new_x = $current_x + 1;
                $new_y = $current_y - 1;
                break;
            case 'move_southwest':
                $new_x = $current_x - 1;
                $new_y = $current_y + 1;
                break;
            case 'move_southeast':
                $new_x = $current_x + 1;
                $new_y = $current_y + 1;
                break;
        }

        return ['x' => $new_x, 'y' => $new_y];
    }

    public static function checkPVPDelay(int $last_pvp): int {
        $diff = time() - $last_pvp;
        return self::TRAVEL_DELAY_PVP - $diff;
    }

    public static function checkAIDelay(int $last_ai): int {
        $diff = time() - $last_ai;
        return self::TRAVEL_DELAY_AI - $diff;
    }

    public static function checkDeathDelay(int $last_death): int {
        $diff = time() - $last_death;
        return self::TRAVEL_DELAY_DEATH - $diff;
    }

    public static function checkMovementDelay(float $last_movement): int {
        $diff = floor(microtime(true) * 1000) - $last_movement;
        return self::TRAVEL_DELAY_MOVEMENT - $diff;
    }

    public static function getLocation(System $system, string $x, string $y, string $z): array {
        $result = $system->query("SELECT * FROM `maps_locations` WHERE `x`='{$x}' AND `y`='{$y} 'AND `map_id`='{$z}' LIMIT 1");
        return $system->db_last_num_rows ? $system->db_fetch($result) : [];
    }

    public static function getPortalData(System $system, int $portal_id): array {
        $result = $system->query("SELECT * FROM `maps_portals` WHERE `portal_id`={$portal_id} AND `active`=1");
        if ($system->db_last_num_rows < 1) {
            return [];
        }
        return $system->db_fetch($result);
    }

    public static function getMapData(System $system, $map_id): array {
        $result = $system->query("SELECT * FROM `maps` WHERE `map_id`={$map_id}");
        if (!$system->db_last_num_rows) {
            return [];
        }
        $map_data = $system->db_fetch($result);
        // location data
        $result = $system->query("SELECT * FROM `maps_locations` WHERE `map_id`={$map_data['map_id']}");
        $location_data = $system->db_fetch_all($result);
        $map_data['locations'] = $location_data;
        // portal data
        $result = $system->query("SELECT * FROM `maps_portals` WHERE `from_id`={$map_data['map_id']}");
        $portal_data = $system->db_fetch_all($result);
        $map_data['portals'] = $portal_data;

        return $map_data;
    }
}