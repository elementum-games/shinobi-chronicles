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

    public System $system;

    public $bloodline_id;
    public int $id;
    public string $name;
    public int $clan_id;
    public int $rank;
    public string $description;

    protected ?string $raw_passive_boosts;
    protected ?string $raw_combat_boosts;

    public $passive_boosts;
    public $combat_boosts;

    /** @var Jutsu[] */
    private array $base_jutsu;
    /** @var Jutsu[] */
    public array $jutsu;

    public function __construct(int $bloodline_id, ?int $user_id = null) {
        global $system;
        $this->system =& $system;
        if(!$bloodline_id) {
            $system->error("Invalid bloodline id!");
            return false;
        }
        $this->bloodline_id = (int)$bloodline_id;
        // $this->id = 'BL' . $this->user_id;

        $result = $system->query("SELECT * FROM `bloodlines` WHERE `bloodline_id`='$this->bloodline_id' LIMIT 1");
        if($system->db_last_num_rows == 0) {
            $system->error("Bloodline does not exist!");
            return false;
        }

        $bloodline_data = $system->db_fetch($result);

        $this->name = $bloodline_data['name'];
        $this->clan_id = $bloodline_data['clan_id'];
        $this->rank = $bloodline_data['rank'];

        $this->raw_passive_boosts = $bloodline_data['passive_boosts'];
        $this->raw_combat_boosts = $bloodline_data['combat_boosts'];
        
        $this->base_jutsu = [];
        $this->jutsu = [];
        $jutsu_data = $bloodline_data['jutsu'];
        if($jutsu_data) {
            $uj = json_decode($bloodline_data['jutsu'], true);
            foreach($uj as $id => $j) {
                $this->base_jutsu[$id] = new Jutsu(
                    $id,
                    $j['name'], 
                    $j['rank'], 
                    $j['jutsu_type'], 
                    $j['power'],
                    $j['effect'], 
                    $j['effect_amount'] ?? 0,
                    $j['effect_length'] ?? 0,
                    $j['description'], 
                    $j['battle_text'], 
                    $j['cooldown'] ?? 0,
                    $j['use_type'], 
                    $j['use_cost'], 
                    $j['purchase_cost'], 
                    Jutsu::PURCHASE_TYPE_BLOODLINE,
                    $j['parent_jutsu'] ?? 0,
                    $j['element'], 
                    $j['hand_seals']
                );
                $this->base_jutsu[$id]->is_bloodline = true;
            }

            $this->jutsu = $this->base_jutsu;
        }

        // Load user-related BL data if relevant
        if($user_id) {
            $result = $system->query("SELECT * FROM `user_bloodlines` WHERE `user_id`=$user_id LIMIT 1");
            if(mysqli_num_rows($result) == 0) {
                $this->system->message("Invalid user bloodline data!");
                $this->system->printMessage();
                return false;
            }

            $user_bloodline = mysqli_fetch_assoc($result);
            $this->name = $user_bloodline['name'];

            if($user_bloodline['jutsu']) {
                $user_jutsu = json_decode($user_bloodline['jutsu'], true);
                $this->jutsu = array();

                if(is_array($user_jutsu)) {
                    foreach($user_jutsu as $uj) {
                        $id = $uj['jutsu_id'];
                        
                        $this->jutsu[$id] = $this->base_jutsu[$id];
                        $this->jutsu[$id]->id = $id;
                        $this->jutsu[$id]->setLevel($uj['level'], $uj['exp']);
                    }
                }
            }
            else {
                $this->jutsu = array();
            }
        }

        if($this->raw_passive_boosts) {
            $this->passive_boosts = json_decode($this->raw_passive_boosts, true);
        }
        if($this->raw_combat_boosts) {
            $this->combat_boosts = json_decode($this->raw_combat_boosts, true);
        }
    }
    
    public function setBoostAmounts(
        int $user_rank,
        int $ninjutsu_skill, int $taijutsu_skill, int $genjutsu_skill, int $user_bloodline_skill,
        int $base_stats, int $total_stats, int $stats_max_level,
        int $regen_rate
    ) {
        $ratios = [
            'offense_boost' => 0.04,
            'defense_boost' => 0.04,
            'speed_boost' => 0.08,
            'mental_boost' => 0.1,
            'heal' => 0.04,
            'regen' => 0.15,
        ];

        if($this->raw_passive_boosts) {
            $this->passive_boosts = json_decode($this->raw_passive_boosts, true);
        }
        if($this->raw_combat_boosts) {
            $this->combat_boosts = json_decode($this->raw_combat_boosts, true);
        }
        // Each ratio operates on assumption of 5 BLP
        foreach($this->passive_boosts as $id => $boost) {
            $boost_power = floor($boost['power'] / 5);

            $bloodline_skill = $user_bloodline_skill + self::BASE_BLOODLINE_SKILL;

            switch($boost['effect']) {
                case 'regen':
                    $regen_multiplier = ($user_bloodline_skill / $stats_max_level);
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

        // (boosts are 50% at 1:2 offense:bl_skill)
        foreach($this->combat_boosts as $id => $boost) {
            $boost_power = floor($boost['power'] / 5);

            $bloodline_skill = $user_bloodline_skill + self::BASE_BLOODLINE_SKILL;
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
                    $skill_ratio = $this->skillRatio($jutsu_type_skill, $bloodline_skill);
                    $this->combat_boosts[$id]['power'] =
                        round($boost_power * $skill_ratio * $ratios['offense_boost'], 3);
                    break;

                case 'ninjutsu_resist':
                case 'genjutsu_resist':
                case 'taijutsu_resist':
                    $skill_ratio = $this->skillRatio($jutsu_type_skill, $bloodline_skill);

                    // Est. jutsu power
                    $skill = $bloodline_skill + $jutsu_type_skill;
                    $jutsu_power = $user_rank;
                    $jutsu_power += round($skill / $stats_max_level, 3);
                    if($jutsu_power > $user_rank + 1) {
                        $jutsu_power = $user_rank + 1;
                    }

                    $multiplier = $jutsu_power;

                    $this->combat_boosts[$id]['power'] =
                        round($boost_power * $multiplier * $skill_ratio * $ratios['defense_boost'], 3);
                    break;

                case 'speed_boost':
                case 'cast_speed_boost':
                    $this->combat_boosts[$id]['power'] = round($boost_power * $ratios['speed_boost'], 3);
                    break;

                case 'intelligence_boost':
                case 'willpower_boost':
                    $this->combat_boosts[$id]['power'] = round($boost_power * $ratios['mental_boost'], 3);
                    break;

                // (NEEDS TESTING/ADJUSTMENT)
                case 'heal':
                    // Est. jutsu power
                    $skill = $bloodline_skill;
                    $jutsu_power = $user_rank;
                    $jutsu_power += round($skill / $stats_max_level, 3);
                    if($jutsu_power > $user_rank + 1) {
                        $jutsu_power = $user_rank + 1;
                    }
                    $stat_multiplier = 35 * $jutsu_power; /* est jutsu power */

                    // Defensive power
                    $defense = 50 + ($total_stats * 0.01);

                    $this->combat_boosts[$id]['power'] =
                        round($boost_power * $stat_multiplier * $ratios['heal'] / $defense, 3);
                    $this->combat_boosts[$id]['divider'] = $defense;

                    break;
            }

            $this->combat_boosts[$id]['effect_amount'] = round($this->combat_boosts[$id]['power'] * $bloodline_skill, 3);
        }
    }

    private function skillRatio(int $offense_skill, int $bloodline_skill): float {
        $bloodline_skill += 10;

        $skill_ratio = round($offense_skill / $bloodline_skill, 3);
        if($skill_ratio > 1.0) {
            $skill_ratio = 1.0;
        }
        else if($skill_ratio < 0.55) {
            $skill_ratio = 0.55;
        }
        return $skill_ratio;
    }
}
