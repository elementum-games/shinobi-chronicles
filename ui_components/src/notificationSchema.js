// @flow strict

// Keep this in sync with NotificationApiPresenter.php



export type NotificationType = {|
    +action_url: string,
    +type: "training"
        | "training_complete"
        | "stat_transfer"
        | "specialmission"
        | "specialmission_complete"
        | "specialmission_failed"
        | "mission"
        | "mission_team"
        | "mission_clan"
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
        | "raid_ally"
        | "raid_enemy"
        | "caravan"
        | "seat_challenge"
        | "lock_challenge"
        | "proposal_created"
        | "proposal_passed"
        | "proposal_canceled"
        | "proposal_expired"
        | "policy_change"
        | "diplomacy_declare_war"
        | "diplomacy_form_alliance"
        | "diplomacy_end_war"
        | "diplomacy_end_alliance"
        | "challenge_pending"
        | "challenge_accepted"
        | "kage_change"
        | "achievement"
        | "daily_task"
        | "ramen_buff",
    +message: string,
    +notification_id: number,
    +user_id: number,
    +created: number,
    +duration: number,
    +expires: number,
    +alert: boolean,
|};

export type MissionNotificationType = $ReadOnly<{
    ...NotificationType,
    +type: "mission" | "mission_team" | "mission_clan",
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