<?php

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class BattleApiPresenter {
    public static function buildResponse(
        BattleV2 $battle,
        BattleField $battle_field,
        Fighter $player,
        Fighter $opponent,
        bool $is_spectating,
        bool $player_action_submitted,
        array $player_default_attacks,
        array $player_equipped_jutsu
    ): array {
        return [
            'id' => $battle->battle_id,
            'fighters' => [
                $player->combat_id => BattleApiPresenter::fighterResponse(fighter: $player, is_player: true,),
                $opponent->combat_id => BattleApiPresenter::fighterResponse(fighter: $opponent),
            ],
            'playerId' => $player->combat_id,
            'opponentId' => $opponent->combat_id,
            'playerDefaultAttacks' => array_map(
                function($jutsu) use($battle) {
                    return BattleApiPresenter::jutsuResponse($jutsu, $battle);
                },
                array_values($player_default_attacks)
            ),
            'playerEquippedJutsu' => array_map(
                function(array $id_and_type) use ($player, $battle) {
                    if(!$player->hasJutsu($id_and_type['id'])) {
                        return null;
                    }

                    return BattleApiPresenter::jutsuResponse($player->jutsu[$id_and_type['id']], $battle);
                },
                $player_equipped_jutsu
            ),
            'playerEquippedWeapons' => array_map(
                function($weapon_id) use ($player) {
                    if(!$player->hasItem($weapon_id)) {
                        return null;
                    }

                    return BattleApiPresenter::weaponResponse($player->items[$weapon_id]);
                },
                $player->equipped_weapon_ids
            ),
            'playerBloodlineJutsu' => array_map(
                function(Jutsu $jutsu) use ($player, $battle) {
                    return BattleApiPresenter::jutsuResponse($jutsu, $battle);
                },
                $player->bloodline != null ? array_values($player->bloodline->jutsu) : []
            ),
            'field' => BattleApiPresenter::fieldResponse($battle_field),
            'isSpectating' => $is_spectating,
            'isMovementPhase' => $battle->isMovementPhase(),
            'isAttackPhase' => $battle->isAttackPhase(),
            'isPreparationPhase' => $battle->isPreparationPhase(),
            'isComplete' => $battle->isComplete(),
            'playerActionSubmitted' => $player_action_submitted,
            'turnSecondsRemaining' => $battle->timeRemaining(),
            'turnCount' => $battle->turn_count,
            'lastTurnText' => '',
            'lastTurnLog' => BattleApiPresenter::turnLogResponse($battle->getLastTurnLog()),
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
            'fighterLocations' => $field->fighter_locations,
        ];
    }

    private static function fieldAllTilesResponse(BattleField $field): array {
        return array_values(
            array_map(
                function(BattleFieldTile $tile) {
                    return BattleApiPresenter::fieldTileResponse($tile);
                },
                $field->getTiles()
            )
        );
    }

    private static function fieldTileResponse(BattleFieldTile $tile): array {
        return [
            'index' => $tile->index,
            'fighterIds' => $tile->fighter_ids,
        ];
    }

    private static function fighterResponse(Fighter $fighter, bool $is_player = false): array {
        return [
            'id' => $fighter->combat_id,
            'name' => $fighter->getName(),
            'isNpc' => ($fighter instanceof NPC),
            'isAlly' => $is_player,
            'avatarLink' => $fighter->avatar_link,
            'maxAvatarSize' => $fighter->getAvatarSize(),
            'health' => $fighter->health,
            'maxHealth' => $fighter->max_health,
            'chakra' => $fighter->chakra,
            'maxChakra' => $fighter->max_chakra,
            'hasBloodline' => (bool)$fighter->bloodline_id,
            'movementRange' => 2,
        ];
    }

    private static function jutsuResponse(Jutsu $jutsu, BattleV2 $battle): array {
        return [
            'id' => $jutsu->id,
            'combatId' => $jutsu->combat_id,
            'name' => $jutsu->name,
            'activeCooldownTurnsLeft' => $battle->jutsu_cooldowns[$jutsu->combat_id] ?? 0,
            'jutsuType' => $jutsu->jutsu_type,
            'targetType' => $jutsu->target_type,
            'handSeals' => explode('-', $jutsu->hand_seals),
            'range' => $jutsu->range,
            'element' => $jutsu->element,
        ];
    }

    #[ArrayShape(['id' => "int", 'name' => "string", 'effect' => "string", 'effectAmount' => "float"])]
    private static function weaponResponse(Item $weapon): array {
        return [
            'id' => $weapon->id,
            'name' => $weapon->name,
            'effect' => $weapon->effect,
            'effectAmount' => $weapon->effect_amount,
        ];
    }

    private static function turnLogResponse(?BattleLogV2 $turn_log): ?array {
        if($turn_log == null) {
            return null;
        }

        return [
            'turnNumber' => $turn_log->turn_number,
            'isMovementPhase' => $turn_log->turn_phase === BattleV2::TURN_TYPE_MOVEMENT,
            'isAttackPhase' => $turn_log->turn_phase === BattleV2::TURN_TYPE_ATTACK,
            'isPreparationPhase' => false,
            'fighterActions' => array_map(
                function(FighterActionLog $action_log){
                    return [
                        "fighterId" => $action_log->fighter_id,
                        "actionDescription" => self::unescapeQuotes(
                            $action_log->action_description
                        ),
                        "pathSegments" => array_map(function(AttackPathSegment $segment) {
                            return [
                                'tileIndex' => $segment->tile->index,
                                'rawDamage' => $segment->raw_damage,
                                'timeArrived' => $segment->time_arrived,
                            ];
                        }, $action_log->path_segments),
                        "hits" => array_map(function(AttackHitLog $hit) {
                            return [
                                'attackerId' =>  $hit->attacker_id,
                                'attackerName' => self::unescapeQuotes($hit->attacker_name),
                                'targetId' => $hit->target_id,
                                'targetName' => self::unescapeQuotes($hit->target_name),
                                'damageType' => $hit->damage_type,
                                'damage' => $hit->damage,
                                'timeOccurred' => $hit->time_occurred
                            ];
                        }, $action_log->hits),
                        "effectHits" => array_map(function(EffectHitLog $hit) {
                            return [
                                'casterId' =>  $hit->caster_id,
                                'targetId' => $hit->target_id,
                                'type' => $hit->type,
                                'description' =>  self::unescapeQuotes($hit->description),
                            ];
                        }, $action_log->effect_hits),
                        "newEffectAnnouncements" => self::unescapeQuotes(
                            $action_log->new_effect_announcements
                        ),
                        "jutsuElement" => $action_log->jutsu_element,
                        "jutsuType" => $action_log->jutsu_type,
                        "jutsuUseType" => $action_log->jutsu_use_type,
                        "jutsuTargetType" => $action_log->jutsu_target_type,
                    ];
                },
                $turn_log->fighter_action_logs
            )
        ];
    }

    private static function unescapeQuotes(string|array $content): array|string {
        return str_replace("&#039;", "'", $content);
    }
}