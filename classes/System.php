<?php

use JetBrains\PhpStorm\Pure;

require_once __DIR__ . '/EntityId.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/MarkdownParser.php';
require_once __DIR__ . '/API.php';
require_once __DIR__ . '/Layout.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Route.php';

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

    const DB_DATETIME_MS_FORMAT = 'Y-m-d H:i:s.u';

    const SC_ADMIN_EMAIL = "admin@shinobichronicles.com";
    const SC_NO_REPLY_EMAIL = "no-reply@shinobichronicles.com";
    const UNSERVICEABLE_EMAIL_DOMAINS = ['hotmail.com', 'live.com', 'msn.com', 'outlook.com'];

    // TODO: Remove! This is a temporary way to do events
    const SC_EVENT_START = 0;
    const SC_EVENT_END = 1641769200;
    const SC_EVENT_NAME = 'Holiday 2021';

    public static bool $SC_EVENT_ACTIVE = true;

    public static array $villages = ['Stone', 'Cloud', 'Leaf', 'Sand', 'Mist'];

    // Variable for error message
    public string $message = "";
    public bool $message_displayed = false;

    public array $debug_messages = [];

    // Variable for DB connection resource
    private string $host;
    private string $username;
    private string $password;
    private string $database;
    public $con;

    public $environment;

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

    // Variables for query() function to track things
    public $db_result;
    public string $db_query_type;
    public int $db_last_num_rows = 0;
    public int $db_last_affected_rows;
    public $db_last_insert_id;

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
            'staffColor' => "#A000B0",
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

    //Chat variables
    const CHAT_MAX_POST_LENGTH = 350;

    // Default layout
    const DEFAULT_LAYOUT = 'shadow_ribbon';
    const VERSION_NUMBER = '0.8.0';

    // Misc stuff
    const SC_MAX_RANK = 4;

    const MAX_LINK_DISPLAY_LENGTH = 60;

    public static array $banned_words = [
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

    public array $debug = [
        'battle' => false,
        'battle_effects' => false,
        'jutsu_collision' => false,
        'damage' => false,
        'bloodline' => false,
        'stat_cut' => false,
    ];

    public Router $router;

    public function __construct() {
        require __DIR__ . "/../secure/vars.php";
        /** @var $host */
        /** @var $username */
        /** @var $password */
        /** @var $database */
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        $this->environment = $ENVIRONMENT ?? self::ENVIRONMENT_DEV;
        $this->register_open = $register_open ?? false;
        $this->SC_OPEN = $SC_OPEN ?? false;
        $this->USE_NEW_BATTLES = $USE_NEW_BATTLES ?? false;

        $this->router = new Router($web_url ?? 'http://localhost/');

        $this->timezoneOffset = date('Z');

        // TODO: REMOVE TEMPORARY EVENT STUFF
        if(time() > self::SC_EVENT_END) {
            self::$SC_EVENT_ACTIVE = false;
        }
    }

    /* function dbConnect()
        Connects to a MySQL database and selects a DB. Stores connection resource in $con and returns.
        -Parameters-
        None; Uses @host, @user_name, @password, @database from /secure/vars.php for DB credentials.
    */
    public function dbConnect(): mysqli {
        if($this->con) {
            return $this->con;
        }

        $con = new mysqli($this->host, $this->username, $this->password) or $this->error(mysqli_error($this->con));
        mysqli_select_db($con, $this->database) or $this->error(mysqli_error($this->con));

        $this->con = $con;
        return $con;
    }

    /* function clean(raw_input)
        Cleans raw input to be safe for use in queries. Requires $this->con to have a connection for using mysqli_real_escape_string
        -Parameters-
        @raw_input: Input to be sanitized
    */
    public function clean($raw_input): string {
        if(!$this->con) {
            $this->dbConnect();
        }

        $input = trim($raw_input);
        $search_terms = array('&yen;');
        $replace_terms = array('[yen]');
        $input = str_replace($search_terms, $replace_terms, $input);
        $input = htmlspecialchars(
            string: $input,
            flags: ENT_QUOTES,
            double_encode: false
        );

        $input = str_replace($replace_terms, $search_terms, $input);
        $input = mysqli_real_escape_string($this->con, $input);
        return $input;
    }

    /* function query(query) */
    public function query($query, $debug = false): mysqli_result|bool {
        $query = trim($query);

        //Debugging
        if($debug) {
            $this->debugMessage($query);
            return false;
        }

        $expected_query_types = [
            'select',
            'insert',
            'update',
            'delete'
        ];
        $normalized_query = trim(strtolower($query));

        // default to first word
        $this->db_query_type = explode(' ', $normalized_query)[0];

        // double check for expected types in case of weird whitespace
        foreach($expected_query_types as $query_type) {
            if(str_starts_with($normalized_query, $query_type)) {
                $this->db_query_type = $query_type;
            }
        }

        if(!$this->con) {
            $this->dbConnect();
        }

        $result = mysqli_query($this->con, $query) or $this->error(mysqli_error($this->con));

        if($this->db_query_type == 'select') {
            $this->db_last_num_rows = mysqli_num_rows($result);
            $this->db_result = $result;
        }
        else {
            $this->db_last_affected_rows = mysqli_affected_rows($this->con);
        }

        if($this->db_query_type == 'insert') {
            $this->db_last_insert_id = mysqli_insert_id($this->con);
        }
        return $result;
    }

    /* function db_fetch(result set, return_type)

    */
    public function db_fetch($result = false, $return_type = 'assoc'): ?array {
        if(!$result) {
            $result = $this->db_result;
        }

        if($return_type == 'assoc') {
            return mysqli_fetch_assoc($result);
        }
        else {
            return mysqli_fetch_array($result);
        }
    }

    /**
     * @param false  $result
     * @param string $return_type
     * @return array|null
     */
    public function db_fetch_all($result = false, ?string $id_column = null): array {
        if(!$result) {
            $result = $this->db_result;
        }

        $entities = [];
        while($row = $this->db_fetch($result)) {
            if($id_column) {
                $entities[$row[$id_column]] = $row;
            }
            else {
                $entities[] = $row;
            }
        }
        return $entities;
    }


    /* function message(message, force_message)

        Stores a message for display later.

        -Parameters-

        @message: 		Message to be stored for display

        @force_message: Whether or not to overwrite a pre-stored message that has not been displayed. Defaults to false.

    */
    public function message($message, $force_message = false): void {
        if(strlen($this->message) == 0 || $force_message) {
            $this->message = $message;
        }
    }

    public function debugMessage($message) {
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


    /**
     * Sends PM to specified recipient
     * * OLD PRIVATE MESSAGES
     * @param $sender
     * @param $recipient
     * @param $subject
     * @param $message
     * @param int $staff_level
     * @return bool
     */
    public function send_pm($sender, $recipient, $subject, $message, $staff_level = 0): bool {
        if(!$this->con) {
            $this->dbConnect();
        }

        $time = time();
        $type = 0;
        $userlevel = 1;
        $sender = $this->clean($sender);
        $subject = $this->clean($subject);
        $recipient = $this->clean($recipient);
        $message = $this->clean(trim($message));

        $query = "INSERT INTO `private_messages` (`sender`, `recipient`, `subject`, `message`, `time`, `message_read`, `staff_level`)
				VALUES ('$sender', '$recipient', '$subject', '$message', " . time() . ", 0, '$staff_level')";
        $this->query($query);
        if($this->db_last_affected_rows) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function currentTimeMs(): int {
        return floor(microtime(true) * 1000);
    }

    /**
     * Logs an error message(usually from DB), displays a generic error message to user, displays page end, then exits script.
     *
     * @param $error_message
     */
     public function error($error_message): void {
        error_log($error_message . ' in ' . System::simpleStackTrace());
        // DEBUG MODE
        //echo $error_message;

        $admins = array(1, 190, 193);

        if($this->environment == 'dev' || in_array($_SESSION['user_id'] ?? null, $admins)) {
            $message = $error_message;
        }
        else {
            $message = "An error has occurred. Please make a report to the administrators if the problem persists.";
        }

        $this->message($message);
        if($this->is_api_request) {
            API::exitWithError($message);
        }
        $this->printMessage(true);

        global $side_menu_start;
        global $side_menu_end;
        global $footer;

        $pages = Router::$routes;

        echo $side_menu_start;
        foreach($pages as $id => $page) {
            if(!isset($page->menu) || $page->menu != Route::MENU_USER) {
                continue;
            }

            echo "<li><a href='{$this->router->base_url}?id=$id'>" . $page->title . "</a></li>";
        }
        echo $side_menu_end . $footer;
        exit;
    }

    /* function explicitLanguageCheck(word)
        Checks our list of banned words, returns true or false if censored words are detected.
        -Parameters-
        @string
    */
    public function explicitLanguageCheck($string): bool {
        foreach(self::$banned_words as $word) {
            if(str_contains(strtolower($string), $word)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param      $text
     * @return string
     */
    public function explicitLanguageReplace($text): string {
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
            'texts' => []
        ];
        foreach ($cleaned_memes as $meme)
        {
            $meme_code = strtolower(':' . preg_replace($file_type_filter,'', str_replace($search_symbols, '', $meme)) . ':');

            if (in_array($meme_code, $meme_array['codes'])) continue;

            $meme_array['codes'][] = $meme_code;
            $meme_array['images'][] = "<img src='./images/memes/${meme}' title='${meme_code}' alt='${meme_code}' style='max-width: 75px;max-height: 75px'/>";
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

        $reg_exUrl = '/((?:http|https)\:\/\/(?:[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,5})(?:\/[^\:\s\\\\]*)?)/i';
        if(self::LOCAL_HOST) {
            //Allow regex to work with local links if system is set to local host
            $reg_exUrl = '/((?:http|https)\:\/\/(?:[a-zA-Z0-9\-\.]+[a-zA-Z]{2,5})(?:\/[^\:\s\\\\]*)?)/i';
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

        array_push($search_array, '[image_prefix]');
        array_push($replace_array, 'https://');

        array_push($search_array, '\r\n');
        array_push($replace_array, '<br />');

        array_push($search_array, '&amp;#039;');
        array_push($replace_array, "&#039;");

        array_push($search_array, '&amp;lt;');
        array_push($replace_array, "&lt;");
        array_push($search_array, '&amp;gt;');
        array_push($replace_array, "&gt;");

        $text = str_ireplace($search_array,$replace_array,$text);

        return $text;

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
            $message .= $days . ($days > 1 ? " days, " : "day, ") .
                $hours . ($hours > 1 ? " hours, " : "hour, ") .
                $minutes . ($minutes > 1 ? " minutes" : "minute");
        }
        else if($hours) {
            $minutes -= $hours * 60;
            $message .= $hours . ($hours > 1 ? " hours, " : "hour, ") .
                $minutes . ($minutes > 1 ? " minutes" : "minute");
        }
        else {
            $message .= $minutes . ($minutes > 1 ? " minutes" : "minute");
        }
        return $message;
    }

    public function log($type, $title, $contents): void {
        $type = $this->clean($type);
        $title = $this->clean($title);

        if (is_array($contents))
        {
            $contents = json_encode($contents);
        }

        $contents = $this->clean($contents);

        $this->query("INSERT INTO `logs` (`log_type`, `log_title`, `log_time`, `log_contents`)
			VALUES ('$type', '$title', " . time() . ", '$contents')");
    }

    /**
     * @throws Exception
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
               throw new Exception("Invalid currency type!");
       }

        $this->query(
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
                '{$this->clean($transaction_description)}',
                " . time() . "
            )");
    }

    public function hash_password($password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify_password($password, $hash): bool {
        return password_verify($password, $hash);
    }

    public function fetchLayoutByName($layout): Layout {
        $system = $this;

        switch($layout) {
            case 'cextralite':
                return require "layout/cextralite.php";
            case 'classic_blue':
                return require  "layout/classic_blue.php";
            case 'shadow_ribbon':
                return require  "layout/shadow_ribbon.php";
            case 'geisha':
                return require  "layout/geisha.php";
            case 'blue_scroll':
                return require  "layout/blue_scroll.php";
            case 'rainbow_road':
                return require  "layout/rainbow_road.php";
            default:
                return require  "layout/" . self::DEFAULT_LAYOUT . ".php";
        }
    }

    public function fetchGlobalMessage(): ?array {
        $result = $this->query("SELECT `global_message`, `time` FROM `system_storage` LIMIT 1");
        if($result->num_rows < 1) {
            return null;
        }

        $results = $this->db_fetch($result);

        return [
            'message' => str_replace("\r\n", "<br />", $results['global_message']),
            'time' => date("l, M j, Y - g:i A", $results['time'])
        ];
    }

    #[Pure]
    public function getReactFile(string $component_name): string {
        $filename = "ui_components/build/{$component_name}.js";
        return $this->router->base_url . $filename . "?v=" .  filemtime($filename);
    }

    /**
     * @param string $entity_id
     * @return EntityId
     * @throws Exception
     */
    public static function parseEntityId(string $entity_id): EntityId {
        $arr = explode(':', $entity_id);
        if(count($arr) != 2) {
            throw new Exception("Invalid entity id {$entity_id}!");
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
}