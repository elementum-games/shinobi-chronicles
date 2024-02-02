<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/EntityId.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/MarkdownParser.php';
require_once __DIR__ . '/API.php';
require_once __DIR__ . '/Layout.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Route.php';

require_once __DIR__ . '/../classes/event/DoubleExpEvent.php';
require_once __DIR__ . '/../classes/event/DoubleReputationEvent.php';
require_once __DIR__ . '/../classes/event/BonusExpWeekend.php';
require_once __DIR__ . '/../classes/event/HolidayBonusEvent.php';

class System {
    //TODO: Move these
    const KUNAI_PER_DOLLAR = 2; // Currnecy
    const CURRENCY_TYPE_MONEY = 'money'; // Currency
    const CURRENCY_TYPE_PREMIUM_CREDITS = 'premium_credits'; // Currency
    const BLOODLINE_ROLL_CHANCE = 50; // Bloodline
    const ARENA_COOLDOWN = 4 * 1000; // Battle??
    const DEFAULT_LAYOUT = 'new_geisha'; // Layout
    const EXTENDED_BOOST_MULTIPLIER = 5; // Multiplies long by this value to calc extend (TrainingManager)
    const SC_MAX_RANK = 4; // RankManager

    public static array $villages = ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist']; // Village

    // Event
    const HOLIDAYS = [
        'Jan 1' => 'New Years Boost',
        'Jan 15' => 'Martin Luther King Day Boost',
        'Feb 14' => 'Valentines Day Boost',
        'May 27' => 'Memorial Day Boost',
        'Jun 19' => 'Juneteenth Boost',
        'Jul 4' => 'Independence Day Boost',
        'Sep 2' => 'Labor Day Boost',
        'Nov 11' => 'Veterans Day Boost',
        'Nov 28' => 'Thanksgiving Day Boost',
        'Dec 25' => 'Christmas Day Boost',
    ];

    // StaffManager, handle differently with CSS overhaul
    public array $SC_STAFF_COLORS = array(
        User::STAFF_MODERATOR => array(
            'staffBanner' => "moderator",
            'staffColor' => "#009020",
            'pm_class' => 'moderator'
        ),
        User::STAFF_HEAD_MODERATOR => array(
            'staffBanner' => "head moderator",
            'staffColor' => "#0090A0",
            'pm_class' => 'headModerator'
        ),
        User::STAFF_CONTENT_ADMIN => array(
            'staffBanner' => "content admin",
            'staffColor' => "#6400AF",
            'pm_class' => 'contentAdmin'
        ),
        User::STAFF_ADMINISTRATOR => array(
            'staffBanner' => "administrator",
            'staffColor' => "#A00000",
            'pm_class' => 'administrator'
        ),
        User::STAFF_HEAD_ADMINISTRATOR => array(
            'staffBanner' => "head administrator",
            'staffColor' => "#A00000",
            'pm_class' => 'administrator'
        )
    );

    // TEMPORARY - Need to find proper parser
    public static array $explicit_words = [
        'fuck',
        'fuk',
        'fck',
        'fuq',
        'fook',

        'asshole',
        'anus',

        'shit',
        'sht',
        'shiee',
        'shiet',

        'fag ',
        'faggot',
        'fgt',

        'cunt',

        'bitch',
        ' bich',
        ' bish',

        'retard',

        'cock ',
        'cocksu',
        'dick',
        'tits',
        'titt',
        'nigga',
        ' niga',
        'niga ',
        'nigger',
        'pussy',
        'pussies',
        'douche',
        'slut',
        'thot',

        'cum ',
        'cummi',
        'cumming',
        'jizz',

        // Sex terms
        'anal ',
        'blowjob',
        'creampie',
        'handjob',
        'rimjob',

        ' rape',

        'dildo',
    ];
    public static array $banned_words = [
        'fag ',
        'faggot',
        'retard',
        'nigger',
    ];

    // END TODO

    // Environment settings
    const ENVIRONMENT_DEV = 'dev';
    const ENVIRONMENT_PROD = 'prod';
    const DEV_ONLY_FEATURES_DEFAULT = false;
    const LOCAL_HOST = true;
    const USE_NEW_BATTLES = false;
    const WAR_ENABLED = true;
    const REQUIRE_USER_VERIFICATION = false;

    const VERSION_NUMBER = '0.11';
    const VERSION_NAME = '0.11 Warring Shadows';

    const LOGOUT_LIMIT = 720;
    const MAX_LINK_DISPLAY_LENGTH = 60;

