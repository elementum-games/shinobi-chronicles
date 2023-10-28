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

/*	Class:		System
	Purpose: 	Handle database connection and queries. Handle storing and printing of error messages.
*/
class System {
    const ENVIRONMENT_DEV = 'dev';
    const ENVIRONMENT_PROD = 'prod';
    const LOCAL_HOST = true;

    const KUNAI_PER_DOLLAR = 2;
    const LOGOUT_LIMIT = 720;
    const BLOODLINE_ROLL_CHANCE = 50;
    const ARENA_COOLDOWN = 4 * 1000;

    const CURRENCY_TYPE_MONEY = 'money';
    const CURRENCY_TYPE_PREMIUM_CREDITS = 'premium_credits';

    const SC_ADMIN_EMAIL = "admin@shinobichronicles.com";
    const SC_NO_REPLY_EMAIL = "no-reply@shinobichronicles.com";
    const UNSERVICEABLE_EMAIL_DOMAINS = ['hotmail.com', 'live.com', 'msn.com', 'outlook.com'];

    // Temporary event data storage
    public ?Event $event;

    public static array $villages = ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'];

    // Variable for error message
    public string $message = "";
    public bool $message_displayed = false;

    public array $debug_messages = [];

    // Sub-components
    public Database $db;
    public Router $router;
    public ?Layout $layout;
    public bool $enable_mobile_layout = false;

    public string $environment;

    public bool $SC_OPEN;
    public bool $register_open;
    public bool $USE_NEW_BATTLES = false;

    public string $link;

    // Request lifecycle
    public bool $is_api_request = false;
    public bool $is_legacy_ajax_request = false;

    public $timezoneOffset;

    // Training boost switches
    public int $TRAIN_BOOST = 0; // Extra points per training, 0 for none
    public int $LONG_TRAIN_BOOST = 0; // Extra points per long training, 0 for none
    const EXTENDED_BOOST_MULTIPLIER = 5; // Multiples long by this value to calc extended boost

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

    // Default layout
    const DEFAULT_LAYOUT = 'new_geisha';
    const VERSION_NUMBER = '0.9.3';
    const VERSION_NAME = '0.9.3 Warring Shadows';

    // Misc stuff
    const SC_MAX_RANK = 4;

    const MAX_LINK_DISPLAY_LENGTH = 60;

    public bool $war_enabled = false;

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

    public array $debug = [
        'battle' => false,
        'battle_effects' => false,
        'jutsu_collision' => false,
        'damage' => false,
        'bloodline' => false,
        'stat_cut' => false,
    ];

    public function __construct() {
        require __DIR__ . "/../secure/vars.php";
        /** @var $host */
        /** @var $username */
        /** @var $password */
        /** @var $database */
        $this->db = new Database($host, $username, $password, $database);

        $this->environment = $ENVIRONMENT ?? self::ENVIRONMENT_DEV;
        $this->register_open = $register_open ?? false;
        $this->SC_OPEN = $SC_OPEN ?? false;
        $this->USE_NEW_BATTLES = $USE_NEW_BATTLES ?? false;

        $this->router = new Router($web_url ?? 'http://localhost/');

        $this->timezoneOffset = date('Z');

        $this->checkForActiveEvent();

        $this->war_enabled = true;
    }

    /**
     * @param $message
     * @param bool $force_message
     * @return void
     */
    public function message($message, bool $force_message = false): void {
        if(strlen($this->message) == 0 || $force_message) {
            $this->message = $message;
        }
    }

    public function debugMessage($message): void {
        $this->debug_messages[] = $message;
    }

    /* function printMessage()
        Displays message, if one is stored.
        -Parameters-
        None
    */
    public function printMessage($force_display = false): bool {
        if(strlen($this->message) && (!$this->message_displayed || $force_display)) {
            echo "<p class='systemMessage'>{$this->message}</p>";
            $this->message = '';
            $this->message_displayed = true;
            return true;
        }
        return true;
    }

