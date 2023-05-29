<?php /** @noinspection PhpRedundantOptionalArgumentInspection */

require_once __DIR__ . "/Jutsu.php";
require_once __DIR__ . "/Team.php";
require_once __DIR__ . "/DailyTask.php";
require_once __DIR__ . "/ForbiddenSeal.php";
require_once __DIR__ . "/battle/Fighter.php";
require_once __DIR__ . "/travel/TravelCoords.php";
require_once __DIR__ . "/travel/Travel.php";
require_once __DIR__ . "/StaffManager.php";
require_once __DIR__ . "/Rank.php";
require_once __DIR__ . "/Village.php";
require_once __DIR__ . "/Clan.php";

/*	Class:		User
	Purpose:	Fetch user data and load into class variables.
*/

class User extends Fighter {
    const ENTITY_TYPE = 'U';

    const AVATAR_MAX_SIZE = 150;

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

    public static array $ELEMENTS = [
        'Fire', 'Wind', 'Lightning', 'Earth', 'Water'
    ];

    const MIN_NAME_LENGTH = 2;
    const MAX_NAME_LENGTH = 18;
    const MIN_PASSWORD_LENGTH = 6;
    const PARTIAL_LOCK = 3;
    const FULL_LOCK = 5;

    const BASE_EXP = 500;
    const BASE_REGEN = 25;

    const BASE_JUTSU_SLOTS = 4;
    const BASE_ARMOR_SLOTS = 2;
    const BASE_WEAPON_SLOTS = 1;

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
    const ATTACK_LINK_DURATION_MIN = 10;

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
    public Village $village;
    public int $level;
    public bool $level_up;

    public int $rank_num;
    public Rank $rank;
    public bool $rank_up;

    public int $exp;
    public $staff_level;
    public StaffManager $staff_manager;
    public $support_level;
    public int $bloodline_id;
    public $bloodline_name;

    public int $clan_id = 0;
    public ?Clan $clan = null;

    public TravelCoords $location;
    public TravelCoords $village_location;
    public bool $in_village;
    public MapLocation $current_location;

    public float $last_movement_ms;
    public string $attack_id;
    public int $attack_id_time_ms;
    public array $filters;
    public $train_type;
    public $train_gain;
    public int $train_time;

    private int $money;

    public int $pvp_wins;
    public int $pvp_losses;
    public int $ai_wins;
    public int $ai_losses;

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
    public array $special_items;

    /** @var Item[] */
    public array $items;
    public array $equipped_weapon_ids;

    public ?Bloodline $bloodline = null;
    public float $bloodline_skill;

    public $ban_data;
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
    public bool $inventory_loaded;

    public int $last_update;
    public int $last_active;
    public ForbiddenSeal $forbidden_seal;
    public bool $forbidden_seal_loaded = false;
    public $chat_color;
    public $chat_effect;
    public $last_login;

    public $jutsu_scrolls;
    public string $avatar_link;
    public $profile_song;
    public $log_actions;

    /*
     * AS = 10
     * Genin = 40
     * Chuunin = 100
     * Jonin = 170
     */
    public int $regen_rate;

    public array $elements;

    public int $regen_boost = 0;

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

    public int $last_ai_ms;

    public int $last_free_stat_change;

    public int $last_pvp_ms;
    public int $last_death_ms;

    private int $premium_credits;
    public int $premium_credits_purchased;

    public bool $censor_explicit_language = true;

    public int $total_stats;

    public int $scout_range;

    public int $stealth;
    public int $village_changes;
    public int $clan_changes;

    public int $clan_office;

    public array $equipped_armor;
    public array $bloodline_offense_boosts;
    public array $bloodline_defense_boosts;

    public ?int $sensei_id = null;
    public bool $accept_students;

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
     * @param System $system
     * @param int    $user_id
     * @throws Exception
     */
    public function __construct(System $system, int $user_id) {
        $this->system =& $system;
        if(!$user_id) {
            throw new Exception("Invalid user id!");
        }

        $this->user_id = $user_id;
        $this->id = self::ENTITY_TYPE . ':' . $this->user_id;
    }

