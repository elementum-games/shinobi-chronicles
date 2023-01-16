import { timeSince } from "./util.js";
import { apiFetch } from "../utils/network.js";
import ConversationList from "./ConversationList.js";

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
const convoListRefreshDuration = 60000; // 60 seconds
const convoDataRefreshDuration = 5000; // 5 seconds
const max_messages_per_fetch = 25;
const Inbox = ({
  inboxAPILink,
  convo_count,
  convo_count_max,
  sender
}) => {
  const [successMessage, setSuccessMessage] = React.useState(null);
  const [errorMessage, setErrorMessage] = React.useState(null);
  const [convoList, setConvoList] = React.useState(false);
  const [selectedConvoData, setSelectedConvoData] = React.useState(false);
  const [viewDetailsPanel, setViewDetailsPanel] = React.useState(false);
  const [newConvoPanel, setNewConvoPanel] = React.useState(false);
  const [sendToName, setSendToName] = React.useState(sender);

  // load convos
  React.useEffect(() => {
    // Load the convos and update ever X seconds
    LoadConvoList();
    let timerLoadConvoList = setInterval(() => {
      LoadConvoList();
    }, convoListRefreshDuration);
    return () => clearInterval(timerLoadConvoList);
  }, []);

  // refresh convo being viewed
  React.useEffect(() => {
    if (selectedConvoData) {
      let timerCheckForUpdates = setInterval(() => {
        CheckForUpdates();
      }, convoDataRefreshDuration);
      return () => clearInterval(timerCheckForUpdates);
    }
  }, [selectedConvoData]);

  // API ACTIONS
  // check for new messages
  const CheckForUpdates = () => {
    console.log('Checking for new messages...');
    apiFetch(inboxAPILink, {
      request: 'CheckForNewMessages',
      requested_convo_id: selectedConvoData.convo_id,
      timestamp: selectedConvoData.all_messages[0].time
    }).then(handleAPIResponse);
  };

  // load the left panel convo list
  const LoadConvoList = () => {
    console.log('Updating conversation list....');
    apiFetch(inboxAPILink, {
      request: 'LoadConvoList'
    }).then(handleAPIResponse);
  };

  // view a convo
  const ViewConvo = view_convo_id => {
    console.log('Requesting conversation...');
    ResetMessages();
    apiFetch(inboxAPILink, {
      request: 'ViewConvo',
      requested_convo_id: view_convo_id
    }).then(handleAPIResponse);
  };
  const LoadNextPage = event => {
    event.preventDefault();
    if (selectedConvoData) {
      console.log('Fetching next page...');
      const oldest_message_id = selectedConvoData.all_messages[selectedConvoData.all_messages.length - 1].message_id;
      apiFetch(inboxAPILink, {
        request: 'LoadNextPage',
        requested_convo_id: selectedConvoData.convo_id,
        oldest_message_id: oldest_message_id
      }).then(handleAPIResponse);
    }
  };

  // submit a message in a convo
  const SubmitMessage = () => {
    console.log('Submitting message...');
    apiFetch(inboxAPILink, {
      request: 'SendMessage',
      requested_convo_id: selectedConvoData.convo_id,
      message: document.getElementsByClassName(textboxInput)[0].innerText
    }).then(handleAPIResponse);
  };

  // change the title of the current convo
  const ChangeTitle = convo_id => {
    console.log('Changing Title...');
    apiFetch(inboxAPILink, {
      request: 'ChangeTitle',
      requested_convo_id: convo_id,
      new_title: document.getElementsByClassName(changeTitleInput)[0].value
    }).then(handleAPIResponse);
  };

  // add a player to the convo
  const AddPlayer = convo_id => {
    console.log('Adding player to conversation..');
    apiFetch(inboxAPILink, {
      request: 'AddPlayer',
      requested_convo_id: convo_id,
      new_player: document.getElementsByClassName(addPlayerInput)[0].value
    }).then(handleAPIResponse);
  };

  // remove a player from the convo
  const RemoveFromConvo = (convo_id, user_id) => {
    console.log('Removing player from conversation...');
    apiFetch(inboxAPILink, {
      request: 'RemovePlayer',
      requested_convo_id: convo_id,
      remove_player: user_id
    }).then(handleAPIResponse);
  };

  // leave the conversation
  const LeaveConversation = convo_id => {
    console.log('Leaving conversation...');
    apiFetch(inboxAPILink, {
      request: 'LeaveConversation',
      requested_convo_id: convo_id
    }).then(handleAPIResponse);
  };

  // create a new convo
  const CreateNewConvo = () => {
    console.log('Creating a new conversation...');
    apiFetch(inboxAPILink, {
      request: 'CreateNewConvo',
      members: document.getElementsByClassName(newConvoMembersInput)[0].value,
      title: document.getElementsByClassName(newConvoTitleInput)[0].value,
      message: document.getElementsByClassName(newConvoMessageInput)[0].value
    }).then(handleAPIResponse);
  };
  // HANDLE FETCH REQUESTS
  const handleAPIResponse = response => {
    // Update errors
    if (response.errors.length > 0 && response.errors.isArray) {
      ResetMessages();
      setErrorMessage(response.errors.join(' - '));
    } else if (response.errors.length > 0) {
      ResetMessages();
      setErrorMessage(response.errors);
    }
    if (response.data && response.data.response_data && response.errors.length <= 0) {
      switch (response.data.request) {
        case 'LoadConvoList':
          console.log('Conversation list loaded successfully.');
          setConvoList(response.data.response_data); // SET CONVO LIST
          break;
        case 'ViewConvo':
          console.log('Viewing conversation.', response);
          setSelectedConvoData(response.data.response_data); // SET CONVO
          LoadConvoList(); // RELOAD CONVO LIST
          setViewDetailsPanel(false); // REMOVE THE DETAILS PANEL IF ON
          setNewConvoPanel(false); // REMOVE THE NEW CONVO PANEL IF ON
          ClearTextbox(textboxInput, true); // CLEAR THE TEXTBOX
          setSendToName(null); // CLEAR THE NEW CONVO INPUT
          break;
        case 'LoadNextPage':
          if (response.data.response_data.older_messages) {
            console.log('Rendering older messages.');
            setSelectedConvoData(updateConvoObject(selectedConvoData, response.data.response_data.older_messages, false));
          }
          break;
        case 'CheckForNewMessages':
          // if we get new entries, recreate the convo object with the new messages
          if (response.data.response_data.new_messages) {
            console.log('Rendering new messages.');
            setSelectedConvoData(updateConvoObject(selectedConvoData, response.data.response_data.new_messages));
          }
          break;
        case 'SendMessage':
          console.log('Message sent.');
          ViewConvo(selectedConvoData.convo_id); // REFRESH CONVO BEING VIEWED

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
          ViewConvo(selectedConvoData.convo_id); // REFRESH CONVO BEING VIEWED

          // Success Message
          ResetMessages();
          setSuccessMessage('Player Added');
          break;
        case 'RemovePlayer':
          console.log('Player has been removed.');
          ViewConvo(selectedConvoData.convo_id); // REFRESH CONVO BEING VIEWED

          // Success Message
          ResetMessages();
          setSuccessMessage('Player has been removed');
          break;
        case 'LeaveConversation':
          console.log('You have left the conversation.');
          LoadConvoList(); // RELOAD CONVO LIST
          setSelectedConvoData(false); // REMOVE THE CONVO BEING VIEWED
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
          setNewConvoPanel(null);
          setSendToName(null);

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
    new_object.all_messages = [...first_array, ...second_array];
    return new_object;
  };
  const ResetMessages = () => {
    setSuccessMessage(null);
    setErrorMessage(null);
  };

  // update the character count display
  const CountCharacters = () => {
    const max_chars = document.getElementsByClassName(textboxCharCount)[0];
    const textbox = document.getElementsByClassName(textboxInput)[0];
    max_chars.innerText = selectedConvoData.max_characters - textbox.innerText.length + 1;
  };

  // shortcuts on input and textboxes
  const InputShortcuts = (event, targetButton) => {
    // Submit action on enter
    // Enter -> 13 --- Shift -> 16
    if (event.keyCode === 13 && !event.shiftKey) {
      event.preventDefault();
      document.getElementsByClassName(targetButton)[0].click();
    }
  };

  // toggle the view details pane;
  const ToggleViewDetails = () => {
    // toggle
    setViewDetailsPanel(!viewDetailsPanel);
  };

  // toggle the new convo panel that
  const ToggleNewConvoPanel = () => {
    // toggle
    setNewConvoPanel(!newConvoPanel);

    // Reset the convo being viewed
    setSelectedConvoData(false);
    setViewDetailsPanel(false);
  };

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
  };
  return (
    /*#__PURE__*/
    // CONTAINER START
    React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
      id: "inbox_new_container"
    }, /*#__PURE__*/React.createElement("a", {
      href: "#",
      id: "inbox_new",
      onClick: () => ToggleNewConvoPanel()
    }, /*#__PURE__*/React.createElement("span", null, "New Conversation")), successMessage && /*#__PURE__*/React.createElement("p", {
      className: "systemMessage-new systemMessage-new-success"
    }, successMessage), errorMessage && /*#__PURE__*/React.createElement("p", {
      className: "systemMessage-new systemMessage-new-error"
    }, errorMessage)), /*#__PURE__*/React.createElement("div", {
      id: "inbox_container"
    }, /*#__PURE__*/React.createElement(ConversationList, {
      convoCount: convo_count,
      convoCountMax: convo_count_max,
      convoList: convoList,
      selectedConvoData: selectedConvoData,
      viewConvo: ViewConvo
    }), /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_main_container"
    }, selectedConvoData &&
    /*#__PURE__*/
    // CONVO CONTROLS
    React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_main_controls_container"
    }, /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_main_controls_textbox"
    }, /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_textbox_characters_remaining",
      className: textboxCharCount
    }, selectedConvoData.max_characters), /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_textbox",
      contentEditable: "true",
      className: textboxInput,
      onKeyUp: () => CountCharacters(),
      onKeyDown: event => InputShortcuts(event, textboxButton),
      suppressContentEditableWarning: true
    }, /*#__PURE__*/React.createElement("br", null))), /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_main_controls_submit"
    }, /*#__PURE__*/React.createElement("a", {
      href: "#",
      onClick: () => SubmitMessage(),
      className: textboxButton
    }, /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_textbox_submit"
    }))), /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_main_controls_details"
    }, /*#__PURE__*/React.createElement("div", {
      className: "inbox_convo_controls_details_button",
      onClick: () => ToggleViewDetails()
    }))), /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_container"
    }, selectedConvoData.all_messages && selectedConvoData.all_messages.map(message_data => /*#__PURE__*/React.createElement(ConvoMessageCard, {
      key: "convoMessageCard:" + message_data.message_id,
      message_data: message_data,
      convo_data: selectedConvoData
    })), selectedConvoData.all_messages.length >= max_messages_per_fetch && /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_more_container"
    }, /*#__PURE__*/React.createElement("a", {
      href: "#",
      id: "inbox_convo_more_button",
      onClick: event => LoadNextPage(event)
    }, "Load More Messages")))), viewDetailsPanel && selectedConvoData && /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_details_container"
    }, /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_details_close"
    }, /*#__PURE__*/React.createElement("div", {
      className: "inbox_convo_details_close_button",
      onClick: () => ToggleViewDetails()
    }, "Cancel")), /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_details_members_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "inbox_convo_details_title"
    }, "Members"), selectedConvoData.convo_members.map(member => /*#__PURE__*/React.createElement(ConvoDetailsMemberCard, {
      key: member.user_id,
      member_data: member,
      convoData: selectedConvoData,
      onClick: () => RemoveFromConvo(selectedConvoData.convo_id, member.user_name)
    }))), /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_details_actions_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "inbox_convo_details_title"
    }, "Actions"), /*#__PURE__*/React.createElement("div", {
      className: "inbox_convo_details_action"
    }, /*#__PURE__*/React.createElement("button", {
      type: "button",
      onClick: () => LeaveConversation(selectedConvoData.convo_id)
    }, "Leave Conversation")), parseInt(selectedConvoData.owner_id, 10) === selectedConvoData.self && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "inbox_convo_details_action"
    }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
      type: "text",
      name: "invite",
      placeholder: "Player Name",
      className: addPlayerInput,
      onKeyDown: event => InputShortcuts(event, addPlayerButton)
    }), /*#__PURE__*/React.createElement("button", {
      type: "button",
      className: addPlayerButton,
      onClick: () => AddPlayer(selectedConvoData.convo_id)
    }, "Add Player")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
      type: "text",
      name: "title",
      placeholder: "Title (Leave blank to remove title)",
      className: changeTitleInput,
      onKeyDown: event => InputShortcuts(event, changeTitleButton)
    }), /*#__PURE__*/React.createElement("button", {
      type: "button",
      className: changeTitleButton,
      onClick: () => ChangeTitle(selectedConvoData.convo_id)
    }, "Change Title")))))), (newConvoPanel || sendToName) && /*#__PURE__*/React.createElement("div", {
      id: "inbox_new_convo_container"
    }, /*#__PURE__*/React.createElement("input", {
      type: "text",
      className: newConvoMembersInput,
      placeholder: "Seperate users with commas",
      defaultValue: sendToName
    }), /*#__PURE__*/React.createElement("input", {
      type: "text",
      className: newConvoTitleInput,
      placeholder: "Title (Optional)"
    }), /*#__PURE__*/React.createElement("textarea", {
      className: newConvoMessageInput,
      placeholder: "Message body"
    }), /*#__PURE__*/React.createElement("button", {
      type: "button",
      onClick: () => CreateNewConvo()
    }, "Create Conversation")), !selectedConvoData && /*#__PURE__*/React.createElement("div", {
      id: "inbox_convo_main_placeholder"
    }, "Select a conversation to view it"))))
    // CONTAINER
  );
};

