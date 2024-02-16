<?php

class StaffManager {
    public System $system;

    public int $staff_level;
    public int $support_level;
    public int $user_id;
    public string $user_name;

    const MINUTES_PER_DAY = 1440;
    const MINUTES_PER_MONTH = self::MINUTES_PER_DAY * 30;
    const DATE_FORMAT = 'm/d/y H:i:s';
    const PERM_BAN_VALUE = -1;
    const OW_MIN = 10;
    const OW_MAX = 1000;

    const BAN_TYPE_GAME = 'game';
    const BAN_TYPE_CHAT = 'tavern';
    const BAN_TYPE_PM = 'PM';
    const BAN_TYPE_AVATAR = 'avatar';
    const BAN_TYPE_JOURNAL = 'journal';
    const BAN_TYPE_IP = 'restricted site access';
    public static array $ban_types = [
        self::BAN_TYPE_CHAT, self::BAN_TYPE_GAME, self::BAN_TYPE_PM,
        self::BAN_TYPE_JOURNAL, self::BAN_TYPE_AVATAR, self::BAN_TYPE_IP
    ];
    public static array $ban_menu_items = [
        self::BAN_TYPE_CHAT, self::BAN_TYPE_GAME, self::BAN_TYPE_PM
    ];

    public static array $ban_lengths = [
        '30_minute' => 30,
        '60_minute' => 60,
        '1_day' => self::MINUTES_PER_DAY,
        '3_day' => 3 * self::MINUTES_PER_DAY,
        '1_week' => 7 * self::MINUTES_PER_DAY,
        '2_week' => 14 * self::MINUTES_PER_DAY,
        '1_month' => self::MINUTES_PER_MONTH,
        '3_month' => 3 * self::MINUTES_PER_MONTH,
    ];

    public static array $hm_ban_lengths = [
        '6_month' => 6 * self::MINUTES_PER_MONTH,
        '1_year' => 12 * self::MINUTES_PER_MONTH,
    ];

    public static $admin_ban_lengths = [
        'permanent' => self::PERM_BAN_VALUE
    ];

    const VERDICT_UNHANDLED = 0;
    const VERDICT_GUILTY = 1;
    const VERDICT_NOT_GUILTY = 2;

    const MULTI_DEFAULT = '';
    const MULTI_APPROVED = 'approved';
    const MULTI_PENDING = 'pending';
    const MULTI_DENIED = 'denied';
    public static array $multi_statuses = [
        self::MULTI_APPROVED, self::MULTI_PENDING, self::MULTI_DENIED
    ];

    const STAFF_NONE = 0;
    const STAFF_MODERATOR = 1;
    const STAFF_HEAD_MODERATOR = 2;
    const STAFF_CONTENT_ADMIN = 3;
    const STAFF_ADMINISTRATOR = 4;
    const STAFF_HEAD_ADMINISTRATOR = 5;
    public static array $staff_level_names = [
        self::STAFF_NONE => [
            'short' =>'User',
            'long' => 'User',
        ],
        self::STAFF_MODERATOR => [
            'short' => 'Mod',
            'long' => 'Moderator',
        ],
        self::STAFF_HEAD_MODERATOR => [
            'short' => 'Head Mod',
            'long' => 'Head Moderator',
        ],
        self::STAFF_CONTENT_ADMIN => [
            'short' => 'Content Admin',
            'long' => 'Content Administrator',
        ],
        self::STAFF_ADMINISTRATOR => [
            'short' => 'Admin',
            'long' => 'Administrator',
        ],
        self::STAFF_HEAD_ADMINISTRATOR => [
            'short' => 'Head Admin',
            'long' => 'Head Administrator',
        ],
    ];

    const RECORD_NOT_MIN_SIZE = 20;
    const RECORD_NOTE = 'note';
    const RECORD_BAN_ISSUED = 'ban';
    const RECORD_BAN_REMOVED = 'ban_removed';
    const RECORD_OFFICIAL_WARNING = 'official_warning';

    const STAFF_LOG_MOD = 'mod_action';
    const STAFF_LOG_HEAD_MOD = 'head_mod_action';
    const STAFF_LOG_ADMIN = 'admin_action';
    const STAFF_LOG_SUPPORT = 'support_action';

