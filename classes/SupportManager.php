<?php

/**
 * Class SupportManager
 *
 * Note: Set admin to true to fetch/update supports without restriction. Leave it false and set user ID/key
 * to automatically restrict search/update of functions that care
 * to only supports matching that ID/key
 */
class SupportManager {
    public static $validationConstraints = [
        'subject' => [
            'min' => 5,
            'max' => 75,
        ],
        'message' => [
            'min' => 10,
            'max' => 2500,
        ],
    ];

    public static $TYPE_ACCOUNT_PROBLEM = 'Account Problem';
    public static $TYPE_GAME_QUESTION = 'Game Question';
    public static $TYPE_MISC = 'Misc. Request';
    public static $TYPE_APPEAL_BAN = 'Appeal Ban';
    public static $TYPE_REPORT_STAFF = 'Report Staff Member';
    public static $TYPE_CONTENT_TEXT = 'Content Text Issue';
    public static $TYPE_BALANCE = 'Balance Feedback';
    public static $TYPE_SUGGESTION = 'Suggestion';
    public static $TYPE_BUG = 'Report Bug';
    public static $TYPE_MOD_REQUEST = 'Mod Request';

    public static $PRIORITY_LOW = 'low';
    public static $PRIORITY_REG = 'reg';
    public static $PRIORITY_PREM = 'premium';
    public static $PRIORITY_HIGH = 'high';
    public static $PRIOIRTY_PREM_HIGH = 'premium_high';

    public static $strfString = '%m/%d/%y @ %I:%M:%S';

    public static $staffNotify = 86400 * 3;
    public static $autoClose = 86400 * 5;

    /** @var System $system */
    protected $system;

    public $user_id;
    public $support_level;
    public $admin;
    public $key = '';
    public $requestTypeUserlevels;
    public $requestPremiumCosts;

