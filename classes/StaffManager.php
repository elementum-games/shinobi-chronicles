<?php

class StaffManager {
    public User $player;
    public System $system;

    public int $staff_level;
    public int $support_level;
    public int $user_id;
    public string $user_name;

    const PERM_BAN_VALUE = -1;

    const BAN_TYPE_GAME = 'game';
    const BAN_TYPE_CHAT = 'tavern';
    const BAN_TYPE_PM = 'PM';
    //These ban types do not need to be added to the ban_types array
    //They are managed in a different manner
    const BAN_TYPE_AVATAR = 'avatar';
    const BAN_TYPE_JOURNAL = 'journal';
    const BAN_TYPE_IP = 'restricted site access';
    public static array $ban_types = [
        self::BAN_TYPE_CHAT, self::BAN_TYPE_GAME, self::BAN_TYPE_PM
    ];

    const VERDICT_UNHANDLED = 0;
    const VERDICT_GUILTY = 1;
    const VERDICT_NOT_GUILTY = 2;

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

    public function __construct(User $player) {
        $this->player = $player;
        $this->system = $this->player->system;

        $this->user_id = $this->player->user_id;
        $this->user_name = $this->player->user_name;
        $this->staff_level = $this->player->staff_level;
        $this->support_level = $this->player->support_level;
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
        $result = $this->system->query($query);
        if($this->system->db_last_num_rows) {
            while($user = $this->system->db_fetch($result)) {
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
        $result = $this->system->query("SELECT `user_id`, `user_name`, `ban_data` FROM `users` WHERE `ban_data` != null OR `ban_data` != ''");
        if($this->system->db_last_num_rows) {
            while($user = $this->system->db_fetch($result)) {
                $ban_string = '';
                $ban_data = json_decode($user['ban_data'], true);
                $count = 0;
                $size = sizeof($ban_data);
                foreach($ban_data as $name => $ban_end) {
                    $count++;
                    $ban_string .= "<b>" . ucwords($name) . ':</b>';
                    if($ban_end == self::PERM_BAN_VALUE) {
                        $ban_string .= " <em>Permanent</em>";
                    }
                    else {
                        $ban_string .= " ({$this->system->time_remaining($ban_end-time())})";
                    }
                    if($count%2 == 0 && $count != $size) {
                        $ban_string .= ", <br />";
                    }
                    else {
                        if($count != $size) {
                            $ban_string .= ", ";
                        }
                    }
                }
                $user['ban_string'] = $ban_string;
                $return[] = $user;
            }
        }
        return $return;
    }

    /**
     * Returns if a record can be viewed by requesting staff member
     * @param $to_view_staff_level
     * @return bool
     */
    public function canViewRecord($to_view_staff_level):bool {
        switch($to_view_staff_level) {
            case $this->isHeadAdmin():
                return true;
            case $this->isUserAdmin():
                if($to_view_staff_level == self::STAFF_HEAD_ADMINISTRATOR) {
                    return false;
                }
                return true;
            case $this->isHeadModerator():
                if($to_view_staff_level == self::STAFF_HEAD_MODERATOR || $to_view_staff_level >= self::STAFF_ADMINISTRATOR) {
                    return false;
                }
                return true;
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
        $this->system->query("INSERT INTO `user_record`
            (`staff_id`, `staff_name`, `user_id`, `record_type`, `time`, `data`)
            VALUES
            ('{$this->user_id}', '{$this->user_name}', '{$user_id}', '{$record_type}', '" . time() . "', '{$content}')
        ");

        if($this->system->db_last_insert_id) {
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
        $this->system->query("INSERT INTO `staff_logs` 
            (`time`, `type`, `content`) 
            VALUES 
            ('" . time() . "', '{$type}', '{$content}')
        ");
    }

    /**
     * Removes record note from a user record. Staff permission check is set handled in modPanel
     * @param $record_id
     * @param $user_id
     * @param $user_name
     * @return bool
     */
    public function manageRecord($record_id, $user_id, $user_name, $delete = true):bool {
        $this->system->query("UPDATE `user_record` SET `deleted`='{$delete}' WHERE `record_id`='{$record_id}' AND `user_id`='{$user_id}' LIMIT 1");
        if($this->system->db_last_affected_rows) {
            $action_type = ($delete) ? self::STAFF_LOG_HEAD_MOD : self::STAFF_LOG_ADMIN;
            $log_data = "{$this->user_name} ({$this->user_id}) " . ($delete ? 'removed' : 'recovered') . " record note ID $record_id "
            . "on {$user_name}\'s ({$user_id}) record.";
            $this->staffLog($action_type, $log_data);
            return true;
        }
        return false;
    }

    public function getUserByName($user_name) {
        $result = $this->system->query("SELECT `user_id`, `user_name`, `staff_level`, `ban_data`, `ban_type`, `ban_expire` FROM `users` WHERE `user_name`='{$user_name}' LIMIT 1");
        if(!$this->system->db_last_num_rows) {
            return false;
        }
        return $this->system->db_fetch($result);
    }

    public function getBannedIP($ip_address) {
        $result = $this->system->query("SELECT * FROM `banned_ips` WHERE `ip_address`='{$ip_address}' LIMIT 1");
        if($this->system->db_last_num_rows) {
            return $this->system->db_fetch($result);
        }
        return false;
    }

    public function canBanUser($new_ban_type, $new_ban_expire, $user_data) {
        $new_ban_expire = time() + ($new_ban_expire * 86400);
        $ban_data = json_decode($user_data['ban_data'], true);

        //No self banning
        if($user_data['user_id'] == $this->user_id) {
            throw new Exception("You can not ban yourself!");
        }
        if(isset($ban_data[$new_ban_type]) && $ban_data[$new_ban_type] == StaffManager::PERM_BAN_VALUE) {
            if(!in_array($new_ban_type, [self::BAN_TYPE_JOURNAL, self::BAN_TYPE_AVATAR])) {
                throw new Exception("Permanent bans must be removed by using the unban system!");
            }
        }
        switch($this->staff_level) {
            case self::STAFF_MODERATOR:
                if($user_data['staff_level'] >= self::STAFF_MODERATOR) {
                    throw new Exception("You do not have permission to ban " .
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
                    throw new Exception("{$this->getStaffLevelName(null, 'long')}s can't reduce bans.");
                }
            case self::STAFF_HEAD_MODERATOR:
                if($user_data['staff_level'] == self::STAFF_HEAD_MODERATOR || $user_data['staff_level'] >= self::STAFF_ADMINISTRATOR) {
                    throw new Exception("You are not allowed to ban {$this->getStaffLevelName(null, 'long')}s, "
                    . "{$this->getStaffLevelName(self::STAFF_ADMINISTRATOR, 'long')}s or "
                    . "{$this->getStaffLevelName(self::STAFF_HEAD_ADMINISTRATOR)}s!");
                }
                return true;
            case self::STAFF_ADMINISTRATOR:
                if($user_data['staff_level'] == self::STAFF_HEAD_ADMINISTRATOR) {
                    throw new Exception("You are not allowed to ban {$this->getStaffLevelName(self::STAFF_HEAD_ADMINISTRATOR, 'long')}s!");
                }
                return true;
            case self::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                throw new Exception("Invalid staff level!");
        }
    }

    public function canUnbanUser($user_data) {
        switch($this->staff_level) {
            case self::STAFF_HEAD_MODERATOR:
                if($user_data['staff_level'] == self::STAFF_HEAD_MODERATOR || $user_data['staff_level'] >= self::STAFF_ADMINISTRATOR) {
                    throw new Exception("You do not have permission to unban this user!");
                }
                return true;
            case self::STAFF_ADMINISTRATOR:
                if($user_data['staff_level'] == self::STAFF_HEAD_ADMINISTRATOR) {
                    throw new Exception("You do not have permission to unban this user!");
                }
                return true;
            case self::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                throw new Exception("You do not have permission to unban members!");
        }
    }

    public function banUser($ban_type, $ban_length, $user_data) {
        if($ban_length > 0 || $ban_length == self::PERM_BAN_VALUE) {
            $ban_data = json_decode($user_data['ban_data'], true);
            $ban_expire = ($ban_length == self::PERM_BAN_VALUE ? $ban_length : time() + ($ban_length * 86400));
            $old_ban_remaining = false;

            //Create new ban data for storage
            if($ban_data == null || empty($ban_data)) {
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

            $this->system->query("UPDATE `users` SET `ban_data`='{$ban_data}' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");
            if($this->system->db_last_affected_rows) {
                //Log action into staff logs
                $staff_log = "{$this->user_name}({$this->user_id}) $ban_type ban to " .
                "{$user_data['user_name']}({$user_data['user_id']})";
                if($ban_length != -1) {
                    $staff_log .= " for {$ban_length} day(s).";
                }
                else {
                    $staff_log .= " permanently.";
                }
                if($old_ban_remaining != false) {
                    $staff_log .= "<br />$old_ban_remaining => {$this->system->time_remaining($ban_length*86400)}";
                }
                $this->staffLog(self::STAFF_LOG_MOD, $staff_log);

                //Add to user record
                $record_string = "Received a ";
                if($ban_length == self::PERM_BAN_VALUE) {
                    $record_string .= "permanent ";
                }
                else {
                    $record_string .= "{$ban_length} day ";
                }
                $record_string .= ucwords($ban_type) . " ban.";
                $this->addRecord($user_data['user_id'], $user_data['user_name'], self::RECORD_BAN_ISSUED,
                    $record_string, false);
                return true;
            }
            return false;
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
                throw new Exception("Player is not currently banned.");
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

        $this->system->query("UPDATE `users` SET `ban_data`='{$ban_data}' WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");
        if($this->system->db_last_affected_rows) {
            $this->staffLog(StaffManager::STAFF_LOG_HEAD_MOD,
                "($this->user_name} ({$this->user_id}) removed {$user_data['user_name']}\'s ({$user_data['user_id']}) "
                . $unban_string . ".");
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
        $this->system->query($query);
        if($this->system->db_last_affected_rows) {
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
        $ban_lengths = [
            1 => '1 Day',
            7 => '1 Week',
            30 => '1 Month',
            90 => '3 Months'
        ];
        switch($this->staff_level) {
            case self::STAFF_HEAD_MODERATOR:
                $ban_lengths = array_replace($ban_lengths, [
                    180 => '6 Months',
                    365 => '1 Year'
                ]);
                break;
            case self::STAFF_ADMINISTRATOR:
            case self::STAFF_HEAD_ADMINISTRATOR:
                $ban_lengths = array_replace($ban_lengths, [
                    545 => '1.5 Years',
                    self::PERM_BAN_VALUE => 'Permanent'
                ]);
                break;
        }
        return $ban_lengths;
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
        return match ($this->staff_level) {
            User::STAFF_MODERATOR, User::STAFF_HEAD_MODERATOR, User::STAFF_ADMINISTRATOR, User::STAFF_HEAD_ADMINISTRATOR => true,
            default => false,
        };
    }

    public function isHeadModerator(): bool {
        return match ($this->staff_level) {
            User::STAFF_HEAD_MODERATOR, User::STAFF_ADMINISTRATOR, User::STAFF_HEAD_ADMINISTRATOR => true,
            default => false,
        };
    }

    public function isContentAdmin(): bool {
        return match ($this->staff_level) {
            User::STAFF_CONTENT_ADMIN, User::STAFF_ADMINISTRATOR, User::STAFF_HEAD_ADMINISTRATOR => true,
            default => false,
        };
    }

    public function isUserAdmin(): bool {
        return match ($this->staff_level) {
            User::STAFF_ADMINISTRATOR, User::STAFF_HEAD_ADMINISTRATOR => true,
            default => false,
        };
    }

    public function isHeadAdmin(): bool {
        return $this->staff_level == User::STAFF_HEAD_ADMINISTRATOR;
    }

    public function hasAdminPanel(): bool {
        return $this->isContentAdmin() || $this->isUserAdmin() || $this->isHeadAdmin();
    }
}