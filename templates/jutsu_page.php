<?php
/**
 * @var System $system
 * @var string $self_link
 * @var array $child_jutsu
 * @var User $player
 * @var array $jutsu_list
 * @var int $max_equipped_jutsu
 */

require_once __DIR__ . '/../classes/RankManager.php';
$rank_names = RankManager::fetchNames($system);
?>

<style>
    .jutsu_list {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 0px;
        align-items: center;
        justify-content: center;
    }
    .jutsu_list_table tr {
        text-align: center;
    }
    .jutsu_list_table {
        position: relative;
    }

    .jutsu_slots_container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    .jutsu_select {
        width:200px;
        height:60px;
        outline: none;
        border: 0px;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
    }
    .jutsu_select_wrapper {
        border-radius: 36px;
        border-color: var(--theme-content-darker2-bg-color) !important;
        display:inline-block;
        margin: 10px;
        overflow:hidden;
        border:solid;
    }
    .jutsu_select_wrapper.over {
        border-color:var(--theme-text-color-normal) !important;
        border:dashed;
    }

    .jutsu_scrolls_expand:hover {
         box-shadow: 0px 0px 2px 2px rgb(0,0,0);
    }
    .jutsu_scrolls_expand {
        cursor: pointer;
        border-radius: 0px 0px 10px 10px;
    }

    .jutsu_block_table {
        flex-basis: 170px;
        margin: 2px 2px !important;
        border-radius: 10px !important;
        cursor: grab;
    }
    .jutsu_block_expand:hover {
         box-shadow: 0px 0px 2px 2px rgb(0,0,0);
    }
    .jutsu_block_expand {
        cursor: pointer;
        border-radius: 0px 0px 10px 10px;
    }
    .jutsu_block_title th {
        border-radius: 10px 10px 0px 0px !important;
    }
    .jutsu_block_table tr {
        text-align: center;
    }
    .jutsu_block_table td {
        padding: 0px !important;
        border-radius: 0px !important;
    }
    .jutsu_block_table td p {
        margin: 5px 0px;
    }

    .jutsu_filter {
        text-align: right;
    }
    .jutsu_filter_button {
        background-color: var(--theme-content-bg-color);
        color: var(--theme-text-color-dark);
        font-weight: bold;
        border-width: 3px;
        float: left;
        margin-right: 5px;
    }
    .active {
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
        border: solid;
    }
    .jutsu_filter_checkbox {
        margin-left: 15px;
    }
    .jutsu_filter_checkbox_container {
        padding-right: 15px;
    }
    .jutsu_filter_button_container {
        padding-left: 10px;
    }
    .type_filter.active {
        background-color: #ccc;
    }

    .jutsu_details_table {
        border-radius: 10px;
    }
        .jutsu_details_table tr {
            text-align: center;
        }
    .jutsu_details_label_column {
        vertical-align: top;
        display: inline-block;
        text-align: left;
    }
    .jutsu_details_label_column p {
        font-weight: bold;
        margin: 5px 0px;
    }
    .jutsu_details_info_column {
        display: inline-block;
        margin-left: 10px;
        text-align: left;
    }
    .jutsu_details_info_column p {
        margin: 5px 0px;
    }
    .jutsu_details_close {
        border-radius: 0px 0px 10px 10px;
        cursor: pointer;
    }
    .jutsu_details_close:hover {
        box-shadow: 0px 0px 2px 2px rgb(0,0,0);
    }
    .jutsu_details_label {
        display: inline-block;
    }
    .jutsu_details_label p {
        font-weight: bold;
        margin: 5px 0px;
    }
    .jutsu_details_description {
        height: 65px;
        display: flex;
        justify-content: center;
        flex-direction: column;
    }
    .jutsu_details_description p {
        margin: 5px 0px;
    }
    #jutsu_description {
        height: 40px;
        flex-shrink: 0;
    }
    .jutsu_details_child {
        display: inline-block;
    }
    .jutsu_details_child p {
        margin: 5px 0px;
    }
    #jutsu_name {
        text-align: center;
        margin: 5px 0px 5px 0px;
    }
    .hidden {
        display: none;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        $(".jutsu_scrolls_expand").on('click', function () {
            $('.jutsu_scroll').toggleClass('hidden');
            $(this).html() == "+" ? $(this).html("-") : $(this).html("+");
        });
        // hide details modal
        $(".jutsu_details_table").hide();
        $(".jutsu_details_close").on('click', function () {
            $(".jutsu_details_table").hide();
            $("#jutsu_name").text('');
        });
        $(".jutsu_block_expand").on('click', function () {
            if ($("#" + $(this).attr("data-target")).attr("data-jutsu_name") == $("#jutsu_name").text()) {
                $(".jutsu_details_table").hide();
                $("#jutsu_name").text('');
            }
            else {
                $(".jutsu_details_table").show();
                // only display child jutsu if set
                $("#" + $(this).attr("data-target")).attr("data-jutsu_child") == "None" ? $("#jutsu_details_child_row").hide() : $("#jutsu_details_child_row").show();
                // populate details modal with jutsu data
                $("#jutsu_name").text($("#" + $(this).attr("data-target")).attr("data-jutsu_name"));
                $("#jutsu_rank").text($("#" + $(this).attr("data-target")).attr("data-jutsu_rank"));
                $("#jutsu_type").text($("#" + $(this).attr("data-target")).attr("data-jutsu_type"));
                $("#jutsu_element").text($("#" + $(this).attr("data-target")).attr("data-jutsu_element"));
                $("#jutsu_cost").text($("#" + $(this).attr("data-target")).attr("data-jutsu_cost"));
                $("#jutsu_level").text($("#" + $(this).attr("data-target")).attr("data-jutsu_level"));
                $("#jutsu_experience").text($("#" + $(this).attr("data-target")).attr("data-jutsu_experience"));
                $("#jutsu_seals").text($("#" + $(this).attr("data-target")).attr("data-jutsu_seals"));
                $("#jutsu_power").text($("#" + $(this).attr("data-target")).attr("data-jutsu_power"));
                $("#jutsu_cooldown").text($("#" + $(this).attr("data-target")).attr("data-jutsu_cooldown"));
                $("#jutsu_effect").text($("#" + $(this).attr("data-target")).attr("data-jutsu_effect"));
                $("#jutsu_duration").text($("#" + $(this).attr("data-target")).attr("data-jutsu_duration"));
                $("#jutsu_description").text($("#" + $(this).attr("data-target")).attr("data-jutsu_description"));
                $("#jutsu_child").text($("#" + $(this).attr("data-target")).attr("data-jutsu_child"));
                // set forget jutsu url
                var href = $("#forget_jutsu").attr("href");
                href = href.substring(0, href.indexOf("&forget_jutsu=") + "&forget_jutsu=".length) + $("#" + $(this).attr("data-target")).attr("data-jutsu_id");
                $("#forget_jutsu").attr("href", href);
            }
        });
        // trigger filter logic on user input, only allow one active type
        $("#jutsu_filter_taijutsu").on('click', function () {
            this.classList.toggle('active');
            $("#jutsu_filter_ninjutsu").removeClass('active');
            $("#jutsu_filter_genjutsu").removeClass('active');
            filter();
        });
        $("#jutsu_filter_ninjutsu").on('click', function () {
            this.classList.toggle('active');
            $("#jutsu_filter_taijutsu").removeClass('active');
            $("#jutsu_filter_genjutsu").removeClass('active');
            filter();
        });
        $("#jutsu_filter_genjutsu").on('click', function () {
            this.classList.toggle('active');
            $("#jutsu_filter_taijutsu").removeClass('active');
            $("#jutsu_filter_ninjutsu").removeClass('active');
            filter();
        });
        $("#jutsu_filter_damage").on('click', function () {
            filter();
        });
        $("#jutsu_filter_buff").on('click', function () {
            filter();
        });
        $("#jutsu_filter_debuff").on('click', function () {
            filter();
        });
        // get list of jutsu, filter and hide based on jutsu_filter input
        function filter() {
            var jutsu = $(".jutsu_block_table").removeClass('hidden');
            // if no type selected display all
            if ($("#jutsu_filter_taijutsu").hasClass('active') || $("#jutsu_filter_ninjutsu").hasClass('active') || $("#jutsu_filter_genjutsu").hasClass('active')) {
                if (!$("#jutsu_filter_taijutsu").hasClass('active')) {
                    jutsu.filter('[data-jutsu_type="Taijutsu"]').addClass('hidden');
                }
                if (!$("#jutsu_filter_ninjutsu").hasClass('active')) {
                    jutsu.filter('[data-jutsu_type="Ninjutsu"]').addClass('hidden');
                }
                if (!$("#jutsu_filter_genjutsu").hasClass('active')) {
                    jutsu.filter('[data-jutsu_type="Genjutsu"]').addClass('hidden');
                }
            }
            if (!$("#jutsu_filter_damage").is(':checked')) {
                jutsu.filter('[data-jutsu_effect*="None"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect*="Residual Damage"]').addClass('hidden');
            }
            if (!$("#jutsu_filter_buff").is(':checked')) {
                jutsu.filter('[data-jutsu_effect="Taijutsu Boost"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Ninjutsu Boost"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Genjutsu Boost"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Speed Boost"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Cast Speed Boost"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Barrier"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Release Genjutsu"]').addClass('hidden');
            }
            if (!$("#jutsu_filter_debuff").is(':checked')) {
                jutsu.filter('[data-jutsu_effect="Taijutsu Nerf"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Ninjutsu Nerf"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Genjutsu Nerf"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Speed Nerf"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Cast Speed Nerf"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Willpower Nerf"]').addClass('hidden');
                jutsu.filter('[data-jutsu_effect="Intelligence Nerf"]').addClass('hidden');
            }
        }
        // Drag and drop functionality
        $(".jutsu_block_table").on('dragstart', function (e) {
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            e.originalEvent.dataTransfer.dropEffect = 'move';
            e.originalEvent.dataTransfer.setData('text/html', $(this).attr("data-jutsu_select"));
        });
        $(".jutsu_select").on('dragenter', function (e) {
            e.preventDefault();
            this.parentElement.classList.add('over');
        });
        $(".jutsu_select").on('dragover', function (e) {
            e.preventDefault();
        });
        $(".jutsu_select").on('dragleave', function () {
            this.parentElement.classList.remove('over');
        });
        $(".jutsu_select").on('drop', function (e) {
            e.preventDefault();
            this.parentElement.classList.remove('over');
            console.log(e.originalEvent.dataTransfer.getData('text/html'));
            $(this).val((e.originalEvent.dataTransfer.getData('text/html')));
        });
    });
