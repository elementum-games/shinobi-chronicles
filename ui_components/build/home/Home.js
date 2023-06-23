import { apiFetch } from "../utils/network.js";
function Home({
  newsApiLink,
  loginErrorText,
  registerErrorText,
  resetErrorText,
  loginMessageText,
  registerPreFill,
  initialNewsPosts
}) {
  const [displayLogin, setDisplayLogin] = React.useState(loginErrorText != "" || loginMessageText != "" ? true : false);
  const [displayRegister, setDisplayRegister] = React.useState(registerErrorText == "" ? false : true);
  const [displayReset, setDisplayReset] = React.useState(resetErrorText == "" ? false : true);
  const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(LoginSection, {
    displayLogin: displayLogin,
    setDisplayLogin: setDisplayLogin,
    displayRegister: displayRegister,
    setDisplayRegister: setDisplayRegister,
    displayReset: displayReset,
    setDisplayReset: setDisplayReset,
    loginErrorText: loginErrorText,
    registerErrorText: registerErrorText,
    resetErrorText: resetErrorText,
    loginMessageText: loginMessageText,
    registerPreFill: registerPreFill
  }), /*#__PURE__*/React.createElement(NewsSection, {
    newsPosts: newsPosts
  }), /*#__PURE__*/React.createElement(FeatureSection, null), /*#__PURE__*/React.createElement(WorldSection, null), /*#__PURE__*/React.createElement(ContactSection, null));
}
function LoginSection({
  displayLogin,
  setDisplayLogin,
  displayRegister,
  setDisplayRegister,
  displayReset,
  setDisplayReset,
  loginErrorText,
  registerErrorText,
  resetErrorText,
  loginMessageText,
  registerPreFill
}) {
  function handleLogin() {
    setDisplayRegister(false);
    setDisplayReset(false);
    if (!displayLogin) {
      setDisplayLogin(true);
    } else {
      document.getElementById('login_form').submit();
    }
  }
  function handleRegister() {
    setDisplayReset(false);
    setDisplayLogin(false);
    if (!displayRegister) {
      setDisplayRegister(true);
    } else {
      document.getElementById('register_form').submit();
    }
  }
  function handleDisplayReset() {
    setDisplayLogin(false);
    setDisplayRegister(false);
    setDisplayReset(true);
  }
  function handleReset() {
    document.getElementById('reset_form').submit();
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "home_section login_section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_center_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_center_inner"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_inner_title"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/decorations/homepagelogo.png"
  })), /*#__PURE__*/React.createElement("div", {
    className: "login_inner_version"
  }, "0.9 MAKE THINGS LOOK GOOD"), /*#__PURE__*/React.createElement("div", {
    className: "login_inner_input_container"
  }, displayLogin && /*#__PURE__*/React.createElement("form", {
    id: "login_form",
    action: "",
    method: "post",
    style: {
      zIndex: 1
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_input_top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_username_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "login_username_label"
  }, "username"), /*#__PURE__*/React.createElement("input", {
    type: "text",
    name: "user_name",
    className: "login_username_input login_text_input"
  })), /*#__PURE__*/React.createElement("div", {
    className: "login_password_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "login_username_label"
  }, "password"), /*#__PURE__*/React.createElement("input", {
    type: "password",
    name: "password",
    className: "login_password_input login_text_input"
  })), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: "login",
    value: "login"
  })), loginMessageText != "" && /*#__PURE__*/React.createElement("div", {
    className: "login_input_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_message_label"
  }, loginMessageText)), loginErrorText != "" && /*#__PURE__*/React.createElement("div", {
    className: "login_input_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_error_label"
  }, loginErrorText), /*#__PURE__*/React.createElement("div", {
    className: "reset_link",
    onClick: () => handleDisplayReset()
  }, "reset password"))), displayRegister && /*#__PURE__*/React.createElement("form", {
    id: "register_form",
    action: "",
    method: "post",
    style: {
      zIndex: 3
    }
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
  })), /*#__PURE__*/React.createElement("div", {
    className: "register_close",
    onClick: () => setDisplayRegister(false)
  }, "close")), /*#__PURE__*/React.createElement("div", {
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
  }, "(Note: Currently we cannot send emails to addresses from:"), /*#__PURE__*/React.createElement("div", {
    className: "register_email_notice"
  }, "hotmail.com, live.com, msn.com, outlook.com)")), /*#__PURE__*/React.createElement("div", {
    className: "register_character_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_gender_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_field_label"
  }, "gender"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_gender_male",
    name: "gender",
    value: "Male",
    defaultChecked: registerPreFill.gender == "Male"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_gender_male"
  }, "Male")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_gender_female",
    name: "gender",
    value: "Female",
    defaultChecked: registerPreFill.gender == "Female"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_gender_female"
  }, "Female")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_gender_nonbinary",
    name: "gender",
    value: "Non-binary",
    defaultChecked: registerPreFill.gender == "Non-binary"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_gender_nonbinary"
  }, "Non-binary")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_gender_none",
    name: "gender",
    value: "None",
    defaultChecked: registerPreFill.gender == "None"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_gender_none"
  }, "None"))), /*#__PURE__*/React.createElement("div", {
    className: "register_village_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "register_field_label"
  }, "village"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_stone",
    name: "village",
    value: "Stone",
    defaultChecked: registerPreFill.village == "Stone"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_stone"
  }, "Stone")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_cloud",
    name: "village",
    value: "Cloud",
    defaultChecked: registerPreFill.village == "Cloud"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_cloud"
  }, "Cloud")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_leaf",
    name: "village",
    value: "Leaf",
    defaultChecked: registerPreFill.village == "Leaf"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_leaf"
  }, "Leaf")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_sand",
    name: "village",
    value: "Sand",
    defaultChecked: registerPreFill.village == "Sand"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_sand"
  }, "Sand")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_mist",
    name: "village",
    value: "Mist",
    defaultChecked: registerPreFill.village == "Mist"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_mist"
  }, "Mist")))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    className: "register_terms_notice"
  }, "By clicking 'Create a Character' I affirm that I have read and agree to abide by the Rules and Terms of Service. I understand that if I fail to abide by the rules as determined by the moderating staff, I may be temporarily or permanently banned and that I will not be compensated for time lost. I also understand that any actions taken by anyone on my account are my responsibility."))), registerErrorText != "" && /*#__PURE__*/React.createElement("div", {
    className: "register_input_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_error_label",
    style: {
      marginBottom: "30px",
      marginLeft: "30px",
      marginTop: "-15px"
    }
  }, registerErrorText))), displayReset && /*#__PURE__*/React.createElement("form", {
    id: "reset_form",
    action: "",
    method: "post",
    style: {
      zIndex: 1
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "reset_input_top"
  }, /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: "reset",
    value: "reset"
  }), /*#__PURE__*/React.createElement("div", {
    className: "login_username_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "login_username_label"
  }, "username"), /*#__PURE__*/React.createElement("input", {
    type: "text",
    name: "username",
    className: "login_username_input login_text_input"
  })), /*#__PURE__*/React.createElement("div", {
    className: "reset_email_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "reset_email_label"
  }, "email address"), /*#__PURE__*/React.createElement("input", {
    type: "email",
    name: "email",
    className: "reset_email_input login_text_input"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "reset_input_bottom"
  }, resetErrorText != "" && /*#__PURE__*/React.createElement("div", {
    className: "login_error_label"
  }, resetErrorText), /*#__PURE__*/React.createElement("div", {
    className: "reset_link",
    onClick: () => handleReset()
  }, "send email"))), /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    name: "login",
    className: "login_button",
    width: "162",
    height: "32",
    onClick: () => handleLogin(),
    style: {
      zIndex: 2
    }
  }, /*#__PURE__*/React.createElement("rect", {
    className: "login_button_background",
    width: "100%",
    height: "100%"
  }), /*#__PURE__*/React.createElement("text", {
    className: "login_button_shadow_text",
    x: "81",
    y: "18",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "login"), /*#__PURE__*/React.createElement("text", {
    className: "login_button_text",
    x: "81",
    y: "16",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "login")), /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    name: "register",
    className: "register_button",
    width: "162",
    height: "32",
    onClick: () => handleRegister(),
    style: {
      zIndex: 4
    }
  }, /*#__PURE__*/React.createElement("rect", {
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
  }, "create a character"))))), /*#__PURE__*/React.createElement("div", {
    className: "login_news_button"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_diamond_container"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "home_diamond_svg",
    width: "100",
    height: "100"
  }, /*#__PURE__*/React.createElement("g", {
    className: "home_diamond_rotategroup diamond_red",
    transform: "rotate(45 50 50)"
  }, /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_rear",
    x: "29",
    y: "29",
    width: "78",
    height: "78"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_up",
    x: "4",
    y: "4",
    width: "45px",
    height: "45px"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_right",
    x: "51",
    y: "4",
    width: "45",
    height: "45"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_left",
    x: "4",
    y: "51",
    width: "45",
    height: "45"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_down",
    x: "51",
    y: "51",
    width: "45",
    height: "45"
  })), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_shadow_text",
    x: "50",
    y: "40",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "news &"), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_red_text",
    x: "50",
    y: "38",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "news &"), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_shadow_text",
    x: "50",
    y: "64",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "updates"), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_red_text",
    x: "50",
    y: "62",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "updates")))), /*#__PURE__*/React.createElement("div", {
    className: "login_features_button"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_diamond_container"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100",
    height: "100"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "login_world_button"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_diamond_container"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100",
    height: "100"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "login_contact_button"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_diamond_container"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "home_diamond_svg",
    width: "100",
    height: "100"
  }, /*#__PURE__*/React.createElement("g", {
    className: "home_diamond_rotategroup diamond_blue",
    transform: "rotate(45 50 50)"
  }, /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_rear",
    x: "29",
    y: "29",
    width: "78",
    height: "78"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_up",
    x: "4",
    y: "4",
    width: "45px",
    height: "45px"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_right",
    x: "51",
    y: "4",
    width: "45",
    height: "45"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_left",
    x: "4",
    y: "51",
    width: "45",
    height: "45"
  }), /*#__PURE__*/React.createElement("rect", {
    className: "home_diamond_down",
    x: "51",
    y: "51",
    width: "45",
    height: "45"
  })), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_shadow_text",
    x: "50",
    y: "52",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "contact us"), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_blue_text",
    x: "50",
    y: "50",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "contact us")))));
}
function NewsSection({}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null);
}
function FeatureSection({}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null);
}
function WorldSection({}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null);
}
function ContactSection({}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null);
}
window.Home = Home;