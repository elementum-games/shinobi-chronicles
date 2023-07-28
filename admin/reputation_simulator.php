<?php
/**
 * @var System $system
 * @var User $user
 */
require_once __DIR__ . "/_authenticate_admin.php";
$self_link = $system->router->base_url . 'admin/reputation_simulator.php';
$data = null;
$debug_data = null;

class RepPlayerReputation extends UserReputation {
    public function runDecay(RepPlayer $repPlayer, $sim_data) {
        $this->updateRepTier($sim_data);
        $decay_mod = $sim_data['decay_modifier']/100;
        $decay_amount = RepPlayerReputation::$VillageRep[$repPlayer->reputation->rank]['base_decay'];
        if($repPlayer->reputation->getWeeklyRepAmount() >= $repPlayer->reputation->weekly_cap) {
            $decay_amount *= $decay_mod;
        }

        /*if($sim_data['debug']) {
            echo $repPlayer->name . "<br />";
            echo "Rep Rank {$repPlayer->reputation->rank}<br />" .
            "Rep Amount: {$repPlayer->reputation->getRepAmount()}<br />" .
            "Weekly Rep Amount: {$repPlayer->reputation->getWeeklyRepAmount()}<br />" .
            "BD: " . RepPlayerReputation::$VillageRep[$repPlayer->reputation->rank]['base_decay'] . "<br />
            Decay Mod: $decay_mod<br />
            Decay Amount: $decay_amount<br /><hr />";
        }*/

        $repPlayer->reputation->weekly_rep = 0;
        $repPlayer->reputation->subtractRep($decay_amount);
        $repPlayer->total_rep_lost += $decay_amount;
        // Update tier once more should rep loss have resulted in de-rank
        $this->updateRepTier($sim_data);
    }

    public function updateRepTier($sim_data) {
        $this->rank = self::tierByRepAmount($this->rep);
        $REP_RANK = self::$VillageRep[$this->rank];
        $this->rank_name = $REP_RANK['title'];
        $this->weekly_cap = $REP_RANK['weekly_cap'];
        $this->pvp_cap = ($sim_data['daily_pvp_cap']) ? floor($REP_RANK['weekly_pvp_cap'] * ($sim_data['pvp_daily_con']/100)) : $REP_RANK['weekly_pvp_cap'];
    }
}
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
        self::TYPE_CASUAL => 5,
        self::TYPE_AVERAGE => 55,
        self::TYPE_ABOVE_AVERAGE => 60,
        self::TYPE_NIGHTMARE => 100,
    ];
    const DAILY_COMPLETE_RATE = [
        self::TYPE_CASUAL => 30,
        self::TYPE_AVERAGE => 55,
        self::TYPE_ABOVE_AVERAGE => 80,
        self::TYPE_NIGHTMARE => 100,
    ];

    public string $type;
    public string $name;
    public int $village_rep;
    public int $total_rep_lost;
    public int $total_rep_earned;
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
        $this->total_rep_lost = 0;
        $this->total_rep_earned = 0;
        $this->reputation = new RepPlayerReputation($this->village_rep, $this->weekly_rep,
            $this->pvp_rep, $this->last_pvp_reset,
            $this->last_pvp_kills, $this->last_killer_ids, 0, null);
    }
}

$sim_data = array(
    'debug' => false,
    'show_weekly_data' => true,
    'daily_pvp_cap' => true,
    'weeks' => 4,
    'player_types' => RepPlayer::$player_types,
    'weekly_cap_rates' => RepPlayer::WEEKLY_CAP_RATE,
    'weekly_pvp_rates' => RepPlayer::PVP_CAP_RATE,
    'daily_comp_rate' => RepPlayer::DAILY_COMPLETE_RATE,
    'daily_task_bypass' => UserReputation::DAILY_TASK_BYPASS_CAP,
    'average_weekly_daily' => 140,
    'decay_modifier' => UserReputation::DECAY_MODIFIER * 100,
    'pvp_daily_con' => UserReputation::PVP_WEEKLY_CONVERSION * 100,
    'reputation_data' => UserReputation::$VillageRep,
);

