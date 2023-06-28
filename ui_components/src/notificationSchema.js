// @flow strict

// Keep this in sync with NotificationApiPresenter.php



export type NotificationType = {|
    +action_url: string,
    +type: "training"
        | "training_complete"
        | "specialmission"
        | "specialmission_complete"
        | "specialmission_failed"
        | "mission"
        | "mission_team"
        | "mission_clain"
        | "rank"
        | "system"
        | "warning"
        | "report"
        | "battle"
        | "challenge"
        | "team"
        | "marriage"
        | "student"
        | "inbox"
        | "chat"
        | "event",
    +message: string,
    +notification_id: number,
    +user_id: number,
    +created: number,
    +duration: number,
    +alert: boolean,
|};

export type MissionNotificationType = $ReadOnly<{
    ...NotificationType,
    +type: "mission",
    +type: "mission_team",
    +type: "mission_clan",
    +mission_rank: string,
}>;

export type ChatNotificationType = $ReadOnly<{
    ...NotificationType,
    +type: "chat",
    +post_id: number,
}>;

export type BattleNotificationType = $ReadOnly<{
    ...NotificationType,
    +type: "battle",
    +battle_id: number,
}>;