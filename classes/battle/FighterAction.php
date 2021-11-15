<?php

/**
 * Class FighterAction
 *
 * Represents a submitted action, what a fighter has chosen to do.
 */
class FighterAction {
    public ?int $jutsu_id;
    public int $jutsu_purchase_type;
    public ?int $weapon_id;

    public function __construct(int $jutsu_id, int $jutsu_purchase_type, ?int $weapon_id) {
        $this->jutsu_id = $jutsu_id;
        $this->jutsu_purchase_type = $jutsu_purchase_type;
        $this->weapon_id = $weapon_id;
    }

    /**
     * @param array $action_data
     * @return FighterAction
     */
    public static function fromDb(array $action_data): FighterAction {
        return new FighterAction(
            $action_data['jutsu_id'],
            $action_data['jutsu_purchase_type'],
            $action_data['weapon_id']
        );
    }
}
