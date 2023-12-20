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
    public int $level;
    public string $gender;

    public int $rank;
    public float $rank_progress;

    public float $health;
    public float $max_health;
    public float $chakra = 100;
    public float $max_chakra = 100;
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

    public int $battle_iq = 0; // chance to use optimal jutsu over random
    public bool $scaling = false; // whether AI will scale to the nearest possible level
    public array $shop_jutsu_priority = []; // if set, AI will prioritize these jutsu on non-optimal roll

    /** @var Jutsu[] */
    public array $jutsu = [];

    public int $bloodline_id = 0;

    public $current_move;

    public int $staff_level = 0;

    /**
     * NPC constructor.
     * @param System $system
     * @param int    $ai_id Id of the NPC, used to select and update data from database
     * @throws RuntimeException
     */
    public function __construct(System $system, int $ai_id) {
        $this->system =& $system;
        if(!$ai_id) {
            throw new RuntimeException("Invalid NPC opponent!");
        }
        $this->ai_id = $system->db->clean($ai_id);
        $this->id = self::ID_PREFIX . ':' . $this->ai_id;

        $result = $system->db->query("SELECT `ai_id`, `name` FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
        if($system->db->last_num_rows == 0) {
            throw new RuntimeException("NPC does not exist!");
        }

        $result = $this->system->db->fetch($result);

        $this->name = $result['name'];

        if(!isset($_SESSION['ai_logic'])) {
            $_SESSION['ai_logic'] = array();
            $_SESSION['ai_logic']['special_move_used'] = false;
        }

        return true;
    }

    /**
     * Loads NPC data from the database into class members
     * @throws RuntimeException
     */
    public function loadData(?User $player = null) {
        $result = $this->system->db->query("SELECT * FROM `ai_opponents` WHERE `ai_id`='$this->ai_id' LIMIT 1");
        $ai_data = $this->system->db->fetch($result);

        $this->rankManager = new RankManager($this->system);
        $this->rankManager->loadRanks();

        $this->rank = $ai_data['rank'];
        $this->level = $ai_data['level'];
        $this->scaling = $ai_data['scaling'];

        $base_level = $this->rankManager->ranks[$this->rank]->base_level;
        $max_level = $this->rankManager->ranks[$this->rank]->max_level;
        // if player is set we can apply AI scaling
        if (isset($player) && $this->scaling) {
            $this->level = max(min($player->level, $max_level), $base_level);
        }
        // set jutsu level relative to base and max levels for the rank, minimum 1
        $jutsu_level = max(1, intval((($this->level - $base_level) / ($max_level - $base_level)) * 100));
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

        $this->battle_iq = $ai_data['battle_iq'];

        $moves = json_decode($ai_data['moves'], true);

        foreach($moves as $move) {
            if (!isset ($move['use_type'])) {
                $move['use_type'] = Jutsu::USE_TYPE_MELEE;
            }
            if (!isset($move['effect'])) {
                $move['effect'] = "none";
            }
            if (!isset($move['effect_amount'])) {
                $move['effect_amount'] = 0;
            }
            if (!isset($move['effect_length'])) {
                $move['effect_length'] = 0;
            }
            $jutsu = $this->initJutsu(count($this->jutsu), $move['jutsu_type'], $move['power'], $move['battle_text'], $move['use_type'], $move['effect'], $move['effect_amount'], $move['effect_length']);
            $jutsu->setLevel($jutsu_level, 0);
            switch($jutsu->jutsu_type) {
                case Jutsu::TYPE_NINJUTSU:
                    $jutsu->use_type = $jutsu->use_type != Jutsu::USE_TYPE_MELEE ? $jutsu->use_type : Jutsu::USE_TYPE_PROJECTILE;
                    break;
                case Jutsu::TYPE_TAIJUTSU:
                    $jutsu->use_type = $jutsu->use_type != Jutsu::USE_TYPE_MELEE ? $jutsu->use_type : Jutsu::USE_TYPE_MELEE;
                    break;
                case Jutsu::TYPE_GENJUTSU:
                    $jutsu->use_type = $jutsu->use_type != Jutsu::USE_TYPE_MELEE ? $jutsu->use_type : Jutsu::USE_TYPE_PROJECTILE;
                    $jutsu->effects[0]->effect = $jutsu->effects[0]->effect != "none" ? $jutsu->effects[0]->effect : 'residual_damage';
                    $jutsu->effects[0]->effect_amount = $jutsu->effects[0]->effect_amount != 0 ? $jutsu->effects[0]->effect_amount : 30;
                    $jutsu->effects[0]->effect_length = $jutsu->effects[0]->effect_length != 0 ? $jutsu->effects[0]->effect_length : 3;
                    break;
                default:
                    throw new RuntimeException("Invalid jutsu type!");
            }

            $this->jutsu[] = $jutsu;
            // this is important so battle logic treats all NPC jutsu as equipped jutsu
            $this->equipped_jutsu[] = [
                'id' => $jutsu->id,
                'type' => $jutsu->jutsu_type
            ];
        }

        // get array, safe formatting
        $this->shop_jutsu_priority = array_map('intval', explode(",", str_replace(' ', '', $ai_data['shop_jutsu_priority'])));

        // get shop jutsu
        $jutsu_result = $this->system->db->query("SELECT * FROM `jutsu` WHERE `jutsu_id` IN (" . $ai_data['shop_jutsu'] . ")");
        $jutsu_result = $this->system->db->fetch_all($jutsu_result);
        foreach ($jutsu_result as $jutsu_data) {
            $shop_jutsu = Jutsu::fromArray($jutsu_data['jutsu_id'], $jutsu_data);
            $shop_jutsu->setLevel($jutsu_level, 0);
            $this->jutsu[] = $shop_jutsu;
            // this is important so battle logic treats all NPC jutsu as equipped jutsu
            $this->equipped_jutsu[] = [
                'id' => $shop_jutsu->id,
                'type' => $shop_jutsu->jutsu_type
            ];
        }

        $this->loadDefaultJutsu();
        // $this->loadRandomShopJutsu();
    }

    private function loadDefaultJutsu() {
        $result = $this->system->db->query(
            "SELECT `battle_text`, `power`, `jutsu_type` FROM `jutsu`
                    WHERE `rank` <= '{$this->rank}'
                    AND `purchase_type`='" . Jutsu::PURCHASE_TYPE_DEFAULT . "'
                    ORDER BY `rank` DESC LIMIT 1"
        );
        while ($row = $this->system->db->fetch($result)) {
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
        $result = $this->system->db->query(
            "SELECT `battle_text`, `power`, `jutsu_type` FROM `jutsu`
                    WHERE `rank` = '{$this->rank}' AND `jutsu_type` = '{$jutsuTypes[$aiType]}'
                    AND `purchase_type` != '1' AND `purchase_type` != '3' LIMIT 1"
        );
        while ($row = $this->system->db->fetch($result)) {
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

    public function chooseAttack(BattleManager|BattleManagerV2 $battle): Jutsu {
        // probability of choosing best move
        $x = mt_rand(1, 100);
        $choose_best = $this->battle_iq >= $x ? true : false;
        if ($choose_best) {
            // determine best move
            $this->current_move = $this->chooseBestAttack($battle);
        }
        else {
            // pick random move, or use priority list
            $this->current_move = $this->chooseRandomAttack($battle);
        }
        return $this->current_move;
    }

    function chooseBestAttack(BattleManager|BattleManagerV2 $battle): Jutsu {
        $best_damage = 0;
        $best_jutsu = null;
        // simulate each jutsu and determine best
        foreach ($this->jutsu as $jutsu) {
            $jutsu->setCombatId($this->combat_id);
            // check jutsu cooldown
            if (isset($battle->getCooldowns()[$jutsu->combat_id])) {
                continue;
            }
            // get simulated result
            $test_damage = $battle->simulateAIAttack($jutsu);
            $result = $test_damage['player_simulated_damage_taken'] - $test_damage['ai_simulated_damage_taken'];
            // debug
            echo $jutsu->name . ": " . $result . "<br>";
            // if no other jutsu selected use this for basis of comparison
            if (empty($best_jutsu)) {
                $best_damage = $result;
                $best_jutsu = $jutsu;
            } 
            // if better than previous then best set as best
            else if ($result > $best_damage) {
                $best_damage = $result;
                $best_jutsu = $jutsu;
            }
        }
        return $best_jutsu;
    }

    function chooseRandomAttack(BattleManager|BattleManagerV2 $battle): Jutsu {
        $random_jutsu = null;
        $jutsu_list = array_keys($this->jutsu);
        // if shop jutsu priority and not on cooldown use before random
        foreach ($this->shop_jutsu_priority as $jutsu_id) {
            foreach ($this->jutsu as $jutsu) {
                $jutsu->setCombatId($this->combat_id);
                if ($jutsu->id == $jutsu_id) {
                    if (isset($battle->getCooldowns()[$jutsu->combat_id])) {
                        continue;
                    } else {
                        return $jutsu;
                    }
                }
            }
        }
        // randomize jutsu list and check until one without cooldown found (standard strike as fallback)
        shuffle($jutsu_list);
        foreach ($this->jutsu as $jutsu) {
            $jutsu->setCombatId($this->combat_id);
            if (isset($battle->getCooldowns()[$jutsu->combat_id])) {
                continue;
            } else {
                return $jutsu;
            }
        }
    }

    public function initJutsu(int $id, $jutsu_type, float $power, string $battle_text, string $use_type = Jutsu::USE_TYPE_MELEE, string $effect = "none", int $effect_amount = 0, int $effect_length = 0): Jutsu {
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
            id: $id,
            name: 'Move ' . $id,
            rank: $this->rank,
            jutsu_type: $jutsu_type,
            base_power: $power,
            range: 2,
            effect_1: $effect,
            base_effect_amount_1: $effect_amount,
            effect_length_1: $effect_length,
            effect_2: 'none',
            base_effect_amount_2: 0,
            effect_length_2: 0,
            description: "N/A",
            battle_text: $battle_text_swapped,
            cooldown: 0,
            use_type: $use_type,
            target_type: Jutsu::TARGET_TYPE_FIGHTER_ID,
            use_cost: $this->rank * 5,
            purchase_cost: $this->rank * 1000,
            purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            parent_jutsu: 0,
            element: Jutsu::ELEMENT_NONE,
            hand_seals: ''
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

    public function getMoney(): int {
        return $this->money;
    }

    public function hasJutsu(int $jutsu_id): bool {
        return false;
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
     * @throws RuntimeException
     */
    public static function fromEntityId(System $system, string $entity_id_str): NPC {
        $entityId = System::parseEntityId($entity_id_str);

        if($entityId->entity_type != self::ID_PREFIX) {
            throw new RuntimeException("Invalid entity type for NPC class!");
        }

        return new NPC($system, $entityId->id);
    }
}
