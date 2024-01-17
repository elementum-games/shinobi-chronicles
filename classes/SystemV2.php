<?php
require_once __DIR__ . '/../classes/_sysRequires.php';
class SystemV2 {
    // Environment settings
    const ENVIRONMENT_DEV = 'dev';
    const ENVIRONMENT_PROD = 'prod';
    const DEV_ONLY_FEATURES_DEFAULT = false;
    const LOCAL_HOST = true;
    const SERVER_TIME_ZONE = 'America/New_York';
    const REPUTATION_RESET_DAY = 'Friday';
    const REPUTATION_RESET_HOUR = 20;
    const REPUTATION_RESET_MINUTE = 0;


    public function __construct(
        public Database $db,
        public Router $router,
        public bool $SC_OPEN,
        public readonly bool $USE_NEW_BATTLES,
        public readonly bool $WAR_ENABLED,
        public readonly bool $REQUIRE_USER_VERIFICATION,
        public DateTimeImmutable $SERVER_TIME,
        public ?DateTimeImmutable $UPDATE_MAINTENANCE = null,
        public ?DateTimeImmutable $REPUTATION_RESET = null,
        public ?int $timezoneOffset = null,
        public string $environment = self::ENVIRONMENT_DEV,
        public readonly bool $enable_dev_only_features = self::DEV_ONLY_FEATURES_DEFAULT,
        public readonly bool $local_host = self::LOCAL_HOST,
        public readonly bool $register_open = false
    ){}

    /****************************************
     *            TIME FUNCTIONS            *
     ****************************************/
    public function loadRepReset(): void {
        // Reset is today
        if(strtolower($this->SERVER_TIME->format('l')) == strtolower(self::REPUTATION_RESET_DAY)) {
            // Set time to today at proper hour and minute
            $this->REPUTATION_RESET = $this->SERVER_TIME->setTime(hour: self::REPUTATION_RESET_HOUR, minute: self::REPUTATION_RESET_MINUTE);
            // Reset has passed for today, move to next week and set to proper hour & minute
            if($this->REPUTATION_RESET->getTimestamp() <= $this->SERVER_TIME->getTimestamp()) {
                $this->REPUTATION_RESET = $this->SERVER_TIME->modify('next ' . self::REPUTATION_RESET_DAY);
                $this->REPUTATION_RESET = $this->REPUTATION_RESET->setTime(hour: self::REPUTATION_RESET_HOUR, minute: self::REPUTATION_RESET_MINUTE);
            }
        }
        // Reset is later in the week
        else {
            $this->REPUTATION_RESET = $this->SERVER_TIME->modify('next ' . self::REPUTATION_RESET_DAY);
            $this->REPUTATION_RESET = $this->REPUTATION_RESET->setTime(hour: self::REPUTATION_RESET_HOUR, minute: self::REPUTATION_RESET_MINUTE);
        }
    }


    public static function initialize(): SystemV2 {
        /**
         * This must be called here to properly pull variables into initializer
         * @var $HOST
         * @var $USERNAME
         * @var $PASSWORD
         * @var $DATABASE
         *
         * @var string $ENVIRONMENT
         * @var string $WEB_URL
         * @var bool $SC_OPEN
         * @var bool $ENABLE_DEV_ONLY_FEATURES
         * @var bool $LOCAL_HOST_CONNECTION
         * @var bool $REGISTER_OPEN
         * @var bool $USE_NEW_BATTLES
         * @var bool $WAR_ENABLED
         * @var bool $REQUIRE_USER_VERIFICATION
         */
        require_once __DIR__ . '/../secure/vars.php';

        $system = new SystemV2(
            db: new Database($HOST, $USERNAME, $PASSWORD, $DATABASE),
            router: new Router($WEB_URL),
            SC_OPEN: $SC_OPEN,
            USE_NEW_BATTLES: $USE_NEW_BATTLES,
            WAR_ENABLED: $WAR_ENABLED,
            REQUIRE_USER_VERIFICATION: $REQUIRE_USER_VERIFICATION,
            SERVER_TIME: new DateTimeImmutable("now", new DateTimeZone(self::SERVER_TIME_ZONE)),
            environment: $ENVIRONMENT,
            enable_dev_only_features: $ENABLE_DEV_ONLY_FEATURES,
            local_host: $LOCAL_HOST_CONNECTION,
            register_open: $REGISTER_OPEN
        );

        // Load reputation reset
        $system->loadRepReset();

        // Legacy layout support
        $system->timezoneOffset = date(format: 'Z');

        return $system;
    }
}