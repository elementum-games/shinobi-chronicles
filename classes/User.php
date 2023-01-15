<?php /** @noinspection PhpRedundantOptionalArgumentInspection */

require_once __DIR__ . "/Jutsu.php";
require_once __DIR__ . "/Team.php";
require_once __DIR__ . "/DailyTask.php";
require_once __DIR__ . "/battle/Fighter.php";

/*	Class:		User
	Purpose:	Fetch user data and load into class variables.
*/

class User extends Fighter {
    const ENTITY_TYPE = 'U';

    const AVATAR_MAX_SIZE = 150;
    const AVATAR_MAX_SEAL_SIZE = 200;
    const AVATAR_MAX_FILE_SIZE = 1024 ** 2; // 1024 kb

    const GENDER_MALE = 'Male';
    const GENDER_FEMALE = 'Female';
    const GENDER_NON_BINARY = 'Non-binary';
    const GENDER_NONE = 'None';

    public static array $genders = [
            User::GENDER_MALE,
            User::GENDER_FEMALE,
            User::GENDER_NON_BINARY,
            User::GENDER_NONE
        ];

    const MIN_NAME_LENGTH = 2;
    const MIN_PASSWORD_LENGTH = 6;

    const BASE_EXP = 500;

    const MAX_CONSUMABLES = 10;

    const STAFF_NONE = 0;
    const STAFF_MODERATOR = 1;
    const STAFF_HEAD_MODERATOR = 2;
    const STAFF_CONTENT_ADMIN = 3;
    const STAFF_ADMINISTRATOR = 4;
    const STAFF_HEAD_ADMINISTRATOR = 5;

    const SUPPORT_NONE = 0;
    const SUPPORT_BASIC = 1;
    const SUPPORT_INTERMEDIATE = 2;
    const SUPPORT_CONTENT_ONLY = 3;
    const SUPPORT_SUPERVISOR = 4;
    const SUPPORT_ADMIN = 5;

    const UPDATE_NOTHING = 0;
    const UPDATE_REGEN = 1;
    const UPDATE_FULL = 2;

    public static int $jutsu_train_gain = 5;

    public System $system;

    public string $id;
    public int $user_id;
    public string $user_name;
    public $username_changes;
    public $blacklist;
    public $original_blacklist;

    /** @var DailyTask[] */
    public array $daily_tasks;
    public int $daily_tasks_reset;

    // Loaded in loadData
    public float $health;
    public float $max_health;
    public float $stamina;
    public float $max_stamina;
    public float $chakra;
    public float $max_chakra;

    public string $current_ip;
    public string $last_ip;
    public string $email;
    public $failed_logins;
    public $global_message_viewed;

    public string $gender;
    public int $spouse;
    public string $spouse_name;
    public int $marriage_time;
    public $village;
    public $level;
    public $rank;

    public $exp;
    public $staff_level;
    public $support_level;
    public int $bloodline_id;
    public $bloodline_name;
    public $clan;
    public $village_location;
    public $in_village;
    public $location; // x.y
    public $x;
    public $y;

    public $train_type;
    public $train_gain;
    public $train_time;

    private int $money;

    public $pvp_wins;
    public $pvp_losses;
    public $ai_wins;
    public $ai_losses;

    public $missions_completed;
    public $presents_claimed;

    public $monthly_pvp;

    /** @var Jutsu[] */
    public array $jutsu;

    public $ninjutsu_ids;
    public $genjutsu_ids;
    public $taijutsu_ids;

    public array $equipped_jutsu;
    public $equipped_items;

    public array $items;
    public array $equipped_weapons;

    public ?Bloodline $bloodline = null;
    public float $bloodline_skill;

    public $ban_type;
    public $ban_expire;
    public $journal_ban;
    public $avatar_ban;
    public $song_ban;

    public $layout;

    // Team
    public ?Team $team = null;
    public array $fake_team = [];
    public ?int $team_invite;

    // Internal class variables
    public $inventory_loaded;

    public $rank_name;

    public $last_update;
    public $last_active;
    public $forbidden_seal;
    public $chat_color;
    public $last_login;

    public $jutsu_scrolls;
    public string $avatar_link;
    public $profile_song;
    public $log_actions;

    public int $base_level;
    public int $max_level;
    public int $base_stats;
    public int $stats_per_level;
    public int $health_gain;
    public int $pool_gain;
    public int $stat_cap;
    public int $exp_per_level;
    public int $stats_max_level;
    public int $regen_rate;

    public array $elements;

    public int $regen_boost;

    public int $battle_id;

    /**
     * @var mixed
     */
    public $challenge;

    public int $mission_id;
    /**
     * @var mixed
     */
    public $mission_stage;

    public int $special_mission;

    public int $exam_stage;

    public int $last_ai;

    public int $last_free_stat_change;

    public int $last_pvp;
    public int $last_death;

    private int $premium_credits;
    public int $premium_credits_purchased;

    public int $total_stats;

    public int $scout_range;

    public int $stealth;
    public int $village_changes;
    public int $clan_changes;

    public int $clan_office;

    public array $equipped_armor;
    public array $bloodline_offense_boosts;
    public array $bloodline_defense_boosts;

    public array $stats = [
        'ninjutsu_skill',
        'taijutsu_skill',
        'genjutsu_skill',
        'cast_speed',
        'speed',
        'intelligence',
        'willpower'
    ];

    /**
     * User constructor.
     * @param $user_id
     * @throws Exception
     */
    public function __construct($user_id) {
        global $system;
        $this->system =& $system;

        if(!$user_id) {
            throw new Exception("Invalid user id!");
        }
        $this->user_id = $this->system->clean($user_id);
        $this->id = self::ENTITY_TYPE . ':' . $this->user_id;

        $result = $this->system->query("SELECT `user_id`, `user_name`, `ban_type`, `ban_expire`, `journal_ban`, `avatar_ban`, `song_ban`, `last_login`,
			`forbidden_seal`, `chat_color`, `staff_level`, `username_changes`, `support_level`, `special_mission`
			FROM `users` WHERE `user_id`='$this->user_id' LIMIT 1"
        );
        if($this->system->db_last_num_rows == 0) {
            throw new Exception("User does not exist!");
        }

        $result = $this->system->db_fetch($result);

        $this->user_name = $result['user_name'];
        $this->username_changes = $result['username_changes'];

        $this->staff_level = $result['staff_level'];
        $this->support_level = $result['support_level'];

        $this->ban_type = $result['ban_type'];
        $this->ban_expire = $result['ban_expire'];
        $this->journal_ban = $result['journal_ban'];
        $this->avatar_ban = $result['avatar_ban'];
        $this->song_ban = $result['song_ban'];

        $this->last_login = $result['last_login'];

        $this->forbidden_seal = $result['forbidden_seal'];
        $this->chat_color = $result['chat_color'];

        if($this->ban_type && $this->ban_expire <= time()) {
            $this->system->message("Your " . $this->ban_type . " ban has ended.");
            $this->ban_type = '';

            $this->system->query("UPDATE `users` SET `ban_type`='', `ban_expire`='0' WHERE `user_id`='$this->user_id' LIMIT 1");
        }

        $this->inventory_loaded = false;

        return true;
    }

