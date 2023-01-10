<?php

/* Class:		Bloodline
*/
class Bloodline {
    const SKILL_REDUCTION_ON_CHANGE = 0.1;

    const RANK_ADMIN = 5;
    const RANK_LESSER = 4;
    const RANK_COMMON = 3;
    const RANK_ELITE = 2;
    const RANK_LEGENDARY = 1;

    const BASE_BLOODLINE_SKILL = 100;

    public static array $public_ranks = [
        self::RANK_LEGENDARY => 'Legendary',
        self::RANK_ELITE  => 'Elite',
        self::RANK_COMMON  => 'Common',
        self::RANK_LESSER  => 'Lesser'
    ];

    public int $bloodline_id;
    public int $id;
    public string $name;
    public int $clan_id;
    public int $rank;
    public string $description;

    protected ?string $raw_passive_boosts;
    protected ?string $raw_combat_boosts;

    public array $passive_boosts = [];
    public array $combat_boosts = [];

    /** @var Jutsu[] */
    private array $base_jutsu;
    /** @var Jutsu[] */
    public array $jutsu;

    public function __construct(array $bloodline_data) {
        $this->bloodline_id = $bloodline_data['bloodline_id'];
        // $this->id = 'BL' . $this->user_id;

        $this->name = $bloodline_data['name'];
        $this->clan_id = $bloodline_data['clan_id'];
        $this->rank = $bloodline_data['rank'];

        $this->raw_passive_boosts = $bloodline_data['passive_boosts'];
        $this->raw_combat_boosts = $bloodline_data['combat_boosts'];
        
        $this->base_jutsu = [];
        $this->jutsu = [];
        $jutsu_data = $bloodline_data['jutsu'];
        if($jutsu_data) {
            $jutsu_arr = json_decode($bloodline_data['jutsu'], true);
            foreach($jutsu_arr as $id => $j) {
                $this->base_jutsu[$id] = new Jutsu(
                    id: $id,
                    name: $j['name'],
                    rank: $j['rank'],
                    jutsu_type: $j['jutsu_type'],
                    base_power: $j['power'],
                    effect: $j['effect'],
                    base_effect_amount: $j['effect_amount'] ?? 0,
                    effect_length: $j['effect_length'] ?? 0,
                    description: $j['description'],
                    battle_text: $j['battle_text'],
                    cooldown: $j['cooldown'] ?? 0,
                    use_type: $j['use_type'],
                    use_cost: $j['use_cost'] ?? 0,
                    purchase_cost: $j['purchase_cost'] ?? 0,
                    purchase_type: Jutsu::PURCHASE_TYPE_BLOODLINE,
                    parent_jutsu: $j['parent_jutsu'] ?? 0,
                    element: $j['element'],
                    hand_seals: $j['hand_seals']
                );
                $this->base_jutsu[$id]->is_bloodline = true;
            }

            $this->jutsu = $this->base_jutsu;
        }

        if($this->raw_passive_boosts) {
            $this->passive_boosts = json_decode($this->raw_passive_boosts, true);
        }
        if($this->raw_combat_boosts) {
            $this->combat_boosts = json_decode($this->raw_combat_boosts, true);
        }
    }

    /**
     * @throws Exception
     */
    public static function loadFromId(System $system, int $bloodline_id, ?int $user_id = null): Bloodline {
        if(!$bloodline_id) {
            throw new Exception("Invalid bloodline id!");
        }

        $result = $system->query("SELECT * FROM `bloodlines` WHERE `bloodline_id`='$bloodline_id' LIMIT 1");
        if($system->db_last_num_rows == 0) {
            throw new Exception("Bloodline does not exist!");
        }
        $bloodline_data = $system->db_fetch($result);

        $bloodline = new Bloodline($bloodline_data);

        // Load user-related BL data if relevant
        if($user_id) {
            $result = $system->query("SELECT * FROM `user_bloodlines` WHERE `user_id`=$user_id LIMIT 1");
            if(mysqli_num_rows($result) == 0) {
                throw new Exception("Invalid user bloodline data!");
            }

            $user_bloodline = mysqli_fetch_assoc($result);
            $bloodline->name = $user_bloodline['name'];

            if($user_bloodline['jutsu']) {
                $user_jutsu = json_decode($user_bloodline['jutsu'], true);
                $bloodline->jutsu = array();

                if(is_array($user_jutsu)) {
                    foreach($user_jutsu as $uj) {
                        $id = $uj['jutsu_id'];

                        $bloodline->jutsu[$id] = $bloodline->base_jutsu[$id];
                        $bloodline->jutsu[$id]->id = $id;
                        $bloodline->jutsu[$id]->setLevel($uj['level'], $uj['exp']);
                    }
                }
            }
            else {
                $bloodline->jutsu = array();
            }
        }

        return $bloodline;
    }

