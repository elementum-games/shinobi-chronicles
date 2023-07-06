import { clickOnEnter } from "../utils/uiHelpers.js";
export function RegisterForm({
  registerErrorText,
  registerPreFill
}) {
  const formRef = React.useRef(null);
  return /*#__PURE__*/React.createElement("form", {
    id: "register_form",
    action: "",
    method: "post",
    ref: formRef
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_input_top"
  }, /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: "register",
    value: "register"
  }), /*#__PURE__*/React.createElement("div", {
    className: "register_username_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_username_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "register_field_label"
  }, "username"), /*#__PURE__*/React.createElement("input", {
    type: "text",
    name: "user_name",
    className: "register_username_input login_text_input",
    defaultValue: registerPreFill.user_name
  }))), /*#__PURE__*/React.createElement("div", {
    className: "register_password_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_password_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "register_field_label"
  }, "password"), /*#__PURE__*/React.createElement("input", {
    type: "password",
    name: "password",
    className: "register_password_input login_text_input"
  })), /*#__PURE__*/React.createElement("div", {
    className: "register_confirm_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "register_field_label"
  }, "confirm password"), /*#__PURE__*/React.createElement("input", {
    type: "password",
    name: "confirm_password",
    className: "register_password_confirm login_text_input"
  }))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "register_email_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "register_field_label"
  }, "email"), /*#__PURE__*/React.createElement("input", {
    type: "text",
    name: "email",
    className: "login_username_input login_text_input",
    defaultValue: registerPreFill.email
  }))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "register_email_notice"
  }, "(Note: Currently we cannot send emails to addresses from:", /*#__PURE__*/React.createElement("br", null), "hotmail.com, live.com, msn.com, outlook.com)")), /*#__PURE__*/React.createElement("div", {
    className: "register_character_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_gender_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_field_label"
  }, "gender"), /*#__PURE__*/React.createElement("select", {
    name: "gender",
    defaultValue: registerPreFill.gender
  }, /*#__PURE__*/React.createElement("option", {
    value: "Male"
  }, "Male"), /*#__PURE__*/React.createElement("option", {
    value: "Female"
  }, "Female"), /*#__PURE__*/React.createElement("option", {
    value: "Non-binary"
  }, "Non-binary"), /*#__PURE__*/React.createElement("option", {
    value: "None"
  }, "None"))), /*#__PURE__*/React.createElement("div", {
    className: "register_village_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_field_label"
  }, "village"), /*#__PURE__*/React.createElement("select", {
    name: "village",
    defaultValue: registerPreFill.village
  }, /*#__PURE__*/React.createElement("option", {
    value: "Stone"
  }, "Stone"), /*#__PURE__*/React.createElement("option", {
    value: "Cloud"
  }, "Cloud"), /*#__PURE__*/React.createElement("option", {
    value: "Leaf"
  }, "Leaf"), /*#__PURE__*/React.createElement("option", {
    value: "Sand"
  }, "Sand"), /*#__PURE__*/React.createElement("option", {
    value: "Mist"
  }, "Mist")))), /*#__PURE__*/React.createElement(CreateCharacterButton, {
    onClick: () => formRef.current?.submit()
  }), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "register_terms_notice"
  }, "By clicking 'Create a Character' I affirm that I have read and agree to abide by the Rules and Terms of Service. I understand that if I fail to abide by the rules as determined by the moderating staff, I may be temporarily or permanently banned and that I will not be compensated for time lost. I also understand that any actions taken by anyone on my account are my responsibility."))), registerErrorText !== "" && /*#__PURE__*/React.createElement("div", {
    className: "register_input_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_error_label",
    style: {
      marginBottom: "30px",
      marginLeft: "30px",
      marginTop: "-15px"
    }
  }, registerErrorText)));
}
export function CreateCharacterButton({
  onClick
}) {
  return /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    name: "register",
    className: "register_button",
    width: "162",
    height: "32",
    onClick: onClick,
    onKeyPress: clickOnEnter
  }, /*#__PURE__*/React.createElement("radialGradient", {
    id: "register_fill_default",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#84314e',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#68293f',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("radialGradient", {
    id: "register_fill_click",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#68293f',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#84314e',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("rect", {
    className: "register_button_background",
    width: "100%",
    height: "100%"
  }), /*#__PURE__*/React.createElement("text", {
    className: "register_button_shadow_text",
    x: "81",
    y: "18",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "create a character"), /*#__PURE__*/React.createElement("text", {
    className: "register_button_text",
    x: "81",
    y: "16",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "create a character"));
}