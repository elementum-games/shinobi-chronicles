/**
 * @param array{{
 * user_id:         int,
 * user_name:       string,
 * target_x:        int,
 * target_y:        int,
 * target_map_id:   int,
 * rank_name:       string,
 * rank_num:            int,
 * village_icon:    string,
 * alignment:       string,
 * attack:          boolean,
 * attack_id:       string,
 * level:           int,
 * battle_id:       int,
 * direction:       string
 * }} player
 */
export const ScoutArea = ({
    mapData,
    scoutData,
    membersLink,
    attackPlayer,
    ranksToView,
    playerId,
}) => {
    return (
        <div className='travel-scout-container'>
            <div className='travel-scout'>
                {(mapData) && scoutData
                    .filter(user => ranksToView[parseInt(user.rank_num)] === true)
                    .map((player_data) => (
                        (player_data.user_id != playerId) &&
                        <Player
                            key={player_data.user_id}
                            player_data={player_data}
                            membersLink={membersLink}
                            attackPlayer={attackPlayer}
                        />
                    )
                )}
            </div>
        </div>
    );
};

const Player = ({
    player_data,
    membersLink,
    attackPlayer,
}) => {
    return (
        <div key={player_data.user_id}
             className={alignmentClass(player_data.alignment)}>
            <div className='travel-scout-name'>
                <a href={membersLink + '&user=' + player_data.user_name}>
                    {player_data.user_name}
                </a>
                <span>Lv.{player_data.level} - {player_data.rank_name}</span>
            </div>
            <div className='travel-scout-location'>
                {player_data.target_x} &#8729; {player_data.target_y}
            </div>
            <div className='travel-scout-faction'>
                <img src={'./' + player_data.village_icon} alt='mist' />
            </div>
            <div className='travel-scout-attack'>
                {(player_data.attack === true && parseInt(player_data.battle_id, 10) === 0) && (
                    <a onClick={() => attackPlayer(player_data.attack_id)}></a>
                )}
                {(player_data.attack === true && parseInt(player_data.battle_id, 10) > 0) && (
                    <span className='in-battle'></span>
                )}
                {(player_data.attack === false && player_data.direction !== 'none') && (
                    <span className={`direction ${player_data.direction}`}></span>
                )}
            </div>
        </div>
    );
}

const alignmentClass = (alignment) => {
    let class_name = 'travel-scout-entry travel-scout-';
    switch (alignment) {
        case 'Ally':
            class_name += 'ally';
            break;
        case 'Enemy':
            class_name += 'enemy';
            break;
        case 'Neutral':
            class_name += 'neutral';
            break;
    }
    return class_name;
}