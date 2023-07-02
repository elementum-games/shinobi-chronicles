<?php

require_once __DIR__ . '/inbox/Inbox.php';

class SupportManager {
    public int $user_id;
    public string $user_name;
    public int $user_support_level;
    public string $ip_address;
    public bool $staff;

    public array $requestTypeUserlevels;
    public array $requestPremiumCosts;

    public static bool $debug = false; //  Warning this flag will display A LOT of debugging data as many functions ise it
    // and they are not limited by post-cursory flags.

    public static array $validationConstraints = [
        'subject' => [
            'min' => 5,
            'max' => 75,
        ],
        'message' => [
            'min' => 10,
            'max' => 2500,
        ],
    ];

    public static string $TYPE_ACCOUNT_PROBLEM = 'Account Problem';
    public static string $TYPE_GAME_QUESTION = 'Game Question';
    public static string $TYPE_MISC = 'Misc. Request';
    public static string $TYPE_APPEAL_BAN = 'Appeal Ban';
    public static string $TYPE_REPORT_STAFF = 'Report Staff Member';
    public static string $TYPE_CONTENT_TEXT = 'Content Text Issue';
    public static string $TYPE_BALANCE = 'Balance Feedback';
    public static string $TYPE_SUGGESTION = 'Suggestion';
    public static string $TYPE_BUG = 'Report Bug';
    public static string $TYPE_MOD_REQUEST = 'Mod Request';

    public static string $PRIORITY_LOW = 'low';
    public static string $PRIORITY_REG = 'reg';
    public static string $PRIORITY_PREM = 'premium';
    public static string $PRIORITY_HIGH = 'high';
    public static string $PRIORITY_PREM_HIGH = 'premium_high';

    public static string $strfString = '%m/%d/%y @ %I:%M:%S';

    public static $autoClose = 86400 * 5;

    private $user;
    private System $system;

    /**
     * @param $system
     * @param false|User $user
     * @param bool $staff
     */
    public function __construct($system, $user = false, $staff = false) {
        $this->system = $system;
        $this->user = $user;

        $this->user_support_level = ($this->user) ? $this->user->support_level : false;
        $this->user_id = ($this->user) ? $this->user->user_id : 0;
        $this->ip_address = ($this->user) ? $this->user->user_id : $_SERVER['REMOTE_ADDR'];

        // Only allow a staff connection if a user is defined
        $this->staff = ($this->user) ? $staff : false;

        $this->requestTypeUserlevels = [
            self::$TYPE_GAME_QUESTION => User::SUPPORT_BASIC,
            self::$TYPE_MISC => User::SUPPORT_INTERMEDIATE,
            self::$TYPE_APPEAL_BAN => User::SUPPORT_SUPERVISOR,
            self::$TYPE_ACCOUNT_PROBLEM => User::SUPPORT_SUPERVISOR,
            self::$TYPE_REPORT_STAFF => User::SUPPORT_SUPERVISOR,
            self::$TYPE_CONTENT_TEXT => User::SUPPORT_CONTENT_ONLY,
            self::$TYPE_BALANCE => User::SUPPORT_CONTENT_ONLY,
            self::$TYPE_SUGGESTION => User::SUPPORT_CONTENT_ONLY,
            self::$TYPE_BUG => User::SUPPORT_ADMIN,
            self::$TYPE_MOD_REQUEST => User::SUPPORT_ADMIN,
        ];

        $this->requestPremiumCosts = [
            self::$TYPE_GAME_QUESTION => 10,
            self::$TYPE_MISC => 10,
            self::$TYPE_APPEAL_BAN => 5,
            self::$TYPE_ACCOUNT_PROBLEM => 5,
            self::$TYPE_REPORT_STAFF => 0,
            self::$TYPE_CONTENT_TEXT => 5,
            self::$TYPE_BALANCE => 5,
            self::$TYPE_SUGGESTION => 5,
            self::$TYPE_BUG => 0,
            self::$TYPE_MOD_REQUEST => 10,
        ];
    }

    public function getSupportTypes() {
        if($this->user == false) {
            $types = [
                self::$TYPE_ACCOUNT_PROBLEM,
                self::$TYPE_BUG,
                self::$TYPE_APPEAL_BAN,
            ];
        }
        else {
            $types = [
                self::$TYPE_ACCOUNT_PROBLEM,
                self::$TYPE_BUG,
                self::$TYPE_GAME_QUESTION,
                self::$TYPE_CONTENT_TEXT,
                self::$TYPE_BALANCE,
                self::$TYPE_REPORT_STAFF,
                self::$TYPE_APPEAL_BAN,
                self::$TYPE_SUGGESTION,
                self::$TYPE_MISC,
            ];

            if($this->user->isModerator()) {
                $types[] = self::$TYPE_MOD_REQUEST;
            }
        }
        return $types;
    }

