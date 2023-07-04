export function RegisterForm({
  registerErrorText,
  registerPreFill,
  formRef
}) {
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
  }, "gender"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_gender_male",
    name: "gender",
    value: "Male",
    defaultChecked: registerPreFill.gender === "Male"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_gender_male"
  }, "Male")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_gender_female",
    name: "gender",
    value: "Female",
    defaultChecked: registerPreFill.gender === "Female"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_gender_female"
  }, "Female")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_gender_nonbinary",
    name: "gender",
    value: "Non-binary",
    defaultChecked: registerPreFill.gender === "Non-binary"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_gender_nonbinary"
  }, "Non-binary")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_gender_none",
    name: "gender",
    value: "None",
    defaultChecked: registerPreFill.gender === "None"
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
    defaultChecked: registerPreFill.village === "Stone"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_stone"
  }, "Stone")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_cloud",
    name: "village",
    value: "Cloud",
    defaultChecked: registerPreFill.village === "Cloud"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_cloud"
  }, "Cloud")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_leaf",
    name: "village",
    value: "Leaf",
    defaultChecked: registerPreFill.village === "Leaf"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_leaf"
  }, "Leaf")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_sand",
    name: "village",
    value: "Sand",
    defaultChecked: registerPreFill.village === "Sand"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_sand"
  }, "Sand")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("input", {
    className: "register_option",
    type: "radio",
    id: "register_village_mist",
    name: "village",
    value: "Mist",
    defaultChecked: registerPreFill.village === "Mist"
  }), /*#__PURE__*/React.createElement("label", {
    className: "register_option_label",
    htmlFor: "register_village_mist"
  }, "Mist")))), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
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