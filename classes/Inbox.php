<?php

require_once __DIR__ . '/InboxUser.php';

class Inbox {
	const INBOX_SIZE = 50;
    // Seal inbox size is managed in the Forbidden Seal Class
	const INBOX_SIZE_STAFF = 100;

	const MIN_CONVO_SIZE = 2;
	const MAX_CONVO_SIZE = 10;
	const MIN_MESSAGE_LENGTH = 2;
	const MAX_MESSAGE_LENGTH = 1000;

    const MAX_TITLE_LENGTH = 26;
    const MAX_MESSAGE_FETCH = 25;

    const ALERT_YEN_RECEIVED = 1;
    const ALERT_AK_RECEIVED = 2;
    const ALERT_AK_OFFER_COMPLETED = 3;
    const ALERT_SUPPORT_REQUEST_UPDATED = 4;

    const DEFAULT_AVATAR = './images/default_avatar.png';

    const SYSTEM_MESSAGE_NAMES = [
        1 => 'Money Transfer System',
        2 => 'Kunai Transfer System',
        3 => 'Ancient Kunai Exchange',
        4 => 'Support System'
    ];

    const SYSTEM_MESSAGE_CODES = [
        1 => 'money_transfer',
        2 => 'kunai_transfer',
        3 => 'market_exchange',
        4 => 'support_system'
    ];

    public function __construct() {
    }

    /**
     * @param System $system
     * @param int    $message_id
     * @return array fetch
     * @throws Exception
     */
    public static function getInfoFromMessageId(System $system, int $message_id): array {
        $convo_result = $system->db->query(
            "SELECT `convo_id`, `time` FROM `convos_messages` WHERE `message_id`='{$message_id}' LIMIT 1"
        );
        if($system->db->last_num_rows) {
            $convo_data = $system->db->fetch($convo_result);
            $timeMax = $convo_data['time'] + 43200;
            $timeMin = $convo_data['time'] - 43200;

            $sql = "SELECT `convos_messages`.*, `users`.`user_name`, `users`.`staff_level`
                FROM `convos_messages`
                INNER JOIN `users`
                ON `convos_messages`.`sender_id`=`users`.`user_id`
                WHERE `convo_id`='{$convo_data['convo_id']}' AND `time` BETWEEN {$timeMin} AND {$timeMax}
                ORDER BY `time` DESC";

            $result = $system->db->query($sql);
            $sender_id = null;
            $user_name = null;
            $staff_level = null;
            $time = null;

            if($system->db->last_num_rows) {
                $returnString = '';
                while($post = $system->db->fetch($result)) {
                    if($post['message_id'] == $message_id) {
                        $sender_id = $post['sender_id'];
                        $user_name = $post['user_name'];
                        $staff_level = $post['staff_level'];
                        $time = $post['time'];

                        $returnString.= "<span style=color:red;>";
                    }
                    $returnString .= "<b>{$post['user_name']}:&nbsp;</b>{$post['message']}";
                    if($post['message_id'] == $message_id) {
                        $returnString .= "<br />(REPORTED MESSAGE)</span>";
                    }

                    $returnString .= "<br /><br />";
                }
                return [
                    'message_id' => $message_id,
                    'convo_id' => $convo_data['convo_id'],
                    'sender_id' => $sender_id,
                    'message' => $returnString,
                    'user_name' => $user_name,
                    'staff_level' => $staff_level,
                    'time' => $time
                ];
            }
        }

        return false;
    }

    /**
     * @param     $system
     * @param int|string $convo_id
     * @return int
     */
    public static function checkConvo($system, int|string $convo_id): int {
        $sql = "SELECT COUNT(`convo_id`) FROM `convos` WHERE `convo_id`='{$convo_id}' AND `active`=1";
        $result = $system->db->query($sql);
        $count = $result->fetch_row();
        return $count[0];
    }