    public static array $verdicts = [
        self::VERDICT_UNHANDLED => 'Unhandled',
        self::VERDICT_GUILTY => 'Guilty',
        self::VERDICT_NOT_GUILTY => 'Not Guilty'
    ];

    public function __construct(System $system, $user_id, $user_name, $staff_level, $support_level) {
        $this->system = $system;

        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->staff_level = $staff_level;
        $this->support_level = $support_level;
    }


    /**
     * Queries DB for users that have failed logins within specified parameters (i.e. partial/full lock out based on failed logins)
     * Returns array of locked out users, or empty array if no locked users are found
     * @return array
     */
    public function getLockedUsers():array {
        $return = [];
        $query = "SELECT `user_name`, `user_id`, `failed_logins` FROM `users` WHERE `failed_logins` >= " . User::PARTIAL_LOCK
            . " ORDER BY `failed_logins` DESC";
        $result = $this->system->db->query($query);
        if($this->system->db->last_num_rows) {
            while($user = $this->system->db->fetch($result)) {
                $return[] = $user;
            }
        }
        return $return;
    }

    /**
     * Queries DB and returns currently banned users. Returns empty array no banned users are found.
     * @return array
     */
    public function getBannedUsers():array {
        $return = [];
        $result = $this->system->db->query(
            "SELECT `user_id`, `user_name`, `ban_data` FROM `users` WHERE `ban_data` IS NOT NULL AND `ban_data` != ''"
        );
        if($this->system->db->last_num_rows) {
            while($user = $this->system->db->fetch($result)) {
                $ban_data = json_decode($user['ban_data'], true);
                if($ban_data == null) {
                    continue;
                }

                $ban_displays = [];

                foreach($ban_data as $name => $ban_end) {
                    $display = "<b>" . ucwords($name) . ':</b>';
                    if($ban_end == self::PERM_BAN_VALUE) {
                        $display .= " <em>Permanent</em>";
                    }
                    else {
                        $display .= " ({$this->system->time_remaining($ban_end - time())})";
                    }

                    $ban_displays[] = $display;
                }
                $user['ban_string'] = implode(", <br />", $ban_displays);
                $return[] = $user;
            }
        }
        return $return;
    }

    /**
     * Queries multi account DB to check if staff member has done any work
     *                  in regard to the specified account.
     * @param $user_id
     * @return array|string|null
     */
    public function checkMultiStatus($user_id) {
        $result = $this->system->db->query("SELECT * FROM `multi_accounts` WHERE `user_id`='$user_id' LIMIT 1");
        if($this->system->db->last_num_rows) {
            return $this->system->db->fetch($result)['status'];
        }
        return false;
    }

    /**
     * Returns if a record can be viewed by requesting staff member
     * @param $to_view_staff_level
     * @return bool
     */
    public function canViewRecord($to_view_staff_level):bool {
        switch($this->staff_level) {
            case $this->isHeadAdmin():
                return true;
            case $this->isUserAdmin():
                if($to_view_staff_level < self::STAFF_ADMINISTRATOR) {
                    return true;
                }
                return false;
            case $this->isHeadModerator():
                if(in_array($to_view_staff_level, [self::STAFF_CONTENT_ADMIN, self::STAFF_MODERATOR, self::STAFF_NONE])) {
                    return true;
                }
                return false;
            case $this->isModerator():
                if($to_view_staff_level == self::STAFF_NONE) {
                    return true;
                }
                return false;
            default:
                return false;
        }
    }

    /**
     * Inserts record data into DB
     * @param $user_id
     * @param $record_type
     * @param $content
     * @return bool
     */
    public function addRecord($user_id, $user_name, $record_type, $content, $logStaffAction = true):bool {
        //staff id, staff_name, user_id, record_type, time, data
        $this->system->db->query(
            "INSERT INTO `user_record`
                (`staff_id`, `staff_name`, `user_id`, `record_type`, `time`, `data`)
                VALUES
                ('{$this->user_id}', '{$this->user_name}', '{$user_id}', '{$record_type}', '" . time() . "', '{$content}')
            "
        );

        if($this->system->db->last_insert_id) {
            if($logStaffAction) {
                $this->staffLog(self::STAFF_LOG_MOD, "{$this->user_name} ({$this->user_id}) added a note to " .
                    "{$user_name}\'s ({$user_id}) record.");
            }
            return true;
        }
        return false;
    }

