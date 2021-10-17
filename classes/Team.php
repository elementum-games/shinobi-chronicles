<?php

class Team {
    const TYPE_SHINOBI = 1;
    const MAX_MEMBERS = 4;

    const MIN_NAME_LENGTH = 5;
    const MAX_NAME_LENGTH = 35;

    const BOOST_TRAINING = 'training';
    const BOOST_AI_MONEY = 'ai_money';

    public static array $allowed_boosts = [
        Team::BOOST_TRAINING => [
            'small' => [
                'amount' => 10,
                'points_cost' => 25,
            ],
            'medium' => [
                'amount' => 20,
                'points_cost' => 50,
            ],
            'large' => [
                'amount' => 30,
                'points_cost' => 100,
            ],
        ],
        Team::BOOST_AI_MONEY => [
            'small' => [
                'amount' => 10,
                'points_cost' => 25,
            ],
            'medium' => [
                'amount' => 15,
                'points_cost' => 50,
            ],
            'large' => [
                'amount' => 20,
                'points_cost' => 100,
            ],
        ],
    ];

    public System $system;
    public int $id;

    public bool $initialized = false;

    public string $name;
    public string $village;
    public string $type;

    public string $boost;
    public int $boost_amount;
    public int $boost_time;

    public int $points;
    public int $monthly_points;

    // TODO: Rename to $leader_id
    public int $leader;
    // TODO: Rename to $member_ids
    public array $members;

    public int $mission_id;
    public ?array $mission_stage;
    public string $logo;

    /**
     * Team constructor.
     * @param System $system
     * @param int    $team_id
     */
    public function __construct(System $system, int $team_id) {
        $this->system = $system;
        $this->id = $team_id;
    }

    public function init($data) {
        $this->name = $data['name'];
        $this->village = $data['village'];
        $this->type = $data['type'];

        $this->boost = $data['boost'];
        $this->boost_amount = $data['boost_amount'];
        $this->boost_time = $data['boost_time'];

        $this->points = $data['points'];
        $this->monthly_points = $data['monthly_points'];
        $this->leader = $data['leader'];
        $this->members = json_decode($data['members'], true);
        $this->mission_id = $data['mission_id'];
        $this->mission_stage = json_decode($data['mission_stage'], true);
        $this->logo = $data['logo'];
        
        
        // check if boosts have expired.
        $seven_days = 60*60*24*7;
        if($this->boost_time + $seven_days <= time()) {
            $this->boost = 'none';
            $this->boost_amount = 0;
        }

        $this->initialized = true;
    }

    public static function create(System $system, string $name, $village, int $founder_id): bool {
        $query = "INSERT INTO `teams` 
				(`name`, `type`, `village`, 
				 `boost`, `boost_amount`, `boost_time`, 
				 `points`, `monthly_points`, 
				 `leader`, `members`, 
				 `mission_id`, `logo`) 
				 VALUES
				('{$name}', " . Team::TYPE_SHINOBI . ", '{$village}', 
				 'none', 0, 0, 
				 0, 0, 
				 '{$founder_id}', '[{$founder_id},0,0,0]', 
				 0, './images/default_avatar.png')";
        $system->query($query);

        return $system->db_last_affected_rows == 1;
    }

    public function getDefenseBoost(User $player): float {
        $result = $this->system->query("SELECT COUNT(`user_id`) as `count` FROM `users`
                    WHERE `team_id`='{$this->id}' AND `location`='$player->location' AND `last_active` > UNIX_TIMESTAMP() - 120");
        $location_count = $this->system->db_fetch($result)['count'];
        
        return (($location_count - 1) * 0.05);
    }

    public function checkForTrainingBoostTrigger(): ?float {
        $boost_amount = null;
        if($this->boost == Team::BOOST_TRAINING) {
            $random_number = mt_rand(1, 100);
            if ($random_number <= $this->boost_amount) {
                // boost success
                // a "flat" 20% increase in stat gain
                $boost_amount = 0.2;
            }
        }
        return $boost_amount;
    }

    public function getAIMoneyBoostAmount(): ?float {
        if($this->boost == Team::BOOST_AI_MONEY) {
            return $this->boost_amount / 100;
        }
        else {
            return null;
        }
    }

    public function getBoostLabel(): string {
        return ucwords(str_replace('_', ' ', $this->boost));
    }

    public function addPoints($point_gain) {
        $this->points += $point_gain;
        $this->monthly_points += $point_gain;

        $this->system->query("UPDATE `teams` SET `points`=`points`+'$point_gain', `monthly_points`=`monthly_points`+'$point_gain'  
        WHERE `team_id`={$this->id} LIMIT 1");
    }

    /**
     * @param User $player
     */
    public function addMember(User $player) {
        if($this->village != $player->village) {
            throw new Exception("You must be in the same village to join a team!");
        }

        $first_open_slot = array_search(0, $this->members);
        if($first_open_slot === false) {
            throw new Exception("No open slot available!");
        }

        $this->members[$first_open_slot] = $player->user_id;

        $this->system->query("UPDATE `teams` 
            SET `members`='" . json_encode($this->members) . "'
            WHERE `team_id`='{$this->id}'");

        if($this->system->db_last_affected_rows === 1) {
            $player->team = $this;
            $player->team_invite = 0;
        }
        else {
            throw new Exception("Error adding you to the team!");
        }
    }

    /**
     * @param string $boost_type
     * @param string $boost_size
     * @throws Exception
     */
    public function setBoost(string $boost_type, string $boost_size) {
        $boost = self::$allowed_boosts[$boost_type][$boost_size] ?? null;
        if($boost == null) {
            throw new Exception("Invalid boost!");
        }

        if ($boost['points_cost'] > $this->points) {
            throw new Exception('Your team does not have enough points for this boost!');
        }

        $this->points -= $boost['points_cost'];

        $this->system->query("UPDATE `teams` SET 
                    `boost`='{$boost_type}', 
                    `boost_amount`='{$boost['amount']}', 
                    `points`='{$this->points}', 
                    `boost_time`='" . time() . "' 
                    WHERE `team_id`='{$this->id}'");
    }

    /**** STATIC UTILS ****/

    /**
     * @param System $system
     * @param int    $team_id
     * @return array|null
     */
    protected static function findDataById(System $system, int $team_id): ?array {
        $result = $system->query("SELECT * FROM `teams` WHERE `team_id`='{$team_id}' LIMIT 1");
        if($system->db_last_num_rows == 0) {
            return null;
        }

        return $system->db_fetch($result);
    }

    /**
     * @param System $system
     * @param int    $team_id
     * @return Team|null
     */
    public static function findById(System $system, int $team_id): ?Team {
        $data = Team::findDataById($system, $team_id);
        if($data) {
            $team = new Team($system, $team_id);
            $team->init($data);
            return $team;
        }
        else {
            return null;
        }
    }

    /**
     * @return User
     * @throws Exception
     */
    public function fetchLeader(): User {
        $leader = new User($this->leader);
        $leader->loadData(User::UPDATE_NOTHING, true);
        return $leader;
    }
}