/**
 *
 * @param {array} member_data
 * @param convoData
 * @returns
 */
const ConvoDetailsMemberCard = ({
  member_data,
  convoData,
  onClick
}) => {
  // if the member is this user return nothing
  if (convoData.self === member_data.user_id) {
    return null;
  }
  return /*#__PURE__*/React.createElement("div", {
    key: member_data.user_id,
    className: "inbox_convo_details_member"
  }, /*#__PURE__*/React.createElement("div", {
    className: "inbox_convo_details_member_avatar"
  }, /*#__PURE__*/React.createElement("a", {
    href: convoData.profile_link + member_data.user_name
  }, /*#__PURE__*/React.createElement("img", {
    src: member_data.avatar_link
  }))), /*#__PURE__*/React.createElement("div", {
    className: "inbox_convo_details_member_name"
  }, /*#__PURE__*/React.createElement("a", {
    href: convoData.profile_link + member_data.user_name,
    className: "userLink"
  }, member_data.user_name)), parseInt(convoData.owner_id, 10) === convoData.self && /*#__PURE__*/React.createElement("div", {
    className: "inbox_convo_details_member_actions"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#",
    onClick: onClick,
    className: "inbox_convo_details_member_remove"
  })));
};

/**
 *
 * @param {array} message_data
 * @returns
 */