    public static function currentTimeMs(): int {
        return floor(microtime(true) * 1000);
    }

    /* function explicitLanguageCheck(word)
        Checks our list of banned words, returns true or false if censored words are detected.
        -Parameters-
        @string
    */
    public function explicitLanguageCheck($string): bool {
        foreach(self::$explicit_words as $word) {
            if(str_contains(strtolower($string), $word)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param      $text
     * @param bool $allow_non_banned_words
     * @return string
     */
    public function explicitLanguageReplace($text, bool $allow_non_banned_words = false): string {
        $word_replace = [];

        if(!$allow_non_banned_words) {
            foreach(self::$explicit_words as $word) {
                $word_replace[] = str_repeat('*', strlen($word));
            }

            $text = str_ireplace(self::$explicit_words, $word_replace, $text);
        }


        $word_replace = [];
        foreach(self::$banned_words as $word) {
            $word_replace[] = str_repeat('*', strlen($word));
        }

        return str_ireplace(self::$banned_words, $word_replace, $text);
    }

    /**
     * @param      $text
     * @return string
     */
    public function bannedLanguageReplace($text): string {
        //String censorship
        $word_replace = [];
        foreach(self::$banned_words as $key => $word) {
            $word_replace[] = str_repeat('*', strlen($word));
        }

        return str_ireplace(self::$banned_words, $word_replace, $text);
    }

    public function getMemes(): array {
        $memes_dir = __DIR__ . '/../images/memes';
        $meme_files = scandir($memes_dir);
        $file_type_filter = '/(?:.png|.jpg|.gif)$/i';

        if ($meme_files === False)
        {
            return [];
        }
        $cleaned_memes = array_filter($meme_files, function ($meme) use ($file_type_filter) {
            return preg_match($file_type_filter, $meme) == 1;
        });
        $search_symbols = ['-', '_'];
        $meme_array = [
            'codes' => [],
            'images' => [],
            'urls' => [],
            'texts' => []
        ];
        foreach ($cleaned_memes as $meme)
        {
            $meme_code = strtolower(':' . preg_replace($file_type_filter,'', str_replace($search_symbols, '', $meme)) . ':');

            if (in_array($meme_code, $meme_array['codes'])) continue;

            $url = "./images/memes/${meme}";

            $meme_array['codes'][] = $meme_code;
            $meme_array['images'][] = "<img src='{$url}' title='${meme_code}' alt='${meme_code}' style='max-width: 75px;max-height: 75px'/>";
            $meme_array['urls'][] = $url;
            $meme_array['texts'][] = $meme_code;
        }
        return $meme_array;
    }

    public function html_parse($text, $img = false, $faces = false): array|string {
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

        if($img) {
            $search_array[count($search_array)] = "[img]https://";
            $search_array[count($search_array)] = "[img]http://";
            $search_array[count($search_array)] = "[/img]";
            $replace_array[count($replace_array)] = "<img src='[image_prefix]";
            $replace_array[count($replace_array)] = "<img src='[image_prefix]";
            $replace_array[count($replace_array)] = "' />";
        }

        $text = str_ireplace($search_array,$replace_array,$text);

        $search_array = [];
        $replace_array = [];

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

        $search_array[] = '[image_prefix]';
        $replace_array[] = 'https://';

        $search_array[] = '\r\n';
        $replace_array[] = '<br />';

        $search_array[] = '&amp;#039;';
        $replace_array[] = "&#039;";

        $search_array[] = '&amp;lt;';
        $replace_array[] = "&lt;";
        $search_array[] = '&amp;gt;';
        $replace_array[] = "&gt;";

        return str_ireplace($search_array, $replace_array, $text);

    }

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

    public function imageCheck($image, $size): string {
        $avatar_limit = $size;

        $width = $avatar_limit;
        $height = $avatar_limit;

        return "<img src='$image' style='max-width:{$width}px;max-height:{$height}px;' />";
    }

    public function timeAgo($timestamp): string {
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

    public function time_remaining($timestamp): string {
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

    public function log($type, $title, $contents): void {
        $type = $this->db->clean($type);
        $title = $this->db->clean($title);

        if (is_array($contents))
        {
            $contents = json_encode($contents);
        }

        $contents = $this->db->clean($contents);

        $this->db->query(
            "INSERT INTO `logs` (`log_type`, `log_title`, `log_time`, `log_contents`)
                VALUES ('$type', '$title', " . time() . ", '$contents')"
        );
    }

    /**
     * @throws RuntimeException
     */
    public function currencyLog(
        int $character_id,
        string $currency_type,
        int $previous_balance,
        int $new_balance,
        int $transaction_amount,
        string $transaction_description
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

    public function hash_password($password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify_password($password, $hash): bool {
        return password_verify($password, $hash);
    }

    public function setLayoutByName(string $layout): Layout {
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
     * @throws RuntimeException
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

    public function isDevEnvironment(): bool {
        return $this->environment == System::ENVIRONMENT_DEV;
    }

    // Note: The system can currently only support one event type
    public function checkForActiveEvent(): void {
        // Base data
        $current_datetime = new DateTimeImmutable();
        $this->event = null;

        // Dev Environment Event start times
        if($this->isDevEnvironment()) {
            $july_2023_lantern_event_start_time = new DateTimeImmutable('2023-07-15');
            $double_exp_start_time = new DateTimeImmutable('2023-09-13');
            $double_reputation_start_time = new DateTimeImmutable('2023-10-16');
        }
        // Production Event start times
        else {
            $july_2023_lantern_event_start_time = new DateTimeImmutable('2023-07-01');
            $double_exp_start_time = new DateTimeImmutable('2023-09-19');
            $double_reputation_start_time = new DateTimeImmutable('2023-10-18');
        }
        /*****CORE EVENTS*****/
        // TODO: Make core events more manageable
        // Double exp gains
        $double_exp_end_time = new DateTimeImmutable('2023-10-4');
        if($current_datetime > $double_exp_start_time && $current_datetime < $double_exp_end_time) {
            $this->event = new DoubleExpEvent($double_exp_end_time);
        }
        // Double reputation gains
        $double_reputation_end_time = new DateTimeImmutable('2023-10-29');
        if($current_datetime > $double_reputation_start_time && $current_datetime < $double_reputation_end_time) {
            $this->event = new DoubleReputationEvent($double_reputation_end_time);
        }

        /*****LIMITED TIME EVENTS*****/
        // July 2023 Lantern Event
        $july_2023_lantern_event_end_time = new DateTimeImmutable('2023-07-16');
        if($current_datetime > $july_2023_lantern_event_start_time && $current_datetime < $july_2023_lantern_event_end_time) {
            $this->event = new LanternEvent($july_2023_lantern_event_end_time);
        }
    }

    /**
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
            else if($hours && $hours != '00') {
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
        return $string;
    }

    public static function dateTimeFromMicrotime(float $microtime): DateTime|bool {
        return DateTime::createFromFormat(
            'U.u',
            number_format($microtime, 2, '.', '')
        );
    }

    public static function slug(string $string): string {
        return strtolower(str_replace(' ', '_', $string));
    }

    public static function unSlug(string $slug): string {
        return ucwords(str_replace('_', ' ', $slug));
    }

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

    public function getReactFile(string $component_name): string {
        $filename = "ui_components/build/{$component_name}.js";
        return $this->router->base_url . $filename . "?v=" .  filemtime($filename);
    }

    public function getCssFileLink(string $file_name): string {
        return $this->router->base_url . $file_name . "?v=" .  filemtime($file_name);
    }

    public static function clampNumber($number, $min, $max)
    {
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
}