<?php
/**
 * @var System $system
 * @var array $ranks
 * @var array $bloodlines
 */

 require_once __DIR__ . '/../classes/RankManager.php';
 $RANK_NAMES = RankManager::fetchNames($system);
?>

<!-- Toggle Script -->
<script type="text/javascript">
    function toggleBloodlineDetails(name, byID = false) {
        if(byID) {
            name = '#'+name;
        } else {
            name = '.'+name;
        }

        if(name.indexOf("_rank") >= 0) {
            if($(name+'_box').is(':checked')) {
                $(name).show();
            }else {
                $(name).hide();
            }
        } else {
            $(name).toggle();
        }
    }

    //filter by category
    function filterByType(type){
        var htmlElementsTypes = document.getElementsByClassName('bloodlineDetails');
       
        for (var i = 0; i < htmlElementsTypes.length; i ++) {

            //show all
            if(type == 'none'){
                htmlElementsTypes[i].style.display = 'block';
                continue;
            }

            //show [type]
            if(!htmlElementsTypes[i].classList.contains(type)){
                htmlElementsTypes[i].style.display = 'none';
            } else {
                htmlElementsTypes[i].style.display = 'block';
            }
        }
    }
</script>

<!-- Content -->
<table class="table">

    <tr><th>Bloodline List</th></tr>

    <!-- Bloodline UI Filter -->
    <tr><td style='text-align:center;'>
        <p>Using the form below, you can search for bloodlines based on rank.</p>
        <div style="text-align:center;">
            <!--Selection Input: Ranks-->
            <?php foreach(Bloodline::$public_ranks as $id=> $name): ?>
                <input type="checkbox" onclick="toggleBloodlineDetails('<?=$id?>_rank')"
                       class="<?=$id?>_rank_box" checked="checked" /><?=$name?>
            <?php endforeach ?>

            <!--Selection Input: Jutsu Type-->
            <?php ?>
            <br>
                <label for="jutsuTypeFilter">Jutsu Type:</label>
                <select name="jutsuTypeFilter" onchange="filterByType(this.value)" id="jutsuTypeFilter">
                    <option value="none">None</option>
                    <option value="Fire">Fire</option>
                    <option value="Lightning">Lightning</option>
                    <option value="Water">Water</option>
                    <option value="Earth">Earth</option>
                    <option value="Wind">Wind</option>
                </select>
            <?php ?>
        </div></td>
    </tr>

    <?php foreach(Bloodline::$public_ranks as $id=> $rank): ?>
        <?php if(empty($bloodlines[$id])): ?>
            <?php continue; ?>
        <?php else: ?>
            <tr class="<?=$id?>_rank"><th><?=$rank?></th></tr>
            <?php foreach($bloodlines[$id] as $bloodline_id => $bloodline): ?>
                <tr class="<?=$id?>_rank">

                <!-- Get Jutsu Types -->
                <?php 
                $jutsuType = "";
                foreach ($bloodline['jutsu'] as $ability){
                    $jutsuType .=  $ability->element . " ";
                } 
                ?>

                <td class="<?= $bloodline['village'] ?> bloodlineDetails <?= $jutsuType?>">
                    <a onclick="toggleBloodlineDetails('<?=$bloodline_id?>_details')"
                       style="cursor:pointer;"><?=$bloodline['name']?></a><br />
                    <div class="<?=$bloodline_id?>_details" style="display:none; margin-left:1.5em;">
                        <label style="width:4.25em; font-weight:bold;">Village:</label><?=$bloodline['village']?><br />
                        <!-- Passive Boost List -->
                        <?php if(isset($bloodline['passive_boosts']) && !empty($bloodline['passive_boosts'])): ?>
                            <label style="width:9.5em; font-weight:bold;">Passive Boosts:</label><br />
                            <div style="margin-left:1.5em;">
                            <?php foreach($bloodline['passive_boosts'] as $passive_boost): ?>
                                <?=ucwords(str_replace('_', ' ', $passive_boost->effect))?>:
                                <?= $passive_boost->power ?> boost power<br />
                            <?php endforeach ?>
                            </div>
                        <?php endif ?>
                        <!-- Combat Boost List -->
                        <?php if(isset($bloodline['combat_boosts']) && !empty($bloodline['combat_boosts'])): ?>
                            <label style="width:9.5em; font-weight:bold;">Combat Boosts:</label><br />
                            <div style="margin-left:1.5em;">
                                <?php foreach($bloodline['combat_boosts'] as $combat_boost): ?>
                                    <?=ucwords(str_replace('_', ' ', $combat_boost->effect))?> =>
                                    <?= $combat_boost->power ?> boost power<br />
                                <?php endforeach ?>
                            </div>
                        <?php ENDIF ?>
                        <!-- Jutsu List -->
                        <label style='width:4em; font-weight:bold;'>Jutsu:</label><br />
                        <div style="margin-left:1.5em;">
                            <?php foreach($bloodline['jutsu'] as $ability): ?>
                                <em><?=$ability->name?></em><br />
                                <div style="margin-left:1.5em;">
                                    <label style="width:7.5em; font-weight:bold;">Rank:</label> <?=$RANK_NAMES[$ability->rank]?><br />
                                    <label style="width:7.5em; font-weight:bold;">Element:</label> <?=ucwords($ability->element)?><br />
                                    <label style="width:7.5em; font-weight:bold;">Use Cost:</label>
                                        <?=$ability->use_cost?> <?= $ability->jutsu_type == 'Taijutsu' ? 'Stamina' : 'Chakra' ?><br />
                                    <label style="width:7.5em; font-weight:bold;">Effect:</label> <?=ucwords(str_replace('_', ' ', $ability->effect))?>
                                        <?php if($ability->effect != 'none'): ?>
                                            - <?=$ability->effect_length?> turn(s)<br />
                                        <?php else: ?>
                                            <br />
                                        <?php endif ?>
                                    <label style="width:7.5em; font-weight:bold;">Jutsu Type:</label> <?=ucwords($ability->jutsu_type)?><br />
                                    <label style="width:7.5em; font-weight:bold;">Description:</label> <?=$ability->description?><br />
                                    <br />
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </td></tr>
            <?php endforeach ?>
        <?php endif ?>
    <?php endforeach ?>

</table>
