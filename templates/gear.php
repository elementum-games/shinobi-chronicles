<?php
/**
 * @var System $system
 * @var User $player
 * @var int $max_equipped_weapons
 * @var int $max_equipped_armor
 */
?>

<?php $system->printMessage(); ?>

<form action='<?= $system->router->getUrl('gear') ?>' method='post' style='margin:0;'>
<table id='equipment gear' class='table' style='text-align:center;'>
    <tr class='threeColumns'>
        <th style='width:33%;'>Weapons</th>
        <th style='width:33%;'>Armor</th>
        <th style='width:33%;'>Consumables</th>
    </tr>
    <tr class='threeColumns'>
        <td>
            <?php if($player->items): ?>
                <?php foreach($player->items as $item): ?>
                    <?php if($item->use_type != Item::USE_TYPE_WEAPON) continue; ?>

                    <?= $item->name ?>
                    <sup style='font-size:9px;'>(<?= $item->effect_amount ?> <?= System::unSlug($item->effect) ?>)</sup>
                    <br />
                <?php endforeach; ?>
            <?php endif; ?>
        </td>
        <td>
            <?php if($player->items): ?>
                <?php foreach($player->items as $item): ?>
                    <?php if($item->use_type != Item::USE_TYPE_ARMOR) continue; ?>

                    <?= $item->name ?>
                    <sup style='font-size:9px;'>(<?= $item->effect_amount ?> <?= System::unSlug($item->effect) ?>)</sup>
                    <br />
                <?php endforeach; ?>
            <?php endif; ?>
        </td>
        <td>
            <?php if($player->items): ?>
                <?php foreach($player->items as $item): ?>
                    <?php if($item->use_type != Item::USE_TYPE_CONSUMABLE) continue; ?>

                    <?= $item->name ?>
                    <sup style='font-size:9px;'>(<?= $item->effect_amount ?> <?= System::unSlug($item->effect) ?>)</sup>
                    <br />
                <?php endforeach; ?>
            <?php endif; ?>
        </td>
    </tr>

    <?php if($player->special_items): ?>
        <tr><th colspan='3'>Special</th></tr>
        <tr><td colspan='3' style='text-align:center;'>
            <?php foreach($player->items as $item): ?>
                <?php if($item->use_type != Item::USE_TYPE_SPECIAL) continue; ?>

                <?= $item->name ?>
                <?php if ($item->quantity > 1): ?>
                    &nbsp;x<?= $item->quantity ?>
                <?php endif; ?>
                <?php if ($item->effect_amount > 0): ?>
                    <sup style='font-size:9px;'>(<?= $item->effect_amount ?> <?= $item->effect ?>)</sup>
                <?php endif; ?>
                <br />
            <?php endforeach; ?>
        </td></tr>
    <?php endif; ?>
    
    <tr class='twoHeaders'>
        <!--<th>Equipped Weapons</th>-->
        <th colspan='2'>Equipped Gear</th>
        <th>Use Items</th>
    </tr>
    <tr class='threeColumns'>
        <?php $item_count = 1; ?>
       <!-- <td class='fullwidth' style='width:33%;'>
            <?php /*for($i = 0; $i < $max_equipped_weapons; $i++): */?>
                <select style='margin-top: 7px' name='items[<?/*= $item_count++ */?>]'>
                    <option value='none'>None</option>
                    <?php /*foreach($player->items as $item): */?>
                        <?php /*if($item->use_type != Item::USE_TYPE_WEAPON) continue; */?>
                        <option value='<?/*= $item->id */?>'
                            <?php /*if(!empty($player->equipped_weapon_ids[$i]) && $item->id == $player->equipped_weapon_ids[$i]): */?>
                                selected='selected'
                            <?php /*endif; */?>
                        >
                            <?/*= $item->name */?>
                        </option>
                    <?php /*endforeach; */?>
                </select>
                <br />
            <?php /*endfor; */?>
        </td>-->
        <td class='fullwidth' style='width:66%;' colspan='2'>
            <?php for($i = 0; $i < $max_equipped_armor; $i++): ?>
                <select style='margin-top: 7px' name='items[<?= $item_count++ ?>]'>
                    <option value='none'>None</option>
                    <?php foreach($player->items as $item): ?>
                        <?php if($item->use_type != Item::USE_TYPE_ARMOR) continue; ?>
                        <option value='<?= $item->id ?>'
                            <?php if(!empty($player->equipped_armor_ids[$i]) && $item->id == $player->equipped_armor_ids[$i]): ?>
                                selected='selected'
                            <?php endif; ?>
                        >
                            <?= $item->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br />
            <?php endfor; ?>
            </td>
        <td class='fullwidth' style='text-align:center;'>
            <div style='display: flex; gap: 10px; align-items: center; justify-content: center; flex-direction: column'>
                <?php foreach($player->items as $id => $item): ?>
                    <?php if($item->use_type != Item::USE_TYPE_CONSUMABLE) continue; ?>
                    <?php if($item->quantity <= 0) continue; ?>

                    <a href='<?= $system->router->getUrl('gear', ['use_item' => $id]) ?>'>
                        <span class='button' style='min-width:8em; margin: 0'><?= $item->name ?><br />
                            <span style='font-weight:normal;'>Amount: <?= $item->quantity ?></span><br/>
                            <?php if($item->effect == 'heal'): ?>
                                <span style='font-weight:normal;'>(Heal <?= $item->effect_amount ?> HP)</span>
                            <?php endif; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </td>
    <tr><td colspan='3' style='text-align:center;'>
        <input type='submit' name='equip_item' value='Equip' />
    </td></tr>
</table>