    /**
     * Creates staff log of moderator[done], support staff[not done] and admin actions[not done]
     * @param $type
     * @param $content
     */
    public function staffLog($type, $content) {
        $this->system->db->query(
            "INSERT INTO `staff_logs` 
                (`time`, `staff_id`, `type`, `content`) 
                VALUES 
                ('" . time() . "', {$this->user_id}, '{$type}', '{$content}')
            "
        );
    }

    public function getCurrencyLogs(
        int $character_id,
        $offset = 0,
        $limit = 100,
        ?string $currency_type = null,
        ?string $transaction_description_prefix = null
    ): array {
        $query = "SELECT * FROM `currency_logs` WHERE `character_id`={$character_id}";
        if($currency_type != null) {
            $query .= " AND `currency_type`='{$currency_type}'";
        }
        if($transaction_description_prefix != null) {
            $query .= " AND `transaction_description` LIKE '{$transaction_description_prefix}%'";
        }
        $query .= " ORDER BY `id` DESC LIMIT $limit OFFSET $offset";

        $result = $this->system->db->query($query);
        if($this->system->db->last_num_rows) {
            return $this->system->db->fetch_all($result);
        }
        return [];
    }

    public function countCurrencyLogs(
        int $character_id,
        $offset = 0,
        $limit = 100,
        ?string $currency_type = null,
        ?string $transaction_description_prefix = null
    ): int {
        $query = "SELECT COUNT(*) as `count` FROM `currency_logs` WHERE `character_id`={$character_id}";
        if($currency_type != null) {
            $query .= " AND `currency_type`='{$currency_type}'";
        }
        if($transaction_description_prefix != null) {
            $query .= " AND `transaction_description` LIKE '{$transaction_description_prefix}%'";
        }
        $query .= " ORDER BY `id` DESC LIMIT $limit OFFSET $offset";

        $result = $this->system->db->query($query);
        if($this->system->db->last_num_rows) {
            return (int) $this->system->db->fetch($result)['count'];
        }

        return 0;
    }

    public function getStaffLogs($table, $log_type = 'all', $offset = 0, $limit = 100, $maxCount = false) {
        $query = "SELECT " . ($maxCount ? 'COUNT(*)' : '*') . " FROM `" . $table . "` ";
        switch($log_type) {
            case self::STAFF_LOG_MOD:
                $query .= "WHERE `type`='$log_type'";
                break;
            default:
                break;
        }

        $id_name = 'log_id';
        if($table == 'currency_logs' || $table == 'player_logs') {
            $id_name = 'id';
        }

        $query .= " ORDER BY `" . $id_name . "` DESC LIMIT $limit OFFSET $offset";

        $result = $this->system->db->query($query);
        if($this->system->db->last_num_rows) {
            if($maxCount) {
                return (int) $this->system->db->fetch($result)['COUNT(*)'];
            }
            return $this->system->db->fetch_all($result);
        }
        return false;
    }

    /**
     * Removes record note from a user record. Staff permission check is set handled in modPanel
     * @param $record_id
     * @param $user_id
     * @param $user_name
     * @return bool
     */
    public function manageRecord($record_id, $user_id, $user_name, $delete = true):bool {
        $this->system->db->query(
            "UPDATE `user_record` SET `deleted`='{$delete}' WHERE `record_id`='{$record_id}' AND `user_id`='{$user_id}' LIMIT 1"
        );
        if($this->system->db->last_affected_rows) {
            $action_type = ($delete) ? self::STAFF_LOG_HEAD_MOD : self::STAFF_LOG_ADMIN;
            $log_data = "{$this->user_name} ({$this->user_id}) " . ($delete ? 'removed' : 'recovered') . " record note ID $record_id "
            . "on {$user_name}\'s ({$user_id}) record.";
            $this->staffLog($action_type, $log_data);
            return true;
        }
        return false;
    }