    public function setBoostAmounts(
        int $user_rank,
        int $ninjutsu_skill, int $taijutsu_skill, int $genjutsu_skill, int $bloodline_skill,
        int $base_stats, int $total_stats, int $stats_max_level,
        int $regen_rate
    ): void {
        $ratios = [
            'offense_boost' => 0.03,
            'defense_boost' => 0.045,
            'speed_boost' => 0.08,
            'mental_boost' => 0.1,
            'heal' => 0.03,
            'regen' => 0.15,
        ];
        $bloodline_skill += self::BASE_BLOODLINE_SKILL;

        if($this->raw_passive_boosts) {
            $this->passive_boosts = json_decode($this->raw_passive_boosts, true);
        }
        if($this->raw_combat_boosts) {
            $this->combat_boosts = json_decode($this->raw_combat_boosts, true);
        }

        // Each ratio operates on assumption of 5 BLP
        foreach($this->passive_boosts as $id => $boost) {
            $boost_power = floor($boost['power'] / 5);

            switch($boost['effect']) {
                case 'regen':
                    $regen_multiplier = ($bloodline_skill / $stats_max_level);
                    if($regen_multiplier > 1) {
                        $regen_multiplier = 1;
                    }
                    $regen_multiplier *= $ratios['regen'];

                    $this->passive_boosts[$id]['power'] = round($boost_power * $regen_multiplier, 2);
                    $this->passive_boosts[$id]['effect_amount'] = floor($this->passive_boosts[$id]['power'] * $regen_rate);
                    break;

                case 'scout_range':
                case 'stealth':
                    $boost_amount = 0;
                    if($bloodline_skill < ($base_stats * 0.4) && $user_rank > 2) {
                        if($user_rank > 3 && $bloodline_skill > 750) {
                            $boost_amount = 1;
                        }
                        $this->passive_boosts[$id]['progress'] = round($bloodline_skill / ($base_stats * 0.4), 2) * 100;
                    }
                    else {
                        $boost_amount = $user_rank - 2;

                        $extra_boost = $bloodline_skill / ($stats_max_level * 0.5);
                        if($extra_boost > 0.99) {
                            $boost_amount += 1;
                        }
                        else {
                            $this->passive_boosts[$id]['progress'] = round($extra_boost, 2) * 100;
                        }

                    }
                    $this->passive_boosts[$id]['effect_amount'] = $boost_amount;

                    break;
            }
        }

        // (boosts are 50% at 1:2 offense:bl_skill) - but why tho?
        foreach($this->combat_boosts as $id => $boost) {
            $boost_power = floor($boost['power'] / 5);

            $jutsu_type_skill = 10;

            switch($boost['effect']) {
                case 'ninjutsu_boost':
                case 'ninjutsu_resist':
                    $jutsu_type_skill = $ninjutsu_skill;
                    break;
                case 'genjutsu_boost':
                case 'genjutsu_resist':
                    $jutsu_type_skill = $genjutsu_skill;
                    break;
                case 'taijutsu_boost':
                case 'taijutsu_resist':
                    $jutsu_type_skill = $taijutsu_skill;
                    break;
            }

            switch($boost['effect']) {
                case 'ninjutsu_boost':
                case 'genjutsu_boost':
                case 'taijutsu_boost':
                    $this->combat_boosts[$id]['power'] =
                        round($boost_power * $ratios['offense_boost'], 3);
                    break;

                case 'ninjutsu_resist':
                case 'genjutsu_resist':
                case 'taijutsu_resist':
                    $this->combat_boosts[$id]['power'] =
                        round($boost_power * $ratios['defense_boost'], 3);
                    break;

                case 'speed_boost':
                case 'cast_speed_boost':
                    $this->combat_boosts[$id]['power'] = round($boost_power * $ratios['speed_boost'], 3);
                    break;

                case 'intelligence_boost':
                case 'willpower_boost':
                    $this->combat_boosts[$id]['power'] = round($boost_power * $ratios['mental_boost'], 3);
                    break;

                case 'heal':
                    $this->combat_boosts[$id]['power'] =
                        round($boost_power * $ratios['heal'], 3);
                    break;
            }

            $this->combat_boosts[$id]['effect_amount'] = round($this->combat_boosts[$id]['power'] * $bloodline_skill, 3);
        }
    }

