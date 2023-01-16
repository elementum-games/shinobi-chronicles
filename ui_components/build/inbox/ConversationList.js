import { formatTitle, timeSince } from "./util.js";
export default function ConversationList({
  convoCount,
  convoCountMax,
  convoList,
  selectedConvoData,
  viewConvo
}) {
  return /*#__PURE__*/React.createElement("div", {
    id: "inbox_convo_list_container"
  }, /*#__PURE__*/React.createElement("div", {
    id: "inbox_convo_list_title_container"
  }, /*#__PURE__*/React.createElement("div", {
    id: "inbox_convo_list_title"
  }, "Conversations"), /*#__PURE__*/React.createElement("div", {
    id: "inbox_convo_list_count"
  }, /*#__PURE__*/React.createElement("span", {
    id: "inbox_convo_list_count_active"
  }, convoCount), "/", /*#__PURE__*/React.createElement("span", {
    id: "inbox_convo_list_coint_total"
  }, convoCountMax))), convoList && /*#__PURE__*/React.createElement("div", {
    id: "inbox_convo_list"
  }, convoList.map(convo => /*#__PURE__*/React.createElement(ConvoListCard, {
    key: convo.convo_id,
    convo: convo,
    selectedConvo: selectedConvoData,
    viewConvo: viewConvo
  }))));
} // CUSTOM COMPONENTS

const ConvoListCard = ({
  convo,
  selectedConvo,
  viewConvo
}) => {
  // if the user is currently viewing this convo
  const listClass = (convo.convo_id === selectedConvo.convo_id && 'inbox_convo_list_container_selected') + ' inbox_convo_list_container';
  return /*#__PURE__*/React.createElement("div", {
    key: convo.convo_id,
    onClick: () => viewConvo(convo.convo_id),
    className: listClass
  }, /*#__PURE__*/React.createElement("div", {
    className: "inbox_convo_avatar"
  }, /*#__PURE__*/React.createElement("img", {
    src: convo.members[0].avatar_link
  }), convo.members.length > 1 && /*#__PURE__*/React.createElement("img", {
    src: convo.members[1].avatar_link
  })), /*#__PURE__*/React.createElement("div", {
    className: "inbox_convo_title"
  }, formatTitle(convo.title, convo.members)), /*#__PURE__*/React.createElement("div", {
    className: "inbox_convo_lastmessage"
  }, timeSince(convo.latest_timestamp, 'single', true)), /*#__PURE__*/React.createElement("div", {
    className: "inbox_convo_unread"
  }, convo.unread && /*#__PURE__*/React.createElement("div", null)));
};