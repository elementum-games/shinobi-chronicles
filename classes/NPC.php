<?php

/* 	Class:		NPC
	Purpose:	Contains all information for a specific NPC, functions for selecting move, calculated damage dealt and
				received, etc
*/
class NPC extends Fighter {
    const ID_PREFIX = 'NPC';

    public System $system;
    public RankManager $rankManager;

    public string $id;
    public int $ai_id;
    public string $name;
    public float $max_health;
    public int $level;
    public string $gender;

    public int $rank;
    public float $rank_progress;

    public float $health;
    public float $chakra = 0;
    public float $stamina = 0;

    public float $ninjutsu_skill;

    public float $genjutsu_skill;
    public float $taijutsu_skill;
    public float $cast_speed;

    public float $speed;
    public float $strength;
    public float $intelligence;
    public float $willpower;

    public int $money;

    /** @var Jutsu[] */
    public array $jutsu = [];

    public int $bloodline_id = 0;

    public $current_move;

    public int $staff_level = 0;

    /**
     * NPC constructor.
     * @param System $system
     * @param int    $ai_id Id of the NPC, used to select and update data from database
     * @throws Exception
     */
    public function __construct(System $system, int $ai_id) {
        $this->system =& $system;
        if(!$ai_id) {
            $system->error("Invalid NPC opponent!");
            return false;
        }
        $this->ai_id = $system->clean($ai_id);
        $this->id = self::ID_PREFIX . ':' . $this->ai_id;

        $result = $system->query("SELECT `ai_id`, `name` FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
        if($system->db_last_num_rows == 0) {
            throw new Exception("NPC does not exist!");
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
     * Loads NPC data from the database into class members
     * @throws Exception
     */
    public function loadData() {
        $result = $this->system->query("SELECT * FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
        $ai_data = $this->system->db_fetch($result);

        $this->rankManager = new RankManager($this->system);
        $this->rankManager->loadRanks();

        $this->rank = $ai_data['rank'];
        $this->level = $ai_data['level'];

        $base_level = $this->rankManager->ranks[$this->rank]->base_level;
        $max_level = $this->rankManager->ranks[$this->rank]->max_level;
        $this->rank_progress = round(($this->level - $base_level) / ($max_level - $base_level), 2);

        $this->max_health = $this->rankManager->healthForRankAndLevel($this->rank, $this->level) * $ai_data['max_health'];
        $this->health = $this->max_health;

        $this->gender = "Male";

        $stats_for_level = $this->rankManager->statsForRankAndLevel($this->rank, $this->level);
        $this->ninjutsu_skill = $stats_for_level * $ai_data['ninjutsu_skill'];
        $this->genjutsu_skill = $stats_for_level * $ai_data['genjutsu_skill'];
        $this->taijutsu_skill = $stats_for_level * $ai_data['taijutsu_skill'];

        $this->cast_speed = $stats_for_level * $ai_data['cast_speed'];

        $this->speed = $stats_for_level * $ai_data['speed'];
        $this->strength = $stats_for_level * $ai_data['strength'];
        $this->intelligence = $stats_for_level * $ai_data['intelligence'];
        $this->willpower = $stats_for_level * $ai_data['willpower'];

        $attributes = array('cast_speed', 'speed', 'strength', 'intelligence', 'willpower');
        foreach($attributes as $attribute) {
            if($this->{$attribute} <= 0) {
                $this->{$attribute} = 1;
            }
        }

        $this->money = $ai_data['money'];

        $moves = json_decode($ai_data['moves'], true);

        foreach($moves as $move) {
            $jutsu = $this->initJutsu(count($this->jutsu), $move['jutsu_type'], $move['power'], $move['battle_text']);

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

            $this->jutsu[] = $jutsu;
        }

        $this->loadDefaultJutsu();
        // $this->loadRandomShopJutsu();
    }

    private function loadDefaultJutsu() {
        $result = $this->system->query(
            "SELECT `battle_text`, `power`, `jutsu_type` FROM `jutsu` 
                    WHERE `rank` <= '{$this->rank}'
                    AND `purchase_type`='" . Jutsu::PURCHASE_TYPE_DEFAULT . "'
                    ORDER BY `rank` DESC LIMIT 1");
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
            $this->jutsu[] = $this->initJutsu(
                count($this->jutsu),
                $moveArr['jutsu_type'],
                $moveArr['power'],
                $moveArr['battle_text']
            );
        }
    }

    private function loadRandomShopJutsu() {
        $jutsuTypes = ['ninjutsu', 'taijutsu'];
        $aiType = rand(0, 1);
        $result = $this->system->query(
            "SELECT `battle_text`, `power`, `jutsu_type` FROM `jutsu` 
                    WHERE `rank` = '{$this->rank}' AND `jutsu_type` = '{$jutsuTypes[$aiType]}' 
                    AND `purchase_type` != '1' AND `purchase_type` != '3' LIMIT 1");
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
            $this->jutsu[] = $this->initJutsu(
                count($this->jutsu),
                $moveArr['jutsu_type'],
                $moveArr['power'],
                $moveArr['battle_text']
            );
        }
    }

    public function chooseAttack(): Jutsu {
        if(!$_SESSION['ai_logic']['special_move_used'] && $this->jutsu[1]) {
            $this->current_move =& $this->jutsu[1];
            $_SESSION['ai_logic']['special_move_used'] = true;
        }
        else {
            $randMove = rand(1, (count($this->jutsu) - 1));
            $this->current_move =& $this->jutsu[$randMove];
        }

        return $this->current_move;
    }

    public function initJutsu(int $id, $jutsu_type, float $power, string $battle_text): Jutsu {
        $battle_text_alt = str_replace(
            ['[player]', '[opponent]'],
            ['[playerX]', '[opponentX]'],
            $battle_text
        );

        $battle_text_swapped = str_replace(
            ['[playerX]', '[opponentX]'],
            ['[opponent]', '[player]'],
            $battle_text_alt
        );

        $jutsu = new Jutsu(
            $id,
            'Move ' . $id,
            $this->rank,
            $jutsu_type,
            $power,
            'none',
            0,
            0,
            "N/A",
            $battle_text_swapped,
            0,
            Jutsu::USE_TYPE_PHYSICAL,
            $this->rank * 5,
            $this->rank * 1000,
            Jutsu::PURCHASE_TYPE_PURCHASEABLE,
            0,
            Jutsu::ELEMENT_NONE,
            ''
        );

        $jutsu_level = 5 + ($this->rank_progress * 50);
        $jutsu->setLevel($jutsu_level, 0);

        return $jutsu;
    }

    public function updateData() {
        $_SESSION['ai_health'] = $this->health;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAvatarSize(): int {
        return 125;
    }

    public function getInventory() {
    }

    public function hasItem(int $item_id): bool {
        return false;
    }

    public function hasEquippedJutsu(int $jutsu_id): bool {
        return true;
    }

    public function useJutsu(Jutsu $jutsu) {

    }

    public function updateInventory() {

    }

    /**
     * @param System $system
     * @param string $entity_id_str
     * @return NPC
     * @throws Exception
     */
    public static function fromEntityId(System $system, string $entity_id_str): NPC {
        $entityId = System::parseEntityId($entity_id_str);

        if($entityId->entity_type != self::ID_PREFIX) {
            throw new Exception("Invalid entity type for NPC class!");
        }

        return new NPC($system, $entityId->id);
    }
}
