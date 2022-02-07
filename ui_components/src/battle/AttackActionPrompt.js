// @flow
import * as React from "react";

export default function AttackActionPrompt({ battle }) {
    const player = battle.fighters[battle.playerId];
    const opponent = battle.fighters[battle.opponentId];

    /*
    <input type='hidden' id='hand_seal_input' name='hand_seals' value='<?= $prefill_hand_seals ?>' />
<input type='hidden' id='jutsuType' name='jutsu_type' value='<?= $prefill_jutsu_type ?>' />
<input type='hidden' id='weaponID' name='weapon_id' value='<?= $prefill_weapon_id ?>' />
<input type='hidden' id='jutsuID' name='jutsu_id' value='<?= $prefill_jutsu_id ?>' />
<input type='hidden' id='itemID' name='item_id' value='<?= $prefill_item_id ?>' />
     */

    const isSelectingHandSeals = true;
    const isSelectingWeapon = false;

    return (
        <React.Fragment>
            <tr>
                <td>
                    {isSelectingHandSeals && <HandSealsInput />}
                    {isSelectingWeapon && <WeaponInput fighter={player} />}
                </td>
            </tr>
            <tr>
                <th className='jutsuCategoryHeader'>
                    <span>Ninjutsu</span>
                    <span>Taijutsu</span>
                    <span>Genjutsu</span>
                    {player.hasBloodline && <span>Bloodline</span>}
                </th>
            </tr>
            <tr>
                <td>
                    <JutsuInput />
                </td></tr>
        </React.Fragment>
    );
}

function HandSealsInput() {
    // 1-12
    const handSealNumbers = Array(12).fill().map((_, i) => i + 1);

    return (
        <div id='handSeals'>
            {handSealNumbers.map((num) => {
                return (
                    <p key={`handseal:${num}`} data-selected='no' data-handseal='<?= $i ?>'>
                        <img src={`./images/handseal_${num}.png`} draggable='false'/>
                        <span className='handsealNumber'>1</span>
                        <span className='handsealTooltip'>&nbsp;</span>
                    </p>
                );
            })}
            <div id='handsealOverlay'>
            </div>
        </div>
    );
}

function WeaponInput({ fighter }) {
    return (
        <div id='weapons'>
            <p className='weapon' data-id='0'>
                <b>None</b>
            </p>
            {fighter.equippedWeapons.map(weapon => (
                <p className='weapon'>
                    <b>{weapon.name}</b><br/>
                    {weapon.effect}
                    {weapon.effectAmount}%
                </p>
            ))}
        </div>
    );
}

const NINJUTSU = 'ninjutsu', TAIJUTSU = 'taijutsu', GENJUTSU = 'genjutsu';

