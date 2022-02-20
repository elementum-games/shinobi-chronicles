// @flow
import type { BattleType, FighterType, JutsuType } from "./battleSchema.js";

type JutsuCategory = 'ninjutsu' | 'genjutsu' | 'taijutsu' | "bloodline";

export default function AttackActionPrompt({ battle }: { +battle: BattleType }): React$Node {
    const player = battle.fighters[battle.playerId];
    const opponent = battle.fighters[battle.opponentId];

    const [handSeals, setHandSeals] = React.useState([]);
    const [jutsuId, setJutsuId] = React.useState(-1);
    const [jutsuCategory, setJutsuCategory] = React.useState('ninjutsu');
    const [jutsuType, setJutsuType] = React.useState('ninjutsu');
    const [weaponId, setWeaponId] = React.useState(0);

    const isSelectingHandSeals = ['ninjutsu', 'genjutsu'].includes(jutsuCategory);
    const isSelectingWeapon = jutsuCategory === 'taijutsu';

    const handleJutsuChange = (jutsuId: number, jutsuCategory: JutsuCategory) => {
        setJutsuCategory(jutsuCategory);
        setJutsuId(jutsuId);
    };

    return (
        <React.Fragment>
            <tr>
                <td>
                    {isSelectingHandSeals && <HandSealsInput onChange={setHandSeals} />}
                    {isSelectingWeapon && <WeaponInput fighter={player} />}
                </td>
            </tr>
            <tr>
                <th className='jutsuCategoryHeader'>
                    <div>
                        <span>Ninjutsu</span>
                        <span>Taijutsu</span>
                        <span>Genjutsu</span>
                        {player.hasBloodline && <span>Bloodline</span>}
                    </div>
                </th>
            </tr>
            <tr>
                <td>
                    <JutsuInput
                        battle={battle}
                        player={player}
                        onChange={handleJutsuChange}
                    />
                    <input type='hidden' id='hand_seal_input' name='hand_seals' value='<?= $prefill_hand_seals ?>' />
                    <input type='hidden' id='jutsuType' name='jutsu_type' value='<?= $prefill_jutsu_type ?>' />
                    <input type='hidden' id='weaponID' name='weapon_id' value='<?= $prefill_weapon_id ?>' />
                    <input type='hidden' id='jutsuID' name='jutsu_id' value='<?= $prefill_jutsu_id ?>' />
                    <p style={{ display: "block", textAlign: "center", margin: "auto" }}>
                        <input id='submitbtn' type='submit' name='attack' value='Submit' />
                    </p>
                </td></tr>
        </React.Fragment>
    );
}