    const SC_ADMIN_EMAIL = "admin@shinobichronicles.com";
    const SC_NO_REPLY_EMAIL = "no-reply@shinobichronicles.com";
    const UNSERVICEABLE_EMAIL_DOMAINS = ['hotmail.com', 'live.com', 'msn.com', 'outlook.com'];

    // Currently this only enables errors on production
    public static array $developers = [
        1, // Lsmjudoka
        254, // Hitori
        1603, // Arth
    ];

    // Time-based settings
    const SERVER_TIME_ZONE = 'America/New_York';
    const REPUTATION_RESET_DAY = 'Friday';
    const REPUTATION_RESET_HOUR = 20;
    const REPUTATION_RESET_MINUTE = 0;

    public function __construct(
        public Database $db,
        public Router $router,
        public bool $SC_OPEN,
        public readonly bool $USE_NEW_BATTLES,
        public readonly bool $war_enabled,
        public readonly bool $REQUIRE_USER_VERIFICATION,
        public DateTimeImmutable $SERVER_TIME,
        public ?DateTimeImmutable $UPDATE_MAINTENANCE = null,
        public ?DateTimeImmutable $REPUTATION_RESET = null,
        public ?Layout $layout = null,
        public ?int $timezoneOffset = null,
        public bool $enable_mobile_layout = false,
        public string $environment = self::ENVIRONMENT_DEV,
        public readonly bool $enable_dev_only_features = self::DEV_ONLY_FEATURES_DEFAULT,
        public readonly bool $local_host = self::LOCAL_HOST,
        public readonly bool $register_open = false,
        public ?Event $event = null,
        public string $message = '',
        public bool $message_displayed = false,
        public array $debug_messages = [],
        public bool $is_api_request = false,
        public bool $is_legacy_ajax_request = false,
        public int $TRAIN_BOOST = 0, // Extra points per training - REMOVE? Handle with TrainingManager & Events
        public int $LONG_TRAIN_BOOST = 0, // Extra points per long training - Remave as above?
        public readonly array $debug = [
            'battle' => false,
            'battle_effects' => false,
            'jutsu_collision' => false,
            'damage' => false,
            'bloodline' => false,
            'stat_cut' => false,
        ],
        public readonly array $testNotifications = [
            'caravan' => false,
            'raid' => false,
            'event' => false,
            'diplomacy' => false,
        ],
        public array $homeVars = [],
    ){}

    /******************************************************
     *        Debuging, System Messages and Strings       *
     ******************************************************/

    /**
     * Sets message for display in UI
     * @param $message - String value to be displayed on UI
     * @param bool $force_message - Allows overriding previous message, if not displayed
     * @return void
     */
    public function message(string $message, bool $force_message = false): void {
        if(!strlen($this->message) || $force_message) {
            $this->message = $message;
        }
    }

    /**
     * Displays system message, if one is set
     * TODO: Can this be a void method? Make a more modern display method
     * @param bool $force_display - Displays message regardless of length or previous display
     * @return bool
     */
    public function printMessage(bool $force_display = false): bool {
        if(strlen($this->message) && (!$this->message_displayed || $force_display)) {
            echo "<p class='systemMessage'>{$this->message}</p>";
            $this->message = '';
            $this->message_displayed = true;
        }
        return true;
    }

    /**
     * Fetches global message string and time and returns as an array
     * @return ?array
     */
    public function fetchGlobalMessage(): ?array {
        $result = $this->db->query("SELECT `global_message`, `time` FROM `system_storage` LIMIT 1");
        if($result->num_rows < 1) {
            return null;
        }

        $results = $this->db->fetch($result);

        return [
            'message' => str_replace("\r\n", "<br />", $results['global_message']),
            'time' => date("l, M j, Y - g:i A", $results['time'])
        ];
    }

    /**
     * Stores debugging messages
     * @return void
     */
    public function debugMessage($message): void {
        $this->debug_messages[] = $message;
    }

    /**
     *
     * @return Closure
     */
    public static function getEchoDebugClosure(): Closure {
        return function ($category, $label, $contents) {
            if(php_sapi_name() == "cli") {
                echo "\r\nDEBUG ($label)\r\n" . $contents . "\r\n";
            }
            else {
                echo "<br />DEBUG ($label)<br />" . $contents . "<br />";
            }
        };
    }

    /**
     *
     * @return string
     */
    public static function simpleStackTrace(): string {
        $stack_trace_arr = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);

        // Display the first line as file_name (line # would be the line that called this method, not super useful)
        $display = $stack_trace_arr[0]['file'];

