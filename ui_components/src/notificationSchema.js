// @flow strict

// Keep this in sync with NotificationApiPresenter.php



export type NotificationType = {|
    +action_url: string,
    +type: "training"
        | "training_complete"
        | "specialmission"
        | "specialmission_complete"
        | "rank"
        | "system"
        | "warning"
        | "report"
        | "battle"
        | "challenge"
        | "team"
        | "marriage"
        | "student"
        | "inbox",
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
    +mission_rank: string,
}>;