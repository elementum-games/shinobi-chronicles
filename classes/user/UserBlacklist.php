<?php
class UserBlacklist {
    public function __construct(
        public int $user_id,
        public string $blacklist_data = '',
        public array $blacklist = array(),
        public bool $update = false
    ) {}

    public function userBlocked(int $target_id) {
        if(array_key_exists($target_id, $this->blacklist)) {
            return true;
        }
        return false;
    }

    public function userBlockedByName(string $target_name) {
        foreach($this->blacklist as $id => $data) {
            if(strtolower($data['user_name']) == strtolower($target_name)) {
                return true;
            }
        }
        return false;
    }

    public function addUser(int $user_id, array $blacklist_user) {
        $this->blacklist[$user_id] = $blacklist_data;
        $this->dbEncode();
        $this->update = true;
    }

    public function removeUser(int $user_id) {
        unset($this->blacklist[$user_id]);
        $this->update = true;
    }

    public function hasUsersBlocked() {
        return !empty($this->blacklist);
    }

    public function getBlacklistArray() {
        return $this->blacklist;
    }
    
    public function loadBlacklistData(): void {
        $this->blacklist = json_decode($this->blacklist_data, true);
        // Legacy blacklist support
        if(!empty($this-blacklist)) {
            $new_blacklist = null;
            foreach($this->blacklist as $id => $user_data) {
                if(!isset($user_data['user_name'])) {
                    $this->update = true;
                    $new_blacklist[$id] = $user_data[$id];
                }
            }
        }
        if($this->update) {
            $this->blacklist = $new_blacklist;
            $this->dbEncode();
        }
    }

    public function dbEncode(): void {
        $this->blacklist_data = json_encode($this->blacklist);
    }

    public function updateData(): void {
        $this->system->db->query(
            "UPDATE `blacklist` SET `blocked_ids`='{$this->blacklist_data}' WHERE `user_id`='{$this->user_id}' LIMIT 1"
        );
    }

    public function createBlacklist(): void {
        $this->system->db->query(
            query: "INSERT INTO `blacklist` (`user_id`, `blocked_ids`) VALUES ('{$this->user_id}', '{$this->blacklist_data}')"
        );
    }

    public static function fromDb(int $user_id, string $blacklist_data): UserBlacklist {
        $blacklist = new UserBlacklist(
            user_id: $user_id,
            blacklist_data: $blacklist_data
        );
        $blacklist->loadBlacklistData();
        return $blacklist;
    }
}