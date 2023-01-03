<?php
/**
 * @var Item[] $all_items
 * @var string $self_link
 */

$item_type = Item::USE_TYPE_WEAPON;
if(isset($_GET['item_type'])) {
    switch($_GET['item_type']) {
        case 'weapon':
            $item_type = Item::USE_TYPE_WEAPON;
            break;
        case 'armor':
            $item_type = Item::USE_TYPE_ARMOR;
            break;
        case 'consumable':
            $item_type = Item::USE_TYPE_CONSUMABLE;
            break;
    }
}

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
    <a href='<?= $self_link ?>&item_type=weapon' class='<?= ($item_type == Item::USE_TYPE_WEAPON ? 'selected' : "") ?>'>
        Weapons
    </a> |
    <a href='<?= $self_link ?>&item_type=armor' class='<?= ($item_type == Item::USE_TYPE_ARMOR ? 'selected' : "") ?>'>
        Armor
    </a> |
    <a href='<?= $self_link ?>&item_type=consumable' class='<?= ($item_type == Item::USE_TYPE_CONSUMABLE ? 'selected' : "") ?>'>
        Consumables
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
                <a href="<?= $self_link ?>&item_id=<?= $item->id ?>"><?= $item->name ?></a>
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