</script>

<table class='table'>
    <tr><th colspan='3'>Equipped Jutsu</th></tr>
    <tr><td colspan='3'>
        <form action='<?= $self_link ?>' method='post'>
            <div class='jutsu_slots_container'>
                <?php for($i = 0; $i < $max_equipped_jutsu; $i++): ?>
                    <?php $slot_equipped_jutsu = $player->equipped_jutsu[$i]['id'] ?? null; ?>
                    <div class="jutsu_select_wrapper">
                        <select class="jutsu_select" name='jutsu[<?= ($i + 1) ?>]'>
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
                    </div>
                <?php endfor; ?>
            </div>
            <div style='text-align:center;margin:8px 0 5px;'>
                <input type='submit' name='equip_jutsu' value='Equip' />
            </div>
        </form>
    </tr>

    <!-- Purchase jutsu-->
    <?php if(!empty($player->jutsu_scrolls)): ?>
        <tr><th colspan='3'>Jutsu Scrolls (<?= count($player->jutsu_scrolls) ?>)</th></tr>

        <?php foreach($player->jutsu_scrolls as $id => $jutsu_scroll): ?>
            <tr id='jutsu_scrolls' class="jutsu_scroll"><td colspan='3'>
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
            <tr>
                <th class="jutsu_scrolls_expand" colspan="3">-</th>
            </tr>
    <?php endif; ?>
