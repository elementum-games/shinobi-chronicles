<?php
class Blacklist {
    public static array $blockable_staff_levels = [
        User::STAFF_NONE,
        User::STAFF_CONTENT_ADMIN
    ];

    public function __construct(
        public System $system,
        public int $user_id,
        public string $encodedBlacklist = "[]",
        public array $blacklist = array(),
        public bool $update = false
    ) {
        $this->loadBlacklist();
    }

    // Load list from db
    public function loadBlacklist(): void {
        $result = $this->system->db->query("SELECT `blocked_ids` FROM `blacklist` WHERE `user_id` = $this->user_id LIMIT 1");
        if($result->num_rows != 0) {
            $db_blacklist = $this->system->db->fetch($result)['blocked_ids'];
            $this->encodedBlacklist = $db_blacklist;
            $this->blacklist = json_decode($db_blacklist, true);

            // Legacy blacklist support
            if(!empty($this->blacklist)) {
                $first_key = array_keys($this->blacklist)[0];
                if(isset($this->blacklist[$first_key][$first_key])) {
                    $this->decodeLegacyBlacklist();
                }
            }
        }
        else {
            $this->blacklist = array();
            $this->setEncodedBlacklist();
        }
    }

    // Check if player has anybody blocked
    public function hasAnyUsersBlocked(): bool {
        return !empty($this->blacklist);
    }

    /**
     * @return int[] user IDs of accounts the player has blocked
     */
    public function blockedUserIds(bool $exclude_staff = false): array {
        $blocked_user_ids = array_keys($this->blacklist);

        if(!$exclude_staff) {
            return $blocked_user_ids;
        }
        if(count($blocked_user_ids) <= 0) {
            return $blocked_user_ids;
        }

        $result = $this->system->db->query("
            SELECT `user_id` FROM `users` 
            WHERE `user_id` IN (" . implode(",", $blocked_user_ids) . ")
            AND `staff_level` IN (" . implode(",", self::$blockable_staff_levels) . ")
        ");
        return array_map(function($user_record) {
            return $user_record['user_id'];
        }, $this->system->db->fetch_all($result));
    }

    // Check if user is blocked by id
    public function userBlocked(int $user_id): bool {
        return isset($this->blacklist[$user_id]);
    }

    // Legacy chat support
    public function userBlockedByName(string $user_name): bool {
        foreach($this->blacklist as $id => $user_data) {
            if($user_data['user_name'] == $user_name) {
                return true;
            }
        }
        return false;
    }

    // Add name
    public function addUser(int $user_id, string $user_name, int $staff_level): void {
        $this->blacklist[$user_id] = [
            'user_name' => $user_name,
            'staff_level' => $staff_level,
        ];
        $this->setEncodedBlacklist();
    }

    // Remove name
    public function removeName(int $user_id): void {
        unset($this->blacklist[$user_id]);
        $this->setEncodedBlacklist();
    }

    // Returns name based on id
    public function getBlockedUsername(int $user_id): string {
        if($this->userBlocked($user_id)) {
            return $this->blacklist[$user_id]['user_name'];
        }
        return "NameNotFound";
    }

    // Convert legacy blacklist to current standard
    public function decodeLegacyBlacklist(): void {
        $new_blacklist = array();
        foreach($this->blacklist as $id => $data) {
            foreach($data as $user_id => $user_data) {
                $new_blacklist[$user_id] = array(
                    "user_name" => $user_data['user_name'],
                    "staff_level" => $user_data['staff_level']
                );
            }
        }
        $this->blacklist = $new_blacklist;
        $this->setEncodedBlacklist();
    }

    // Generate settings page list
    public function generateSettingsList(string $self_link): string {
        if(!empty($this->blacklist)) {
            $return = "";
            foreach ($this->blacklist as $user_id => $user_data) {
                $return .= "<a href='{$this->system->router->links['members']}&user={$user_data['user_name']}'>{$user_data['user_name']}</a><sup>(<a href='$self_link&blacklist_remove=$user_id'>x</a>)</sup>,";
            }
            return substr($return, 0, strlen($return)-1);
        }
        return "No blacklist!";
    }

    // Prep blacklist for storage in database - call whenever any changes are made
    private function setEncodedBlacklist(): void {
        $this->encodedBlacklist = json_encode($this->blacklist);
        $this->update = true;
    }

    // Update blacklist
    public function updateData() {
        $this->system->db->query("UPDATE `blacklist` SET `blocked_ids`='$this->encodedBlacklist' WHERE `user_id` = $this->user_id LIMIT 1");
    }
}