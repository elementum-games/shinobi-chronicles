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
    +field: BattleFieldType,
    +isSpectating: boolean,
    +isMovementPhase: boolean,
    +isAttackPhase: boolean,
    +isPreparationPhase: boolean,
    +isComplete: boolean,
    +playerActionSubmitted: boolean,
    +turnSecondsRemaining: number,
    +lastTurnText: string,
    +currentPhaseLabel: string,
    +jutsuTypes: {|
        +ninjutsu: string,
        +taijutsu: string,
        +genjutsu: string,
    |}
|};

// BattleApiPresenter::fighterResponse
export type FighterType = {|
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
    +equippedWeapons: $ReadOnlyArray<WeaponType>
|};

// BattleApiPresenter::fieldResponse
export type BattleFieldType = {|
    +tiles: $ReadOnlyArray<BattleFieldTileType>,
|};

// BattleApiPresenter::fieldTileResponse
export type BattleFieldTileType = {|
    +fighterIds: $ReadOnlyArray<String>
|};

// BattleApiPresenter::jutsuResponse
export type JutsuType = {|
    +id: number,
    +combatId: string,
    +name: string,
    +activeCooldownTurnsLeft: number,
    +jutsuType: string,
|};

// BattleApiPresenter::weaponResponse
export type WeaponType = {|
    +name: string,
    +effect: string,
    +effectAmount: number,
|};

