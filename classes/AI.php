<?php

/* 	Class:		AI
	Purpose:	Contains all information for a specific AI, functions for selecting move, calculated damage dealt and
				received, etc
*/
class AI {
    public SystemFunctions $system;

    public $id;
    public $ai_id;
    public $name;
    public $max_health;
    public $level;
    public $gender;

    public $rank;

    public $health;
    public $chakra;

    public $ninjutsu_skill;

    public $genjutsu_skill;
    public $taijutsu_skill;
    public $cast_speed;

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

    /** @var Jutsu[] */
    public array $moves;

    public $current_move;


    // Combat vars
    public $taijutsu_nerf;

    public $speed_boost = 0;
    public $speed_nerf = 0;

    public $cast_speed_boost = 0;
    public $cast_speed_nerf = 0;

    public $intelligence_boost = 0;
    public $intelligence_nerf = 0;

    public $willpower_boost = 0;
    public $willpower_nerf = 0;


    public $barrier;


    /**
     * AI constructor.
     * @param int $ai_id Id of the AI, used to select and update data from database
     */
    public function __construct(int $ai_id) {
        global $system;
        $this->system =& $system;
        if(!$ai_id) {
            $system->error("Invalid AI opponent!");
            return false;
        }
        $this->ai_id = $system->clean($ai_id);
        $this->id = 'A' . $this->ai_id;


        $result = $system->query("SELECT `ai_id`, `name` FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
        if($system->db_num_rows == 0) {
            $system->error("AI does not exist!");
            return false;
        }

        $result = $this->system->db_fetch($result);

        $this->name = $result['name'];

        if(!isset($_SESSION['ai_logic'])) {
            $_SESSION['ai_logic'] = array();
            $_SESSION['ai_logic']['special_move_used'] = false;
        }

        return true;
    }

    /**
     * Loads AI data from the database into class members
     * @throws Exception
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

        $moves = json_decode($ai_data['moves'], true);

        $count = 0;
        foreach($moves as $move) {
            $jutsu = $this->initJutsu($count, $move['jutsu_type'], $move['power'], $move['battle_text']);

            switch($jutsu->jutsu_type) {
                case Jutsu::TYPE_NINJUTSU:
                    $jutsu->use_type = Jutsu::USE_TYPE_PROJECTILE;
                    break;
                case Jutsu::TYPE_TAIJUTSU:
                    $jutsu->use_type = Jutsu::USE_TYPE_PHYSICAL;
                    break;
                case Jutsu::TYPE_GENJUTSU:
                    $jutsu->effect = 'residual_damage';
                    $jutsu->effect_amount = 30;
                    $jutsu->effect_length = 3;
                    break;
                default:
                    throw new Exception("Invalid jutsu type!");
            }

            $this->moves[$count] = $jutsu;

            $count++;
        }

        $jutsuTypes = ['ninjutsu', 'taijutsu'];
        $aiType = rand(0, 1);
        $result = $this->system->query(
            "SELECT `battle_text`, `power`, `jutsu_type` FROM `jutsu` 
                    WHERE `rank` = '{$this->rank}' AND `jutsu_type` = '{$jutsuTypes[$aiType]}' 
                    AND `purchase_type` != '1' AND `purchase_type` != '3' LIMIT 5");
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
            $this->moves[] = $this->initJutsu(
                count($this->moves),
                $moveArr['jutsu_type'],
                $moveArr['power'],
                $moveArr['battle_text']
            );
        }
    }

    /* function chooseMove()
    */
    public function chooseMove(): Jutsu {
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

    public function initJutsu(int $id, $jutsu_type, float $power, string $battle_text): Jutsu {
        return new Jutsu(
            $id,
            'Move ' . $id,
            $this->rank,
            $jutsu_type,
            $power,
            'none',
            0,
            0,
            "N/A",
            $battle_text,
            0,
            Jutsu::USE_TYPE_PHYSICAL,
            $this->rank * 5,
            $this->rank * 1000,
            Jutsu::PURCHASE_TYPE_PURCHASEABLE,
            0,
            Jutsu::ELEMENT_NONE,
            ''
        );
    }

    /* function calcDamage() CONTAINS TEMP FIX
    *	Calculates raw damage based on AI stats and jutsu or item strength
        -Parameters-
        @attack: Copy of the attack data.
        @attack_type (default_jutsu, equipped_jutsu, item, bloodline_jutsu,):
            Type of thing to check for, either item or jutsu
    */
    public function calcDamage(Jutsu $attack, $attack_type = 'default_jutsu'): float {
        switch($attack_type) {
            case 'default_jutsu':
                break;
            case 'equipped_jutsu':
                break;
            default:
                throw new Exception("Invalid jutsu type!");
                break;
        }
        $offense_skill = $attack->jutsu_type . '_skill';
        $offense_boost = 0;
        if(isset($this->{$attack->jutsu_type . '_nerf'})) {
            // echo "Nerf: " . $this->{$attack->jutsu_type . '_nerf'} . "<br />";
            $offense_boost -= $this->{$attack->jutsu_type . '_nerf'};
        }

        // TEMP FIX (should be 0.10)
        $offense = (35 + $this->{$offense_skill} * 0.09);
        $offense += $offense_boost;

        $min = 20;
        $max = 35;
        $rand = (int)(($min + $max) / 2);
        // $rand = mt_rand($min, $max);

        $damage = round($offense * $attack->power * $rand, 2);

        return $damage;
    }

    /* function calcDamageTaken()
    *	Calculates final damage taken based on AI stats and attack type
        -Parameters-
        @raw_damage: Raw damage dealt before defense
        @defense_type (ninjutsu, taijutsu, genjutsu, weapon):
            Type of thing to check for, either item or jutsu
    */
    public function calcDamageTaken($raw_damage, $defense_type) {
        $defense = 50;

        switch($defense_type) {
            case 'ninjutsu':
                $defense += SystemFunctions::diminishing_returns($this->ninjutsu_skill * 0.03, 40);
                break;
            case 'genjutsu':
                $defense += SystemFunctions::diminishing_returns($this->genjutsu_skill * 0.03, 40);
                break;
            case 'taijutsu':
                $defense += SystemFunctions::diminishing_returns($this->taijutsu_skill * 0.03, 40);
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
