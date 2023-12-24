<?php
class System {
    // Environment keys
    const ENVIRONMENT_DEV = 'dev';
    const ENVIRONMENT_PROD = 'prod';
    const LOCAL_HOST = true; // TODO: Where is this used??

    // Version & layout control
    const VERSION_NUMBER = '0.2';
    const VERSION_NAME = '0.2 Among the Shadows';
    const DEFAULT_LAYOUT = 'new_geisha';

    // Session control
    const LOGOUT_LIMIT = 720;
    const ARENA_COOLDOWN = 4 * 1000;

    // Registration
    const SC_ADMIN_EMAIL = "admin@shinobichronicles.com";
    const SC_NO_REPLY_EMAIL = "no-reply@shinobichronicles.com";
    const UNSERVICEABLE_EMAIL_DOMAINS = ['hotmail.com', 'live.com', 'msn.com', 'outlook.com'];

    // System Defaults
    const ENVIRONMENT_DEFAULT = self::ENVIRONMENT_DEV;
    const SERVER_OPEN_DEFAULT = true;
    const REGISTRATION_OPEN_DEFAULT = false;
    const ENABLE_DEV_ONLY_FEATURES_DEFAULT = false;
    const ENABLE_NEW_BATTLES_DEFAULT = false;
    const ENABLE_WAR_DEFAULT = true;

    // Premium content
    const BLOODLINE_ROLL_CHANCE = 50; // TODO: Move this to Bloodline???

    // TODO: Move currency keys to Currency
    const KUNAI_PER_DOLLAR = 2;
    const CURRENCY_TYPE_MONEY = 'money';
    const CURRENCY_TYPE_PREMIUM = 'premium_credits';

    // TODO: Move to villages
    public static array $villages = ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'];


    public function __construct(
        public Database $db,
        public Router $router,
        public TimeManager $TIME_MANAGER,
        public ?Event $event = null,
        public readonly string $ENVIRONMENT = self::ENVIRONMENT_DEFAULT,
        public string $message = "",
        public bool $messgae_displayed = false,
        public array $debug_messages = [],
        public readonly bool $enable_dev_only_features = self::ENABLE_DEV_ONLY_FEATURES_DEFAULT,
        public readonly bool $register_open = self::REGISTRATION_OPEN_DEFAULT,
        public readonly bool $SC_OPEN = self::SERVER_OPEN_DEFAULT,
        public readonly bool $USE_NEW_BATTLES = self::ENABLE_NEW_BATTLES_DEFAULT,
        public readonly bool $war_enabled = self::ENABLE_WAR_DEFAULT
    ) {}


    public static function LOAD(): System {
        /**
         * @var string $host
         * @var string $username
         * @var string $password
         * @var string $database
         * @var string $ENVIRONMENT
         * @var string $web_url
         * @var bool $SC_OPEN
         * @var bool $register_open
         */
        require_once __DIR__ . '/_autoload.php';
        require_once __DIR__ . '/../secure/vars.php';

        return new System(
            db: new Database(
                host: $host,
                username: $username,
                password: $password,
                database: $database
            ),
            router: new Router($web_url),
            TIME_MANAGER: TimeManager::LOAD(),
            ENVIRONMENT: $ENVIRONMENT,
            register_open: $register_open,
            SC_OPEN: $SC_OPEN
        );
    }
}