</table>

 <!--Jutsu Details Modal-->
            <table class="table jutsu_details_table">
                <tr>
                    <th id="jutsu_name" colspan="2"></th>
                </tr>
                <tr>
                    <td>
                        <div class="jutsu_details_label_column">
                            <p>Rank:</p>
                            <p>Type:</p>
                            <p>Element:</p>
                            <p>Use cost:</p>
                            <p>Level:</p>
                        </div>
                        <div class="jutsu_details_info_column">
                            <p id="jutsu_rank"></p>
                            <p id="jutsu_type"></p>
                            <p id="jutsu_element"></p>
                            <p id="jutsu_cost"></p>
                            <p id="jutsu_level"></p>
                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <div class="jutsu_details_label_column">
                            <p>Hand seals:</p>
                            <p>Power:</p>
                            <p>Cooldown:</p>
                            <p>Effect:</p>
                            <p>Duration:</p>
                        </div>
                        <div class="jutsu_details_info_column">
                            <p id="jutsu_seals"></p>
                            <p id="jutsu_power"></p>
                            <p id="jutsu_cooldown"></p>
                            <p id="jutsu_effect"></p>
                            <p id="jutsu_duration"></p>
                        </div>
                    </td>
                </tr>
                <tr id="jutsu_details_child_row">
                    <td colspan="2">
                        <div class="jutsu_details_label">
                            <p>Child Jutsu:</p>
                        </div>
                        <div class="jutsu_details_child">
                            <p id="jutsu_child"></p>
                
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="jutsu_details_label">
                            <p>Description:</p>
                        </div>
                        <div class="jutsu_details_description">
                            <p id="jutsu_description"></p>
                            <p style='text-align:center; margin-top: auto'>
                                <a id="forget_jutsu" href='<?= $self_link ?>&forget_jutsu='>Forget Jutsu!</a>
                            </p>
                        </div>
                    </td>
                </tr>
                <tr style="text-align:center">
                    <th colspan="2" class="jutsu_details_close">
                        <div>Close</div>
                    </th>
                </tr>
            </table>

