// @flow

// Fighters and Field
import type { BattleType as BattleData, JutsuType } from "./battleSchema.js";
import type { AttackInputFields } from "./AttackActionPrompt.js";

import FighterDisplay from "./FighterDisplay.js";
import BattleField from "./BattleField.js";

type FightersAndFieldProps = {|
    +battle: BattleData,
    +attackInput: AttackInputFields,
    +membersLink: string,
    +isSelectingTile: boolean,
    +selectedJutsu: ?JutsuType,
    +onTileSelect: (tileIndex: number) => void,
|};

export function FightersAndField({
    battle,
    attackInput,
    membersLink,
    isSelectingTile,
    selectedJutsu,
    onTileSelect
}: FightersAndFieldProps): React$Node {
    const player = battle.fighters[ battle.playerId ];
    const opponent = battle.fighters[ battle.opponentId ];

    const { fighters, field, isSpectating } = battle;

    const handleTileSelect = (tileIndex) => {
        onTileSelect(tileIndex);
    };

    let status = '';
    if(battle.isPreparationPhase) {
        status = 'Prepare to Fight';
    }
    else if(battle.isMovementPhase) {
        status = 'Setup / Move';
    }
    else {
        status = 'Attack';
    }

    return (
        <table className='table'>
            <tbody>
            <tr>
                <th style={{ width: "50%" }}>
                    <a href={`${membersLink}}&user=${player.name}`} style={{ textDecoration: "none" }}>
                        {player.name}
                    </a>
                </th>
                <th style={{ width: "50%" }}>
                    {opponent.isNpc ?
                        opponent.name
                        :
                        <a href={`${membersLink}}&user=${opponent.name}`} style={{ textDecoration: "none" }}>
                            {opponent.name}
                        </a>
                    }
                </th>
            </tr>
            <tr>
                <td colSpan='2'>
                    <div className="fightersRow">
                        <FighterDisplay
                            fighter={player}
                            showChakra={!isSpectating}
                        />
                        <div className="battleStatus">
                            <TimeRemaining
                                turnCount={battle.turnCount}
                                turnSecondsRemaining={battle.turnSecondsRemaining}
                            />
                            <div className='status'>{status}</div>
                        </div>
                        <FighterDisplay
                            fighter={opponent}
                            isOpponent={true}
                            showChakra={!isSpectating}
                        />
                    </div>
                </td>
            </tr>
            <tr>
                <td colSpan='2'>
                    <BattleField
                        player={player}
                        fighters={fighters}
                        tiles={field.tiles}
                        fighterLocations={field.fighterLocations}
                        selectedJutsu={selectedJutsu}
                        isMovementPhase={battle.isMovementPhase}
                        lastTurnLog={battle.lastTurnLog}
                        onTileSelect={handleTileSelect}
                    />
                </td>
            </tr>
            </tbody>
        </table>
    );
}


function TimeRemaining({
    turnSecondsRemaining,
    turnCount
}) {
    const [secondsRemaining, setSecondsRemaining] = React.useState(turnSecondsRemaining);

    React.useEffect(() => {
        setSecondsRemaining(turnSecondsRemaining);
    }, [turnCount]);

    React.useEffect(() => {
        const decrementTimeRemaining = () => {
            setSecondsRemaining(prevSeconds => (
                prevSeconds <= 0
                    ? 0
                    : prevSeconds - 1
            ));
        };
        const intervalId = setInterval(decrementTimeRemaining, 1000);

        return () => clearInterval(intervalId);
    }, []);


    return <div className='turnTimeLeft'>
        {secondsRemaining}
    </div>;
}
