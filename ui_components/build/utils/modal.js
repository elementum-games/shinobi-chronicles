export function Modal({
  isOpen,
  header,
  text,
  ContentComponent,
  componentProps,
  onConfirm,
  onClose
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, isOpen && "closed" && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "modal_backdrop"
  }), /*#__PURE__*/React.createElement("div", {
    className: "modal"
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal_header"
  }, header), /*#__PURE__*/React.createElement("div", {
    className: "modal_text"
  }, text), ContentComponent && /*#__PURE__*/React.createElement("div", {
    className: "modal_content"
  }, /*#__PURE__*/React.createElement(ContentComponent, componentProps)), onConfirm && /*#__PURE__*/React.createElement("div", {
    className: "modal_confirm_button",
    onClick: () => onConfirm()
  }, "confirm"), /*#__PURE__*/React.createElement("div", {
    className: "modal_cancel_button",
    onClick: () => onClose()
  }, "cancel"))));
}