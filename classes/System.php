<?php

require_once __DIR__ . '/EntityId.php';

/*	Class:		System
	Purpose: 	Handle database connection and queries. Handle storing and printing of error messages.
*/
class System {
    const KUNAI_PER_DOLLAR = 2;
    const LOGOUT_LIMIT = 120;
    const BLOODLINE_ROLL_CHANCE = 50;

    const MENU_USER = 'user';
    const MENU_ACTIVITY = 'activity';
    const MENU_VILLAGE = 'village';

    const NOT_IN_VILLAGE = 0;
    const IN_VILLAGE_OKAY = 1;
    const ONLY_IN_VILLAGE = 2;

    const SC_MODERATOR = 1;
    const SC_HEAD_MODERATOR = 2;
    const SC_ADMINISTRATOR = 3;
    const SC_HEAD_ADMINISTRATOR = 4;

    const DB_DATETIME_MS_FORMAT = 'Y-m-d H:i:s.u';

    // Variable for error message
    public $message;
    public $message_displayed;

    // Variable for DB connection resource
    private $host;
    private $username;
    private $password;
    private $database;
    public $con;

    public $environment;

    public $SC_OPEN;
    public $register_open;

    public $link;

    public $timezoneOffset;

    // Training boost switches
    public $TRAIN_BOOST = 0; // Extra points per training, 0 for none
    public $LONG_TRAIN_BOOST = 0; // Extra points per long training, 0 for none

    // Variables for query() function to track things
    public $db_result;
    public $db_query_type;
    public $db_num_rows;
    public $db_affected_rows;
    public $db_insert_id;

    public $SC_STAFF_COLORS = array(
        System::SC_MODERATOR => array(
            'staffBanner' => "moderator",
            'staffColor' => "009020",
            'pm_class' => 'moderator'
        ),
        System::SC_HEAD_MODERATOR => array(
            'staffBanner' => "head moderator",
            'staffColor' => "0090A0",
            'pm_class' => 'headModerator'
        ),
        System::SC_ADMINISTRATOR => array(
            'staffBanner' => "administrator",
            'staffColor' => "A00000",
            'pm_class' => 'administrator'
        ),
        System::SC_HEAD_ADMINISTRATOR => array(
            'staffBanner' => "head administrator",
            'staffColor' => "A00000",
            'pm_class' => 'administrator'
        )
    );

    // Keep in sync with pages.php
    const PAGE_IDS = [
        'profile' => 1,
        'settings' => 3,
        'members' => 6,
        'bloodline' => 10,
        'arena' => 12,
        'mod' => 16,
        'admin' => 17,
        'report' => 18,
        'battle' => 19,
        'spar' => 22,
        'mission' => 14,
        'rankup' => 25,
    ];
    public array $links = [];

    //Chat variables
    const CHAT_MAX_POST_LENGTH = 350;

    // Default layout
    const DEFAULT_LAYOUT = 'shadow_ribbon';
    const VERSION_NUMBER = '0.8.0';

    // Map size
    const MAP_SIZE_X = 18;
    const MAP_SIZE_Y = 12;

    // Misc stuff
    const SC_MAX_RANK = 3;

    const MAX_LINK_DISPLAY_LENGTH = 60;

    public static $banned_words = [
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
        'bich',
        'bish',

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
        'rape ',

        'dildo',
    ];

    public $debug = [
        'battle' => false,
        'battle_effects' => false,
        'jutsu_collision' => false,
        'damage' => false,
        'bloodline' => false,
    ];

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

        $this->environment = isset($ENVIRONMENT) ? $ENVIRONMENT : 'dev';
        $this->link = isset($web_url) ? $web_url : 'http://localhost/';

        $this->register_open = isset($register_open) ? $register_open : false;
        $this->SC_OPEN = isset($SC_OPEN) ? $SC_OPEN : false;

        $this->links = [];
        foreach(self::PAGE_IDS as $slug => $id) {
            $this->links[$slug] = $this->link . '?id=' . $id;
        }

        $this->timezoneOffset = date('Z');
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
        $input = htmlspecialchars($input, ENT_QUOTES);

