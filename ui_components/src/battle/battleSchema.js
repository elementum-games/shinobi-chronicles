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
    +equippedWeapons: $ReadOnlyArray<WeaponType>
|};

// BattleApiPresenter::fieldResponse
export type BattleFieldType = {|
    +tiles: $ReadOnlyArray<BattleFieldTileType>,
|};

// BattleApiPresenter::fieldTileResponse
export type BattleFieldTileType = {|
    +index: number,
    +fighterIds: $ReadOnlyArray<string>
|};

// BattleApiPresenter::jutsuResponse
export type JutsuType = {|
    +id: number,
    +combatId: string,
    +name: string,
    +activeCooldownTurnsLeft: number,
    +jutsuType: string,
    +handSeals: $ReadOnlyArray<string>,
|};

// BattleApiPresenter::weaponResponse
export type WeaponType = {|
    +id: number,
    +name: string,
    +effect: string,
    +effectAmount: number,
|};

export type JutsuCategory = 'ninjutsu' | 'genjutsu' | 'taijutsu' | "bloodline";

