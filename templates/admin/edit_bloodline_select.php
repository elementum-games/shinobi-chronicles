<?php

/**
 * @var Bloodline[] $all_bloodlines
 * @var string $self_link
 * @var array $bloodline_constraints
 */

$bloodline_ranks = $bloodline_constraints['rank']['options'];

$selected_link_style = "text-decoration:none;";

$current_rank = 1;

?>

<!--// Filter links -->
<p id='top' style='text-align:center;margin-top:20px;margin-bottom:-5px;'>
    <?php foreach($bloodline_ranks as $id => $name): ?>
        | <a href='#rank-<?= $id ?>'><?= $name ?></a> |
    <?php endforeach; ?>
    <!--<a href='<?/*= $self_link */?>&jutsu_type=ninjutsu'
       style='font-size:14px;<?/*= ($jutsu_type == 'ninjutsu' ? $selected_link_style : "") */?>'>
        Ninjutsu
    </a> |
    <a href='<?/*= $self_link */?>&jutsu_type=taijutsu'
       style='font-size:14px;<?/*= ($jutsu_type == 'taijutsu' ? $selected_link_style : "") */?>'>
        Taijutsu
    </a> |
    <a href='<?/*= $self_link */?>&jutsu_type=genjutsu'
       style='font-size:14px;<?/*= ($jutsu_type == 'genjutsu' ? $selected_link_style : "") */?>'>
        Genjutsu
    </a>-->
</p>

<style>
    p {
        margin: 2px 0;
    }
    .bloodlineJutsu {
        display: inline-block;
        width: 32%;
    }
</style>
<table class='table'>
    <tr>
        <th style='width:15%;'>Name</th>
        <th style='width:20%;'>Passive Boosts</th>
        <th style='width:20%;'>Combat Boosts</th>
    </tr>
    <tr><th colspan='3' id='rank-<?= $current_rank ?>'><?= $bloodline_ranks[$current_rank] ?></th></tr>
    <?php foreach($all_bloodlines as $id => $bloodline): ?>
        <?php
        if($bloodline->rank > $current_rank) {
            $current_rank = $bloodline->rank;
            echo "<tr><th colspan='3' id='rank-{$current_rank}'>
                $bloodline_ranks[$current_rank]
                <a href='#top'>(Top)</a>
            </th></tr>";
        }
        ?>
        <tr style='background:rgba(0,0,0,0.15);'>
            <td>
                <a href="<?= $self_link ?>&bloodline_id=<?= $bloodline->bloodline_id ?>"><?= $bloodline->name ?></a><br />
                Clan ID: <?= $bloodline->clan_id ?>
            </td>
            <td>
                <?php foreach($bloodline->base_passive_boosts as $boost): ?>
                    <p><?= $boost->power ?> <?= $boost->effect ?></p>
                <?php endforeach; ?>
            </td>
            <td>
                <?php foreach($bloodline->base_combat_boosts as $boost): ?>
                    <p><?= $boost->power ?> <?= $boost->effect ?></p>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <td colspan='3'>
                <?php foreach($bloodline->jutsu as $jutsu): ?>
                    <?php /** @var Jutsu $jutsu */ ?>
                    <div class='bloodlineJutsu'>
                        <p>
                            <b><?= $jutsu->name ?></b>
                            <?php if($jutsu->element != Jutsu::ELEMENT_NONE): ?>
                                <i>(<?= ucwords($jutsu->element) ?>)</i>
                            <?php endif; ?>
                        </p>
                        <p>Power: <?= $jutsu->base_power ?></p>
                        <p>
                            <?php if($jutsu->effects[0]->effect !== 'none'): ?>
                                <?= System::unSlug($jutsu->effects[0]->effect) ?>
                                (<?= $jutsu->effects[0]->effect_amount ?>% / <?= $jutsu->effects[0]->effect_length ?> turns)
                            <?php else: ?>
                                No effect
                            <?php endif; ?>
                        </p>
                        <?php if($jutsu->effects[1]->effect !== 'none'): ?>
                            <p>
                                <?= System::unSlug($jutsu->effects[1]->effect) ?>
                                (<?= $jutsu->effects[1]->effect_amount ?>% / <?= $jutsu->effects[1]->effect_length ?> turns)
                            </p>   
                        <?php endif; ?>
                        <p>Use Cost: <?= $jutsu->use_cost ?></p>
                        <p>Purchase Cost: &yen;<?= $jutsu->purchase_cost ?></p>
                    </div>
                <?php endforeach; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>