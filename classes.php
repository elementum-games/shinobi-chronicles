<?php
/*

File: 		classes.php

Coder:		Levi Meahan

Created:	02/21/2012

Revised:	08/13/2014 by Levi Meahan

Purpose:	Contains declarations for all OOP classes. Documentation included with each class or in documentation.html

*/

require("user_class.php");

/*	Class:		SystemFunctions

	Purpose: 	Handle database connection and queries. Handle storing and printing of error messages.

*/
class SystemFunctions {	
	// Variable for error message
	public $message;
	public $message_displayed;
	
	// Variable for DB connection resource
	public $con;
	
	// Variables for query() function to track things
	public $db_result;
	public $db_query_type;
	public $db_num_rows;
	public $db_affected_rows;
	
	/* function dbConnect()
		Connects to a MySQL database and selects a DB. Stores connection resource in $con and returns. 
		-Paramaters-
		None; Uses @host, @user_name, @password, @database from /secure/vars.php for DB credentials.
	*/
	public function dbConnect() {
		if($this->con) {
			return $this->con;
		}
		
		require("./secure/vars.php");
		$con = new mysqli($host, $username, $password) or $this->error(mysqli_error($this->con));
		mysqli_select_db($con, $database) or $this->error(mysqli_error($this->con));
		
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
	
}

/* 	Class:		AI
	Purpose:	Contains all information for a specific AI, functions for selecting move, calculated damage dealt and 
				received, etc
*/
class AI {	
	public $id;
	public $ai_id;
	public $name;
	public $max_health;
	public $level;
	public $gender;
	
	public $ninjutsu_offense;
	public $genjutsu_offense;
	public $taijutsu_offense;
	public $ninjutsu_defense;
	public $genjutsu_defense;
	public $taijutsu_defense;
	
	public $speed;
	public $strength;
	public $intelligence;
	public $willpower;

	public $money;
	
	public $moves;
	
	public $current_move;
	
