// @flow strict

// Match this type to BattleApiPresenter::buildResponse
export type BattleType = {|
    +id: number,
    +fighters: $ReadOnlyArray<FighterType>,
    +playerId: string,
    +opponentId: string,
    +field: BattleFieldType,
    +isSpectating: boolean,
    +isMovementPhase: boolean,
    +isAttackPhase: boolean,
    +isPreparationPhase: boolean,
    +isComplete: boolean,
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
|};

// BattleApiPresenter::fieldResponse
export type BattleFieldType = {|
    +tiles: $ReadOnlyArray<BattleFieldTileType>,
|};

// BattleApiPresenter::fieldTileResponse
export type BattleFieldTileType = {|
    +fighterIds: $ReadOnlyArray<String>
|};