function runRepSimulation(&$data, $sim_data, &$debug_data, $weeks = 4) {
    //Set reputation data
    RepPlayerReputation::$VillageRep = $sim_data['reputation_data'];

    // Basic debug information
    if($sim_data['debug']) {
        $debug_data['basic_data'] = [
            'simulation_settings' => [
                'daily_pvp_cap' => ($sim_data['daily_pvp_cap']) ? 'True' : 'False',
                'daily_bypass_cap' => ($sim_data['daily_task_bypass']) ? 'True' : 'False',
            ]
        ];
    }

    foreach(RepPlayer::$player_types as $type) {
        $data[] = new RepPlayer($type);
    }

    for($i=1;$i<=$weeks;$i++) {
        foreach($data as $rep_user) {
            /**
             * @var $rep_user RepPlayer
             */

            // Weekly Data
            if($sim_data['weekly_cap_rates'][$rep_user->type] > 0) {
                $weekly_cap = mt_rand(1, 100);
                if ($weekly_cap <= $sim_data['weekly_cap_rates'][$rep_user->type]) {
                    if ($sim_data['debug']) {
                        $debug_data[$rep_user->type][$i]['weekly_cap_data'] = "Capped weekly through non-daily means ({$rep_user->reputation->weekly_cap}).";
                    }
                    $rep_user->reputation->addRep($rep_user->reputation->weekly_cap);
                    $rep_user->total_rep_earned += $rep_user->reputation->weekly_cap;
                    $weekly_rep_amount = $rep_user->reputation->weekly_cap;
                } else {
                    $weekly_cap = mt_rand(1, $sim_data['weekly_cap_rates'][$rep_user->type]);
                    $weekly_rep_amount = $rep_user->reputation->weekly_cap * ($weekly_cap / 100);
                    if ($sim_data['debug']) {
                        $debug_data[$rep_user->type][$i]['weekly_cap_data'] = "Did not cap weekly through non-daily means ($weekly_rep_amount).";
                    }
                    $rep_user->reputation->addRep($weekly_rep_amount);
                    $rep_user->total_rep_earned += $weekly_rep_amount;
                }
                // Run rep rank update
                $rep_user->reputation->updateRepTier($sim_data);
            }

            // Daily task data
            if($sim_data['daily_comp_rate'][$rep_user->type] > 0) {
                $daily_cap = mt_rand(1, 100);
                if ($daily_cap <= $sim_data['daily_comp_rate'][$rep_user->type]) {
                    $daily_task_amount = $rep_user->reputation->addRep($sim_data['average_weekly_daily'], $sim_data['daily_task_bypass']);
                    $rep_user->total_rep_earned += $daily_task_amount;
                    if ($sim_data['debug']) {
                        $debug_data[$rep_user->type][$i]['daily_tasks'] = "Earned a full amount of reputation from daily tasks ($daily_task_amount).";
                    }
                } else {
                    //Simulate 7 - 21 daily tasks completed
                    $amount_per_task = $sim_data['average_weekly_daily'] / 21;
                    $tasks_completed = mt_rand(7, 21);
                    $amount_to_add = $tasks_completed * $amount_per_task;

                    $daily_task_amount = $rep_user->reputation->addRep($amount_to_add, $sim_data['daily_task_bypass']);
                    $rep_user->total_rep_earned += $daily_task_amount;
                    if ($sim_data['debug']) {
                        $debug_data[$rep_user->type][$i]['daily_tasks'] = "Did not earn a full amount of reputation from daily tasks ($daily_task_amount).";
                    }
                }
                // Run rep rank update
                $rep_user->reputation->updateRepTier($sim_data);
            }

            // Pvp data
            if($sim_data['weekly_pvp_rates'][$rep_user->type] > 0) {
                $pvp_cap = mt_rand(1, 100);
                if ($pvp_cap <= $sim_data['weekly_pvp_rates'][$rep_user->type]) {
                    $rep_gain = $rep_user->reputation->pvp_cap;
                    //Change rep amount to match weekly gains
                    if ($sim_data['daily_pvp_cap']) {
                        $rep_gain = $rep_gain * 7;
                    }

                    $rep_gain = $rep_user->reputation->addRep($rep_gain, true, true);
                    $rep_user->total_rep_earned += $rep_gain;
                    if ($sim_data['debug']) {
                        $debug_data[$rep_user->type][$i]['pvp'] = "Earned a full amount of reputation from pvp ($rep_gain).";
                    }
                } else {
                    $pvp_cap = mt_rand(1, $sim_data['weekly_pvp_rates'][$rep_user->type]);
                    // Calculate pvp weekly cap into 7 days
                    $rep_cap = $rep_user->reputation->pvp_cap / 7;
                    // Daily Rep cap
                    if ($sim_data['daily_pvp_cap']) {
                        $rep_cap = $rep_user->reputation->pvp_cap * ($sim_data['pvp_daily_con'] / 100);
                    }

                    // Simulate 1 - 7 days of pvp cap being met
                    $days_completed = mt_rand(1, ceil($pvp_cap / 100 * 7));
                    $rep_gain = $rep_cap * $days_completed;

                    // A bit more randomness for 1 or 2 days
                    if ($days_completed <= 2) {
                        $rep_gain += ceil(mt_rand(1, 3) * ($rep_cap * mt_rand(0.45, 0.85)));
                    }

                    $rep_gain = $rep_user->reputation->addRep($rep_gain, true, true);
                    $rep_user->total_rep_earned += $rep_gain;
                    if ($sim_data['debug']) {
                        $debug_data[$rep_user->type][$i]['pvp'] = "Did not earn a full amount of reputation from pvp ($rep_gain).";
                    }
                }

                // Run rep rank update
                $rep_user->reputation->updateRepTier($sim_data);
            }

            // Set weekly data
            $rep_user->weekly_data[$i] = array(
                'village_rep' => $rep_user->village_rep,
                'rep_rank' => $rep_user->reputation->rank,

                'structure:1' => '<br /><b>Weekly Data</b><br />',
                'weekly_rep_total' => $rep_user->weekly_rep,
                'weekly_rep' => ($weekly_rep_amount) ?? 0,
                'daily_task_rep' => ($daily_task_amount) ?? 0,
                'weekly_cap' => $rep_user->reputation->weekly_cap,
                'weekly_rate' => round($rep_user->reputation->getWeeklyRepAmount() / $rep_user->reputation->weekly_cap * 100, 2) . '%',

                'structure:2' => '<br /><b>PvP Data</b><br />',
                'pvp_rep' => $rep_user->pvp_rep,
                'pvp_cap' => ($sim_data['daily_pvp_cap']) ? $rep_user->reputation->pvp_cap * 7 : $rep_user->reputation->pvp_cap,
                'pvp_rate' => round($rep_user->pvp_rep / (($sim_data['daily_pvp_cap']) ? $rep_user->reputation->pvp_cap * 7 : $rep_user->reputation->pvp_cap) * 100, 2) . '%',
            );

            // Reset caps and run decay
            $rep_user->reputation->resetPvpRep();
            $rep_user->reputation->runDecay($rep_user, $sim_data);

            // Update reputation
            $before_decay = $rep_user->weekly_data[$i]['village_rep'];
            $rep_user->weekly_data[$i]['village_rep'] = $rep_user->reputation->getRepAmount();

            // Set decay data
            $rep_user->weekly_data[$i]['structure:3'] = '<br /><b>Decay Data</b><br />';
            $rep_user->weekly_data[$i]['before_decay'] = $before_decay;
            $rep_user->weekly_data[$i]['rep_loss'] = $before_decay - $rep_user->weekly_data[$i]['village_rep'];
        }
    }
}

