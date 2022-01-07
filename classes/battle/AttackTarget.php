<?php

require_once __DIR__ . '/../Jutsu.php';

abstract class AttackTarget {
    public string $type;

    /**
     * @param array $target_data
     * @return AttackFighterIdTarget|AttackDirectionTarget|AttackTileTarget
     * @throws Exception
     */
    public static function fromDb(array $target_data): AttackFighterIdTarget|AttackDirectionTarget|AttackTileTarget {
        if($target_data['type'] === Jutsu::TARGET_TYPE_FIGHTER_ID) {
            return new AttackFighterIdTarget($target_data['fighter_id']);
        }
        else if($target_data['type'] === Jutsu::TARGET_TYPE_TILE) {
            return new AttackTileTarget($target_data['tile_index']);
        }
        else if($target_data['type'] === Jutsu::TARGET_TYPE_DIRECTION) {
            return new AttackDirectionTarget($target_data['direction']);
        }
        else {
            throw new Exception("Invalid AttackTarget db data!");
        }
    }
}

class AttackFighterIdTarget extends AttackTarget {
    public static string $_type = Jutsu::TARGET_TYPE_FIGHTER_ID;
    public string $type;

    public string $fighter_id;

    public function __construct(string $fighter_id) {
        // for db export
        $this->type = self::$_type;

        $this->fighter_id = $fighter_id;
    }
}

class AttackTileTarget extends AttackTarget {
    public static string $_type = Jutsu::TARGET_TYPE_TILE;
    public string $type;

    public int $tile_index;

    public function __construct(int $tile_index) {
        // for db export
        $this->type = self::$_type;

        $this->tile_index = $tile_index;
    }
}

class AttackDirectionTarget extends AttackTarget {
    public static string $_type = Jutsu::TARGET_TYPE_DIRECTION;
    public string $type;

    // TODO: Convert to enum when PHP 8.1 is supported
    const DIRECTION_LEFT = 'left';
    const DIRECTION_RIGHT = 'right';

    public string $direction;

    /**
     * AttackDirectionTarget constructor.
     * @param string $direction
     * @throws Exception
     */
    public function __construct(string $direction) {
        // for db export
        $this->type = self::$_type;

        switch($direction) {
            case self::DIRECTION_LEFT:
            case self::DIRECTION_RIGHT:
                $this->direction = $direction;
                break;
            default:
                throw new Exception("Invalid direction!");
        }
    }
}