    public function canProcess($type) {
        // Support type not set, allow anyone to process
        if(!isset($this->requestTypeUserlevels[$type])) {
            return true;
        }

        if($this->user_support_level == User::SUPPORT_CONTENT_ONLY) {
            if($this->requestTypeUserlevels[$type] == User::SUPPORT_CONTENT_ONLY) {
                return true;
            }
        }
        else {
            if($this->user_support_level >= $this->requestTypeUserlevels[$type]) {
                return true;
            }
        }

        return false;
    }

    public function processTypes($returnSqlString = false) {
        $processArr = [];
        $processStr = "(";

        foreach($this->requestTypeUserlevels as $type=>$support_level_req) {
            if($this->canProcess($type)) {
                /**** DEBUG ****/
                if(self::$debug) {
                    echo "S_SL: {$this->user_support_level} can process $type | $support_level_req<br />";
                }
                $processArr[] = $type;
                $processStr .= "'{$type}', ";
            }
            else {
                /**** DEBUG ****/
                if(self::$debug) {
                    echo "S_SL: {$this->user_support_level} can not process $type | $support_level_req<br />";
                }
            }
        }

        // Complete string
        $processStr = substr($processStr, 0, strlen($processStr)-2) . ")";

        if($returnSqlString) {
            return $processStr;
        }
        return $processArr;
    }

    public function createSupport($user_name, $support_type, $subject, $message, $premium = false, $email = '', $support_key = '') {

        $this->system->db->query(
            "INSERT INTO `support_request`
                (
                    `time`, 
                  `updated`, 
                  `user_id`, 
                  `ip_address`, 
                  `email`, 
                  `support_type`, 
                  `subject`,
                  `message`,
                  `open`, 
                  `admin_response`,
                  `user_name`,
                  `support_key`,
                  `premium`
                )
            VALUES
                (
                    '" . time() . "',
                    '" . time() . "',
                    '{$this->user_id}',
                    '{$this->ip_address}',
                    '{$email}',
                    '{$support_type}',
                    '{$subject}',
                    '{$message}',
                    '1',
                    '0',
                    '{$user_name}',
                    '{$support_key}',
                    '{$premium}'
                )
            "
        );

        if($this->system->db->last_insert_id) {
            return $this->system->db->last_insert_id;
        }
        return false;
    }

    public function getTypePriority($type, $premium = false) {
        switch($type) {
            case self::$TYPE_ACCOUNT_PROBLEM:
            case self::$TYPE_REPORT_STAFF:
            case self::$TYPE_APPEAL_BAN:
            case self::$TYPE_BUG:
                $priority = self::$PRIORITY_HIGH;
                break;
            case self::$TYPE_MISC:
                $priority = self::$PRIORITY_LOW;
                break;
            default:
                $priority = self::$PRIORITY_REG;
        }

        if($premium) {
            $priority = ($priority == self::$PRIORITY_HIGH) ? self::$PRIORITY_PREM_HIGH : self::$PRIORITY_PREM;
        }

        return $priority;
    }

    public function supportSearch($variables = [], $limit = false, $order = [], $offset = 0, $count = false) {
        if($count) {
            $query = "SELECT COUNT(*) ";
        }
        else {
            $query = "SELECT * ";
        }

        $query .= "FROM `support_request`";

        // Restrict search
        if(!empty($variables)) {
            $query .= " WHERE ";
            foreach($variables as $col => $val) {
                $definedVals = ['<', '>', '='];
                if(in_array(substr($val, 0, 1), $definedVals)) {
                    $keys = explode('_', $val);
                    $comparison = $this->system->db->clean($keys[0]);
                    $value = $this->system->db->clean($keys[1]);
                    $query .= "`$col` {$comparison} '{$value}' AND ";
                }
                elseif(substr($val, 0, 2) == 'IN') {
                    $keys = explode('_', $val);
                    $value = $keys[1];
                    $query .= "`$col` IN {$value} AND ";
                }
                else {
                    $query .= "`$col` = '{$val}' AND ";
                }
            }
            $query = substr($query, 0, strlen($query) - 4) . " ";
        }

        // Order search
        if(!empty($order)) {
            $query .= " ORDER BY ";
            foreach($order as $col => $dir) {
                $query .= "`$col` $dir, ";
            }
            $query = substr($query, 0, strlen($query) - 2) . " ";
        }

        // Limit search
        if($limit) {
            $query .= " LIMIT " . $offset . ', ' . $limit;
        }

        /*** DEBUG ***/
        if(self::$debug) {
            echo $query;
        }

        $result = $this->system->db->query($query);
        if($this->system->db->last_num_rows) {
            if($count) {
                return $this->system->db->fetch($result)["COUNT(*)"];
            }

            if(!$this->staff) {
                $supports = [];
                while($row = $this->system->db->fetch($result)) {
                    $supports[] = $row;
                }
                return $supports;
            }
            else {
                $supports = [
                    self::$PRIORITY_PREM_HIGH => [],
                    self::$PRIORITY_HIGH => [],
                    self::$PRIORITY_PREM => [],
                    self::$PRIORITY_REG => [],
                    self::$PRIORITY_LOW => [],
                ];

                // Prioritize supports
                while($row = $this->system->db->fetch($result)) {
                    $supports[$this->getTypePriority($row['support_type'], $row['premium'])][] = $row;
                }

                return array_merge($supports[self::$PRIORITY_PREM_HIGH], $supports[self::$PRIORITY_HIGH],
                    $supports[self::$PRIORITY_PREM], $supports[self::$PRIORITY_REG], $supports[self::$PRIORITY_LOW]);
            }
        }
        return false;
    }

