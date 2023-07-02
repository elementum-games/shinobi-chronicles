<?php

class ReportManager {
    public User $player;
    public System $system;
    public bool $staff_connection;

    const REPORT_TYPE_PROFILE = 1;
    const REPORT_TYPE_PM = 2;
    const REPORT_TYPE_CHAT = 3;

    const VERDICT_UNHANDLED = 0;
    const VERDICT_GUILTY = 1;
    const VERDICT_NOT_GUILTY = 2;

    const MAX_NOTE_SIZE = 1000;

    public static array $report_verdicts = array (
        self::VERDICT_UNHANDLED => 'Unhandled',
        self::VERDICT_GUILTY => 'Guilty',
        self::VERDICT_NOT_GUILTY => 'Not Guilty'
    );

    public static array $report_types = array(
        self::REPORT_TYPE_PROFILE => 'Profile/Journal',
        self::REPORT_TYPE_PM => 'Private Message',
        self::REPORT_TYPE_CHAT => 'Chat Post'
    );
    public static array $report_reasons = array(
        'Spamming', 'Harassment', 'Explicit Language/Content'
    );

    public function __construct($system, $player, $staff_connection = false) {
        $this->player = $player;
        $this->system = $system;
        $this->staff_connection = $staff_connection;
    }

    /**
     * Pull report data based on report_id and staff permissions
     * @param $report_id
     * @param bool $debug
     * @return array|false|null
     */
    public function getReport($report_id, bool $debug = false):array|false|null {

        if(!$this->staff_connection) {
            if($debug) {
                echo "Not a staff connection...<br />";
            }
            return false;
        }
        if($debug) {
            echo "Staff connections...<br />";
        }

        $result = $this->system->db->query("SELECT * FROM `reports` WHERE `report_id`='{$report_id}' LIMIT 1");
        if($this->system->db->last_num_rows) {
            if($debug) {
                echo "Report is found...";
            }
            $report = $this->system->db->fetch($result);

            //Staff perm check
            if($report['staff_level'] > User::STAFF_NONE) {
                if($debug) {
                    echo "Checking staff perms...<br />";
                }
                switch($report['staff_level']) {
                    case User::STAFF_MODERATOR:
                        if($this->player->staff_manager->isHeadModerator() || $this->player->staff_manager->isUserAdmin()) {
                            return $report;
                        }
                        return false;
                    case User::STAFF_HEAD_MODERATOR:
                    case User::STAFF_CONTENT_ADMIN:
                        if($this->player->staff_manager->isUserAdmin()) {
                            return $report;
                        }
                        return false;
                    case User::STAFF_ADMINISTRATOR:
                    case User::STAFF_HEAD_ADMINISTRATOR:
                        if($this->player->staff_manager->isHeadAdmin()) {
                            return $report;
                        }
                        return false;
                    default:
                        return false;
                }
            }

            //Standard user report, return report
            if($debug) {
                echo "Standard report...<br />";
            }
            return $report;
        }
        if($debug) {
            echo "Report flat not found.<br />";
        }
        return false;
    }

    /**
     * Check if Chat/PM has been reported
     * @param $content_id
     * @param $report_type
     * @return bool
     */
    public function checkIfReported($content_id, $report_type):bool {
        //Failsafe, profiles can be reported at all times
        if($report_type == self::REPORT_TYPE_PROFILE) {
            return false;
        }
        $this->system->db->query(
            "SELECT `report_id` FROM `reports` WHERE `content_id`='$content_id' AND `report_type`='$report_type'"
        );
        if($this->system->db->last_num_rows) {
            return true;
        }
        return false;
    }

    /**
     * Inserts report into database
     * @param $report_type
     * @param $content_id
     * @param $content
     * @param $reported_user_id
     * @param $staff_level
     * @param $reason
     * @param $notes
     * @return bool
     */
    public function submitReport($report_type, $content_id, $content, $reported_user_id, $staff_level, $reason, $notes) {
        $this->system->db->query(
            "INSERT INTO `reports`
                (`report_type`, `content_id`, `content`, `user_id`, `reporter_id`, `staff_level`, 
                    `reason`, `notes`, `status`, `time`)
                VALUES
                ('$report_type', '$content_id', '$content', '$reported_user_id', '{$this->player->user_id}', '$staff_level', 
                    '$reason', '$notes', '" . self::VERDICT_UNHANDLED . "', '" . time() . "')
            "
        );

        if($this->system->db->last_insert_id) {
            return true;
        }
        return false;
    }

