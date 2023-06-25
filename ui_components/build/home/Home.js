import { apiFetch } from "../utils/network.js";
function Home({
  homeLinks,
  isLoggedIn,
  isAdmin,
  version,
  initialLoginDisplay,
  loginErrorText,
  registerErrorText,
  resetErrorText,
  loginMessageText,
  registerPreFill,
  initialNewsPosts
}) {
  const [loginDisplay, setLoginDisplay] = React.useState(initialLoginDisplay);
  const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);
  const newsRef = React.useRef(null);
  const contactRef = React.useRef(null);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(LoginSection, {
    homeLinks: homeLinks,
    isLoggedIn: isLoggedIn,
    version: version,
    loginDisplay: loginDisplay,
    setLoginDisplay: setLoginDisplay,
    loginErrorText: loginErrorText,
    registerErrorText: registerErrorText,
    resetErrorText: resetErrorText,
    loginMessageText: loginMessageText,
    registerPreFill: registerPreFill,
    newsRef: newsRef,
    contactRef: contactRef
  }), /*#__PURE__*/React.createElement(NewsSection, {
    newsRef: newsRef,
    initialNewsPosts: initialNewsPosts,
    homeLinks: homeLinks,
    isAdmin: isAdmin
  }), /*#__PURE__*/React.createElement(FeatureSection, null), /*#__PURE__*/React.createElement(WorldSection, null), /*#__PURE__*/React.createElement(ContactSection, {
    contactRef: contactRef
  }), /*#__PURE__*/React.createElement(FooterSection, null));
}
function LoginSection({
  homeLinks,
  isLoggedIn,
  version,
  loginDisplay,
  setLoginDisplay,
  loginErrorText,
  registerErrorText,
  resetErrorText,
  loginMessageText,
  registerPreFill,
  newsRef,
  contactRef
}) {
  function handleLogin() {
    if (loginDisplay != "login") {
      setLoginDisplay("login");
    } else {
      document.getElementById('login_form').submit();
    }
  }
  function handleRegister() {
    if (loginDisplay != "register") {
      setLoginDisplay("register");
    } else {
      document.getElementById('register_form').submit();
    }
  }
  function handleReset() {
    document.getElementById('reset_form').submit();
  }
  function scrollTo(element) {
    element.scrollIntoView({
      behavior: 'smooth'
    });
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
  }, version), /*#__PURE__*/React.createElement("div", {
    className: "login_inner_input_container"
  }, loginDisplay == "login" && /*#__PURE__*/React.createElement("form", {
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
    onClick: () => setLoginDisplay("reset")
  }, "reset password"))), loginDisplay == "register" && /*#__PURE__*/React.createElement("form", {
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
    onClick: () => setLoginDisplay("none")
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
  }, registerErrorText))), loginDisplay == "reset" && /*#__PURE__*/React.createElement("form", {
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
  }, "send email"))), loginDisplay == "rules" && /*#__PURE__*/React.createElement("div", {
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
    onClick: () => setLoginDisplay("none")
  }, "close")), /*#__PURE__*/React.createElement("div", {
    className: "rules_content"
  }, "These rules are meant to serve as a guideline for on-site behavior. Case-by-case interpretation and enforcement is at the discretion of the moderating staff. If you have any problems with a moderator's decision, do not call them out in the chat. Follow the chain of command; any problems with a moderator go to a head moderator FIRST before going to an admin.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("h3", null, "Offensive language"), /*#__PURE__*/React.createElement("div", null, "Using offensive language is against the rules. All users are encouraged to avoid using language that would offend others in public or private settings. Shinobi Chronicles promotes an environment for a mixed age group; thus, inappropriate language is prohibited. This includes, but not limited to:", /*#__PURE__*/React.createElement("ul", null, /*#__PURE__*/React.createElement("li", null, "Profanity that bypasses the explicit language filter (e.g. w0rd instead of word)"), /*#__PURE__*/React.createElement("li", null, "Racism"), /*#__PURE__*/React.createElement("li", null, "Religious discrimination"), /*#__PURE__*/React.createElement("li", null, "Explicit or excessive sexual references"), /*#__PURE__*/React.createElement("li", null, "Inappropriate references to illegal drugs and their use"), /*#__PURE__*/React.createElement("li", null, "Accounts with offensive usernames"))), /*#__PURE__*/React.createElement("h3", null, "Images"), /*#__PURE__*/React.createElement("div", null, "All user pictures are subject to moderation (i.e. avatars, signatures, or any other publicly displayed images). Inappropriate pictures would contain the following:", /*#__PURE__*/React.createElement("ul", null, /*#__PURE__*/React.createElement("li", null, "Sexual content"), /*#__PURE__*/React.createElement("li", null, "Profanity"), /*#__PURE__*/React.createElement("li", null, "Racism"), /*#__PURE__*/React.createElement("li", null, "Harassment ")), "The administration reserves the right to deem user-pictures inappropriate, even when not falling under any of the above categories. If the subjected user refuses to change the picture after the request of staff, the administration will be forced to change the picture and ban the user."), /*#__PURE__*/React.createElement("h3", null, "Social Etiquette/Spamming"), /*#__PURE__*/React.createElement("div", null, "To promote a social and peaceful environment, a few guidelines have been set to ensure a user friendly experience. Those guidelines are as follows:", /*#__PURE__*/React.createElement("ul", null, /*#__PURE__*/React.createElement("li", null, "Within publicly accessible locations, excessive use of any language besides English is not allowed. (Other languages can be used in Personal Messages or other private places.)"), /*#__PURE__*/React.createElement("li", null, "Sexually excessive, and/or racist posts are not allowed."), /*#__PURE__*/React.createElement("li", null, "Harassing other players and/or staff is not allowed"), /*#__PURE__*/React.createElement("li", null, "Excessive use of BBCode, ASCII art, or meme faces is not permissible."), /*#__PURE__*/React.createElement("li", null, "Nonsensical posts that do not contribute to the conversation in any way are not allowed."), /*#__PURE__*/React.createElement("li", null, "Harassment, trolling, or otherwise pestering a user is not allowed."), /*#__PURE__*/React.createElement("li", null, "Unnecessarily breaking up chat messages into multiple short posts (e.g. \"hello\" \"my\" \"name\" \"is\" \"bob\") is not allowed."))), /*#__PURE__*/React.createElement("h3", null, "Account Responsibility:"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("ul", null, /*#__PURE__*/React.createElement("li", null, "Account limits: 2 accounts"), /*#__PURE__*/React.createElement("li", null, "Attacking your own account is not allowed."), /*#__PURE__*/React.createElement("li", null, "Account sharing is not allowed."), /*#__PURE__*/React.createElement("li", null, "Impersonating staff is forbidden"))), /*#__PURE__*/React.createElement("h3", null, "Glitching/Hacking:"), /*#__PURE__*/React.createElement("div", null, "Exploiting bugs/glitches, attempting to hack/crack the site or its data, or changing site code is strictly prohibited. Any attempts will be met with severe punishment.", /*#__PURE__*/React.createElement("br", null), "There is ", /*#__PURE__*/React.createElement("i", null, "Zero Tolerance"), " for planning attacks against other games anywhere on Shinobi-Chronicles. Any discussion of these topics is strictly forbidden and will be met with punishment as severe as the situation dictates."), /*#__PURE__*/React.createElement("h3", null, "Manga Spoilers"), /*#__PURE__*/React.createElement("div", null, "As this is an anime/manga-themed game, it can be expected that most of the userbase follows various ongoing manga/anime series. Since many people for various reasons do not read the manga, but only watch the anime, posting spoilers of things that have not happened in the anime yet of a major ongoing series (Naruto, One Piece, My Hero Academia, Demon Slayer, etc) is not allowed as it can significantly lessen the experience of watching the show.", /*#__PURE__*/React.createElement("br", null)), /*#__PURE__*/React.createElement("h3", null, "Bots/macros/etc:"), /*#__PURE__*/React.createElement("div", null, "Bots, macros, or any other devices (hardware or software) that play the game for you, are prohibited. Any characters caught botting will receive a ban along with a stat cut."), /*#__PURE__*/React.createElement("h3", null, "Links:"), /*#__PURE__*/React.createElement("div", null, "Linking to sites that violate any of these rules (e.g: sites with explicit content) is prohibited.", /*#__PURE__*/React.createElement("br", null), "Linking to sites that contain language unsuitable for SC is allowed provided a clear warning is provided in the post. Linking to sites that break any of the other rules or linking to sites that contain inappropriate language without providing a warning is strictly not allowed."))), loginDisplay == "terms" && /*#__PURE__*/React.createElement("div", {
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
    onClick: () => setLoginDisplay("none")
  }, "close")), /*#__PURE__*/React.createElement("div", {
    className: "terms_content"
  }, "Shinobi-chronicles.com is a fan site: We did not create Naruto nor any of the characters and content in Naruto. While inspired by Naruto, the content of this site is fan-made and not meant to infringe upon any copyrights, it is simply here to further the continuing popularity of Japanese animation. In no event will shinobi-chronicles.com, its host, and any other companies and/or sites linked to shinobi-chronicles.com be liable to any party for any direct, indirect, special or other consequential damages for any use of this website, or on any other hyperlinked website, including, without limitation, any lost profits, loss of programs or other data on your information handling system or otherwise, even if we are expressly advised of the possibility of such damages.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("p", null, "Shinobi-chronicles.com accepts no responsibility for the actions of its members i.e. Self harm, vandalism, suicide, homicide, genocide, drug abuse, changes in sexual orientation, or bestiality. Shinobi-chronicles.com will not be held responsible and does not encourage any of the above actions or any other form of anti social behaviour. The staff of shinobi-chronicles.com reserve the right to issue bans and/or account deletion for rule infractions. Rule infractions will be determined at the discretion of the moderating staff."), /*#__PURE__*/React.createElement("p", null, "Loans or transactions of real or in-game currency are between players. Staff take no responsibility for the completion of them. If a player loans real or in-game currency to another player, staff will not be responsible for ensuring the currency is returned."), /*#__PURE__*/React.createElement("p", null, "Ancient Kunai(Premium credits) that have already been spent on in-game purchases of any kind or traded to another player cannot be refunded. Staff are not responsible for lost shards or time on Forbidden Seals lost due to user bans."), /*#__PURE__*/React.createElement("br", null), "The Naruto series is created by and copyright Masashi Kishimoto and TV Tokyo, all rights reserved.")), !isLoggedIn && /*#__PURE__*/React.createElement(LoginButtons, {
    handleLogin: handleLogin,
    handleRegister: handleRegister
  }), isLoggedIn && /*#__PURE__*/React.createElement(LogoutButtons, {
    homeLinks: homeLinks
  })))), /*#__PURE__*/React.createElement("div", {
    className: "login_news_button"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_diamond_container"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "home_diamond_svg",
    width: "100",
    height: "100",
    role: "button",
    tabIndex: "0",
    onClick: () => scrollTo(newsRef.current)
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
    role: "button",
    tabIndex: "0",
    style: {
      transform: "scale(0.85)"
    },
    onClick: () => setLoginDisplay("rules")
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
    role: "button",
    tabIndex: "0",
    style: {
      transform: "scale(0.85)"
    },
    onClick: () => setLoginDisplay("terms")
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
    height: "100",
    role: "button",
    tabIndex: "0",
    onClick: () => scrollTo(contactRef.current)
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
function NewsSection({
  newsRef,
  initialNewsPosts,
  homeLinks,
  isAdmin
}) {
  const [activePostId, setActivePostId] = React.useState(initialNewsPosts[0] != "undefined" ? initialNewsPosts[0].post_id : null);
  const [editPostId, setEditPostId] = React.useState(null);
  const [numPosts, setNumPosts] = React.useState(initialNewsPosts.length);
  const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);
  const titleRef = React.useRef(null);
  const versionRef = React.useRef(null);
  const contentRef = React.useRef(null);
  const updateTagRef = React.useRef(null);
  const bugfixTagRef = React.useRef(null);
  const eventTagRef = React.useRef(null);
  function formatNewsDate(ticks) {
    var date = new Date(ticks * 1000);
    var formattedDate = date.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: '2-digit'
    });
    return formattedDate;
  }
  function cleanNewsContents(contents) {
    console.log(contents);
    const parser = new DOMParser();
    const decodedString = parser.parseFromString(contents.replace(/[\r\n]+/g, " ").replace(/<br\s*\/?>/g, '\n'), 'text/html').body.textContent;
    return decodedString;
  }
  function saveNewsItem(postId) {
    console.log(contentRef.current.value);
    apiFetch(homeLinks.news_api, {
      request: 'saveNewsPost',
      post_id: postId,
      title: titleRef.current.textContent,
      version: versionRef.current.textContent,
      content: contentRef.current.value,
      update: updateTagRef.current.checked,
      bugfix: bugfixTagRef.current.checked,
      event: eventTagRef.current.checked,
      num_posts: numPosts
    }).then(response => {
      if (response.errors.length) {
        console.warn(response.errors);
      } else {
        setNewsPosts(response.data.postData);
      }
    });
    setEditPostId(null);
  }
  function NewsItem({
    newsItem
  }) {
    return /*#__PURE__*/React.createElement("div", {
      className: "news_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: activePostId == newsItem.post_id ? "news_item_header" : "news_item_header news_item_header_minimized",
      onClick: () => setActivePostId(newsItem.post_id)
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_title"
    }, newsItem.title.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_version"
    }, newsItem.version && newsItem.version.toUpperCase()), newsItem.tags.map((tag, index) => /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, tag.toUpperCase()))), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details_container"
    }, isAdmin && /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => setEditPostId(newsItem.post_id)
    }, "EDIT"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details"
    }, "POSTED ", formatNewsDate(newsItem.time), " BY ", newsItem.sender.toUpperCase()))), activePostId == newsItem.post_id && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_banner"
    }), /*#__PURE__*/React.createElement("div", {
      className: "news_item_content",
      dangerouslySetInnerHTML: {
        __html: newsItem.message
      }
    })));
  }
  function NewsItemEdit({
    newsItem
  }) {
    return /*#__PURE__*/React.createElement("div", {
      className: "news_item_editor"
    }, /*#__PURE__*/React.createElement("div", {
      className: activePostId == newsItem.post_id ? "news_item_header" : "news_item_header news_item_header_minimized",
      onClick: () => setActivePostId(newsItem.post_id)
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_title",
      ref: titleRef,
      contentEditable: "true",
      suppressContentEditableWarning: true
    }, newsItem.title.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_version",
      ref: versionRef,
      contentEditable: "true",
      suppressContentEditableWarning: true
    }, newsItem.version && newsItem.version.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "UPDATE"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_update",
      type: "checkbox",
      ref: updateTagRef,
      defaultChecked: newsItem.tags.includes("update")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "BUG FIXES"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_bugfixes",
      type: "checkbox",
      ref: bugfixTagRef,
      defaultChecked: newsItem.tags.includes("bugfix")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "EVENT"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_event",
      type: "checkbox",
      ref: eventTagRef,
      defaultChecked: newsItem.tags.includes("event")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details_container"
    }, isAdmin && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => setEditPostId(null)
    }, "CANCEL"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => saveNewsItem(newsItem.post_id)
    }, "SAVE")), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details"
    }, "POSTED ", formatNewsDate(newsItem.time), " BY ", newsItem.sender.toUpperCase()))), activePostId == newsItem.post_id && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_banner"
    }), /*#__PURE__*/React.createElement("textarea", {
      className: "news_item_content_editor",
      ref: contentRef,
      defaultValue: cleanNewsContents(newsItem.message)
    })));
  }
  return /*#__PURE__*/React.createElement("div", {
    ref: newsRef,
    id: "news_container",
    className: "home_section news_section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_header"
  }, /*#__PURE__*/React.createElement("label", {
    className: "home_header_label"
  }, "NEWS & UPDATES"), /*#__PURE__*/React.createElement("div", {
    className: "home_external_links"
  }, /*#__PURE__*/React.createElement("a", {
    href: homeLinks['github'],
    className: "home_github_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "home_github",
    src: "../../../images/v2/icons/githubhover.png"
  })), /*#__PURE__*/React.createElement("a", {
    href: homeLinks['discord'],
    className: "home_discord_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "home_discord",
    src: "../../../images/v2/icons/discordhover.png"
  })))), /*#__PURE__*/React.createElement("div", {
    className: "news_item_container"
  }, newsPosts && newsPosts.map(newsItem => newsItem.post_id == editPostId ? /*#__PURE__*/React.createElement(NewsItemEdit, {
    key: newsItem.post_id,
    newsItem: newsItem
  }) : /*#__PURE__*/React.createElement(NewsItem, {
    key: newsItem.post_id,
    newsItem: newsItem
  }))));
}
function FeatureSection({}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null);
}
function WorldSection({}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null);
}
function ContactSection({
  contactRef
}) {
  return /*#__PURE__*/React.createElement("div", {
    ref: contactRef,
    id: "contact_container",
    className: "home_section contact_section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "home_header"
  }, /*#__PURE__*/React.createElement("label", {
    className: "home_header_label"
  }, "CONTACT US")), /*#__PURE__*/React.createElement("div", {
    className: "home_form_container"
  }));
}
function FooterSection({}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "home_section footer_section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "footer_text"
  }, "SHINOBI CHRONICLES V0.9.0 COPYRIGHT \xA9 LM VISIONS"));
}
function LoginButtons({
  handleLogin,
  handleRegister
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("svg", {
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
  }, /*#__PURE__*/React.createElement("radialGradient", {
    id: "login_fill_default",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#464f87',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#343d77',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("radialGradient", {
    id: "login_fill_click",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#343d77',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#464f87',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("rect", {
    className: "login_button_background",
    width: "100%",
    height: "100%",
    fill: "url(#login_fill_default)"
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
  }, "create a character")));
}
function LogoutButtons({
  homeLinks
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
    href: homeLinks['profile'],
    style: {
      display: "flex"
    }
  }, /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    className: "profile_button",
    width: "162",
    height: "32"
  }, /*#__PURE__*/React.createElement("radialGradient", {
    id: "profile_fill_default",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#464f87',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#343d77',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("radialGradient", {
    id: "profile_fill_click",
    cx: "50%",
    cy: "50%",
    r: "50%",
    fx: "50%",
    fy: "50%"
  }, /*#__PURE__*/React.createElement("stop", {
    offset: "0%",
    style: {
      stopColor: '#343d77',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#464f87',
      stopOpacity: 1
    }
  })), /*#__PURE__*/React.createElement("rect", {
    className: "profile_button_background",
    width: "100%",
    height: "100%"
  }), /*#__PURE__*/React.createElement("text", {
    className: "profile_button_shadow_text",
    x: "81",
    y: "18",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "profile"), /*#__PURE__*/React.createElement("text", {
    className: "profile_button_text",
    x: "81",
    y: "16",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "profile"))), /*#__PURE__*/React.createElement("a", {
    href: homeLinks['logout'],
    style: {
      display: "flex"
    }
  }, /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    className: "logout_button",
    width: "162",
    height: "32"
  }, /*#__PURE__*/React.createElement("radialGradient", {
    id: "logout_fill_default",
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
    id: "logout_fill_click",
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
    className: "logout_button_background",
    width: "100%",
    height: "100%"
  }), /*#__PURE__*/React.createElement("text", {
    className: "logout_button_shadow_text",
    x: "81",
    y: "18",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "logout"), /*#__PURE__*/React.createElement("text", {
    className: "logout_button_text",
    x: "81",
    y: "16",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, "logout"))));
}
window.Home = Home;