    /**
     * SupportManager constructor
     *
     * Set admin to true to fetch/update supports without restriction.
     *
     * Leave admin false and set user ID to automatically restrict every search/update to only supports
     * matching that ID unless fetched by key
     *
     * @param      $system
     * @param int  $user_id
     * @param bool $admin
     */
    public function __construct($system, $user_id = 0, $support_level = false, $admin = false) {
        $this->system = $system;
        $this->user_id = $user_id;
        $this->support_level = $support_level;
        $this->admin = $admin;

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

    /**** SUPPORT FUNCTIONS ****/

    /**
     * Returns support request types based on user level
     * @param int $userlevel
     * @return array
     */
    public function getSupportTypes($staffLevel) {
        if(!$staffLevel) {
            $types = [
                'Account Problem',
                'Report Bug',
                'Appeal Ban',
            ];
        }
        else {
            $types = [
                'Account Problem',
                'Report Bug',
                'Game Question',
                'Content Text Issue',
                'Balance Feedback',
                'Report Staff Member',
                'Appeal Ban',
                'Misc. Request',
                'Suggestion',
            ];
            if($staffLevel >= System::SC_MODERATOR) {
                $types[] = 'Mod Request';
            }
        }
        return $types;
    }

    /**
     * Returns true/false based on if userlevel has permission to access request type
     *
     * @param $type
     * @return bool
     */
    public function canProcess($type): bool
    {
        // Not set error, allow anybody to attempt to process
        if(!isset($this->requestTypeUserlevels[$type])) {
            return true;
        }

        if($this->support_level == User::SUPPORT_CONTENT_ONLY && $this->requestTypeUserlevels[$type] == User::SUPPORT_CONTENT_ONLY) {
            return true;
        }
        else if($this->support_level >= $this->requestTypeUserlevels[$type]) {
            return true;
        }

        return false;
    }

    /**
     * @param false $returnSqlString
     * @return array|string
     *
     * Returns array or sql string of supports that user can process
     * String returned must be used with an IN type mysql clause
     * String e.g.: ('Game Question', 'Misc. Request)
     * Parentheses and single quotes are included in the return
     */
    public function processTypes($returnSqlString = false) {
        $typeRestrictions = [
            self::$TYPE_GAME_QUESTION => User::SUPPORT_BASIC
        ];

        $canProcess = [];
        foreach($this->requestTypeUserlevels as $type=>$userlevel) {
            if($this->support_level == User::SUPPORT_CONTENT_ONLY && User::SUPPORT_CONTENT_ONLY == $userlevel) {
                $canProcess[] = $type;
            }
            else if($this->support_level != User::SUPPORT_CONTENT_ONLY && $this->support_level >= $userlevel) {
                $canProcess[] = $type;
            }
        }

        if(!$returnSqlString) {
            return $canProcess;
        }

        $string = "(";
        foreach($canProcess as $type) {
            $string .= "'{$type}', ";
        }
        return substr($string, 0, strlen($string)-2) . ")";
    }

    /**
     * @param string $ip_address
     * @param string $email
     * @param string $type
     * @param string $subject
     * @param string $details
     * @param int    $user_id
     * @param int    $character_id
     * @param        $name
     * @param null   $supportkey
     * @return bool
     */
    public function createSupport($ip_address, $email, $type, $subject, $details, $user_id, $name, $premium = false, $support_key = null
    ) {
        $this->system->query("INSERT INTO `support_request`
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
              '{$user_id}',
              '{$ip_address}', 
              '{$email}', 
              '{$type}', 
              '{$subject}',
              '{$details}',
              '1', 
              '0',
              '{$name}',
              '{$support_key}',
              '{$premium}'
            )
        ");

        if($this->system->db_last_insert_id) {
            return $this->system->db_last_insert_id;
        }
        return false;
    }

    /**
     * @param           $support_id
     * @param bool|int  $user_id Optional
     * @return bool|mixed
     */
    public function fetchSupportByID($support_id, $user_id = false) {
        // If not admin, restrict to user ID
        if($this->user_id && !$this->admin) {
            $user_id = $this->user_id;
        }

        $criteria = ['support_id' => $support_id];
        if($user_id !== false) {
            $criteria['user_id'] = $user_id;
        }

        $result = $this->supportSearch($criteria, 1);
        return ($result ? $result[0] : false);
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function fetchSupportByKey($key) {
        $result = $this->supportSearch(['support_key' => $key], 1, ['time' => 'DESC']);
        return ($result ? $result[0] : false);
    }

    /**
     * @param int $user_id optional, uses $this->user_id if not provided
     * @return array|bool
     */
    public function fetchUserSupports($user_id = 0) {
        // Quit if user is not admin and requesting a different user's supports
        if($user_id && $user_id != $this->user_id && !$this->admin) {
            return [];
        }

        // Use user's ID if this is not an admin load
        if(!$user_id && !$this->admin) {
            $user_id = $this->user_id;
        }

        $result = $this->system->query("SELECT * FROM `support_request` 
            WHERE `user_id`={$user_id} ORDER BY `open` DESC, `updated` DESC LIMIT 50");
        $supports = [];
        while($row = $this->system->db_fetch($result)) {
            $supports[] = $row;
        }

        return $supports;
    }

    /**
     * @param string $category
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function fetchAllSupports($category = '', $user_id = 0, $limit = 100, $offset = 0, $debug = false) {
        $canProcess = $this->processTypes(true);

        $orderDirection = 'ASC';
        $query = "SELECT * FROM `support_request` ";
        switch($category) {
            case 'awaiting_staff':
                $query .= "WHERE `open`=1 AND `admin_response`=0 AND `support_type` IN {$canProcess}";
                break;
            case 'awaiting_user':
                $query .= "WHERE `open`=1 AND `admin_response`=1 AND `support_type` IN {$canProcess}";
                break;
            case 'closed':
                $query .= "WHERE `open`=0 AND `support_type` IN {$canProcess}";
                $orderDirection = 'DESC';
                break;
            default:
                $query .= "WHERE `open`=1 AND `admin_response`=0 AND `support_type` IN {$canProcess}";
        }
        $query .= " ORDER BY `updated` {$orderDirection}, `premium` DESC LIMIT {$offset},{$limit}";

        if($debug) {
            echo $query;
        }

        $supports = [
            self::$PRIOIRTY_PREM_HIGH => [],
            self::$PRIORITY_HIGH => [],
            self::$PRIORITY_PREM => [],
            self::$PRIORITY_REG => [],
            self::$PRIORITY_LOW => []
        ];
        $result = $this->system->query($query);
        while($row = $this->system->db_fetch($result)) {
            $supports[self::getTypePriority($row['support_type'], $row['premium'])][] = $row;
        }

        return array_merge($supports[self::$PRIOIRTY_PREM_HIGH], $supports[self::$PRIORITY_HIGH],
            $supports[self::$PRIORITY_PREM], $supports[self::$PRIORITY_REG], $supports[self::$PRIORITY_LOW]);
    }

    /**
     * Runs a query based on variables, order and limit data provided.
     * !IMPORTANT! This will return an array of rows or false even if only one row is requested!
     *
     * !IMPORTANT! Normally this function will use column and value as equal
     * (e.g. 'open'=>1 ==> `$col`='$val'. If you would like a different comparison for any given column,
     * prepend the value with which comparison you would like to use and an underscore -
     * (e.g. ['open'=>'>_1'] ==> `$col` > '1').
     *
     * @param array    $variables
     * @param bool|int $limit
     * @param array    $order
     * @param bool|int $offset
     * @param bool     $count
     * @return array|bool
     */
    public function supportSearch($variables = [], $limit = false, $order = [], $offset = 0, $count = false) {
        if($count) {
            $query = "SELECT COUNT(*)";
        }
        else {
            $query = "SELECT * ";
        }

        $query .= "FROM `support_request`";

        if(!empty($variables)) {
            $query .= " WHERE ";
            foreach($variables as $col => $val) {
                $definedVals = ['>', '<', '='];
                if(in_array(substr($val, 0, 1), $definedVals)) {
                    $keys = explode('_', $val);
                    $comparison = $keys[0];
                    $value = $keys[1];
                    $query .= "`$col` {$comparison} '{$value}' AND ";
                }
                elseif(substr($val, 0, 2) == 'IN') {
                    $keys = explode('_', $val);
                    $value = $keys[1];
                    $query .= "`$col` IN {$value} AND ";
                }
                else {
                    $query .= "`$col`='{$val}' AND ";
                }
            }
            $query = substr($query, 0, strlen($query) - 4);
        }
        if(!empty($order)) {
            $query .= "ORDER BY ";
            foreach($order as $col => $val) {
                $query .= "`$col` $val, ";
            }
            $query = substr($query, 0, strlen($query) - 2) . " ";
        }
        if($limit) {
            $query .= ' LIMIT ' . $offset . ', ' . $limit;
        }

        $result = $this->system->query($query);
        if($this->system->db_last_num_rows) {
            if($count) {
                return $this->system->db_fetch($result)["COUNT(*)"];
            }
            if(!$this->admin) {
                $supports = [];
                while($row = $this->system->db_fetch($result)) {
                    $supports[] = $row;
                }
                return $supports;
            }
            else {
                $supports = [
                    'high' => [],
                    'reg' => [],
                    'low' => [],
                ];
                while($row = $this->system->db_fetch($result)) {
                    switch($row['support_type']) {
                        case 'Account Problem':
                        case 'Report Staff Member':
                        case 'Appeal Ban':
                            $priority = 'high';
                            break;
                        case 'Misc. Reqeust':
                            $priority = 'low';
                            break;
                        default:
                            $priority = 'reg';
                            break;
                    }

                    $supports[$priority][] = $row;
                }
                return array_merge($supports['high'], $supports['reg'], $supports['low']);
            }
        }
        return false;
    }

    /**
     * By default calls updateSupport which will handle notifying user if this was an admin update
     *
     * @param      $support_id
     * @param      $userid
     * @param      $username
     * @param      $response
     * @param bool $update
     * @return bool
     */
    public function addSupportResponses($support_id, $userid, $username, $response, $ip_address, $staff_level = 0, $update = true) {
        $this->system->query("INSERT INTO `support_request_responses`
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
              '{$userid}',
              '{$username}',
              '{$response}',
              '{$ip_address}'
            )
        ");

        if($this->system->db_last_insert_id) {
            if($update && $this->admin) {
                $this->updateSupport($support_id, $userid, $username, $staff_level);
            } else {
                $this->updateSupport($support_id);
            }
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @param $support_id
     * @return bool
     * Allows a user to assign their guest support to their account, should they want to follow up further
     * through their account.
     */
    public function assignGuestSupportToUser($support_id) {
        $data = $this->supportSearch(['support_id'=>$support_id], 1, ['time'=>'DESC'])[0];

        if(!$data) {
            return false;
        }
        if($data['user_id'] != 0) {
            //return false;
        }

        if($this->system->query("UPDATE `support_request` SET `user_id`='{$this->user_id}' WHERE `support_id`='{$support_id}' LIMIT 1")) {
            $this->addSupportResponses($support_id, $this->user_id, $data['user_name'], "Added to my account.", $_SERVER['REMOTE_ADDR']);
            return true;
        }
        return false;
    }

    /**
     *
     *
     *
     * @param  int        $support_id
     * @return bool
     * @throws Exception
     */
    public function updateSupport($support_id, $admin_id = 0, $admin_name = '', $staff_level=0) {
        // Validate support exists and belongs to user
        if($this->admin) {
            $support_data = $this->fetchSupportByID($support_id);
        }
        else {
            $support_data = $this->fetchSupportByID($support_id, $this->user_id);
        }
        if(!$support_data) {
            throw new Exception("Invalid support!");
        }

        // Notify user of update
        if($this->admin) {
            if($support_data['user_id']) {
                $this->system->send_pm($admin_id, $support_data['user_id'], 'Support Updated',
                    "Your support {$support_data['subject']} has been updated and can be viewed here "
                . $this->system->link . "support.php?support_id=" . $support_id, $staff_level);
            }
        }
        // Email if ticket submitted by guest
        if($support_data['user_id'] == 0) {
            // Make sure a name/administrator is placed in email
            $admin_name = ($admin_name == '') ? 'Administrator' : $admin_name;

            $subject = "Shinobi-Chronicles support request updated";
            $message = "Your support was updated by {$admin_name}. Click the link below to access your support: \r\n" .
                "{$this->system->link}support.php?support_key={$support_data['support_key']} \r\n" .
                "If the link does not work, your support key is: {$support_data['support_key']}";
            $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
            $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
            mail($support_data['email'], $subject, $message, $headers);
        }

        $query = "UPDATE `support_request` SET `updated`='" . time() . "',
            `admin_response`='{$this->admin}' WHERE `support_id`='{$support_id}' LIMIT 1";

        $this->system->query($query);
        if ($this->system->db_last_affected_rows) {
            return true;
        }
        return false;
    }

    /**
     * @param      $support_id
     * @param bool $inactive
     * @param bool $admin_name
     * @return bool
     * @throws Exception
     */
    public function closeSupport($support_id, $staff_level = 0, $inactive = false, $admin_name = false) {
        if($this->admin) {
            $support = $this->fetchSupportByID($support_id);
        }
        else if($this->user_id) {
            $support = $this->fetchSupportByID($support_id);
        }
        else if($this->key) {
            $support = $this->fetchSupportByID($support_id);
            if($support['supportkey'] != $this->key) {
                throw new Exception("Invalid support!");
            }
        }
        else {
            throw new Exception("No authorization level!");
        }

        if(!$support && !$inactive) {
            throw new Exception("Invalid support data!");
        }
        if(!$support['open'] && !$inactive) {
            throw new Exception("Support is already closed!");
        }

        $this->system->query("UPDATE `support_request` SET `open`=0 WHERE `support_id`={$support_id}");
        if($this->system->db_last_affected_rows) {
            if($inactive) {
                $this->addSupportResponses($support_id, 0, 'System', '[Closed for inactivity]', 0);
            }
            else {
                $this->addSupportResponses($support_id,
                    $this->user_id,
                    ($admin_name  ? $admin_name : $support['user_name']),
                    "[Request Closed]",
                    -1,
                    $staff_level
                );
            }
            return true;
        }
        return false;
    }

    /**
     * Fetches all responses associated with given support
     * @param $support_id
     * @return array|bool
     */
    public function fetchSupportResponses($support_id) {
        $result = $this->system->query("SELECT * FROM `support_request_responses` 
            WHERE `support_id`='{$support_id}' ORDER BY `time` DESC, `response_id` DESC");
        if($this->system->db_last_num_rows) {
            $responses = [];
            while($row = $this->system->db_fetch($result)) {
                $responses[] = $row;
            }
            return $responses;
        }
        return false;
    }

    /**
     * Returns support id via matched support key
     * @param string $support_key
     * @return bool|mixed
     */
    public function getSupportIdByKey($support_key) {
        $result = $this->system->query("SELECT `support_id` FROM `support_request` WHERE `support_key`='{$support_key}' ORDER BY `time` DESC LIMIT 1");
        if($this->system->db_last_num_rows) {
            return ($this->system->db_fetch($result)['support_id']);
        }
        return false;
    }

    public function autocloseSupports() {
        $timeRequired = time() - (self::$autoClose);
        $result = $this->system->query("SELECT * FROM `support_request` 
            WHERE `admin_response`='1' AND `open`='1' AND `updated`<'{$timeRequired}'");
        $toClose = [];
        if($this->system->db_last_num_rows) {
            while($row = $this->system->db_fetch($result)) {
                $toClose[] = $row['support_id'];
            }
        }

        foreach($toClose as $key => $id) {
            $this->closeSupport($id, 0, true);
        }
    }

    /**
     * @param $type
     * @return string
     */
    public static function getTypePriority($type, $premium = false) {
        switch($type) {
            case 'Account Problem':
            case 'Report Staff Member':
            case 'Appeal Ban':
            case 'Report Bug':
                $priority = self::$PRIORITY_HIGH;
                break;
            case 'Misc. Request':
                $priority = self::$PRIORITY_LOW;
                break;
            default:
                $priority = self::$PRIORITY_REG;
                break;
        }

        if($priority == 'high' && $premium) {
            $priority = self::$PRIOIRTY_PREM_HIGH;
        } elseif($premium) {
            $priority = self::$PRIORITY_PREM;
        }

        return $priority;
    }
}