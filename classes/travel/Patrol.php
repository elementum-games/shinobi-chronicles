<?php

class Patrol {

    const CARAVAN_TYPE_RESOURCE = 'resource';
    const CARAVAN_TYPE_SUPPLY = 'supply';
    const PATROL_TYPE_CARAVAN = 'caravan';
    const PATROL_TYPE_PATROL = 'patrol';

    public int $id;
    public int $start_time;
    public ?int $travel_time;
    public ?int $travel_interval;
    public int $region_id;
    public int $current_x;
    public int $current_y;
    public int $map_id = 1;
    public string $name = "Patrol";
    public int $level = 100;
    public int $village_id;
    public ?int $ai_id = null;
    public string $patrol_type;
    public string $alignment;
    public ?int $tier = null;
    const DESTINATION_BUFFER_MS = 5000; // duration non-looped patrols should appear at their destination
    public function __construct(array $row, string $patrol_type) {
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        $this->patrol_type = $patrol_type;
    }
    public function setLocation(System $system) {
        $route_locations = [];
        $loop = false;
        switch ($this->patrol_type) {
            case self::PATROL_TYPE_PATROL:
                $loop = true;
                // patrols loop between each location within their region
                $result = $system->db->query("SELECT `x`, `y`, `type` FROM `region_locations` WHERE `region_id` = {$this->region_id}");
                $result = $system->db->fetch_all($result);

                foreach ($result as $point) {
                    if ($point['type'] == 'castle') {
                        $route_locations[] = ['x' => $point['x'], 'y' => $point['y']];
                    }
                }
                foreach ($result as $point) {
                    if ($point['type'] == 'village') {
                        $route_locations[] = ['x' => $point['x'], 'y' => $point['y']];
                    }
                }
                break;
            case self::PATROL_TYPE_CARAVAN:
                $loop = false;
                // get village location
                $village_result = $system->db->query("SELECT `x`, `y` FROM `maps_locations`
                    INNER JOIN `villages` ON `villages`.`map_location_id` = `maps_locations`.`location_id`
                    WHERE `village_id` = {$this->village_id} LIMIT 1");
                $village_result = $system->db->fetch($village_result);
                // get castle location
                $castle_result = $system->db->query("SELECT `x`, `y` FROM `region_locations`
                    WHERE `region_id` = {$this->region_id} AND `type` = 'castle' LIMIT 1");
                $castle_result = $system->db->fetch($castle_result);
                switch ($this->caravan_type) {
                    case self::CARAVAN_TYPE_RESOURCE:
                        // move from castle -> village
                        $route_locations[] = ['x' => $castle_result['x'], 'y' => $castle_result['y']];
                        $route_locations[] = ['x' => $village_result['x'], 'y' => $village_result['y']];
                        break;
                    case self::CARAVAN_TYPE_SUPPLY:
                        // move from village -> castle
                        $route_locations[] = ['x' => $village_result['x'], 'y' => $village_result['y']];
                        $route_locations[] = ['x' => $castle_result['x'], 'y' => $castle_result['y']];
                        break;
                }
                break;
        }

        // if total travel time is set, we use given duration
        if (!empty($this->travel_time)) {
            $position = $this->calculatePositionNormalized(time() * 1000, $this->start_time * 1000, $this->travel_time, $route_locations, $loop);
        }
        // if travel interval is set, we can calculate the total time based on the distance and interval
        else if (!empty($this->travel_interval)) {
            $loop_duration = $this->totalIntermediatePoints($route_locations) * $this->travel_interval;
            $position = $this->calculatePositionNormalized(time() * 1000, $this->start_time * 1000, $loop_duration, $route_locations, $loop);
        }
        else {
            throw new RuntimeException("Invalid Patrol Configuration");
        }
        $this->current_x = $position['x'];
        $this->current_y = $position['y'];
    }

    /* Piecewise Linear Interpolation Formula
    function calculatePosition($t, $T_start, $T_cycle, $points)
    {
        $n = count($points);
        $T_norm = (($t - $T_start) % $T_cycle) / $T_cycle;
        $segmentLength = 1 / $n;

        for ($i = 0; $i < $n; $i++) {
            $t1 = $i * $segmentLength;
            $t2 = ($i + 1) * $segmentLength;

            if ($T_norm >= $t1 && $T_norm <= $t2) {
                $ratio = ($T_norm - $t1) / ($t2 - $t1);
                $nextIndex = ($i + 1) % $n; // Circular loop back to the first point after the last
                $x = $points[$i]['x'] + $ratio * ($points[$nextIndex]['x'] - $points[$i]['x']);
                $y = $points[$i]['y'] + $ratio * ($points[$nextIndex]['y'] - $points[$i]['y']);

                // if adjacent to given point consider at that point
                if (abs($x - $points[$i]['x']) + abs($y - $points[$i]['y']) < 2) {
                    return ['x' => $points[$i]['x'], 'y' => $points[$i]['y']];
                }

                return ['x' => $x, 'y' => $y];
            }
        }

        return ['x' => $points[0]['x'], 'y' => $points[0]['y']];
    }*/

    /* Reverses the formula to generate a new start time for a point - this is used to correct the position if the patrol is stationary for a time
    function findNewStartTime($t, $T_cycle, $points, $desiredPoint)
    {
        $n = count($points);
        $segmentLength = 1 / $n;

        for ($i = 0; $i < $n; $i++) {
            $nextIndex = ($i + 1) % $n;
            $dx = $points[$nextIndex]['x'] - $points[$i]['x'];
            $dy = $points[$nextIndex]['y'] - $points[$i]['y'];

            $ratio = (($desiredPoint['x'] - $points[$i]['x']) / $dx + ($desiredPoint['y'] - $points[$i]['y']) / $dy) / 2;
            if ($ratio >= 0 && $ratio <= 1) {
                $T_norm = $i * $segmentLength + $ratio * $segmentLength;
                $T_start = $t - $T_norm * $T_cycle;
                return $T_start;
            }
        }

        return null;
    }*/

    // Used to determine the total points in the path for speed calculations
    private function totalIntermediatePoints($points, $step_size = 1)
    {
        $total_points = 0;
        $n = count($points);

        for ($i = 0; $i < $n; $i++) {
            $nextIndex = ($i + 1) % $n;
            $d = $this->distance($points[$i]['x'], $points[$i]['y'], $points[$nextIndex]['x'], $points[$nextIndex]['y']);
            $total_points += floor($d / $step_size) - 1; // -1 because we don't count the original points as intermediate
        }

        return $total_points;
    }

    // Helper
    private function distance($x1, $y1, $x2, $y2)
    {
        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
    }

    /* Variation of the original formula that uses an equal time for each segment between given points.
     * $t: The current time.
     * $T_start: The start time of the cycle.
     * $T_cycle: The total duration of one cycle.
     * $points: An array of points (coordinates) that the object should pass through.
     * $loop: A boolean variable indicating whether the path should loop back to the start or not.
     */
    function calculatePositionNormalized($t, $T_start, $T_cycle, $points, $loop = true)
    {
        // Variable to determine if the movement is in a loop or not. 0 for looping, 1 for not looping.
        $loopFactor = $loop ? 0 : 1;

        // Total number of points in the $points array.
        $n = count($points);

        // Initialize variables to store the total length of the path, the lengths of individual segments, and the time allocated for each segment.
        $totalLength = 0;
        $segmentLengths = [];
        $segmentTimes = [];

        // If not looping and time complete, set to final point.
        if (!$loop && $T_start + $T_cycle <= $t) {
            return ['x' => $points[$n - 1]['x'], 'y' => $points[$n - 1]['y']];
        }

        // Calculate the total length and individual segment lengths.
        // If $loop is false, excludes the final segment (connecting the final point to the first point.
        for ($i = 0; $i < $n - $loopFactor; $i++) {
            $nextIndex = ($i + 1) % $n;
            $length = $this->calculateSegmentLength($points[$i], $points[$nextIndex]);
            $segmentLengths[] = $length;
            $totalLength += $length;
        }

        // Calculate the time to allocate for each segment based on its length.
        for ($i = 0; $i < $n - $loopFactor; $i++) {
            $segmentTimes[$i] = ($segmentLengths[$i] / $totalLength) * $T_cycle;
        }

        // Normalize the time within the cycle duration.
        $T_norm = ($t - $T_start) % $T_cycle;
        // Keeps track of the accumulated time looping through each segment.
        $elapsedTime = 0;

        // Calculate the current position.
        // The last loop calculates the object's position at the current time $t, based on which segment it's supposed to be in.
        for ($i = 0; $i < $n - $loopFactor; $i++) {
            $nextIndex = ($i + 1) % $n;

            if ($T_norm >= $elapsedTime && $T_norm <= ($elapsedTime + $segmentTimes[$i])) {
                $ratio = ($T_norm - $elapsedTime) / $segmentTimes[$i];
                $x = $points[$i]['x'] + $ratio * ($points[$nextIndex]['x'] - $points[$i]['x']);
                $y = $points[$i]['y'] + $ratio * ($points[$nextIndex]['y'] - $points[$i]['y']);

                // if adjacent to given point consider at that point
                if (abs($x - $points[$i]['x']) + abs($y - $points[$i]['y']) < 2) {
                    return ['x' => $points[$i]['x'], 'y' => $points[$i]['y']];
                }

                return ['x' => $x, 'y' => $y];
            }

            $elapsedTime += $segmentTimes[$i];
        }
        
        // Fail-safe if no conditions met, returns first point.
        return ['x' => $points[0]['x'], 'y' => $points[0]['y']];
    }

    // Helper
    private function calculateSegmentLength($point1, $point2)
    {
        return sqrt(pow($point2['x'] - $point1['x'], 2) + pow($point2['y'] - $point1['y'], 2));
    }

    public function setAlignment(User $user) {
        if ($user->village->village_id == $this->village_id) {
            $this->alignment = 'Ally';
        }
        else {
            $alignment = $user->village->relations[$this->village_id]->relation_type;
            switch ($alignment) {
                case VillageRelation::RELATION_NEUTRAL:
                    $this->alignment = 'Neutral';
                    break;
                case VillageRelation::RELATION_ALLIANCE:
                    $this->alignment = 'Ally';
                    break;
                case VillageRelation::RELATION_WAR:
                    $this->alignment = 'Enemy';
                    break;
            }
        }
        return;
    }
}