<?php
class BlockedNotificationManager {
    public function __construct(
        public System $system,
        public ?array $blockedNotifications
    ){}

    public function notificationBlocked(string $notification_type): bool {
        return in_array($notification_type, $this->blockedNotifications);
    }

    public function updateBlockedNotifications(array $new_blocked_notifs): void {
        $this->blockedNotifications = $new_blocked_notifs;
    }
    public function dbEncode(): string {
        return json_encode($this->blockedNotifications);
    }
    public static function fromDb(System $system, string $blocked_notifications_string): BlockedNotificationManager {
        $blocked_notifications = json_decode($blocked_notifications_string, true);
        if(!is_array($blocked_notifications)) {
            $blocked_notifications = array();
        }
        return new BlockedNotificationManager(system: $system, blockedNotifications: $blocked_notifications);
    }
}