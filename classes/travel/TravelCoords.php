<?php

class TravelCoords {
    const DIRECTION_NORTH = "north";
    const DIRECTION_NORTHEAST = "northeast";
    const DIRECTION_EAST = "east";
    const DIRECTION_SOUTHEAST = "southeast";
    const DIRECTION_SOUTH = "south";
    const DIRECTION_SOUTHWEST = "southwest";
    const DIRECTION_WEST = "west";
    const DIRECTION_NORTHWEST = "northwest";
    
    public int $x;
    public int $y;
    public int $map_id;


    public function __construct(int $x, int $y, int $map_id) {
        $this->x = $x;
        $this->y = $y;
        $this->map_id = $map_id;
    }

    public function toString(): string {
        return $this->x . ':' . $this->y . ':' . $this->map_id;
    }

    public function displayString(): string {
        return $this->x . ':' . $this->y;
    }

    public function distanceDifference(TravelCoords $target_coords): int {
        $diff_x = abs($this->x - $target_coords->x);
        $diff_y = abs($this->y - $target_coords->y);
        return max($diff_x, $diff_y);
    }

    public function directionToTarget(TravelCoords $target_coords): string {
        $diff_x = ($target_coords->x - $this->x);
        $diff_y = ($target_coords->y - $this->y);

        if ($diff_x != 0 || $diff_y != 0) {
            $angle = atan2($diff_y, $diff_x);
            $angle_degrees = rad2deg($angle);
            $angle_degrees = fmod(($angle_degrees + 450), 360);
            $directions = [
                self::DIRECTION_NORTH,
                self::DIRECTION_NORTHEAST,
                self::DIRECTION_EAST,
                self::DIRECTION_SOUTHEAST,
                self::DIRECTION_SOUTH,
                self::DIRECTION_SOUTHWEST,
                self::DIRECTION_WEST,
                self::DIRECTION_NORTHWEST
            ];
            $index = round($angle_degrees / (360 / count($directions)));

            return $directions[$index % count($directions)];
        }

        return "none";
    }

    public function equals(TravelCoords $comp_coords): bool {
        return $this->x === $comp_coords->x && $this->y === $comp_coords->y && $this->map_id === $comp_coords->map_id;
    }

    public static function fromDbString(string $db_string): TravelCoords {
        $coords_arr = explode(':', $db_string);
        return new TravelCoords($coords_arr[0], $coords_arr[1], $coords_arr[2]);
    }
}