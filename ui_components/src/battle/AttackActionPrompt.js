// @flow
import type { BattleType } from "./battleSchema.js";

import { JutsuInput } from "./JutsuInput.js";

import { unSlug } from "../utils/string.js";

import type { JutsuCategory } from "./battleSchema.js";
import { findPlayerJutsu } from "./playerUtils.js";

export type AttackInputFields = {|
    handSeals: $ReadOnlyArray<string>,
    jutsuId: number,
    jutsuCategory: JutsuCategory,
    jutsuType: 'ninjutsu' | 'genjutsu' | 'taijutsu',
    weaponId: number,
    targetTileIndex: ?number
|};


type Props = {|
    +battle: BattleType,
    +selectedAttack: AttackInputFields,
    +updateSelectedAttack: ($Shape<AttackInputFields>) => void,
|};

export default function AttackActionPrompt({
    battle,
    selectedAttack,
    updateSelectedAttack
}: Props): React$Node {
    const player = battle.fighters[battle.playerId];
    const opponent = battle.fighters[battle.opponentId];

    const {
        jutsuId,
        jutsuCategory,
        jutsuType,
        handSeals,
        weaponId
    } = selectedAttack;

    const isSelectingHandSeals = ['ninjutsu', 'genjutsu'].includes(jutsuCategory);
    const isSelectingWeapon = jutsuCategory === 'taijutsu';

    const handleHandSealsChange = (handSeals: $ReadOnlyArray<string>) => {
        updateSelectedAttack({ handSeals });
    };

    const handleJutsuChange = (jutsuId: number, newJutsuCategory: JutsuCategory) => {
        let newSelectedAttack: $Shape<AttackInputFields> = {
            jutsuCategory: newJutsuCategory,
            jutsuId
        };

        const jutsu = findPlayerJutsu(battle, jutsuId, newJutsuCategory === 'bloodline');

        if (jutsu != null) {
            newSelectedAttack.jutsuType = jutsu.jutsuType;
            if (newJutsuCategory === "ninjutsu" || newJutsuCategory === "genjutsu") {
                newSelectedAttack.handSeals = jutsu.handSeals;
            }
            else {
                newSelectedAttack.handSeals = [];
            }
        }
        else {
            console.error("Invalid jutsu!");
        }

        updateSelectedAttack(newSelectedAttack);
    };

    const handleWeaponChange = (weaponId: number) => {
        console.log("Weapon selected ", weaponId);
        updateSelectedAttack({ weaponId });
    };

    return (
        <React.Fragment>
            <tr>
                <td>
                    {isSelectingHandSeals && <HandSealsInput initialHandSeals={handSeals} onChange={handleHandSealsChange} />}
                    {isSelectingWeapon &&
                        <WeaponInput
                            weapons={battle.playerEquippedWeapons}
                            selectedWeaponId={weaponId}
                            onChange={handleWeaponChange}
                        />
                    }
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
                </td></tr>
        </React.Fragment>
    );
}

type HandSealsInputProps = {|
    +initialHandSeals: $ReadOnlyArray<string>,
    +onChange: ($ReadOnlyArray<string>) => void,
    +tooltips?: { [string]: number }
|};
function HandSealsInput({ initialHandSeals, onChange, tooltips = {} }: HandSealsInputProps) {
    const [selectedHandSeals, setSelectedHandSeals] = React.useState(initialHandSeals);
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

    React.useEffect(() => {
        setSelectedHandSeals(initialHandSeals)
    }, [initialHandSeals])

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

function WeaponInput({ weapons, selectedWeaponId, onChange }) {
    return (
        <div id='weapons'>
            <p style={{textAlign: "center", fontStyle: "italic"}}>Please select a weapon to augment your Taijutsu with:</p>
            <p
                className={`weapon ${selectedWeaponId === 0 ? 'selected' : ''}`}
                onClick={() => onChange(0)}
            >
                <b>None</b>
            </p>
            {weapons.map((weapon, i) => (
                <p
                    key={i}
                    className={`weapon ${selectedWeaponId === weapon.id ? 'selected' : ''}`}
                    onClick={() => onChange(weapon.id)}
                >
                    <b>{weapon.name}</b><br/>
                    {unSlug(weapon.effect)} ({weapon.effectAmount}%)
                </p>
            ))}
        </div>
    );
}
