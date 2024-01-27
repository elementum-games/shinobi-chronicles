<?php
/**
 * @var System $system
 * @var User $player
 * @var ?int $jutsu_id_to_view
 * @var ?string $jutsu_type_to_view
 * @var string $view
 * @var array $RANK_NAMES
 * @var array $shop_jutsu
 * @var array $shop_items
 *
 * @var int $max_consumables
 */
?>


<div class='submenu'>
    <ul class='submenu'>
        <li style='width:31%;'><a href='<?= $system->router->getUrl('store', ['view' => 'jutsu']) ?>'>Jutsu Scrolls</a></li>
        <li style='width:31%;'><a href='<?= $system->router->getUrl('store', ['view' => 'gear']) ?>'>Gear</a></li>
        <li style='width:36%;'><a href='<?= $system->router->getUrl('store', ['view' => 'consumables']) ?>'>Consumables</a></li>
    </ul>
</div>
<div class='submenuMargin'></div>
<?php $system->printMessage(); ?>

<?php if(!empty($jutsu_to_view)): ?>
    <?php if(!empty($_GET['view_jutsu'])): ?>
        <table class='table'>
            <tr><th>
                <?= $jutsu_to_view['name'] ?>
                (<a href='<?= $system->router->getUrl('store', ['jutsu_type' => $jutsu_to_view['jutsu_type']]) ?>'>Return</a>)
            </th></tr>
            <tr><td>
                <label style='width:6.5em;'>Rank:</label><?= $RANK_NAMES[$jutsu_to_view['rank']] ?><br />
                <?php if($jutsu_to_view['parent_jutsu']): ?>
                <label style='width:6.5em;'>Parent Jutsu:</label><?= $shop_jutsu[$jutsu_to_view['parent_jutsu']]['name'] ?><br />
                <?php endif; ?>
                <?php if($jutsu_to_view['element'] != 'None'): ?>
                    <label style='width:6.5em;'>Element:</label>
                    <span style='
                        color:<?=
                            $player->elements && in_array($jutsu_to_view['element'], $player->elements)
                            ? "#00C000"
                            : "#C00000"
                        ?>;
                        font-weight:bold;'
                    >
                        <?= $jutsu_to_view['element'] ?>
                    </span>
                    <br />
                <?php endif; ?>
                    <label style='width:6.5em;'>Use cost:</label><?= $jutsu_to_view['use_cost'] ?><br />
                    <?php if($jutsu_to_view['cooldown']): ?>
                        <label style='width:6.5em;'>Cooldown:</label><?= $jutsu_to_view['cooldown'] ?> turn(s)<br />
                    <?php endif; ?>
                    <?php if($jutsu_to_view['effect'] || $jutsu_to_view['use_type'] == Jutsu::USE_TYPE_BARRIER): ?>
                        <label style='width:6.5em;'>Effect:</label><?= $jutsu_to_view['use_type'] == Jutsu::USE_TYPE_BARRIER ? "Barrier" : System::unSlug($jutsu_to_view['effect']) ?><br />
                    <?php endif; ?>
                    <?php if ($jutsu_to_view['effect2'] != "none"): ?>
                        <label style='width:6.5em;'>Effect:</label><?= System::unSlug($jutsu_to_view['effect2']) ?><br />
                    <?php endif; ?>
                    <label style='width:6.5em;float:left;'>Description:</label>
                        <p style='display:inline-block;margin:0;width:37.1em;'><?= $jutsu_to_view['description'] ?></p>
                    <br style='clear:both;' />
                    <label style='width:6.5em;'>Jutsu type:</label><?= System::unSlug($jutsu_to_view['jutsu_type']) ?>
                    <?php if(count($jutsu_to_view['child_jutsu_names']) > 0): ?>
                        <br />
                        <br /><label>Learn <b><?= $jutsu_to_view['name'] ?></b> to level 50 to unlock:</label>
                            <p style='margin-left:10px;margin-top:5px;'>
                            <?php foreach($jutsu_to_view['child_jutsu_names'] as $child_jutsu_name): ?>
                                <?= $child_jutsu_name ?>
                                <br />
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>

            </td></tr>
        </table>
    <?php endif; ?>

<?php elseif($view == 'jutsu'): ?>

<table class='table'><tr><th>Jutsu Scrolls</th></tr>
    <tr><td style='text-align:center;'>
        You can buy Jutsu Scrolls in this section for any jutsu of your rank or below.
        Once you have purchased a scroll, go to the Jutsu page to learn the jutsu.<br />
        <br />
        <b>Your Yen:</b> &yen;<?= number_format($player->getMoney()) ?>
    </td></tr>
</table>

<h2>
    <div style='text-align:center;margin-bottom:0;'>
        <a
            href='<?= $system->router->getUrl('store', ['view' => 'jutsu', 'jutsu_type' => 'ninjutsu']) ?>'
            class='<?= ($jutsu_type_to_view == 'ninjutsu' ? 'selected' : "") ?>'
        > Ninjutsu</a> |
        <a
            href='<?= $system->router->getUrl('store', ['view' => 'jutsu', 'jutsu_type' => 'taijutsu']) ?>'
            class='<?= ($jutsu_type_to_view == 'taijutsu' ? 'selected' : "") ?>'
        > Taijutsu</a> |
        <a
            href='<?= $system->router->getUrl('store', ['view' => 'jutsu', 'jutsu_type' => 'genjutsu']) ?>'
            class='<?= ($jutsu_type_to_view == 'genjutsu' ? 'selected' : "") ?>'
        > Genjutsu</a>
    </div>
</h2>

