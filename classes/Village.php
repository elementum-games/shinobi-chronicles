<?php

require_once __DIR__ . '/SpecialMission.php';
require_once __DIR__ . '/../classes/village/VillageRelation.php';

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
            $this->kage_name = VillageManager::KAGE_NAMES[$this->village_id];
            $this->coords = VillageManager::getLocation($this->system, $this->village_id);
            $this->relations = VillageManager::getRelations($this->system, $this->village_id);
        }
        // updated legacy constructor logic
        else {
            $this->name = $village;
            $this->getVillageData();
            $this->kage_name = VillageManager::KAGE_NAMES[$this->village_id];
            $this->coords = VillageManager::getLocation($this->system, $this->village_id);
            $this->relations = VillageManager::getRelations($this->system, $this->village_id);
        }
    }

    private function getVillageData()
    {
        $result = $this->system->db->query(
            "SELECT * FROM `villages`
                WHERE `villages`.`name`='{$this->name}'"
        );
        $result = $this->system->db->fetch($result);
        $this->region_id = $result['region_id'];
        $this->village_id = $result['village_id'];
        $this->points = $result['points'];
        $this->resources = json_decode($result['resources'], true);
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

    public function updateResources(bool $run_query = true): string {
        $resources = json_encode($this->resources);
        $query = "UPDATE `villages` SET `resources` = '{$resources}' WHERE `village_id` = {$this->village_id}";
        if ($run_query) {
            $this->system->db->query("UPDATE `villages` SET `resources` = '{$resources}' WHERE `village_id` = {$this->village_id}");
        }
        return $query;
    }
}
