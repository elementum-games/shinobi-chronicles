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

    //used to keep track of current filters
    let typeFilter = 'none';
    let elementFilter = 'none';

    function filterByElement(type){
        elementFilter = type;
        applyFilter();
    }

    function filterByType(type){
        typeFilter = type;
        applyFilter();
    }

    function applyFilter(){
        var htmlElementsTypes = document.getElementsByClassName('bloodlineDetails');
       
        //for elements
        for (var i = 0; i < htmlElementsTypes.length; i ++) {

            htmlElementsTypes[i].style.display = "block";  //this is important

            if(elementFilter == 'none'){
                continue; //skip this html element
            }

            //user has picked an element
            if(!htmlElementsTypes[i].classList.contains(elementFilter))
            {
                htmlElementsTypes[i].style.display = 'none';
            }
        }

        //for elements
        for (var i = 0; i < htmlElementsTypes.length; i ++) {

            if(typeFilter == 'none'){
                continue; //skip this html element
            }

            //user has picked a type
            if(!htmlElementsTypes[i].classList.contains(typeFilter))
            {
                htmlElementsTypes[i].style.display = 'none';
            }
        }
    }
</script>

<!-- Content -->
<table class="table">
    <tr><th>Bloodline List</th></tr>
    <!-- Bloodline UI Filter -->
    <tr><td style='text-align:center;'>
        <span>Using the form below, you can search for bloodlines based on rank.</span>
        <br />
        <div style="text-align:center; margin-top: 5px; margin-bottom: 5px;">
            <!--Selection Input: Ranks-->
            <?php foreach(Bloodline::$public_ranks as $id=> $name): ?>
                <?php if ($id > 3) continue; ?>
                <input type="checkbox" onclick="toggleBloodlineDetails('<?=$id?>_rank')"
                       class="<?=$id?>_rank_box" checked="checked" /><?=$name?>
            <?php endforeach ?>

            <!--Selection Input: Jutsu Type-->
            <?php ?>
            <br>
                <label style='margin-bottom: 5px; margin-top: 5px' for="jutsuElementFilter">Jutsu Element:</label>
                <select name="jutsuElementFilter" onchange="filterByElement(this.value)" id="jutsuElementFilter">
                    <option value="none">Any</option>
                    <option value="Fire">Fire</option>
                    <option value="Lightning">Lightning</option>
                    <option value="Water">Water</option>
                    <option value="Earth">Earth</option>
                    <option value="Wind">Wind</option>
                </select>

                <br>

                <label for="jutsuTypeFilter">Jutsu Type:</label>
                <select name="jutsuTypeFilter" onchange="filterByType(this.value)" id="jutsuTypeFilter">
                    <option value="none">Any</option>
                    <option value="ninjutsu">Ninjutsu</option>
                    <option value="genjutsu">Genjutsu</option>
                    <option value="taijutsu">Taijutsu</option>
                </select>
            <?php ?>
        </div></td>
    </tr>

    <?php foreach(Bloodline::$public_ranks as $id=> $rank): ?>
        <?php if(empty($bloodlines[$id]) || $id > 3): ?>
            <?php continue; ?>
        <?php else: ?>
            <tr class="<?=$id?>_rank"><th><?=$rank?></th></tr>
            <?php foreach($bloodlines[$id] as $bloodline_id => $bloodline): ?>
                <tr class="<?=$id?>_rank">

                <!-- Get Jutsu Types -->
                <?php 
                $jutsuElement = "";
                $jutsuType = "";
                foreach ($bloodline['jutsu'] as $ability){
                    $jutsuElement .=  $ability->element . " ";
                    $jutsuType .=  $ability->jutsu_type . " ";
                } 
                ?>

                <td class="<?= $bloodline['village'] ?> bloodlineDetails <?= $jutsuType ?> <?= $jutsuElement ?>">
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