    public function manageMulti($user_id, $status):bool {
        $update = $this->checkMultiStatus($user_id);
        if($update != false) {
            $this->system->db->query(
                "UPDATE `multi_accounts` SET `status`='$status' WHERE `user_id`='$user_id' LIMIT 1"
            );
            if($this->system->db->last_affected_rows) {
                $this->staffLog(self::STAFF_LOG_HEAD_MOD, "{$this->user_name}({$this->user_id}) updated "
                    . "multi-account status to $status for user# $user_id.");
                return true;
            }
            return false;
        }
        else {
            $this->system->db->query(
                "INSERT INTO `multi_accounts`
                    (`user_id`, `status`)
                    VALUES
                    ('$user_id', '$status')
                "
            );

            if($this->system->db->last_insert_id) {
                $this->staffLog(self::STAFF_LOG_HEAD_MOD, "{$this->user_name}({$this->user_id}) began "
                    . "multi-account process for user# $user_id with a status of $status.");
                return true;
            }
            return false;
        }
    }

    public function getUserByName($user_name, $full_load = false) {
        $query = "SELECT " . ($full_load ? "*" : "`user_id`, `user_name`, `staff_level`, `ban_data`, `ban_type`, `ban_expire`");
        $result = $this->system->db->query($query . " FROM `users` WHERE `user_name`='{$user_name}' LIMIT 1");
        if(!$this->system->db->last_num_rows) {
            return false;
        }
        return $this->system->db->fetch($result);
    }

    public function getUserByID($user_id, $full_load = false) {
        $query = "SELECT " . ($full_load ? "*" : "`user_id`, `user_name`, `staff_level`, `ban_data`, `ban_type`, `ban_expire`");
        $result = $this->system->db->query($query . " FROM `users` WHERE `user_id`='{$user_id}' LIMIT 1");
        if(!$this->system->db->last_num_rows) {
            return false;
        }
        return $this->system->db->fetch($result);
    }

    public function getBannedIP($ip_address) {
        $result = $this->system->db->query("SELECT * FROM `banned_ips` WHERE `ip_address`='{$ip_address}' LIMIT 1");
        if($this->system->db->last_num_rows) {
            return $this->system->db->fetch($result);
        }
        return false;
    }

    public function canBanUser($new_ban_type, $new_ban_expire, $user_data) {
        $new_ban_expire = time() + ($new_ban_expire * 86400);
        $ban_data = json_decode($user_data['ban_data'], true);

        //No self banning
        if($user_data['user_id'] == $this->user_id) {
            throw new RuntimeException("You can not ban yourself!");
        }
        if(isset($ban_data[$new_ban_type]) && $ban_data[$new_ban_type] == StaffManager::PERM_BAN_VALUE) {
            if(!in_array($new_ban_type, [self::BAN_TYPE_JOURNAL, self::BAN_TYPE_AVATAR])) {
                throw new RuntimeException("Permanent bans must be removed by using the unban system!");
            }
        }
        switch($this->staff_level) {
            case self::STAFF_MODERATOR:
                if($user_data['staff_level'] >= self::STAFF_MODERATOR) {
                    throw new RuntimeException("You do not have permission to ban " .
                    self::$staff_level_names[$user_data['staff_level']]['long'] . "s!");
                }
                //No need to check any further on journal/avatar bans, these do not have expires
                if($new_ban_type == self::BAN_TYPE_AVATAR && $new_ban_type == self::BAN_TYPE_JOURNAL) {
                    return true;
                }
                if($ban_data == null) {
                    return true;
                }
                else {
                    if(isset($ban_data[$new_ban_type]) && $new_ban_expire > $ban_data[$new_ban_type]) {
                        return true;
                    }
                    if(!isset($ban_data[$new_ban_type])) {
                        echo "yeahhhh...";
                        return true;
                    }
                    throw new RuntimeException("{$this->getStaffLevelName(null, 'long')}s can't reduce bans.");
                }
            case self::STAFF_HEAD_MODERATOR:
                if($user_data['staff_level'] == self::STAFF_HEAD_MODERATOR || $user_data['staff_level'] >= self::STAFF_ADMINISTRATOR) {
                    throw new RuntimeException("You are not allowed to ban {$this->getStaffLevelName(null, 'long')}s, "
                    . "{$this->getStaffLevelName(self::STAFF_ADMINISTRATOR, 'long')}s or "
                    . "{$this->getStaffLevelName(self::STAFF_HEAD_ADMINISTRATOR)}s!");
                }
                return true;
            case self::STAFF_ADMINISTRATOR:
                if($user_data['staff_level'] == self::STAFF_HEAD_ADMINISTRATOR) {
                    throw new RuntimeException("You are not allowed to ban {$this->getStaffLevelName(self::STAFF_HEAD_ADMINISTRATOR, 'long')}s!");
                }
                if($user_data['staff_level'] == self::STAFF_ADMINISTRATOR) {
                    throw new RuntimeException("You can not ban fellow " . $this->getStaffLevelName(self::STAFF_ADMINISTRATOR, 'long') . "!");
                }
                return true;
            case self::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                throw new RuntimeException("Invalid staff level!");
        }
    }