<table class='table' style='margin-top:15px;'>
    <tr id='shop_table_header'>
        <th style='width:25%;'>Name</th>
        <th style='width:15%;'>Effect(s)</th>
        <th style='width:10%;'>Type</th>
        <th style='width:10%;'>Element</th>
        <th style='width:10%;'>Cost</th>
        <th style='width:10%;'></th>
    </tr>

    <?php if(count($shop_jutsu) < 1): ?>
        <tr><td colspan='5'>No jutsu found!</td></tr>
    <?php else: ?>
        <?php
            $jutsu_displayed = 0;
            $rank = current($shop_jutsu)['rank'];
        ?>

        <?php foreach($shop_jutsu as $id => $jutsu): ?>
            <?php if($jutsu_type_to_view && $jutsu['jutsu_type'] != $jutsu_type_to_view) continue; ?>
            <?php if($player->hasJutsu($jutsu['jutsu_id'])) continue; ?>
            <?php if(isset($player->jutsu_scrolls[$jutsu['jutsu_id']])) continue; ?>

            <?php $jutsu_displayed++; ?>

            <tr class='table_multicolumns'>
                <td style='width:30%; text-align:center;'>
                    <a href='<?= $system->router->getUrl('store', ['view' => 'jutsu', 'view_jutsu' => $jutsu['jutsu_id']])?>'>
                        <?= $jutsu['name'] ?>
                    </a>
                </td>
                <td style='width:25%; text-align:center;'>
                    <?php echo ($jutsu['use_type'] == Jutsu::USE_TYPE_BARRIER ? "Barrier" : System::unSlug($jutsu['effect'])) . ($jutsu['effect2'] != "none" ? "<br>" . System::unSlug($jutsu['effect2']) : "") ?>
                </td>
                <td style='width:25%; text-align:center;'><?= System::unSlug($jutsu['jutsu_type']) ?></td>
                <td style='width:25%; text-align:center;'><?= System::unSlug($jutsu['element']) ?></td>
                <td style='width:25%; text-align:center;'>&yen;<?= number_format($jutsu['purchase_cost']) ?></td>
                <td style='width:25%; text-align:center;'>
                    <a
                        href='<?= $system->router->getUrl('store', [
                            'view' => 'jutsu',
                            'purchase_jutsu' => $jutsu['jutsu_id'],
                            'jutsu_type' => $jutsu['jutsu_type']
                        ])?>'
                        style='text-align:center;'
                    >Purchase</a>
            </tr>
        <?php endforeach; ?>

        <?php if($jutsu_displayed == 0): ?>
        <tr><td colspan='4'>No jutsu available!</td></tr>
        <?php endif; ?>
    <?php endif; ?>
    </table>
<?php elseif($view == 'gear' || $view == 'consumables'): ?>
    <table class='table'><tr><th><?= ucwords($view) ?></th></tr>
        <tr><td style='text-align:center;'>
            You can buy armor/consumable items in this section for your rank or below.<br />
            <br />
            <b>Your Yen:</b> &yen;<?= number_format($player->getMoney()) ?>
        </td></tr>
    </table>
    <table class='table'>
        <tr>
            <th style='width:35%;'>Name</th>
            <th style='width:25%;'>Effect</th>
            <th style='width:20%;'>Cost</th>
            <th style='width:20%;'></th>
        </tr>

        <?php if(count($shop_items) < 0): ?>
            <tr><td colspan='4'>No items found!</td></tr>
        <?php else: ?>
            <?php $items_displayed = 0; ?>
            <?php foreach($shop_items as $item): ?>
                <?php
                    /** @var Item $item */
                    if($item->use_type == Item::USE_TYPE_CONSUMABLE && $view != 'consumables') {
                        continue;
                    }
                    else if(($item->use_type == Item::USE_TYPE_WEAPON || $item->use_type == Item::USE_TYPE_ARMOR) && $view != 'gear') {
                        continue;
                    }
                    else if($item->use_type == Item::USE_TYPE_SPECIAL && $view != 'gear') {
                        continue;
                    }

                    if($view != 'consumables' && $player->hasItem($item->id)) {
                        continue;
                    }

                    $items_displayed++;

                    if($view == 'consumables' && $player->hasItem($item->id)) {
                        $owned = $player->items[$item->id]->quantity;
                    }
                    else {
                        $owned = 0;
                    }
                ?>

                <tr class='table_multicolumns' style='text-align:center;'>
                    <td style='width:35%;'><?= $item->name ?>
                        <?php if($owned): ?>
                            <br />(Owned: <?= $owned ?>/<?= $item->max_quantity ?>)
                        <?php endif; ?>
                    </td>
                    <td style='width:25%;'><?= System::unSlug($item->effect) ?></td>
                    <td style='width:20%; text-align:center;'>&yen;<?= number_format($item->purchase_cost) ?></td>
                    <td style='width:20%;'>
                        <a class='button' href='<?= $system->router->getUrl('store', ['view' => $view, 'purchase_item' => $item->id])?>'>
                            Purchase <?= ($view == 'consumables' ? "1" : "") ?>
                        </a>
                        <?php if($view == 'consumables'): ?>
                            <br />
                            <a
                                class='button'
                                style='margin-bottom:1px;'
                                href='<?= $system->router->getUrl('store', [
                                    'view' => $view,
                                    'purchase_item' => $item->id,
                                    'max' => 'true'
                                ])?>'
                            >
                                Purchase Max
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if($items_displayed == 0): ?>
                <tr><td colspan='4'>No items available!</td></tr>
            <?php endif; ?>
        <?php endif; ?>
    </table>
<?php endif; ?>
