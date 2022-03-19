<?php

use JetBrains\PhpStorm\ArrayShape;

require_once __DIR__ . '/../System.php';
require_once __DIR__ . '/../Coords.php';
require_once __DIR__ . '/Battle.php';
require_once __DIR__ . '/BattleFieldTile.php';

class BattleField {
    private System $system;
    private Battle $battle;

    /** @var int[] */
    private array $fighter_locations;

    protected int $min_tile;
    protected int $max_tile;

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

    // PUBLIC VIEW API
    public function getFighterLocation(string $combat_id): ?int {
        return $this->fighter_locations[$combat_id] ?? null;
    }

    public function getDisplayTiles(): array {
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
}