function calcRateClass($rate) {
    $rate = (int) $rate;
    if($rate >= 100) {
        return 'green bold';
    }
    elseif($rate >= 85) {
        return 'green';
    }
    elseif($rate >= 60) {
        return 'yellow';
    }
    elseif($rate >= 25) {
        return 'red';
    }
    else {
        return 'red bold';
    }
}

if(isset($_POST['run_sim'])) {
    $error = null;

    $weeks = isset($_POST['weeks']) ? (int)$_POST['weeks'] : 4;
    $decay_mod = (int)$_POST['decay_modifier'];
    $pvp_con = (int)$_POST['pvp_daily_con'];

    // Set simulation data for form
    $sim_data['weeks'] = $weeks;
    $sim_data['debug'] = isset($_POST['debug']);
    $sim_data['decay_modifier'] = $decay_mod;
    $sim_data['pvp_daily_con'] = $pvp_con;
    $sim_data['average_weekly_daily'] = (int)$_POST['average_weekly_daily'];
    $sim_data['show_weekly_data'] = isset($_POST['show_weekly_data']);
    $sim_data['daily_pvp_cap'] = isset($_POST['daily_pvp_cap']);
    $sim_data['daily_task_bypass'] = isset($_POST['daily_task_bypass']);

    // Cap Rates
    foreach($sim_data['player_types'] as $p_type) {
        $sim_data['weekly_cap_rates'][$p_type] = (int)$_POST[$p_type.'_weekly_cap_rate'];
        $sim_data['weekly_pvp_rates'][$p_type] = (int)$_POST[$p_type.'_pvp_cap_rate'];
        $sim_data['daily_comp_rate'][$p_type] = (int)$_POST[$p_type.'_daily_comp_rate'];
    }
    // Reputation data
    foreach($sim_data['reputation_data'] as $rank_id => $tier) {
        // Min reputation for rank 1 MUST BE 0
        if($rank_id == 1) {
            $_POST[$rank_id.'_min_rep'] = 0;
        }

        if($rank_id > 1 && $sim_data['reputation_data'][$rank_id-1]['min_rep'] > (int)$_POST[$rank_id.'_min_rep']) {
            $error = "Invalid reputation rank data, check min reputation data! Ranks $rank_id and " . $rank_id-1 . ".";
            break;
        }

        $sim_data['reputation_data'][$rank_id]['min_rep'] = (int)$_POST[$rank_id.'_min_rep'];
        $sim_data['reputation_data'][$rank_id]['weekly_cap'] = (int)$_POST[$rank_id.'_weekly_cap'];
        $sim_data['reputation_data'][$rank_id]['weekly_pvp_cap'] = (int)$_POST[$rank_id.'_pvp_cap'];
        $sim_data['reputation_data'][$rank_id]['base_decay'] = (int)$_POST[$rank_id.'_base_decay'];
    }

    if($error == null) {
        // Run simulation
        runRepSimulation($data, $sim_data, $debug_data, $weeks);
    }
}
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
    table td {
        border: 1px solid black;
    }

    label {
        display: inline-block;
        width: 115px;
        margin-left: 3px;
    }
    label.indent {
        margin-left: 10px;
        width: 75px;
    }
    label.lrg {
        margin-left: 3px;
        width: 125px;
    }
    label.smol {
        margin-left: 3px;
        width: 90px;
    }
    div.playerData {
        width: 48%;
        display: inline-block;
        margin-top: 3px;
        margin-left: 3px;
        border: 1px solid black;
    }
    div.playerData p.header {
        font-weight: bolder;
        margin: 0 0 3px 0;
        padding: 2px;
        text-shadow: 0 1px 3px black, 0 1px 5px black;
        color: white;
        text-align: center;
    }
    div.playerData .casual{
        background-color: green;
    }
    div.playerData .average{
        background-color: yellow;
    }
    div.playerData .above_average{
        background-color: orange;
    }
    div.playerData .nightmare{
        background-color: red;
    }
    div.playerData input[type=text] {
        width: 48px;
    }
    form input[type=submit] {
        margin-top: 5px;
    }
    form input[type=text] {
        margin-bottom: 5px;
    }

    div.weekly_data {
        width: 24.5%;
        min-height: 50px;
        margin-top: 3px;
        margin-bottom: 3px;

        display: inline-block;
        border:1px solid black;
    }
    div.final_data {
        width: 99.4%;
        min-height: 50px;
        margin: 5px auto;
        border:1px solid black;
    }
    div.weekly_data p.header {
        margin: 0;
        padding: 5px;

        background-color: green;

        font-weight: bolder;
        color: white;
    }
    div.final_data p.header {
        margin: 0;
        padding: 5px;

        background-color: red;

        font-weight: bolder;
        color: white;
    }

    div.tool-tip {
        width: 13px;
        height: 13px;
        font-size: 13px;
        text-align: center;
        border-radius: 100%;
        display: inline-block;
        background-color: black;
        color: white;
        font-weight: bolder;
    }

    div#error {
        text-align: center;
        font-weight: bold;
        color: darkred;
    }

    .green {
        color: green;
    }
    .yellow {
        color: yellow;
    }
    .red {
        color: darkred;
    }
    .bold {
        font-weight: bold;
    }