        // Display the rest of the lines as file:line_num
        foreach(array_slice($stack_trace_arr, 1) as $stack_trace_row) {
            $display .= " > " . $stack_trace_row['file'] . ":" . $stack_trace_row['line'];
        }

        return $display;
    }

    /**
     * Checks string for banned words and returns bool based on check
     * @param string $string
     * @return bool
     */
    public function explicitLanguageCheck(string $string): bool {
        foreach(self::$explicit_words as $word) {
            if(str_contains(strtolower($string), $word)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns censored explicit words from string, based on user settings
     * Banned words are censored regardless of user settings
     * @param string $text
     * @param bool $allow_non_banned_words
     * @return string
     */
    public function explicitLanguageReplace(string $text, bool $allow_non_banned_words): string {
        $word_replace = [];
        if(!$allow_non_banned_words) {
            foreach(self::$explicit_words as $word) {
                $word_replace[] = str_repeat('*', strlen($word));
            }

            $text = str_ireplace(self::$explicit_words, $word_replace, $text);
        }

        // Censor banned words, regardless of user settings
        return $this->bannedLanguageReplace($text);
    }

    /**
     * Retruns censored banned words from string, regardless of user settings
     * @param string $text
     * @return string
     */
    public function bannedLanguageReplace(string $text): string {
        $word_replace = [];
        foreach(self::$banned_words as $key => $word) {
            $word_replace[] = str_repeat('*', strlen($word));
        }

        return str_ireplace(self::$banned_words, $word_replace, $text);
    }

    /**
     * Fetches and returns array of memes based on meme file
     * @return array
     */
    public function getMemes(): array {
        $meme_dir = __DIR__ . '/../images/memes';
        $meme_files = scandir($meme_dir);

        // Memes not found
        if(!$meme_files) {
            return array();
        }

        // Regex to filter out directories and non-image files
        $file_type_filter = '/(?:.png|.jpg|.gif)$/i';
        $search_symbols = ['-', '_'];
        $meme_array = [
            'codes' => [],
            'images' => [],
            'urls' => [],
            'texts' => [],
        ];

        // Set memes for storage in array
        $cleaned_memes = array_filter($meme_files, function($meme) use ($file_type_filter) {
            return preg_match($file_type_filter, $meme) == 1;
        });

        foreach($cleaned_memes as $meme) {
            $meme_code = strtolower(':' . preg_replace($file_type_filter, '', str_replace($search_symbols, '', $meme)) . ':');
            $url = "./images/memes/$meme";

            // Skip duplicate meme
            if(in_array($meme_code, $meme_array['codes'])) {
                continue;
            }

            $meme_array['codes'][] = $meme_code;
            $meme_array['images'][] = "<img src='$url' title='$meme_code' alt='$meme_code' style='max_width: 75px; max-height: 75px;' />";
            $meme_array['urls'][] = $url;
            $meme_array['texts'][] = $meme_code;
        }

        return $meme_array;
    }

    /**
     * Parses string for html/bbcode, images, memes, and youtube links
     * @var string $text
     * @var bool $img
     * @var bool $faces
     * @var bool $youtube
     * @return array|string
     */
    public function html_parse(string $text, bool $img = false, bool $faces = false, $youtube = false): array|string {
        $search_array = array(
            "[b]","[/b]","[u]","[/u]","[i]","[/i]",
            "&lt;3","[strike]","[/strike]","[super]","[/super]","[sub]","[/sub]", "[center]", "[/center]", "[right]", "[/right]",
        );
        $replace_array = array("<b>","</b>","<u>","</u>","<i>","</i>","&hearts;",
            "<del>","</del>","<sup>","</sup>","<sub>","</sub>", "<p style='text-align:center;'>", "</p>",
            "<p style='text-align:right;'>", "</p>",
        );

        $text = str_replace($search_array, $replace_array, $text);

        $memes = $this->getMemes();
        $text = str_replace($memes['codes'], ($faces ? $memes['images'] : $memes['texts']), $text);

        // Exclude images
        if($img) {
            $search_array[] = "[img]https://";
            $search_array[] = "[img]http://";
            $search_array[] = "[/img]";
            $replace_array[] = "<img src='[https_prefix]";
            $replace_array[] = "<img src='[https_prefix]";
            $replace_array[] = "' />";
        }
        if($youtube) {
            $search_array[] = "[youtube]https://";
            $replace_array[] = "[youtube][https_prefix]";
        }

        $text = str_ireplace($search_array,$replace_array,$text);

        $search_array = [];
        $replace_array = [];

        // Process links
        $reg_exUrl = '/((?:http|https):\/\/(?:[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,5})(?:\/[^\:\s\\\\]*)?)/i';
        if(self::LOCAL_HOST) {
            //Allow regex to work with local links if system is set to local host
            $reg_exUrl = '/((?:http|https):\/\/(?:[a-zA-Z0-9\-\.]+[a-zA-Z]{2,5})(?:\/[^\:\s\\\\]*)?)/i';
        }
        $text = preg_replace_callback(
            $reg_exUrl,
            function($matches) {
                $display = $matches[1];
                if(strlen($display) > System::MAX_LINK_DISPLAY_LENGTH) {
                    $display = substr($display, 0, System::MAX_LINK_DISPLAY_LENGTH - 3) . '...';
                }
                return "<a href='{$matches[1]}' target='_blank'>{$display}</a>";
            },
            $text
        );

        // process tags

        $search_array[] = '[https_prefix]';
        $replace_array[] = 'https://';

        $search_array[] = '\r\n';
        $replace_array[] = '<br />';

        $search_array[] = '&amp;#039;';
        $replace_array[] = "&#039;";

        $search_array[] = '&amp;lt;';
        $replace_array[] = "&lt;";
        $search_array[] = '&amp;gt;';
        $replace_array[] = "&gt;";

        $text = str_ireplace($search_array, $replace_array, $text);

        // Youtube videos
        if($youtube) {
            $text = preg_replace_callback(
                pattern: "!\[youtube\]https:\/\/"
                . "(?#full URL or short URL)(?:(?>www\.youtube\.com\/watch\?v=)|(?>youtu\.be\/))"
                . "(?#capture video ID)([\w-]+)"
                . "(?#capture optional time specifier)(?:[?&]t=(\d+))?"
                . "(?#any extra chars before ending tag, discard)(.*)?\[\/youtube\]!",
                callback: function($matches) {
                    return "<iframe
                     width='750'
                     height='600'
                     src='https://www.youtube.com/embed/{$matches[1]}" . ($matches[2] ? "?start={$matches[2]}" : "") . "'
                     frameborder='0'
                     allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
                     allowfullscreen
                 ></iframe>";
                },
                subject: str_replace("&amp;", "&", $text)
            );
        }

        return $text;
    }

    /**
     * Returns formatted string from MarkdownParser
     * @var string $text
     * @var bool $allow_images
     * @var bool $strip_breaks
     * @var bool $faces
     * @return string
     */
    public function parseMarkdown($text, $allow_images = false, $strip_breaks = true, $faces = false): string {
        if($strip_breaks) {
            $text = str_replace("\n", "", $text);
        }

        $text = str_replace("[br]", "\n", $text);

        return MarkdownParser::instance()
            ->setImagesDisabled(!$allow_images)
            ->setBreaksEnabled(true)
            ->text($text);
    }

    /**
     * Returns img markup, intended for user avatars
     * @var string $image_src
     * @var int $size
     * @return string
     */
    public function imageCheck(string $image_src, int $size): string {
        return "<img src='$image_src' style='max-width:{$size}px;max-height:{$size}px;' />";
    }

    /**
     * Formats readable variables into db-friendly string for storage
     * @var string $string
     * @return string
     */
    public static function slug(string $string): string {
        return strtolower(str_replace(' ', '_', $string));
    }
    /**
     * Formats named values from db for display
     * @var string $slug
     * @return string
     */
    public static function unSlug(string $slug): string {
        return ucwords(str_replace('_', ' ', $slug));
    }

    /******************************************************
     *                    TIME FUNCTIONS                  *
     ******************************************************/

    /**
     * Uses server time to determine reputation reset time.
     * This must be store as DateTimeImmutable.
     * @return void
     */
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

    /**
     * Returns current time in rounded miliseconds
     * @return int
     */
    public static function currentTimeMs(): int {
        return floor(microtime(true) * 1000);
    }

    /**
     * Returns DateTime based on provided microtime
     * @var float $microtime
     * @return DateTime|bool
     */
    public static function dateTimeFromMicrotime(float $microtime): DateTime|bool {
        return DateTime::createFromFormat(
            'U.u',
            number_format($microtime, 2, '.', '')
        );
    }

    public static function currentYear(): int {
        return (int) date('Y', time());
    }

    /**
     * @return int month of the year: 1-12
     */
    public static function currentMonth(): int {
        return (int) date('n', time());
    }

    /**
     * @return int day of the month: 1-31
     */
    public static function currentDay(): int {
        return (int) date('j', time());
    }

    /**
     * @return int hour of the day in 24-hour format: 0-23
     */
    public static function currentHour(): int {
        return (int) date('G', time());
    }

    /**
     * @return int minute of the hour, 0-59
     */
    public static function currentMinute(): int {
        return (int)date('i');
    }

    /**
     * @return int day of the week: 0 (Sunday) - 6 (Saturday)
     */
    public static function currentDayOfWeek(): int {
        return (int) date('w');
    }

    /**
     * Returns formatted string based on a previous unix timestamp
     * @var int $timestamp
     */
    public function timeAgo(int $timestamp): string {
        $time = time() - $timestamp;

        $days = 0;
        $hours = 0;
        $minutes = 0;

        $time_string = '';

        // Days
        if($time >= 86400) {

            $days = floor($time / 86400);

            $time -= $days * 86400;

            $time_string .= $days . ' days, ';

        }

        // Hours
        if($time >= 3600) {

            $hours = floor($time / 3600);

            $time -= $hours * 3600;

            $time_string .= $hours . ' hours, ';

        }

        // Minutes
        $minutes = ceil($time / 60);

        $time_string .= $minutes . ' minute(s) ago';

        return $time_string;
    }

    /**
     * Returns formatted string based on unix timestamp in the future
     * @var int $timestamp
     * @return string
     */
    public function time_remaining(int $timestamp): string {
        $days = floor($timestamp / 86400);
        $hours = floor($timestamp / 3600);
        $minutes = ceil($timestamp / 60);

        $message = '';
        if($days) {
            $minutes -= $hours * 60;
            $hours -= $days * 24;
            $message .= $days . ($days > 1 ? " days, " : " day, ") .
                $hours . ($hours > 1 ? " hours, " : " hour, ") .
                $minutes . ($minutes > 1 ? " minutes" : " minute");
        }
        else if($hours) {
            $minutes -= $hours * 60;
            $message .= $hours . ($hours > 1 ? " hours, " : " hour, ") .
                $minutes . ($minutes > 1 ? " minutes" : " minute");
        }
        else {
            $message .= $minutes . ($minutes > 1 ? " minutes" : " minute");
        }
        return $message;
    }

    /**
     * Static method of returning formatted time string
     * TODO: Consolidate timeRemaining functions to reduce duplicated code
     * @var int $time_remaining
     * @var string $format
     * @var bool $include_days
     * @var bool $include_seconds
     * @return string
     */
    public static function timeRemaining($time_remaining, $format = 'short', $include_days = true, $include_seconds = true): string {
        if($include_days) {
            $days = floor($time_remaining / 86400);
            $time_remaining -= $days * 86400;
        }
        else {
            $days = null;
        }

        $hours = floor($time_remaining / 3600);
        $time_remaining -= $hours * 3600;

        if($include_seconds) {
            $minutes = floor($time_remaining / 60);
            $time_remaining -= $minutes * 60;

            $seconds = $time_remaining;
        }
        else {
            $minutes = ceil($time_remaining / 60);
        }

        if($hours < 10 && $format == 'short') {
            $hours = '0' . $hours;
        }
        if($minutes < 10 && $format == 'short') {
            $minutes = '0' . $minutes;
        }
        if($include_seconds && $seconds < 10 && $format == 'short') {
            $seconds = '0' . $seconds;
        }

        $string = '';
        if($format == 'long') {
            if($days && $include_days) {
                $string = "$days day(s), $hours hour(s), $minutes minute(s)";
            }
            else if ($hours && $hours != '00') {
                $string = "$hours hour(s), $minutes minute(s)";
            }
            else {
                $string = "$minutes minute(s)";
            }

            if($include_seconds) {
                $string .= ", $seconds seconds";
            }
        }
        else if($format == 'short') {
            if($days) {
                $string = "$days day(s), $hours:$minutes";
            }
            else if($hours && $hours != '00') {
                $string = "$hours:$minutes";
            }
            else {
                $string = "$minutes";
            }

            if($include_seconds) {
                $string .= ":$seconds";
            }
        }
        else if($format == 'days') {
            if ($days) {
                $string = "$days day(s)";
            } else {
                $string = "0 days";
            }
        }
        return $string;
    }

    /******************************************************
     *                         Logs                       *
     ******************************************************/

    /**
     * Inserts various log types into System logs.
     * Custom methods should be used for other managers.
     * @var string $type
     * @var string $title
     * @var array|string $contents
     * @return void
     */
    public function log(string $type, string $title, array|string $contents): void {
        $type = $this->db->clean($type);
        $title = $this->db->clean($title);

        if (is_array($contents)) {
            $contents = json_encode($contents);
        }

        $contents = $this->db->clean($contents);

        $this->db->query(
            "INSERT INTO `logs` (`log_type`, `log_title`, `log_time`, `log_contents`)
                VALUES ('$type', '$title', " . time() . ", '$contents')"
        );
    }

    /**
     * Addes currency transactions to currency log
     * TODO: Move to Currency
     * @var int $character_id
     * @var string $currency_type
     * @var int $previous_balance
     * @var int $new_balance
     * @var int $transaction_amount
     * @var string $transaction_description
     * @throws RuntimeException
     * @return void
     */
    public function currencyLog(
        int $character_id, string $currency_type,
        int $previous_balance, int $new_balance,
        int $transaction_amount, string $transaction_description
    ): void {
        switch($currency_type) {
            case 'premium_credits':
            case 'money':
                break;
            default:
                throw new RuntimeException("Invalid currency type!");
        }

        $this->db->query(
            "INSERT INTO `currency_logs` (
                `character_id`,
                `currency_type`,
                `previous_balance`,
                `new_balance`,
                `transaction_amount`,
                `transaction_description`,
                `transaction_time`
            ) VALUES (
               '{$character_id}',
                '{$currency_type}',
                '{$previous_balance}',
                '{$new_balance}',
                '{$transaction_amount}',
                '{$this->db->clean($transaction_description)}',
                " . time() . "
            )"
        );
    }

    /**
     * Returns available premium packages
     * TODO: Move to Currency
     * @return array
     */
    public static function getKunaiPacks(): array {
        $kunai_packs = [
            [
                'cost' => 5,
                'bonus' => 0
            ],
            [
                'cost' => 10,
                'bonus' => 0
            ],
            [
                'cost' => 25,
                'bonus' => 15
            ],
            [
                'cost' => 50,
                'bonus' => 40
            ],
            [
                'cost' => 100,
                'bonus' => 100
            ],
        ];
        foreach($kunai_packs as &$pack) {
            $pack['kunai'] = $pack['cost'] * System::KUNAI_PER_DOLLAR;
        }

        return $kunai_packs;
    }

    /******************************************************
     *                    Miscelaenous                    *
     ******************************************************/

    /**
     * Hash password for db storage
     * TODO: Move to User
     * @var string $password
     * @return string
     */
    public function hash_password(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifies password based on provided password and stored has
     * TODO: Move to User
     * @var string $password
     * @var string $password_hash
     * @return bool
     */
    public function verify_password(string $password, string $password_hash): bool {
        return password_verify($password, $password_hash);
    }

    /**
     * Returns whether or not environment is dev
     * @return bool
     */
    public function isDevEnvironment(): bool {
        return $this->environment == self::ENVIRONMENT_DEV;
    }

    /**
     * Returns Layout based on provided layout name
     * @var string $layout
     * @return Layout
     */
    public function setLayoutByName(string $layout): Layout {
        // Legacy layout support
        $system = $this;

        switch($layout) {
            case 'cextralite':
                $this->layout = require "layout/cextralite.php";
                break;
            case 'classic_blue':
                $this->layout = require  "layout/classic_blue.php";
                break;
            case 'shadow_ribbon':
                $this->layout = require  "layout/shadow_ribbon.php";
                break;
            case 'geisha':
                $this->layout = require  "layout/geisha.php";
                break;
            case 'blue_scroll':
                $this->layout = require  "layout/blue_scroll.php";
                break;
            case 'rainbow_road':
                $this->layout = require "layout/rainbow_road.php";
                break;
            case 'new_geisha':
                require_once __DIR__ . "/../layout/new_geisha.php";
                // This needs to be first so the function can read it
                $this->enable_mobile_layout = true;
                $this->layout = getNewGeishaLayout($this, $this->enable_mobile_layout);
                break;
            case 'sumu':
                require_once __DIR__ . "/../layout/sumu.php";
                // This needs to be first so the function can read it
                $this->enable_mobile_layout = true;
                $this->layout = getSumuLayout($this, $this->enable_mobile_layout);
                break;
            default:
                $this->layout = require "layout/" . self::DEFAULT_LAYOUT . ".php";
                break;
        }

        return $this->layout;
    }

    /**
     * Checks for active events and sets weekend & holiday boosts
     * By default, this will use Server time
     * @return void
     */
    public function checkForActiveEvent(): void {
        $SERVER_TIME_ZONE = new DateTimeZone(self::SERVER_TIME_ZONE);
        // Set events on development environments
        if($this->isDevEnvironment()) {
            $july_2023_lantern_event_start_time = new DateTimeImmutable('2023-07-15', $SERVER_TIME_ZONE);
            $double_exp_start_time = new DateTimeImmutable('2023-12-25', $SERVER_TIME_ZONE);
            $double_reputation_start_time = new DateTimeImmutable('2024-01-19', $SERVER_TIME_ZONE);
        }
        // Set events on production environments
        else {
            $july_2023_lantern_event_start_time = new DateTimeImmutable('2023-07-15', $SERVER_TIME_ZONE);
            $double_exp_start_time = new DateTimeImmutable('2023-12-25', $SERVER_TIME_ZONE);
            $double_reputation_start_time = new DateTimeImmutable('2023-10-16', $SERVER_TIME_ZONE);
        }

        /********* CORE EVENTS *********/
        // Double exp gains
        $double_exp_end_time = new DateTimeImmutable('2023-12-27');
        if($this->SERVER_TIME > $double_exp_start_time && $this->SERVER_TIME < $double_exp_end_time) {
            $this->event = new DoubleExpEvent($double_exp_end_time);
        }
        // Double reputation gains
        $double_reputation_end_time = new DateTimeImmutable('2024-01-2');
        if($this->SERVER_TIME > $double_reputation_start_time && $this->SERVER_TIME < $double_reputation_end_time) {
            $this->event = new DoubleReputationEvent($double_reputation_end_time);
        }

        /***** LIMITED TIME EVENTS *****/
        $july_2023_lantern_event_end_time = new DateTimeImmutable('2023-07-16', $SERVER_TIME_ZONE);
        if($this->SERVER_TIME > $july_2023_lantern_event_start_time && $this->SERVER_TIME < $july_2023_lantern_event_end_time) {
            $this->event = new LanternEvent($july_2023_lantern_event_end_time);
        }

        /******* HOLIDAY BOOSTS ********/
        if (!isset($this->event) && in_array($this->SERVER_TIME->format('M j'), array_keys(self::HOLIDAYS))) {
            $endTime = new DateTimeImmutable("tomorrow", $SERVER_TIME_ZONE);
            $this->event = new HolidayBonusEvent($endTime, self::HOLIDAYS[$this->SERVER_TIME->format('M j')]);
        }

        /******* WEEKEND BOOSTS ********/
        if (!isset($this->event) && (System::currentDayOfWeek() == 0 || System::currentDayOfWeek() == 6)) {
            $endTime = new DateTimeImmutable('next Monday', $SERVER_TIME_ZONE);
            $this->event = new BonusExpWeekend($endTime);
        }

        // This needs to be last - test notification on DEV servers
        if($this->testNotifications['event'] && is_null($this->event) && $this->isDevEnvironment()) {
            $this->event = new DoubleExpEvent($this->SERVER_TIME->modify("+2 weeks"));
        }
    }

    /**
     * Closes and opens server in-real time, without needing manual code edits
     * Sets maintenance time-frame and displays a countdown on non-legacy layouts
     * @return void
     */
    public function checkForMaintenance(): void {
        // Check for db-based maintenance
        $db_maint = $this->db->query("SELECT `maintenance_begin_time`, `maintenance_end_time` FROM `system_storage`");
        $db_maint = $this->db->fetch($db_maint);

        // Hard closure
        if($db_maint['maintenance_end_time'] == -1) {
            $this->SC_OPEN = false;
            $this->UPDATE_MAINTENANCE = null;
        }

        if($db_maint['maintenance_begin_time'] > $this->SERVER_TIME->getTimestamp() || $db_maint['maintenance_end_time'] > $this->SERVER_TIME->getTimestamp()) {
            $maintenanceBegin = $this->SERVER_TIME->setTimestamp($db_maint['maintenance_begin_time']);
            $maintenanceEnd = $this->SERVER_TIME->setTimestamp($db_maint['maintenance_end_time']);

            // Display timer for maintenance window
            if($this->SERVER_TIME->getTimestamp() < $maintenanceBegin->getTimestamp()) {
                $this->UPDATE_MAINTENANCE = $maintenanceBegin;
            }
            // Close SC for maintenance window - NOTE: This can be overridden in vars.php if window can't be easily determined
            if($this->SERVER_TIME->getTimestamp() > $maintenanceBegin->getTimestamp() && $this->SERVER_TIME->getTimestamp() < $maintenanceEnd->getTimestamp()) {
                $this->SC_OPEN = false;
                $this->UPDATE_MAINTENANCE = $maintenanceEnd;
            }
        }
    }

    /**
     * Returns estimated time server will come back online, rounded up to nearst 5 minutes
     * E.g. 7 minutes => 10 minutes // 1 minute => 5 minutes
     * If system is closed, returns generic closed message
     * @return string
     */
    public function getMaintenenceEndTime(): string {
        if(!$this->SC_OPEN && $this->UPDATE_MAINTENANCE) {
            $mins = ceil(($this->UPDATE_MAINTENANCE->getTimestamp() - time()) / 60); // Round up to nearest minute
            $mins += 5 - ($mins % 5); // Add remainder to bring to nearst 5 minutes
            return "$mins minutes";
        }

        return "1 hour";
    }

    /**
     * Returns entity based on type & ID
     * @param string $entity_id
     * @return EntityId
     * @throws RuntimeException
     */
    public static function parseEntityId(string $entity_id): EntityId {
        $arr = explode(':', $entity_id);
        if(count($arr) != 2) {
            throw new RuntimeException("Invalid entity id {$entity_id}!");
        }

        return new EntityId($arr[0], (int)$arr[1]);
    }

    /**
     * Returns build file for react components
     * @var string $component_name
     * @return string
     */
    public function getReactFile(string $component_name): string {
        $filename = "ui_components/build/{$component_name}.js";
        return $this->router->base_url . $filename . "?v=" .  filemtime($filename);
    }

    /**
     * Returns src string of css file
     * @var string $file_name
     * @return string
     */
    public function getCssFileLink(string $file_name): string {
        return $this->router->base_url . $file_name . "?v=" .  filemtime($file_name);
    }

    /**
     * Helper function
     * @param $number
     * @param $min
     * @param $max
     * @return int
     */
    public static function clampNumber($number, $min, $max): int {
        return max(min($number, $max), $min);
    }

    /**
     * Example:
     * php -f somefile.php a=1 b[]=2 b[]=3
     *
     * This will return an an array of
     * [
     *   'a' => '1',
     *   'b' => ['2', '3']
     * ]
     *
     * @return array
     */
    public static function parseCommandLineArgs(): array {
        $result = [];

        parse_str(implode('&', array_slice($_SERVER['argv'], 1)), $result);

        return $result;
    }

    /**
     * Initializes and returns system
     * @param bool $load_layout
     * @return System
     * @throws Exception
     */
    public static function initialize(bool $load_layout = true): System {
        /**
         * This must be called here to properly pull variables into initializer
         * @var $host
         * @var $username
         * @var $password
         * @var $database
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
         *
         * @var string $web_url
         * @var bool $register_open
         */
        require_once __DIR__ . '/../secure/vars.php';

        $system = new System(
            db: new Database($host, $username, $password, $database),
            router: new Router($web_url),
            SC_OPEN: $SC_OPEN,
            USE_NEW_BATTLES: $USE_NEW_BATTLES ?? self::USE_NEW_BATTLES,
            war_enabled: $WAR_ENABLED ?? self::WAR_ENABLED,
            REQUIRE_USER_VERIFICATION: $REQUIRE_USER_VERIFICATION ?? self::REQUIRE_USER_VERIFICATION,
            SERVER_TIME: new DateTimeImmutable("now", new DateTimeZone(self::SERVER_TIME_ZONE)),
            environment: $ENVIRONMENT,
            enable_dev_only_features: $ENABLE_DEV_ONLY_FEATURES ?? self::DEV_ONLY_FEATURES_DEFAULT,
            local_host: $LOCAL_HOST_CONNECTION ?? self::LOCAL_HOST,
            register_open: $register_open
        );

        // Load reputation layout, reputation reset and check for server maintenance
        $system->loadRepReset();
        $system->checkForActiveEvent();
        $system->checkForMaintenance();

        // Set home page variables
        $system->homeVars = [
            'view' => 'none',
            // TODO: This can be removed, header API has the two links used available
            'links' => [
                'news_api' => $system->router->api_links['news'],
                'logout' => $system->router->base_url . '?logout=1',
                'profile' => $system->router->getUrl('profile'),
                'github' => $system->router->links['github'],
                'discord' => $system->router->links['discord'],
                'support' => $system->router->base_url . 'support.php',
                'login_url' => $system->router->base_url,
                'register_url' => $system->router->base_url,
            ],
            'errors' => [
                'login' => '',
                'register' => '',
                'reset' => '',
            ],
            'messages' => [
                'login' => '',
                'register' => '',
                'reset' => '',
            ],
            'register_prefill' => [],
        ];

        // Load layout if this is not an api request
        if($load_layout) {
            $system->setLayoutByName(self::DEFAULT_LAYOUT);
        }

        // Legacy layout support
        $system->timezoneOffset = date(format: 'Z');

        return $system;
    }
}