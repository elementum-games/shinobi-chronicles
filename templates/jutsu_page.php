<?php
/**
 * @var string $self_link
 * @var ?Jutsu $jutsu_to_view
 * @var array $jutsu_to_view_child_names
 *
 * @var User $player
 * @var int $max_equipped_jutsu
 */
?>

<?php if($jutsu_to_view != null): ?>
    <table class='table'>
        <tr><th><?= $jutsu_to_view->name ?> (<a href='<?= $self_link ?>'>Return</a>)</th></tr>
        <tr><td>
            <label style='width:6.5em;'>Rank:</label><?= $jutsu_to_view->rank ?><br />
            <?php if($jutsu_to_view->element != 'none'): ?>
                <label style='width:6.5em;'>Element:</label><?= $jutsu_to_view->element ?><br />
            <?php endif; ?>
            <label style='width:6.5em;'>Use cost:</label><?= $jutsu_to_view->use_cost ?><br />
            <?php if($jutsu_to_view->jutsu_type != 'taijutsu'): ?>
                <label style='width:6.5em;'>Hand seals:</label><?= $jutsu_to_view->hand_seals ?><br />
            <?php endif; ?>
            <?php if($jutsu_to_view->cooldown): ?>
            <label style='width:6.5em;'>Cooldown:</label><?= $jutsu_to_view->cooldown ?> turn(s)<br />
            <?php endif; ?>
            <?php if($jutsu_to_view->effect): ?>
                <label style='width:6.5em;'>Effect:</label><?= System::unSlug($jutsu_to_view->effect) ?>
                - <?= $jutsu_to_view->effect_length ?> turns<br />
            <?php endif; ?>
            <label style='width:6.5em;'>Jutsu type:</label><?= ucwords($jutsu_to_view->jutsu_type) ?><br />
            <label style='width:6.5em;'>Power:</label><?= round($jutsu_to_view->power, 1) ?><br />
            <label style='width:6.5em;'>Level:</label><?= $jutsu_to_view->level ?><br />
            <label style='width:6.5em;'>Exp:</label><?= $jutsu_to_view->exp ?><br />

            <label style='width:6.5em;float:left;'>Description:</label>
            <p style='display:inline-block;margin:0;width:37.1em;'><?= $jutsu_to_view->description ?></p>
            <br style='clear:both;' />

            <?php if(count($jutsu_to_view_child_names) > 0): ?>
            <br />
            <br /><label>Learn <b><?= $jutsu_to_view->name ?></b> to level 50 to unlock:</label>
            <p style='margin-left:10px;margin-top:5px;'>
                <?php foreach($jutsu_to_view_child_names as $child_jutsu_name): ?>
                    <?= $child_jutsu_name ?><br />
                <?php endforeach; ?>
            </p>
            <?php endif; ?>

            <p style='text-align:center'>
                <a href='<?= $self_link ?>&view_jutsu=<?= $jutsu_to_view->id ?>&forget_jutsu=<?= $jutsu_to_view->id ?>'>Forget Jutsu!</a>
            </p>
        </td></tr>
    </table>