</style>

<?php if(!empty($_POST['run_sim']) && !is_null($error)):?>
    <div id="error"><?=$error?></div>
<?php endif ?>
<?php if(!is_null($data)): ?>
    <?php if(!is_null($debug_data)): ?>
        <table>
            <tr><th>Debug Data</th></tr>
            <?php foreach($debug_data as $db_p_type => $debug_datum): ?>
                <tr><th><?=System::unSlug($db_p_type)?></th></tr>
                <tr>
                    <td>
                        <?php foreach($debug_datum as $db_week_num => $db_value): ?>
                            <b><?=((is_int($db_week_num)) ? 'Week' : '')?> <?=$db_week_num?></b><br />
                            <?php foreach($db_value as $db_name => $db_string): ?>
                                <label class="indent lrg"><?=$db_name?>:</label><?=$db_string?><br />
                            <?php endforeach ?>
                            <br />
                        <?php endforeach ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>
    <?php endif ?>
    <table>
        <?php foreach($data as $datum): /**@var RepPlayer $datum**/?>
            <tr><th><?=$datum->name?></th></tr>
            <tr>
                <td style="text-align: center;">
                    <?php if($sim_data['show_weekly_data']): ?>
                        <?php foreach($datum->weekly_data as $week => $weekly_datum): ?>
                            <div class="weekly_data">
                                <p class="header">Week #<?=$week?></p>
                                <?php foreach($weekly_datum as $name => $value): ?>
                                    <?php if(str_contains($name, 'structure')): ?>
                                        <?= $value ?>
                                    <?php elseif(str_contains($name, '_rate')): ?>
                                        <?php
                                            $class = calcRateClass($value);
                                        ?>
                                        <?=System::unSlug($name)?>: <span class="<?=$class?>"><?=$value?></span><br />
                                    <?php else: ?>
                                        <?=System::unSlug($name)?>: <?= $value ?><br />
                                    <?php endif ?>
                                <?php endforeach ?>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                    <div class="final_data">
                        <p class="header">Final Data - <?=sizeof($datum->weekly_data)?> Weeks</p>
                        Reputation Rank: <?=$datum->reputation->rank?><br />
                        Reputation Amt: <?=$datum->reputation->getRepAmount()?><br />
                        Total Rep Earned: <?=$datum->total_rep_earned?><br />
                        Total Rep Decayed: <?=$datum->total_rep_lost?>
                    </div>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif ?>

