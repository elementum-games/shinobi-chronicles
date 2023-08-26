<?php
ini_set('display_errors', 'On');
/**
 * @var System $system
 * @var User $user
 */
require_once __DIR__ . "/../classes/Currency.php";
require_once __DIR__ . "/_authenticate_admin.php";
$self_link = $system->router->base_url . 'admin/money_helper.php';
$DISPLAY_DATA = [];
$RANKS = [
    1 => "Akademi-sai",
    2 => "Genin",
    3 => "Chuunin",
    4 => "Jonin",
    5 => "Sannin",
];
if(isset($_POST['calc_gain'])) {
    $type = 'gain';
    $multiplier = (int) $_POST['multiplier'];
    $multiple_of = (int) $_POST['multiple_of'];
    $rank = (int) $_POST['rank'];

    $DISPLAY_DATA = [
        'rank' => $rank,
        'multiplier' => $multiplier,
        'multiple_of' => $multiple_of,
		'raw' => Currency::calcRawYenGain(rank_num: $rank, multiplier: $multiplier),
		'final_result' => Currency::getRoundedYen(rank_num: $rank, multiplier: $multiplier, multiple_of: $multiple_of)
    ];
}
else if(isset($_POST['special_mission'])) {
    $rank = (int) $_POST['rank'];
	$difficulty = $system->db->clean($_POST['difficulty']);
	$battle_multiple = (int) $_POST['battle_multiple'];
	$mission_multiple = (int) $_POST['mission_multiple'];
	
	$raw_complete = Currency::calcRawYenGain(
		rank_num: $rank,
		multiplier: Currency::getSpecialMissionMultiplier(difficulty: $difficulty)
	);
	
	$avg_complete = Currency::roundYen(
		num: $raw_complete,
		multiple_of: $mission_multiple
	);
	
	$raw_battle = Currency::calcSpecialMissionBattleGain(
		user_rank: $rank,
		difficulty: $difficulty
	);
	
	$avg_battle = Currency::roundYen(
		num: $raw_battle,
		multiple_of: $battle_multiple
	);

    $DISPLAY_DATA = [
        'rank' => $rank,
		'difficulty' => $difficulty,
		'battle_multiple' => $battle_multiple,
		'mission_multiple' => $mission_multiple,
		'div_1' => "<br />",
		
		'raw_complete' => $raw_complete,
		'final_lowest_rate' => $user->currency->money->symbol.Currency::roundYen(
			num: $raw_complete * (SpecialMission::MISSION_COMPLETE_RANDOMNESS + .1),
			multiple_of: $mission_multiple
		),
		'final_complete_average' => $avg_complete,
		'final_highest_rate' => $user->currency->money->symbol.Currency::roundYen(
			num: $raw_complete * (SpecialMission::MISSION_COMPLETE_RANDOMNESS + .4),
			multiple_of: $mission_multiple
		),
		'div_2' => "<br />",
		
		'raw_battle' => $raw_battle,
		'battle_lowest_rate' => $user->currency->money->symbol.Currency::roundYen(
			num: $raw_battle * (SpecialMission::BATTLE_RANDOMNESS + .1),
			multiple_of: $battle_multiple
		),
		'battle_average_rate' => $avg_battle,
		'battle_highest_rate' => $user->currency->money->symbol.Currency::roundYen(
			num: $raw_battle * (SpecialMission::BATTLE_RANDOMNESS + .4),
			multiple_of: $battle_multiple
		),
		
		'div_3' => "<br />",
		'Typical Gain' => $user->currency->money->symbol.($avg_battle * 9) + $avg_complete
    ];
}
else if(isset($_POST['calc_arena'])) {
    $multiple_of = (int) $_POST['multiple_of'];
    $arena_min = (int) $_POST['arena_min'];
    $arena_avg = (int) $_POST['arena_avg'];
    $arena_max = (int) $_POST['arena_max'];
    $type = 'arena_gains';

    for($i = 1; $i<6; $i++) {
        $DISPLAY_DATA[$i] = [
            Currency::getRoundedYen(
                rank_num: $i,
                multiplier: $arena_min,
                multiple_of: $multiple_of
            ),
            Currency::getRoundedYen(
                rank_num: $i,
                multiplier: $arena_avg,
                multiple_of: $multiple_of
            ),
            Currency::getRoundedYen(
                rank_num: $i,
                multiplier: $arena_max,
                multiple_of: $multiple_of
            )
        ];
    }
}
?>
<style>
    body {
        width: 100%;
        height: 100%;
        padding: 0;
        margin: 0;
    }
    table.table {
        width: 95%;

        margin: 5px auto;
        border: 1px solid black;

        border-collapse: collapse;
    }
    table.table th {
        text-align: center;
        text-transform: uppercase;
        background-color: gray;
    }
    table.table tr, table.table td {
        border: 1px solid black;
    }
    table.small_width {
        width: 35%;
    }
