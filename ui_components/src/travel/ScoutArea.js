export const ScoutArea = ({mapData, scoutData, attackLink, membersLink}) => {

    return (
        <div className='travelScoutContainer'>
            <table className='table scoutTable'>
                <thead>
                <tr>
                    <th>
                        Name
                    </th>
                    <th>
                        Rank
                    </th>
                    <th>
                        Lvl
                    </th>
                    <th>
                        Village
                    </th>
                    <th>
                        Loc
                    </th>
                    <th>
                        Action
                    </th>
                </tr>
                </thead>
                <tbody>
                {(scoutData && mapData) && scoutData.map((player) => (
                    <tr key={'userid:' + player.user_id}>
                        <td>
                            <a href={membersLink + '&user=' + player.user_name }>{player.user_name}</a>
                        </td>
                        <td>
                            {player.name}
                        </td>
                        <td>
                            {player.level}
                        </td>
                        <td>
                            <img src={player.village_icon}
                                 alt={player.village}
                                 className='scoutTableVillageIcon' />
                            &nbsp;
                            <span className={'scoutTableAlign' + player.alignment}>
                                {player.village}
                            </span>
                        </td>
                        <td>
                            {player.location}
                        </td>
                        <td>
                            <ActionDisplay key={player.user_id}
                                           player_data={player}
                                           map_data={mapData}
                                           attackLink={attackLink} />
                        </td>
                    </tr>
                ))}
                </tbody>
            </table>
        </div>
    );
};

const ActionDisplay = ({player_data, map_data, attackLink}) => {

    let return_attack = false;
    let return_blank = true;
    let return_text = '';

    const battle_id = parseInt(player_data.battle_id, 10);

    // if the user is an ally or enemy
    if (player_data.alignment === 'Ally') {
        return_text = 'Ally';
    } else {
        return_text = 'Protected';
        return_attack = true;
    }

    if (battle_id !== 0) {
        return_text = 'In Battle';
    }

    // if the user is on the same tile
    if (player_data.location === map_data.player_location) {
        return_blank = false;
    }

    // if the user is the display player
    if (parseInt(map_data.self_user_id, 10) === parseInt(player_data.user_id, 10)) {
        return (<span>You</span>);
    }

    // if the user is on the same tile and it's an enemy of the same rank
    if (!return_blank && return_attack && player_data.attackable && battle_id === 0) {
        return (<a href={attackLink+'&attack='+player_data.attack_id}>Attack</a>);
    }

    if (!return_blank) {
        return (<span>{return_text}</span>);
    } else {
        return (<></>);
    }
};