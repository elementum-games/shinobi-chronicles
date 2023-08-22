<?php
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
    ];
}
?>

<?php if(!empty($DISPLAY_DATA)): ?>
    Rank: <?=$DISPLAY_DATA['rank']?><br />
    Multiplier: <?=$DISPLAY_DATA['multiplier']?><br />
    Multiple: <?=$DISPLAY_DATA['multiple_of']?><br />
    Raw: <?=Currency::calcRawYenGain(rank_num: $DISPLAY_DATA['rank_num'], multiplier: $DISPLAY_DATA['multiplier'])?><br />
    Final Result: <?=Currency::getRoundedYen(rank_num: $DISPLAY_DATA['rank_num'], multiplier: $DISPLAY_DATA['multiplier'], multiple_of: $DISPLAY_DATA['multiple_of'])?>
<?php endif ?>
<form action="<?=$self_link?>" method="post">
    Rank: <select name="rank">
        <option value='1'>Akademi-sai</option>
        <option value='2'>Genin</option>
        <option value='3'>Chuunin</option>
        <option value='4'>Jonin</option>
        <option value='5'>Sennin</option>
    </select><br />
    Multiplier: <input type='text' name='multiplier' /><br />
    Multiple Of: <input type='text' name='multiple_of' /><br />
    <input type='submit' name='calc_gain' value='Run' />
</form>