function JutsuInput() {
    let  c1_count = 0, c2_count = 0, c3_count = 0;

    const categories = [
        battle.jutsuTypes.ninjutsu,
        battle.jutsuTypes.taijutsu,
        battle.jutsuTypes.genjutsu,
    ]

    return (
        <div id='jutsu'>
            {categories.map((category) => (
                <div className='jutsuCategory'>
                    {battle.playerDefaultAttacks.filter(jutsu => jutsu.type === battles.jutsuTypes)}
                    <?php foreach($battleManager->default_attacks as $attack): ?>
                    <?php if($attack->jutsu_type != $jutsu_types[$i]) continue; ?>
                    <span id='default<?= $c1_count ?>'
                          className='jutsuName <?= $jutsu_types[$i] ?>'
                          data-handseals='<?= ($attack->jutsu_type != Jutsu::TYPE_TAIJUTSU ? $attack->hand_seals : '') ?>'
                          data-id='<?= $attack->id ?>'
                    >
        <?= $attack->name ?><br/>
        <strong>D<?= $c1_count ?></strong>
        </span><br/>
                    <?php $c1_count++; ?>
                    <?php endforeach; ?>

                    <?php if(is_array($player->equipped_jutsu)): ?>
                    <?php foreach($player->equipped_jutsu as $jutsu): ?>
                    <?php
                    /!** @var Jutsu jutsu *!/
                    if($player->jutsu[$jutsu['id']]->jutsu_type != $jutsu_types[$i]) {
                    continue;
                    }

                    $player_jutsu = $player->jutsu[$jutsu['id']];
                    $player_jutsu->setCombatId($player->combat_id);

                    $cd_left = $battle->jutsu_cooldowns[$player_jutsu->combat_id] ?? 0;
                    ?>

                    <div
                        id='<?= $jutsu_types[$i] ?><?= $c2_count ?>'
                        className='jutsuName <?= $jutsu_types[$i] ?>'
                        data-handseals='<?= $player_jutsu->hand_seals ?>'
                        data-id='<?= $jutsu[' id'] ?>'
                        aria-disabled='<?= ($cd_left > 0 ? "true" : "false") ?>'
                    >
                        <?= $player_jutsu->name ?><br/>
                        <?php if($cd_left > 0): ?>
                        (CD: <?= $cd_left ?> turns)
                        <?php else: ?>
                        <strong><?= strtoupper($jutsu_types[$i][0]) ?><?= $c2_count ?></strong>
                        <?php endif; ?>
                    </div><br/>
                    <?php $c2_count++; ?>
                    <?php endforeach; ?>
                    <?php $c2_count = 0; ?>
                    <?php endif; ?>
                </div>
            ))}

            <!-- Display bloodline jutsu-->
            {player.hasBloodline && <JutsuCategoryList />}


            <form action='<?= $self_link ?>' method='post'>
                <input type='hidden' id='hand_seal_input' name='hand_seals' value='<?= $prefill_hand_seals ?>' />
                <input type='hidden' id='jutsuType' name='jutsu_type' value='<?= $prefill_jutsu_type ?>' />
                <input type='hidden' id='weaponID' name='weapon_id' value='<?= $prefill_weapon_id ?>' />
                <input type='hidden' id='jutsuID' name='jutsu_id' value='<?= $prefill_jutsu_id ?>' />
                <input type='hidden' id='itemID' name='item_id' value='<?= $prefill_item_id ?>' />
                <p style='display:block;text-align:center;margin:auto;'>
                    <input id='submitbtn' type='submit' name='attack' value='Submit' />
                </p>
            </form>
        </div>
    );
}

function JutsuCategoryList({defaultJutsu, equippedJutsu, bloodlineJutsu}) {
    return (
        <div className='jutsuCategory'>
            <?php if(!empty($player->bloodline->jutsu)): ?>
            <?php foreach($player->bloodline->jutsu as $id => $jutsu): ?>
            <?php
            $jutsu->setCombatId($player->combat_id);
            $cd_left = $battle->jutsu_cooldowns[$jutsu->combat_id] ?? 0;
            ?>

            <div
                id='bloodline<?= $c3_count ?>'
                className='jutsuName bloodline_jutsu'
                data-handseals='<?= $jutsu->hand_seals ?>'
                data-id='<?= $id ?>'
                aria-disabled='<?= ($cd_left > 0 ? "true" : "false") ?>'
            >
                <?= $jutsu->name ?><br/>
                <?php if($cd_left > 0): ?>
                (CD: <?= $cd_left ?> turns)
                <?php else: ?>
                <strong>B<?= $c3_count ?></strong>
                <?php endif; ?>
            </div><br/>
            <?php $c3_count++; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    )
}