    /**
     * @param System $system
     * @param string $user_name
     * @return array|null
     */
    public static function getUserData(System $system, string $user_name): ?InboxUser {
        $sql = "SELECT `users`.`user_id`,
                `users`.`user_name`,
                `users`.`avatar_link`,
               `users`.`forbidden_seal`,
               `users`.`staff_level`,
               `blacklist`.`blocked_ids`
                FROM `users`
                INNER JOIN `blacklist`
                ON `users`.`user_id`=`blacklist`.`user_id`
                WHERE `users`.`user_name`='{$user_name}'";
        $result = $system->db->query($sql);
        if (!$system->db->last_num_rows) {
            return null;
        }

        $data = $system->db->fetch($result);

        return new InboxUser(
            user_id: $data['user_id'],
            user_name: $data['user_name'],
            avatar_link: $data['avatar_link'],
            forbidden_seal: $data['forbidden_seal'] ? json_decode($data['forbidden_seal'], true) : null,
            staff_level: $data['staff_level'],
            blocked_ids: json_decode($data['blocked_ids'], true)
        );
    }

    /**
     * @param InboxUser[] $convo_members
     * @return boolean
     */
    public static function checkBlacklist(array $convo_members): bool {
        $user_ids = [];
        foreach($convo_members as $member) {
            $user_ids[$member->user_id] = $member->user_id;
        }
        foreach($convo_members as $member) {
            if (empty($member->blocked_ids)) {
                continue;
            }
            $comparison = array_intersect_key($user_ids, $member->blocked_ids);
            if ($comparison) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param System      $system
     * @param int         $owner_id
     * @param string|null $title
     * @return int
     */
    public static function createConversation(System $system, int $owner_id, ?string $title): int {
        $system->db->query("INSERT INTO `convos` (`owner_id`, `title`) VALUES ('{$owner_id}', '{$title}')");
        return $system->db->last_insert_id;
    }

    /**
     * @param int|string $convo_id
     * @param int $sender_id
     * @param string $message
     * @return int message_id
     */
    public static function sendMessage($system, $convo_id, $sender_id, $message): int {
        $timestamp = time();
        $sql = "INSERT INTO `convos_messages` (`convo_id`, `sender_id`, `message`, `time`)
                VALUES ('{$convo_id}', '{$sender_id}', '{$message}', '{$timestamp}')";
        $system->db->query($sql);
        return $system->db->last_insert_id;
    }

    /**
     * @param System system
     * @param int type
     * @param int sender_id
     * @param int target_id
     * @param string message
     * @return int insert_id
     */
    public static function sendAlert($system, $type, $sender_id, $target_id, $message): int {
        $time = time();
        $sql = "INSERT INTO `convos_alerts` (`system_id`, `sender_id`, `target_id`, `message`, `time`)
                VALUES ('{$type}', '{$sender_id}', '{$target_id}', '{$message}', '{$time}')";
        $result = $system->db->query($sql);
        return $system->db->last_insert_id;
    }

    /**
     * @param int|string $convo_id
     * @param string $title
     * @return int last_affected_rows
     */
    public static function updateTitle($system, $convo_id, $title): int {
        $sql = "UPDATE `convos` SET `title`='{$title}' WHERE `convo_id`='{$convo_id}'";
        $result = $system->db->query($sql);
        return $system->db->last_affected_rows;
    }

    /**
     * @param int|string $convo_id
     * @param int $user_id
     * @return int entry_id
     */
    public static function addUserToConvo($system, $convo_id, $user_id): int {
        $sql = "INSERT INTO `convos_users` (`convo_id`, `user_id`)
                VALUES ('{$convo_id}', '{$user_id}')";
        $result = $system->db->query($sql);
        return $system->db->last_insert_id;
    }

    /**
     * @param int|string $convo_id
     * @param int user_id
     * @return int last_affected_rows
     */
    public static function removePlayerFromConvo($system, $convo_id, $user_id): bool {
        $system->db->query("DELETE FROM `convos_users` WHERE `convo_id`='{$convo_id}' AND `user_id`='{$user_id}'");
        return $system->db->last_affected_rows > 0;
    }

    /**
     * @param System system
     * @param int convo_id
     * @param int user_id
     * @return boolean
     */
    public static function toggleMute(System $system, int $convo_id, int $user_id): bool {
        $system->db->query(
            "UPDATE `convos_users` SET `muted`=(`muted` ^ 1) WHERE `convo_id`='{$convo_id}' AND `user_id`={$user_id}"
        );
        return $system->db->last_affected_rows > 0;
    }

    /**
     * @param System $system
     * @param int    $convo_id
     * @return bool whether delete succeeded or not
     */
    public static function deleteConvo(System $system, int|string $convo_id): bool {
        $system->db->query("UPDATE `convos` SET `active`=0 WHERE `convo_id`='{$convo_id}'");
        return $system->db->last_affected_rows > 0;
    }

    /**
     * @param int $user_id
     * @return int $count
     */
    public static function conversationCountForUser($system, $user_id): int {
        $sql = "SELECT COUNT(`entry_id`) FROM `convos_users` WHERE `user_id`='{$user_id}'";
        $result = $system->db->query($sql);
        $count = $result->fetch_row();
        return $count[0];
    }

    /**
     * @param ForbiddenSeal|array $forbidden_seal
     * @param int $staff_level
     * @return int
     */
    public static function maxConvosAllowed($forbidden_seal, $staff_level): int {
        $max_size = self::INBOX_SIZE;
        if ($staff_level) {
            $max_size = self::INBOX_SIZE_STAFF;
        } 
        elseif ($forbidden_seal instanceof ForbiddenSeal) {
            $max_size = $forbidden_seal->inbox_size;
        } 
        elseif (is_array($forbidden_seal)) {
            $max_size = ForbiddenSeal::$benefits[$forbidden_seal['level']]['inbox_size'];
        }
        return $max_size;
    }

    /**
     * @param $title
     * @return bool
     */
    public static function checkTitleLength($title): bool {
        return strlen($title) <= self::MAX_TITLE_LENGTH;
    }

    /**
     * @param array|ForbiddenSeal $forbidden_seal
     * @param int $staff_level
     * @return int MAX_MESSAGE_LENGTH
     */
    public static function checkMaxMessageLength($forbidden_seal, $staff_level): int {
        if($staff_level) {
            return ForbiddenSeal::$benefits[ForbiddenSeal::$STAFF_SEAL_LEVEL]['pm_size'];
        }
        elseif($forbidden_seal instanceof ForbiddenSeal) {
            return $forbidden_seal->pm_size;
        }
        else {
            return self::MAX_MESSAGE_LENGTH;
        }
    }

    /**
     * @param string $message
     * @param array? $forbidden_seal
     * @param int $staff_level
     * @return boolean
     */
    public static function checkMessageLength($message, $forbidden_seal, $staff_level): bool {
        return strlen($message) <= self::checkMaxMessageLength($forbidden_seal, $staff_level) ? true : false;
    }

    /**
     * @param InboxUser[] convo_members
     * @param int user_id
     * @return boolean
     */
    public static function checkIfUserInConvo($convo_members, $user_id): bool {
        foreach($convo_members as $member) {
            if ($member->user_id == $user_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int|string $convo_id
     * @return boolean
     */
    public static function updateLastViewedForUser($system, $convo_id, $user_id): bool {
        $timestamp = time();
        $sql = "UPDATE `convos_users` SET `last_read`={$timestamp} WHERE `convo_id`='{$convo_id}' AND `user_id`={$user_id}";
        $result = $system->db->query($sql);
        return $system->db->last_affected_rows;
    }

    /**
     * @param     $system
     * @param int|string $convo_id
     * @param     $user_id
     * @return int (0,1)
     */
    public static function verifyAccessToConvo($system, $convo_id, $user_id): int {
        $sql = "SELECT COUNT(`entry_id`) FROM `convos_users` WHERE `convo_id`='{$convo_id}' AND `user_id`='{$user_id}'";
        $result = $system->db->query($sql);
        $count = $result->fetch_row();
        return $count[0];
    }

    /**
     * @param System $system
     * @param int    $convo_id
     * @return null|InboxUser[] $convo_members
     */
    public static function getConvoMembers(System $system, int|string $convo_id): ?array {
        $sql = "SELECT `users`.`user_name`, `users`.`user_id`, `users`.`avatar_link`, `blacklist`.`blocked_ids`, `users`.`forbidden_seal`, `users`.`staff_level`
                FROM `convos_users`
                INNER JOIN `users`
                ON `convos_users`.`user_id`=`users`.`user_id`
                INNER JOIN `blacklist`
                ON `blacklist`.`user_id`=`users`.`user_id`
                WHERE `convos_users`.`convo_id`='{$convo_id}'";
        $result = $system->db->query($sql);
        if (!$system->db->last_num_rows) {
            return null;
        }

        $users_data = $system->db->fetch_all($result);

        return array_map(function ($user_data) {
            return new InboxUser(
                user_id: $user_data['user_id'],
                user_name: $user_data['user_name'],
                avatar_link: $user_data['avatar_link'],
                forbidden_seal: $user_data['forbidden_seal']
                    ? json_decode($user_data['forbidden_seal'], true)
                    : null,
                staff_level: $user_data['staff_level'],
                blocked_ids: json_decode($user_data['blocked_ids'], true)
            );
        }, $users_data);
    }

    public static function getSystemConvo(System $system, int $system_id, int $user_id): array {
        $convo = ['convo_id' => self::SYSTEM_MESSAGE_CODES[$system_id], 'convo_members' => []];
        $sql = "SELECT *
                FROM `convos_alerts`
                WHERE `system_id`='{$system_id}'
                AND `target_id`={$user_id}
                AND `alert_deleted`=0
                ORDER BY `alert_id` DESC";
        $result = $system->db->query($sql);
        if (!$system->db->last_num_rows) {
            return [];
        }
        $convo_data = $system->db->fetch_all($result);
        $all_messages = [];
        foreach($convo_data as $message_data) {
            $message = [];
            $message['message_id'] = $message_data['alert_id'];
            // self_message
            $message['self_message'] = false;
            // profile_link
            $message['profile_link'] = $system->router->links['members'];
            // avatar_link
            $message['avatar_link'] = self::DEFAULT_AVATAR;
            // chat color
            $message['chat_color'] = null;
            // user name
            $message['user_name'] = self::SYSTEM_MESSAGE_NAMES[$system_id];
            // time
            $message['time'] = $message_data['time'];
            // report link
            $message['report_link'] = $system->router->links['report'];
            // message
            $message['message'] = $message_data['message'];
            $all_messages[] = $message;
        }
        $convo['all_messages'] = $all_messages;
        return $convo;
    }

    /**
     * @param System $system
     * @param User $user
     * @param int|string $convo_id
     * @param int? $timestamp
     * @return boolean|array $messages_data
     */
    public static function getMessages($system, $user, $convo_id, $timestamp = 0, $message_id = 99999999999): bool|array {
        $limit = self::MAX_MESSAGE_FETCH;
        $new_message_array = [];

        $sql = "SELECT `convos_messages`.*, `users`.`user_name`, `users`.`avatar_link`, `users`.`forbidden_seal`, `users`.`staff_level`, `users`.`chat_color`
                FROM `convos_messages`
                INNER JOIN `users`
                ON `convos_messages`.`sender_id`=`users`.`user_id`
                WHERE `convos_messages`.`convo_id`='{$convo_id}'
                AND `convos_messages`.`deleted`=0
                AND `convos_messages`.`time`>{$timestamp}
                AND `convos_messages`.`message_id`<{$message_id}
                ORDER BY `convos_messages`.`time` DESC
                LIMIT {$limit}";
        $result = $system->db->query($sql);
        if (!$system->db->last_num_rows) {
            return false;
        }
        $all_message_data = $system->db->fetch_all($result);
        foreach($all_message_data as $message_data) {
            $message_data['message'] = $system->html_parse(stripslashes($message_data['message']), false, true);
            $message_data['self_message'] = $message_data['sender_id'] == $user->user_id ? true : false;
            $new_message_array[] = $message_data;
        }

        return $new_message_array;
    }

    /**
     * @param int|string $convo_id
     * @return int $owner_id
     */
    public static function getConvoOwner($system, $convo_id): int {
        $sql = "SELECT `owner_id` FROM `convos` WHERE `convo_id`='{$convo_id}'";
        $result = $system->db->query($sql);
        $owner_id = $system->db->fetch();
        return $owner_id['owner_id'];
    }

    /**
     * @param System system
     * @param int system_id
     * @param int user_id
     * @return int last_affacted_rows
     */
    public static function updateUnreadSystemAlert($system, $system_id, $user_id): int {
        $sql = "UPDATE `convos_alerts`
                SET `unread`=0
                WHERE `unread`=1
                AND `system_id`='{$system_id}'
                AND `target_id`='{$user_id}'";
        $result = $system->db->query($sql);
        return $system->db->last_affected_rows;
    }

    /**
     * @param System system
     * @param int user_id
     * @return array
     */
    public static function getAlertsForUser($system, $user_id): array {
        $return_arr = [];
        // grab all the alerts for the user
        $sql = "SELECT t1.*
                FROM `convos_alerts` t1
                JOIN (
                    SELECT MAX(`alert_id`) as `alert_id`
                    FROM `convos_alerts`
                    WHERE `target_id`={$user_id}
                    GROUP BY `system_id`
                ) t2
                ON t1.`alert_id` = t2.`alert_id`
                WHERE t1.`target_id`={$user_id}";
        $result = $system->db->query($sql);
        if (!$system->db->last_num_rows) {
            return [];
        }
        $all_alerts = $system->db->fetch_all($result);
        foreach($all_alerts as $alert) {
            // build a fake conversation to display
            $tmp_array = [];
            // avatar_link = SELF::DEFAULT_AVATAR
            $tmp_array['members'] = array(['avatar_link' => self::DEFAULT_AVATAR]);
            // user_name
            $tmp_array['title'] = self::SYSTEM_MESSAGE_NAMES[$alert['system_id']];
            // time = we have this
            $tmp_array['latest_timestamp'] = $alert['time'];
            $tmp_array['unread'] = $alert['unread'] ? true : false;
            $tmp_array['convo_id'] = self::SYSTEM_MESSAGE_CODES[$alert['system_id']];

            $return_arr[] = $tmp_array;
        }
        return $return_arr;
    }


    /**
     * @param int $user_id
     * @return array $convos
     */
    public static function allConvosForUser($system, $user_id): array {
        $convos = []; // return array

        // get all alerts and add it to the return array
        $alerts = self::getAlertsForUser($system, $user_id);
        if (!empty($alerts)) {
            $convos = array_merge($convos, $alerts);
        }

        // Grab all convos the user is a member of
        $sql = "SELECT * FROM `convos`
                INNER JOIN `convos_users`
                ON `convos`.`convo_id` = `convos_users`.`convo_id`
                WHERE `convos_users`.`user_id`='{$user_id}'
                AND `convos`.`active`=1
                ORDER BY `convos_users`.`last_read` DESC";
        $result = $system->db->query($sql);
        $convos_data = $system->db->fetch_all($result);

        foreach($convos_data as $convo) {
            $convo['title'] = htmlspecialchars_decode($convo['title'], ENT_QUOTES);

            // Get a list of members for each conversation
            $sql = "SELECT `convos_users`.`user_id`, `users`.`user_name`,
            `users`.`avatar_link`
                    FROM `convos_users`
                    INNER JOIN `users`
                    ON `convos_users`.`user_id` = `users`.`user_id`
                    WHERE `convos_users`.`convo_id`='{$convo['convo_id']}'
                    AND `convos_users`.`user_id`<>'{$user_id}'";
            $result = $system->db->query($sql);
            $user_data = $system->db->fetch_all($result);
            $convo['members'] = $user_data;

            // get last message data from each convo
            $sql = "SELECT `time`
                    FROM `convos_messages`
                    WHERE `convo_id`='{$convo['convo_id']}'
                    AND `deleted`=0
                    ORDER by `time` DESC
                    LIMIT 1";
            $result = $system->db->query($sql);
            $message_data = $system->db->fetch($result);

            // unread messages
            if (!empty($message_data)) {
                $convo['unread'] = ($convo['last_read'] < $message_data['time']) ? true : false;
                $convo['latest_timestamp'] = $message_data['time'];
            } else {
                $convo['unread'] = false;
                $convo['latest_timestamp'] = 0;
            }

            $convos[] = $convo;
        }

        // sort convos by the most recent action
        usort($convos, function($a, $b) {
            return $a['latest_timestamp'] < $b['latest_timestamp'];
        });

        return $convos;
    }

}

