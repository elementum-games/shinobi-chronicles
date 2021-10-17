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

        $this->passive_boosts = $bloodline_data['passive_boosts'];
        $this->combat_boosts = $bloodline_data['combat_boosts'];
        
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


        if($this->passive_boosts) {
            $this->passive_boosts = json_decode($this->passive_boosts, true);
            //var_dump($this->passive_boosts);
        }
        if($this->combat_boosts) {
            $this->combat_boosts = json_decode($this->combat_boosts, true);
        }

    }
}
