import { formatTitle, timeSince } from "./util.js";

export default function ConversationList({ convoCount, convoCountMax, convoList, selectedConvoData, viewConvo }) {
    return (
        <div id='inbox_convo_list_container'>
            {/* LEFT PANEL HEADER */}
            <div id='inbox_convo_list_title_container'>
                <div id='inbox_convo_list_title'>
                    Conversations
                </div>
                <div id='inbox_convo_list_count'>
                    <span id='inbox_convo_list_count_active'>
                        {/* DISPLAY COUNT OF ACTIVE CONVERSATIONS */}
                        { convoCount }
                    </span>
                    /
                    <span id='inbox_convo_list_coint_total'>
                        {/* DISPLAY COUNT OF ALLOWED CONVERSATION */}
                        { convoCountMax }
                    </span>
                </div>
            </div>
            {/* DISPLAY OF ALL ACTIVE CONVERSATIONS */}
            { (convoList) && (
                <div id='inbox_convo_list'>
                    {convoList.map((convo) => (
                        <ConvoListCard
                            key={ convo.convo_id }
                            convo={ convo }
                            selectedConvo={selectedConvoData}
                            viewConvo={viewConvo}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

// CUSTOM COMPONENTS
const ConvoListCard = ({convo, selectedConvo, viewConvo }) => {

    // if the user is currently viewing this convo
    const listClass = (convo.convo_id === selectedConvo.convo_id && 'inbox_convo_list_container_selected')
        + ' inbox_convo_list_container';

    return (
        <div key={convo.convo_id}
             onClick={() => viewConvo(convo.convo_id)}
             className={ listClass }>

            {/* CONVO SELECT CARD AVATAR */}
            <div className='inbox_convo_avatar'>
                {/* DISPLAY MAIN AVATAR */}
                <img src={ convo.members[0].avatar_link } />
                {/* DISPLAY GROUP ONLY AVATAR  */}
                { convo.members.length > 1 && (<img src={ convo.members[1].avatar_link } />) }
            </div>

            {/* CONVO SELECT CARD TITLE */}
            <div className='inbox_convo_title'>
                { formatTitle(convo.title, convo.members) }
            </div>

            {/* CONVO SELECT CARD MESSAGE TIMESTAMP */}
            <div className='inbox_convo_lastmessage'>
                { timeSince(convo.latest_timestamp, 'single', true) }
            </div>

            {/* CONVO SELECT CARD UNREAD NOTIFICATION */}
            <div className='inbox_convo_unread'>
                { convo.unread && <div></div> }
            </div>
        </div>
    );
}