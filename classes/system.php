<?php

/*	Class:		SystemFunctions

	Purpose: 	Handle database connection and queries. Handle storing and printing of error messages.

*/
class SystemFunctions {
    const KUNAI_PER_DOLLAR = 2;
    const LOGOUT_LIMIT = 120;
    const BLOODLINE_ROLL_CHANCE = 50;

    // Variable for error message
    public $message;
    public $message_displayed;

    // Variable for DB connection resource
    private $host;
    private $username;
    private $password;
    private $database;
    public $con;

    public $register_open;

    // Variables for query() function to track things
    public $db_result;
    public $db_query_type;
    public $db_num_rows;
    public $db_affected_rows;
    public $db_insert_id;

    public $debug = [
        'battle' => false,
        'damage' => false,
        'bloodline' => false,
    ];

    public function __construct() {
        require("./secure/vars.php");
        /** @var $host */
        /** @var $username */
        /** @var $password */
        /** @var $database */
        /** @var $register_open */
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        $this->register_open = isset($register_open) ? $register_open : false;
    }


    /* function dbConnect()
        Connects to a MySQL database and selects a DB. Stores connection resource in $con and returns.
        -Paramaters-
        None; Uses @host, @user_name, @password, @database from /secure/vars.php for DB credentials.
    */
    public function dbConnect() {
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
        -Paramaters-
        @raw_input: Input to be sanitized
    */
    public function clean($raw_input) {
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
    public function db_fetch($result = false, $return_type = 'assoc') {
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

    /* function message(message, force_message)

        Stores a message for display later.

        -Paramaters-

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
        -Paramaters-
        None
    */
    public function printMessage($force_display = false) {
        if(strlen($this->message) && (!$this->message_displayed || $force_display)) {
            echo "<p class='systemMessage'>$this->message</p>";
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
    public function send_pm($sender, $recipient, $subject, $message, $staff_level = 0) {
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

        $message = (in_array($_SESSION['user_id'], $admins)) ? $error_message : "An error has occured. Please make a report to the administrators if the problem persists.";

        $this->message($message);
        $this->printMessage();
        global $side_menu_start;
        global $side_menu_end;
        global $footer;

        echo $side_menu_start . $side_menu_end . $footer;
        exit;
    }

    /* function censor_check(word)
        Checks our list of banned words, returns true or false if censored words are detected.
        -Parameters-
        @string
    */
    public function censor_check($string) {
        $banned_words = [
            'fuck',
            'shit',
            'asshole',
            'bitch',
            'cunt',
            'fag',
            'asshat',
            'pussy',
            ' dick',
            'whore'
        ];
        foreach($banned_words as $word) {

            if(strpos(strtolower($string), $word) !== false) {

                return true;

            }

        }
        return false;

    }

    public function getMemes() {
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
            $search_array[count($search_array)] = "[img]";
            $search_array[count($search_array)] = "[/img]";
            $replace_array[count($replace_array)] = "<img src='";
            $replace_array[count($replace_array)] = "' style='/*IMG_SIZE*/' />";

            $search_array[count($search_array)] = "[url]http:";
            $search_array[count($search_array)] = "[url]www.";
            $search_array[count($search_array)] = "[/url]";

            $replace_array[count($replace_array)] = "<a href='http:";
            $replace_array[count($replace_array)] = "<a href='http://www.";
            $replace_array[count($replace_array)] = "'>[Link]</a>";
        }
        else {
            $reg_exUrl = "/(?:http|https)\:\/\/([a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,5})(?:\/[^\:\s\\\\]*)?/i";

            preg_match_all($reg_exUrl, $text, $matches);

            $websites = array();

            foreach($matches[0] as $pattern){

                preg_match($reg_exUrl, $pattern, $url);

                if(!$websites[$pattern]) {
                    $websites[$pattern] = trim($url[1]);
                    $text = str_replace($pattern, sprintf("<a href='%s' target='_blank'>%s</a>", $pattern, $websites[$pattern]), $text);
                }

            }
        }

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

    public function imageCheck($image, $size) {

        $avatar_limit = $size;



        $width = $avatar_limit;

        $height = $avatar_limit;



        return "<img src='$image' style='max-width:{$width}px;max-height:{$height}px;' />";

    }
    public function timeAgo($timestamp) {

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

    public function time_remaining($timestamp) {
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
    public function convertUser() {
        if(func_num_args() != 1){
            return false;
        }
        $identity = func_get_arg(0);
        if(filter_var($identity, FILTER_VALIDATE_INT)){
            $result = $this->db_fetch($this->query("SELECT `user_name` FROM `users` WHERE `user_id` = '{$identity}'"));
            return $result['user_name'];
        }
        else if(is_string($identity)){
            $identity = $this->clean($identity);
            $result = $this->db_fetch($this->query("SELECT `user_id` FROM `users` WHERE `user_name` = '{$identity}'"));
            return $result['user_id'];
        }

    }

    public function hash_password($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }

}
