<?php

class LoginManager {
    public function __construct(
        public System $system,
        public string $login_message_text = "",
        public string $login_error_text = "",
        public bool $login_user_not_active = false,
        public string $reset_error_text = "",
        public string $initial_home_view = "none",
        public ?array $home_links = null,

        public string $register_error_text = "",
        public array $register_prefill = array(),

        public int $min_username_length = User::MIN_NAME_LENGTH,
        public int $max_username_length = User::MAX_NAME_LENGTH,
        public int $min_password_length = User::MIN_PASSWORD_LENGTH
    ){}

    public function loadHomeLinks(): void {
        $this->home_links = array(
            'news_api' => $this->system->router->api_links['news'],
            'logout' => $this->system->router->base_url . "?logout=1",
            'profile' => $this->system->router->getUrl('profile'),
            'github' => $this->system->router->links['github'],
            'discord' => $this->system->router->links['discord'],
            'support' => $this->system->router->base_url . "support.php",
        );
    }
    public static function loadLoginManager(System $system): LoginManager {
        $loginManager = new LoginManager(system: $system);
        $loginManager->loadHomeLinks();
        return $loginManager;
    }

    public static function logout(System $system): void {
        $_SESSION = array();
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: {$system->router->base_url}");
        exit;
    }
}