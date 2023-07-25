<?php
/**
 * @var System $system
 * @var User $user
 */
require_once __DIR__ . "/_authenticate_admin.php";
$self_link = $system->router->base_url . 'admin/reputation_simulator.php';
$sub_data = null;

class RepPlayer {
    const TYPE_CASUAL = 'casual';
    const TYPE_AVERAGE = 'average';
    const TYPE_ABOVE_AVERAGE = 'above_average';
    const TYPE_NIGHTMARE = 'nightmare';
    public static array $player_types = [
        self::TYPE_CASUAL, self::TYPE_AVERAGE, self::TYPE_ABOVE_AVERAGE, self::TYPE_NIGHTMARE
    ];

    const WEEKLY_CAP_RATE = [
        self::TYPE_CASUAL => 50,
        self::TYPE_AVERAGE => 65,
        self::TYPE_ABOVE_AVERAGE => 80,
        self::TYPE_NIGHTMARE => 100,
    ];
    const PVP_CAP_RATE = [
        self::TYPE_CASUAL => 10,
        self::TYPE_AVERAGE => 55,
        self::TYPE_ABOVE_AVERAGE => 60,
        self::TYPE_NIGHTMARE => 100,
    ];

    public string $type;
    public string $name;
    public int $village_rep;
    public int $weekly_rep;
    public int $pvp_rep;
    public int $last_pvp_reset;
    public ?string $last_pvp_kills;
    public ?string $last_killer_ids;
    public UserReputation $reputation;
    public array $weekly_data;
    public function __construct($player_type) {
        $this->type = $player_type;
        $this->name = System::unSlug($this->type) . ' Player';
        $this->village_rep = 0;
        $this->pvp_rep = 0;
        $this->last_pvp_reset = 0;
        $this->weekly_rep = 0;
        $this->last_pvp_kills = json_encode(array());
        $this->last_killer_ids = json_encode(array());
        $this->weekly_data = array();
        $this->reputation = new UserReputation($this->village_rep, $this->weekly_rep,
            $this->pvp_rep, $this->last_pvp_reset,
            $this->last_pvp_kills, $this->last_killer_ids, 0, null);
    }
}
function runSimulation(&$data, $weeks = 4) {
    foreach(RepPlayer::$player_types as $type) {
        $data[] = new RepPlayer($type);
    }

    for($i=1;$i<=$weeks;$i++) {
        foreach($data as $rep_user) {
            /**
             * @var $rep_user RepPlayer
             */
            $rep_user->weekly_data[$i] = array(
                'village_rep' => $rep_user->village_rep,
                'pvp_rep' => $rep_user->pvp_rep,
                'weekly_rep' => $rep_user->weekly_rep,
                'rep_rank' => $rep_user->reputation->rank
            );
        }
    }
}
$data = null;
runSimulation($data);
?>

<style>
    table {
        width: 80%;
        margin: 5px auto;
        border: 1px solid black;
        border-collapse: collapse;
    }
    table th {
        border: 1px solid black;
        background-color: grey;
        color: white;
    }
    label {
        display: inline-block;
        width: 175px;
    }
    label.indent {
        margin-left: 10px;
        width: 100px;
    }
</style>

<table style="width: 40%;text-align: center;">
    <tr>
        <th>Simulation Data</th>
    </tr>
    <tr>
        <td>
            <form action="<?=$self_link?>" method="post">
                <?php foreach(RepPlayer::$player_types as $key => $type): ?>
                    <b><?=System::unSlug($type)?> Player Data</b><br />
                    <div style="width:100%;text-align: left;margin-left: 5px;">
                        <label>Weekly Completion Rate:</label><input type="text" name="weekly_completion_rate"
                            value="<?=$sub_data[$type]['weekly_completion_rate'] ?? RepPlayer::WEEKLY_CAP_RATE[$type]?>" /><br />
                        <label>PvP Completion Rate:</label><input type="text" name="pvp_completion_rate"
                            value="<?=$sub_data[$type]['pvp_completion_rate'] ?? RepPlayer::PVP_CAP_RATE[$type]?>" />
                    </div>
                    <hr />
                <?php endforeach ?>
                <b>General Data</b>
                <div style="width:100%;text-align: left;margin-left: 5px;">
                    <input type="checkbox" <?=(UserReputation::PVP_REP_RESET_DAILY ? 'checked':'')?>/><label>Use Daily PvP Cap</label><br />
                    <?php foreach(UserReputation::$VillageRep as $rank => $rank_data): ?>
                        <b><?=$rank_data['title']?></b><br />
                        <label class="indent">Weekly Cap:</label><input type="text" name="<?=$rank?>_weekly_cap"
                            value="<?=$sub_data['rep_rank_'.$rank]['weekly_cap'] ?? $rank_data['weekly_cap']?>" /><br />
                        <label class="indent">PvP Cap:</label><input type="text" name="<?=$rank?>_pvp_cap"
                            value="<?=$sub_data['rep_rank_'.$rank]['pvp_cap'] ?? $rank_data['weekly_pvp_cap']?>" /><br />
                        <label class="indent">Decay Rate:</label><input type="text" name="<?=$rank?>_decay_rate"
                            value="<?=$sub_data['rep_rank_'.$rank]['decay_rate'] ?? $rank_data['base_decay']?>" /><br />
                    <?php endforeach ?>
                </div>
                <input type="submit" name="run_sim" value="Run" />
            </form>
        </td>
    </tr>
</table>