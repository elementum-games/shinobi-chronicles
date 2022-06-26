// @flow strict-local

import type { BattleType, FighterType, JutsuType } from "./battleSchema.js";

import type { JutsuCategory } from "./battleSchema.js";

type JutsuInputProps = {|
    +battle: BattleType,
    +player: FighterType,
    +onChange: (id: number, category: JutsuCategory) => void,
|};
export function JutsuInput({ battle, player, onChange }: JutsuInputProps): React$Node {
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