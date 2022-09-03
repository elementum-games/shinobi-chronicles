// @flow strict-local

import type { BattleType, FighterType, JutsuType } from "./battleSchema.js";

import type { JutsuCategory } from "./battleSchema.js";

type JutsuInputProps = {|
    +battle: BattleType,
    +player: FighterType,
    +onChange: (id: number, category: JutsuCategory) => void,
|};
export function JutsuInput({ battle, player, onChange }: JutsuInputProps): React$Node {
    const [selectedCategory, setSelectedCategory] = React.useState<?JutsuCategory>(null);

    const playerJutsu = [...battle.playerDefaultAttacks, ...battle.playerEquippedJutsu];

    const jutsuCategories = {
        ninjutsu: {
            key: 'ninjutsu',
            initial: 'Q',
            jutsu: playerJutsu.filter(jutsu => jutsu.jutsuType === battle.jutsuTypes.ninjutsu),
        },
        taijutsu: {
            key: 'taijutsu',
            initial: 'W',
            jutsu: playerJutsu.filter(jutsu => jutsu.jutsuType === battle.jutsuTypes.taijutsu),
        },
        genjutsu: {
            key: 'genjutsu',
            initial: 'E',
            jutsu: playerJutsu.filter(jutsu => jutsu.jutsuType === battle.jutsuTypes.genjutsu),
        },
        bloodline: {
            key: 'bloodline',
            initial: 'R',
            jutsu: battle.playerBloodlineJutsu
        }
    };
    const jutsuCategoryKeys: $ReadOnlyArray<$Keys<typeof jutsuCategories>> = [
        'ninjutsu', 'taijutsu', 'genjutsu', 'bloodline'
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

    const handleKeyDown = (event: KeyboardEvent) => {
        // Check for category select
        for(let categoryKey of Object.keys(jutsuCategories)) {
            const category = jutsuCategories[categoryKey];

            if(event.key === category.initial.toLowerCase() || event.key === category.initial.toUpperCase()) {
                setSelectedCategory(category.key);
                return;
            }
        }

        // Check for jutsu select
        let numericKey = parseInt(event.key);
        if(!isNaN(numericKey) && selectedCategory != null) {
            // Offset to avoid using 0 as a visible number
            numericKey -= 1;

            const category = jutsuCategories[selectedCategory];
            if(category == null) {
                return;
            }

            if(category?.jutsu[numericKey] != null) {
                handleJutsuSelect(selectedCategory, category.jutsu[numericKey]);
            }
        }
    };
    React.useEffect(() => {
        document.addEventListener('keydown', handleKeyDown);

        return () => document.removeEventListener('keydown', handleKeyDown);
    });

    return (
        <div id='jutsuContainer'>
            {jutsuCategoryKeys.map((categoryKey) => {
                const category = jutsuCategories[categoryKey];

                if(category.key === 'bloodline' && !player.hasBloodline) {
                    return null;
                }

                return (
                    <div className='jutsuCategory' key={categoryKey}>
                        {category.jutsu.map((jutsu, i) => {
                            return (
                                <Jutsu
                                    key={i}
                                    jutsu={jutsu}
                                    selected={category.key === selectedJutsu.categoryKey && jutsu.id === selectedJutsu.id}
                                    onClick={() => handleJutsuSelect(category.key, jutsu)}
                                    hotkeyDisplay={category.key === selectedCategory
                                        ? `${i + 1}`
                                        : `${category.initial}${i + 1}`
                                    }
                                    isBloodline={category.key === 'bloodline'}
                                />
                            )
                        })}
                    </div>
                );
            })}
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