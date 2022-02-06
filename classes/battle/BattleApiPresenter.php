<?php

use JetBrains\PhpStorm\ArrayShape;

class BattleApiPresenter {
    public static function buildResponse(
        Battle $battle,
        BattleField $battle_field,
        Fighter $player,
        Fighter $opponent,
        bool $is_spectating
    ): array {
        return [
            'id' => $battle->battle_id,
            'fighters' => [
                $player->combat_id => BattleApiPresenter::fighterResponse(fighter: $player, is_player: true),
                $opponent->combat_id => BattleApiPresenter::fighterResponse(fighter: $opponent),
            ],
            'playerId' => $player->combat_id,
            'opponentId' => $opponent->combat_id,
            'field' => BattleApiPresenter::fieldResponse($battle_field),
            'isSpectating' => $is_spectating,
            'isMovementPhase' => $battle->isMovementPhase(),
            'isAttackPhase' => $battle->isAttackPhase(),
            'isPreparationPhase' => $battle->isPreparationPhase(),
            'isComplete' => $battle->isComplete()
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
        ];
    }
}