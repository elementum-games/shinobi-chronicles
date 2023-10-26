<?php

require_once __DIR__ . "/RegionCoords.php";

class Region {
    public int $region_id = 0;
    public string $name;
    public array $coordinates = [];
    public array $vertices = [];
    public int $village;
    public string $color;

    const VILLAGE_COLORS = [
        0 => "rgba(0, 0, 0, 0)",
        1 => "rgba(10, 144, 35, 0.35)", // stone
        2 => "rgba(240, 163, 10, 0.35)", // cloud
        3 => "rgba(255, 0, 0, 0.35)", // leaf
        4 => "rgba(249, 105, 14, 0.35)", // sand
        5 => "rgba(61, 84, 255, 0.35)", // mist
        6 => "rgba(68, 4, 139, 0.35)", // dark/rain
    ];

    public static function fromDb(array $region_data, int $min_x = 0, int $min_y = 0, int $max_x = 84, int $max_y = 48, int $map_id = 1, bool $get_coordinates = true): Region {
        $new_region = new Region();
        $new_region->region_id = $region_data['region_id'];
        $new_region->name = $region_data['name'];
        $new_region->village = $region_data['village'];
        $new_region->color = self::VILLAGE_COLORS[$new_region->village];
        $new_region->vertices = json_decode($region_data['vertices']);
        // generate coordinate list
        if ($get_coordinates) {
            for ($i = $min_x; $i < $max_x; $i++) {
                for ($j = $min_y; $j < $max_y; $j++) {
                    $coord = new RegionCoords($i, $j, $map_id);
                    if (self::coordInRegion($coord, $new_region->vertices)) {
                        $coord->region_id = $new_region->region_id;
                        $coord->color = $new_region->color;
                        $new_region->coordinates[$i][$j] = $coord;
                    }
                }
            }
            // calculate borders
            for ($i = $min_x; $i < $max_x; $i++) {
                for ($j = $min_y; $j < $max_y; $j++) {
                    if (isset($new_region->coordinates[$i][$j])) {
                        if (!isset($new_region->coordinates[$i][$j - 1])) {
                            $new_region->coordinates[$i][$j]->border_top = true;
                        }
                        if (!isset($new_region->coordinates[$i][$j + 1])) {
                            $new_region->coordinates[$i][$j]->border_bottom = true;
                        }
                        if (!isset($new_region->coordinates[$i - 1][$j])) {
                            $new_region->coordinates[$i][$j]->border_left = true;
                        }
                        if (!isset($new_region->coordinates[$i + 1][$j])) {
                            $new_region->coordinates[$i][$j]->border_right = true;
                        }
                    }
                }
            }
        }
        return $new_region;
    }

    // ray-cast algorithm
    public static function coordInRegion(RegionCoords $coord, $polygon) {
        $x = $coord->x;
        $y = $coord->y-1;
        $numVertices = count($polygon);
        $inside = false;

        for ($i = 0, $j = $numVertices - 1; $i < $numVertices; $j = $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            $intersect = (($yi > $y) != ($yj > $y)) &&
                ($x <= ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}