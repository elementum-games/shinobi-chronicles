<?php
ini_set('display_errors', 'On');
/**
 * @var System $system
 * @var User $user
 */
require_once __DIR__ . "/../classes/Currency.php";
require_once __DIR__ . "/_authenticate_admin.php";
$self_link = $system->router->base_url . 'admin/item_cost_helper.php';

$RANKS = [
    1 => "Akademi-sai",
    2 => "Genin",
    3 => "Chuunin",
    4 => "Jonin",
    5 => "Sannin",
];
$DISPLAY_DATA = [];
if(isset($_POST['calc_cost'])) {
    $type = $system->db->clean($_POST['item_type']);
    $rank = (int) $_POST['rank'];
    $jutsu_effect_amount = (float) $_POST['jutsu_effect_amount'];

    if($type == 'item') {
        $ranks = [1, 2, 3, 4, 5];
        if($rank != 0) {
            $ranks = [$rank];
        }
        foreach($ranks as $rank_num) {
            $DISPLAY_DATA[$rank_num] = Currency::calcItemCost($rank_num);
        }
    }
    if($type == 'ramen') {
        $ranks = [1, 2, 3, 4, 5];
        if($rank != 0) {
            $ranks = [$rank];
        }
        foreach($ranks as $rank_num) {
            $village_cost_veg = Currency::calcRamenCost(
                rank_num: $rank_num,
                ramen_type: Item::RAMEN_TYPE_VEGETABLE,
                arena: false
            );
            $village_cost_pork = Currency::calcRamenCost(
                rank_num: $rank_num,
                ramen_type: Item::RAMEN_TYPE_PORK,
                arena: false
            );
            $village_cost_del = Currency::calcRamenCost(
                rank_num: $rank_num,
                ramen_type: Item::RAMEN_TYPE_DELUXE,
                arena: false
            );

            $arena_cost_veg = Currency::calcRamenCost(
                rank_num: $rank_num,
                ramen_type: Item::RAMEN_TYPE_VEGETABLE,
                arena: true
            );
            $arena_cost_pork = Currency::calcRamenCost(
                rank_num: $rank_num,
                ramen_type: Item::RAMEN_TYPE_PORK,
                arena: true
            );
            $arena_cost_del = Currency::calcRamenCost(
                rank_num: $rank_num,
                ramen_type: Item::RAMEN_TYPE_DELUXE,
                arena: true
            );

            $DISPLAY_DATA[$rank_num] = [
                'current_' . Item::RAMEN_TYPE_VEGETABLE => $village_cost_veg,
                'current_' . Item::RAMEN_TYPE_PORK => $village_cost_pork,
                'current_' . Item::RAMEN_TYPE_DELUXE => $village_cost_del,
                'old_' . Item::RAMEN_TYPE_VEGETABLE => $rank_num * 5,
                'old_' . Item::RAMEN_TYPE_PORK => $rank_num * 25,
                'old_' . Item::RAMEN_TYPE_DELUXE => $rank_num * 50,
                'current_arena_' . Item::RAMEN_TYPE_VEGETABLE => $arena_cost_veg,
                'current_arena_' . Item::RAMEN_TYPE_PORK => $arena_cost_pork,
                'current_arena_' . Item::RAMEN_TYPE_DELUXE => $arena_cost_del,
                'old_arena_' . Item::RAMEN_TYPE_VEGETABLE => $rank_num * 5 * 5,
                'old_arena_' . Item::RAMEN_TYPE_PORK => $rank_num * 25 * 5,
                'old_arena_' . Item::RAMEN_TYPE_DELUXE => $rank_num * 50 * 5,
            ];
        }
    }
    if($type == 'jutsu') {
        $ranks = [1, 2, 3, 4, 5];
        if($rank != 0) {
            $ranks = [$rank];
        }

        foreach($ranks as $rank_num) {
            $DISPLAY_DATA[$rank_num] = [
                'cost_no_effect' => [],
                'cost_effect' => [],
            ];
            for($i=0.25;$i<=5;$i+=0.25) {
                $DISPLAY_DATA[$rank_num]['cost_no_effect'][(string)$i] = Currency::calcJutsuScrollCost(
                    jutsu_rank: $rank_num,
                    jutsu_power: $i,
                    effect_amount: 0
                );
                $DISPLAY_DATA[$rank_num]['cost_effect'][(string)$i] = Currency::calcJutsuScrollCost(
                    jutsu_rank: $rank_num,
                    jutsu_power: $i,
                    effect_amount: $jutsu_effect_amount
                );
            }
        }
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
    table.small_width {
        width: 65%;
    }
    table.super_small_width {
        width: 25%;
    }
    table.table th {
        text-align: center;
        text-transform: uppercase;
        background-color: gray;

        border: 1px solid black;
    }
    table.table tr, table.table td {
        border: 1px solid black;
    }
</style>
<?php if(!empty($DISPLAY_DATA)): ?>
    <?php if(isset($type) && $type == 'ramen'): ?>
        <table class="table">
            <tr><th colspan="12">Ramen Costs</th></tr>
            <?php foreach($DISPLAY_DATA as $rank_num => $ramen_data): ?>
                <tr><th colspan="12"><?= $RANKS[$rank_num]?></th></tr>
                <tr style="text-align: center;">
                    <td colspan="6">In-Village</td>
                    <td colspan="6">Arena</td>
                </tr>
                <tr style="text-align: center;">
                    <td>Current <?=Item::RAMEN_TYPE_VEGETABLE?></td>
                    <td>Current <?=Item::RAMEN_TYPE_PORK?></td>
                    <td>Current <?=Item::RAMEN_TYPE_DELUXE?></td>
                    <td>Old <?=Item::RAMEN_TYPE_VEGETABLE?></td>
                    <td>Old <?=Item::RAMEN_TYPE_PORK?></td>
                    <td>Old <?=Item::RAMEN_TYPE_DELUXE?></td>
                    <td>Current <?=Item::RAMEN_TYPE_VEGETABLE?></td>
                    <td>Current <?=Item::RAMEN_TYPE_PORK?></td>
                    <td>Current <?=Item::RAMEN_TYPE_DELUXE?></td>
                    <td>Old <?=Item::RAMEN_TYPE_VEGETABLE?></td>
                    <td>Old <?=Item::RAMEN_TYPE_PORK?></td>
                    <td>Old <?=Item::RAMEN_TYPE_DELUXE?></td>
                </tr>
                <tr style="text-align: center;">
                    <?php foreach($ramen_data as $ramen_string => $ramen_cost): ?>
                        <td><?=$user->currency->money->symbol.$ramen_cost?></td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
        </table>
    <?php elseif(isset($type) && $type == 'jutsu'): ?>
        <table class="table">
            <tr><th colspan="21">Jutsu Costs</th></tr>
            <tr>
                <th colspan="1"></th>
                <th colspan="20">Power<?=(($jutsu_effect_amount > 0) ? " - <em>(Effect Amount $jutsu_effect_amount)</em>" : "")?></th>
            </tr>
            <tr>
                <th style="width: 8%;">Rank</th>
                <?php for($i=0.25;$i<=5;$i+=0.25): ?>
                    <th style="width:4.5%;"><?=$i?></th>
                <?php endfor ?>
            </tr>
            <?php foreach($DISPLAY_DATA as $rank_num => $cost_data): ?>
                <tr style="text-align: center;">
                    <td><?=$RANKS[$rank_num]?></td>
                    <?php foreach($cost_data['cost_no_effect'] as $x => $cost): ?>
                        <td>
                            <?=$user->currency->money->symbol.$cost?>
                            <?php if($jutsu_effect_amount > 0): ?>
                            <br />
                                <em>(<?=$user->currency->money->symbol.$cost_data['cost_effect'][$x]?>)*</em>
                            <?php endif?>
                        </td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
            <tr><td colspan="21"><em>*Jutsu cost w/effect</em></td></tr>
        </table>
    <?php elseif(isset($type) && $type == 'item'): ?>
        <table class='table super_small_width'>
            <tr>
                <th>Rank</th>
                <th>Cost</th>
            </tr>
            <?php foreach($DISPLAY_DATA as $rank_num => $cost): ?>
                <tr style="text-align:center;">
                    <td><?=$RANKS[$rank_num]?></td>
                    <td><?=$user->currency->money->symbol.$cost?></td>
                </tr>
            <?php endforeach ?>
        </table>
    <?php endif ?>
<?php endif ?>
<form action='<?=$self_link?>' method='post'>
    Item Type: <select name='item_type'>
        <option value='ramen' <?=(isset($type) && $type=='ramen' ? 'selected' : '')?>>Ramen</option>
        <option value='jutsu' <?=(isset($type) && $type=='jutsu' ? 'selected' : '')?>>Jutsu</option>
        <option value='item'  <?=(isset($type) && $type=='item' ? 'selected' : '')?>>Item/Consumable</option>
    </select><br />
    Rank: <select name='rank'>
        <option value='0' <?=(isset($rank) && $rank==0 ? 'selected' : '')?>>All Ranks</option>
        <option value='1' <?=(isset($rank) && $rank==1 ? 'selected' : '')?>>Akademi-sai</option>
        <option value='2' <?=(isset($rank) && $rank==2 ? 'selected' : '')?>>Genin</option>
        <option value='3' <?=(isset($rank) && $rank==3 ? 'selected' : '')?>>Chuunin</option>
        <option value='4' <?=(isset($rank) && $rank==4 ? 'selected' : '')?>>Jonin</option>
        <option value='5' <?=(isset($rank) && $rank==5 ? 'selected' : '')?>>Senin</option>
    <select><br />
    Effect Amount (jutsu only): <input type='text' name='jutsu_effect_amount' value='<?=($jutsu_effect_amount ?? 0)?>' /><br />
    <input type='submit' name='calc_cost' value='Calculate' />
</form>