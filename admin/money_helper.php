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
if(isset($_POST['calc_gain'])) {
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
	
	$raw_battle = Currency::calcSpecialMissionBattleGain(
		user_rank: $rank,
		difficulty: $difficulty
	);
	/*$yen_gain = Currency::roundYen(
		num: $yen_gain * self::BATTLE_RANDOMNESS + (mt_rand(1, 3) / 10),
		multiple_of: self::BATTLE_ROUND_MONEY_TO
	);*/

    $DISPLAY_DATA = [
        'rank' => $rank,
		'difficulty' => $difficulty,
		'battle_multiple' => $battle_multiple,
		'mission_multiple' => $mission_multiple,
		'div_1' => "<br />",
		
		'raw_complete' => $raw_complete,
		'final_lowest_rate' => Currency::roundYen(
			num: $raw_complete * (SpecialMission::MISSION_COMPLETE_RANDOMNESS + .1),
			multiple_of: SpecialMission::MISSION_COMPLETE_ROUND_MONEY_TO
		),
		'final_complete_average' => Currency::roundYen(
			num: $raw_complete,
			multiple_of: SpecialMission::MISSION_COMPLETE_ROUND_MONEY_TO
		),
		'final_highest_rate' => Currency::roundYen(
			num: $raw_complete * (SpecialMission::MISSION_COMPLETE_RANDOMNESS + .4),
			multiple_of: SpecialMission::MISSION_COMPLETE_ROUND_MONEY_TO
		),
		'div_2' => "<br />",
		
		'raw_battle' => $raw_battle,
		'battle_lowest_rate' => Currency::roundYen(
			num: $raw_battle * (SpecialMission::BATTLE_RANDOMNESS + .1),
			multiple_of: SpecialMission::BATTLE_ROUND_MONEY_TO
		),
		'battle_average_rate' => Currency::roundYen(
			num: $raw_battle,
			multiple_of: SpecialMission::BATTLE_ROUND_MONEY_TO
		),
		'battle_highest_rate' => Currency::roundYen(
			num: $raw_battle * (SpecialMission::BATTLE_RANDOMNESS + .3),
			multiple_of: SpecialMission::BATTLE_ROUND_MONEY_TO
		),
    ];
}
?>

<?php if(!empty($DISPLAY_DATA)): ?>
    <?php foreach($DISPLAY_DATA as $name => $value): ?>
		<?php if(!str_contains($name, 'div_')): ?>
			<?=$name?>:
		<?php endif ?>
		<?=$value?><br />
	<?php endforeach ?>
<br /><br />
<?php endif ?>
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
    Battle Multiple: <input type='text' name='battle_multiple' value='<?=($DISPLAY_DATA['multiple_of'])??5?>' /><br />
	Complete Multiple: <input type='text' name='mission_multiple' value='<?=($DISPLAY_DATA['multiple_of'])??25?>' /><br />
    <input type='submit' name='special_mission' value='Run' />
</form>