</style>
<?php if(!empty($DISPLAY_DATA)): ?>
    <?php if(isset($type) && $type == 'arena_gains'): ?>
        <table class="table small_width">
            <tr><th colspan="4">Arena Gains</th></tr>
            <tr>
                <th>Rank</th>
                <th>Lowest Gain</th>
                <th>Average Gain</th>
                <th>Max Gain</th>
            </tr>
            <?php foreach($DISPLAY_DATA as $rank => $arena_data): ?>
                <tr style="text-align: center;">
                    <td><?=$RANKS[$rank]?></td>
                    <?php foreach($arena_data as $gain): ?>
                        <td><?=$user->currency->money->symbol.$gain?></td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
        </table>
    <?php elseif(isset($type) && $type == 'gain'): ?>
        <table class="table small_width">
            <tr><th colspan="4">Currency Gain - <?=$RANKS[$DISPLAY_DATA['rank']]?></th></tr>
            <tr>
                <th>Final Gain</th>
                <th>Raw Gain</th>
                <th>Multiplier</th>
                <th>Multiple</th>
            </tr>
            <tr style="text-align: center;">
                <td><?=$user->currency->money->symbol.$DISPLAY_DATA['final_result']?></td>
                <td><?=$user->currency->money->symbol.$DISPLAY_DATA['raw']?></td>
                <td><?=$DISPLAY_DATA['multiplier']?></td>
                <td><?=$DISPLAY_DATA['multiple_of']?></td>
            </tr>
        </table>
    <?php else: ?>
        <?php foreach($DISPLAY_DATA as $name => $value): ?>
            <?php if(!str_contains($name, 'div_')): ?>
                <?=$name?>:
            <?php endif ?>
            <?=$value?><br />
        <?php endforeach ?>
    <?php endif ?>
<br /><br />
<?php endif ?>
<table class="table">
    <tr>
        <th style="width: 31%;">Money Gains</th>
        <th style="width: 31%">Arena Gains</th>
        <th style="width: 31%;">Special Mission Gains</th>
    </tr>
    <tr>
        <td style="padding: 10px; max-width:49%;">
            <form action="<?=$self_link?>" method="post">
                Rank: <select name="rank">
                    <option value='1' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 1) ? "selected" : ""?>>Akademi-sai</option>
                    <option value='2' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 2) ? "selected" : ""?>>Genin</option>
                    <option value='3' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 3) ? "selected" : ""?>>Chuunin</option>
                    <option value='4' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 4) ? "selected" : ""?>>Jonin</option>
                    <option value='5' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 5) ? "selected" : ""?>>Sennin</option>
                </select><br />
                Multiplier: <input type='text' name='multiplier' value='<?=($DISPLAY_DATA['multiplier'])??1?>' /><br />
                Multiple Of: <input type='text' name='multiple_of' value='<?=($DISPLAY_DATA['multiple_of'])??1?>' /><br />
                <input type='submit' name='calc_gain' value='Run' />
            </form>
        </td>
        <td style="padding: 10px; max-width:49%;">
            <form action="<?=$self_link?>" method="post">
                Multiple Of: <input type='text' name='multiple_of' value='<?=($multiple_of)??5?>' /><br />
                Arena min multiplier: <input type='text' name='arena_min' value='<?=($arena_min)??1?>' /><br />
                Arena average: <input type='text' name='arena_avg' value='<?=($arena_avg)??3?>' /><br />
                Arena max: <input type='text' name='arena_max' value='<?=($arena_max)??5?>' /><br />
                <input type='submit' name='calc_arena' value='Run' />
            </form>
        </td>
        <td style="padding: 10px;">
            <form action="<?=$self_link?>" method="post">
                Rank: <select name="rank">
                    <option value='1' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 1) ? "selected" : ""?>>Akademi-sai</option>
                    <option value='2' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 2) ? "selected" : ""?>>Genin</option>
                    <option value='3' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 3) ? "selected" : ""?>>Chuunin</option>
                    <option value='4' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 4) ? "selected" : ""?>>Jonin</option>
                    <option value='5' <?=(isset($DISPLAY_DATA['rank']) && $DISPLAY_DATA['rank'] == 5) ? "selected" : ""?>>Sennin</option>
                </select><br />
                SM Difficulty: <select name="difficulty">
                    <option value='easy' <?=(isset($DISPLAY_DATA['difficulty']) && $DISPLAY_DATA['difficulty'] == 'easy') ? "selected" : ""?>>Easy</option>
                    <option value='normal' <?=(isset($DISPLAY_DATA['difficulty']) && $DISPLAY_DATA['difficulty'] == 'normal') ? "selected" : ""?>>Normal</option>
                    <option value='hard' <?=(isset($DISPLAY_DATA['difficulty']) && $DISPLAY_DATA['difficulty'] == 'hard') ? "selected" : ""?>>Hard</option>
                    <option value='nightmare' <?=(isset($DISPLAY_DATA['difficulty']) && $DISPLAY_DATA['difficulty'] == 'nightmare') ? "selected" : ""?>>Nightmare</option>
                </select><br />
                Battle Multiple: <input type='text' name='battle_multiple' value='<?=($DISPLAY_DATA['battle_multiple'])??SpecialMission::BATTLE_ROUND_MONEY_TO?>' /><br />
                Complete Multiple: <input type='text' name='mission_multiple' value='<?=($DISPLAY_DATA['mission_multiple'])??SpecialMission::MISSION_COMPLETE_ROUND_MONEY_TO?>' /><br />
                <input type='submit' name='special_mission' value='Run' />
            </form>
        </td>
    </tr>
</table>