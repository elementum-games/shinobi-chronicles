<?php
/**
 * @var Item[] $all_items
 * @var string $item_type
 */

$selected_link_style = "text-decoration:none;";

?>
<style>
    .categoryLinks {
        text-align:center;
        margin-top: 20px;
        margin-bottom: -5px;
    }
    .categoryLinks a {
        font-size:14px;
    }
    .categoryLinks a.selected {
        text-decoration: none;
    }
</style>


<p class='categoryLinks'>
    <a href='<?= $system->routerV2->current_route ?>&item_type=<?= Item::USE_TYPE_WEAPON ?>' class='<?= ($item_type == Item::USE_TYPE_WEAPON ? 'selected' : "") ?>'>
        Weapons</a> |
    <a href='<?= $system->routerV2->current_route ?>&item_type=<?= Item::USE_TYPE_ARMOR ?>' class='<?= ($item_type == Item::USE_TYPE_ARMOR ? 'selected' : "") ?>'>
        Armor</a> |
    <a href='<?= $system->routerV2->current_route ?>&item_type=<?= Item::USE_TYPE_CONSUMABLE ?>' class='<?= ($item_type == Item::USE_TYPE_CONSUMABLE ? 'selected' : "") ?>'>
        Consumables</a> |
    <a href='<?= $system->routerV2->current_route ?>&item_type=<?= Item::USE_TYPE_SPECIAL ?>' class='<?= ($item_type == Item::USE_TYPE_SPECIAL ? 'selected' : "") ?>'>
        Special</a> | 
    <a href='<?= $system->routerV2->current_route ?>&item_type=<?= Item::USE_TYPE_CURRENCY ?>' class='<?= ($item_type == Item::USE_TYPE_CURRENCY ? 'selected' : "") ?>'>
        Currency
    </a>
</p>

<table class='table'>
    <tr>
        <th style='width:25%;'>Name</th>
        <th style='width:10%;'>Power</th>
        <th style='width:25%;'>Effect</th>
        <th style='width:20%;'>Cost</th>
    </tr>
    <?php foreach($all_items as $id => $item): ?>
        <?php if($item->use_type != $item_type) continue; ?>
        <tr>
            <td>
                <a href="<?= $system->routerV2->current_route ?>&item_id=<?= $item->id ?>"><?= $item->name ?></a>
            </td>
            <td><?= $item->effect_amount ?></td>
            <td>
                <?= System::unSlug($item->effect) ?>
                (<?= $item->effect_amount . $item->effectDisplayUnit() ?>)
            </td>
            <td>&yen;<?= $item->purchase_cost ?></td>
        </tr>
    <?php endforeach; ?>
</table>