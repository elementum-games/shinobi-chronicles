<?php

use JetBrains\PhpStorm\ArrayShape;

require_once __DIR__ . '/../System.php';
require_once __DIR__ . '/../Coords.php';
require_once __DIR__ . '/Battle.php';
require_once __DIR__ . '/BattleFieldTile.php';
require_once __DIR__ . '/AttackTarget.php';

class BattleField {
    private System $system;
    private Battle $battle;

    /** @var int[] */
    public array $fighter_locations;

    public int $min_tile;
    public int $max_tile;

    /**
     * BattleField constructor.
     * @param System $system
     * @param Battle $battle
     * @throws Exception
     */
    public function __construct(System $system, Battle &$battle) {
        $this->system = $system;
        $this->battle = &$battle;

        $field_data = json_decode($battle->raw_field, true);

        $this->fighter_locations = $field_data['fighter_locations'];
        $this->init();
    }

    private function init(): void {
        if(count($this->fighter_locations) < 1) {
            return;
        }

        $first_fighter_location_key = array_key_first($this->fighter_locations);

        $left_most_fighter_index = $this->fighter_locations[$first_fighter_location_key];
        $right_most_fighter_index = $this->fighter_locations[$first_fighter_location_key];

        foreach($this->fighter_locations as $combat_id => $tile_index) {
            if($tile_index < $left_most_fighter_index) {
                $left_most_fighter_index = $tile_index;
            }
            if($tile_index > $right_most_fighter_index) {
                $right_most_fighter_index = $tile_index;
            }
        }

        /*
         * this is a little confusing, but this is NOT the distance between fighters,
         * rather it's the farthest distance to get from one fighter to another. visual:
         *
         * distance 0
         * - - - A - - -
         *       B
         *
         * distance 1
         * - - A B - -
         *
         * distance 2
         * - - A - B - -
         *
         * distance 3
         * - A - - B -
         *
         * distance 4
         * - A - - - B -
         *
         * distance 5
         * A - - - - B
         */
        $fighter_max_range = abs($left_most_fighter_index - $right_most_fighter_index);

        $min_tiles = 6;
        if($fighter_max_range % 2 == 0) {
            $min_tiles = 7;
        }

        $tiles_needed_for_minimum = $min_tiles - ($fighter_max_range + 1);

        if($tiles_needed_for_minimum >= 2) {
            $this->min_tile = $left_most_fighter_index - ($tiles_needed_for_minimum / 2);
            $this->max_tile = $right_most_fighter_index + ($tiles_needed_for_minimum / 2);
        }
        else {
            $this->min_tile = $left_most_fighter_index;
            $this->max_tile = $right_most_fighter_index;
        }
    }

    // PUBLIC MUTATION API

    /**
     * @param string $fighter_id
     * @param int    $target_tile
     * @throws Exception
     */
    public function moveFighterTo(string $fighter_id, int $target_tile) {
        if(!$this->tileIsInBounds($target_tile)) {
            throw new Exception("Cannot move to target tile - Out of bounds!");
        }

        $this->fighter_locations[$fighter_id] = $target_tile;
    }

    public function tileIsInBounds(int $target_tile): bool {
       return $target_tile >= $this->min_tile && $target_tile <= $this->max_tile;
    }

    /**
     * @param Fighter               $attacker
     * @param BattleAttack          $attack
     * @param AttackDirectionTarget $target
     * @return BattleAttack
     * @throws Exception
     */
    public function setupDirectionAttack(
        Fighter $attacker, BattleAttack $attack, AttackDirectionTarget $target
    ): BattleAttack {
        if(!isset($this->fighter_locations[$attacker->combat_id])) {
            throw new Exception("Invalid attacker location!");
        }

        $tiles = $this->getTiles();

        $starting_tile_index = $this->getFighterLocation($attacker->combat_id) +
            ($target->isDirectionLeft() ? -1 : 1);
        $starting_tile = $tiles[$starting_tile_index] ?? null;
        if(!$this->tileIsInBounds($starting_tile_index) || $tiles[$starting_tile_index] == null) {
            throw new Exception("Invalid starting tile! {$starting_tile_index}");
        }

        $attack->first_tile = $starting_tile;

        $attack->path_segments = [];
        $index = $starting_tile_index;
        for($count = 0; $count < $attack->jutsu->range; $count++) {
            $tile = $this->getTiles()[$index] ?? null;

            $distance_from_start = abs($index - $starting_tile_index);
            if($distance_from_start >= $attack->jutsu->range) {
                break;
            }

            // +1 to include starting tile
            $time_arrived = floor(
                ($distance_from_start + 1) / $attack->jutsu->travel_speed
            );

            $attack->path_segments[] = new AttackPathSegment(
                $tile,
                $attack->starting_raw_damage,
                $time_arrived
            );

            $index += $target->isDirectionLeft() ? -1 : 1;
            if(!$this->tileIsInBounds($index)) {
                break;
            }
        }

        // sort collisions by time occurrence, process
        // if a collision takes place on a path segment that doesn't exist anymore, remove it

        /*
        const USE_TYPE_MELEE = 'physical';
        const USE_TYPE_PROJECTILE = 'projectile';
        const USE_TYPE_PROJECTILE_AOE = 'projectile_aoe';
        const USE_TYPE_REMOTE_SPAWN = 'spawn';
        const USE_TYPE_BUFF = 'buff';
        const USE_TYPE_BARRIER = 'barrier';
        */

        return $attack;
    }

