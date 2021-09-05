<?php

/*	Class:		SystemFunctions

	Purpose: 	Handle database connection and queries. Handle storing and printing of error messages.

*/
class SystemFunctions {
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
    public function html_parse($text, $img = false, $faces = false) {
        $search_array = array(
            "[b]","[/b]","[u]","[/u]","[i]","[/i]",
            "&lt;3","[strike]","[/strike]","[super]","[/super]","[sub]","[/sub]", "[center]", "[/center]", "[right]", "[/right]",
            ":lesiregusta:", ":lemadamgusta:",
            ":lol:", ":yuno:", ":challengeaccepted:", ":foreveralone:", ":rageface:", ":megusta:", ":heckyeah:", ":okay:", ":screwthis:",
            ":skyrimstan:", ":asianfather:", ":iknowthatfeel:", ":skeptical:", ":truestory:", ":pokerface:", ":awwyeah:",
            ":ohgodwhy:", ":wut:", ":areyouserious:", ":lololort:", ":likeasir:", ":likealady:", ":allofthe:", ":grumpycat:", ":notbad:",
            ":whathasbeenseen:", ":no:", ":motherofgod:", ":ifyouknow:",":creepygusta:",":whatthe:",
            ":insanitywolf:", ":why:", ":yoda:", ":wrongfail:", ":alot:", ":herpderp:", ":doge:", ":confessionbear:", ":kappa:",
            ":howaboutno:", ':awkwardseal:', ':wegotabadass:', ':facepalm:',
            ':opieop:', ':oskomodo:', ':babyrage:', ':pogchamp:', ':smileyderp:', 'BibleThump', 'HeyGuys', ':aliensguy:', 'lilyDango', ':likeaboss:',
            ':vaultboy:');


        if($faces) {
            $replace_array = array("<b>","</b>","<u>","</u>","<i>","</i>","&hearts;",
                "<del>","</del>","<sup>","</sup>","<sub>","</sub>", "<p style='text-align:center;'>", "</p>",
                "<p style='text-align:right;'>", "</p>",
                "<img src=\"http://lsmjudoka.com/images/memes/like_a_sir_gusta.png\" />",
                "<img src=\"http://lsmjudoka.com/images/memes/like_a_lady_gusta.png\" />",
                "<img src=\"http://lsmjudoka.com/images/memes/small_lol.png\" alt=':lol:' title=':lol:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/yuno.png\" alt=':yuno:' title=':yuno:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/challenge_accepted.png\" alt=':challengeaccepted:' title=':challengeaccepted:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/forever_alone.png\" alt=':foreveralone:' title=':foreveralone:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/rage_face.png\" alt=':rageface:' title=':rageface:'  />",
                "<img src=\"http://lsmjudoka.com/images/memes/me_gusta.png\" alt=':megusta:' title=':megusta:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/f_yeah.png\" alt=':heckyeah:' title=':heckyeah:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/okay.png\" alt=':okay:' title=':okay:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/fts.png\" alt=':screwthis:' title=':screwthis:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/skyrim_stan.png\" alt=':skyrimstan:' title=':skyrimstan:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/asian_father.png\" alt=':asianfather:' title=':asianfather:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/i_know_that_feel_bro.png\" alt=':iknowthatfeel:' title=':iknowthatfeel:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/skeptical.png\" alt=':skeptical:' title=':skeptical:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/true_story.png\" alt=':truestory:' title=':truestory:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/poker_face.png\" alt=':pokerface:' title=':pokerface:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/aww_yeah.png\" alt=':awwyeah:' title=':awwyeah:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/oh_god_why.png\" alt=':ohgodwhy:' title=':ohgodwhy:'  />",
                "<img src=\"http://lsmjudoka.com/images/memes/wut.gif\" alt=':wut:' title=':wut:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/are_you_serious.png\" alt=':areyouserious:' title=':areyouserious:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/reverse_lol.png\" />",
                "<img src=\"http://lsmjudoka.com/images/memes/like_a_sir.png\" alt=':likeasir:' title=':likeasir:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/like_a_lady.png\" alt=':likealady:' title=':likealady:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/all_of_the.png\" alt=':allofthe:' title=':allofthe:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/grumpy_cat.png\" alt=':grumpycat:' title=':grumpycat:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/not_bad.png\" alt=':notbad:' title=':notbad:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/what_has_been_seen.png\" alt=':whathasbeenseen:' title=':whathasbeenseen:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/no.png\" alt=':no:' title=':no:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/mother_of_god.png\" alt=':motherofgod:' title=':motherofgod:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/if_you_know.png\" alt=':ifyouknow:' title=':ifyouknow:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/creepy_gusta.png\" alt=':creepygusta:' title=':creepygusta:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/what_the.png\" alt=':whatthe:' title=':whatthe:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/insanity_wolf.png\" alt=':insanitywolf:' title=':insanitywolf:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/why.png\" alt=':why:' title=':why:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/yoda_meme.png\" alt=':yoda:' title=':yoda:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/staff_fail.png\" />",
                "<img src=\"http://lsmjudoka.com/images/memes/alot.png\" alt=':alot:' title=':alot:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/herp_derp.png\" alt=':herpderp:' title=':herpderp:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/doge.png\" alt=':doge:' title=':doge:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/confession_bear.png\" alt=':confessionbear:' title=':confessionbear:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/kappa.png\" alt=':kappa:' title=':kappa:' />",
                "<img src=\"http://lsmjudoka.com/images/memes/how_about_no.png\" alt=':howaboutno:' title=':howaboutno:' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/awkward_seal.png\" title=':awkwardseal:' alt=':awkwardseal:' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/watch_out_we_got_a_badass.png\" title=':wegotabadass:' alt=':wegotabadass:' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/facepalm.png\" title=':facepalm:' alt=':facepalm:' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/opieop.png\" title=':opieop:' alt=':opieop:' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/oskomodo.png\" title=':oskomodo:' alt=':oskomodo:' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/babyrage.png\" title=':babyrage:' alt=':babyrage:' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/pogchamp.png\" title=':pogchamp:' alt=':pogchamp:' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/smiley_derp.png\" title=':smileyderp:' alt=':smileyderp:' />",
                "<img src=\"https://static-cdn.jtvnw.net/emoticons/v1/86/1.0\" title='BibleThump' alt='BibleThump' />",
                "<img src=\"https://static-cdn.jtvnw.net/emoticons/v1/30259/1.0\" title='HeyGuys' alt='HeyGuys' />",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/aliens_guy.png\" title=':aliensguy:' alt=':aliensguy:' />",
                "<img src=\"https://static-cdn.jtvnw.net/emoticons/v1/14856/1.0\" />",
                "<img src=\"./images/memes/likeaboss.png\" title=':likeaboss:' alt=':likeaboss:'>",
                "<img src=\"http://worldofbleach-rpg.com/images/memes/vault_boy.png\" title=':vaultboy:' alt=':vaultboy:' />",
            );
        }

        else {

            $replace_array = array("<b>","</b>","<u>","</u>","<i>","</i>","&hearts;",
                "<del>","</del>","<sup>","</sup>","<sub>","</sub>", "<p style='text-align:center;'>", "</p>",
                "<p style='text-align:right;'>", "</p>",
                ":like a sir gusta:",
                ":like a lady gusta:",
                ":lol:",
                ":Y u no?:",
                ":challenge accepted:",
                ":forever alone:",
                ":rage face:",
                ":me gusta:",
                ":heck yeah:",
                ":okay:",
                ":screw this:",
                ":skyrim stan:",
                ":asian father:",
                ":i know that feel:",
                ":skeptical:",
                ":true story:",
                ":poker face:",
                ":aww yeah:",
                ":oh god why:",
                ":wut:",
                ":are you serious:",
                ":lol:",
                ":like a sir:",
                ":like a lady:",
                ":all of the:",
                ":grumpy cat:",
                ":not bad:",
                ":what has been seen:",
                ":no:",
                ":mother of god:",
                ":if you know what I mean:",
                ":creepy gusta:",
                ":what the:",
                ":insanity wolf:",
                ":why:",
                ":yoda:",
                ":staff-only fail:",
                ":alot:",
                ":herp derp:",
                ":doge:",
                ":confession bear:",
                ":kappa:",
                ":how about no:",
                ":awkward seal:",
                ":watch out we got a badass here:",
                ":facepalm:",
                ":opieop:",
                ":oskomodo:",
                ":babyrage:",
                ":pogchamp:",
                ":smileyderp:",
                "BibleThump",
                "HeyGuys",
                ":aliensguy:",
                "Dango",
                ":likeaboss:",
                ":vaultboy:",
            );

        }

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

}