    /**
     * @param System $system
     * @param int    $user_id
     * @return User
     * @throws Exception
     */
    public static function loadFromId(System $system, int $user_id): User {
        $user = new User($user_id);

        $result = $system->query("SELECT 
            `user_id`, 
            `user_name`, 
            `ban_type`, 
            `ban_expire`, 
            `journal_ban`, 
            `avatar_ban`, 
            `song_ban`, 
            `last_login`,
			`forbidden_seal`, 
			`chat_color`, 
			`staff_level`, 
			`username_changes`, 
			`support_level`, 
			`special_mission`
			FROM `users` WHERE `user_id`='$user_id' LIMIT 1"
        );
        if($system->db_last_num_rows == 0) {
            throw new Exception("User does not exist!");
        }

        $result = $system->db_fetch($result);

        $user->user_name = $result['user_name'];
        $user->username_changes = $result['username_changes'];

        $user->staff_level = $result['staff_level'];
        $user->support_level = $result['support_level'];

        $user->ban_type = $result['ban_type'];
        $user->ban_expire = $result['ban_expire'];
        $user->journal_ban = $result['journal_ban'];
        $user->avatar_ban = $result['avatar_ban'];
        $user->song_ban = $result['song_ban'];

        $user->last_login = $result['last_login'];

        $user->forbidden_seal = $result['forbidden_seal'];
        $user->chat_color = $result['chat_color'];

        if($user->ban_type && $user->ban_expire <= time()) {
            $system->message("Your " . $user->ban_type . " ban has ended.");
            $user->ban_type = '';

            $system->query("UPDATE `users` SET `ban_type`='', `ban_expire`='0' WHERE `user_id`='$user->user_id' LIMIT 1");
        }

        $user->inventory_loaded = false;

        return $user;
    }
    
    /* function loadData()
        Loads user data from the database into class members
        -Parameters-
        Update (1 = regen, 2 = training)
    */