function setAbilityClickHandlers() {
    let hand_seals = [];
    let hand_seal_prompt = 'Please enter handseals (click jutsu name for hint):';
    let weapons_prompt = 'Please select a weapon to augment your Taijutsu with:';
    $('#handSeals p img').click(function() {
        let parent = $(this).parent();
        let seal = parent.attr('data-handseal');
        // Select hand seal
        if(parent.attr('data-selected') === 'no') {
            parent.attr('data-selected', 'yes');
            $(this).css('border-color', '<?= $gold_color ?>');
            parent.children('.handsealNumber').show();
            hand_seals.splice(hand_seals.length, 0, seal);
        }
        // De-select handseal
        else if(parent.attr('data-selected') === 'yes') {
            parent.attr('data-selected', 'no');
            $(this).css('border-color', 'rgba(0,0,0,0)');
            parent.children('.handsealNumber').hide();

            hand_seals = hand_seals.filter(hs => hs !== seal);
            /!*for(let x in hand_seals) {
            if(hand_seals[x] === seal) {
                hand_seals.splice(x,1);
                break;
            }
        }*!/
    }
    // Update display
    $('#hand_seal_input').val(hand_seals.join('-'));
    $('#jutsuID').val('0');
    let id = '';
    for(let x in hand_seals) {
        id = 'handseal_' + hand_seals[x];
        $('#' + id).children('.handsealNumber').text((parseInt(x) + 1));
    }
});

let currentlySelectedJutsu = false;
let lastJutsu, firstJutsu = false;
$('.jutsuName').click(function(){

    if(lastJutsu !== this && firstJutsu) {

        let seals = $(lastJutsu).attr('data-handseals').split('-');
        for(let ay in seals) {
            if(!isNaN(parseInt(seals[ay]))) {
                id = 'handseal_' + seals[ay];
                $('#' + id + ' img').trigger('click');
            }
        }

        lastJutsu = this;

        let new_seals = $(lastJutsu).attr('data-handseals').split('-');
        for(let ayy in new_seals) {
            if(!isNaN(parseInt(new_seals[ayy]))) {
                id = 'handseal_' + new_seals[ayy];
                $('#' + id + ' img').trigger('click');
            }
        }

    }

    if(! firstJutsu) {
        lastJutsu = this;
        firstJutsu = true;
        let seals = $(lastJutsu).attr('data-handseals').split('-');
        for(let ay in seals) {
            if(!isNaN(parseInt(seals[ay]))) {
                id = 'handseal_' + seals[ay];
                $('#' + id + ' img').trigger('click');
            }
        }
    }

    if(currentlySelectedJutsu !== false) {
        $(currentlySelectedJutsu).css('box-shadow', '0px');
    }
    currentlySelectedJutsu = this;
    $(currentlySelectedJutsu).css('box-shadow', '0px 0px 4px 0px #000000');
    $('.handsealTooltip').html('&nbsp;');
    let handseal_string = $(this).attr('data-handseals');
    let handseal_array = handseal_string.split('-');
    for(let x in handseal_array) {
        if(!isNaN(parseInt(handseal_array[x]))) {
            id = 'handseal_' + handseal_array[x];
            $('#' + id).children('.handsealTooltip').text((parseInt(x) + 1));
        }
    }
});

let currentlySelectedWeapon = $('p[data-id=0]');
$('.weapon').click(function(){
    if(currentlySelectedWeapon !== false) {
        $(currentlySelectedWeapon).css('box-shadow', '0px');
    }
    currentlySelectedWeapon = this;
    $(currentlySelectedWeapon).css('box-shadow', '0px 0px 4px 0px #000000');
    $('#weaponID').val( $(this).attr('data-id') );
});

let display_state = 'ninjutsu';

$('#jutsu .ninjutsu').click(function(){
    if(display_state !== 'ninjutsu' && display_state !== 'genjutsu') {
        $('#textPrompt').text(hand_seal_prompt);
        $('#weapons').hide();
        $('#handSeals').show();
        $('#handsealOverlay').fadeOut();
    }
    display_state = 'ninjutsu';
    $('#jutsuType').val('ninjutsu');
});
$('#jutsu .genjutsu').click(function(){
    if(display_state !== 'genjutsu' && display_state !== 'ninjutsu') {
        $('#textPrompt').text(hand_seal_prompt);
        $('#weapons').hide();
        $('#handSeals').show();
        $('#handsealOverlay').fadeOut();
    }
    display_state = 'genjutsu';
    $('#jutsuType').val('genjutsu');
});
$('#jutsu .taijutsu').click(function(){
    if(display_state !== 'taijutsu') {
        $('#textPrompt').text(weapons_prompt);
        $('#handSeals').hide();
        $('#weapons').show();
        if(display_state === 'bloodline_jutsu') {
            $('#handsealOverlay').fadeOut();
        }
    }
    display_state = 'taijutsu';
    $('#jutsuType').val('taijutsu');
    $('#jutsuID').val($(this).attr('data-id'));
});
$('#jutsu .bloodline_jutsu').click(function(){
    if(display_state !== 'bloodline_jutsu') {
        $('#handsealOverlay').fadeIn();
    }
    display_state = 'bloodline_jutsu';
    $('#jutsuType').val('bloodline_jutsu');
    $('#jutsuID').val($(this).attr('data-id'));
});
}

