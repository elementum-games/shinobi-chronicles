<?php

require_once __DIR__ . "/MapLocation.php";

class Travel {

    const TRAVEL_DELAY_MOVEMENT = 400; // milliseconds
    const TRAVEL_DELAY_PVP = 750; // milliseconds
    const TRAVEL_DELAY_AI = 500; // milliseconds
    const TRAVEL_DELAY_DEATH = 15000; // milliseconds
    const HOME_VILLAGE_COLOR = 'FFEF30';
    const PLAYER_ICON = '/images/ninja_head.png';
    const DEFAULT_MAP_ID = 1;

    public function __construct() {
    }

    public static function getNewMovementValues(string $direction, TravelCoords $current): TravelCoords {
        $new_x = $current->x;
        $new_y = $current->y;
        switch($direction) {
            case 'north':
                $new_y -= 1;
                break;
            case 'south':
                $new_y += 1;
                break;
            case 'west':
                $new_x -= 1;
                break;
            case 'east':
                $new_x += 1;
                break;
            case 'northwest':
                $new_x -= 1;
                $new_y -= 1;
                break;
            case 'northeast':
                $new_x += 1;
                $new_y -= 1;
                break;
            case 'southwest':
                $new_x -= 1;
                $new_y += 1;
                break;
            case 'southeast':
                $new_x += 1;
                $new_y += 1;
                break;
        }
        return new TravelCoords($new_x, $new_y, $current->map_id);
    }

    public static function checkPVPDelay(int $last_pvp_ms): int {
        $diff = System::currentTimeMs() - $last_pvp_ms;
        return self::TRAVEL_DELAY_PVP - $diff;
    }

    public static function checkAIDelay(int $last_ai_ms): int {
        $diff = System::currentTimeMs() - $last_ai_ms;
        return self::TRAVEL_DELAY_AI - $diff;
    }

    public static function checkDeathDelay(int $last_death_ms): int {
        $diff = System::currentTimeMs() - $last_death_ms;
        return self::TRAVEL_DELAY_DEATH - $diff;
    }

    public static function checkMovementDelay(float $last_movement_ms): int {
        $diff = System::currentTimeMs() - $last_movement_ms;
        return self::TRAVEL_DELAY_MOVEMENT - $diff;
    }

    public static function getLocation(System $system, string $x, string $y, string $z): MapLocation {
        $result = $system->query("SELECT * FROM `maps_locations` WHERE `x`='{$x}' AND `y`='{$y} 'AND `map_id`='{$z}' LIMIT 1");
        if ($system->db_last_num_rows) {
            return new MapLocation($system->db_fetch($result));
        } else {
            return new MapLocation([]);
        }
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
        return $system->db_fetch($result);
    }
}