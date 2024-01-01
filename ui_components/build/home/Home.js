import { RegisterForm, CreateCharacterButton } from "./RegisterForm.js";
import { Rules, Terms } from "./staticPageContents.js";
import { clickOnEnter } from "../utils/uiHelpers.js";
import { News } from "./News.js";
function Home({
  homeLinks,
  isLoggedIn,
  isAdmin,
  version,
  versionNumber,
  initialView,
  loginErrorText,
  registerErrorText,
  resetErrorText,
  loginMessageText,
  registerPreFill,
  initialNewsPosts,
  scOpen,
  reopenTimeWindow
}) {
  const newsRef = React.useRef(null);
  const contactRef = React.useRef(null);
  const activeElement = React.useRef(null);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(MainBannerSection, {
    homeLinks: homeLinks,
    isLoggedIn: isLoggedIn,
    version: version,
    initialView: initialView,
    loginErrorText: loginErrorText,
    registerErrorText: registerErrorText,
    resetErrorText: resetErrorText,
    loginMessageText: loginMessageText,
    registerPreFill: registerPreFill,
    newsRef: newsRef,
    contactRef: contactRef,
    AshBackground: AshBackground,
    scOpen: scOpen,
    reopenTimeWindow: reopenTimeWindow
  }), /*#__PURE__*/React.createElement("div", {
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
  })))), /*#__PURE__*/React.createElement(News, {
    initialNewsPosts: initialNewsPosts,
    isAdmin: isAdmin,
    version: version,
    homeLinks: homeLinks
  })), /*#__PURE__*/React.createElement(FeatureSection, null), /*#__PURE__*/React.createElement(WorldSection, null), /*#__PURE__*/React.createElement(FooterSection, {
    version: versionNumber
  }));
}
function MainBannerSection({
  homeLinks,
  isLoggedIn,
  version,
  initialView,
  loginErrorText,
  registerErrorText,
  resetErrorText,
  loginMessageText,
  registerPreFill,
  newsRef,
  contactRef,
  AshBackground,
  scOpen,
  reopenTimeWindow
}) {
  const [loginDisplay, setLoginDisplay] = React.useState(initialView === "reset" ? "reset" : "login");
  const [activeModalName, setActiveModalName] = React.useState(initialView === "register" ? "register" : "none");
  const loginFormRef = React.useRef(null);
  function handleLogin() {
    loginFormRef.current?.submit();
  }
  function scrollTo(element) {
    if (element == null) return;
    element.scrollIntoView({
      behavior: 'smooth'
    });
  }
  function toSupport() {
    window.location.href = homeLinks['support'];
  }
  let activeModal = null;
  switch (activeModalName) {
    case "register":
      activeModal = /*#__PURE__*/React.createElement(MainBannerModal, {
        title: null,
        className: "register",
        handleCloseClick: () => setActiveModalName("none")
      }, /*#__PURE__*/React.createElement(RegisterForm, {
        registerErrorText: registerErrorText,
        registerPreFill: registerPreFill
      }));
      break;
    case "rules":
      activeModal = /*#__PURE__*/React.createElement(MainBannerModal, {
        title: "rules",
        className: "rules",
        handleCloseClick: () => setActiveModalName("none")
      }, /*#__PURE__*/React.createElement(Rules, null));
      break;
    case "terms":
      activeModal = /*#__PURE__*/React.createElement(MainBannerModal, {
        title: "terms",
        className: "terms",
        handleCloseClick: () => setActiveModalName("none")
      }, /*#__PURE__*/React.createElement(Terms, null));
      break;
  }
  React.useEffect(() => {
    if (initialView === "news" && newsRef.current) {
      scrollTo(newsRef.current);
    }
  }, [initialView]);
  return /*#__PURE__*/React.createElement("div", {
    className: "home_section main_banner_section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "main_banner_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "main_banner_image"
  }), /*#__PURE__*/React.createElement("div", {
    className: "main_banner_title"
  }, /*#__PURE__*/React.createElement("img", {
    src: "/images/v2/decorations/homepagelogo.png"
  }), /*#__PURE__*/React.createElement("div", {
    className: "title_version"
  }, version)), !scOpen && /*#__PURE__*/React.createElement("div", {
    className: "sc_maintenance_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "sc_maintenance_message"
  }, "Shinobi Chronicles is currently closed for maintenance.", /*#__PURE__*/React.createElement("br", null), "Check back in ", reopenTimeWindow, ".")), activeModal, /*#__PURE__*/React.createElement(AshBackground, null), /*#__PURE__*/React.createElement("div", {
    className: "login_container",
    style: activeModal != null ? {
      visibility: "hidden"
    } : {}
  }, !isLoggedIn && loginDisplay !== "reset" && /*#__PURE__*/React.createElement(LoginForm, {
    loginMessageText: loginMessageText,
    loginErrorText: loginErrorText,
    setLoginDisplay: setLoginDisplay,
    formRef: loginFormRef
  }), loginDisplay === "reset" && /*#__PURE__*/React.createElement(ResetPasswordForm, {
    resetErrorText: resetErrorText,
    handleCloseClick: () => setLoginDisplay("login")
  }), !isLoggedIn && /*#__PURE__*/React.createElement(LoginButton, {
    onCLick: handleLogin
  }), !isLoggedIn && activeModalName !== "register" && /*#__PURE__*/React.createElement(CreateCharacterButton, {
    onClick: () => setActiveModalName("register")
  }), isLoggedIn && /*#__PURE__*/React.createElement(LoggedInButtons, {
    homeLinks: homeLinks
  })), /*#__PURE__*/React.createElement("div", {
    className: "banner_button news"
  }, /*#__PURE__*/React.createElement(BannerDiamondButton, {
    handleClick: () => scrollTo(newsRef.current),
    firstLineText: "news &",
    secondLineText: "updates",
    color: "red",
    largeSize: true
  })), /*#__PURE__*/React.createElement("div", {
    className: "banner_button rules"
  }, /*#__PURE__*/React.createElement(BannerDiamondButton, {
    handleClick: () => {
      activeModalName === "rules" ? setActiveModalName("none") : setActiveModalName("rules");
    },
    firstLineText: "rules",
    color: "blue"
  })), /*#__PURE__*/React.createElement("div", {
    className: "banner_button terms"
  }, /*#__PURE__*/React.createElement(BannerDiamondButton, {
    handleClick: () => {
      activeModalName === "terms" ? setActiveModalName("none") : setActiveModalName("terms");
    },
    firstLineText: "terms of",
    secondLineText: "service",
    color: "red"
  })), /*#__PURE__*/React.createElement("div", {
    className: "banner_button contact"
  }, /*#__PURE__*/React.createElement(BannerDiamondButton, {
    handleClick: () => toSupport(),
    firstLineText: "contact us",
    color: "blue",
    largeSize: true
  }))));
}
function BannerDiamondButton({
  color,
  firstLineText,
  secondLineText,
  largeSize = false,
  handleClick
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "home_diamond_container"
  }, /*#__PURE__*/React.createElement("svg", {
    className: "home_diamond_svg",
    width: "100",
    height: "100",
    role: "button",
    tabIndex: "0",
    style: !largeSize ? {
      transform: "scale(0.85)"
    } : {},
    onClick: handleClick,
    onKeyPress: clickOnEnter
  }, /*#__PURE__*/React.createElement("g", {
    className: `home_diamond_rotategroup diamond_${color}`,
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
  })), secondLineText == null && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_shadow_text",
    x: "50",
    y: "52",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, firstLineText), /*#__PURE__*/React.createElement("text", {
    className: `home_diamond_${color}_text`,
    x: "50",
    y: "50",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, firstLineText)), secondLineText != null && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_shadow_text",
    x: "50",
    y: "40",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, firstLineText), /*#__PURE__*/React.createElement("text", {
    className: `home_diamond_${color}_text`,
    x: "50",
    y: "38",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, firstLineText), /*#__PURE__*/React.createElement("text", {
    className: "home_diamond_shadow_text",
    x: "50",
    y: "64",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, secondLineText), /*#__PURE__*/React.createElement("text", {
    className: `home_diamond_${color}_text`,
    x: "50",
    y: "62",
    textAnchor: "middle",
    dominantBaseline: "middle"
  }, secondLineText))));
}
function LoginForm({
  loginMessageText,
  loginErrorText,
  setLoginDisplay,
  formRef
}) {
  const handleInputKeyDown = e => {
    if (e.code !== "Enter") {
      return;
    }
    e.preventDefault();
    formRef.current?.submit();
  };
  return /*#__PURE__*/React.createElement("form", {
    id: "login_form",
    action: "",
    method: "post",
    ref: formRef
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_input_top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_username_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "login_username_label"
  }, "username"), /*#__PURE__*/React.createElement("input", {
    type: "text",
    name: "user_name",
    className: "login_username_input login_text_input",
    onKeyDown: handleInputKeyDown
  })), /*#__PURE__*/React.createElement("div", {
    className: "login_password_wrapper"
  }, /*#__PURE__*/React.createElement("label", {
    className: "login_username_label"
  }, "password"), /*#__PURE__*/React.createElement("input", {
    type: "password",
    name: "password",
    className: "login_password_input login_text_input",
    onKeyDown: handleInputKeyDown
  })), /*#__PURE__*/React.createElement("input", {
    type: "hidden",
    name: "login",
    value: "login"
  })), loginMessageText !== "" && /*#__PURE__*/React.createElement("div", {
    className: "login_input_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_message_label"
  }, loginMessageText)), loginErrorText !== "" && /*#__PURE__*/React.createElement("div", {
    className: "login_input_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "login_error_label"
  }, loginErrorText), /*#__PURE__*/React.createElement("div", {
    className: "reset_link",
    onClick: () => setLoginDisplay("reset")
  }, "reset password")));
}
function ResetPasswordForm({
  resetErrorText,
  handleCloseClick
}) {
  const formRef = React.useRef(null);
  return /*#__PURE__*/React.createElement("form", {
    id: "reset_form",
    action: "",
    method: "post",
    ref: formRef
  }, /*#__PURE__*/React.createElement("h3", null, "Reset Password"), /*#__PURE__*/React.createElement("button", {
    className: "modal_close",
    onKeyPress: clickOnEnter,
    onClick: handleCloseClick
  }, "X"), /*#__PURE__*/React.createElement("div", {
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
  }, resetErrorText !== "" && /*#__PURE__*/React.createElement("div", {
    className: "login_error_label"
  }, resetErrorText), /*#__PURE__*/React.createElement("div", {
    className: "reset_link",
    onClick: () => formRef.current?.submit()
  }, "send email")));
}
function MainBannerModal({
  title,
  className,
  children,
  handleCloseClick
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: `main_banner_modal ${className}`
  }, title ? /*#__PURE__*/React.createElement("div", {
    className: "modal_header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal_title"
  }, title), /*#__PURE__*/React.createElement("div", {
    role: "button",
    tabIndex: "0",
    className: "modal_close",
    onKeyPress: clickOnEnter,
    onClick: handleCloseClick
  }, "close")) : /*#__PURE__*/React.createElement("div", {
    className: "modal_close standalone",
    role: "button",
    tabIndex: "0",
    onKeyPress: clickOnEnter,
    onClick: handleCloseClick
  }, "close"), /*#__PURE__*/React.createElement("div", {
    className: "modal_content"
  }, children));
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
function FooterSection({
  version
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "home_section footer_section"
  }, /*#__PURE__*/React.createElement("div", {
    className: "footer_text"
  }, "SHINOBI CHRONICLES V", version, " COPYRIGHT \xA9 LM VISIONS"));
}
function LoginButton({
  onCLick
}) {
  return /*#__PURE__*/React.createElement("svg", {
    role: "button",
    tabIndex: "0",
    name: "login",
    className: "login_button",
    width: "162",
    height: "32",
    onClick: () => onCLick(),
    onKeyPress: clickOnEnter
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
      stopColor: '#1F3E56',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#264563',
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
      stopColor: '#264563',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#1F3E56',
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
  }, "login"));
}
function LoggedInButtons({
  homeLinks
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
    role: "button",
    href: homeLinks['profile'],
    style: {
      display: "flex",
      zIndex: 2
    }
  }, /*#__PURE__*/React.createElement("svg", {
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
      stopColor: '#1F3E56',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#264563',
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
      stopColor: '#264563',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#1F3E56',
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
    role: "button",
    href: homeLinks['logout'],
    style: {
      display: "flex",
      zIndex: 2
    }
  }, /*#__PURE__*/React.createElement("svg", {
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
      stopColor: '#721B25',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#822d31',
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
      stopColor: '#822d31',
      stopOpacity: 1
    }
  }), /*#__PURE__*/React.createElement("stop", {
    offset: "100%",
    style: {
      stopColor: '#721B25',
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
const AshBackground = () => {
  const canvasRef = React.useRef(null);
  const bufferRef = React.useRef(null);
  React.useEffect(() => {
    const cntr = document.getElementById("canvascontainer");
    const W = cntr.offsetWidth;
    const H = cntr.offsetHeight;
    const canvas = [canvasRef.current, bufferRef.current];
    const ctxs = [canvas[0].getContext("2d"), canvas[1].getContext("2d")];
    let C = 0;
    let angle = 0;
    const A = [];
    function ash(o) {
      var i,
        j,
        m = Math.random(),
        p = randomRange(4, 8, m);
      if (o && o.x) this.x = o.x;else this.x = m * W;
      if (o && o.y) this.y = o.y;else this.y = m * H;
      if (o && o.a) this.a = o.a;else this.a = m * (p - 4) + 4;
      this.r = randomRange(233, 255, m);
      this.g = randomRange(65, 100, m);
      this.b = randomRange(65, 100, m);
      if (o && o.dp) this.dp = o.dp;else {
        this.dp = [{
          x: 0,
          y: 0
        }];
        for (i = 0; i < p; i++) {
          j = i == 0 || p / 2 > i ? 1 : -1;
          this.dp.push({
            x: this.dp[i].x + randomRange(5, 30) * j,
            y: this.dp[i].y + randomRange(5, 30) * j
          });
        }
      }
    }
    function randomRange(from, to, seed) {
      return Math.floor((seed ? seed : Math.random()) * (to - from + 1) + from);
    }
    function draw() {
      var grad, i, j, p, ctx;
      if (C == 0) {
        //Show the canvas
        canvas[0].style.visibility = "visible";
        canvas[1].style.visibility = "hidden";
        C = 1;
      } else {
        //Show the buffer
        canvas[1].style.visibility = "visible";
        C = 0;
      }
      ctx = ctxs[C];
      ctx.clearRect(0, 0, W, H);
      for (i = 0; i < A.length; i++) {
        p = A[i];
        grad = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.a);
        grad.addColorStop(0, "rgba(" + p.r + ", " + p.g + ", " + p.b + ", 1)");
        grad.addColorStop(0.9, "rgba(" + p.r + ", " + p.g + ", " + p.b + ", " + randomRange(1, 10) / 10 + ")");
        grad.addColorStop(1, "rgba(" + p.r + ", " + p.g + ", " + p.b + ", 0)");
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        for (j = 1; j < p.dp.length; j++) ctx.lineTo(p.x + p.dp[j].x, p.y + p.dp[j].y);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.globalAlpha = 0.7;
        ctx.fill();
      }
      update();
    }
    function update() {
      var i, p;
      angle += 0.01;
      for (i = 0; i < A.length; i++) {
        p = A[i];
        p.y += Math.cos(angle + A.length) + p.a / 4;
        p.x += Math.sin(angle) * 2;
        if (p.x > W + 5 || p.x < -5 || p.y > H) {
          if (i % 3 > 0) A[i] = new ash({
            y: -10,
            a: p.a,
            d: p.d,
            dp: p.dp
          });else {
            //Enter from the left
            if (Math.sin(angle) > 0) A[i] = new ash({
              x: -5,
              a: p.a,
              d: p.d,
              dp: p.dp
            });
            //Enter from the right
            else A[i] = new ash({
              x: W + 5,
              a: p.a,
              d: p.d,
              dp: p.dp
            });
          }
        }
      }
    }
    canvas[0].width = W;
    canvas[0].height = H;
    canvas[1].width = W;
    canvas[1].height = H;
    for (var i = 0; i < 75; i++) {
      var initialY = Math.random() * H; // random position between 0 to canvas height
      A.push(new ash({
        y: initialY
      }));
    }
    const interval = setInterval(draw, 15);

    // Cleanup function
    return () => {
      clearInterval(interval);
    };
  }, []);
  return /*#__PURE__*/React.createElement("div", {
    id: "canvascontainer"
  }, /*#__PURE__*/React.createElement("canvas", {
    ref: canvasRef,
    id: "canvas",
    style: {
      visibility: 'hidden'
    }
  }), /*#__PURE__*/React.createElement("canvas", {
    ref: bufferRef,
    id: "buffer",
    style: {
      visibility: 'hidden'
    }
  }));
};
window.Home = Home;