<?php else: ?>
    <table class='table'>
        <tr>
            <th id='ninjutsu_title_header' style='width:33%;'>Ninjutsu</th>
            <th id='taijutsu_title_header' style='width:33%;'>Taijutsu</th>
            <th id='genjutsu_title_header' style='width:33%;'>Genjutsu</th>
        </tr>

        <tr>
            <td id='ninjutsu_table_data'>
                <?php if($player->ninjutsu_ids): ?>
                    <?php
                        $sortedJutsu = [];
                        foreach($player->ninjutsu_ids as $jutsu_id) {
                            $sortedJutsu[] = $player->jutsu[$jutsu_id]->rank;
                        }
                        array_multisort($sortedJutsu, $player->ninjutsu_ids);
                    ?>
                    <?php foreach($player->ninjutsu_ids as $jutsu_id): ?>
                        <a href='<?= $self_link ?>&view_jutsu=<?= $jutsu_id ?>' title='Level: <?= $player->jutsu[$jutsu_id]->level ?>'>
                            <?= $player->jutsu[$jutsu_id]->name ?>
                        </a><br />
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>

            <td id='taijutsu_table_data'>
                <?php if($player->taijutsu_ids): ?>
                    <?php
                        $sortedJutsu = [];
                        foreach($player->taijutsu_ids as $jutsu_id) {
                            $sortedJutsu[] = $player->jutsu[$jutsu_id]->rank;
                        }
                        array_multisort($sortedJutsu, $player->taijutsu_ids);
                    ?>
                    <?php foreach($player->taijutsu_ids as $jutsu_id): ?>
                        <a href='<?= $self_link ?>&view_jutsu=<?= $jutsu_id ?>' title='Level: <?= $player->jutsu[$jutsu_id]->level ?>'>
                            <?= $player->jutsu[$jutsu_id]->name ?>
                        </a><br />
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>

            <td id='genjutsu_table_data'>
                <?php if($player->genjutsu_ids): ?>
                    <?php
                        $sortedJutsu = [];
                        foreach($player->genjutsu_ids as $jutsu_id) {
                            $sortedJutsu[] = $player->jutsu[$jutsu_id]->rank;
                        }
                        array_multisort($sortedJutsu, $player->genjutsu_ids);
                    ?>
                    <?php foreach($player->genjutsu_ids as $jutsu_id): ?>
                        <a href='<?= $self_link ?>&view_jutsu=<?= $jutsu_id ?>' title='Level: <?= $player->jutsu[$jutsu_id]->level ?>'>
                            <?= $player->jutsu[$jutsu_id]->name ?>
                        </a><br />
                    <?php endforeach; ?>
                <?php endif; ?>
                </td></tr>
        <tr><th colspan='3'>Equipped Jutsu</th></tr>

        <tr><td colspan='3'>
            <form action='<?= $self_link ?>' method='post'>
                <div style='text-align:center;'>
                    <div style='display:inline-block;'>
                        <?php $row_start = 1; ?>
                        <?php for($i = 0; $i < $max_equipped_jutsu; $i++): ?>
                            <?php $slot_equipped_jutsu = $player->equipped_jutsu[$i]['id'] ?? null; ?>
                            <select name='jutsu[<?= ($i + 1) ?>]'>
                                <option value='none' <?= (!$player->equipped_jutsu ? "selected='selected'" : "") ?>>None</option>
                                <?php foreach($player->jutsu as $jutsu): ?>
                                <option
                                    value='<?= $jutsu->jutsu_type ?>-<?= $jutsu->id ?>'
                                    <?= ($jutsu->id == $slot_equipped_jutsu ? "selected='selected'" : "") ?>
                                >
                                    <?= $jutsu->name ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <br />

                            <!--// Start second row-->
                            <?php if($row_start++ > 2): ?>
                                </div><div style='display:inline-block;'>
                                <?php $row_start = 1; ?>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <br />
                    <input type='submit' name='equip_jutsu' value='Equip' />
                </div>
            </form>
        </tr>

        <!-- Purchase jutsu-->
        <?php if(!empty($player->jutsu_scrolls)): ?>
            <tr><th colspan='3'>Jutsu scrolls</th></tr>

            <?php foreach($player->jutsu_scrolls as $id => $jutsu_scroll): ?>
                <tr id='jutsu_scrolls'><td colspan='3'>
                    <span style='font-weight:bold;'><?= $jutsu_scroll->name ?></span><br />
                    <div style='margin-left:2em;'>
                        <label style='width:6.5em;'>Rank:</label><?= $jutsu_scroll->rank ?><br />
                        <label style='width:6.5em;'>Element:</label><?= $jutsu_scroll->element ?><br />
                        <label style='width:6.5em;'>Use cost:</label><?= $jutsu_scroll->use_cost ?><br />
                        <?php if($jutsu_scroll->cooldown > 0): ?>
                            <label style='width:6.5em;'>Cooldown:</label><?= $jutsu_scroll->cooldown ?> turn(s)<br />
                        <?php endif; ?>
                        <label style='width:6.5em;float:left;'>Description:</label>
                        <p style='display:inline-block;margin:0;width:37.1em;'><?= $jutsu_scroll->description ?></p>
                        <br style='clear:both;' />
                        <label style='width:6.5em;'>Jutsu type:</label><?= ucwords($jutsu_scroll->jutsu_type) ?><br />
                    </div>
                    <p style='text-align:right;margin:0;'><a href='<?= $self_link ?>&learn_jutsu=<?= $id ?>'>Learn</a></p>
                </td></tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
<?php endif; ?>