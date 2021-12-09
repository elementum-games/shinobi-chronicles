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
    }

    public function getFighterLocation(string $combat_id): ?int {
        return $this->fighter_locations[$combat_id] ?? null;
    }

    public function getDisplayTiles(): array {
        if(count($this->fighter_locations) < 1) {
            return [];
        }

        $first_fighter_location_key = array_key_first($this->fighter_locations);

        $min_tile = $this->fighter_locations[$first_fighter_location_key];
        $max_tile = $this->fighter_locations[$first_fighter_location_key];

        $fighters_by_tile = [];
        foreach($this->fighter_locations as $combat_id => $tile_index) {
            if($tile_index < $min_tile) {
                $min_tile = $tile_index;
            }
            if($tile_index > $max_tile) {
                $max_tile = $tile_index;
            }


            $fighter = $this->battle->getFighter($combat_id);
            if($fighter == null) {
                continue;
            }

            if(isset($fighters_by_tile[$tile_index])) {
                $fighters_by_tile[$tile_index][] = $fighter;
            }
            else {
                $fighters_by_tile[$tile_index] = [$fighter];
            }
        }

        $min_tile -= 2;
        $max_tile += 2;

        $tiles = [];
        for($i = $min_tile; $i <= $max_tile; $i++) {
            $tiles[$i] = new BattleFieldTile(
                fighters: $fighters_by_tile[$i] ?? []
            );
        }

        return $tiles;
    }

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
