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
                    'label' => $notification->label,
                    'critical' => $notification->critical,
                ];
            },
            $notificationManager->getUserNotifications()
        );
    }
}