    public function fetchAllSupports($category = '', $limit = 100, $offset = 0, $order = 'ASC') {
        if(!$this->staff) {
            return false;
        }

        switch($category) {
            case 'awaiting_staff':
                $criteria = [
                    'open' => 1,
                    'admin_response' => 0,
                    'support_type' => 'IN_' . $this->processTypes(true),
                ];
                break;
            case 'awaiting_user':
                $criteria = [
                    'open' => 1,
                    'admin_response' => 1,
                    'support_type' => 'IN_' . $this->processTypes(true),
                ];
                break;
            case 'closed':
                $criteria = [
                    'open' => 0,
                    'support_type' => 'IN_' . $this->processTypes(true),
                ];
                break;
            default:
                $criteria = [
                    'open' => 1,
                    'admin_response' => 0,
                    'support_type' => 'IN_' . $this->processTypes(true)
                ];
        }

        return $this->supportSearch($criteria, $limit, ['updated' => $order], $offset);
    }

    public function fetchUserSupports() {
        if(!$this->user) {
            return [];
        }

        $supports = $this->supportSearch(['user_id'=>$this->user_id], 50, ['open'=>'DESC', 'updated'=>'DESC']);

        return ($supports ?? []);
    }

    public function fetchSupportByID($support_id, $forceGuest = false) {
        $support_id = (int) $support_id;

        $criteria = ['support_id' => $support_id];

        //If not staff, restrict to user id
        if(!$this->staff && !$forceGuest) {
            $criteria['user_id'] = $this->user_id;
        }

        $result = $this->supportSearch($criteria, 1);
        return($result ? $result[0] : false);
    }

    public function fetchSupportResponses($support_id) {
        $result = $this->system->db->query(
            "SELECT * FROM `support_request_responses` 
                WHERE `support_id`='{$support_id}' ORDER BY `time` DESC, `response_id` DESC"
        );
        if($this->system->db->last_num_rows) {
            $responses = [];
            while($row = $this->system->db->fetch($result)) {
                $responses[] = $row;
            }
            return $responses;
        }
        return false;
    }

    public function updateSupport($support_id) {
        $support_data = $this->fetchSupportByID($support_id);

        if(!$support_data) {
            throw new RuntimeException("Invalid support!");
        }

        // Notify user of update
        if($this->staff) {
            if($support_data['user_id']) {
                $message = "Your support {$support_data['subject']} has been updated and can be viewed here <a href=\'" .
                    $this->system->router->base_url . "support.php?support_id=" . $support_id . "\'>" .
                    "{$this->system->router->base_url}support.php?support_id=$support_id</a>";
                Inbox::sendAlert($this->system, Inbox::ALERT_SUPPORT_REQUEST_UPDATED, $this->user_id, $support_data['user_id'], $message);
            }
        }
        // Email if ticket submitted by guest
        if($support_data['user_id'] == 0 && $this->staff) {
            // Make sure a name/administrator is placed in email
            $admin_name = ($this->user) ? $this->user->user_name : 'Administrator';

            $subject = "Shinobi-Chronicles support request updated";
            $message = "Your support was updated by {$admin_name}. Click the link below to access your support: \r\n" .
                "{$this->system->router->base_url}support.php?support_key={$support_data['support_key']} \r\n" .
                "If the link does not work, your support key is: {$support_data['support_key']}";
            $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
            $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
            mail($support_data['email'], $subject, $message, $headers);
        }

        $query = "UPDATE `support_request` SET `updated`='" . time() . "',
            `admin_response`='{$this->staff}' WHERE `support_id`='{$support_id}' LIMIT 1";

        if($this->system->db->query($query)) {
            return true;
        }
        return false;
    }