const nin = 78;
const gen = 71;
const tai = 84;
const bl = 66;
const def_ault = 68;
let arr = [];
function handleKeyUp(event) {
    //arr->array will hold 2 elements [JutsuName, Number];

    //enter key
    if(event.which === 13){
        document.getElementById('submitbtn').click();
    }

    //(If Key is a Letter, Letter will be turned into string for Arr)
    if(event.which === nin) {
        arr[0] = 'ninjutsu';
    }
    else if(event.which === gen) {
        arr[0] = 'genjutsu';
    }
    else if(event.which === tai) {
        arr[0] = 'taijutsu';
    }
    else if(event.which === bl) {
        arr[0] = 'bloodline';
    }
    else if(event.which === def_ault) {
        arr[0] = 'default'; /!*default*!/
    }

    //if arr[0] is not a valid string, arr will clear
    if(typeof(arr[0]) == null){
        arr = [];
    }

    //if user presses correct number (between 0-9) store in Arr[1];
    let key = -1;
    switch (event.which){
        case 48:
        case 96:
            key = 0;
            break;
        case 49:
        case 97:
            key = 1;
            break;
        case 50:
        case 98:
            key = 2;
            break;
        case 51:
        case 99:
            key = 3;
            break;
        case 52:
        case 100:
            key = 4;
            break;
        case 53:
        case 101:
            key = 5;
            break;
        case 54:
        case 102:
            key = 6;
            break;
        case 55:
        case 103:
            key = 7;
            break;
        case 56:
        case 104:
            key = 8;
            break;
        case 57:
        case 105:
            key = 9;
            break;
    }
    arr[1] = key;

    //if arr[0] not a string, and arr[1] is not the default -1, continue;
    if(typeof(arr[0]) == 'string' && arr[1] !== -1){
        //creating the ID name to get the Element to add the click() function to
        const classname = arr[0] + arr[1];
        console.log(classname);
        console.log('selection successful');
        document.getElementById(classname).click();

        // document.getElementById(classname).addClass('focused') should add something like this
        //for visual so user knows selection is made
    }
}

/*

$gold_color = '#FDD017';

$prefill_hand_seals = $_POST['hand_seals'] ?? '';
$prefill_jutsu_type = $_POST['jutsu_type'] ?? Jutsu::TYPE_NINJUTSU;
$prefill_weapon_id = $_POST['weapon_id'] ?? '0';
$prefill_jutsu_id = $_POST['jutsu_id'] ?? '';
$prefill_item_id = $_POST['item_id'] ?? '';

    ?>

<form action='<?= $self_link ?>' method='post'>
<input type='hidden' id='hand_seal_input' name='hand_seals' value='<?= $prefill_hand_seals ?>' />
<input type='hidden' id='jutsuType' name='jutsu_type' value='<?= $prefill_jutsu_type ?>' />
<input type='hidden' id='weaponID' name='weapon_id' value='<?= $prefill_weapon_id ?>' />
<input type='hidden' id='jutsuID' name='jutsu_id' value='<?= $prefill_jutsu_id ?>' />
<input type='hidden' id='itemID' name='item_id' value='<?= $prefill_item_id ?>' />
<p style='display:block;text-align:center;margin:auto;'>
<input id='submitbtn' type='submit' name='attack' value='Submit' />
</p>
</form>
</div>
</td></tr>
*/