    /**
     * @throws Exception
     */
    public function loadData($UPDATE = User::UPDATE_FULL, $remote_view = false): string {
        $result = $this->system->query("SELECT * FROM `users` WHERE `user_id`='$this->user_id' LIMIT 1");
        $user_data = $this->system->db_fetch($result);

        $this->current_ip = $user_data['current_ip'];
        $this->last_ip = $user_data['last_ip'];
        // IP stuff
        if(!$remote_view && $this->current_ip != $_SERVER['REMOTE_ADDR']) {
            $this->last_ip = $this->current_ip;
            $this->current_ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->email = $user_data['email'];

        $this->global_message_viewed = $user_data['global_message_viewed'];

        $this->last_update = $user_data['last_update'];
        $this->last_active = $user_data['last_active'];
        $this->failed_logins = $user_data['failed_logins'];
        $this->avatar_link = $user_data['avatar_link'];
        $this->profile_song = $user_data['profile_song'];

        $this->log_actions = $user_data['log_actions'];

        // Message blacklist
        $this->blacklist = [];
        $result = $this->system->query("SELECT `blocked_ids` FROM `blacklist` WHERE `user_id`='$this->user_id' LIMIT 1");
        if($this->system->db_last_num_rows != 0) {
            $blacklist = $this->system->db_fetch($result);
            $this->blacklist = json_decode($blacklist['blocked_ids'], true);
            $this->original_blacklist = $this->blacklist;
        }
        else {
            $blacklist_json = json_encode($this->blacklist);
            $this->system->query("INSERT INTO `blacklist` (`user_id`, `blocked_ids`) VALUES ('{$this->user_id}', '{$blacklist_json}')");
            $this->original_blacklist = []; // Default an empty array, user did not have an original.
        }

        // Rank stuff
        $this->rank = $user_data['rank'];
        $rank_data = $this->system->query("SELECT * FROM `ranks` WHERE `rank_id`='$this->rank'");
        if($this->system->db_last_num_rows == 0) {
            $this->system->message("Invalid rank!");
            $this->system->printMessage("Invalid rank!");
        }
        else {
            $rank_data = $this->system->db_fetch($rank_data);
            $this->rank_name = $rank_data['name'];
            $this->base_level = $rank_data['base_level'];
            $this->max_level = $rank_data['max_level'];
            $this->base_stats = $rank_data['base_stats'];
            $this->stats_per_level = $rank_data['stats_per_level'];
            $this->health_gain = $rank_data['health_gain'];
            $this->pool_gain = $rank_data['pool_gain'];
            $this->stat_cap = $rank_data['stat_cap'];

            $this->exp_per_level = $this->stats_per_level * 10;

            $this->stats_max_level = $this->base_stats + ($this->stats_per_level * ($this->max_level - $this->base_level));
        }

        $this->gender = $user_data['gender'];
        $this->village = $user_data['village'];
        $this->level = $user_data['level'];
        $this->health = $user_data['health'];
        $this->max_health = $user_data['max_health'];
        $this->stamina = $user_data['stamina'];
        $this->max_stamina = $user_data['max_stamina'];
        $this->chakra = $user_data['chakra'];
        $this->max_chakra = $user_data['max_chakra'];

        if($this->health > $this->max_health) {
            $this->health = $this->max_health;
        }
        if($this->chakra > $this->max_chakra) {
            $this->chakra = $this->max_chakra;
        }
        if($this->stamina > $this->max_stamina) {
            $this->stamina = $this->max_stamina;
        }

        $this->regen_rate = $user_data['regen_rate'];
        $this->regen_boost = 0;

        $this->battle_id = $user_data['battle_id'];
        $this->challenge = $user_data['challenge'];

        $this->mission_id = $user_data['mission_id'];
        if($this->mission_id) {
            $this->mission_stage = json_decode($user_data['mission_stage'], true);
        }

        $this->special_mission = $user_data['special_mission'];

        $this->exam_stage = $user_data['exam_stage'];

        $this->last_ai = $user_data['last_ai'];
        $this->last_free_stat_change = $user_data['last_free_stat_change'];
        $this->last_pvp = $user_data['last_pvp'];
        $this->last_death = $user_data['last_death'];

        $this->layout = $user_data['layout'];

        $this->exp = $user_data['exp'];
        $this->bloodline_id = $user_data['bloodline_id'];
        $this->bloodline_name = $user_data['bloodline_name'];

        if($this->bloodline_id) {
            array_unshift($this->stats, 'bloodline_skill');
        }

        $this->location = $user_data['location']; // x.y
        $location = explode(".", $this->location);
        $this->x = $location[0];
        $this->y = $location[1];

        $this->train_type = $user_data['train_type'];
        $this->train_gain = $user_data['train_gain'];
        $this->train_time = $user_data['train_time'];

        $this->money = $user_data['money'];
        $this->premium_credits = $user_data['premium_credits'];
        $this->premium_credits_purchased = $user_data['premium_credits_purchased'];

        $this->pvp_wins = $user_data['pvp_wins'];
        $this->pvp_losses = $user_data['pvp_losses'];
        $this->ai_wins = $user_data['ai_wins'];
        $this->ai_losses = $user_data['ai_losses'];
        $this->monthly_pvp = $user_data['monthly_pvp'];

        $this->missions_completed = json_decode($user_data['missions_completed'], true);
        if(!is_array($this->missions_completed)) {
            $this->missions_completed = [];
        }

        $this->presents_claimed = json_decode($user_data['presents_claimed'], true);
        if(!is_array($this->presents_claimed)) {
            $this->presents_claimed = [];
        }

        $this->ninjutsu_skill = $user_data['ninjutsu_skill'];
        $this->genjutsu_skill = $user_data['genjutsu_skill'];
        $this->taijutsu_skill = $user_data['taijutsu_skill'];

        $this->bloodline_skill = $user_data['bloodline_skill'];

        $this->cast_speed = $user_data['cast_speed'];
        $this->speed = $user_data['speed'];
        $this->intelligence = $user_data['intelligence'];
        $this->willpower = $user_data['willpower'];

        $this->total_stats = $this->ninjutsu_skill + $this->genjutsu_skill + $this->taijutsu_skill + $this->bloodline_skill +
            $this->cast_speed + $this->speed + $this->intelligence + $this->willpower;

        $this->ninjutsu_boost = 0;
        $this->genjutsu_boost = 0;
        $this->taijutsu_boost = 0;

        $this->cast_speed_boost = 0;
        $this->speed_boost = 0;
        $this->intelligence_boost = 0;
        $this->willpower_boost = 0;

        $this->defense_boost = 0;

        $this->ninjutsu_resist = 0;
        $this->taijutsu_resist = 0;
        $this->genjutsu_resist = 0;

        // Combat nerfs
        $this->ninjutsu_nerf = 0;
        $this->taijutsu_nerf = 0;
        $this->genjutsu_nerf = 0;

        $this->cast_speed_nerf = 0;
        $this->speed_nerf = 0;
        $this->intelligence_nerf = 0;
        $this->willpower_nerf = 0;

        $this->scout_range = 1;
        $this->stealth = 0;

        if($this->rank > 3) {
            $this->scout_range++;
        }
        if($this->isHeadAdmin()) {
            $this->scout_range += 2;
        }

        $this->village_changes = $user_data['village_changes'];
        $this->clan_changes = $user_data['clan_changes'];

        // Village
        $result = $this->system->query("SELECT `location` FROM `villages` WHERE `name`='{$this->village}' LIMIT 1");
        if($this->system->db_last_num_rows != 0) {
            $result = $this->system->db_fetch($result);
            $this->village_location = $result['location'];
            if($this->location === $this->village_location) {
                $this->in_village = true;
            }
        }
        else {
            $this->in_village = false;
        }

        // Daily Tasks
        // Daily Tasks
        $this->daily_tasks = [];
        $this->daily_tasks_reset = 0;
        $result = $this->system->query("SELECT `tasks`, `last_reset` FROM `daily_tasks` WHERE `user_id`='$this->user_id' LIMIT 1");
        if($this->system->db_last_num_rows !== 0) {
            $dt = $this->system->db_fetch($result);

            $dt_arr = json_decode($dt['tasks'], true);
            $this->daily_tasks = array_map(function($dt_data) {
                return new DailyTask($dt_data);
            }, $dt_arr);

            $this->daily_tasks_reset = $dt['last_reset'];
        }
        else {
            $this->system->query("INSERT INTO `daily_tasks` (`user_id`, `tasks`, `last_reset`)
			    VALUES ('{$this->user_id}', '" . json_encode([]) . "', '" . time() . "')"
            );
        }

        if(empty($this->daily_tasks) || (time() - $this->daily_tasks_reset) > (60 * 60 * 24)) {
            $this->daily_tasks = DailyTask::generateNewTasks($this);

            $this->system->query("UPDATE `daily_tasks` SET
                `tasks`='" . json_encode($this->daily_tasks) . "',
                `last_reset`='" . time() . "'
                WHERE `user_id`='{$this->user_id}'");
        }
        else if($UPDATE == User::UPDATE_FULL && !$remote_view) {
            // check if the user has completed stuff and reward them if so
            foreach($this->daily_tasks as $task) {
                if(!$task->complete && $task->progress >= $task->amount) {
                    $task->progress = $task->amount;
                    $task->complete = true;
                    $this->addMoney($task->reward, "Completed daily task");

                    $this->system->message('You have completed ' . $task->name . ' and earned ¥' . $task->reward);
                }
            }
        }

        // Clan
        $this->clan = $user_data['clan_id'];
        if($this->clan) {
            $result = $this->system->query("SELECT * FROM `clans` WHERE `clan_id`='$this->clan' LIMIT 1");
            if($this->system->db_last_num_rows == 0) {
                $this->clan = false;
            }
            else {
                $clan_data = $this->system->db_fetch($result);
                $this->clan = [
                    'id' => $clan_data['clan_id'],
                    'name' => $clan_data['name'],
                    'village' => $clan_data['village'],
                    'bloodline_only' => $clan_data['bloodline_only'],
                    'boost' => $clan_data['boost'],
                    'boost_amount' => $clan_data['boost_amount'],
                    'points' => $clan_data['points'],
                    'leader' => $clan_data['leader'],
                    'elder_1' => $clan_data['elder_1'],
                    'elder_2' => $clan_data['elder_2'],
                    'challenge_1' => $clan_data['challenge_1'],
                    'logo' => $clan_data['logo'],
                    'motto' => $clan_data['motto'],
                    'info' => $clan_data['info'],
                ];

                $this->clan_office = $user_data['clan_office'];
            }
        }

        // Team
        $this->team = null;
        $this->team_invite = null;
        $this->fake_team = [
            'name' => 'Something',
            'avatar' => 'Something Else',
        ];

        $team_id = $user_data['team_id'];
        if($team_id) {
            // Invite stuff
            if(substr($team_id, 0, 7) == 'invite:') {
                $this->team_invite = (int)explode(':', $team_id)[1];
            }
            // Player team stuff
            else {
                $this->team = Team::findById($this->system, $team_id);
                if($this->team != null) {
                    $this->defense_boost += $this->team->getDefenseBoost($this);
                }
            }
        }

        // Spouse
        $this->spouse = $user_data['spouse'];
        $this->marriage_time = $user_data['marriage_time'];
        $result = $this->system->query("SELECT `user_name` FROM `users` WHERE `user_id`='$this->spouse' LIMIT 1");
        if($this->system->db_last_num_rows) {
            $this->spouse_name = $this->system->db_fetch($result)['user_name'];
        }
        else {
            //TODO: Make a log if this becomes an issue
            $this->spouse_name = '???';
        }

        // Bloodline
        if($this->bloodline_id) {
            $this->bloodline = Bloodline::loadFromId(
                system: $this->system,
                bloodline_id: $this->bloodline_id,
                user_id: $this->user_id
            );

            // Debug info
            if($this->system->debug['bloodline']) {
                echo "Debugging {$this->getName()}<br />";
                foreach($this->bloodline->passive_boosts as $id => $boost) {
                    echo "Boost: " . $boost['effect'] . " : " . $boost['power'] . "<br />";
                }
                foreach($this->bloodline->combat_boosts as $id => $boost) {
                    echo "Boost: " . $boost['effect'] . " : " . $boost['power'] . "<br />";
                }
                echo "<br />";
            }

            $this->bloodline->setBoostAmounts(
                $this->rank,
                $this->ninjutsu_skill, $this->taijutsu_skill, $this->genjutsu_skill, $this->bloodline_skill,
                $this->base_stats, $this->total_stats, $this->stats_max_level,
                $this->regen_rate
            );

            // Debug info
            if($this->system->debug['bloodline']) {
                echo "Debugging {$this->getName()}<br />";
                foreach($this->bloodline->passive_boosts as $id => $boost) {
                    echo "Boost: " . $boost['effect'] . " : " . $boost['power'] . "<br />";
                }
                foreach($this->bloodline->combat_boosts as $id => $boost) {
                    echo "Boost: " . $boost['effect'] . " : " . $boost['power'] . "<br />";
                }
                echo "<br />";
            }

            // Apply out-of-combat effects
            if(!empty($this->bloodline->passive_boosts)) {
                foreach($this->bloodline->passive_boosts as $id => $effect) {
                    switch($effect['effect']) {
                        case 'scout_range':
                            $this->scout_range += $effect['effect_amount'];
                            break;
                        case 'stealth':
                            $this->stealth += $effect['effect_amount'];
                            break;
                        case 'regen':
                            $this->regen_boost += $effect['effect_amount'];
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        // Chat color
        if($this->chat_color == '') {
            $this->chat_color = 'black';
        }

        // Forbidden seal
        if($this->forbidden_seal) {
            $this->forbidden_seal = json_decode($user_data['forbidden_seal'], true);

            if($this->forbidden_seal['time'] < time() && $UPDATE >= User::UPDATE_FULL && !(!$this->forbidden_seal['level'] && $this->forbidden_seal['color'])) {
                $this->system->message("Your Forbidden Seal has receded.");
                $this->forbidden_seal = false;
            }

            // Patch infinite premium from user name color
            if(isset($this->forbidden_seal['color']) && $UPDATE >= User::UPDATE_FULL) {
                if(!isset($this->forbidden_seal['level'])) {
                    $this->chat_color = $this->forbidden_seal['color'];
                    $this->forbidden_seal = false;
                } else {
                    $this->chat_color = $this->forbidden_seal['color'];
                    unset($this->forbidden_seal['color']);
                }
            }

            // Regen boost
            else {
                if($this->forbidden_seal['level'] == 1) {
                    $this->regen_boost += $this->regen_rate * 0.1;
                }
                else if($this->forbidden_seal['level'] == 2) {
                    $this->regen_boost += $this->regen_rate * 0.2;
                }

            }
        }

        //In Village Regen
        if($this->in_village) {
            $this->regen_boost += 20 + $this->regen_rate;
        }

        // Elements
        $elements = $user_data['elements'];
        if($elements) {
            $this->elements = json_decode(
                $user_data['elements'] ?? "[]",
                true
            );
        }
        else {
            $this->elements = [];
        }

        // Special Mission - Kick user out if battle is started
        if ($this->battle_id && $this->special_mission) {
            $cancel_mission = SpecialMission::cancelMission($this->system, $this, $this->special_mission);
        }

        // Regen/time-based events
        $time_difference = time() - $this->last_update;
        if($time_difference > 60 && $UPDATE >= User::UPDATE_REGEN) {
            $minutes = floor($time_difference / 60);

            $regen_amount = $minutes * ($this->regen_rate + $this->regen_boost);

            // In-battle decrease
            if($this->battle_id) {
                $regen_amount -= round($regen_amount * 0.7, 1);
            }

            $this->health += $regen_amount * 2;
            $this->chakra += $regen_amount;
            $this->stamina += $regen_amount;

            if($this->health > $this->max_health) {
                $this->health = $this->max_health;
            }
            if($this->chakra > $this->max_chakra) {
                $this->chakra = $this->max_chakra;
            }
            if($this->stamina > $this->max_stamina) {
                $this->stamina = $this->max_stamina;
            }

            $this->last_update += $minutes * 60;
        }

        // Check training
        $display = '';
        if($this->train_time && $UPDATE >= User::UPDATE_FULL) {
            if($this->train_time < time()) {
                $team_boost_description = "";

                // Jutsu training
                if(str_contains($this->train_type, 'jutsu:')) {
                    $jutsu_id = $this->train_gain;
                    $this->getInventory();

                    $gain = User::$jutsu_train_gain;
                    if($this->system->TRAIN_BOOST) {
                        $gain += $this->system->TRAIN_BOOST;
                    }
                    if($this->jutsu[$jutsu_id]->level + $gain > 100) {
                        $gain = 100 - $this->jutsu[$jutsu_id]->level;
                    }

                    if($this->checkInventory($jutsu_id, 'jutsu')) {
                        if($this->jutsu[$jutsu_id]->level < 100) {
                            $new_level = $this->jutsu[$jutsu_id]->level + $gain;

                            if($new_level > 100) {
                                $this->jutsu[$jutsu_id]->level = 100;
                            }
                            else {
                                $this->jutsu[$jutsu_id]->level += $gain;
                            }
                            $message = $this->jutsu[$jutsu_id]->name . " has increased to level " .
                                $this->jutsu[$jutsu_id]->level . '.';

                            $jutsu_skill_type = $this->jutsu[$jutsu_id]->jutsu_type . '_skill';
                            if($this->total_stats < $this->stat_cap) {
                                $this->{$jutsu_skill_type}++;
                                $this->exp += 10;
                                $message .= ' You have gained 1 ' . ucwords(str_replace('_', ' ', $jutsu_skill_type)) .
                                    ' and 10 experience.';
                            }

                            $this->system->message($message);

                            if(!$this->ban_type) {
                                $this->updateInventory();
                            }
                        }
                    }

                    $this->train_time = 0;
                }
                // Skill/attribute training
                else {
                    // TEAM BOOST TRAINING GAINS
                    if($this->team != null) {
                        $boost_percent = $this->team->checkForTrainingBoostTrigger();
                        if($boost_percent != null) {
                            $boost_amount = round($this->train_gain * $boost_percent, 0, PHP_ROUND_HALF_DOWN);
                            $this->train_gain += $boost_amount;

                            $team_boost_description = '<br />LUCKY! Your team bond triggered a breakthrough and resulted in increased progress!
							<br />
							You gained an additional <b>[ ' . $boost_amount . ' ]</b> point(s)';
                        }
                    }

                    // Check caps
                    $gain = $this->train_gain;

                    $total_stats = $this->total_stats + $gain;

                    if($total_stats > $this->stat_cap) {
                        $gain -= $total_stats - $this->stat_cap;
                        if($gain < 0) {
                            $gain = 0;
                        }
                    }

                    $this->{$this->train_type} += $gain;
                    $this->exp += $gain * 10;

                    $this->train_time = 0;
                    $this->system->message("You have gained " . $gain . " " . ucwords(str_replace('_', ' ', $this->train_type)) .
                        " and " . ($gain * 10) . " experience." . $team_boost_description
                    );
                }
            }
            else {
                //*setTimeout is used to notify training finished*//
                if(strpos($this->train_type, 'jutsu:') !== false) {
                    $train_type = str_replace('jutsu:', '', $this->train_type);
                    $display .= "<p class='trainingNotification'>Training: " . ucwords(str_replace('_', ' ', $train_type)) . "<br />" .
                        "<span id='trainingTimer'>" . System::timeRemaining($this->train_time - time(), 'short', false, true) . " remaining</span></p>";
                    $display .= "<script type='text/javascript'>
					let train_time = " . ($this->train_time - time()) . ";
          setTimeout(()=>{titleBarFlash();}, train_time * 1000);
					</script>";
                }
                else {
                    //*setTimeout is used to notify training finished*//
                    $display .= "<p class='trainingNotification'>Training: " . ucwords(str_replace('_', ' ', $this->train_type)) . "<br />" .
                        "<span id='trainingTimer'>" . System::timeRemaining($this->train_time - time(), 'short', false, true) . " remaining</span></p>";
                    $display .= "<script type='text/javascript'>
					let train_time = " . ($this->train_time - time()) . ";
          setTimeout(()=>{titleBarFlash();}, train_time * 1000);
					</script>";
                }
            }
        }

        // Correction location
        $villages = $this->system->getVillageLocations();
        if(isset($villages[$this->location]) &&
            $this->location !== $this->village_location &&
            !$this->isHeadAdmin()
        ) {
            $this->x--;
        }

        return $display;
    }

    public function getInventory() {
        // Query user owned inventory
        $result = $this->system->query("SELECT * FROM `user_inventory` WHERE `user_id` = '{$this->user_id}'");

        $player_jutsu = [];
        $player_items = [];
        $equipped_jutsu = [];
        $equipped_items = [];

        // Decode JSON of inventory into variables
        if($this->system->db_last_num_rows > 0) {
            $user_inventory = $this->system->db_fetch($result);
            $player_jutsu = json_decode($user_inventory['jutsu'], true);
            $player_items = json_decode($user_inventory['items']);
            $equipped_jutsu = json_decode($user_inventory['equipped_jutsu']);
            $equipped_items = json_decode($user_inventory['equipped_items']);
        }
        else {
            $this->system->query("INSERT INTO `user_inventory` (`user_id`, `items`, `bloodline_jutsu`, `jutsu`)
                VALUES ('{$this->user_id}', '', '', '')"
            );
        }

        // Assemble query strings and fetch data of jutsu/items user owns from jutsu/item tables
        $player_jutsu_string = '';

        if($player_jutsu) {
            $player_jutsu_array = $player_jutsu;
            $player_jutsu = [];
            foreach($player_jutsu_array as $jutsu_data) {
                if(!is_numeric($jutsu_data['jutsu_id'])) {
                    continue;
                }
                $player_jutsu[$jutsu_data['jutsu_id']] = $jutsu_data;
                $player_jutsu_string .= $jutsu_data['jutsu_id'] . ',';
            }
            $player_jutsu_string = substr($player_jutsu_string, 0, strlen($player_jutsu_string) - 1);

            $this->jutsu = [];

            $result = $this->system->query(
                "SELECT * FROM `jutsu` WHERE `jutsu_id` IN ({$player_jutsu_string})
				AND `purchase_type` != '1' AND `rank` <= '{$this->rank}'"
            );
            if($this->system->db_last_num_rows > 0) {
                while($jutsu_data = $this->system->db_fetch($result)) {
                    $jutsu_id = $jutsu_data['jutsu_id'];
                    $jutsu = Jutsu::fromArray($jutsu_id, $jutsu_data);

                    if($player_jutsu[$jutsu_id]['level'] == 0) {
                        $this->jutsu_scrolls[$jutsu_id] = $jutsu;
                        continue;
                    }

                    $this->jutsu[$jutsu_id] = $jutsu;
                    $this->jutsu[$jutsu_id]->setLevel($player_jutsu[$jutsu_id]['level'], $player_jutsu[$jutsu_id]['exp']);

                    switch($jutsu_data['jutsu_type']) {
                        case 'ninjutsu':
                            $this->ninjutsu_ids[$jutsu_id] = $jutsu_id;
                            break;
                        case 'genjutsu':
                            $this->genjutsu_ids[$jutsu_id] = $jutsu_id;
                            break;
                        case 'taijutsu':
                            $this->taijutsu_ids[$jutsu_id] = $jutsu_id;
                            break;
                    }
                }
            }
        }
        else {
            $this->jutsu = [];
        }

        $this->equipped_jutsu = [];
        if(!empty($equipped_jutsu)) {
            $count = 0;
            foreach($equipped_jutsu as $jutsu_data) {
                if($this->checkInventory($jutsu_data->id, 'jutsu')) {
                    $this->equipped_jutsu[$count]['id'] = $jutsu_data->id;
                    $this->equipped_jutsu[$count]['type'] = $jutsu_data->type;
                    $count++;
                }
            }
        }
        else {
            $this->equipped_jutsu = [];
        }

        if($player_items) {
            $player_items_array = $player_items;
            $player_items = [];
            $player_items_string = '';

            foreach($player_items_array as $item) {
                if(!is_numeric($item->item_id)) {
                    continue;
                }
                $player_items[$item->item_id] = $item;
                $player_items_string .= $item->item_id . ',';
            }
            $player_items_string = substr($player_items_string, 0, strlen($player_items_string) - 1);

            $this->items = [];

            $result = $this->system->query("SELECT * FROM `items` WHERE `item_id` IN ({$player_items_string})");
            if($this->system->db_last_num_rows > 0) {
                while($item = $this->system->db_fetch($result)) {
                    $this->items[$item['item_id']] = $item;
                    $this->items[$item['item_id']]['quantity'] = $player_items[$item['item_id']]->quantity;
                }

            }
            else {
                $this->items = [];
            }
        }
        else {
            $this->items = [];
        }

        $this->equipped_items = [];
        $this->equipped_weapons = [];
        $this->equipped_armor = [];
        if($equipped_items) {
            foreach($equipped_items as $item_id) {
                if($this->checkInventory($item_id, 'item')) {
                    $this->equipped_items[] = $item_id;
                    if($this->items[$item_id]['use_type'] == 1) {
                        $this->equipped_weapons[] = $item_id;
                    }
                    else if($this->items[$item_id]['use_type'] == 2) {
                        $this->equipped_armor[] = $item_id;
                    }
                }
            }
        }

        $this->inventory_loaded = true;
    }

    /* function checkInventory()
    *	Checks user inventory, returns true if the item/jutsu is owned, false if it isn't.
        -Parameters-
        @item_id: Id of the item/jutsu to be checked for
        @inventory_type (jutsu, item): Type of thing to check for, either item or jutsu
    */
    public function checkInventory($item_id, $inventory_type = 'jutsu'): bool {
        if(!$item_id) {
            return false;
        }

        if($inventory_type == 'jutsu') {
            if(isset($this->jutsu[$item_id])) {
                return true;
            }
        }
        else if($inventory_type == 'item') {
            if(isset($this->items[$item_id])) {
                return true;
            }
        }

        return false;
    }

    public function hasJutsu(int $jutsu_id): bool {
        return isset($this->jutsu[$jutsu_id]);
    }

    public function hasItem(int $item_id): bool {
        return isset($this->items[$item_id]);
    }

    /* function useJutsu
        pool check, calc exp, etc */
    public function useJutsu(Jutsu $jutsu): bool {
        switch($jutsu->jutsu_type) {
            case 'ninjutsu':
            case 'genjutsu':
                $energy_type = 'chakra';
                break;
            case 'taijutsu':
                $energy_type = 'stamina';
                break;
            default:
                return false;
        }

        if($this->{$energy_type} < $jutsu->use_cost) {
            $this->system->message("You do not have enough $energy_type!");
            return false;
        }

        switch($jutsu->purchase_type) {
            case Jutsu::PURCHASE_TYPE_PURCHASEABLE:
                // Element check
                if($jutsu->element && $jutsu->element != Jutsu::ELEMENT_NONE) {
                    if($this->elements) {
                        if(array_search($jutsu->element, $this->elements) === false) {
                            $this->system->message("You do not possess the elemental chakra for this jutsu!");
                            return false;
                        }
                    }
                    else {
                        $this->system->message("You do not possess the elemental chakra for this jutsu!");
                        return false;
                    }
                }

                if($this->jutsu[$jutsu->id]->level < 100) {
                    $this->jutsu[$jutsu->id]->exp += round(1000 / ($this->jutsu[$jutsu->id]->level * 0.9));

                    if($this->jutsu[$jutsu->id]->exp >= 1000) {
                        $this->jutsu[$jutsu->id]->exp = 0;
                        $this->jutsu[$jutsu->id]->level++;
                        $this->system->message($jutsu->name . " has increased to level " . $this->jutsu[$jutsu->id]->level . ".");
                    }
                }

                $this->{$energy_type} -= $jutsu->use_cost;
                break;
            case Jutsu::PURCHASE_TYPE_BLOODLINE:
                if($this->bloodline->jutsu[$jutsu->id]->level < 100) {
                    $this->bloodline->jutsu[$jutsu->id]->exp += round(500 / ($this->bloodline->jutsu[$jutsu->id]->level * 0.9));

                    if($this->bloodline->jutsu[$jutsu->id]->exp >= 1000) {
                        $this->bloodline->jutsu[$jutsu->id]->exp = 0;
                        $this->bloodline->jutsu[$jutsu->id]->level++;
                        $this->system->message($jutsu->name . " has increased to level " . $this->bloodline->jutsu[$jutsu->id]->level . ".");
                    }
                }

                $this->{$energy_type} -= $jutsu->use_cost;
                break;
            case Jutsu::PURCHASE_TYPE_DEFAULT:
                $this->{$energy_type} -= $jutsu->use_cost;
                break;

            default:
                $this->system->message("Invalid jutsu type!");
                return false;
        }

        return true;
    }

    public function getMoney(): int {
        return $this->money;
    }

    /**
     * @throws Exception
     */
    private function setMoney(int $new_amount, string $description) {
        $this->system->currencyLog(
            $this->user_id,
            System::CURRENCY_TYPE_MONEY,
            $this->money,
            $new_amount,
            $new_amount - $this->money,
            $description
        );
        $this->money = $new_amount;
    }

    /**
     * @throws Exception
     */
    public function addMoney(int $amount, string $description) {
        $this->setMoney($this->money + $amount, $description);
    }

    /**
     * @throws Exception
     */
    public function subtractMoney(int $amount, string $description) {
        if($this->money < $amount) {
            throw new Exception("Not enough money!");
        }
        $this->setMoney($this->money - $amount, $description);
    }

    public function getPremiumCredits(): int {
        return $this->premium_credits;
    }

    /**
     * @throws Exception
     */
    private function setPremiumCredits(int $new_amount, string $description) {
        $this->system->currencyLog(
            $this->user_id,
            System::CURRENCY_TYPE_PREMIUM_CREDITS,
            $this->premium_credits,
            $new_amount,
            $new_amount - $this->premium_credits,
            $description
        );
        $this->premium_credits = $new_amount;
    }

    /**
     * @throws Exception
     */
    public function addPremiumCredits(int $amount, string $description) {
        $this->setPremiumCredits($this->premium_credits + $amount, $description);
    }


    /**
     * @throws Exception
     */
    public function subtractPremiumCredits(int $amount, string $description) {
        if($this->money < $amount) {
            throw new Exception("Not enough Ancient Kunai!");
        }
        $this->setPremiumCredits($this->premium_credits - $amount, $description);
    }


    /* function moteToVillage()
        moves user to village */
    public function moveToVillage() {
        $this->location = $this->village_location;
        $location = explode('.', $this->location);
        $this->x = $location[0];
        $this->y = $location[1];
    }

    /* function updateData()
        Updates user data from class members into database
        -Parameters-
    */
    public function updateData() {
        $this->location = $this->x . '.' . $this->y;

        $query = "UPDATE `users` SET
		`current_ip` = '$this->current_ip',
		`last_ip` = '$this->last_ip',
		`failed_logins` = '$this->failed_logins',
		`last_login` = '$this->last_login',
		`last_update` = '$this->last_update',
		`last_active` = '" . time() . "',
		`avatar_link` = '$this->avatar_link',
		`profile_song` = '$this->profile_song',
		`global_message_viewed` = '$this->global_message_viewed',
		`gender` = '$this->gender',
		`spouse`  = '$this->spouse',
		`marriage_time` = '$this->marriage_time',
		`village` = '$this->village',
		`level` = '$this->level',
		`rank` = '$this->rank',
		`health` = '$this->health',
		`max_health` = '$this->max_health',
		`stamina` = '$this->stamina',
		`max_stamina` = '$this->max_stamina',
		`chakra` = '$this->chakra',
		`max_chakra` = '$this->max_chakra',
		`regen_rate` = '$this->regen_rate',
		`stealth` = '$this->stealth',
		`exp` = '$this->exp',
		`bloodline_id` = '$this->bloodline_id',
		`bloodline_name` = '$this->bloodline_name',";
        if($this->clan) {
            $query .= "`clan_id` = '{$this->clan['id']}',
			`clan_office`='{$this->clan_office}',";
        }

        if($this->team) {
            $query .= "`team_id` = '{$this->team->id}',";
        }
        else if($this->team_invite) {
            $query .= "`team_id` = 'invite:{$this->team_invite}',";
        }
        else {
            $query .= "`team_id`=0,";
        }

        $query .= "`battle_id` = '$this->battle_id',
		`challenge` = '$this->challenge',
		`location` = '$this->location',";
        if($this->mission_id) {
            if(is_array($this->mission_stage)) {
                $mission_stage = json_encode($this->mission_stage);
            }
            else {
                $mission_stage = $this->mission_stage;
            }
            $query .= "`mission_id`='$this->mission_id',
			`mission_stage`='$mission_stage',";
        }
        else {
            $query .= "`mission_id`=0,";
        }

        if ($this->special_mission) {
            $query .= "`special_mission`='$this->special_mission',";
        } else {
            $query .= "`special_mission`='0',";
        }

        $query .= "`exam_stage` = '{$this->exam_stage}',
		`last_ai` = '$this->last_ai',
		`last_free_stat_change` = '{$this->last_free_stat_change}',
		`last_pvp` = '$this->last_pvp',
		`last_death` = '$this->last_death',";

        $forbidden_seal = $this->forbidden_seal;
        if(is_array($forbidden_seal)) {
            $forbidden_seal = json_encode($forbidden_seal);
        }

        $elements = $this->elements;
        if(is_array($elements)) {
            $elements = json_encode($this->elements);
        }

        $missions_completed = $this->missions_completed;
        if(is_array($missions_completed)) {
            $missions_completed = json_encode($missions_completed);
        }

        $presents_claimed = $this->presents_claimed;
        if(is_array($presents_claimed)) {
            $presents_claimed = json_encode($this->presents_claimed);
        }

        $query .= "`forbidden_seal`='$forbidden_seal',
        `chat_color` = '$this->chat_color',
		`train_type` = '$this->train_type',
		`train_gain` = '$this->train_gain',
		`train_time` = '$this->train_time',
		`money` = '$this->money',
		`premium_credits` = '$this->premium_credits',
		`pvp_wins` = '$this->pvp_wins',
		`pvp_losses` = '$this->pvp_losses',
		`ai_wins` = '$this->ai_wins',
		`ai_losses` = '$this->ai_losses',
		`missions_completed` = '$missions_completed',
		`presents_claimed` = '$presents_claimed',
		`monthly_pvp` = '$this->monthly_pvp',
		`elements` = '$elements',
		`ninjutsu_skill` = '$this->ninjutsu_skill',
		`genjutsu_skill` = '$this->genjutsu_skill',
		`taijutsu_skill` = '$this->taijutsu_skill',
		`bloodline_skill` = '$this->bloodline_skill',
		`cast_speed` = '$this->cast_speed',
		`speed` = '$this->speed',
		`intelligence` = '$this->intelligence',
		`willpower` = '$this->willpower',
		`village_changes` = '$this->village_changes',
		`clan_changes` = '$this->clan_changes'
		WHERE `user_id` = '{$this->user_id}' LIMIT 1";
        $this->system->query($query);

        // Update Blacklist
        if(count($this->blacklist) != count($this->original_blacklist)) {
            $blacklist_json = json_encode($this->blacklist);
            $this->system->query("UPDATE `blacklist` SET `blocked_ids`='{$blacklist_json}' WHERE `user_id`='{$this->user_id}' LIMIT 1");
        }

        //Update Daily Tasks
        if($this->daily_tasks) {
            $dt = json_encode($this->daily_tasks);
            $this->system->query("UPDATE `daily_tasks` SET `tasks`='{$dt}' WHERE `user_id`='{$this->user_id}'");
        }
    }

    /* function updateInventory()
        Updates user inventory from class members into database
        -Parameters-
    */
    public function updateInventory(): bool {
        if(!$this->inventory_loaded) {
            $this->system->error("Called update without fetching inventory!");
            return false;
        }

        $player_jutsu = [];
        $player_items = [];

        $jutsu_count = 0;
        $item_count = 0;

        if(!empty($this->jutsu)) {
            foreach($this->jutsu as $jutsu) {
                $player_jutsu[$jutsu_count] = [
                    'jutsu_id' => $jutsu->id,
                    'level' => $jutsu->level,
                    'exp' => $jutsu->exp,
                ];
                $jutsu_count++;
            }
        }

        if($this->jutsu_scrolls && !empty($this->jutsu_scrolls)) {
            foreach($this->jutsu_scrolls as $jutsu_scroll) {
                $player_jutsu[$jutsu_count] = [
                    'jutsu_id' => $jutsu_scroll->id,
                    'level' => $jutsu_scroll->level,
                    'exp' => $jutsu_scroll->exp,
                ];
                $jutsu_count++;
            }
        }

        if($this->items && !empty($this->items)) {
            foreach($this->items as $item) {
                $player_items[$item_count] = [
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ];
                $item_count++;
            }
        }

        $player_jutsu_json = json_encode($player_jutsu);
        $player_items_json = json_encode($player_items);
        $player_equipped_jutsu_json = json_encode($this->equipped_jutsu);
        $player_equipped_items_json = json_encode($this->equipped_items);

        $this->system->query("UPDATE `user_inventory` SET
			`jutsu` = '{$player_jutsu_json}',
			`items` = '{$player_items_json}',
			`equipped_jutsu` = '{$player_equipped_jutsu_json}',
			`equipped_items` = '{$player_equipped_items_json}'
			WHERE `user_id` = '{$this->user_id}' LIMIT 1"
        );

        $bloodline_jutsu = [];
        if($this->bloodline_id && !empty($this->bloodline->jutsu)) {
            $jutsu_count = 0;
            foreach($this->bloodline->jutsu as $jutsu) {
                if($jutsu->rank > $this->rank) {
                    continue;
                }
                $bloodline_jutsu[$jutsu_count]['jutsu_id'] = $jutsu->id;
                $bloodline_jutsu[$jutsu_count]['level'] = $jutsu->level;
                $bloodline_jutsu[$jutsu_count]['exp'] = $jutsu->exp;
                $jutsu_count++;
            }

            $bloodline_jutsu_json = json_encode($bloodline_jutsu);

            $this->system->query("UPDATE `user_bloodlines` SET `jutsu` = '{$bloodline_jutsu_json}'
				WHERE `user_id` = '{$this->user_id}' LIMIT 1"
            );
        }

        return true;
    }

    public function getName(): string {
        return $this->user_name;
    }

    public function getAvatarSize(): int {
        return $this->forbidden_seal ? self::AVATAR_MAX_SEAL_SIZE : self::AVATAR_MAX_SIZE;
    }

    public function canChangeChatColor(): bool {
        // Premium purchased
        if($this->premium_credits_purchased) {
            return true;
        }

        // Forbidden seal
        if($this->forbidden_seal && $this->forbidden_seal['time'] > time()) {
            return true;
        }

        // Staff level
        if($this->isModerator() || $this->isHeadModerator() || $this->isContentAdmin() || $this->isUserAdmin() || $this->isHeadAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getNameColors(): array
    {
        $return = [
            'black' => 'normalUser'
        ];

        if($this->forbidden_seal || $this->isHeadAdmin()) {
            $return = array_merge($return, [
                'blue' => 'blue',
                'pink' => 'pink',
            ]);
        }

        if($this->premium_credits_purchased > 0 || $this->isHeadAdmin()) {
            $return = array_merge($return, [
                'gold' => 'gold'
            ]);
        }

        if($this->isModerator()) {
            $return['green'] = 'moderator';
        }

        if($this->isHeadModerator()) {
            $return['teal'] = 'headModerator';
        }

        if($this->isContentAdmin()) {
            $return['purple'] = 'contentAdmin';
        }

        if($this->isUserAdmin()) {
            $return['red'] = 'administrator';
        }

        return $return;
    }

    public function getAvatarFileSize($format='MB'): string {
        $max_size = self::AVATAR_MAX_FILE_SIZE;
        switch($format) {
            default:
                $divisor = 1024 * 1024;
                $suffix = "MB";
                break;
            case 'kb':
                $divisor = 1024;
                $suffix = "KB";
            break;
        }
        return floor($max_size / $divisor) . $suffix;
    }

    public function expForNextLevel() {
        return $this->exp_per_level * (($this->level + 1) - $this->base_level) + ($this->base_stats * 10);
    }

    public function hasEquippedJutsu(int $jutsu_id): bool {
        if(!isset($this->jutsu[$jutsu_id])) {
            return false;
        }

        foreach($this->equipped_jutsu as $jutsu) {
            if($jutsu['id'] == $jutsu_id) {
                return true;
            }
        }
        return false;
    }

    public function removeJutsu(int $jutsu_id) {
        $jutsu = $this->jutsu[$jutsu_id];
        unset($this->jutsu[$jutsu_id]);

        switch($jutsu->jutsu_type) {
            case Jutsu::TYPE_NINJUTSU:
                unset($this->ninjutsu_ids[$jutsu_id]);
                break;
            case Jutsu::TYPE_TAIJUTSU:
                unset($this->taijutsu_ids[$jutsu_id]);
                break;
            case Jutsu::TYPE_GENJUTSU:
                unset($this->genjutsu_ids[$jutsu_id]);
                break;
        }
    }

    public function clearMission() {
        $this->mission_id = 0;
        $this->mission_stage = [];
    }

    public function isSupportStaff(): bool {
        switch($this->support_level) {
            case User::SUPPORT_BASIC:
            case User::SUPPORT_INTERMEDIATE:
            case User::SUPPORT_CONTENT_ONLY:
            case User::SUPPORT_SUPERVISOR:
            case User::SUPPORT_ADMIN:
                return true;
            default:
                return false;
        }
    }

    public function isSupportSupervisor(): bool {
        switch($this->support_level) {
            case User::SUPPORT_SUPERVISOR:
            case User::SUPPORT_ADMIN:
                return true;
            default:
                return false;
        }
    }

    public function isSupportAdmin(): bool {
        switch($this->support_level) {
            case User::SUPPORT_ADMIN:
                return true;
            default:
                return false;
        }
    }

    public function isModerator(): bool {
        switch($this->staff_level) {
            case User::STAFF_MODERATOR:
            case User::STAFF_HEAD_MODERATOR:
            case User::STAFF_ADMINISTRATOR:
            case User::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                return false;
        }
    }

    public function isHeadModerator(): bool {
        switch($this->staff_level) {
            case User::STAFF_HEAD_MODERATOR:
            case User::STAFF_ADMINISTRATOR:
            case User::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                return false;
        }
    }

    public function isContentAdmin(): bool {
        switch($this->staff_level) {
            case User::STAFF_CONTENT_ADMIN:
            case User::STAFF_ADMINISTRATOR:
            case User::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                return false;
        }
    }

    public function isUserAdmin(): bool {
        switch($this->staff_level) {
            case User::STAFF_ADMINISTRATOR:
            case User::STAFF_HEAD_ADMINISTRATOR:
                return true;
            default:
                return false;
        }
    }

    public function isHeadAdmin(): bool {
        return $this->staff_level == User::STAFF_HEAD_ADMINISTRATOR;
    }

    public function hasAdminPanel(): bool {
        return $this->isContentAdmin() || $this->isUserAdmin() || $this->isHeadAdmin();
    }

    const LOG_TRAINING = 'training';
    const LOG_ARENA = 'arena';
    const LOG_LOGIN = 'login';

    public function log(string $log_type, string $log_contents): bool {
        $valid_log_types = [self::LOG_TRAINING, self::LOG_ARENA, self::LOG_LOGIN];
        if(!in_array($log_type, $valid_log_types)) {
            error_log("Invalid player log type {$log_type}");
            return false;
        }

        $dateTime = System::dateTimeFromMicrotime(microtime(true));

        $dateTimeFormat = System::DB_DATETIME_MS_FORMAT;
        $this->system->query("INSERT INTO `player_logs`
            (`user_id`, `user_name`, `log_type`, `log_time`,
             `log_contents`)
            VALUES
            ({$this->user_id}, '{$this->user_name}', '{$log_type}', '{$dateTime->format($dateTimeFormat)}',
             '{$this->system->clean($log_contents)}'
            )
        "
        );

        return true;
    }

    /**
     * @param string $entity_id
     * @return User
     * @throws Exception
     */
    public static function fromEntityId(string $entity_id): User {
        $entity_id = System::parseEntityId($entity_id);

        if($entity_id->entity_type != self::ENTITY_TYPE) {
            throw new Exception("Entity ID is not a User!");
        }

        return new User($entity_id->id);
    }

    public static function create(
        System $system,
        $user_name,
        $password,
        $email,
        $gender,
        $village,
        $location,
        $verification_code
    ) {

        $initial_vars = [
            'user_name' => $user_name,
            'password' => $password,
            'email' => $email,
            'staff_level' => User::STAFF_NONE,
            'last_ip' => $_SERVER['REMOTE_ADDR'],
            'failed_logins' => 0,

            'gender' => $gender,
            'spouse' => 0,
            'village' => $village,
            'level' => 1,
            'rank' => 1,
            'health' => 100.00,
            'max_health' => 100.00,
            'stamina' => 100.00,
            'max_stamina' => 100.00,
            'chakra' => 100.00,
            'max_chakra' => 100.00,
            'regen_rate' => 10,

            'exp' => User::BASE_EXP,
            'bloodline_id' => 0,
            'bloodline_name' => '',
            'clan_id' => 0,
            'location' => $location,
            'money' => 100,
            'pvp_wins' => 0,
            'pvp_losses' => 0,
            'ai_wins' => 0,
            'ai_losses' => 0,

            'train_type' => '',
            'train_time' => 0,
            'train_gain' => 0,

            'ninjutsu_skill' => 10,
            'genjutsu_skill' => 10,
            'taijutsu_skill' => 10,
            'bloodline_skill' => 0,

            'cast_speed' => 5,
            'speed' => 5,
            'intelligence' => 5,
            'willpower' => 5,

            'register_date' => time(),
            'verify_key' => $verification_code,
            'layout' => 'shadow_ribbon',
            'avatar_link' => './images/default_avatar.png',

            // '', '', '', 0, 0, 0, 0,
            'forbidden_seal' => '',
            'chat_color' => '',
            'current_ip' => $_SERVER['REMOTE_ADDR'],
            'profile_song' => '',
            'last_ai' => 0,
            'last_free_stat_change' => 0,
            'last_pvp' => 0,
            'last_death' => 0,

            'mission_stage' => '',
            'ban_type' => '',
        ];

        $columns = array_map(function($key) {
            return "`{$key}`";
        }, array_keys($initial_vars));

        $values = array_map(function($val) {
            return "'{$val}'";
        }, $initial_vars);

        /** @noinspection SqlInsertValues */
        $query = "INSERT INTO `users` (" . implode(',', $columns) . ")
		    VALUES(" . implode(',', $values) . ")";
        $system->query($query);

        return $system->db_last_insert_id;
    }
}
