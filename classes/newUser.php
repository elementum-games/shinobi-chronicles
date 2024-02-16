<?php
require_once __DIR__ . "/ForbiddenSeal.php";
require_once __DIR__ . "/StaffManager.php";
class newUser {
    const ENTITY_TYPE = 'U';

    const UPDATE_FULL = 'full';
    const UPDATE_PASSWORD = 'password';
    const UPDATE_EMAIL = 'email';
    const UPDATE_LOGIN = 'login';
    public function __construct(
        private System $system,
        public readonly bool $read_only,

        public readonly int $user_id,
        public string $user_name,
        private string $password,
        public string $email,

        private int $premium_credits,
        private int $premium_credits_purchased,

        public bool $user_verified,
        public ForbiddenSeal $forbidden_seal,
        public readonly int $staff_level,
        public StaffManager $staff_manager,

        public int $last_login,
        public int $last_login_attempt,
        public int $failed_logins
    ){}

    // Password & email
    public static function verify_password(string $password, string $password_hash) {
        return self::verify_password($password, $password_hash);
    }
    public function verifyPassword(string $password): bool {
        return self::verify_password($password, $this->password);
    }
    public function updatePassword(string $new_password): void {
        $this->password = $new_password;
        $this->update(UPDATE: self::UPDATE_PASSWORD);
    }
    public function updateEmail(string $new_email) {
        $this->email = $new_email;
        $this->update(self::UPDATE_EMAIL);
    }


    // Update data
    public function update($UPDATE = self::UPDATE_FULL): void {
        $query = "";
        match($UPDATE) {
            self::UPDATE_FULL => $query .=
                "`user_name`='{$this->user_name}',
                `user_verified`='{$this->user_verified}',
                `forbidden_seal`=' " . $this->forbidden_seal->dbEncode() . "',
                `staff_level`='{$this->staff_level}',
                `last_login`='{$this->last_login}',
                `last_login_attempt`='{$this->last_login_attempt}',
                `failed_logins`='{$this->failed_logins}',
                `premium_credits`='{$this->premium_credits}',
                `premium_credits_purchased`='{$this->premium_credits_purchased}'",
            self::UPDATE_LOGIN => $query .=
                "`last_login`='" . time() . "',
                `last_login_attempt`='{$this->last_login_attempt}',
                `failed_logins`='{$this->last_login}'",
            self::UPDATE_PASSWORD => $query .= "`password`='{$this->password}'",
            self::UPDATE_EMAIL => $query .= "`email`='{$this->email}'"
        };

        $this->system->db->query("UPDATE `users` SET " . $query . " WHERE `user_id`='{$this->user_id}' LIMIT 1");
    }

    // Load users
    public static function loadUser(System $system, array $user_data, bool $read_only): newUser {
        // Format seal data
        if(is_null($user_data['forbidden_seal'])) {
            $user_data['forbidden_seal'] = array(
                'level' => 0,
                'time' => null,
            );
        }
        else {
            $user_data['forbidden_seal'] = json_decode($user_data['forbidden_seal'], associative: true);
        }

        return new newUser(
            system: $system,
            read_only: $read_only,
            user_id: $user_data['user_id'],
            user_name: $user_data['user_name'],
            password: $user_data['password'],
            email: $user_data['email'],
            premium_credits: $user_data['premium_credits'],
            premium_credits_purchased: $user_data['premium_credits_purchased'],
            user_verified: (bool) $user_data['user_verified'],
            forbidden_seal: ForbiddenSeal::fromDb(
                system: $system,
                seal_level: $user_data['forbidden_seal']['level'],
                seal_end_time: $user_data['forbidden_seal']['time']
            ),
            staff_level: $user_data['staff_level'],
            staff_manager: new StaffManager(
                system: $system,
                user_id: $user_data['user_id'],
                user_name: $user_data['user_name'],
                staff_level: $user_data['staff_level'],
                support_level: $user_data['support_level']
            ),

            last_login: $user_data['last_login'],
            last_login_attempt: $user_data['last_login_attempt'],
            failed_logins: $user_data['failed_logins']
        );
    }
    public static function findUserFromId(System $system, int $user_id, bool $read_only): ?array {
        $query = "SELECT * FROM `users` WHERE `user_id`='$user_id' LIMIT 1";
        if(!$read_only) {
            $query .= " FOR UPDATE";
        }

        // Query db for user
        $result = $system->db->query($query);
        // User not found
        if(!$system->db->last_num_rows) {
            return null;
        }
        // Return user data
        return $system->db->fetch($result);
    }
    public static function findUserFromUsername(System $system, string $user_name, bool $read_only): ?array {
        $query = "SELECT * FROM `users` WHERE `user_name`='$user_name' LIMIT 1";
        if(!$read_only) {
            $query .= " FOR UPDATE";
        }

        // Query db for user
        $result = $system->db->query($query);
        // User not found
        if(!$system->db->last_num_rows) {
            return null;
        }
        // Return user data
        return $system->db->fetch($result);
    }
    public static function loadFromId(System $system, int $user_id, bool $read_only = false): newUser {
        $user_data = self::findUserFromId(system: $system, user_id: $user_id);
        // User not found
        if(!$user_data) {
            throw new RuntimeException("User not found!");
        }
        return self::loadUser(
            system: $system,
            user_data: $user_data,
            read_only: $read_only
        );
    }
    public static function loadFromUsername(System $system, string $user_name, bool $read_only = false): newUser {
        $user_data = self::findUserFromUsername(
            system: $system,
            user_name: $user_name,
            read_only: $read_only
        );

        // User not found
        if(!$user_data) {
            throw new RuntimeException("User not found!");
        }

        return self::loadUser(
            system: $system,
            user_data: $user_data,
            read_only: $read_only
        );
    }
}