<!--Jutsu List-->
<table class="table jutsu_list_table">
    <tr>
        <th>Jutsu</th>
    </tr>
    <tr>
        <th>
            <div class="jutsu_filter">
                <div class="jutsu_filter_button_container">
                    <button id="jutsu_filter_taijutsu" class="jutsu_filter_button">Taijutsu</button>
                    <button id="jutsu_filter_ninjutsu" class="jutsu_filter_button">Ninjutsu</button>
                    <button id="jutsu_filter_genjutsu" class="jutsu_filter_button">Genjutsu</button>
                </div>
                <div class="jutsu_filter_checkbox_container">
                    <input id="jutsu_filter_damage" class="jutsu_filter_checkbox" type="checkbox" name="damage_toggle" checked/>
                    <label for="damage_toggle">Damage</label>
                    <input id="jutsu_filter_buff" class="jutsu_filter_checkbox" type="checkbox" name="buff_toggle" checked/>
                    <label for="buff_toggle">Buff</label>
                    <input id="jutsu_filter_debuff" class="jutsu_filter_checkbox" type="checkbox" name="debuff_toggle" checked/>
                    <label for="debuff_toggle">Debuff</label>
                </div>
            </div>
        </th>
    </tr>
    <tr>
        <td>
            <div class="jutsu_list">
                <?php foreach ($jutsu_list as $jutsu): ?>
                    <!--data attributes used for filter logic-->
                    <table class="table jutsu_block_table" title="<?= $jutsu->name ?> (<?= $jutsu->level ?>)" draggable="true" data-jutsu_type="<?= ucwords($jutsu->jutsu_type) ?>" data-jutsu_effect="<?= System::unSlug($jutsu->effect) ?>" data-jutsu_select="<?= $jutsu->jutsu_type . '-' . $jutsu->id ?>">
                        <tr class="jutsu_block_title">
                            <th colspan="2">
                                <?= strlen($jutsu->name) > 21 ? substr($jutsu->name,0,19)."..." : $jutsu->name; ?>
                                <!--data attributes used to populate details modal-->
                                <div id="jutsu_<?=  $jutsu->id?>" class="jutsu_data" 
                                     data-jutsu_id="<?= $jutsu->id ?>"
                                     data-jutsu_name="<?= $jutsu->name ?>"
                                     data-jutsu_rank="<?= $rank_names[$jutsu->rank] ?>"
                                     data-jutsu_type="<?= ucwords($jutsu->jutsu_type) ?>"
                                     data-jutsu_element="<?= $jutsu->element ?>"
                                     data-jutsu_cost="<?= $jutsu->use_cost ?>"
                                     data-jutsu_level="<?php echo $jutsu->level == 100 ? $jutsu->level : $jutsu->level . " (" . $jutsu->exp . "/1000)" ?>"
                                     data-jutsu_experience="<?= $jutsu->exp ?>"
                                     data-jutsu_seals="<?= $jutsu->jutsu_type == "taijutsu" ? "None" : $jutsu->hand_seals ?>"
                                     data-jutsu_power="<?= $jutsu->power ?> (+<?= round($jutsu->power - $jutsu->base_power, 2) ?>)"
                                     data-jutsu_cooldown="<?php echo $jutsu->cooldown == 1 ? $jutsu->cooldown . " turn" :  $jutsu->cooldown . " turns" ?>"
                                     data-jutsu_effect="<?php echo ($jutsu->effect == "none" || $jutsu->effect == "barrier") ? System::unSlug($jutsu->effect) : System::unSlug($jutsu->effect) . " (" . round($jutsu->effect_amount, 0) . "%)" ?>"
                                     data-jutsu_duration="<?php echo $jutsu->effect_length == 1 ? $jutsu->effect_length . " turn" :  $jutsu->effect_length . " turns" ?>"
                                     data-jutsu_description="<?= $jutsu->description ?>"
                                     data-jutsu_child="<?php echo array_key_exists($jutsu->id, $child_jutsu) ? implode(', ' , $child_jutsu[$jutsu->id][0]) : "None" ?>">
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <p><b>Power</b></p>
                                <p><?= $jutsu->power ?></p>
                            </td>
                            <td>
                                <p><b>Cooldown</b></p>
                                <p><?= $jutsu->cooldown ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?php if ($jutsu->effect == "none" || $jutsu->effect == "barrier"): ?>
                                <p><?= System::unSlug($jutsu->effect) ?></p>
                                <?php else : ?>
                                <p><?= System::unSlug($jutsu->effect) ?> (<?= round($jutsu->effect_amount, 0) ?>%)</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="jutsu_block_expand" colspan="2" data-target="jutsu_<?= $jutsu->id?>">+</th>
                        </tr>
                    </table>
                <?php endforeach; ?>
            </div>
        </td>
    </tr>
</table>