    /**
     * @param System $system
     * @param int    $bloodline_id
     * @param int    $user_id
     * @param bool   $display
     * @return bool
     * @throws Exception
     */
    public static function giveBloodline(System $system, int $bloodline_id, int $user_id, bool $display = true): bool {
        $result = $system->query("SELECT * FROM `bloodlines` WHERE `bloodline_id` = '$bloodline_id' LIMIT 1");
        if($system->db_last_num_rows == 0) {
            throw new Exception("Invalid bloodline!");
        }
        $bloodline = $system->db_fetch($result);

        $user_bloodline['bloodline_id'] = $bloodline['bloodline_id'];
        $user_bloodline['name'] = $bloodline['name'];
        $user_bloodline['passive_boosts'] = $bloodline['passive_boosts'];
        $user_bloodline['combat_boosts'] = $bloodline['combat_boosts'];
        // 5000 bl skill -> 20 power = 1 increment of BL effect
        // Heal: 1 increment = 100 heal

        $effects = [
            // Passive boosts
            'scout_range' => [
                'multiplier' => 0.00004,
            ],
            'stealth' => [
                'multiplier' => 0.00004,
            ],
            'regen' => [
                'multiplier' => 0.0001,
            ],
            // Combat boosts
            'heal' => [
                'multiplier' => 0.001,
            ],
            'ninjutsu_boost' => [
                'multiplier' => 0.01,
            ],
            'taijutsu_boost' => [
                'multiplier' => 0.01,
            ],
            'genjutsu_boost' => [
                'multiplier' => 0.01,
            ],
            'ninjutsu_resist' => [
                'multiplier' => 0.01,
            ],
            'taijutsu_resist' => [
                'multiplier' => 0.01,
            ],
            'genjutsu_resist' => [
                'multiplier' => 0.01,
            ],
            'speed_boost' => [
                'multiplier' => 0.001,
            ],
            'cast_speed_boost' => [
                'multiplier' => 0.001,
            ],
            'endurance_boost' => [
                'multiplier' => 0.001,
            ],
            'intelligence_boost' => [
                'multiplier' => 0.001,
            ],
            'willpower_boost' => [
                'multiplier' => 0.001,
            ],
        ];
        if($user_bloodline['passive_boosts']) {
            $user_bloodline['passive_boosts'] = json_decode($user_bloodline['passive_boosts'], true);
            foreach($user_bloodline['passive_boosts'] as $id => $boost) {
                if(isset($effects[$boost['effect']])) {
                    $user_bloodline['passive_boosts'][$id]['power'] =
                        round($boost['power'] * $effects[$boost['effect']]['multiplier'], 6);
                }
            }
            $user_bloodline['passive_boosts'] = json_encode($user_bloodline['passive_boosts']);
        }
        if($user_bloodline['combat_boosts']) {
            $user_bloodline['combat_boosts'] = json_decode($user_bloodline['combat_boosts'], true);
            foreach($user_bloodline['combat_boosts'] as $id => $boost) {
                if(isset($effects[$boost['effect']])) {
                    $user_bloodline['combat_boosts'][$id]['power'] =
                        round($boost['power'] * $effects[$boost['effect']]['multiplier'], 6);
                }
            }
            $user_bloodline['combat_boosts'] = json_encode($user_bloodline['combat_boosts']);
        }

        // move ids (level & exp -> 0)
        $user_bloodline['jutsu'] = false;
        $result = $system->query("SELECT `bloodline_id` FROM `user_bloodlines` WHERE `user_id`='$user_id' LIMIT 1");

        // Insert new row
        if($system->db_last_num_rows == 0) {
            $query = "INSERT INTO `user_bloodlines` (`user_id`, `bloodline_id`, `name`, `passive_boosts`, `combat_boosts`, `jutsu`)
			VALUES ('$user_id', '$bloodline_id', '{$user_bloodline['name']}', '{$user_bloodline['passive_boosts']}', 
			'{$user_bloodline['combat_boosts']}', '{$user_bloodline['jutsu']}')";
        }

        // Update existing row
        else {
            $query = "UPDATE `user_bloodlines` SET
			`bloodline_id` = '$bloodline_id',
			`name` = '{$user_bloodline['name']}',
			`passive_boosts` = '{$user_bloodline['passive_boosts']}',
			`combat_boosts` = '{$user_bloodline['combat_boosts']}',
			`jutsu` = '{$user_bloodline['jutsu']}'
			WHERE `user_id`='$user_id' LIMIT 1";
        }
        $system->query($query);

        if($system->db_last_affected_rows == 1) {
            if($display) {
                $system->message("Bloodline given!");
            }
            $result = $system->query("SELECT `exp`, `bloodline_skill` FROM `users` WHERE `user_id`='$user_id' LIMIT 1");
            $result = $system->db_fetch($result);
            $new_exp = $result['exp'];
            $new_bloodline_skill = $result['bloodline_skill'];
            if($result['bloodline_skill'] > 10) {
                $bloodline_skill_reduction = ($result['bloodline_skill'] - 10) * Bloodline::SKILL_REDUCTION_ON_CHANGE;
                $new_exp -= $bloodline_skill_reduction * 10;
                $new_bloodline_skill -= $bloodline_skill_reduction;
            }

            $query = "UPDATE `users` SET 
            `bloodline_id`='$bloodline_id', 
            `bloodline_name`='{$bloodline['name']}', 
            `bloodline_skill`='{$new_bloodline_skill}',
            `exp`='{$new_exp}'
			WHERE `user_id`='$user_id' LIMIT 1";

            $system->query($query);
            if($user_id == $_SESSION['user_id']) {
                global $player;
                $player->bloodline_id = $bloodline_id;
                $player->bloodline_name = $bloodline['name'];
                $player->exp = $new_exp;
                $player->bloodline_skill = $new_bloodline_skill;
            }
        }
        else {
            throw new Exception("Error giving bloodline! (Or user already has this BL)");
        }

        if($display) {
            $system->printMessage();
        }
        return true;
    }
}
