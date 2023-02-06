<?php

/**
 * @var Jutsu[] $all_jutsu
 * @var string $self_link
 * @var string[] $RANK_NAMES
 */

$jutsu_type = 'ninjutsu';
if(!empty($_GET['jutsu_type'])) {
    switch($_GET['jutsu_type']) {
        case 'ninjutsu':
            $jutsu_type = 'ninjutsu';
            break;
        case 'taijutsu':
            $jutsu_type = 'taijutsu';
            break;
        case 'genjutsu':
            $jutsu_type = 'genjutsu';
            break;
    }
}

$selected_link_style = "text-decoration:none;";

?>

<!-- // Filter links -->
<p style='text-align:center;margin-top:20px;margin-bottom:-5px;'>
    <a href='<?= $self_link ?>&jutsu_type=ninjutsu'
       style='font-size:14px;<?= ($jutsu_type == 'ninjutsu' ? $selected_link_style : "") ?>'>
        Ninjutsu
    </a> |
    <a href='<?= $self_link ?>&jutsu_type=taijutsu'
       style='font-size:14px;<?= ($jutsu_type == 'taijutsu' ? $selected_link_style : "") ?>'>
        Taijutsu
    </a> |
    <a href='<?= $self_link ?>&jutsu_type=genjutsu'
       style='font-size:14px;<?= ($jutsu_type == 'genjutsu' ? $selected_link_style : "") ?>'>
        Genjutsu
    </a>
</p>

<table class='table'>
    <tr>
        <th style='width:25%;'>Name</th>
        <th style='width:8%;'>Power</th>
        <th style='width:30%;'>Effect</th>
        <th style='width:18%;'>Element</th>
        <th style='width:19%;'>Cost</th>
    </tr>
    <tr><th colspan='5'><?= $RANK_NAMES[1] ?></th></tr>
    <?php $current_rank = 1; ?>
    <?php foreach($all_jutsu as $id => $jutsu): ?>
        <?php
        if($jutsu->jutsu_type != $jutsu_type) {
            continue;
        }
        if($jutsu->rank > $current_rank) {
            $current_rank = $jutsu->rank;
            echo "<tr><th colspan='5'>$RANK_NAMES[$current_rank]</th></tr>";
        }
        ?>
        <tr>
            <td>
                <a href="<?= $self_link ?>&jutsu_id=<?= $jutsu->id ?>"><?= $jutsu->name ?></a>
            </td>
            <td><?= $jutsu->power ?></td>
            <td>
                <?php if($jutsu->use_type == Jutsu::USE_TYPE_BARRIER): ?>
                    Barrier
                <?php else: ?>
                    <?= System::unSlug($jutsu->effect) ?>
                    <?php if($jutsu->effect !== 'none'): ?>
                        (<?= $jutsu->effect_amount ?>% / <?= $jutsu->effect_length ?> turns)
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <td><?= ucwords($jutsu->element) ?></td>
            <td>&yen;<?= $jutsu->purchase_cost ?></td>
        </tr>
    <?php endforeach; ?>
</table>