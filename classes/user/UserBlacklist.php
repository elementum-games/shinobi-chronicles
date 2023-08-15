<?php
class UserBlacklist {
    public function __construct(
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

    public function loadList() {

    }
    public function loadBlacklistData(): void {
        $this->blacklist = json_decode($this->blacklist_data, true);
    }

    public function setBlacklistData(array $blacklist): void {
        $this->blacklist = $blacklist;
        $this->dbEncode();
    }

    public function dbEncode(): void {
        $this->blacklist_data = json_encode($this->blacklist);
    }

    public static function fromDb(string $blacklist_data): UserBlacklist {
        $blacklist = new UserBlacklist(
            blacklist_data: $blacklist_data
        );
        $blacklist->loadBlacklistData();
        return $blacklist;
    }
}