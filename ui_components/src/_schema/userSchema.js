// @flow strict-local

import type { JutsuElement } from "../battle/battleSchema.js";

export type PlayerGenderOptions = "Male" | "Female" | "Non-binary" | "None";

// KEEP IN SYNC WITH UserApiPresenter::playerDataResponse
export type PlayerDataType = {|
    +avatar_link: string,
    +user_name: string,
    +rank_name: string,
    +level: number,
    +exp: number,
    +expForNextLevel: number,
    +nextLevelProgressPercent: number,
    +totalStats: number,
    +totalStatCap: number,
    +gender: PlayerGenderOptions,
    +elements: $ReadOnlyArray<JutsuElement>,
    +has_bloodline: boolean,
    +avatar_size: number,
    +money: number,
    +premiumCredits: number,
    +premiumCreditsPurchased: number,
    +villageName: string,
    +clanId: ?number,
    +clanName: ?string,
    +teamId: ?number,
    +teamName: ?string,
    +forbiddenSealName: string,
    +forbiddenSealTimeLeft: ?string,
|};

// KEEP IN SYNC WITH UserApiPresenter::playerStatsResponse
export type PlayerStatsType = {|
    +ninjutsuSkill: number,
    +taijutsuSkill: number,
    +genjutsuSkill: number,
    +bloodlineSkill: number,
    +castSpeed: number,
    +speed: number,
    +intelligence: number,
    +willpower: number,
|};


export type AvatarStyles =
    | "avy_none"
    | "avy_borderless"
    | "avy_round"
    | "avy_three-point"
    | "avy_three-point-inverted"
    | "avy_four-point"
    | "avy_four-point-90"
    | "avy_four-point-oblique"
    | "avy_five-point"
    | "avy_six-point"
    | "avy_six-point-long"
    | "avy_eight-point"
    | "avy_eight-point-wide"
    | "avy_nine-point"
    | "avy_twelve-point";

// KEEP IN SYNC WITH UserApiPresenter::playerSettingsResponse
export type PlayerSettingsType = {|
    +avatar_style: AvatarStyles,
    +sidebar_position: "left" | "right",
    +enable_alerts: boolean,
|};

export type DailyTaskType = {|
    +name: string,
    +prompt: string,
    +difficulty: string,
    +rewardYen: number,
    +rewardRep: number,
    +progressPercent: string,
    +progressCaption: string,
    +complete: boolean,
|};

export type PlayerAchievementsType = {|
    +completedAchievements: {|
        +id: string,
        +achievedAt: number,
        +rank: "Legendary" | "Elite" | "Greater" | "Common",
        +name: string,
        +prompt: string,
        +rewards: $ReadOnlyArray<{|
            +type: "MONEY" | "FREEMIUM_CREDITS" | "VILLAGE_REP",
            +amount: number,
        |}>,
    |};
|};