const ConvoMessageCard = ({
  message_data,
  convo_data
}) => {
  // if the message was posted by the user
  const messageClass = message_data.self_message ? 'inbox_message inbox_message_self' : 'inbox_message';
  return /*#__PURE__*/React.createElement("div", {
    className: messageClass
  }, /*#__PURE__*/React.createElement("div", {
    className: "inbox_message_avatar"
  }, /*#__PURE__*/React.createElement("a", {
    href: convo_data.profile_link + message_data.user_name
  }, /*#__PURE__*/React.createElement("img", {
    src: message_data.avatar_link
  }))), /*#__PURE__*/React.createElement("div", {
    className: "inbox_message_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "inbox_message_sender"
  }, /*#__PURE__*/React.createElement("a", {
    href: convo_data.profile_link + message_data.user_name,
    className: message_data.chat_color + ' chat userLink'
  }, message_data.user_name)), /*#__PURE__*/React.createElement("div", {
    className: "inbox_message_timestamp"
  }, timeSince(message_data.time, 'short', true)), /*#__PURE__*/React.createElement("div", {
    className: "inbox_message_report"
  }, /*#__PURE__*/React.createElement("a", {
    href: convo_data.report_link + message_data.message_id
  }, "Report")), /*#__PURE__*/React.createElement("div", {
    className: "inbox_message_content"
  }, /*#__PURE__*/React.createElement("pre", {
    dangerouslySetInnerHTML: {
      __html: message_data.message
    }
  }))));
};
window.Inbox = Inbox;