	/* function __construct(ai_id)
	Creates instance of the AI class. Sanitizes and checks AI id to ensure AI exists.
	-Paramaters-
	@ai_id:	Id of the AI, used to select and update data from database
	*/
	public function __construct($ai_id) {
		global $system;
		$this->system =& $system;
		if(!$ai_id) {
			$system->error("Invalid AI opponent!");
			return false;
		}
		$this->ai_id = $system->clean($ai_id);
		$this->id = 'A' . $this->ai_id;
		
		
		$result = $system->query("SELECT `ai_id`, `name` FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
		if(mysqli_num_rows($result) == 0) {
			$system->error("AI does not exist!");
			return false;
		}
		
		$result = $this->system->db_fetch($result);
		
		$this->name = $result['name'];
		
		if(!isset($_SESSION['ai_logic'])) {
			$_SESSION['ai_logic'] = array();
			$_SESSION['ai_logic']['special_move_used'] = false;
		}
	}
	
	/* function loadData()
		Loads AI data from the database into class members
		-Paramaters-
	*/
	public function loadData() {
		$result = $this->system->query("SELECT * FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
		$ai_data = $this->system->db_fetch($result);
		
		$this->rank = $ai_data['rank'];
		$this->max_health = $ai_data['max_health'];
		if(isset($_SESSION['ai_health'])) {
			$this->health = $_SESSION['ai_health'];
		}
		else {
			$this->health = $this->max_health;
			$_SESSION['ai_health'] = $this->health;
		}
		
		$this->gender = "Male";
		
		$this->level = $ai_data['level'];
		
		$this->ninjutsu_skill = $ai_data['ninjutsu_skill'];
		$this->genjutsu_skill = $ai_data['genjutsu_skill'];
		$this->taijutsu_skill = $ai_data['taijutsu_skill'];
		
		$this->cast_speed = $ai_data['cast_speed'];
		
		$this->speed = $ai_data['speed'];
		$this->strength = $ai_data['strength'];
		$this->intelligence = $ai_data['intelligence'];
		$this->willpower = $ai_data['willpower'];
	
		$attributes = array('cast_speed', 'speed', 'strength', 'intelligence', 'willpower');
		foreach($attributes as $attribute) {
			if($this->{$attribute} <= 0) {
				$this->{$attribute} = 1;
			}
		}
	
		$this->money = $ai_data['money'];
	
		$moves = json_decode($ai_data['moves']);
		
		$count = 0;
		foreach($moves as $move) {
			$this->moves[$count]['battle_text'] = $move->battle_text;
			$this->moves[$count]['power'] = $move->power;
			$this->moves[$count]['jutsu_type'] = $move->jutsu_type;
			if($move->jutsu_type == 'genjutsu') {
				$this->moves[$count]['effect'] = 'residual_damage';
				$this->moves[$count]['effect_amount'] = 30;
				$this->moves[$count]['effect_length'] = 3;
			}
			$count++;
		}
                
                $jutsuTypes = ['ninjutsu', 'taijutsu'];
                $aiType = rand(0, 1);
                $result = $this->system->query("SELECT `battle_text`, `power`, `jutsu_type` FROM `jutsu` WHERE `rank` = '{$this->rank}' AND `jutsu_type` = '{$jutsuTypes[$aiType]}' AND `purchase_type` != '1' AND `purchase_type` != '3' LIMIT 5");
                while ($row = $this->system->db_fetch($result)) {
                    $moveArr = [];
                    foreach($row as $type => $data) {
                        if($type == 'battle_text') {
                            $search = ['[player]', '[opponent]', '[gender]', '[gender2]'];
                            $replace = ['opponent1', 'player1', 'he', 'his'];
                            $data = str_replace($search, $replace, $data);
                            $data = str_replace(['player1', 'opponent1'], ['[player]', '[opponent]'], $data);
                        }
                        $moveArr[$type] = $data;
                    }
                    $this->moves[] = $moveArr;
                }
	}

	/* function chooseMove()
	*/
	public function chooseMove() {
		if(!$_SESSION['ai_logic']['special_move_used'] && $this->moves[1]) {
			$this->current_move =& $this->moves[1];
			$_SESSION['ai_logic']['special_move_used'] = true;
		}
		else {
                    $randMove = rand(1, (count($this->moves) - 1));
                    $this->current_move =& $this->moves[$randMove];
		}
		
		return $this->current_move;
	}
	
	/* function calcDamage() CONTAINS TEMP FIX
	*	Calculates raw damage based on AI stats and jutsu or item strength
		-Paramaters-
		@attack: Copy of the attack data.
		@attack_type (default_jutsu, equipped_jutsu, item, bloodline_jutsu,): 
			Type of thing to check for, either item or jutsu
	*/
	public function calcDamage($attack, $attack_type = 'default_jutsu') {
		switch($attack_type) {
			case 'default_jutsu':
				break;
			case 'equipped_jutsu':
				break;
			default:
				throw new Exception("Invalid jutsu type!");
				break;
		}
		$offense_skill = $attack['jutsu_type'] . '_skill';
		$offense_boost = 0;
		if(isset($this->{$attack['jutsu_type'] . '_nerf'})) {
			echo "Nerf: " . $this->{$attack['jutsu_type'] . '_nerf'} . "<br />";
			$offense_boost -= $this->{$attack['jutsu_type'] . '_nerf'};
		}
		
		// TEMP FIX (should be 0.10)
		$offense = (35 + $this->{$offense_skill} * 0.09);
		$offense += $offense_boost;	
		
		$min = 20;
		$max = 35;
		$rand = (int)(($min + $max) / 2);
		// $rand = mt_rand($min, $max);
		
		$damage = round($offense * $attack['power'] * $rand, 2);
		
		return $damage;
	}
	
	/* function calcDamageTaken()
	*	Calculates final damage taken based on AI stats and attack type
		-Paramaters-
		@raw_damage: Raw damage dealt before defense
		@defense_type (ninjutsu, taijutsu, genjutsu, weapon): 
			Type of thing to check for, either item or jutsu
	*/
	public function calcDamageTaken($raw_damage, $defense_type) {		
		$defense = 50;
		
		switch($defense_type) {
			case 'ninjutsu':
				$defense += diminishing_returns($this->ninjutsu_skill * 0.03, 40);
				break;
			case 'genjutsu':
				$defense += diminishing_returns($this->genjutsu_skill * 0.03, 40);
				break;
			case 'taijutsu':
				$defense += diminishing_returns($this->taijutsu_skill * 0.03, 40);
				break;
		}	
		
		$damage = round($raw_damage / $defense, 2);
		if($damage < 0) {
			$damage = 0;
		}
		return $damage;
	}
	
	public function updateData() {
		$_SESSION['ai_health'] = $this->health;
	}
}

/* Class:		Bloodline
*/
class Bloodline {
	public $bloodline_id;
	public $id;
	public $name;
	public $clan_id;
	public $rank;
	
	public $passive_boosts;
	public $combat_boosts;
	public $jutsu;
	
	public function __construct($bloodline_id, $user_id = false) {
		global $system;
		$this->system =& $system;
		if(!$bloodline_id) {
			$system->error("Invalid bloodline id!");
			return false;
		}
		$this->bloodline_id = $system->clean($bloodline_id);
		$this->id = 'BL' . $this->user_id;
			
		$result = $system->query("SELECT * FROM `bloodlines` WHERE `bloodline_id`='$this->bloodline_id' LIMIT 1");
		if(mysqli_num_rows($result) == 0) {
			$system->error("Bloodline does not exist!");
			return false;
		}
		
		$bloodline_data = mysqli_fetch_assoc($result);
		
		$this->name = $bloodline_data['name'];
		$this->clan_id = $bloodline_data['clan_id'];
		$this->rank = $bloodline_data['rank'];
		
		$this->passive_boosts = $bloodline_data['passive_boosts'];
		$this->combat_boosts = $bloodline_data['combat_boosts'];
		$this->jutsu = $bloodline_data['jutsu'];
		if($this->jutsu) {
			$this->jutsu = json_decode($bloodline_data['jutsu'], true);
		}
		
		// Load user-related BL data if relevant
		if($user_id) {
			$user_id = (int)$user_id;
			$result = $system->query("SELECT * FROM `user_bloodlines` WHERE `user_id`=$user_id LIMIT 1");
			if(mysqli_num_rows($result) == 0) {
				$this->system->message("Invalid user bloodline data!");
				$this->system->printMessage();
				return false;
			}
			
			$user_bloodline = mysqli_fetch_assoc($result);
			$this->name = $user_bloodline['name'];
			
			if($user_bloodline['jutsu']) {
				$base_jutsu = $this->jutsu;
				$user_jutsu = json_decode($user_bloodline['jutsu'], true);
				$this->jutsu = array();
				
				if(is_array($user_jutsu)) {
					foreach($user_jutsu as $jutsu) {	
						$this->jutsu[$jutsu['jutsu_id']] = $base_jutsu[$jutsu['jutsu_id']];
						$this->jutsu[$jutsu['jutsu_id']]['jutsu_id'] = $jutsu['jutsu_id'];
						$this->jutsu[$jutsu['jutsu_id']]['level'] = $jutsu['level'];
						$this->jutsu[$jutsu['jutsu_id']]['exp'] = $jutsu['exp'];
						
						$this->jutsu[$jutsu['jutsu_id']]['power'] *= 1 + round($this->jutsu[$jutsu['jutsu_id']]['level'] * 0.005, 2);
						if($this->jutsu[$jutsu['jutsu_id']]['effect'] && $this->jutsu[$jutsu['jutsu_id']]['effect'] != 'none') {
							$this->jutsu[$jutsu['jutsu_id']]['effect_amount'] *= 1 + round($this->jutsu[$jutsu['jutsu_id']]['level'] * 0.002, 3);
						}
					}
				}
			}
			else {
				$this->jutsu = array();
			}
		}
		
		
		if($this->passive_boosts) {
			$this->passive_boosts = json_decode($this->passive_boosts, true);
			//var_dump($this->passive_boosts);
		}
		if($this->combat_boosts) {
			$this->combat_boosts = json_decode($this->combat_boosts, true);
		}
		
	}
}

/* Class:		Mission
*/
class Mission {
	public $mission_id;
	public $name;
	public $rank;
	public $mission_type;
	public $stages;
	public $money;
	
	private $system;
	
	public function __construct($mission_id, &$player = false, &$team = false) {
		global $system;
		$this->system = $system;
		$result = $this->system->query("SELECT * FROM `missions` WHERE `mission_id`='$mission_id' LIMIT 1");
		if($this->system->db_num_rows == 0) {
			return false;
		}
		
		$mission_data = $this->system->db_fetch($result);
		
		$this->player = $player;
		$this->team = $team;
		
		$this->mission_id = $mission_data['mission_id'];
		$this->name = $mission_data['name'];
		$this->rank = $mission_data['rank'];
		$this->mission_type = $mission_data['mission_type'];
		$this->money = $mission_data['money'];
		
		// Unset team if normal mission
		if($this->mission_type != 3) {
			unset($this->team);
			$this->team = false;
		}
		
		$stages = json_decode($mission_data['stages'], true);
		foreach($stages as $id => $stage) {
			$this->stages[($id + 1)] = $stage;
			$this->stages[($id + 1)]['stage_id'] = ($id + 1);
		}
				
		if($this->player && $this->player->mission_id) {
			$this->current_stage = $this->player->mission_stage;
		}
		else {
			if($this->team) {
				$this->nextTeamStage(1);
			}
			else {
				$this->nextStage(1);
			}
		}
	}
	
	public function nextStage($stage_id) {
		global $villages;
		
		// Check for multi-count, stop stage ID
		$new_stage = true;
		if($this->current_stage['count_needed']) {
			$this->current_stage['count']++;
			if($this->current_stage['count'] < $this->current_stage['count_needed']) {
				$stage_id--;
				$new_stage = false;
				$this->current_stage['description'] = $this->stages[$stage_id]['description'];
			}
		}		
		
		// Return signal for mission complete
		if($stage_id > count($this->stages) + 1) {
			return 2;
		}
		// Set to completion stage if all stages have been completed
		if($stage_id > count($this->stages)) {
			$this->current_stage = array(
				'stage_id' => $stage_id + 1,
				'action_type' => 'travel',
				'action_data' => $this->player->village_location,
				'description' => 'Report back to the village to complete the mission.'
			);
			$this->player->mission_stage = $this->current_stage;
			return 1;
		}
		
		// Load new stage data
		if($new_stage) {
			$this->current_stage = $this->stages[$stage_id];
			if($this->current_stage['count'] > 1) {
				$this->current_stage['count_needed'] = $this->current_stage['count'];
				$this->current_stage['count'] = 0;
			}
			else {
				$this->current_stage['count'] = 0;
			}
		}

		if($this->current_stage['action_type'] == 'travel' || $this->current_stage['action_type'] == 'search') {
			for($i = 0; $i < 3; $i++) {
				$location = $this->rollLocation($this->player->village_location);
				if(!isset($villages[$location]) || $location == $this->player->village_location) {
					break;
				}
			}
			
			$this->current_stage['action_data'] = $location;
			
		}
		
		$search_array = array('[action_data]', '[location_radius]');
		$replace_array = array($this->current_stage['action_data'], $this->current_stage['location_radius']);
		
		$this->current_stage['description'] = str_replace($search_array, $replace_array, $this->current_stage['description']);
		
		$this->player->mission_stage = $this->current_stage;
		return 1;
	}
	
	public function nextTeamStage($stage_id) {
		global $villages;
		
		// Return signal for mission complete
		if($stage_id > count($this->stages) + 1) {
			return 2;
		}
		
		// Check for old stage
		$old_stage = false;
		if($this->player->mission_stage['stage_id'] < $this->team['mission_stage']['stage_id']) {
			$old_stage = true;
		}
		
		// Check multi counts, block stage id
		$new_stage = true;
		if($this->team['mission_stage']['count_needed'] && !$old_stage) {
			$this->team['mission_stage']['count']++;
			if($this->team['mission_stage']['count'] < $this->team['mission_stage']['count_needed']) {
				$stage_id--;
				$new_stage = false;
				$mission_stage = json_encode($this->team['mission_stage']);
				$this->system->query("UPDATE `teams` SET `mission_stage`='$mission_stage' WHERE `team_id`={$this->team['id']} LIMIT 1");
			}
		}	
				
		// Set to completion stage if all stages have been completed
		if($stage_id > count($this->stages)) {
			$this->current_stage = array(
				'stage_id' => $stage_id + 1,
				'action_type' => 'travel',
				'action_data' => $this->player->village_location,
				'description' => 'Report back to the village to complete the mission.'
			);
			$this->player->mission_stage = $this->current_stage;
			return 1;
		}
		
		// Clear mission if it was cancelled
		if($new_stage && !$this->team['mission_id']) {
			echo 'cancelled';
			$this->player->mission_id = 0;
			return 1;
		}
		
		// Load new stage data	
		$this->current_stage = $this->stages[$stage_id];
		if($new_stage) {
			if($this->current_stage['count'] > 1) {
				$this->current_stage['count_needed'] = $this->current_stage['count'];
				$this->current_stage['count'] = 0;
			}
			else {
				$this->current_stage['count'] = 0;
				$this->current_stage['count_needed'] = 0;
			}
			
			$this->team['mission_stage']['stage_id'] = $stage_id;
			$this->team['mission_stage']['count'] = $this->current_stage['count'];
			$this->team['mission_stage']['count_needed'] = $this->current_stage['count_needed'];
			
			$mission_stage = json_encode($this->team['mission_stage']);
			
			$this->system->query("UPDATE `teams` SET `mission_stage`='$mission_stage' WHERE `team_id`='{$this->team['id']}' LIMIT 1");
		}

		if($this->current_stage['action_type'] == 'travel' || $this->current_stage['action_type'] == 'search') {
			for($i = 0; $i < 3; $i++) {
				$location = $this->rollLocation($this->player->village_location);
				if(!isset($villages[$location]) || $location == $this->player->village_location) {
					break;
				}
			}
			
			$this->current_stage['action_data'] = $location;		
		}
		
		$search_array = array('[action_data]', '[location_radius]');
		$replace_array = array($this->current_stage['action_data'], $this->current_stage['location_radius']);
		$this->current_stage['description'] = str_replace($search_array, $replace_array, $this->current_stage['description']);
		
		$this->player->mission_stage = $this->current_stage;
		return 1;
	}
	
	public function rollLocation($starting_location) {
		global $villages;
		global $MAP_SIZE_X;
		global $MAP_SIZE_Y;
		
		$starting_location = explode('.', $starting_location);
			
		$max = $this->current_stage['location_radius'] * 2;
		$x = mt_rand(0, $max) - $this->current_stage['location_radius'];
		$y = mt_rand(0, $max) - $this->current_stage['location_radius'];
		if($x == 0 && $y == 0) {
			$x++;
		}
		
		$x += $starting_location[0];
		$y += $starting_location[1];
		
		if($x < 1) {
			$x = 1;
		}
		if($y < 1) {
			$y = 1;
		}
		
		if($x > $MAP_SIZE_X) {
			$x = $MAP_SIZE_X;
		}
		if($y > $MAP_SIZE_Y) {
			$y = $MAP_SIZE_Y;
		}
		
		return $x . '.' . $y;
	}
}

function diminishing_returns($val, $scale) {
    if($val < 0)
        return -diminishing_returns(-$val, $scale);
    $mult = $val / $scale;
    $trinum = (sqrt(8.0 * $mult + 1.0) - 1.0) / 2.0;
    return $trinum * $scale;
}