    public function canUnbanUser($user_data) {
        switch($this->staff_level) {
            case self::STAFF_HEAD_MODERATOR:
                if($user_data['staff_level'] == self::STAFF_HEAD_MODERATOR || $user_data['staff_level'] >= self::STAFF_ADMINISTRATOR) {
                    throw new RuntimeException("You do not have permission to unban this user!");
                }
                return true;
            case self::STAFF_ADMINISTRATOR:
                if($user_data['staff_level'] >= self::STAFF_ADMINISTRATOR) {
                    throw new RuntimeException("You do not have permission to unban this user!");
                }
                return true;
            case self::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                throw new RuntimeException("You do not have permission to unban members!");
        }
    }

    public function canIssueOW($staff_level) {
        switch($this->staff_level) {
            case self::STAFF_MODERATOR:
                if($staff_level >= self::STAFF_MODERATOR) {
                    return false;
                }
                return true;
            case self::STAFF_HEAD_MODERATOR:
                if($staff_level != self::STAFF_HEAD_MODERATOR && $staff_level < self::STAFF_ADMINISTRATOR) {
                    return true;
                }
                return false;
            case self::STAFF_ADMINISTRATOR:
                if($staff_level < self::STAFF_ADMINISTRATOR) {
                    return true;
                }
                return false;
            case self::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                return false;
        }
    }

    public function banUser($ban_type, $ban_length, $user_data) {
        $ban_data = json_decode($user_data['ban_data'], true);
        $old_ban_remaining = false;

        if($ban_length == self::PERM_BAN_VALUE) {
            $ban_expire = $ban_length;
        }
        else {
            // Bans are reported in minutes, add second value to current time
            $ban_expire = time() + ($ban_length * 60);
        }

        //Create new ban data for storage
        if(empty($ban_data)) {
            $ban_data = json_encode(array(
                $ban_type => $ban_expire
            ));
        }
        //Update ban data for storage
        else {
            if(isset($ban_data[$ban_type]) && $ban_expire < $ban_data[$ban_type]) {
                $old_ban_remaining = $this->system->time_remaining(($ban_data[$ban_type]-time()));
            }
            $ban_data[$ban_type] = $ban_expire;
            $ban_data = json_encode($ban_data);
        }

        $this->system->db->query(
            "UPDATE `users` SET `ban_data`='{$ban_data}' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1"
        );
        if($this->system->db->last_affected_rows) {
            //Log action into staff logs
            $staff_log = "{$this->user_name}({$this->user_id}) issued $ban_type ban to " .
            "{$user_data['user_name']}({$user_data['user_id']})";
            if($ban_length > self::MINUTES_PER_DAY * 30) {
                $staff_log .= " for " . ($ban_length / (self::MINUTES_PER_DAY * 30)) . " month(s).";
            }
            elseif($ban_length > self::MINUTES_PER_DAY) {
                $staff_log .= " for " . ($ban_length / self::MINUTES_PER_DAY) . " day(s).";
            }
            elseif($ban_length > self::PERM_BAN_VALUE) {
                $staff_log .= " for {$ban_length} minutes.";
            }
            else {
                $staff_log .= " permanently.";
            }
            
            /*if($old_ban_remaining != false) {
                $staff_log .= "<br />$old_ban_remaining => {$this->system->time_remaining($ban_length*86400)}";
            }*/

            $this->staffLog(self::STAFF_LOG_MOD, $staff_log);

            //Add to user record
            $record_string = "Received a ";
            if($ban_length > self::MINUTES_PER_DAY * 30) {
                $record_string .= ($ban_length / (self::MINUTES_PER_DAY * 30)) . " month ";
            }
            elseif($ban_length > self::MINUTES_PER_DAY) {
                $record_string .= ($ban_length / self::MINUTES_PER_DAY) . " day ";
            }
            elseif($ban_length > self::PERM_BAN_VALUE) {
                $record_string .= " {$ban_length} minute ";
            }
            else {
                $record_string .= " permanent ";
            }
            $record_string .= ucwords($ban_type) . " ban.";
            $this->addRecord($user_data['user_id'], $user_data['user_name'], self::RECORD_BAN_ISSUED,
                $record_string, false);
            return true;
        }
        return false;
    }

