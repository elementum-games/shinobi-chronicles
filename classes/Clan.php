<?php

class Clan {
    const OFFICE_LEADER = 1;
    const OFFICE_ELDER_1 = 2;
    const OFFICE_ELDER_2 = 3;

    const LEADER_MAX_INACTIVITY = 7 * 86400;
    const ELDER_MAX_INACTIVITY = 14 * 86400;

    private System $system;

    public int $id;
    public string $village;
    public string $name;
    public bool $bloodline_only;
    
    public string $boost;
    public ?string $boost_type = 'training';
    public ?string $boost_effect = '';
    public float $boost_amount;

    public int $points;

    public int $leader_id;
    public int $elder_1_id;
    public int $elder_2_id;
    public $challenge_1;

    public string $logo_url;
    public string $motto;
    public string $info;

    public static int $BOOST_AMOUNT = 10;
    public static int $BOOST_COST = 75;

    // Order here is so they show up like this on the clan page
    public static array $offices = [Clan::OFFICE_ELDER_1, Clan::OFFICE_LEADER, Clan::OFFICE_ELDER_2];
    public static array $office_labels = [
        Clan::OFFICE_LEADER => "Leader",
        Clan::OFFICE_ELDER_1 => "Elder 1",
        Clan::OFFICE_ELDER_2 => "Elder 2",
    ];

    public function __construct(
        System $system, 
        int $id,
        string $village,
        string $name,
        bool $bloodline_only,
    
        string $boost,

        float $boost_amount,
        int $points,
    
        int $leader_id,
        int $elder_1_id,
        $elder_2_id,
        $challenge_1,
    
        string $logo_url,
        string $motto,
        string $info,
    ) {
        $this->system = $system;
        $this->id = $id;

        $this->village = $village;
        $this->name = $name;
        $this->bloodline_only = $bloodline_only;
    
        $this->boost = $boost;
        $this->boost_type = explode(':', $boost)[0] ?? null;
        $this->boost_effect = explode(':', $boost)[1] ?? null;
        $this->boost_amount = $boost_amount;

        $this->points = $points;
    
        $this->leader_id = $leader_id;
        $this->elder_1_id = $elder_1_id;
        $this->elder_2_id = $elder_2_id;
        $this->challenge_1 = $challenge_1;
    
        $this->logo_url = $logo_url;
        $this->motto = $motto;
        $this->info = $info;
    }

    /**
     * @throws Exception
     */
    public static function loadFromId(System $system, int $id): ?Clan {
        $result = $system->query("SELECT * FROM `clans` WHERE `clan_id`=$id LIMIT 1");
        if(!$result) {
            return null;
        }
        
        $clan_data = $system->db_fetch($result);
        
        return new Clan(
            system: $system,
            id: $clan_data['clan_id'],
            village: $clan_data['village'],
            name: $clan_data['name'],
            bloodline_only: $clan_data['bloodline_only'],

            boost: $clan_data['boost'],

            boost_amount: $clan_data['boost_amount'],
            points: $clan_data['points'],

            leader_id: $clan_data['leader'],
            elder_1_id: $clan_data['elder_1'],
            elder_2_id: $clan_data['elder_2'],
            challenge_1: $clan_data['challenge_1'],

            logo_url: $clan_data['logo'],
            motto: $clan_data['motto'],
            info: $clan_data['info'],
        );
    } 

