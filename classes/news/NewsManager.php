<?php

require __DIR__ . '/NewsPostDto.php';
require_once __DIR__ . "/../notification/NotificationManager.php";

class NewsManager {
    private System $system;
    private ?User $player;

    public function __construct(System $system, User $player = null) {
        $this->system = $system;
        $this->player = $player;
    }

    /**
     * @return NewsPostDto[]
     */
    public function getNewsPosts(int $max_posts = 8) : array {
        $return_arr = [];

        $result = $this->system->db->query("SELECT * FROM `news_posts` ORDER BY `post_id` DESC LIMIT $max_posts");

        while ($post = $this->system->db->fetch($result)) {
            $return_arr[] = NewsPostDto::fromDb($post);
        }

        return $return_arr;
    }

    /**
     * @return bool
     */
    public function saveNewsPost(NewsPostDto $newsPost): bool
    {
        if ($this->player->hasAdminPanel()) {
            $tags = json_encode($newsPost->tags);
            $time = time();
            if ($newsPost->post_id == 0) {
                $this->system->db->query("INSERT INTO `news_posts` (`sender`, `title`, `message`, `time`, `tags`, `version`)
                    VALUES ('{$this->player->user_name}', '{$newsPost->title}', '{$newsPost->message}', '{$time}', '{$tags}', '{$newsPost->version}')");
                // get users to notify
                $active_threshold = time() - (NotificationManager::ACTIVE_PLAYER_DAYS_LAST_ACTIVE * 86400);
                $user_ids = $this->system->db->query("SELECT `user_id`, `blocked_notifications` FROM `users` WHERE `last_login` > {$active_threshold}");
                $user_ids = $this->system->db->fetch_all($user_ids);
                // create notifications
                foreach ($user_ids as $user) {
                    // Blocked notification
                    $blockedNotifManager = BlockedNotificationManager::BlockedNotificationManagerFromDb(
                        system: $this->system,
                        blocked_notifications_string: $user['blocked_notifications']
                    );
                    if($blockedNotifManager->notificationBlocked(NotificationManager::NOTIFICATION_NEWS)) {
                        continue;
                    }
                    echo "sending notif to {$user['user_id']}<br />";
                    // Send notification
                    $new_notification = new NotificationDto(
                        type: NotificationManager::NOTIFICATION_NEWS,
                        message: "View update notes: {$newsPost->title}",
                        user_id: $user['user_id'],
                        created: time(),
                        expires: time() + (NotificationManager::NOTIFICATION_EXPIRATION_DAYS_NEWS * 86400),
                        alert: false,
                    );
                    NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);
                }
            } else {
                $this->system->db->query("UPDATE `news_posts` SET `title` = '{$newsPost->title}', `message` = '{$newsPost->message}', `tags` = '{$tags}', `version` = '{$newsPost->version}' WHERE `post_id` = '{$newsPost->post_id}'");
            }

            return $this->system->db->last_affected_rows;
        }
        return false;
    }
}