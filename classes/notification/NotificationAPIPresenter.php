<?php

class NotificationApiPresenter
{
    public static function userNotificationResponse(NotificationAPIManager $notificationManager): array
    {
        return array_map(
            function (NotificationDto $notification) {
                return [
                    'action_url' => $notification->action_url,
                    'type' => $notification->type,
                    'message' => $notification->message,
                    'notification_id' => $notification->notification_id,
                    'user_id' => $notification->user_id,
                    'created' => $notification->created,
                    'duration' => $notification->duration,
                    'alert' => $notification->alert,
                ];
            },
            $notificationManager->getUserNotifications()
        );
    }
}