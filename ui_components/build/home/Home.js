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
  const [displayRules, setDisplayRules] = React.useState(false);
  const [displayTerms, setDisplayTerms] = React.useState(false);
  const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(LoginSection, {
    displayLogin: displayLogin,
    setDisplayLogin: setDisplayLogin,
    displayRegister: displayRegister,
    setDisplayRegister: setDisplayRegister,
    displayReset: displayReset,
    setDisplayReset: setDisplayReset,
    displayRules: displayRules,
    setDisplayRules: setDisplayRules,
    displayTerms: displayTerms,
    setDisplayTerms: setDisplayTerms,
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
  displayRules,
  setDisplayRules,
  displayTerms,
  setDisplayTerms,
  loginErrorText,
  registerErrorText,
  resetErrorText,
  loginMessageText,
  registerPreFill
}) {
  function handleLogin() {
    setDisplayRegister(false);
    setDisplayReset(false);
    setDisplayRules(false);
    setDisplayTerms(false);
    if (!displayLogin) {
      setDisplayLogin(true);
    } else {
      document.getElementById('login_form').submit();
    }
  }
  function handleRegister() {
    setDisplayReset(false);
    setDisplayLogin(false);
    setDisplayRules(false);
    setDisplayTerms(false);
    if (!displayRegister) {
      setDisplayRegister(true);
    } else {
      document.getElementById('register_form').submit();
    }
  }
  function handleDisplayReset() {
    setDisplayLogin(false);
    setDisplayRegister(false);
    setDisplayRules(false);
    setDisplayTerms(false);
    setDisplayReset(true);
  }
  function handleReset() {
    document.getElementById('reset_form').submit();
  }
  function handleRules() {
    setDisplayLogin(false);
    setDisplayRegister(false);
    setDisplayReset(false);
    setDisplayTerms(false);
    setDisplayRules(!displayRules);
  }
  function handleTerms() {
    setDisplayLogin(false);
    setDisplayRegister(false);
    setDisplayReset(false);
    setDisplayRules(false);
    setDisplayTerms(!displayTerms);
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
  }, "send email"))), displayRules && /*#__PURE__*/React.createElement("div", {
    className: "rules_modal",
    style: {
      zIndex: 5
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "rules_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "rules_title"
  }, "rules"), /*#__PURE__*/React.createElement("div", {
    className: "rules_close",
    onClick: () => setDisplayRules(false)
  }, "close")), /*#__PURE__*/React.createElement("div", {
    className: "rules_content"
  }, "These rules are meant to serve as a guideline for on-site behavior. Case-by-case interpretation and enforcement is at the discretion of the moderating staff. If you have any problems with a moderator's decision, do not call them out in the chat. Follow the chain of command; any problems with a moderator go to a head moderator FIRST before going to an admin.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("h3", null, "Offensive language"), /*#__PURE__*/React.createElement("div", null, "Using offensive language is against the rules. All users are encouraged to avoid using language that would offend others in public or private settings. Shinobi Chronicles promotes an environment for a mixed age group; thus, inappropriate language is prohibited. This includes, but not limited to:", /*#__PURE__*/React.createElement("ul", null, /*#__PURE__*/React.createElement("li", null, "Profanity that bypasses the explicit language filter (e.g. w0rd instead of word)"), /*#__PURE__*/React.createElement("li", null, "Racism"), /*#__PURE__*/React.createElement("li", null, "Religious discrimination"), /*#__PURE__*/React.createElement("li", null, "Explicit or excessive sexual references"), /*#__PURE__*/React.createElement("li", null, "Inappropriate references to illegal drugs and their use"), /*#__PURE__*/React.createElement("li", null, "Accounts with offensive usernames"))), /*#__PURE__*/React.createElement("h3", null, "Images"), /*#__PURE__*/React.createElement("div", null, "All user pictures are subject to moderation (i.e. avatars, signatures, or any other publicly displayed images). Inappropriate pictures would contain the following:", /*#__PURE__*/React.createElement("ul", null, /*#__PURE__*/React.createElement("li", null, "Sexual content"), /*#__PURE__*/React.createElement("li", null, "Profanity"), /*#__PURE__*/React.createElement("li", null, "Racism"), /*#__PURE__*/React.createElement("li", null, "Harassment ")), "The administration reserves the right to deem user-pictures inappropriate, even when not falling under any of the above categories. If the subjected user refuses to change the picture after the request of staff, the administration will be forced to change the picture and ban the user."), /*#__PURE__*/React.createElement("h3", null, "Social Etiquette/Spamming"), /*#__PURE__*/React.createElement("div", null, "To promote a social and peaceful environment, a few guidelines have been set to ensure a user friendly experience. Those guidelines are as follows:", /*#__PURE__*/React.createElement("ul", null, /*#__PURE__*/React.createElement("li", null, "Within publicly accessible locations, excessive use of any language besides English is not allowed. (Other languages can be used in Personal Messages or other private places.)"), /*#__PURE__*/React.createElement("li", null, "Sexually excessive, and/or racist posts are not allowed."), /*#__PURE__*/React.createElement("li", null, "Harassing other players and/or staff is not allowed"), /*#__PURE__*/React.createElement("li", null, "Excessive use of BBCode, ASCII art, or meme faces is not permissible."), /*#__PURE__*/React.createElement("li", null, "Nonsensical posts that do not contribute to the conversation in any way are not allowed."), /*#__PURE__*/React.createElement("li", null, "Harassment, trolling, or otherwise pestering a user is not allowed."), /*#__PURE__*/React.createElement("li", null, "Unnecessarily breaking up chat messages into multiple short posts (e.g. \"hello\" \"my\" \"name\" \"is\" \"bob\") is not allowed."))), /*#__PURE__*/React.createElement("h3", null, "Account Responsibility:"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("ul", null, /*#__PURE__*/React.createElement("li", null, "Account limits: 2 accounts"), /*#__PURE__*/React.createElement("li", null, "Attacking your own account is not allowed."), /*#__PURE__*/React.createElement("li", null, "Account sharing is not allowed."), /*#__PURE__*/React.createElement("li", null, "Impersonating staff is forbidden"))), /*#__PURE__*/React.createElement("h3", null, "Glitching/Hacking:"), /*#__PURE__*/React.createElement("div", null, "Exploiting bugs/glitches, attempting to hack/crack the site or its data, or changing site code is strictly prohibited. Any attempts will be met with severe punishment.", /*#__PURE__*/React.createElement("br", null), "There is ", /*#__PURE__*/React.createElement("i", null, "Zero Tolerance"), " for planning attacks against other games anywhere on Shinobi-Chronicles. Any discussion of these topics is strictly forbidden and will be met with punishment as severe as the situation dictates."), /*#__PURE__*/React.createElement("h3", null, "Manga Spoilers"), /*#__PURE__*/React.createElement("div", null, "As this is an anime/manga-themed game, it can be expected that most of the userbase follows various ongoing manga/anime series. Since many people for various reasons do not read the manga, but only watch the anime, posting spoilers of things that have not happened in the anime yet of a major ongoing series (Naruto, One Piece, My Hero Academia, Demon Slayer, etc) is not allowed as it can significantly lessen the experience of watching the show.", /*#__PURE__*/React.createElement("br", null)), /*#__PURE__*/React.createElement("h3", null, "Bots/macros/etc:"), /*#__PURE__*/React.createElement("div", null, "Bots, macros, or any other devices (hardware or software) that play the game for you, are prohibited. Any characters caught botting will receive a ban along with a stat cut."), /*#__PURE__*/React.createElement("h3", null, "Links:"), /*#__PURE__*/React.createElement("div", null, "Linking to sites that violate any of these rules (e.g: sites with explicit content) is prohibited.", /*#__PURE__*/React.createElement("br", null), "Linking to sites that contain language unsuitable for SC is allowed provided a clear warning is provided in the post. Linking to sites that break any of the other rules or linking to sites that contain inappropriate language without providing a warning is strictly not allowed."))), displayTerms && /*#__PURE__*/React.createElement("div", {
    className: "terms_modal",
    style: {
      zIndex: 5
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "terms_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "terms_title"
  }, "terms of service"), /*#__PURE__*/React.createElement("div", {
    className: "terms_close",
    onClick: () => setDisplayTerms(false)
  }, "close")), /*#__PURE__*/React.createElement("div", {
    className: "terms_content"
  }, "Shinobi-chronicles.com is a fan site: We did not create Naruto nor any of the characters and content in Naruto. While inspired by Naruto, the content of this site is fan-made and not meant to infringe upon any copyrights, it is simply here to further the continuing popularity of Japanese animation. In no event will shinobi-chronicles.com, its host, and any other companies and/or sites linked to shinobi-chronicles.com be liable to any party for any direct, indirect, special or other consequential damages for any use of this website, or on any other hyperlinked website, including, without limitation, any lost profits, loss of programs or other data on your information handling system or otherwise, even if we are expressly advised of the possibility of such damages.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("p", null, "Shinobi-chronicles.com accepts no responsibility for the actions of its members i.e. Self harm, vandalism, suicide, homicide, genocide, drug abuse, changes in sexual orientation, or bestiality. Shinobi-chronicles.com will not be held responsible and does not encourage any of the above actions or any other form of anti social behaviour. The staff of shinobi-chronicles.com reserve the right to issue bans and/or account deletion for rule infractions. Rule infractions will be determined at the discretion of the moderating staff."), /*#__PURE__*/React.createElement("p", null, "Loans or transactions of real or in-game currency are between players. Staff take no responsibility for the completion of them. If a player loans real or in-game currency to another player, staff will not be responsible for ensuring the currency is returned."), /*#__PURE__*/React.createElement("p", null, "Ancient Kunai(Premium credits) that have already been spent on in-game purchases of any kind or traded to another player cannot be refunded. Staff are not responsible for lost shards or time on Forbidden Seals lost due to user bans."), /*#__PURE__*/React.createElement("br", null), "The Naruto series is created by and copyright Masashi Kishimoto and TV Tokyo, all rights reserved.")), /*#__PURE__*/React.createElement("svg", {
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
    width: "45",
    height: "45"
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
    className: "login_rules_button"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_diamond_container"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "home_diamond_svg",
    width: "100",
    height: "100",
    style: {
      transform: "scale(0.85)"
    },
    onClick: () => handleRules()
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
    width: "45",
    height: "45"
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
  }, "rules"), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_blue_text",
    x: "50",
    y: "50",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "rules")))), /*#__PURE__*/React.createElement("div", {
    className: "login_terms_button"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_diamond_container"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "home_diamond_svg",
    width: "100",
    height: "100",
    style: {
      transform: "scale(0.85)"
    },
    onClick: () => handleTerms()
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
    width: "45",
    height: "45"
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
  }, "terms of"), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_red_text",
    x: "50",
    y: "38",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "terms of"), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_shadow_text",
    x: "50",
    y: "64",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "service"), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_red_text",
    x: "50",
    y: "62",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "service")))), /*#__PURE__*/React.createElement("div", {
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