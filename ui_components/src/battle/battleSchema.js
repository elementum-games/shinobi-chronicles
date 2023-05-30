// @flow strict

// Match this type to BattleApiPresenter::buildResponse
export type BattleType = {|
    +id: number,
    +fighters: { [key: string]: FighterType },
    +playerId: string,
    +opponentId: string,
    +playerDefaultAttacks: $ReadOnlyArray<JutsuType>,
    +playerEquippedJutsu: $ReadOnlyArray<JutsuType>,
    +playerBloodlineJutsu: $ReadOnlyArray<JutsuType>,
    +playerEquippedWeapons: $ReadOnlyArray<WeaponType>,
    +field: BattleFieldType,
    +isSpectating: boolean,
    +isMovementPhase: boolean,
    +isAttackPhase: boolean,
    +isPreparationPhase: boolean,
    +isComplete: boolean,
    +playerActionSubmitted: boolean,
    +turnCount: number,
    +turnSecondsRemaining: number,
    +lastTurnText: string,
    +lastTurnLog: ?BattleLogType,
    +currentPhaseLabel: string,
    +jutsuTypes: {|
        +ninjutsu: string,
        +taijutsu: string,
        +genjutsu: string,
    |}
|};

// BattleApiPresenter::fighterResponse
export type FighterType = {|
    +id: string,
    +name: string,
    +isNpc: boolean,
    +isAlly: boolean,
    +avatarLink: string,
    +maxAvatarSize: number,
    +health: number,
    +maxHealth: number,
    +chakra: number,
    +maxChakra: number,
    +hasBloodline: boolean,
    +equippedWeapons: $ReadOnlyArray<WeaponType>,
    +movementRange: number,
|};

// BattleApiPresenter::fieldResponse
export type BattleFieldType = {|
    +tiles: $ReadOnlyArray<BattleFieldTileType>,
    +fighterLocations: { [key: string]: number },
|};

// BattleApiPresenter::fieldTileResponse
export type BattleFieldTileType = {|
    +index: number,
    +fighterIds: $ReadOnlyArray<string>
|};

export type JutsuCategory = 'ninjutsu' | 'genjutsu' | 'taijutsu' | "bloodline";

// BattleApiPresenter::jutsuResponse
export type JutsuType = {|
    +id: number,
    +combatId: string,
    +name: string,
    +activeCooldownTurnsLeft: number,
    +jutsuType: 'ninjutsu' | 'genjutsu' | 'taijutsu',
    +useType: 'physical' | 'projectile' | 'projectile_aoe' | 'spawn' | 'barrier',
    +targetType: 'fighter_id' | 'tile' | 'direction',
    +handSeals: $ReadOnlyArray<string>,
    +range: number,
    +element: 'None' | 'Fire' | 'Earth' | 'Wind' | 'Water' | 'Lightning';
|};

// BattleApiPresenter::weaponResponse
export type WeaponType = {|
    +id: number,
    +name: string,
    +effect: string,
    +effectAmount: number,
|};

export type BattleLogType = {|
    +turnNumber: number,
    +isMovementPhase: boolean,
    +isAttackPhase: boolean,
    +isPreparationPhase: boolean,
    +fighterActions: { [key: string]: FighterActionLogType }
|};

export type FighterActionLogType = {|
    +fighterId: string,
    +actionDescription: string,
    +pathSegments: $ReadOnlyArray<AttackPathSegmentType>,
    +hits: $ReadOnlyArray<AttackHitLogType>,
    +effectHits: $ReadOnlyArray<EffectHitLogType>,
    +newEffectAnnouncements: $ReadOnlyArray<string>,
    +jutsuElement: JutsuType["element"],
    +jutsuType: JutsuType["jutsuType"],
    +jutsuUseType: JutsuType["useType"],
    +jutsuTargetType: JutsuType["targetType"],
|};

export type AttackPathSegmentType = {|
    +tileIndex: number,
    +rawDamage: number,
    +timeArrived: number,
|};

export type AttackHitLogType = {|
    +attackerId: string,
    +attackerName: string,
    +targetId: string,
    +targetName: string,
    +damageType: JutsuType["jutsuType"],
    +damage: number,
|};

export type EffectHitLogType = {|
    +casterId: string,
    +targetId: string,
    +type: 'heal' | 'break_genjutsu' | 'ninjutsu_damage' | 'taijutsu_damage' | 'genjutsu_damage',
    +description: string,
|};

// Utility types
export type BoundingRect = {|
    +top: number,
    +left: number,
    +width: number,
    +height: number,
|};