        $input = str_replace($replace_terms, $search_terms, $input);
        $input = mysqli_real_escape_string($this->con, $input);
        return $input;
    }

    /* function query(query) */
    public function query($query) {
        $query = trim($query);

        $this->db_query_type = strtolower(substr($query, 0, strpos($query, ' ')));

        if(!$this->con) {
            $this->dbConnect();
        }

        $result = mysqli_query($this->con, $query) or $this->error(mysqli_error($this->con));

        if($this->db_query_type == 'select') {
            $this->db_num_rows = mysqli_num_rows($result);
            $this->db_result = $result;
        }
        else {
            $this->db_affected_rows = mysqli_affected_rows($this->con);
        }

        if($this->db_query_type == 'insert') {
            $this->db_insert_id = mysqli_insert_id($this->con);
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
    public function message($message, $force_message = false) {
        if(strlen($this->message) == 0 || $force_message) {
            $this->message = $message;
        }
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
     *
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
        if($this->db_affected_rows) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * Logs an error message(usually from DB), displays a generic error message to user, displays page end, then exits script.
     *
     * @param $error_message
     */
    public function error($error_message) {
        // DEBUG MODE
        // error_log($error_message);
        //echo $error_message;

        $admins = array(1, 190, 193);

        if($this->environment == 'dev' || in_array($_SESSION['user_id'], $admins)) {
            $message = $error_message;
        }
        else {
            $message = "An error has occured. Please make a report to the administrators if the problem persists.";
        }

        $this->message($message);
        $this->printMessage();

        global $side_menu_start;
        global $side_menu_end;
        global $footer;

        $pages = require 'pages.php';

        echo $side_menu_start;
        foreach($pages as $id => $page) {
            if(!isset($page['menu']) || $page['menu'] != System::MENU_USER) {
                continue;
            }

            echo "<li><a href='{$this->link}?id=$id'>" . $page['title'] . "</a></li>";
        }
        echo $side_menu_end . $footer;
        exit;
    }

    /* function censor_check(word)
        Checks our list of banned words, returns true or false if censored words are detected.
        -Parameters-
        @string
    */
    public function censor_check($string): bool {
        foreach(self::$banned_words as $word) {
            if(strpos(strtolower($string), $word) !== false) {
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
        $memes = require 'memes.php';

        return [
            'codes' => array_map(function ($meme) {
                return $meme['code'];
            }, $memes),
            'images' => array_map(function ($meme) {
                return $meme['image'];
            }, $memes),
            'texts' => array_map(function ($meme) {
                return $meme['text'];
            }, $memes),
        ];
    }

    public function html_parse($text, $img = false, $faces = false) {
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

    public function log($type, $title, $contents) {
        $type = $this->clean($type);
        $title = $this->clean($title);
        $contents = $this->clean($contents);

        $this->query("INSERT INTO `logs` (`log_type`, `log_title`, `log_time`, `log_contents`)
			VALUES ('$type', '$title', " . time() . ", '$contents')");
    }

    public function hash_password($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify_password($password, $hash): bool {
        return password_verify($password, $hash);
    }

    public function renderStaticPageHeader() {
        $system = $this;

        switch(System::DEFAULT_LAYOUT) {
            case 'cextralite':
                require("layout/cextralite.php");
                break;
            case 'classic_blue':
                require("layout/classic_blue.php");
                break;
            case 'geisha':
                require("layout/geisha.php");
                break;
            case 'shadow_ribbon':
            default:
                require("layout/shadow_ribbon.php");
                break;
        }

        /**
         * @var $heading
         * @var $top_menu
         * @var $header
         * @var $body_start
         */

        echo $heading;
        echo $top_menu;
        echo $header;
        echo str_replace("[HEADER_TITLE]", "Rules", $body_start);
    }

    public function renderStaticPageFooter() {
        $system = $this;

        switch(System::DEFAULT_LAYOUT) {
            case 'cextralite':
                require("layout/cextralite.php");
                break;
            case 'classic_blue':
                require("layout/classic_blue.php");
                break;
            case 'geisha':
                require("layout/geisha.php");
                break;
            case 'shadow_ribbon':
            default:
                require("layout/shadow_ribbon.php");
                break;
        }

        /**
         * @var $side_menu_start
         * @var $side_menu_end
         *
         * @var $login_menu
         * @var $footer
         */
        if(isset($_SESSION['user_id'])) {
            echo $side_menu_start;
            echo $side_menu_end;
        }
        else {
            echo $login_menu;
        }

        echo str_replace('<!--[VERSION_NUMBER]-->', System::VERSION_NUMBER, $footer);
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

    public static function diminishing_returns($val, $scale) {
        if($val < 0) {
            return -self::diminishing_returns(-$val, $scale);
        }
        $mult = $val / $scale;
        $trinum = (sqrt(8.0 * $mult + 1.0) - 1.0) / 2.0;
        return $trinum * $scale;
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

    public static function dateTimeFromMicrotime(float $microtime) {
        return DateTime::createFromFormat(
            'U.u',
            number_format($microtime, 2, '.', '')
        );
    }
}

