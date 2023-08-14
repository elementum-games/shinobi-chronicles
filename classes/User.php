<?php /** @noinspection PhpRedundantOptionalArgumentInspection */

require_once __DIR__ . "/Jutsu.php";
require_once __DIR__ . "/Team.php";
require_once __DIR__ . "/DailyTask.php";
require_once __DIR__ . "/UserDailyTasks.php";
require_once __DIR__ . "/ForbiddenSeal.php";
require_once __DIR__ . "/battle/Fighter.php";
require_once __DIR__ . "/travel/TravelCoords.php";
require_once __DIR__ . "/travel/Travel.php";
require_once __DIR__ . "/StaffManager.php";
require_once __DIR__ . "/Rank.php";
require_once __DIR__ . "/Village.php";
require_once __DIR__ . "/Clan.php";
require_once __DIR__ . "/achievements/AchievementsManager.php";
require_once __DIR__ . "/UserReputation.php";
require_once __DIR__ . "/training/TrainingManager.php";
require_once __DIR__ . "/event/LanternEvent.php";

/*	Class:		User
	Purpose:	Fetch user data and load into class variables.
*/

class User extends Fighter {
    const ENTITY_TYPE = 'U';

    const AVATAR_MAX_SIZE = 125;

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

    public static array $HEAL_REGEN_MULTIPLIER = [
        1 => 2,
        2 => 4,
        3 => 10,
        4 => 30,
    ];

    const MIN_NAME_LENGTH = 2;
    const MAX_NAME_LENGTH = 18;
    const MIN_PASSWORD_LENGTH = 6;
    const PARTIAL_LOCK = 3;
    const FULL_LOCK = 5;
    const LOCK_OUT_CD = 5 * 60;
    const MALICIOUS_LOCKOUT_CD = 15 * 60;

    const BASE_EXP = 0;
    const BASE_REGEN = 25;

    const BASE_JUTSU_SLOTS = 4;
    const BASE_ARMOR_SLOTS = 2;
    const BASE_WEAPON_SLOTS = 1;

    const MAX_CONSUMABLES = 15;

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
    private bool $read_only = false;

    public string $id;
    public int $user_id;
    public string $user_name;
    public int $free_username_changes;
    public $blacklist;
    public $original_blacklist;

    /** @var DailyTask[] */
    public ?UserDailyTasks $daily_tasks;
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
    public $last_malicious_ip;
    public $global_message_viewed;

    public string $gender;
    public int $spouse;
    public string $spouse_name;
    public int $marriage_time;
    public int $level;
    public bool $level_up;

    public Village $village;
    public int $village_rep;
    public int $weekly_rep;
    public int $pvp_rep;
    public int $last_pvp_rep_reset;
    public int $mission_rep_cd;
    public UserReputation $reputation;
    public ?string $recent_players_killed_ids;
    public ?string $recent_killer_ids;

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
    public TrainingManager $trainingManager;

    public int $stat_transfer_amount;
    public int $stat_transfer_completion_time;
    public string $stat_transfer_target_stat;

    public Currency $money;

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

    // Use giveItem() to give an item, don't add it directly to array yourself
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

    public string $layout;

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

    /** @var Jutsu[] */
    public array $jutsu_scrolls = [];
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

    public Currency $premium_credits;
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

    /** @var PlayerAchievement[]  */
    public array $achievements = [];

    /** @var AchievementProgress[]  */
    public array $achievements_in_progress = [];

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
     * @throws RuntimeException
     */
    public function __construct(System $system, int $user_id) {
        $this->system =& $system;
        if(!$user_id) {
            throw new RuntimeException("Invalid user id!");
        }

        $this->user_id = $user_id;
        $this->id = self::ENTITY_TYPE . ':' . $this->user_id;
    }