    /**
     * Query db for all active reports, based on staff perms
     * Setting notification to true will return a bool to determine if notification
     *      needs to be set for a staff member
     * @param false $notification
     * @return array|bool
     */
    public function getActiveReports(bool $notification = false):array|bool {
        $reports = [
            'reports' => [],
            'users' => []
        ];
        $query = "SELECT * FROM `reports` WHERE ";
        switch($this->player->staff_level) {
            case User::STAFF_HEAD_ADMINISTRATOR:
                break;
            case User::STAFF_ADMINISTRATOR:
                $query .= "`staff_level` < " . User::STAFF_ADMINISTRATOR . " AND ";
                break;
            case User::STAFF_HEAD_MODERATOR:
                $query .= "`staff_level` IN (" . User::STAFF_CONTENT_ADMIN . ', ' . User::STAFF_MODERATOR . ','
                    . User::STAFF_NONE . ") AND ";
                break;
            case User::STAFF_MODERATOR:
                $query .= "`staff_level` = " . User::STAFF_NONE . " AND ";
                break;
            default:
                return false;
        }
        $query .= "`status`=" . self::VERDICT_UNHANDLED;

        //Query reports
        $result = $this->system->db->query($query);
        if($this->system->db->last_num_rows) {
            if($notification) {
                return true;
            }

            //Pull active reports from DB and add to array, tracking user_ids
            $user_ids = [];
            while($report = $this->system->db->fetch($result)) {
                if(!in_array($report['user_id'], $user_ids)) {
                    $user_ids[] = $report['user_id'];
                }
                if(!in_array($report['reporter_id'], $user_ids)) {
                    $user_ids[] = $report['reporter_id'];
                }
                $reports['reports'][] = $report;
            }

            //Fetch usernames from db
            $user_names = [];
            $query_ids = implode(',', $user_ids);
            $result = $this->system->db->query(
                "SELECT `user_name`,`user_id` FROM `users` WHERE `user_id` IN ($query_ids)"
            );
            if($this->system->db->last_num_rows) {
                while($user = $this->system->db->fetch($result)) {
                    if(!isset($user_names[$user['user_id']])) {
                        $user_names[$user['user_id']] = $user['user_name'];
                    }
                }
            }

            //Set names to return array (use user_ids to set deleted accounts to default string
            foreach($user_ids as $id) {
                if(isset($user_names[$id])) {
                    $reports['users'][$id] = $user_names[$id];
                }
                else {
                    $reports['users'][$id] = 'Not Found';
                }
            }

        }
        return ($notification) ? false : $reports;
    }

    public function updateReportVerdict($report_id, $verdict) {
        if($this->staff_connection) {
            $report = $this->getReport($report_id);
            if(!$report) {
                return false;
            }
            $this->system->db->query("UPDATE `reports` SET `status`={$verdict}, 
                     `moderator_id`={$this->player->user_id} WHERE `report_id`={$report_id} LIMIT 1");
            if($this->system->db->last_affected_rows) {
                $log_type = ($report['status'] == self::VERDICT_UNHANDLED) ? StaffManager::STAFF_LOG_MOD : StaffManager::STAFF_LOG_HEAD_MOD;
                $log_data = "{$this->player->user_name}({$this->player->user_id}) has ";
                if($log_type == StaffManager::STAFF_LOG_MOD) {
                    $log_data .= "marked report #{$report_id} as ";
                }
                else {
                    $log_data .= "changed report#{$report_id} to ";
                }
                $log_data .= self::$report_verdicts[$verdict];
                $this->player->staff_manager->staffLog($log_type, $log_data);
                return true;
            }
            return false;
        }
    }
}
