<?php

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class BattleApiPresenter {
    public static function buildResponse(
        Battle $battle,
        BattleField $battle_field,
        Fighter $player,
        Fighter $opponent,
        bool $is_spectating,
        bool $player_action_submitted,
        array $player_default_attacks
    ): array {
        return [
            'id' => $battle->battle_id,
            'fighters' => [
                $player->combat_id => BattleApiPresenter::fighterResponse(fighter: $player, is_player: true,),
                $opponent->combat_id => BattleApiPresenter::fighterResponse(fighter: $opponent),
            ],
            'playerId' => $player->combat_id,
            'opponentId' => $opponent->combat_id,
            'playerDefaultAttacks' => $player_default_attacks,
            'playerEquippedWeapons' => array_map(
                function($weapon_id) use ($player) {
                    if(!$player->hasItem($weapon_id)) {
                        return null;
                    }

                    return BattleApiPresenter::weaponResponse($player->items[$weapon_id]);
                },
                $player->equipped_weapon_ids
            ),
            'field' => BattleApiPresenter::fieldResponse($battle_field),
            'isSpectating' => $is_spectating,
            'isMovementPhase' => $battle->isMovementPhase(),
            'isAttackPhase' => $battle->isAttackPhase(),
            'isPreparationPhase' => $battle->isPreparationPhase(),
            'isComplete' => $battle->isComplete(),
            'playerActionSubmitted' => $player_action_submitted,
            'turnSecondsRemaining' => $battle->timeRemaining(),
            'lastTurnText' => $battle->battle_text,
            'currentPhaseLabel' => $battle->getCurrentPhaseLabel(),
            'jutsuTypes' => [
                'taijutsu' => Jutsu::TYPE_TAIJUTSU,
                'ninjutsu' => Jutsu::TYPE_NINJUTSU,
                'genjutsu' => Jutsu::TYPE_GENJUTSU
            ],
        ];
    }

    private static function fieldResponse(BattleField $field): array {
        return [
            'tiles' => BattleApiPresenter::fieldAllTilesResponse($field),
        ];
    }

    private static function fieldAllTilesResponse(BattleField $field): array {
        return array_map(
            function(BattleFieldTile $tile) {
                return BattleApiPresenter::fieldTileResponse($tile);
            },
            $field->getDisplayTiles()
        );
    }

    private static function fieldTileResponse(BattleFieldTile $tile): array {
        return [
            'fighterIds' => $tile->fighter_ids,
        ];
    }

    private static function fighterResponse(Fighter $fighter, bool $is_player = false): array {
        return [
            'name' => $fighter->getName(),
            'isNpc' => ($fighter instanceof NPC),
            'isAlly' => $is_player,
            'avatarLink' => $fighter->avatar_link,
            'maxAvatarSize' => $fighter->getAvatarSize(),
            'health' => $fighter->health,
            'maxHealth' => $fighter->max_health,
            'hasBloodline' => (bool)$fighter->bloodline_id,
        ];
    }

    private static function jutsuReponse(Jutsu $jutsu): array {
        return [
            'id' => $jutsu->id,
            'combatId' => $jutsu->combat_id,
        ];
    }

    private static function weaponResponse(Item $weapon): array {
        return [
            'effect' => $weapon->effect
        ];
    }
}