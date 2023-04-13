<?php

/**
 * Class LegacyFighterAction
 *
 * Represents a submitted action, what a fighter has chosen to do.
 */
class LegacyFighterAction {
    public ?int $jutsu_id;
    public int $jutsu_purchase_type;
    public ?int $weapon_id;
    public ?string $weapon_element;

    public function __construct(int $jutsu_id, int $jutsu_purchase_type, ?int $weapon_id, ?string $weapon_element) {
        $this->jutsu_id = $jutsu_id;
        $this->jutsu_purchase_type = $jutsu_purchase_type;
        $this->weapon_id = $weapon_id;
        $this->weapon_element = $weapon_element;
    }

    /**
     * @param array $action_data
     * @return LegacyFighterAction
     */
    public static function fromDb(array $action_data): LegacyFighterAction {
        return new LegacyFighterAction(
            $action_data['jutsu_id'],
            $action_data['jutsu_purchase_type'],
            $action_data['weapon_id'],
            $action_data['weapon_element'] ?? "None"
        );
    }
}