    /**
     * @param System $system
     * @param int    $user_id
     * @param bool   $remote_view
     * @return User
     * @throws RuntimeException
     */
    public static function loadFromId(
        System $system,
        int $user_id,
        bool $remote_view = false,
        bool $read_only = false
    ): User {
        $user = new User($system, $user_id);
        $user->read_only = $read_only;

        $query = "SELECT
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
                FROM `users` WHERE `user_id`='$user_id' LIMIT 1";
        if(!$read_only) {
            $query .= " FOR UPDATE";
        }
        $result = $system->db->query($query);

        if($system->db->last_num_rows == 0) {
            throw new RuntimeException("User does not exist!");
        }

        $user_data = $system->db->fetch($result);

        $user->user_name = $user_data['user_name'];
        $user->free_username_changes = $user_data['username_changes'];

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
                $user->system->db->query(
                    "UPDATE `users` SET
                        `ban_data` = '{$ban_data}',
                       `ban_type` = '',
                       `ban_expire` = NULL
                    WHERE `user_id`='{$user->user_id}' LIMIT 1"
                );
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
     * @throws RuntimeException
     */
    public static function findByName(System $system, string $name, bool $remote_view = true): ?User {
        $result = $system->db->query(
            "SELECT
                   `user_id` FROM `users` WHERE `user_name`='{$name}'"
        );
        $user_id = $system->db->fetch($result)['user_id'] ?? null;

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
     * @throws RuntimeException
     */
    public function loadData($UPDATE = User::UPDATE_FULL, $remote_view = false): void {
        $result = $this->system->db->query("SELECT * FROM `users` WHERE `user_id`='$this->user_id' LIMIT 1 FOR UPDATE");
        $user_data = $this->system->db->fetch($result);

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
        $this->last_malicious_ip = $user_data['last_malicious_ip'];
        $this->avatar_link = $user_data['avatar_link'];
        $this->profile_song = $user_data['profile_song'];

        $this->log_actions = $user_data['log_actions'];

        $this->censor_explicit_language = (bool)$user_data['censor_explicit_language'];

        // Message blacklist
        $this->blacklist = [];
        $result = $this->system->db->query(
            "SELECT `blocked_ids` FROM `blacklist` WHERE `user_id`='$this->user_id' LIMIT 1"
        );
        if($result->num_rows != 0) {
            $blacklist = $this->system->db->fetch($result);
            $this->blacklist = json_decode($blacklist['blocked_ids'], true);
            $this->original_blacklist = $this->blacklist;
        }
        else {
            $blacklist_json = json_encode($this->blacklist);
            $this->system->db->query(
                "INSERT INTO `blacklist` (`user_id`, `blocked_ids`) VALUES ('{$this->user_id}', '{$blacklist_json}')"
            );
            $this->original_blacklist = []; // Default an empty array, user did not have an original.
        }

        // Rank stuff
        $this->rank_num = $user_data['rank'];
        $this->rank_up = $user_data['rank_up'];
        $rank_data = $this->system->db->query("SELECT * FROM `ranks` WHERE `rank_id`='$this->rank_num'");
        if($this->system->db->last_num_rows == 0) {
            $this->system->message("Invalid rank!");
            $this->system->printMessage("Invalid rank!");
        }
        else {
            $rank_data = $this->system->db->fetch($rank_data);
            $this->rank = Rank::fromDb($rank_data);
        }

        $this->village = new Village($this->system, $user_data['village']);
        $this->village_rep = $user_data['village_rep'];
        $this->weekly_rep = $user_data['weekly_rep'];
	    $this->pvp_rep = $user_data['pvp_rep'];
        $this->mission_rep_cd = $user_data['mission_rep_cd'];
        $this->recent_players_killed_ids = $user_data['recent_players_killed_ids'];
	    $this->recent_killer_ids = $user_data['recent_killer_ids'];
        $this->reputation = new UserReputation($this->village_rep, $this->weekly_rep, $this->pvp_rep, $this->recent_players_killed_ids, $this->recent_killer_ids, $this->mission_rep_cd, $this->system->event);

        $this->gender = $user_data['gender'];
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

        $this->ninjutsu_skill = $user_data['ninjutsu_skill'];
        $this->genjutsu_skill = $user_data['genjutsu_skill'];
        $this->taijutsu_skill = $user_data['taijutsu_skill'];

        $this->bloodline_skill = $user_data['bloodline_skill'];

        $this->cast_speed = $user_data['cast_speed'];
        $this->speed = $user_data['speed'];
        $this->intelligence = $user_data['intelligence'];
        $this->willpower = $user_data['willpower'];

        $total_skills = $this->ninjutsu_skill + $this->taijutsu_skill + $this->genjutsu_skill + $this->bloodline_skill;
        $total_attribs = $this->speed + $this->cast_speed;
        $this->daily_tasks = new UserDailyTasks($this->system, $this->user_id, $this->rank_num, $total_skills, $total_attribs, $this->pvp_rep);

        $this->money = new Currency(
            system: $this->system,
            type: Currency::TYPE_MONEY,
            user_id: $this->user_id,
            amount: $user_data['money'],
            userDailyTasks: $this->daily_tasks
        );
        $this->premium_credits = new Currency(
            system: $this->system,
            type: Currency::TYPE_PREMIUM_CREDITS,
            user_id: $this->user_id,
            amount: $user_data['premium_credits']
        );
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

        $this->total_stats = $this->ninjutsu_skill + $this->genjutsu_skill + $this->taijutsu_skill + $this->bloodline_skill +
            $this->cast_speed + $this->speed + $this->intelligence + $this->willpower;

        $this->stat_transfer_amount = $user_data['stat_transfer_amount'];
        $this->stat_transfer_completion_time = $user_data['stat_transfer_completion_time'];
        $this->stat_transfer_target_stat = $user_data['stat_transfer_target_stat'];

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


        // Check Daily Tasks completion
        $this->daily_tasks_reset = $this->daily_tasks->last_reset;
        if($UPDATE == User::UPDATE_FULL && !$remote_view) {
            // Process tasks completion
            $completion_data = $this->daily_tasks->checkTaskCompletion();
            if($completion_data != null) {
                $this->money->add($completion_data['money_gain'], 'Completed daily task');
                $rep_gain = $this->reputation->addRep($completion_data['rep_gain'], UserReputation::DAILY_TASK_BYPASS_CAP);
                $task_display = "You have completed the task" . (sizeof($completion_data['tasks_completed']) > 1 ? "s" : "");
                foreach ($completion_data['tasks_completed'] as $x => $t_name) {
                    if ($x > 0) {
                        if ($x == sizeof($completion_data['tasks_completed']) - 1) {
                            $task_display .= " and ";
                        } else {
                            $task_display .= ", ";
                        }
                    }
                    $task_display .= "$t_name";
                }
                $task_display .= " earning " . Currency::MONEY_SYMBOL . $completion_data['money_gain'] . " and $rep_gain Reputation.";

                $this->system->message($task_display);
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
        $result = $this->system->db->query("SELECT `user_name` FROM `users` WHERE `user_id`='$this->spouse' LIMIT 1");
        if($this->system->db->last_num_rows) {
            $this->spouse_name = $this->system->db->fetch($result)['user_name'];
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

            // Scale Jutsu Power
            foreach ($this->bloodline->jutsu as $jutsu) {
                $rank_diff = $this->rank_num - $jutsu->rank;
                switch ($jutsu->rank) {
                    case 2:
                        // based on scale 2.5 -> 3.5
                        $factor = 0.2;
                        break;
                    case 3:
                        // based on scale 3.5 -> 4.4
                        $factor = 0.1285;
                        break;
                    default:
                        $factor = 0;
                        break;
                }
                $jutsu->power *= 1 + ($rank_diff * $factor);
            }

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

    	$this->village_rep = $user_data['village_rep'];
        $this->weekly_rep = $user_data['weekly_rep'];
    	$this->pvp_rep = $user_data['pvp_rep'];
        $this->mission_rep_cd = $user_data['mission_rep_cd'];
        $this->recent_players_killed_ids = $user_data['recent_players_killed_ids'];
    	$this->recent_killer_ids = $user_data['recent_killer_ids'];
        $this->reputation = new UserReputation($this->village_rep, $this->weekly_rep, $this->pvp_rep, $this->recent_players_killed_ids, $this->recent_killer_ids, $this->mission_rep_cd, $this->system->event, $this->forbidden_seal);

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
            // Array values to undo the "first" "second" etc keys
            $this->elements = array_values(
                json_decode(
                    $user_data['elements'] ?? "[]",
                    true
                )
            );
        }
        else {
            $this->elements = [];
        }

        // Regen/time-based events
        $time_difference = time() - $this->last_update;
        if($time_difference > 60 && $UPDATE >= User::UPDATE_REGEN) {
            $minutes = floor($time_difference / 60);

            $regen_amount = $minutes * ($this->regen_rate + $this->regen_boost);
            $health_multiplier = self::$HEAL_REGEN_MULTIPLIER[$this->rank_num];

            // In-battle decrease
            if($this->battle_id) {
                $regen_amount -= round($regen_amount * 0.7, 1);
                $health_multiplier = 2;
            }

            $this->health += $regen_amount * $health_multiplier;
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
        if($this->stat_transfer_completion_time && $UPDATE >= User::UPDATE_FULL) {
            $this->checkStatTransfer();
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

        $this->achievements = AchievementsManager::fetchPlayerAchievements($this->system, $this->user_id);
        $this->achievements_in_progress = AchievementsManager::fetchPlayerAchievementsInProgress($this->system, $this->user_id);

        // Load training manager
        $this->loadTrainingManager();

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
                $this->system->db->query(
                    "UPDATE `users` SET `ban_data`='{$ban_data}' WHERE `user_id`='{$this->user_id}' LIMIT 1"
                );
                //Return ban message expiry message for display
                if ($this->system->db->last_affected_rows) {
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

    public function loadTrainingManager(): void
    {
        $this->trainingManager = new TrainingManager($this->system, $this->train_type, $this->train_gain,
    $this->train_time, $this->rank, $this->forbidden_seal, $this->reputation, $this->team, $this->clan, $this->sensei_id,
            $this->bloodline_id);
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
     * @return null|array
     */
    public function getOfficialWarning($id): ?array {
        $result = $this->system->db->query(
            "SELECT * FROM `official_warnings` WHERE `user_id`='{$this->user_id}' AND `warning_id`='{$id}' LIMIT 1"
        );
        $warning = $this->system->db->fetch($result);

        if($warning != null) {
            $this->system->db->query("UPDATE `official_warnings` SET `viewed`=1 WHERE `warning_id`='{$id}' LIMIT 1");
            return $warning;
        }
        else {
            return null;
        }
    }

    public function getOfficialWarnings($for_notification = false) {
        $query = "SELECT * FROM `official_warnings` WHERE `user_id`='{$this->user_id}'";
        if($for_notification) {
            $query .= " AND `viewed`=0";
        }
        $query .= " ORDER BY `time` DESC";

        $result = $this->system->db->query($query);
        if($this->system->db->last_num_rows) {
            if($for_notification) {
                return true;
            }
            else {
                $warnings = [];
                while($warning = $this->system->db->fetch($result)) {
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
        $result = $this->system->db->query("SELECT * FROM `user_inventory` WHERE `user_id` = '{$this->user_id}'");

        $player_jutsu = [];
        $player_item_inventory = [];
        $equipped_jutsu = [];
        $equipped_items = [];
        $this->special_items = [];

        // Decode JSON of inventory into variables
        if($this->system->db->last_num_rows > 0) {
            $user_inventory = $this->system->db->fetch($result);
            $player_jutsu = json_decode($user_inventory['jutsu'], true);
            $player_item_inventory = json_decode($user_inventory['items'], true);
            $equipped_jutsu = json_decode($user_inventory['equipped_jutsu']);
            $equipped_items = json_decode($user_inventory['equipped_items']);
        }
        else {
            $this->system->db->query(
                "INSERT INTO `user_inventory` (`user_id`, `items`, `bloodline_jutsu`, `jutsu`)
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

            $result = $this->system->db->query(
                "SELECT * FROM `jutsu` WHERE `jutsu_id` IN ({$player_jutsu_string})
				AND `purchase_type` != '1' AND `rank` <= '{$this->rank_num}'"
            );
            if($this->system->db->last_num_rows > 0) {
                while($jutsu_data = $this->system->db->fetch($result)) {
                    $jutsu_id = $jutsu_data['jutsu_id'];

                    // Scale event jutsu
                    if ($jutsu_data['purchase_type'] == Jutsu::PURCHASE_TYPE_EVENT_SHOP) {
                        if ($this->rank_num == 3) {
                            $jutsu_data['rank'] = 3;
                            $jutsu_data['use_cost'] *= 2;
                            $jutsu_data['power'] *= Jutsu::CHUUNIN_SCALE_MULTIPLIER;
                        }
                        else if ($this->rank_num == 4) {
                            $jutsu_data['rank'] = 4;
                            $jutsu_data['use_cost'] *= 3;
                            $jutsu_data['power'] *= Jutsu::JONIN_SCALE_MULTIPLIER;
                        }
                    }

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

            $result = $this->system->db->query("SELECT * FROM `items` WHERE `item_id` IN ({$player_items_string})");
            if($this->system->db->last_num_rows > 0) {
                while($item_data = $this->system->db->fetch($result)) {
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
     * @throws RuntimeException
     */
    public function checkTraining(): void {
        // Used for sidemenu display
        if($this->train_time < time()) {
            $team_boost_description = "";

            // Bloodline Jutsu training
            // Jutsu training
            if (str_contains($this->train_type, 'bloodline_jutsu:')) {
                $jutsu_id = $this->train_gain;
                $this->getInventory();

                $gain = User::$jutsu_train_gain;
                if ($this->system->TRAIN_BOOST) {
                    $gain += $this->system->TRAIN_BOOST;
                }
                if ($this->bloodline->jutsu[$jutsu_id]->level + $gain > 100) {
                    $gain = 100 - $this->bloodline->jutsu[$jutsu_id]->level;
                }

                // Daily task
                if($this->daily_tasks->hasTaskType(DailyTask::ACTIVITY_TRAINING)) {
                    $this->daily_tasks->progressTask(DailyTask::ACTIVITY_TRAINING, $gain, DailyTask::SUB_TASK_JUTSU);
                }

	    	    if ($this->bloodline->jutsu[$jutsu_id]->level < 100) {
                    $new_level = $this->bloodline->jutsu[$jutsu_id]->level + $gain;

                    if ($new_level > 100) {
                        $this->bloodline->jutsu[$jutsu_id]->level = 100;
                    }
                    else {
                        $this->bloodline->jutsu[$jutsu_id]->level += $gain;
                    }
                    $message = $this->bloodline->jutsu[$jutsu_id]->name . " has increased to level " .
                        $this->bloodline->jutsu[$jutsu_id]->level . '.';

                    $jutsu_skill_type = $this->bloodline->jutsu[$jutsu_id]->jutsu_type . '_skill';
                    if ($this->total_stats < $this->rank->stat_cap) {
                        $this->{$jutsu_skill_type}++;
                        $this->exp += 10;
                        $message .= ' You have gained 1 ' . ucwords(str_replace('_', ' ', $jutsu_skill_type)) .
                        ' and 10 experience.';
                    }

                    // Create notification
                    $new_notification = new NotificationDto(
                        type: "training_complete",
                        message: "Training " . $this->bloodline->jutsu[$jutsu_id]->name . " Complete",
                        user_id: $this->user_id,
                        created: time(),
                        alert: true,
                    );
                    NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_UNIQUE);

                    $this->system->message($message);
                    $this->system->printMessage();

                    if (!$this->ban_type) {
                        $this->updateInventory();
                    }
		        }

                $this->train_time = 0;
            }
            else if(str_contains($this->train_type, 'jutsu:')) {
                $jutsu_id = $this->train_gain;
                $this->getInventory();

                $gain = User::$jutsu_train_gain;
                if($this->system->TRAIN_BOOST) {
                    $gain += $this->system->TRAIN_BOOST;
                }
                if($this->jutsu[$jutsu_id]->level + $gain > 100) {
                    $gain = 100 - $this->jutsu[$jutsu_id]->level;
                }

                // Daily task
                if($this->daily_tasks->hasTaskType(DailyTask::ACTIVITY_TRAINING)) {
                    $this->daily_tasks->progressTask(DailyTask::ACTIVITY_TRAINING, $gain, DailyTask::SUB_TASK_JUTSU);
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

                        // Create notification
                        $new_notification = new NotificationDto(
                            type: "training_complete",
                            message: "Training " . $this->jutsu[$jutsu_id]->name . " Complete",
                            user_id: $this->user_id,
                            created: time(),
                            alert: true,
                        );
                        NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_UNIQUE);

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

                // Daily task
                if($this->daily_tasks->hasTaskType(DailyTask::ACTIVITY_TRAINING)) {
                    $sub_task_type = (str_contains($this->train_type, 'skill')) ? DailyTask::SUB_TASK_SKILL : DailyTask::SUB_TASK_ATTRIBUTES;
                    $this->daily_tasks->progressTask(DailyTask::ACTIVITY_TRAINING, $this->train_gain, $sub_task_type);
                }

                $this->train_time = 0;
                if($gain_description) {
                    $this->system->message($gain_description . '.' . $team_boost_description);
                }
                else if($this->total_stats >= $this->rank->stat_cap) {
                    $this->system->message("Training has finished but you cannot gain any more stats!");
                }

                // Create notification
                $new_notification = new NotificationDto(
                    type: "training_complete",
                    message: str_replace(["<br />", "<b>", "</b>"], " ", $gain_description . '.' . $team_boost_description),
                    user_id: $this->user_id,
                    created: time(),
                    alert: true,
                );
                NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_UNIQUE);
            }
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    public function checkStatTransfer(): void {
        if($this->stat_transfer_completion_time < time()) {
            if(!in_array($this->stat_transfer_target_stat, $this->stats)) {
                $this->system->message("Transferring an invalid stat: {$this->stat_transfer_target_stat}. Transfer cancelled.");
                $this->system->log(
                    'invalid_stat_transfer',
                    $this->user_id,
                    "Stat: {$this->stat_transfer_target_stat} / Amount: $this->stat_transfer_amount"
                );
                $this->stat_transfer_completion_time = 0;
                return;
            }

            // Check caps
            $gain_description = $this->addStatGain($this->stat_transfer_target_stat, $this->stat_transfer_amount);

            $this->stat_transfer_completion_time = 0;
            if($gain_description) {
                $this->system->message($gain_description);
            }
            else if($this->total_stats >= $this->rank->stat_cap) {
                $this->system->message("Transfer has finished but you cannot gain any more stats!");
            }

            // Create notification
/*            $new_notification = new NotificationDto(
                type: "training_complete",
                message: str_replace(["<br />", "<b>", "</b>"], " ", $gain_description . '.' . $team_boost_description),
                user_id: $this->user_id,
                created: time(),
                alert: true,
            );
            NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_UNIQUE);*/
        }
    }

    /**
     * @param string $stat
     * @param int    $stat_gain
     * @return string
     * @throws RuntimeException
     */
    public function addStatGain(string $stat, int $stat_gain): string {
        if(!in_array($stat, $this->stats)) {
            throw new RuntimeException("Invalid stat!");
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
    public function itemQuantity(int $item_id): int {
        return isset($this->items[$item_id]) ? $this->items[$item_id]->quantity : 0;
    }

    public function giveItem(Item $item, int $quantity = 1): void {
        if ($this->hasItem($item->id)) {
            $this->items[$item->id]->quantity += $quantity;
        } else {
            $this->items[$item->id] = $item;
            $this->items[$item->id]->quantity = $quantity;
        }

        if(isset(LanternEvent::$max_item_quantities[$item->id])) {
            $this->items[$item->id]->quantity = min($this->items[$item->id]->quantity, LanternEvent::$max_item_quantities[$item->id]);
        }

        if($item->use_type == Item::USE_TYPE_WEAPON || $item->use_type == Item::USE_TYPE_ARMOR) {
            $item->quantity = 1;
        }

        AchievementsManager::handleItemAcquired($this->system, $this, $item);
    }

    public function giveItemById(int $item_id, int $quantity = 1): void {
        if ($this->hasItem($item_id)) {
            $this->giveItem($this->items[$item_id], $quantity);
        }
        else {
            $result = $this->system->db->query(
                "SELECT * FROM `items` WHERE `item_id` = {$item_id}"
            );
            $item = Item::fromDb($this->system->db->fetch($result));

            $this->giveItem($item, $quantity);
        }
    }

    public function removeItemById(int $item_id, int $quantity = 1): void {
        if(!$this->hasItem($item_id)) {
            return;
        }

        $item = $this->items[$item_id];

        $this->items[$item->id]->quantity -= $quantity;
        if ($this->items[$item->id]->quantity < 1) {
            unset($this->items[$item->id]);
        }
    }

    /**
     * @param Jutsu $jutsu
     * @param float $resource_cost_multiplier
     * @return ActionResult
     */
    public function useJutsu(Jutsu $jutsu, float $resource_cost_multiplier = 1.0): ActionResult {
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

        $jutsu_use_cost = ceil($jutsu->use_cost * $resource_cost_multiplier);

        if($this->{$energy_type} < $jutsu_use_cost) {
            return ActionResult::failed("You do not have enough $energy_type!");
        }

        switch($jutsu->purchase_type) {
            case Jutsu::PURCHASE_TYPE_PURCHASABLE:
            case Jutsu::PURCHASE_TYPE_EVENT_SHOP:
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
                        $levels_gained = floor($this->jutsu[$jutsu->id]->exp / 1000);
                        $this->jutsu[$jutsu->id]->exp -= $levels_gained * 1000;
                        $this->jutsu[$jutsu->id]->level += $levels_gained;
                        if($this->daily_tasks->hasTaskType(DailyTask::ACTIVITY_TRAINING)) {
                            $this->daily_tasks->progressTask(DailyTask::ACTIVITY_TRAINING, $levels_gained, DailyTask::SUB_TASK_JUTSU);
                        }
                        $this->system->message($jutsu->name . " has increased to level " . $this->jutsu[$jutsu->id]->level . ".");
                    }
                }

                $this->{$energy_type} -= $jutsu_use_cost;
                break;
            case Jutsu::PURCHASE_TYPE_BLOODLINE:
                if($this->bloodline->jutsu[$jutsu->id]->level < 100) {
                    $this->bloodline->jutsu[$jutsu->id]->exp += round(500 / ($this->bloodline->jutsu[$jutsu->id]->level * 0.9));

                    if($this->bloodline->jutsu[$jutsu->id]->exp >= 1000) {
                        $levels_gained = floor($this->bloodline->jutsu[$jutsu->id]->exp / 1000);
                        $this->bloodline->jutsu[$jutsu->id]->exp -= $levels_gained * 1000;
                        $this->bloodline->jutsu[$jutsu->id]->level += $levels_gained;
                        if($this->daily_tasks->hasTaskType(DailyTask::ACTIVITY_TRAINING)) {
                            $this->daily_tasks->progressTask(DailyTask::ACTIVITY_TRAINING, $levels_gained, DailyTask::SUB_TASK_JUTSU);
                        }
                        $this->system->message($jutsu->name . " has increased to level " . $this->bloodline->jutsu[$jutsu->id]->level . ".");
                    }
                }

                $this->{$energy_type} -= $jutsu_use_cost;
                break;
            case Jutsu::PURCHASE_TYPE_DEFAULT:
                $this->{$energy_type} -= $jutsu_use_cost;
                break;

            default:
                return ActionResult::failed("Invalid jutsu type!");
        }

        return ActionResult::succeeded();
    }

    /**
     * @throws RuntimeException
     */
    public function calcPlayerMoneyGain(int $multiplier = 1, $multiple_of = 10): int {
        return self::calcMoneyGain($this->rank_num, $multiplier, $multiple_of);
    }
    public static function calcMoneyGain(int $rank_num, int $multiplier = 1, $multiple_of = 10): float {
        if ($multiplier < 1) {
            $multiplier = 1;
        }
        if($multiplier > 10) {
            $multiplier = 10;
        }
        $gain = ceil(pow($rank_num+2, 3) * $multiplier);
        $gain = ceil(((30 * $rank_num) + pow($rank_num+1, 2)) * $multiplier);

        return $gain + ($multiple_of - $gain % $multiple_of);
    }

    /* function moteToVillage()
        moves user to village */
    public function moveToVillage() {
        $this->location = $this->village_location;
    }

    public function checkAchievementCompletion() {
        AchievementsManager::checkForCompletedAchievements($this->system, $this);
    }

    /* function updateData()
        Updates user data from class members into database
        -Parameters-
    */
    public function updateData() {
        if($this->read_only) {
            throw new RuntimeException("Cannot update, User is loaded as read-only!");
        }

        // Check achievements
        $this->checkAchievementCompletion();

        /** @noinspection SqlWithoutWhere */
        $query = "UPDATE `users` SET
		`current_ip` = '$this->current_ip',
		`last_ip` = '$this->last_ip',
		`failed_logins` = '$this->failed_logins',
		`last_malicious_ip` = '$this->last_malicious_ip',
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
		`village_rep` = '$this->village_rep',
		`weekly_rep` = '$this->weekly_rep',
  		`pvp_rep` = '$this->pvp_rep',
		`recent_players_killed_ids` = '$this->recent_players_killed_ids',
		`recent_killer_ids` = '$this->recent_killer_ids',
		`mission_rep_cd` = '$this->mission_rep_cd',
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
        `stat_transfer_amount` = $this->stat_transfer_amount,
        `stat_transfer_completion_time` = $this->stat_transfer_completion_time,
        `stat_transfer_target_stat` = '$this->stat_transfer_target_stat',
		`money` = '{$this->money->getAmount()}',
		`premium_credits` = '{$this->premium_credits->getAmount()}',
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
        $this->system->db->query($query);

        // Update Blacklist
        if(count($this->blacklist) != count($this->original_blacklist)) {
            $blacklist_json = json_encode($this->blacklist);
            $this->system->db->query(
                "UPDATE `blacklist` SET `blocked_ids`='{$blacklist_json}' WHERE `user_id`='{$this->user_id}' LIMIT 1"
            );
        }

        //Update Daily Tasks
        if($this->daily_tasks->tasks) {
            $this->daily_tasks->update();
        }
    }

    public function updateLastActive() {
        $this->system->db->query("UPDATE `users` SET `last_active` = '" . time() . "' WHERE `user_id`={$this->user_id} LIMIT 1");
    }

    /**
     * @throws RuntimeException
     */
    public function updateInventory(): bool {
        if(!$this->inventory_loaded) {
            throw new RuntimeException("Called update without fetching inventory!");
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

        if(!empty($this->items)) {
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

        $this->system->db->query(
            "UPDATE `user_inventory` SET
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

            $this->system->db->query(
                "UPDATE `user_bloodlines` SET `jutsu` = '{$bloodline_jutsu_json}'
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

        // Arthesia override
        if($this->system->environment == System::ENVIRONMENT_PROD && $this->user_id == 1603) {
            $return['purple'] = 'contentAdmin';
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

        $dbDateTime = Database::currentDatetimeForDb();
        $this->system->db->query(
            "INSERT INTO `player_logs`
                (`user_id`, `user_name`, `log_type`, `log_time`,
                 `log_contents`)
                VALUES
                ({$this->user_id}, '{$this->user_name}', '{$log_type}', '{$dbDateTime}',
                 '{$this->system->db->clean($log_contents)}'
                )
            "
        );

        return true;
    }

    /**
     * @param string $entity_id
     * @return User
     * @throws RuntimeException
     */
    public static function fromEntityId(System $system, string $entity_id): User {
        $entity_id = System::parseEntityId($entity_id);

        if($entity_id->entity_type != self::ENTITY_TYPE) {
            throw new RuntimeException("Entity ID is not a User!");
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

            'ninjutsu_skill' => 0,
            'genjutsu_skill' => 0,
            'taijutsu_skill' => 0,
            'bloodline_skill' => 0,

            'cast_speed' => 0,
            'speed' => 0,
            'intelligence' => 0,
            'willpower' => 0,

            'register_date' => time(),
            'verify_key' => $verification_code,
            'user_verified' => 1, // TEMP FIX
            'layout' => System::DEFAULT_LAYOUT,
            'avatar_link' => mt_rand(1, 100) > 50
                ? './images/default_avatar_v2_blue.png'
                : './images/default_avatar_v2_red.png',

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
            'sensei_id' => 0
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
        $system->db->query($query);

        return $system->db->last_insert_id;
    }

    /* User Settings */

    // TO-DO: Full user settings GET, assign to user class variables
    public function setAvatarStyle(string $style): bool {
        $this->system->db->query(
            "INSERT INTO `user_settings` (`user_id`, `avatar_style`)
                VALUES ({$this->user_id}, '{$style}')
                ON DUPLICATE KEY UPDATE `avatar_style`='{$style}';"
        );

        return ($this->system->db->last_affected_rows > 0);
    }
    public function setAvatarFrame(string $frame): bool {
        $this->system->db->query(
            "INSERT INTO `user_settings` (`user_id`, `avatar_frame`)
                VALUES ({$this->user_id}, '{$frame}')
                ON DUPLICATE KEY UPDATE `avatar_frame`='{$frame}';"
        );

        return ($this->system->db->last_affected_rows > 0);
    }
    public function setSidebarPosition(string $position): bool {
        $this->system->db->query(
            "INSERT INTO `user_settings` (`user_id`, `sidebar_position`)
                VALUES ({$this->user_id}, '{$position}')
                ON DUPLICATE KEY UPDATE `sidebar_position`='{$position}';"
        );

        return ($this->system->db->last_affected_rows > 0);
    }
    public function setEnableAlerts(bool $enable): bool {
        $this->system->db->query(
            "INSERT INTO `user_settings` (`user_id`, `enable_alerts`)
                VALUES ({$this->user_id}, '{$enable}')
                ON DUPLICATE KEY UPDATE `enable_alerts`='{$enable}';"
        );

        return ($this->system->db->last_affected_rows > 0);
    }

    public function setCardImage(string $image): bool
    {
        $this->system->db->query(
            "INSERT INTO `user_settings` (`user_id`, `card_link`)
                VALUES ({$this->user_id}, '{$image}')
                ON DUPLICATE KEY UPDATE `card_image`='{$image}';"
        );

        return ($this->system->db->last_affected_rows > 0);
    }
    public function setBannerImage(string $image): bool
    {
        $this->system->db->query(
            "INSERT INTO `user_settings` (`user_id`, `banner_link`)
                VALUES ({$this->user_id}, '{$image}')
                ON DUPLICATE KEY UPDATE `banner_image`='{$image}';"
        );

        return ($this->system->db->last_affected_rows > 0);
    }


    // TO-DO: Replace with user class variables
    public function getAvatarStyle(): string
    {
        $avatar_result = $this->system->db->query(
            "SELECT `avatar_style` FROM `user_settings` WHERE `user_id` = {$this->user_id}"
        );
        $result = $this->system->db->fetch($avatar_result);
        if ($result) {
            if (!array_key_exists($result['avatar_style'], $this->forbidden_seal->avatar_styles)) {
                return "round";
            }
            return $result['avatar_style'];
        }
        return "round";
    }
    public function getAvatarFrame(): string {
        $avatar_result = $this->system->db->query(
            "SELECT `avatar_frame` FROM `user_settings` WHERE `user_id` = {$this->user_id}"
        );
        $result = $this->system->db->fetch($avatar_result);
        if ($result) {
            return $result['avatar_frame'];
        }
        return "default";
    }
    public function getSidebarPosition(): string
    {
        $avatar_result = $this->system->db->query(
            "SELECT `sidebar_position` FROM `user_settings` WHERE `user_id` = {$this->user_id}"
        );
        $result = $this->system->db->fetch($avatar_result);
        if ($result) {
            return $result['sidebar_position'];
        }
        return "left";
    }
    public function getEnableAlerts(): bool
    {
        $alerts_result = $this->system->db->query(
            "SELECT `enable_alerts` FROM `user_settings` WHERE `user_id` = {$this->user_id}"
        );
        $result = $this->system->db->fetch($alerts_result);
        if ($result) {
            return $result['enable_alerts'];
        }
        return false;
    }

    public function getCardImage(): string {
        $card_result = $this->system->db->query(
            "SELECT `card_image` FROM `user_settings` WHERE `user_id` = {$this->user_id}"
        );
        $result = $this->system->db->fetch($card_result);
        if ($result) {
            return $result['card_image'];
        }
        return "./images/default_avatar.png";
    }
    public function getBannerImage(): string {
        $banner_result = $this->system->db->query(
            "SELECT `banner_image` FROM `user_settings` WHERE `user_id` = {$this->user_id}"
        );
        $result = $this->system->db->fetch($banner_result);
        if ($result) {
            return $result['banner_image'];
        }
        return "./images/default_avatar.png";
    }

    public function nextLevelProgressPercent(): int {
        $exp_needed = $this->expForNextLevel();

        $incremental_exp_needed = $exp_needed - $this->exp;

        $progress_percent =
            (($this->rank->exp_per_level - $incremental_exp_needed)
            / $this->rank->exp_per_level)
            * 100;
        if($progress_percent < 0) {
            $progress_percent = 0;
        }
        else if($progress_percent > 100) {
            $progress_percent = 100;
        }

        return $progress_percent;
    }
}