function HandSealsInput({ onChange, tooltips = {} }: {
    +onChange: ($ReadOnlyArray<string>) => void,
    +tooltips?: {
        [string]: number
    }
}) {
    const [selectedHandSeals, setSelectedHandSeals] = React.useState([]);
    let handSeals = {
        "1": { selectedIndex: -1 },
        "2": { selectedIndex: -1 },
        "3": { selectedIndex: -1 },
        "4": { selectedIndex: -1 },
        "5": { selectedIndex: -1 },
        "6": { selectedIndex: -1 },
        "7": { selectedIndex: -1 },
        "8": { selectedIndex: -1 },
        "9": { selectedIndex: -1 },
        "10": { selectedIndex: -1 },
        "11": { selectedIndex: -1 },
        "12": { selectedIndex: -1 }
    };
    selectedHandSeals.forEach((hs, i) => {
        handSeals[hs].selectedIndex = i;
    })

    const setHandSealSelected = React.useCallback((num: string, selected: boolean) => {
        if(handSeals[num].selectedIndex !== -1 && !selected) {
            // Deselect
            const index = handSeals[num].selectedIndex;

            let newHandSeals = [
                ...selectedHandSeals.slice(0, index),
                ...selectedHandSeals.slice(index + 1)
            ];
            setSelectedHandSeals(newHandSeals);
            onChange(newHandSeals);
        }
        else if(handSeals[num].selectedIndex === -1 && selected) {
            let newHandSeals = [
                ...selectedHandSeals,
                num
            ];
            setSelectedHandSeals(newHandSeals);
            onChange(newHandSeals);
        }
        else {
            console.log(`tried to set ${num} to ${selected ? 'selected' : 'unselected'} but `, handSeals, selectedHandSeals)
        }
    }, [handSeals, selectedHandSeals]);

    return (
        <div id='handSeals'>
            {Object.keys(handSeals).map((num) => {
                const selected = handSeals[num].selectedIndex !== -1;

                return (
                    <div key={`handseal:${num}`} className="handSealContainer">
                        <div
                            className={`handSeal ${selected ? "selected" : ""}`}
                            onClick={() => setHandSealSelected(num, !selected)}
                        >
                            <img src={`./images/handseal_${num}.png`} draggable='false'/>
                            <span className='handSealNumber' style={{ display: (selected ? "initial" : "none")}}>
                                {(handSeals[num].selectedIndex + 1)}
                            </span>
                            <span className='handsealTooltip'>{tooltips[num] ?? ""}</span>
                        </div>
                    </div>
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

type JutsuInputProps = {|
    +battle: BattleType,
    +player: FighterType,
    +onChange: (id: number, category: JutsuCategory) => void,
|};
function JutsuInput({ battle, player, onChange }: JutsuInputProps) {
    const standardCategories = [
        {
            key: 'ninjutsu',
            jutsuType: battle.jutsuTypes.ninjutsu,
            initial: 'N',
        },
        {
            key: 'taijutsu',
            jutsuType: battle.jutsuTypes.taijutsu,
            initial: 'T',
        },
        {
            key: 'genjutsu',
            jutsuType: battle.jutsuTypes.genjutsu,
            initial: 'G',
        },
    ];

    const [selectedJutsu, setSelectedJutsu] = React.useState({
        id: 0,
        categoryKey: '',
    });

    const handleJutsuSelect = (categoryKey: JutsuCategory, jutsu: JutsuType) => {
        setSelectedJutsu({
            categoryKey: categoryKey,
            id: jutsu.id,
            jutsuType: jutsu.jutsuType
        });
        onChange(jutsu.id, categoryKey);
    };

    return (
        <div id='jutsuContainer'>
            {standardCategories.map((category, x) => {
                let categoryJutsuCount = 1;

                return (
                    <div className='jutsuCategory' key={x}>
                        {battle.playerDefaultAttacks
                            .filter(jutsu => jutsu.jutsuType === category.jutsuType)
                            .map((jutsu, i) => (
                                <Jutsu
                                    key={i}
                                    jutsu={jutsu}
                                    selected={category.key === selectedJutsu.categoryKey && jutsu.id === selectedJutsu.id}
                                    onClick={() => handleJutsuSelect(category.key, jutsu)}
                                    hotkeyDisplay={`${category.initial}${categoryJutsuCount}`}
                                />
                            ))}

                        {battle.playerEquippedJutsu
                            .filter(jutsu => jutsu.jutsuType === category.jutsuType)
                            .map((jutsu, i) => {
                                return (
                                    <Jutsu
                                        key={i}
                                        jutsu={jutsu}
                                        selected={category.key === selectedJutsu.categoryKey && jutsu.id === selectedJutsu.id}
                                        onClick={() => handleJutsuSelect(category.key, jutsu)}
                                        hotkeyDisplay={`${category.initial}${categoryJutsuCount}`}
                                    />
                                )
                            })}
                    </div>
                );
            })}

            {player.hasBloodline && battle.playerBloodlineJutsu.length > 0 && (
                <div className='jutsuCategory'>
                    {battle.playerBloodlineJutsu.map((jutsu, i) => (
                        <Jutsu
                            key={i}
                            jutsu={jutsu}
                            selected={'bloodline' === selectedJutsu.categoryKey && jutsu.id === selectedJutsu.id}
                            onClick={() => handleJutsuSelect('bloodline', jutsu)}
                            hotkeyDisplay={`B${i}`}
                            isBloodline={true}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

function Jutsu({
    jutsu,
    selected,
    onClick,
    hotkeyDisplay,
    isBloodline = false
}: {
    +jutsu: JutsuType,
    +selected: boolean,
    +onClick: () => void,
    +hotkeyDisplay: string,
    +isBloodline?: boolean
}) {
    const classes = [
        'jutsuButton',
        isBloodline ? 'bloodline_jutsu' : jutsu.jutsuType,
        selected ? 'selected' : '',
    ];
    return (
        <div
            className={classes.join(' ')}
            onClick={onClick}
            aria-disabled={jutsu.activeCooldownTurnsLeft > 0}
        >
            {jutsu.name}
            <br />
            {jutsu.activeCooldownTurnsLeft > 0 ?
                <span>{`(CD: ${jutsu.activeCooldownTurnsLeft} turns)`}</span>
                :
                <strong>{hotkeyDisplay}</strong>
            }
        </div>
    );
}

// gold color when selected
//

/*
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
            }*!/
        }
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
*/

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
