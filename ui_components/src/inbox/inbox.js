import { formatTitle, timeSince } from "./util.js";
import { apiFetch } from "../utils/network.js";

const Inbox = ({
    inboxAPILink,
    convo_count,
    convo_count_max
}) => {
    const [successMessage, setSuccessMessage] = React.useState(null);
    const [errorMessage, setErrorMessage] = React.useState(null);

    const [convoList, setConvoList] = React.useState(false);
    
    const [convoData, setConvoData] = React.useState(false);

    const [viewDetailsPanel, setViewDetailsPanel] = React.useState(false);
    const [newConvoPanel, setNewConvoPanel] = React.useState(false);
    
    const convoListRefreshDuration = 60000; // 60 seconds
    const convoDataRefreshDuration = 5000; // 5 seconds
    const max_messages_per_fetch = 25;

    // CLASS FOR CONVO CARD BEING VIEWED
    const inboxConvoCardActive = 'inbox_convo_list_container_selected';

    // NEW MESSAGE
    const textboxInput = 'PMSubmitMessageInput;';
    const textboxButton = 'PMSubmitMessageButton';
    const textboxCharCount = 'PMTextboxCharacterCount';

    // CONVO DETAILS ACTIONS
    const addPlayerInput = 'PMAddPlayerInput';
    const addPlayerButton = 'PMAddPlayerButton';
    const changeTitleInput = 'PMChangeTitleInput';
    const changeTitleButton = 'PMChangeTitleButton';

    // NEW CONVO
    const newConvoMembersInput = 'PMNewConvoMembers';
    const newConvoTitleInput = 'PMNewConvoTitle';
    const newConvoMessageInput = 'PMNewConvoMessage';

    // load convos
    React.useEffect(() => {
        // Load the convos and update ever X seconds
        LoadConvoList()
        let timerLoadConvoList = setInterval(() => {LoadConvoList()}, convoListRefreshDuration);
       
        return () => clearInterval(timerLoadConvoList);
    }, []);

    // refresh convo being viewed
    React.useEffect(() => {
        if (convoData) {
            let timerCheckForUpdates = setInterval(() => {
                CheckForUpdates();
            }, convoDataRefreshDuration);

            return () => clearInterval(timerCheckForUpdates);
        }
    }, [convoData]);


    // API ACTIONS
    // check for new messages
    const CheckForUpdates = () => {
        console.log('Checking for new messages...');
        apiFetch(
            inboxAPILink,
            {
                request: 'CheckForNewMessages',
                requested_convo_id: convoData.convo_id,
                timestamp: convoData.all_messages[0].time
            }
        )
        .then(handleAPIResponse);
    }

    // load the left panel convo list
    const LoadConvoList = () => {
        console.log('Updating conversation list....');
         apiFetch(
            inboxAPILink,
            {
                request: 'LoadConvoList'
            }
        )
        .then(handleAPIResponse);
    }

    // view a convo
    const ViewConvo = (view_convo_id) => {
        console.log('Requesting conversation...');
        ResetMessages();
        apiFetch(
            inboxAPILink,
            {
                request: 'ViewConvo',
                requested_convo_id: view_convo_id
            }
        )
        .then(handleAPIResponse);
    };

    const LoadNextPage = (event) => {
        event.preventDefault();
        if (convoData) {
            console.log('Fetching next page...');
            const oldest_message_id = convoData.all_messages[convoData.all_messages.length - 1].message_id;
            apiFetch(
                inboxAPILink,
                {
                    request: 'LoadNextPage',
                    requested_convo_id: convoData.convo_id,
                    oldest_message_id: oldest_message_id
                }
            )
            .then(handleAPIResponse);
        }
    }

    // submit a message in a convo
    const SubmitMessage = () => {
        console.log('Submitting message...');
        apiFetch(
            inboxAPILink,
            {
                request: 'SendMessage',
                requested_convo_id: convoData.convo_id,
                message: document.getElementsByClassName(textboxInput)[0].innerText
            }
        )
        .then(handleAPIResponse);
    }

    // change the title of the current convo
    const ChangeTitle = (convo_id) => {
        console.log('Changing Title...');
        apiFetch(
            inboxAPILink,
            {
                request: 'ChangeTitle',
                requested_convo_id: convo_id,
                new_title: document.getElementsByClassName(changeTitleInput)[0].value
            }
        )
        .then(handleAPIResponse);
    }

    // add a player to the convo
    const AddPlayer = (convo_id) => {
        console.log('Adding player to conversation..');
        apiFetch(
            inboxAPILink,
            {
                request: 'AddPlayer',
                requested_convo_id: convo_id,
                new_player: document.getElementsByClassName(addPlayerInput)[0].value
            }
        )
        .then(handleAPIResponse);
    }

    // remove a player from the convo
    const RemoveFromConvo = (convo_id, user_id) => {
        console.log('Removing player from conversation...');
        apiFetch(
            inboxAPILink,
            {
                request: 'RemovePlayer',
                requested_convo_id: convo_id,
                remove_player: user_id
            }
        )
        .then(handleAPIResponse);
    }

    // leave the conversation
    const LeaveConversation = (convo_id) => {
        console.log('Leaving conversation...');
        apiFetch(
            inboxAPILink,
            {
                request: 'LeaveConversation',
                requested_convo_id: convo_id
            }
        )
        .then(handleAPIResponse);
    }
    
    // create a new convo
    const CreateNewConvo = () => {
        console.log('Creating a new conversation...');
        apiFetch(
            inboxAPILink,
            {
                request: 'CreateNewConvo',
                members: document.getElementsByClassName(newConvoMembersInput)[0].value,
                title: document.getElementsByClassName(newConvoTitleInput)[0].value,
                message: document.getElementsByClassName(newConvoMessageInput)[0].value
            }
        )
        .then(handleAPIResponse);
    }
    // HANDLE FETCH REQUESTS
    const handleAPIResponse = (response) => {

        // Update errors
        if (response.errors.length > 0 && response.errors.isArray) {
            ResetMessages();
            setErrorMessage(response.errors.join(' - '));
        } else if (response.errors.length > 0) {
            ResetMessages();
            setErrorMessage(response.errors);
        }

        if (response.data && response.data.response_data && response.errors.length <= 0) {

            switch(response.data.request) {

                case 'LoadConvoList':
                    console.log('Conversation list loaded successfully.');
                    setConvoList(response.data.response_data); // SET CONVO LIST
                    break;

                case 'ViewConvo':
                    console.log('Viewing conversation.', response);
                    setConvoData(response.data.response_data); // SET CONVO
                    LoadConvoList(); // RELOAD CONVO LIST
                    setViewDetailsPanel(false); // REMOVE THE DETAILS PANEL IF ON
                    setNewConvoPanel(false); // REMOVE THE NEW CONVO PANEL IF ON
                    ClearTextbox(textboxInput, true); // CLEAR THE TEXTBOX
                    break;

                case 'LoadNextPage':
                    if (response.data.response_data.older_messages) {
                        console.log('Rendering older messages.', );
                        setConvoData(updateConvoObject(convoData, response.data.response_data.older_messages, false));
                    }
                    break;

                case 'CheckForNewMessages':
                    // if we get new entries, recreate the convo object with the new messages
                    if (response.data.response_data.new_messages) {
                        console.log('Rendering new messages.');
                        setConvoData(updateConvoObject(convoData, response.data.response_data.new_messages));
                    }
                    break;

                case 'SendMessage':
                    console.log('Message sent.');
                    ViewConvo(convoData.convo_id); // REFRESH CONVO BEING VIEWED

                    // Success Message
                    ResetMessages();
                    setSuccessMessage('Message sent!');
                    break;

                case 'ChangeTitle':
                    console.log('Title changed.');
                    LoadConvoList(); // RELOAD CONVO LIST
                    ClearTextbox(changeTitleInput, false, true);

                    // Success Message
                    ResetMessages();
                    setSuccessMessage('Title Updated!');
                    break;

                case 'AddPlayer':
                    console.log('Player has been added.');
                    ViewConvo(convoData.convo_id); // REFRESH CONVO BEING VIEWED

                    // Success Message
                    ResetMessages();
                    setSuccessMessage('Player Added');
                    break;

                case 'RemovePlayer':
                    console.log('Player has been removed.');
                    ViewConvo(convoData.convo_id); // REFRESH CONVO BEING VIEWED

                    // Success Message
                    ResetMessages();
                    setSuccessMessage('Player has been removed');
                    break;
                
                case 'LeaveConversation':
                    console.log('You have left the conversation.');
                    LoadConvoList(); // RELOAD CONVO LIST
                    setConvoData(false); // REMOVE THE CONVO BEING VIEWED
                    setViewDetails(false); // RESET DETAILS PANEL
                    
                    // Success Message
                    ResetMessages();
                    setSuccessMessage('You have left the conversation');
                    break;

                case 'CreateNewConvo':
                    console.log('Conversation has been created.');
                    LoadConvoList(); // RELOAD CONVO LIST
                    ClearTextbox(newConvoMembersInput, false, true);
                    ClearTextbox(newConvoTitleInput, false, true);
                    ClearTextbox(newConvoMessageInput, false, true);

                    // Success Message
                    ResetMessages();
                    setSuccessMessage('Conversation created!');
                    break;

            }
        }
        
    };

    // Non API Actions
    
    // reset all displayed messages

    // update the convodata object with a new set of messages
    const updateConvoObject = (current_object, new_entries, start_of = true) => {
        const new_object = {};
        for (let [key, value] of Object.entries(current_object)) {
            new_object[key] = value;
        }
        const first_array = start_of ? new_entries : current_object.all_messages;
        const second_array = start_of ? current_object.all_messages : new_entries;
        new_object.all_messages = [
            ...first_array,
            ...second_array
        ];
        return new_object;
    }
    

    const ResetMessages = () => {
        setSuccessMessage(null);
        setErrorMessage(null);
    }

    // update the character count display
    const CountCharacters = () => {
        const max_chars = document.getElementsByClassName(textboxCharCount)[0];
        const textbox = document.getElementsByClassName(textboxInput)[0];
        max_chars.innerText = convoData.max_characters - textbox.innerText.length + 1;
    }

    // shortcuts on input and textboxes
    const InputShortcuts = (event, targetButton) => {
        // Submit action on enter
        // Enter -> 13 --- Shift -> 16
        if (event.keyCode === 13 && !event.shiftKey) {
            event.preventDefault();
            document.getElementsByClassName(targetButton)[0].click();
        }        
    }

    // toggle the view details pane;
    const ToggleViewDetails = () => {
        // toggle
        setViewDetailsPanel(!viewDetailsPanel);

    }

    // toggle the new convo panel that
    const ToggleNewConvoPanel = () => {
        // toggle
        setNewConvoPanel(!newConvoPanel);

        // Reset the convo being viewed
        setConvoData(false);
        setViewDetailsPanel(false);
    }

    // clear textboxes or inputs
    const ClearTextbox = (target, br = false, input_tag = false) => {
        // get the target from the DOM
        const target_clear = document.getElementsByClassName(target)[0];
        // text we will place in the target
        const replace_text = br ? '<br />' : '';
        // check if we're dealing with a <input> or <textbox>
        if (input_tag) {
            target_clear.value = replace_text;
        } else {
            target_clear.innerHTML = replace_text;
        }
    }

    // CUSTOM COMPONENTS
    /**
     * 
     * @param {string} convo_data 
     * @returns 
     */
    const ConvoListCard = ({convo_data}) => {

        // if the user is currently viewing this convo
        const listClass = (convo_data.convo_id == convoData.convo_id && inboxConvoCardActive) + ' inbox_convo_list_container';

        return (
            <div key={convo_data.convo_id} 
                onClick={() => ViewConvo(convo_data.convo_id)} 
                className={ listClass }>
                {/* CONVO SELECT CARD AVATAR */}
                <div className='inbox_convo_avatar'>
                    {/* DISPLAY MAIN AVATAR */}
                    <img src={ convo_data.members[0].avatar_link } />
                    {/* DISPLAY GROUP ONLY AVATAR  */}
                    { convo_data.members.length > 1 && (<img src={ convo_data.members[1].avatar_link } />) }
                </div>
                {/* CONVO SELECT CARD TITLE */}
                <div className='inbox_convo_title'>
                    { formatTitle(convo_data.title, convo_data.members) }
                </div>
                {/* CONVO SELECT CARD MESSAGE TIMESTAMP */}
                <div className='inbox_convo_lastmessage'>
                    { timeSince(convo_data.latest_timestamp, 'single', true) }
                </div>
                {/* CONVO SELECT CARD UNREAD NOTIFICATION */}
                <div className='inbox_convo_unread'>
                    { convo_data.unread && <div></div> }
                </div>
            </div>
        );
    }

    /**
     * 
     * @param {array} member_data
     * @returns 
     */
    const ConvoDetailsMemberCard = ({member_data}) => {

        // if the member is this user return nothing
        if (convoData.self == member_data.user_id) {
            return null;
        }

        return (
            <div key={member_data.user_id} className='inbox_convo_details_member'>
                {/* CONVO DETAILS MEMBER AVATAR */}
                <div className='inbox_convo_details_member_avatar'>
                    <a href={ convoData.profile_link + member_data.user_name }> 
                        <img src={ member_data.avatar_link } />
                    </a>
                </div>
                {/* CONVO DETAILS MEMBER NAME */}
                <div className='inbox_convo_details_member_name'>
                    <a href={ convoData.profile_link + member_data.user_name } 
                        className='userLink'>{ member_data.user_name }</a>
                </div>
                {/* CONVO DETAILS MEMBER OWNER ACTION */}
                { convoData.owner_id == convoData.self && (
                <div className='inbox_convo_details_member_actions'>
                    <a href='#' 
                        onClick={() => RemoveFromConvo(convoData.convo_id, member_data.user_name)} 
                        className='inbox_convo_details_member_remove'></a>
                </div>
                )}
            </div>
        );

    }

    /**
     * 
     * @param {array} message_data 
     * @returns 
     */
    const ConvoMessageCard = ({message_data}) => {

        // if the message was posted by the user
        const messageClass = message_data.self_message ? 'inbox_message inbox_message_self' : 'inbox_message';

        return (
            <div className={messageClass}>
                <div className='inbox_message_avatar'>
                    <a href={ message_data.profile_link }><img src={message_data.avatar_link} /></a>
                </div>
                <div className='inbox_message_container'>
                    <div className='inbox_message_sender'>
                        <a href={ message_data.profile_link } className={message_data.chat_color + ' chat userLink'}>{message_data.user_name}</a>
                    </div>
                    <div className='inbox_message_timestamp'>
                        { timeSince(message_data.time, 'short', true) }
                    </div>
                    <div className='inbox_message_report'>
                        <a href={message_data.report_link}>Report</a>
                    </div>
                    <div className='inbox_message_content'>
                        <pre dangerouslySetInnerHTML={{__html: message_data.message }}></pre>
                    </div>
                </div>
            </div>
        );
    }

    

    return (
    // CONTAINER START
    <div>
        <div id="inbox_new_container">
            {/* BUTTON FOR TOGGLING NEW CONVERSATION PANEL */}
            <a href='#' id='inbox_new' onClick={() => ToggleNewConvoPanel() }><span>New Conversation</span></a>
            {/* SUCCESS MESSAGE */}
            {successMessage && (<p className='systemMessage-new systemMessage-new-success'>{successMessage}</p>)}
            {/* ERROR MESSAGE */}
            {errorMessage && (<p className='systemMessage-new systemMessage-new-error'>{errorMessage}</p>)}
        </div>  

        {/* INBOX */}
        <div id='inbox_container'>
            {/* LEFT PANEL */}
            <div id='inbox_convo_list_container'>
                {/* LEFT PANEL HEADER */}
                <div id='inbox_convo_list_title_container'>
                    <div id='inbox_convo_list_title'>
                        Conversations
                    </div>
                    <div id='inbox_convo_list_count'>
                        <span id='inbox_convo_list_count_active'>
                            {/* DISPLAY COUNT OF ACTIVE CONVERSATIONS */}
                            { convo_count }
                        </span>
                        /
                        <span id='inbox_convo_list_coint_total'>
                            {/* DISPLAY COUNT OF ALLOWED CONVERSATION */}
                            { convo_count_max }
                        </span>
                    </div>
                </div>
                {/* DISPLAY OF ALL ACTIVE CONVERSATIONS */}
                { (convoList) && (
                <div id='inbox_convo_list'>
                    {convoList.map((convo) => (
                        <ConvoListCard key={ convo.convo_id } convo_data={ convo } />
                    ))}
                </div>
                )}
            </div>
            {/* LEFT PANEL */}
            {/* RIGHT PANEL */}
            <div id='inbox_convo_main_container'>
                {/* VIEW CONVO */}
                { (convoData) && (
                // CONVO CONTROLS
                <>
                <div id='inbox_convo_main_controls_container'>
                    {/* SEND NEW MESSAGE CONTROLS */}
                    <div id='inbox_convo_main_controls_textbox'>
                        {/* CHARACTER COUNTER DISPLAY */}
                        <div id='inbox_convo_textbox_characters_remaining' 
                                className={ textboxCharCount }>{ convoData.max_characters }</div>
                        {/* TEXTBOX TO SEND MESSAGES */}
                        <div id='inbox_convo_textbox' 
                                contentEditable='true' 
                                className={ textboxInput } 
                                onKeyUp={() => CountCharacters() } 
                                onKeyDown={( event ) => InputShortcuts( event, textboxButton )} 
                                suppressContentEditableWarning={true}><br /></div>
                    </div>
                    <div id='inbox_convo_main_controls_submit'>
                        {/* BUTTON TO SEND MESSAGES */}
                        <a href='#' 
                            onClick={() => SubmitMessage() } 
                            className={ textboxButton }><div id='inbox_convo_textbox_submit'></div></a>
                    </div>
                    {/* VIEW DETAILS COTNROLS */}
                    <div id='inbox_convo_main_controls_details'>
                        {/* BUTTON TO VIEW DETAILS */}
                        <div className='inbox_convo_controls_details_button' 
                                onClick={() => ToggleViewDetails() }></div>
                    </div>
                </div>
                {/* CONVO */}
                <div id='inbox_convo_container'>
                    {/* CONVO MESSAGE */}
                    {(convoData.all_messages) && convoData.all_messages.map( (message_data) => (
                        <ConvoMessageCard key={message_data.message_id} message_data={message_data} />
                    ))}
                    {(convoData.all_messages.length >= max_messages_per_fetch) && (
                    <div id='inbox_convo_more_container'>
                        <a href='#'
                            id ='inbox_convo_more_button'
                            onClick={(event) => LoadNextPage(event)}>
                            Load More Messages
                        </a>
                    </div>
                    )}
                </div>
                </>)}
                {/* CONVO DETAILS PANEL */}
                { (viewDetailsPanel && convoData) && (
                <div id='inbox_convo_details_container'>
                    <div id='inbox_convo_details_close'>
                        <div className='inbox_convo_details_close_button' 
                                onClick={() => ToggleViewDetails() }>Cancel</div>
                    </div>
                    {/* CONVO DETAILS MEMBERS */}
                    <div id='inbox_convo_details_members_container'>
                        <div className='inbox_convo_details_title'>
                            Members
                        </div>
                        {/* CONVO DETAILS MEMBERS LOOP */}
                        { convoData.convo_members.map((member) => (
                            <ConvoDetailsMemberCard key={ member.user_id } member_data={ member } />
                        ))}
                    </div>
                    {/* CONVO DETAILS ACTIONS */}
                    <div id='inbox_convo_details_actions_container'>
                        <div className='inbox_convo_details_title'>
                            Actions
                        </div>
                        <div className='inbox_convo_details_action'>
                            {/* CONVO DETAILS ACTION LEAVE CONVERSATION */}
                            <button type='button' 
                                    onClick={() => LeaveConversation(convoData.convo_id)}>
                                    Leave Conversation
                            </button>
                        </div>
                        {/* CONVO DETAILS OWNER ACTIONS */}
                        { (convoData['owner_id'] == convoData['self']) && (
                        <>
                            <div className='inbox_convo_details_action'>
                                <div>
                                    {/* ACTION ADD PLAYER INPUT */}
                                    <input  type='text' 
                                            name='invite' 
                                            placeholder='Player Name' 
                                            className={ addPlayerInput } 
                                            onKeyDown={(event) => InputShortcuts(event, addPlayerButton)} />
                                    {/* ACTION ADD PLAYER BUTTON  */}
                                    <button type='button' 
                                            className={ addPlayerButton }
                                            onClick={() => AddPlayer(convoData.convo_id)}>
                                            Add Player
                                    </button>
                                </div>
                                <div>
                                    {/* ACTION CHANGE TITLE INPUT */}
                                    <input  type='text' 
                                            name='title' 
                                            placeholder='Title (Leave blank to remove title)' 
                                            className={ changeTitleInput } 
                                            onKeyDown={(event) => InputShortcuts(event, changeTitleButton)} />
                                    {/* ACTION CHANGE TITLE BUTTON */}
                                    <button type='button' 
                                            className={ changeTitleButton }
                                            onClick={() => ChangeTitle(convoData.convo_id)}>
                                            Change Title
                                    </button>
                                </div>
                            </div>
                        </>
                        )}
                    </div>
                </div>
                )}
                {/* NEW CONVO PANEL */}
                { (newConvoPanel) && (
                <div id='inbox_new_convo_container'>
                    {/* NEW CONVO MEMBERS INPUT */}
                    <input type='text' 
                            className={ newConvoMembersInput } 
                            placeholder='Seperate users with commas' />
                    {/* NEW CONVO TITLE INPUT */}
                    <input type='text' 
                            className={ newConvoTitleInput } 
                            placeholder='Title (Optional)' />
                    {/* NEW CONVO MESSAGE INPUT */}
                    <textarea className={ newConvoMessageInput } 
                                placeholder='Message body'></textarea>
                    {/* NEW CONVO BUTTON */}
                    <button type='button' 
                            onClick={ () => CreateNewConvo() }>
                                Create Conversation
                    </button>
                </div>
                )}
                {/* DEFAULT PANEL */}
                { (!convoData) && (
                <div id='inbox_convo_main_placeholder'>
                    Select a conversation to view it
                </div>
                )}
            </div>
            {/* RIGHT PANEL */}
        </div>
        {/* INBOX */}
    </div>
    // CONTAINER
    );
};

window.Inbox = Inbox;