    public function addSupportResponse($support_id, $user_name, $response, $update = true) {
        $this->system->db->query(
            "INSERT INTO `support_request_responses`
                (
                    `support_id`,
                    `time`,
                    `user_id`,
                    `user_name`,
                    `message`,
                    `ip_address`
                )
            VALUES
                (
                    '{$support_id}',
                    '" . time() . "',
                    '{$this->user_id}',
                    '{$user_name}',
                    '{$response}',
                    '{$this->ip_address}'
                )
            "
        );

        if($this->system->db->last_insert_id) {
            if($this->staff && $update) {
                $this->updateSupport($support_id);
            }
            else {
                $this->updateSupport($support_id);
            }
            return true;
        }
        return false;
    }

    public function closeSupport($support_id, $inactive = false) {
        $support = $this->fetchSupportByID($support_id);

        // Support not found
        if(!$support) {
            throw new RuntimeException("Invalid support data!");
        }
        // Support does not belong to user and user is not staff
        if($support['user_id'] != $this->user_id && !$this->staff) {
            throw new RuntimeException("Not authorized to view this support!");
        }
        if(!$support['open'] && !$inactive) {
            throw new RuntimeException("Support already closed!");
        }

        $this->system->db->query("UPDATE `support_request` SET `open`=0 WHERE `support_id`={$support_id} LIMIT 1");
        if($this->system->db->last_affected_rows) {
            if($inactive) {
                $this->addSupportResponse($support_id, 'System', '[Closed fo inactivity]', false);
            }
            else {
                $this->addSupportResponse($support_id, $this->user->user_name, '[Request Closed]');
            }
            return true;
        }
        return false;
    }

    public function openSupport($support_id) {
        $support = $this->fetchSupportByID($support_id);

        // Support not found
        if(!$support) {
            throw new RuntimeException("Invalid support data!");
        }
        // Support does not belong to user and user is not staff
        if($support['user_id'] != $this->user_id && !$this->staff) {
            throw new RuntimeException("Not authorized to view this support!");
        }

        $this->system->db->query("UPDATE `support_request` SET `open`=1 WHERE `support_id`={$support_id} LIMIT 1");
        if($this->system->db->last_affected_rows) {
                $this->addSupportResponse($support_id, $this->user->user_name, '[Request Re-opened]');
            return true;
        }
        return false;
    }

    public function getSupportIdByKey($support_key) {
        $result = $this->system->db->query(
            "SELECT `support_id` FROM `support_request` WHERE `support_key`='{$support_key}' ORDER BY `time` DESC LIMIT 1"
        );
        if($this->system->db->last_num_rows) {
            return ($this->system->db->fetch($result)['support_id']);
        }
        return false;
    }

    public function autocloseSupports() {
        $timeRequired = time() - (self::$autoClose);
        $result = $this->system->db->query(
            "SELECT * FROM `support_request` 
                WHERE `admin_response`='1' AND `open`='1' AND `updated`<'{$timeRequired}'"
        );
        $toClose = [];
        if($this->system->db->last_num_rows) {
            while($row = $this->system->db->fetch($result)) {
                $toClose[] = $row['support_id'];
            }
        }

        foreach($toClose as $key => $id) {
            $this->closeSupport($id, true);
        }
    }

    /**** GUEST SUPPORT FUNCTIONS ****/

    public function fetchSupportByKey($key, $email) {
        $result = $this->supportSearch(['support_key'=>$key, 'email'=>$email], 1, ['time'=>'DESC']);
        return ($result ? $result[0] : false);
    }

    public function assignGuestSupportToUser($support_id) {
        $data = $this->fetchSupportByID($support_id, true);

        // User must be set
        if(!$this->user) {
            throw new RuntimeException("No user to assign to!");
        }
        // Request not found or already assigned
        if(!$data || $data['user_id'] != 0) {
            var_dump($data);
            throw new RuntimeException("User already assigned!");
        }

        $this->system->db->query(
            "UPDATE `support_request` SET `user_id`='{$this->user_id}', `user_name`='{$this->user->user_name}' WHERE `support_id`='{$support_id}' LIMIT 1"
        );

        if($this->system->db->last_affected_rows) {
            $this->addSupportResponse($support_id, $this->user->user_name, "I have added this guest support to my Account.");
            return true;
        }
        return false;
    }
}