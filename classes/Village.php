<?php

require_once __DIR__ . '/SpecialMission.php';

class Village {
    public System $system;
    public TravelCoords $coords;

    public int $village_id;
    public string $name;
    public int $points;
    public int $leader;
    public int $map_location_id;
    public int $region_id;
    public string $kage_name;
    public array $resources = [];
    public array $relations = [];

    const KAGE_NAMES = [
        1 => 'Tsuchikage',
        2 => 'Raikage',
        3 => 'Hokage',
        4 => 'Kazekage',
        5 => 'Tsuchikage',
    ];

    // to-do: we should restructure how village data is being saved
    // player village should reference the village ID and this constructor should get row by ID
    public function __construct($system, $village = '', array $village_row = []) {
        $this->system = $system;
        // new constructor logic
        if (!empty($village_row)) {
            foreach ($village_row as $key => $value) {
                if ($key == 'resources') {
                    $this->$key = json_decode($value, true);
                } else {
                    $this->$key = $value;
                }
            }
            $this->kage_name = self::KAGE_NAMES[$this->village_id];
            $this->coords = self::getLocation($this->system, $this->village_id);
            $this->relations = self::getRelations($this->system, $this->village_id);
        }
        // updated legacy constructor logic
        else {
            $this->name = $village;
            $this->getVillageData();
            $this->kage_name = self::KAGE_NAMES[$this->village_id];
            $this->coords = self::getLocation($this->system, $this->village_id);
            $this->relations = self::getRelations($this->system, $this->village_id);
        }
    }

    public static function getLocation(System $system, string $village_id): ?TravelCoords {
        $result = $system->db->query(
            "SELECT `maps_locations`.`x`, `maps_locations`.`y`, `maps_locations`.`map_id` FROM `villages`
                INNER JOIN `maps_locations` ON `villages`.`map_location_id`=`maps_locations`.`location_id`
                WHERE `villages`.`village_id`='{$village_id}' LIMIT 1"
        );
        if($system->db->last_num_rows != 0) {
            $result = $system->db->fetch($result);
            return new TravelCoords(
                x: (int)$result['x'],
                y: (int)$result['y'],
                map_id: (int)$result['map_id']
            );
        }

        return null;
    }

    private function getVillageData()
    {
        $result = $this->system->db->query(
            "SELECT `village_id`, `region_id` FROM `villages`
                WHERE `villages`.`name`='{$this->name}'"
        );
        $result = $this->system->db->fetch($result);
        $this->region_id = $result['region_id'];
        $this->village_id = $result['village_id'];
    }

    public function addResource(int $resource_id, int $quantity) {
        if (!empty($this->resources[$resource_id])) {
            $this->resources[$resource_id] += $quantity;
        } else {
            $this->resources[$resource_id] = $quantity;
        }
    }

    public function subtractResource(int $resource_id, int $quantity) {
        if (!empty($this->resources[$resource_id]) && $this->resources[$resource_id] > $quantity) {
            $this->resources[$resource_id] -= $quantity;
        } else {
            $this->resources[$resource_id] = 0;
        }
    }

    public function updateResources(): bool {
        $resources = json_encode($this->resources);
        $this->system->db->query("UPDATE `villages` SET `resources` = '{$resources}' WHERE `village_id` = {$this->village_id}");
        if ($this->system->db->last_affected_rows > 0) {
            return true;
        }
        return false;
    }

    /**
     * @return VillageRelation[]
     */
    public function getRelations(System $system, string $village_id): array {
        $relations = [];
        $relations_result = $system->db->query(
            "SELECT * FROM `village_relations`
                WHERE `relation_end` IS NULL
                AND (`village1_id` = {$village_id} OR `village2_id` = {$village_id});
            ");
        $relations_result = $system->db->fetch_all($relations_result);
        foreach ($relations_result as $relation) {
            $relation_target = 0;
            if ($relation['village1_id'] != $village_id) {
                $relation_target = $relation['village1_id'];
            }
            else if ($relation['village2_id'] != $village_id) {
                $relation_target = $relation['village2_id'];
            }
            $relations[$relation_target] = new VillageRelation($relation);
        }
        return $relations;
    }
}