    public function getClanMissions(int $player_rank_num): array {
        $max_mission_rank = Mission::maxMissionRank($player_rank_num);

        $result = $this->system->query(
            "SELECT `mission_id`, `name`, `rank` FROM `missions` 
                WHERE `mission_type`=" . Mission::TYPE_CLAN . " AND `rank` <= $max_mission_rank");
        $missions = array();

        while($row = $this->system->db_fetch($result)) {
            $missions[$row['mission_id']] = $row;
        }

        return $missions;
    }

    public function getTrainingBoostOptions(): array {
        $training_boosts = array(
            'ninjutsu_skill',
            'taijutsu_skill',
            'genjutsu_skill',
            'bloodline_skill',
            'cast_speed',
            'speed',
            'intelligence',
            'willpower',
            'jutsu',
        );
        $boost = explode(':', $this->boost);
        $current_boost_id = null;

        if(count($boost) >= 2) {
            $current_boost_id = array_search($boost[1], $training_boosts);
        }
        if($current_boost_id) {
            unset($training_boosts[$current_boost_id]);
        }

        return $training_boosts;
    }

    /**
     * @return ClanMemberDto[]
     */
    public function fetchOfficers(): array {
        $officer_ids = [$this->leader_id, $this->elder_1_id, $this->elder_2_id];
        $officer_ids = array_filter($officer_ids, function($id) {
            return $id !== 0;
        });


        $officers = [];
        if(count($officer_ids) > 0) {
            $query = "SELECT `user_id`, `user_name`, `rank`, `level`, `exp`, `avatar_link`, `clan_office`, `last_active` 
                FROM `users`
                WHERE `user_id` IN (" . implode(',', $officer_ids) . ")";
            $result = $this->system->query($query);

            if($this->system->db_last_num_rows > 0) {
                while($row = $this->system->db_fetch($result)) {
                    $officers[$row['clan_office']] = new ClanMemberDto(
                        id: $row['user_id'],
                        name: $row['user_name'],
                        rank_num: $row['rank'],
                        level: $row['level'],
                        exp: $row['exp'],
                        last_active: $row['last_active'],
                        avatar_link: $row['avatar_link'],
                        clan_office: $row['clan_office']
                    );
                }
            }
        }

        return $officers;
    }

    /**
     * @throws Exception
     */
    public function setBoost(string $new_boost): void {
        $boost_cost = self::$BOOST_COST;
        $boost_amount = self::$BOOST_AMOUNT;

        $training_boosts = $this->getTrainingBoostOptions();

        if(!in_array($new_boost, $training_boosts)) {
            throw new Exception("Invalid boost!");
        }
        if($this->points < $boost_cost) {
            throw new Exception("Not enough points!");
        }

        $new_boost = 'training:' . $new_boost;

        $this->points -= $boost_cost;

        $this->system->query("UPDATE `clans` SET 
            `boost`='$new_boost', 
            `boost_amount`={$boost_amount}, 
            `points`=`points` - {$boost_cost}
        WHERE `clan_id`='{$this->id}' LIMIT 1");

        $this->boost = $new_boost;
        $this->boost_type = explode(':', $new_boost)[0];
        $this->boost_effect = explode(':', $new_boost)[1];
        $this->boost_amount = $boost_amount;

        $this->system->message("Boost updated!");
    }

    /**
     * @throws Exception
     */
    public function startMission(User $player, int $mission_id): bool {
        $missions = $this->getClanMissions($player->rank_num);

        if(!isset($missions[$mission_id])) {
            throw new Exception("Invalid mission!");
        }
        Mission::start($player, $mission_id);
        $player->log(User::LOG_MISSION, "Clan Mission ID #{$mission_id}");

        return true;
    }

    /**
     * @param User $player
     * @param int  $office
     * @return void
     * @throws Exception
     */
    public function challengeForOffice(User $player, int $office): bool {
        if($player->rank_num < 4 && $office == Clan::OFFICE_LEADER) {
            throw new Exception("Unable to claim leader position at this rank.");
        }
        if($player->clan_office == $office) {
            throw new Exception("You cannot challenge yourself!");
        }
        if($player->clan_office) {
            throw new Exception("Please resign from your current position before taking a new one!");
        }

        // Check cooldown
        $officers = $this->fetchOfficers();
        $current_officer = $officers[$office] ?? null;

        $min_officer_last_active = $office === Clan::OFFICE_LEADER
            ? time() - Clan::LEADER_MAX_INACTIVITY
            : time() - Clan::ELDER_MAX_INACTIVITY;

        if($current_officer != null && $current_officer->last_active > $min_officer_last_active) {
            throw new Exception("Position already taken!");
        }

        // Claim empty seat
        $new_office_label = self::$office_labels[$office];

        // Update clan data
        /** @noinspection SqlResolve */
        switch($office) {
            case Clan::OFFICE_LEADER:
                $this->system->query("UPDATE `clans` SET `leader`='$player->user_id' WHERE `clan_id`='{$this->id}' LIMIT 1");
                $this->leader_id = $player->user_id;
                break;
            case Clan::OFFICE_ELDER_1:
                $this->system->query("UPDATE `clans` SET `elder_1`='$player->user_id' WHERE `clan_id`='{$this->id}' LIMIT 1");
                $this->elder_1_id = $player->user_id;
                break;
            case Clan::OFFICE_ELDER_2:
                $this->system->query("UPDATE `clans` SET `elder_2`='$player->user_id' WHERE `clan_id`='{$this->id}' LIMIT 1");
                $this->elder_2_id = $player->user_id;
                break;
            default:
                throw new Exception("Invalid office!");
        }

        $player->clan_office = $office;

        $this->system->message(
            "You have claimed the clan {$player->clan->name} {$new_office_label} position!"
        );
        return true;
    }

    /**
     * @param string $info
     * @return void
     * @throws Exception
     */
    public function setInfo(string $info): void {
        if(strlen($info) > 700) {
            throw new Exception("Clan info is too long!");
        }

        $this->system->query("UPDATE `clans` SET `info`='$info' WHERE `clan_id`='{$this->id}' LIMIT 1");
        $this->info = $info;

        $this->system->message("Clan info updated!");
    }

    /**
     * @param string $logo_url
     * @return void
     * @throws Exception
     */
    public function setLogoUrl(string $logo_url) {
        if(strlen($logo_url) > 150) {
            throw new Exception("Link is too long!");
        }

        $this->system->query("UPDATE `clans` SET `logo`='$logo_url' WHERE `clan_id`='{$this->id}' LIMIT 1");
        $this->logo_url = $logo_url;
        $this->system->message("Logo updated!");
    }

    /**
     * @throws Exception
     */
    public function setMotto(string $motto): void {
        if(strlen($motto) > 180) {
            throw new Exception("Motto is too long!");
        }

        $this->system->query("UPDATE `clans` SET `motto`='$motto' WHERE `clan_id`='{$this->id}' LIMIT 1");
        $this->motto = $motto;

        $this->system->message("Motto updated!");
    }

    /**
     * @param User $player
     * @return void
     */
    public function resignOffice(User $player): void {
        $office = $player->clan_office;

        switch($player->clan_office) {
            case Clan::OFFICE_LEADER:
                $this->system->query("UPDATE `clans` SET `leader`=0 WHERE `clan_id`='{$this->id}' LIMIT 1");
                $this->leader_id = 0;
                break;
            case Clan::OFFICE_ELDER_1:
                $this->system->query("UPDATE `clans` SET `elder_1`=0 WHERE `clan_id`='{$this->id}' LIMIT 1");
                $this->elder_1_id = 0;
                break;
            case Clan::OFFICE_ELDER_2:
                $this->system->query("UPDATE `clans` SET `elder_2`=0 WHERE `clan_id`='{$this->id}' LIMIT 1");
                $this->elder_2_id = 0;
                break;
        }

        $player->clan_office = 0;
        $this->system->message("You have resigned as Clan {$this->name} " . self::$office_labels[$office] . '.');
    }

}

class ClanMemberDto {
    public function __construct(
        public int $id,
        public string $name,
        public int $rank_num,
        public int $level,
        public int $exp,
        public int $last_active,
        public string $avatar_link = '',
        public int $clan_office = 0
    ) {}
}