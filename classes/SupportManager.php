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

    public static $requestTypeUserlevels = [
        'Game Question' => 'head_mod',
        'Account Problem' => 'head_mod',

        'Sword Feedback' => 'admin',
        'Content Text Issue' => 'admin',

        'Balance Feedback' => 'head_admin',

        'Appeal Ban' => 'head_admin',
        'Suggestion' => 'head_admin',
        'Report Bug' => 'head_admin',
        'Race Change' => 'head_admin',
        'Report Staff Member' => 'head_admin',
        'Misc. Request' => 'head_admin',
        'Mod Request' => 'head_admin',
    ];

    /** @var System $system */
    protected $system;

    public $user_id;
    public $admin;
    public $key = '';

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
    public function __construct($system, $user_id = 0, $admin = false) {
        $this->system = $system;
        $this->user_id = $user_id;
        $this->admin = $admin;
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
     * @param $userlevel
     * @param $type
     * @return bool
     */
    public function hasPermission($userlevel, $type) {
        if(!isset(self::$requestTypeUserlevels[$type])) {
            return true;
        }
        return $userlevel >= $this->system->STAFF_LEVELS[self::$requestTypeUserlevels[$type]];
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
    public function createSupport($ip_address, $email, $type, $subject, $details, $user_id, $name, $supportkey = null
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
              `user_name`
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
              '{$name}'
            )
        ");

        if($this->system->db_last_insert_id) {
            //$this->addSupportResponses($this->system->db_insert_id, $user_id, $name, $details, false);
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
        $result = $this->supportSearch(['supportkey' => $key], 1, ['created' => 'DESC']);
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
            WHERE `user_id`={$user_id} AND `open`=1 ORDER BY `open` DESC, `updated` DESC");
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
    public function fetchAllSupports($category = '', $limit = 100, $offset = 0) {
        $orderDirection = 'ASC';
        $query = "SELECT * FROM `Supports` ";
        switch($category) {
            case 'awaiting_admin':
                $query .= "WHERE `open`=1 AND `admin_response`=0";
                break;
            case 'awaiting_user':
                $query .= "WHERE `open`=1 AND `admin_response`=1";
                break;
            case 'closed':
                $query .= "WHERE `open`=0";
                $orderDirection = 'DESC';
                break;
        }
        $query .= " ORDER BY `updated` {$orderDirection} LIMIT {$offset},{$limit}";

        $supports = [];
        $result = $this->system->query($query);
        while($row = $this->system->dbFetch($result)) {
            $supports[] = $row;
        }

        return $supports;
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
                    switch($row['type']) {
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
    public function addSupportResponses($support_id, $userid, $username, $response, $ip_address, $update = true) {
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
            if($update) {
                $this->updateSupport($support_id);
            }
            return true;
        }
        else {
            return false;
        }
    }

    /**
     *
     *
     *
     * @param  int        $support_id
     * @return bool
     * @throws Exception
     */
    public function updateSupport($support_id) {
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
        if($this->admin && false) {
            if($support_data['character_id']) {
                $this->system->sendPM(System::$SYSTEM_USERID, $support_data['character_id'], 'Request Update',
                    "Your support request {$support_data['subject']} has been updated by an administrator. "
                    . "You can view it here:\r\n"
                    . $this->system->route('support', ['view_request' => $support_data['id']]));
            }
            else {
                $this->system->sendAccountMessage('System', $support_data['user_id'], 'support_updated',
                    'Support Updated',
                    "Your support request {$support_data['subject']} has been updated by an administrator. "
                    . "You can view it here:\r\n"
                    . $this->system->route('support', ['view_request' => $support_data['id']]));
            }
        }

        $this->system->query("UPDATE `support_request` SET
            `updated`='" . time() . "',
            `admin_response`='{$this->admin}'
        WHERE `support_id`='{$support_id}' LIMIT 1");
        if($this->system->db_last_affected_rows) {
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
    public function closeSupport($support_id, $inactive = false, $admin_name = false) {
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

        if(!$support) {
            throw new Exception("Invalid support data!");
        }
        if(!$support['open']) {
            throw new Exception("Support is already closed!");
        }

        $this->system->query("UPDATE `Supports` SET `open`=0 WHERE `id`={$support_id}");
        if($this->system->db_affected_rows) {
            if($inactive) {
                $this->addSupportResponses($support_id,
                    System::$SYSTEM_USERID, 'System', "[Closed for inactivity]"
                );
            }
            else {
                $this->addSupportResponses($support_id,
                    $this->user_id,
                    ($admin_name ? $admin_name : $support['username']),
                    "[Request Closed]"
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
            WHERE `support_id`='{$support_id}' ORDER BY `time` ASC");
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
        $result = $this->system->query("SELECT `id` FROM `Supports` WHERE `supportkey`='{$support_key}' ORDER BY `created` DESC LIMIT 1");
        if($this->system->db_num_rows) {
            return ($this->system->dbFetch($result)['id']);
        }
        return false;
    }

    public function autocloseSupports() {
        $timeRequired = time() - (self::$autoCloseDays * 86400);
        $result = $this->system->query("SELECT * FROM `Supports` 
            WHERE `admin_response`='1' AND `updated`<'{$timeRequired}'");
        $toClose = [];
        if($this->system->db_num_rows) {
            while($row = $this->system->dbFetch($result)) {
                $toClose[] = $row['id'];
            }
        }

        foreach($toClose as $key => $id) {
            $this->closeSupport($id, true);
        }
    }

    /**
     * @param $type
     * @return string
     */
    public static function getTypePriority($type) {
        switch($type) {
            case 'Account Problem':
            case 'Report Staff Member':
            case 'Appeal Ban':
            case 'Report Bug':
                $priority = 'high';
                break;
            case 'Misc. Request':
                $priority = 'low';
                break;
            default:
                $priority = 'reg';
                break;
        }

        return $priority;
    }
}