    /**
     * @param Fighter          $attacker
     * @param BattleAttack     $attack
     * @param AttackTileTarget $target
     * @return BattleAttack
     * @throws Exception
     */
    public function setupTileAttack(
        Fighter $attacker, BattleAttack $attack, AttackTileTarget $target
    ): BattleAttack {
        if(!isset($this->fighter_locations[$attacker->combat_id])) {
            throw new Exception("Invalid attacker location!");
        }

        $tile = $this->getTiles()[$target->tile_index] ?? null;
        if($tile == null) {
            throw new Exception("setupTileAttack: Invalid tile!");
        }

        $attack->first_tile = $tile;
        $attack->root_path_segment = new AttackPathSegment(
            tile: $tile,
            raw_damage: $attack->starting_raw_damage,
            time_arrived: (int)$attack->jutsu->travel_speed,
        );

        /*
        const USE_TYPE_MELEE = 'physical';
        const USE_TYPE_PROJECTILE = 'projectile';
        const USE_TYPE_PROJECTILE_AOE = 'projectile_aoe';
        const USE_TYPE_REMOTE_SPAWN = 'spawn';
        const USE_TYPE_BUFF = 'buff';
        const USE_TYPE_BARRIER = 'barrier';
        */

        return $attack;
    }

    // PUBLIC VIEW API
    public function getFighterLocation(string $combat_id): ?int {
        return $this->fighter_locations[$combat_id] ?? null;
    }

    public function getFighterLocationTile(string $combat_id): ?BattleFieldTile {
        $location = $this->fighter_locations[$combat_id] ?? null;
        if($location == null) {
            return null;
        }

        return $this->tiles[$location] ?? null;
    }

    /**
     * @return BattleFieldTile[]
     */
    public function getTiles(): array {
        $fighter_ids_by_tile = [];
        foreach($this->fighter_locations as $combat_id => $tile_index) {
            if($tile_index < $this->min_tile) {
                $this->min_tile = $tile_index;
            }
            if($tile_index > $this->max_tile) {
                $this->max_tile = $tile_index;
            }

            if(isset($fighter_ids_by_tile[$tile_index])) {
                $fighter_ids_by_tile[$tile_index][] = $combat_id;
            }
            else {
                $fighter_ids_by_tile[$tile_index] = [$combat_id];
            }
        }

        $tiles = [];
        for($i = $this->min_tile; $i <= $this->max_tile; $i++) {
            $tiles[$i] = new BattleFieldTile(
                index: $i,
                fighter_ids: $fighter_ids_by_tile[$i] ?? []
            );
        }


        return $tiles;
    }

    /**
     * @param string $fighter_id
     * @param int    $target_tile
     * @return float|int
     * @throws Exception
     */
    public function distanceFromFighter(string $fighter_id, int $target_tile): float|int {
        if(!isset($this->fighter_locations[$fighter_id])) {
            throw new Exception("Invalid fighter location!");
        }

        return abs($this->fighter_locations[$fighter_id] - $target_tile);
    }

    // PUBLIC DATA IMPORT/EXPORT API

    #[ArrayShape(['fighter_locations' => "array"])]
    public static function getInitialFieldExport(Fighter $player1, Fighter $player2, int $battle_type): array {
        return [
            'fighter_locations' => [
                $player1->combat_id => 2,
                $player2->combat_id => 4
            ]
        ];
    }

    #[ArrayShape(['fighter_locations' => "array"])]
    public function exportToDb(): array {
        return [
            'fighter_locations' => $this->fighter_locations,
        ];
    }

    public function getTileDirectionFromFighter(Fighter $fighter, int $target_tile): string {
        return $target_tile > $this->getFighterLocation($fighter->combat_id)
            ? AttackDirectionTarget::DIRECTION_RIGHT
            : AttackDirectionTarget::DIRECTION_LEFT;
    }
}
