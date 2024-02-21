// @flow strict-local

type Props = {|
    +username: string,
    +avatarLink: string,
    +villageName: string,
    +seatTitle: string,
    +isProvisional?: boolean,
    +provisionalDaysLabel?: string,
    +seatId?: ?number,
    +playerSeatId?: ?number,
    +onResign?: () => {},
    +onClaim?: () => {},
    +onChallenge?: () => {},
|};
export default function KageDisplay({
    username,
    avatarLink,
    seatTitle,
    villageName,
    isProvisional = false,
    provisionalDaysLabel = "",
    seatId = null,
    playerSeatId = null,
    onResign = null,
    onClaim = null,
    onChallenge = null,
}: Props): React$Node {
    return <div className="kage_container">
        <div className="kage_header">
            <div className="header">Kage</div>
            <div className="kage_kanji">{getKageKanji(villageName)}</div>
        </div>

        {avatarLink &&
            <div className="kage_avatar_wrapper">
                <img className="kage_avatar" src={avatarLink} />
            </div>
        }
        {!avatarLink &&
            <div className="kage_avatar_wrapper_empty">
                <div className="kage_avatar_fill"></div>
            </div>
        }
        <div className="kage_nameplate_wrapper">
            <div className="kage_nameplate_decoration nw"></div>
            <div className="kage_nameplate_decoration ne"></div>
            <div className="kage_nameplate_decoration se"></div>
            <div className="kage_nameplate_decoration sw"></div>
            <div className="kage_name">{username ? username : "---"}</div>
            <div className="kage_title">
                {isProvisional ? seatTitle + ": " + provisionalDaysLabel : seatTitle + " of " + villageName}
            </div>
            {seatId != null && seatId === playerSeatId && onResign &&
                <div className="kage_resign_button" onClick={onResign}>resign</div>
            }
            {seatId == null && onClaim &&
                <div className="kage_claim_button" onClick={onClaim}>claim</div>
            }
            {(seatId != null && seatId !== playerSeatId) && onChallange &&
                <div className="kage_challenge_button" onClick={onChallenge}>challenge</div>
            }
        </div>
    </div>;
}

function getKageKanji(village_name) {
    switch (village_name) {
        case 'Stone': return '土影';
        case 'Cloud': return '雷影';
        case 'Leaf': return '火影';
        case 'Sand': return '風影';
        case 'Mist': return '水影';
    }
}
