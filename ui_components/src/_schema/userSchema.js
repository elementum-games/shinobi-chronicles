// @flow strict-local

// KEEP IN SYNC WITH UserApiPresenter::playerDataResponse
export type PlayerDataType = {|
    +avatar_link: string,
    +user_name: string,
    +rank_name: string,
    +level: number,
    +has_bloodline: boolean,
    +avatar_size: number,
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

export type PlayerSettingsType = {|
    +avatar_style: AvatarStyles,
    +sidebar_position: "left" | "right",
    +enable_alerts: boolean,
|};