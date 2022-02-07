export default function AttackActionPrompt({
  battle
}) {
  /*
  <input type='hidden' id='hand_seal_input' name='hand_seals' value='<?= $prefill_hand_seals ?>' />
  <input type='hidden' id='jutsuType' name='jutsu_type' value='<?= $prefill_jutsu_type ?>' />
  <input type='hidden' id='weaponID' name='weapon_id' value='<?= $prefill_weapon_id ?>' />
  <input type='hidden' id='jutsuID' name='jutsu_id' value='<?= $prefill_jutsu_id ?>' />
  <input type='hidden' id='itemID' name='item_id' value='<?= $prefill_item_id ?>' />
   */
  return /*#__PURE__*/React.createElement("div", null, "Attack Action Prompt");
}
/*
$gold_color = '#FDD017';

$prefill_hand_seals = $_POST['hand_seals'] ?? '';
$prefill_jutsu_type = $_POST['jutsu_type'] ?? Jutsu::TYPE_NINJUTSU;
$prefill_weapon_id = $_POST['weapon_id'] ?? '0';
$prefill_jutsu_id = $_POST['jutsu_id'] ?? '';
$prefill_item_id = $_POST['item_id'] ?? '';

    ?>
<script type='text/javascript'>
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
</script>
<style type='text/css'>
#handSeals {
padding-top: 4px;
position: relative;
}

#handSeals p {
display: inline-block;
width: 80px;
height: 90px;
margin: 4px;
position: relative;
}

#handSeals img {
height: 62px;
width: 62px;
position: relative;
z-index: 1;
border: 3px solid rgba(0, 0, 0, 0);
border-radius: 5px;
}

#handSeals .handsealNumber {
display: none;
width: 18px;
position: absolute;
z-index: 20;
text-align: center;
left: 31px;
right: 31px;
bottom: 30px;
/!* Style *!/
font-size: 14px;
font-weight: bold;
background-color: <?= $gold_color ?>;
border-radius: 10px;
}

#handSeals .handsealTooltip {
display: block;
margin: 0;
text-align: center;
height: 16px;
}

#handsealOverlay {
position: absolute;

top: 0;
bottom: 0;
left: 0;
right: 0;

background-color: rgba(255, 255, 255, 0.9);
z-index: 50;
display: none;
}

/!* WEAPONS *!/
#weapons {
height: 200px;
padding-left: 20px;
padding-right: 20px;
}

#jutsu {
padding-left: 5px;
}

#jutsu .jutsuCategory {
display: inline-block;
margin: 0;
vertical-align: top;
margin-right: 1%;
text-align: center;
}

#jutsu .jutsuName {
display: inline-block;
padding: 5px 7px;
margin-bottom: 10px;
/!* Style *!/
background: linear-gradient(#EFEFEF, #E4E4E4);
border: 1px solid #E0E0E0;
border-radius: 15px;
text-align: center;
box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0);
}

#jutsu .jutsuName:last-child {
margin-bottom: 1px;
}

#jutsu .jutsuName:hover {
background: linear-gradient(#E4E4E4, #EFEFEF);
cursor: pointer;
}

#jutsu .jutsuName[aria-disabled='true'] {
opacity: 0.75;
}

#jutsu .jutsuName[aria-disabled='true']:hover {
background: linear-gradient(#EFEFEF, #E4E4E4);
cursor: default;
}

#weapons p.weapon {
display: inline-block;
padding: 8px 10px;
margin-right: 15px;
vertical-align: top;
/!* Style *!/
background-color: rgba(255, 255, 255, 0.1);
border: 1px solid #C0C0C0;
border-radius: 10px;
text-align: center;
box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0);
}

#weapons p.weapon:last-child {
margin-right: 1px;
}

#weapons p.weapon:hover {
background: rgba(0, 0, 0, 0.1);
cursor: pointer;
}
</style>

<tr><td>
<div id='handSeals'>
<?php for ($i = 1; $i <= 12; $i++): ?>
<p id='handseal_<?= $i ?>' data-selected='no' data-handseal='<?= $i ?>'>
<img src='./images/handseal_<?= $i ?>.png' draggable='false' />
<span class='handsealNumber'>1</span>
<span class='handsealTooltip'>&nbsp;</span>
</p>
<?php if($i == 6): ?>
<br />
<?php endif; ?>
<?php endfor; ?>
<div id='handsealOverlay'>
</div>
</div>
<div id='weapons' style='display:none;'>
<p class='weapon' data-id='0' style='box-shadow: 0 0 4px 0 #000000;margin-top:14px;'>
<b>None</b>
</p>
<?php if(is_array($player->equipped_weapons)): ?>
<?php foreach($player->equipped_weapons as $item_id): ?>
<p class='weapon' data-id='<?= $item_id ?>'>
<b><?= $player->items[$item_id]['name'] ?></b><br />
<?= ucwords(str_replace('_', ' ', $player->items[$item_id]['effect'])) ?>
(<?= $player->items[$item_id]['effect_amount'] ?>%)
</p>
<?php endforeach; ?>
<?php endif; ?>
</div>


</td></tr>
<tr><th>
<?php
if($player->bloodline_id) {
$width = '23.5%';
}
else {
$width = '32%';
}
?>
<span style='display:inline-block;width:<?= $width ?>;'>Ninjutsu</span>
<span style='display:inline-block;width:<?= $width ?>;'>Taijutsu</span>
<span style='display:inline-block;width:<?= $width ?>;'>Genjutsu</span>
<?php if($player->bloodline_id): ?>
<span style='display:inline-block;width:<?= $width ?>;'>Bloodline</span>
<?php endif; ?>
</th></tr>
<tr><td>
<div id='jutsu'>
<?php
$c1_count = 0;
$c2_count = 0;
$c3_count = 0;

// Attack list
$jutsu_types = array(Jutsu::TYPE_NINJUTSU, Jutsu::TYPE_TAIJUTSU, Jutsu::TYPE_GENJUTSU);
?>

<?php for($i = 0; $i < 3; $i++): ?>
<div class='jutsuCategory' style='width:<?= $width ?>;'>
<?php foreach($battleManager->default_attacks as $attack): ?>
<?php if($attack->jutsu_type != $jutsu_types[$i]) continue; ?>
<span   id='default<?= $c1_count ?>'
class='jutsuName <?= $jutsu_types[$i] ?>'
data-handseals='<?= ($attack->jutsu_type != Jutsu::TYPE_TAIJUTSU ? $attack->hand_seals : '') ?>'
data-id='<?= $attack->id ?>'
>
<?= $attack->name ?><br />
<strong>D<?= $c1_count ?></strong>
</span><br />
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
class='jutsuName <?= $jutsu_types[$i] ?>'
data-handseals='<?= $player_jutsu->hand_seals ?>'
data-id='<?= $jutsu['id'] ?>'
aria-disabled='<?= ($cd_left > 0 ? "true" : "false") ?>'
>
<?= $player_jutsu->name ?><br />
<?php if($cd_left > 0): ?>
(CD: <?= $cd_left ?> turns)
<?php else: ?>
<strong><?= strtoupper($jutsu_types[$i][0]) ?><?= $c2_count ?></strong>
<?php endif; ?>
</div><br />
<?php $c2_count++; ?>
<?php endforeach; ?>
<?php $c2_count = 0; ?>
<?php endif; ?>
</div>
<?php endfor; ?>

<!-- Display bloodline jutsu-->
<?php if($player->bloodline_id): ?>
<div class='jutsuCategory' style='width:<?= $width ?>;margin-right:0;'>
<?php if(!empty($player->bloodline->jutsu)): ?>
<?php foreach($player->bloodline->jutsu as $id => $jutsu): ?>
<?php
$jutsu->setCombatId($player->combat_id);
$cd_left = $battle->jutsu_cooldowns[$jutsu->combat_id] ?? 0;
?>

<div
id='bloodline<?= $c3_count ?>'
class='jutsuName bloodline_jutsu'
data-handseals='<?= $jutsu->hand_seals ?>'
data-id='<?= $id ?>'
aria-disabled='<?= ($cd_left > 0 ? "true" : "false") ?>'
>
<?= $jutsu->name ?><br />
<?php if($cd_left > 0): ?>
(CD: <?= $cd_left ?> turns)
<?php else: ?>
<strong>B<?= $c3_count ?></strong>
<?php endif; ?>
</div><br />
<?php $c3_count++; ?>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php endif; ?>

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

<script type='text/javascript'>
setAbilityClickHandlers();
document.addEventListener('keyup', handleKeyUp);
</script>
*/