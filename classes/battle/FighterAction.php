<?php

// It's a bit weird that this class knows about its children. But it enables us to
// accomplish things like a unified db extract function and a typed lists
require __DIR__ . '/FighterAttackAction.php';
require __DIR__ . '/FighterMovementAction.php';

/**
 * Class FighterAction
 *
 * Represents a submitted action, what a fighter has chosen to do.
 */
abstract class FighterAction {
    const TYPE_ATTACK = 'attack';
    const TYPE_MOVEMENT = 'movement';

    /**
     * @param array $action_data
     * @return FighterAttackAction|FighterMovementAction
     * @throws RuntimeException
     */
    public static function fromDb(array $action_data): FighterAttackAction|FighterMovementAction {
        if($action_data['type'] === self::TYPE_ATTACK) {
            return new FighterAttackAction(
                fighter_id: $action_data['fighter_id'],
                jutsu_id: $action_data['jutsu_id'],
                jutsu_purchase_type: $action_data['jutsu_purchase_type'],
                weapon_id: $action_data['weapon_id'],
                target: AttackTarget::fromDb($action_data['target'])
            );
        }
        else if($action_data['type'] === self::TYPE_MOVEMENT) {
            return new FighterMovementAction(
                fighter_id: $action_data['fighter_id'],
                target_tile: $action_data['target_tile']
            );
        }
        else {
            throw new RuntimeException("Invalid FighterAction db data!");
        }
    }
}