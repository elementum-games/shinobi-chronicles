<?php

class TravelCoords {

    public int $x;
    public int $y;
    public int $map_id;


    public function __construct(int $x, int $y, int $map_id) {
        $this->x = $x;
        $this->y = $y;
        $this->map_id = $map_id;
    }

    public function fetchString(): string {
        return $this->x . ':' . $this->y . ':' . $this->map_id;
    }

    public function distanceDifference(TravelCoords $target_coords): int {
        $diff_x = abs($this->x - $target_coords->x);
        $diff_y = abs($this->y - $target_coords->y);
        return max($diff_x, $diff_y);
    }

    public function equals(TravelCoords $comp_coords): bool {
        return $this->x === $comp_coords->x && $this->y === $comp_coords->y && $this->map_id === $comp_coords->map_id;
    }

    public static function fromDbString(string $db_string): TravelCoords {
        $coords_arr = explode(':', $db_string);
        return new TravelCoords($coords_arr[0], $coords_arr[1], $coords_arr[2]);
    }

}