    public function unbanUser($unban_type, $user_data) {
        $ban_data = json_decode($user_data['ban_data'], true);
        $unban_string = '';

        if(is_array($unban_type)) {
            $unbanned = false;
            foreach($unban_type as $ban_type) {
                if(isset($ban_data[$ban_type])) {
                    $unbanned = true;
                    $unban_string = ucwords($ban_type) . " Ban, ";
                    unset($ban_data[$ban_type]);
                }
            }
            if(!$unbanned) {
                throw new RuntimeException("Player is not currently banned.");
            }
            $unban_string = substr($unban_string, 0, strlen($unban_string)-2);
        }
        else {
            if(!isset($ban_data[$unban_type])) {
                return false;
            }
            $unban_string = ucwords($unban_type) . " Ban";
            unset($ban_data[$unban_type]);
        }

        $ban_data = empty($ban_data) ? '' : json_encode($ban_data);

        $this->system->db->query(
            "UPDATE `users` SET `ban_data`='{$ban_data}' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1"
        );
        if($this->system->db->last_affected_rows) {
            $this->addRecord($user_data['user_id'], $user_data['user_name'], self::RECORD_BAN_REMOVED,
                "Removed $unban_string.", false);
            $this->staffLog(StaffManager::STAFF_LOG_HEAD_MOD,
                "$this->user_name({$this->user_id}) removed {$user_data['user_name']}\'s ({$user_data['user_id']}) "
                . $unban_string . ".");
            return true;
        }
        return false;
    }

    public function sendOW($content, $user_data) {
        $this->system->db->query(
            "INSERT INTO `official_warnings` 
                (`staff_id`, `staff_name`, `user_id`, `time`, `data`, `viewed`)
                VALUES 
                ('{$this->user_id}', '{$this->user_name}', '{$user_data['user_id']}', '" . time() . "', '{$content}', 0)
            "
        );
        if($this->system->db->last_insert_id) {
            $this->addRecord($user_data['user_id'], $user_data['user_name'], self::RECORD_OFFICIAL_WARNING, $content, false);
            $this->staffLog(self::STAFF_LOG_MOD, "{$this->user_name}($this->user_id) sent {$user_data['user_name']}({$user_data['user_id']}) an Official Warning.");
            return true;
        }
        return false;
    }

    public function removeProfileData($type, $user_data) {
        $query = "UPDATE ";
        switch($type) {
            case self::BAN_TYPE_JOURNAL:
                $query .= "`journals` SET `journal`='' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1";
                break;
            case self::BAN_TYPE_AVATAR:
                $query .= "`users` SET `avatar_link`='./images/default_avatar.png' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1";
                break;
            default:
                return false;
        }
        $this->system->db->query($query);
        if($this->system->db->last_affected_rows) {
            $this->staffLog(self::STAFF_LOG_MOD, "Removed {$user_data['user_name']}({$user_data['user_id']})\'s "
            . ucwords($type) . ".");
            return true;
        }
        return false;
    }