<table style="width: 40%;text-align: center;">
    <tr>
        <th id="top">Simulation Data</th>
    </tr>
    <tr>
        <td>
            <form action="<?=$self_link?>" method="post">
                <input type="submit" name="run_sim" value="Run" />
                <div style="text-align: left; margin-left:5px">
                    <label>Debug<div class="tool-tip" title="Show debugging data">!</div>:</label><input type="checkbox" name="debug" <?=($sim_data['debug'] ? 'checked' : '')?> /><br />
                    <label>Verbose Data<div class="tool-tip" title="Shows expanded data (e.g. weekly data)">!</div>:</label><input type="checkbox" name="show_weekly_data" <?=($sim_data['show_weekly_data'] ? 'checked' : '')?> /><br />
                    <label>Daily PvP<div class="tool-tip" title="Disabled will run weekly cap">!</div>:</label><input type="checkbox" name="daily_pvp_cap" <?=($sim_data['daily_pvp_cap'] ? 'checked' : '')?> /><br />
                    <label>Daily Bypass<div class="tool-tip" title="Daily tasks will bypass weekly caps">!</div>:</label><input type="checkbox" name="daily_task_bypass" <?=($sim_data['daily_task_bypass'] ? 'checked' : '')?> /><br />
                    <label>Decay Mod<div class="tool-tip" title="Rate at which decay is reduced for meeting weekly cap">!</div>:</label><input type="text" name="decay_modifier" value="<?=$sim_data['decay_modifier']?>" /><br />
                    <label>PvP Daily Con<div class="tool-tip" title="Rate at which weekly pvp cap is reduced for daily reset">!</div>:</label><input type="text" name="pvp_daily_con" value="<?=$sim_data['pvp_daily_con']?>" /><br />
                    <label>Avg Dly Amnt<div class="tool-tip" title="Average amount of daily rep rewarded per week for completing ALL daily tasks EVERY DAY">!</div>:</label><input type="text" name="average_weekly_daily" value="<?=$sim_data['average_weekly_daily']?>" /><br />
                    <label>Weeks:</label><input type="text" name="weeks" value="<?=$sim_data['weeks']?>" /><br />
                    <?php foreach($sim_data['player_types'] as $player_type): ?>
                        <div class="playerData">
                            <p class="header <?=$player_type?>"><?=System::unSlug($player_type)?> Player</p>
                            <label class="indent lrg">Weekly Cap Rate:</label><input type="text" name="<?=$player_type?>_weekly_cap_rate"
                                value="<?=$sim_data['weekly_cap_rates'][$player_type]?>"/><br />
                            <label class="indent lrg">PvP Cap Rate:</label><input type="text" name="<?=$player_type?>_pvp_cap_rate"
                                value="<?=$sim_data['weekly_pvp_rates'][$player_type]?>"/>
                            <label class="indent lrg">Daily Comp Rate:</label><input type="text" name="<?=$player_type?>_daily_comp_rate"
                                value="<?=$sim_data['daily_comp_rate'][$player_type]?>"/>
                        </div>
                    <?php endforeach ?>
                    <?php foreach($sim_data['reputation_data'] as $rank_id => $reputation_datum): ?>
                        <?php if(sizeof($sim_data['reputation_data'])%2 != 0 && $rank_id == sizeof($sim_data['reputation_data'])): ?>
                            <div style="display: inline-block; margin-left: 25%;"></div>
                        <?php endif ?>
                        <div class="playerData">
                            <p class="header" style="background-color: gray;"><?=$reputation_datum['title']?> - Tier <?=$rank_id?></p>
                            <label class="smol">Min Rep:</label><input type="text" name="<?=$rank_id?>_min_rep" value="<?=$reputation_datum['min_rep']?>" /><br />
                            <label class="smol">Weekly Cap:</label><input type="text" name="<?=$rank_id?>_weekly_cap" value="<?=$reputation_datum['weekly_cap']?>" /><br />
                            <label class="smol">PvP Cap:</label><input type="text" name="<?=$rank_id?>_pvp_cap" value="<?=$reputation_datum['weekly_pvp_cap']?>" /><br />
                            <label class="smol">Base Decay:</label><input type="text" name="<?=$rank_id?>_base_decay" value="<?=$reputation_datum['base_decay']?>" />
                        </div>
                    <?php endforeach ?>
                </div>
                <a href="#top">Top</a>
            </form>
        </td>
    </tr>
</table>