    /**
     * @param System $system
     * @param int    $user_id
     * @param bool   $remote_view
     * @return User
     * @throws Exception
     */
    public static function loadFromId(System $system, int $user_id, bool $remote_view = false): User {
        $user = new User($system, $user_id);

        $result = $system->query("SELECT
            `user_id`,
            `user_name`,
            `ban_data`,
            `ban_type`,
            `ban_expire`,
            `journal_ban`,
            `avatar_ban`,
            `song_ban`,
            `last_login`,
            `regen_rate`,
			`forbidden_seal`,
			`chat_color`,
			`chat_effect`,
			`staff_level`,
			`username_changes`,
			`support_level`,
			`special_mission`,
            `rank`,
            `sensei_id`,
            `accept_students`,
            `village`
			FROM `users` WHERE `user_id`='$user_id' LIMIT 1"
        );
        if($system->db_last_num_rows == 0) {
            throw new Exception("User does not exist!");
        }

        $user_data = $system->db_fetch($result);

        $user->user_name = $user_data['user_name'];
        $user->username_changes = $user_data['username_changes'];

        $user->staff_level = $user_data['staff_level'];
        $user->support_level = $user_data['support_level'];
        $user->staff_manager = $user->loadStaffManager();

        $user->ban_data = $user->loadBanData($user_data['ban_data']);
        $user->ban_type = $user_data['ban_type'];
        $user->ban_expire = $user_data['ban_expire'];
        $user->journal_ban = $user_data['journal_ban'];
        $user->avatar_ban = $user_data['avatar_ban'];
        $user->song_ban = $user_data['song_ban'];

        $user->last_login = $user_data['last_login'];

        $user->regen_rate = $user_data['regen_rate'];
        $user->regen_boost = 0;

        $user->setForbiddenSealFromDb($user_data['forbidden_seal'], $remote_view);
        $user->regen_boost += ceil($user->regen_rate * ($user->forbidden_seal->regen_boost / 100));

        $user->chat_color = $user_data['chat_color'];
        $user->chat_effect = $user_data['chat_effect'];

        $user->sensei_id = $user_data['sensei_id'];
        $user->village = new Village($system, $user_data['village']);
        $user->rank_num = $user_data['rank'];
        $user->accept_students = $user_data['accept_students'];

        //Todo: Remove this in a couple months, only a temporary measure to support current bans
        if($user->ban_type) {
            if($user->ban_expire > time()) {
                $user->ban_data[$user->ban_type] = $user->ban_expire;
                $ban_data = json_encode($user->ban_data);
                $user->system->query("UPDATE `users` SET
                    `ban_data` = '{$ban_data}',
                   `ban_type` = '',
                   `ban_expire` = NULL
                WHERE `user_id`='{$user->user_id}' LIMIT 1");
            }
        }

        if(!$remote_view) {
            $user->checkBanExpiry();
        }

        $user->inventory_loaded = false;

        return $user;
    }

    /**
     * @param System $system
     * @param string $name
     * @param bool   $remote_view
     * @return User|null
     * @throws Exception
     */
    public static function findByName(System $system, string $name, bool $remote_view = true): ?User {
        $result = $system->query("SELECT
               `user_id` FROM `users` WHERE `user_name`='{$name}'");
        $user_id = $system->db_fetch($result)['user_id'] ?? null;

        if($user_id) {
            return User::loadFromId(system: $system, user_id: $user_id, remote_view: $remote_view);
        }

        return null;
    }

    /* function loadData()
        Loads user data from the database into class members
        -Parameters-
        Update (1 = regen, 2 = training)
    */

    /**
     * @throws Exception
     */
    public function loadData($UPDATE = User::UPDATE_FULL, $remote_view = false): void {
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

        $this->censor_explicit_language = (bool)$user_data['censor_explicit_language'];

        // Message blacklist
        $this->blacklist = [];
        $result = $this->system->query("SELECT `blocked_ids` FROM `blacklist` WHERE `user_id`='$this->user_id' LIMIT 1");
        if($result->num_rows != 0) {
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
        $this->rank_num = $user_data['rank'];
        $this->rank_up = $user_data['rank_up'];
        $rank_data = $this->system->query("SELECT * FROM `ranks` WHERE `rank_id`='$this->rank_num'");
        if($this->system->db_last_num_rows == 0) {
            $this->system->message("Invalid rank!");
            $this->system->printMessage("Invalid rank!");
        }
        else {
            $rank_data = $this->system->db_fetch($rank_data);
            $this->rank = Rank::fromDb($rank_data);
        }

        $this->gender = $user_data['gender'];
        $this->village = new Village($this->system, $user_data['village']);
        $this->level = $user_data['level'];
        $this->level_up = $user_data['level_up'];
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

        $this->last_ai_ms = $user_data['last_ai_ms'];
        $this->last_free_stat_change = $user_data['last_free_stat_change'];
        $this->last_pvp_ms = $user_data['last_pvp_ms'];
        $this->last_death_ms = $user_data['last_death_ms'];

        $this->layout = $user_data['layout'];

        $this->exp = $user_data['exp'];
        $this->bloodline_id = $user_data['bloodline_id'];
        $this->bloodline_name = $user_data['bloodline_name'];

        if($this->bloodline_id) {
            array_unshift($this->stats, 'bloodline_skill');
        }

        $this->location = TravelCoords::fromDbString($user_data['location']);
        $this->current_location = Travel::getLocation($this->system, $this->location->x, $this->location->y, $this->location->map_id);
        $this->last_movement_ms = $user_data['last_movement_ms'];
        // generate a new attack link if it's been 10 minutes
        if ($user_data['attack_id_time_ms'] <= (System::currentTimeMs() - (60 * User::ATTACK_LINK_DURATION_MIN * 1000))) {
            $this->attack_id = uniqid($this->id . ':');
            $this->attack_id_time_ms = System::currentTimeMs();
        } else {
            $this->attack_id = $user_data['attack_id'];
            $this->attack_id_time_ms = $user_data['attack_id_time_ms'];
        }

        if ($user_data['filters'] === null) {
            $filters = [
                'travel_ranks_to_view' => array_fill(1, System::SC_MAX_RANK, true)
            ];
            $user_data['filters'] = json_encode($filters);
        }
        $this->filters = json_decode($user_data['filters'], true);

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

        if($this->rank_num > 3) {
            $this->scout_range++;
        }
        if($this->isHeadAdmin()) {
            $this->scout_range += 2;
        }

        $this->village_changes = $user_data['village_changes'];
        $this->clan_changes = $user_data['clan_changes'];

        // Village
        $this->village_location = Village::getLocation($this->system, $this->village->name);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->in_village = $this->village_location !== null && $this->location->equals($this->village_location);

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
        $this->clan_id = (int)$user_data['clan_id'];
        $this->clan_office = 0;
        if($this->clan_id) {
            $this->clan = Clan::loadFromId($this->system, $this->clan_id);
            if($this->clan) {
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
                $this->rank_num,
                $this->ninjutsu_skill, $this->taijutsu_skill, $this->genjutsu_skill, $this->bloodline_skill,
                $this->rank->base_stats, $this->total_stats, $this->rank->max_level_stats,
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
        $this->setForbiddenSealFromDb($user_data['forbidden_seal'], $remote_view);
        $this->regen_boost += ceil($this->regen_rate * ($this->forbidden_seal->regen_boost / 100));

        //In Village Regen
//        if($this->in_village) {
//            // regen boost or regen rate?
//            $this->regen_boost += ($this->regen_rate / 2);
//        }

        // Location with Regen Boost
        if ($this->current_location->location_id && $this->current_location->regen) {
            $this->regen_boost += ($this->current_location->regen / 100) * $this->regen_rate;
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
        if($this->train_time && $UPDATE >= User::UPDATE_FULL) {
            $this->checkTraining();
        }

        // Correction location
        if(TravelManager::locationIsInVillage($this->system, $this->location) &&
            !$this->location->equals($this->village_location) &&
            !$this->isHeadAdmin()
        ) {
            $this->location->x--;
        }

        // Sensei
        $this->sensei_id = $user_data['sensei_id'];
        $this->accept_students = $user_data['accept_students'];

        return;
    }

    public function loadBanData($ban_data) {
        if($ban_data === null) {
            return array();
        }
        else {
            return json_decode($ban_data, true);
        }
    }
    public function checkBanExpiry():bool|string {
        if($this->ban_data != null) {
            $ban_expired = false;
            $ban_expire_return_string = 'Your ';

            foreach($this->ban_data as $ban_name => $ban_expire_time) {
                if($ban_expire_time != -1 && $ban_expire_time - time() <= 0) {
                    $ban_expired = true;
                    unset($this->ban_data[$ban_name]);
                    $ban_expire_return_string .= "$ban_name ban & ";
                }
            }

            if($ban_expired) {
                //Format data if no bans are presnet
                $ban_data = (!empty($this->ban_data)) ? json_encode($this->ban_data) : null;
                //Update user table to remove ban(s)
                $this->system->query("UPDATE `users` SET `ban_data`='{$ban_data}' WHERE `user_id`='{$this->user_id}' LIMIT 1");
                //Return ban message expiry message for display
                if ($this->system->db_last_affected_rows) {
                    return substr($ban_expire_return_string, 0, strlen($ban_expire_return_string) - 2)
                        . " has expired.";
                }
            }
        }
        return false;
    }
    public function checkBan($type):bool {
        if(in_array($type, StaffManager::$ban_types) && isset($this->ban_data[$type])) {
            if($this->ban_data[$type] == StaffManager::PERM_BAN_VALUE || $this->ban_data[$type] - time() >= 1) {
                return true;
            }
        }
        return false;
    }

    public function setForbiddenSealFromDb(string $forbidden_seal_db, bool $remote_view) {
        if(!$forbidden_seal_db) {
            $this->forbidden_seal = new ForbiddenSeal($this->system, 0, 0);
        }
        else {
            // Prep seal data from DB
            $forbidden_seal = json_decode($forbidden_seal_db, true);

            // Set seal data
            $this->forbidden_seal = new ForbiddenSeal($this->system, $forbidden_seal['level'], $forbidden_seal['time']);
        }

        $this->forbidden_seal_loaded = true;

        // Check if seal is expired & remove if it is
        if(!$remote_view) {
            $this->forbidden_seal->checkExpiration();
        }

        // Load benefits
        $this->forbidden_seal->setBenefits();
    }

    /**
     * Providing an actual ID will return the OW and mark the warning as read
     * @param $id
     * @return bool|void|array
     */
    public function getOfficialWarning($id) {
        $result = $this->system->query("SELECT * FROM `official_warnings` WHERE `user_id`='{$this->user_id}' AND `warning_id`='{$id}' LIMIT 1");
        if($this->system->db_last_num_rows) {
            $this->system->query("UPDATE `official_warnings` SET `viewed`=1 WHERE `warning_id`='{$id}' LIMIT 1");
            return $this->system->db_fetch($result);
        }
        else {
            return false;
        }
    }

    public function getOfficialWarnings($for_notification = false) {
        $query = "SELECT * FROM `official_warnings` WHERE `user_id`='{$this->user_id}'";
        if($for_notification) {
            $query .= " AND `viewed`=0";
        }
        $query .= " ORDER BY `time` DESC";

        $result = $this->system->query($query);
        if($this->system->db_last_num_rows) {
            if($for_notification) {
                return true;
            }
            else {
                $warnings = [];
                while($warning = $this->system->db_fetch($result)) {
                    $warnings[] = $warning;
                }
                return $warnings;
            }
        }
        else {
            if($for_notification) {
                return false;
            }
            else {
                return array();
            }
        }
    }

    public function loadStaffManager() {
        if(isset($this->staff_manager) && $this->staff_manager instanceof StaffManager) {
            return $this->staff_manager;
        }
        return new StaffManager($this->system, $this->user_id, $this->user_name, $this->staff_level, $this->support_level);
    }

    public function getInventory() {
        // Query user owned inventory
        $result = $this->system->query("SELECT * FROM `user_inventory` WHERE `user_id` = '{$this->user_id}'");

        $player_jutsu = [];
        $player_item_inventory = [];
        $equipped_jutsu = [];
        $equipped_items = [];
        $this->special_items = [];

        // Decode JSON of inventory into variables
        if($this->system->db_last_num_rows > 0) {
            $user_inventory = $this->system->db_fetch($result);
            $player_jutsu = json_decode($user_inventory['jutsu'], true);
            $player_item_inventory = json_decode($user_inventory['items'], true);
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
				AND `purchase_type` != '1' AND `rank` <= '{$this->rank_num}'"
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
                if($this->hasJutsu($jutsu_data->id)) {
                    $this->equipped_jutsu[$count]['id'] = $jutsu_data->id;
                    $this->equipped_jutsu[$count]['type'] = $jutsu_data->type;
                    $count++;
                }
            }
        }
        else {
            $this->equipped_jutsu = [];
        }

        if($player_item_inventory) {
            $player_items_array = $player_item_inventory;
            $player_item_inventory = [];
            $player_items_string = '';

            foreach($player_items_array as $item) {
                if(!is_numeric($item['item_id'])) {
                    continue;
                }
                $player_item_inventory[$item['item_id']] = $item;
                $player_items_string .= $item['item_id'] . ',';
            }
            $player_items_string = substr($player_items_string, 0, strlen($player_items_string) - 1);

            $this->items = [];

            $result = $this->system->query("SELECT * FROM `items` WHERE `item_id` IN ({$player_items_string})");
            if($this->system->db_last_num_rows > 0) {
                while($item_data = $this->system->db_fetch($result)) {
                    $item_id = $item_data['item_id'];
                    $this->items[$item_id] = Item::fromDb($item_data, $player_item_inventory[$item_id]['quantity']);
                    if($this->items[$item_id]->use_type == Item::USE_TYPE_SPECIAL) {
                        $this->special_items[$item_id] = $this->items[$item_id];
                    }
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
        $this->equipped_weapon_ids = [];
        $this->equipped_armor = [];
        if($equipped_items) {
            foreach($equipped_items as $item_id) {
                if($this->hasItem($item_id)) {
                    $this->equipped_items[] = $item_id;
                    if($this->items[$item_id]->use_type == 1) {
                        $this->equipped_weapon_ids[] = $item_id;
                    }
                    else if($this->items[$item_id]->use_type == 2) {
                        $this->equipped_armor[] = $item_id;
                    }
                }
            }
        }

        $this->inventory_loaded = true;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function checkTraining(): void {
        // Used for sidemenu display
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

                if($this->hasJutsu($jutsu_id)) {
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
                        if($this->total_stats < $this->rank->stat_cap) {
                            $this->{$jutsu_skill_type}++;
                            $this->exp += 10;
                            $message .= ' You have gained 1 ' . ucwords(str_replace('_', ' ', $jutsu_skill_type)) .
                                ' and 10 experience.';
                        }

                        $this->system->message($message);
                        $this->system->printMessage();

                        if(!$this->ban_type) {
                            $this->updateInventory();
                        }
                    }
                }

                $this->train_time = 0;
            }
            // Skill/attribute training
            else {
                if(!in_array($this->train_type, $this->stats)) {
                    $this->system->message("Training an invalid stat: {$this->train_type}. Training cancelled.");
                    $this->system->log('invalid_training', $this->user_id, "Stat: {$this->train_type} / Amount: $this->train_gain");
                    $this->train_time = 0;
                    return;
                }

                // TEAM BOOST TRAINING GAINS
                if($this->team != null && $this->train_gain < $this->rank->stat_cap * 0.05) {
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
                $gain_description = $this->addStatGain($this->train_type, $this->train_gain);

                $this->train_time = 0;
                if($gain_description) {
                    $this->system->message($gain_description . '.' . $team_boost_description);
                }
                else if($this->total_stats >= $this->rank->stat_cap) {
                    $this->system->message("Training has finished but you cannot gain any more stats!");
                }
            }
        }
    }

    /**
     * @param string $stat
     * @param int    $stat_gain
     * @return string
     * @throws Exception
     */
    public function addStatGain(string $stat, int $stat_gain): string {
        if(!in_array($stat, $this->stats)) {
            throw new Exception("Invalid stat!");
        }

        $new_total_stats = $this->total_stats + $stat_gain;
        if($new_total_stats > $this->rank->stat_cap) {
            $stat_gain -= $new_total_stats - $this->rank->stat_cap;
            if($stat_gain < 0) {
                $stat_gain = 0;
            }
        }

        $this->{$stat} += $stat_gain;
        $this->updateTotalStats();

        $this->exp = $this->total_stats * 10;

        if($stat_gain == 0) {
            return "";
        }

        return "You have gained {$stat_gain} " . System::unSlug($stat) . " and " . ($stat_gain * 10) . " experience";
    }

    public function updateTotalStats() {
        $this->total_stats = $this->ninjutsu_skill + $this->genjutsu_skill + $this->taijutsu_skill + $this->bloodline_skill +
            $this->cast_speed + $this->speed + $this->intelligence + $this->willpower;
    }

    public function getTrainingStatForArena(): ?string {
        $training_stat = null;

        if($this->train_type) {
            if(str_contains($this->train_type, 'jutsu:')) {
                if(!$this->inventory_loaded) {
                    $this->getInventory();
                }

                $jutsu_id = $this->train_gain;
                $jutsu = $this->player->jutsu[$jutsu_id] ?? null;

                if($jutsu != null) {
                    switch($jutsu->jutsu_type) {
                        case Jutsu::TYPE_GENJUTSU:
                            $training_stat = 'genjutsu_skill';
                            break;
                        case Jutsu::TYPE_TAIJUTSU:
                            $training_stat = 'taijutsu_skill';
                            break;
                        case Jutsu::TYPE_NINJUTSU:
                            $training_stat = 'ninjutsu_skill';
                            break;
                    }
                }
            }
            else {
                $training_stat = $this->train_type;
            }
        }

        return $training_stat;
    }

    public function hasJutsu(int $jutsu_id): bool {
        return isset($this->jutsu[$jutsu_id]);
    }

    public function hasItem(int $item_id): bool {
        return isset($this->items[$item_id]);
    }

    /* function useJutsu
        pool check, calc exp, etc */
    public function useJutsu(Jutsu $jutsu): ActionResult {
        switch($jutsu->jutsu_type) {
            case 'ninjutsu':
            case 'genjutsu':
                $energy_type = 'chakra';
                break;
            case 'taijutsu':
                $energy_type = 'stamina';
                break;
            default:
                return ActionResult::failed("Invalid energy type!");
        }

        if($this->{$energy_type} < $jutsu->use_cost) {
            return ActionResult::failed("You do not have enough $energy_type!");
        }

        switch($jutsu->purchase_type) {
            case Jutsu::PURCHASE_TYPE_PURCHASABLE:
                // Element check
                if($jutsu->element && $jutsu->element != Jutsu::ELEMENT_NONE) {
                    if($this->elements) {
                        if(!in_array($jutsu->element, $this->elements)) {
                            return ActionResult::failed("You do not possess the elemental chakra for this jutsu!");
                        }
                    }
                    else {
                        return ActionResult::failed("You do not possess the elemental chakra for this jutsu!");
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
                return ActionResult::failed("Invalid jutsu type!");
        }

        return ActionResult::succeeded();
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
        if($this->getPremiumCredits() < $amount) {
            throw new Exception("Not enough Ancient Kunai!");
        }
        $this->setPremiumCredits($this->premium_credits - $amount, $description);
    }


    /* function moteToVillage()
        moves user to village */
    public function moveToVillage() {
        $this->location = $this->village_location;
    }

    /* function updateData()
        Updates user data from class members into database
        -Parameters-
    */
    public function updateData() {

        /** @noinspection SqlWithoutWhere */
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
		`village` = '{$this->village->name}',
		`level` = '$this->level',
		`level_up` = '" . (int)$this->level_up . "',
		`rank` = '$this->rank_num',
		`rank_up` = '" . (int)$this->rank_up . "',
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
		`bloodline_name` = '$this->bloodline_name',
        `accept_students` = '" . (int)$this->accept_students . "',
        `sensei_id` = '$this->sensei_id',";
        if($this->clan) {
            $query .= "`clan_id` = '{$this->clan->id}',
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
		`last_movement_ms` = '$this->last_movement_ms',
		`attack_id` = '$this->attack_id',
		`attack_id_time_ms` = '$this->attack_id_time_ms',
		`location` = '".$this->location->fetchString()."',
		`filters` = '".json_encode($this->filters)."',";
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
		`last_ai_ms` = '$this->last_ai_ms',
		`last_free_stat_change` = '{$this->last_free_stat_change}',
		`last_pvp_ms` = '$this->last_pvp_ms',
		`last_death_ms` = '$this->last_death_ms',";

        $forbidden_seal = $this->forbidden_seal->dbEncode();

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
        `chat_effect` = '$this->chat_effect',
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
		`clan_changes` = '$this->clan_changes',
		`censor_explicit_language` = " . (int)$this->censor_explicit_language . "
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
                    'item_id' => $item->id,
                    'quantity' => $item->quantity,
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
                if($jutsu->rank > $this->rank_num) {
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
        //Give staff members premium avatar size if they do not have seal
        if($this->staff_level && $this->forbidden_seal->level == 0) {
            return ForbiddenSeal::$benefits[ForbiddenSeal::$STAFF_SEAL_LEVEL]['avatar_size'];
        }
        elseif($this->forbidden_seal->level != 0) {
            return $this->forbidden_seal->avatar_size;
        }
        else {
            return self::AVATAR_MAX_SIZE;
        }
    }

    public function canChangeChatColor(): bool {
        // Premium purchased
        if($this->premium_credits_purchased) {
            return true;
        }

        // Forbidden seal
        if($this->forbidden_seal->level != 0) {
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

        if($this->forbidden_seal->level != 0 || $this->isHeadAdmin()) {
            if($this->isHeadAdmin()) {
                $return = array_merge($return, ForbiddenSeal::$benefits[ForbiddenSeal::$STAFF_SEAL_LEVEL]['name_colors']);
            }
            else {
                $return = array_merge($return, $this->forbidden_seal->name_colors);
            }
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

    public function getAvatarFileSizeDisplay($format='MB'): string {
        $max_size = $this->forbidden_seal->avatar_filesize;
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

    public function expForNextLevel(?int $extra_levels = 0): float|int {
        return $this->rank->exp_per_level * (($this->level + 1 + $extra_levels) - $this->rank->base_level) + ($this->rank->base_stats * 10);
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
    const LOG_MISSION = 'mission';
    const LOG_SPECIAL_MISSION = 'special_mission';
    const LOG_IN_BATTLE = 'in_battle';
    const LOG_NOT_IN_VILLAGE = 'not_in_village';

    public function log(string $log_type, string $log_contents): bool {
        $valid_log_types = [
            self::LOG_TRAINING, self::LOG_ARENA, self::LOG_LOGIN, self::LOG_MISSION, self::LOG_SPECIAL_MISSION, self::LOG_IN_BATTLE, self::LOG_NOT_IN_VILLAGE
        ];
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
    public static function fromEntityId(System $system, string $entity_id): User {
        $entity_id = System::parseEntityId($entity_id);

        if($entity_id->entity_type != self::ENTITY_TYPE) {
            throw new Exception("Entity ID is not a User!");
        }

        return User::loadFromId($system, $entity_id->id);
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
            'regen_rate' => User::BASE_REGEN,

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
            'last_ai_ms' => 0,
            'last_free_stat_change' => 0,
            'last_pvp_ms' => 0,
            'last_death_ms' => 0,

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