    public function getStaffLevelName($staff_level = null, $name_type = 'short') {
        if($staff_level === null) {
            return self::$staff_level_names[$this->staff_level][$name_type];
        }
        return self::$staff_level_names[$staff_level][$name_type];
    }

    public function getBanLengths() {
        $ban_lengths = self::$ban_lengths;

        switch($this->staff_level) {
            case self::STAFF_HEAD_MODERATOR:
                $ban_lengths = array_replace($ban_lengths, self::$hm_ban_lengths);
                break;
            case self::STAFF_ADMINISTRATOR:
            case self::STAFF_HEAD_ADMINISTRATOR:
                $ban_lengths = array_replace($ban_lengths, self::$hm_ban_lengths, self::$admin_ban_lengths);
                break;
        }
        return $ban_lengths;
    }

    /** ADMIN PANEL METHODS **/
    public function getAdminPanelPerms(string $type, bool $permission_check = false): array {
        switch($type) {
            // Keep create content and edit content in line with each other
            case 'create_content':
                if($this->isContentAdmin() || $permission_check) {
                    $tools = ['create_ai', 'create_jutsu', 'create_item', 'create_bloodline', 'create_mission', 'create_clan'];
                }
                return $tools ?? array();
            case 'edit_content':
                if($this->isContentAdmin() || $permission_check) {
                    $tools = ['edit_ai', 'edit_jutsu', 'edit_item', 'edit_bloodline', 'edit_mission', 'edit_clan'];
                }
                return $tools ?? array();
            case 'misc_tools':
                if($this->isUserAdmin() || $permission_check) {
                    $tools = ['create_rank', 'edit_user', 'activate_user', 'stat_cut', 'staff_payments', 'give_bloodline',
                        'edit_rank', 'edit_team', 'delete_user', 'dev_tools', 'manual_transaction', 'logs', 'reset_password', 'server_maint'];
                }
                return $tools ?? array();
            default:
                return array();
        }
    }

    /**
     * The following returns true/false if a user has a specific set of permissions
     * @return bool
     */
    public function isSupportStaff(): bool {
        return match ($this->support_level) {
            User::SUPPORT_BASIC, User::SUPPORT_INTERMEDIATE, User::SUPPORT_CONTENT_ONLY, User::SUPPORT_SUPERVISOR, User::SUPPORT_ADMIN => true,
            default => false,
        };
    }

    public function isSupportSupervisor(): bool {
        return match ($this->support_level) {
            User::SUPPORT_SUPERVISOR, User::SUPPORT_ADMIN => true,
            default => false,
        };
    }

    public function isSupportAdmin(): bool {
        return match ($this->support_level) {
            User::SUPPORT_ADMIN => true,
            default => false,
        };
    }

    public function isModerator(): bool {
        switch($this->staff_level) {
            case self::STAFF_NONE:
            case self::STAFF_CONTENT_ADMIN:
                return false;
            default:
                return true;
        }
    }

    public function isHeadModerator(): bool {
        switch($this->staff_level) {
            case self::STAFF_HEAD_MODERATOR:
            case self::STAFF_ADMINISTRATOR:
            case self::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                return false;
        }
    }

    public function isContentAdmin(): bool {
        switch($this->staff_level) {
            case self::STAFF_CONTENT_ADMIN:
            case self::STAFF_ADMINISTRATOR:
            case self::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                return false;
        }
    }

    public function isUserAdmin(): bool {
        switch($this->staff_level) {
            case self::STAFF_ADMINISTRATOR:
            case self::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                return false;
        }
    }

    public function isHeadAdmin(): bool {
        return $this->staff_level == User::STAFF_HEAD_ADMINISTRATOR;
    }

    public function hasAdminPanel(): bool {
        return $this->isContentAdmin() || $this->isUserAdmin() || $this->isHeadAdmin();
    }

    public static function hasServerMaintAccess(int $staff_level): bool {
        return in_array($staff_level, [self::STAFF_CONTENT_ADMIN, self::STAFF_ADMINISTRATOR, self::STAFF_HEAD_ADMINISTRATOR]);
    }
}
