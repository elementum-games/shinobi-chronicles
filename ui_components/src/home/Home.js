// @flow

import { RegisterForm } from "./RegisterForm.js";
import { Rules, Terms } from "./staticPageContents.js";
import { clickOnEnter } from "../utils/uiHelpers.js"

import type { NewsPostType } from "./newsSchema.js";
import { News } from "./News.js";

type Props = {|
    +homeLinks: $ReadOnlyArray,
    +isLoggedIn: bool,
    +isAdmin: bool,
    +version: string,
    +initialLoginDisplay: string,
    +loginErrorText: string,
    +registerErrorText: string,
    +resetErrorText: string,
    +loginMessageText: string,
    +registerPreFill: $ReadOnlyArray,
    +initialNewsPosts: $ReadOnlyArray<NewsPostType>,
|};
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
    initialNewsPosts,
}: Props) {
    const [loginDisplay, setLoginDisplay] = React.useState(initialLoginDisplay);
    const newsRef = React.useRef(null);
    const contactRef = React.useRef(null);
    const activeElement = React.useRef(null);

    return (
        <>
            <MainBannerSection
                homeLinks={homeLinks}
                isLoggedIn={isLoggedIn}
                version={version}
                loginDisplay={loginDisplay}
                setLoginDisplay={setLoginDisplay}
                loginErrorText={loginErrorText}
                registerErrorText={registerErrorText}
                resetErrorText={resetErrorText}
                loginMessageText={loginMessageText}
                registerPreFill={registerPreFill}
                newsRef={newsRef}
                contactRef={contactRef}
            />
            <div ref={newsRef} id="news_container" className={"home_section news_section"}>
                <div className="home_header">
                    <label className="home_header_label">NEWS & UPDATES</label>
                    <div className="home_external_links">
                        <a href={homeLinks['github']} className="home_github_wrapper">
                            <img className="home_github" src="../../../images/v2/icons/githubhover.png"/>
                        </a>
                        <a href={homeLinks['discord']} className="home_discord_wrapper">
                            <img className="home_discord" src="../../../images/v2/icons/discordhover.png"/>
                        </a>
                    </div>
                </div>
                <News
                    initialNewsPosts={initialNewsPosts}
                    isAdmin={isAdmin}
                    version={version}
                    homeLinks={homeLinks}
                />
            </div>
            <FeatureSection />
            <WorldSection />
            {/*<ContactSection
                contactRef={contactRef}
            />*/}
            <FooterSection />
        </>
    );
}

function MainBannerSection({
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
    contactRef,
}) {
    const activeElement = React.useRef(null);
    function handleLogin() {
        if (loginDisplay !== "login") {
            setLoginDisplay("login");
        }
        else {
            document.getElementById('login_form').submit();
        }
    }
    function handleRegister() {
        if (loginDisplay !== "register") {
            setLoginDisplay("register");
        }
        else {
            document.getElementById('register_form').submit();
        }
    }
    function handleReset() {
        document.getElementById('reset_form').submit();
    }
    function scrollTo(element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
    function toSupport() {
        window.location = homeLinks['support'];
    }

    let activeModal = null;
    switch(loginDisplay) {
        case "register":
            activeModal = <MainBannerModal
                title={null}
                className="register"
                handleCloseClick={() => setLoginDisplay("none")}
            >
                <RegisterForm
                    registerErrorText={registerErrorText}
                    registerPreFill={registerPreFill}
                    setLoginDisplay={setLoginDisplay}
                />
            </MainBannerModal>;
            break;
        case "rules":
            activeModal = <MainBannerModal
                title="rules"
                className="rules"
                handleCloseClick={() => setLoginDisplay("none")}
            >
                <Rules />
            </MainBannerModal>;
            break;
        case "terms":
            activeModal = <MainBannerModal
                title="terms"
                className="terms"
                handleCloseClick={() => setLoginDisplay("none")}
            >
                <Terms />
            </MainBannerModal>;
            break;
    }

    return (
        <div className="home_section main_banner_section">
            <div className="main_banner_container">
                <div className="main_banner_image"></div>
                <div className="main_banner_title">
                    <img src="/images/v2/decorations/homepagelogo.png" />
                    <div className="title_version">{version}</div>
                </div>

                {/* Lanterns WIP
                <div className="home_lantern_1" style={{ zIndex: 1 }}><img src="/images/v2/decorations/lanternbig.png" /></div>
                <div className="home_lantern_2" style={{ zIndex: 0 }}><img src="/images/v2/decorations/lanternbig2.png" /></div>
                <div className="home_lantern_3" style={{ zIndex: 1 }}><img src="/images/v2/decorations/lanternsmall.png" /></div>
                <div className="home_lantern_4" style={{ zIndex: 1 }}><img src="/images/v2/decorations/lanternbig.png" /></div>
                <div className="home_lantern_5" style={{ zIndex: 1 }}><img src="/images/v2/decorations/lanternsmall.png" /></div>
                <div className="home_lantern_6" style={{ zIndex: 1 }}><img src="/images/v2/decorations/lanternbig.png" /></div>
                <div className="home_lantern_7" style={{ zIndex: 1 }}><img src="/images/v2/decorations/lanternsmall.png" /></div>
                <div className="home_lantern_8" style={{ zIndex: 1 }}><img src="/images/v2/decorations/lanternbig2.png" /></div>*/}

                {activeModal}

                <div className="login_container">
                    {loginDisplay === "login" &&
                        <LoginForm
                            loginMessageText={loginMessageText}
                            loginErrorText={loginErrorText}
                            setLoginDisplay={setLoginDisplay}
                        />
                    }
                    {loginDisplay === "reset" &&
                        <form id="reset_form" action="" method="post" style={{ zIndex: 1 }}>
                            <div className="reset_input_top">
                                <input type="hidden" name="reset" value="reset" />
                                <div className="login_username_wrapper">
                                    <label className="login_username_label">username</label>
                                    <input type="text" name="username" className="login_username_input login_text_input" />
                                </div>
                                <div className="reset_email_wrapper">
                                    <label className="reset_email_label">email address</label>
                                    <input type="email" name="email" className="reset_email_input login_text_input" />
                                </div>
                            </div>

                            <div className="reset_input_bottom">
                                {resetErrorText !== "" &&
                                    <div className="login_error_label">{resetErrorText}</div>
                                }
                                <div className="reset_link" onClick={() => handleReset()}>send email</div>
                            </div>
                        </form>
                    }


                    {!isLoggedIn &&
                        <LoggedOutButtons handleLogin={handleLogin} handleRegister={handleRegister} />
                    }
                    {isLoggedIn &&
                        <LoggedInButtons homeLinks={homeLinks} />
                    }
                </div>

                <div className="banner_button news">
                    <BannerDiamondButton
                        handleClick={() => scrollTo(newsRef.current)}
                        firstLineText="news &"
                        secondLineText="updates"
                        color="red"
                        largeSize={true}
                    />
                </div>
                <div className="banner_button rules">
                    <BannerDiamondButton
                        handleClick={() => {
                            loginDisplay === "rules"
                                ? setLoginDisplay("none")
                                : setLoginDisplay("rules")
                        }}
                        firstLineText="rules"
                        color="blue"
                    />
                </div>
                <div className="banner_button terms">
                    <BannerDiamondButton
                        handleClick={() => {
                            loginDisplay === "terms"
                                ? setLoginDisplay("none")
                                : setLoginDisplay("terms")
                        }}
                        firstLineText="terms of"
                        secondLineText="service"
                        color="red"
                    />
                </div>
                {/*<div className="login_features_button">
                    <BannerDiamondButton
                        handleClick={() => {}}
                        firstLineText="game"
                        secondLineText="features"
                        color="blue"
                    />
                </div>
                <div className="login_world_button">
                    <BannerDiamondButton
                        handleClick={() => {}}
                        firstLineText="world info"
                        color="blue"
                    />
                </div>*/}
                <div className="banner_button contact">
                    <BannerDiamondButton
                        handleClick={() => toSupport()}
                        firstLineText="contact us"
                        color="blue"
                        largeSize={true}
                    />
                </div>
            </div>
        </div>
    );
}

type BannerDiamondButtonProps = {|
    +color: "blue" | "red",
    +firstLineText: string,
    +secondLineText?: string,
    +largeSize?: boolean,
    +handleClick: () => void,
|};
function BannerDiamondButton({
    color,
    firstLineText,
    secondLineText,
    largeSize = false,
    handleClick,
}: BannerDiamondButtonProps) {
    return (
        <div className="home_diamond_container">
            <svg
                className="home_diamond_svg"
                width="100"
                height="100"
                role="button"
                tabIndex="0"
                style={!largeSize
                    ? { transform: "scale(0.85)" }
                    : {}
                }
                onClick={handleClick}
                onKeyPress={clickOnEnter}
            >
                <g className={`home_diamond_rotategroup diamond_${color}`} transform="rotate(45 50 50)">
                    <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                    <rect className="home_diamond_up" x="4" y="4" width="45px" height="45px" />
                    <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                    <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                    <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                </g>
                {secondLineText == null &&
                    <>
                        <text className="home_diamond_shadow_text" x="50" y="52" textAnchor="middle" dominantBaseline="middle">{firstLineText}</text>
                        <text className={`home_diamond_${color}_text`} x="50" y="50" textAnchor="middle" dominantBaseline="middle">{firstLineText}</text>
                    </>
                }
                {secondLineText != null &&
                    <>
                        <text className="home_diamond_shadow_text" x="50" y="40" textAnchor="middle" dominantBaseline="middle">{firstLineText}</text>
                        <text className={`home_diamond_${color}_text`} x="50" y="38" textAnchor="middle" dominantBaseline="middle">{firstLineText}</text>
                        <text className="home_diamond_shadow_text" x="50" y="64" textAnchor="middle" dominantBaseline="middle">{secondLineText}</text>
                        <text className={`home_diamond_${color}_text`} x="50" y="62" textAnchor="middle" dominantBaseline="middle">{secondLineText}</text>
                    </>
                }
            </svg>
        </div>
    );
}

type LoginFormProps = {|
    +loginMessageText: string,
    +loginErrorText: string,
    +setLoginDisplay: (string) => void,
|};
function LoginForm({ loginMessageText, loginErrorText, setLoginDisplay }: LoginFormProps) {
    return (
        <form id="login_form" action="" method="post" style={{ zIndex: 1 }}>
            <div className="login_input_top">
                <div className="login_username_wrapper">
                    <label className="login_username_label">username</label>
                    <input type="text" name="user_name" className="login_username_input login_text_input" />
                </div>
                <div className="login_password_wrapper">
                    <label className="login_username_label">password</label>
                    <input type="password" name="password" className="login_password_input login_text_input" />
                </div>
                <input type="hidden" name="login" value="login"/>
            </div>
            {loginMessageText !== "" &&
                <div className="login_input_bottom">
                    <div className="login_message_label">{loginMessageText}</div>
                </div>
            }
            {loginErrorText !== "" &&
                <div className="login_input_bottom">
                    <div className="login_error_label">{loginErrorText}</div>
                    <div className="reset_link" onClick={() => setLoginDisplay("reset")}>reset password</div>
                </div>
            }
        </form>
    )
}

function MainBannerModal({ title, className, children, handleCloseClick}) {
    return (
        <div className={`main_banner_modal ${className}`}>
            {title
                ? <div className="modal_header">
                    <div className="modal_title">{title}</div>
                    <div role="button" tabIndex="0" className="modal_close" onKeyPress={clickOnEnter} onClick={handleCloseClick}>close</div>
                </div>
                :
                <div className="modal_close standalone" role="button" tabIndex="0" onKeyPress={clickOnEnter} onClick={handleCloseClick}>close</div>
            }
            <div className="modal_content">
                {children}
            </div>
        </div>
    );
}


function FeatureSection({ }) {
    return (
        <></>
    );
}

function WorldSection({ }) {
    return (
        <></>
    );
}

function ContactSection({ contactRef }) {
    return (
        <div ref={contactRef} id="contact_container" className={"home_section contact_section"}>
            <div className="home_header"><label className="home_header_label">CONTACT US</label></div>
            <div className="home_form_container">

            </div>
        </div>
    );
}
function FooterSection({ }) {
    return (
        <div className={"home_section footer_section"}>
            <div className="footer_text">SHINOBI CHRONICLES V0.9.0 COPYRIGHT &copy; LM VISIONS</div>
        </div>
    );
}

function LoggedOutButtons({ handleLogin, handleRegister }) {
    return (
        <>
            <svg role="button" tabIndex="0" name="login" className="login_button" width="162" height="32" onClick={() => handleLogin()} onKeyPress={clickOnEnter} style={{ zIndex: 2 }}>
                <radialGradient id="login_fill_default" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                    <stop offset="0%" style={{ stopColor: '#464f87', stopOpacity: 1 }} />
                    <stop offset="100%" style={{ stopColor: '#343d77', stopOpacity: 1 }} />
                </radialGradient>
                <radialGradient id="login_fill_click" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                    <stop offset="0%" style={{ stopColor: '#343d77', stopOpacity: 1 }} />
                    <stop offset="100%" style={{ stopColor: '#464f87', stopOpacity: 1 }} />
                </radialGradient>
                <rect className="login_button_background" width="100%" height="100%" fill="url(#login_fill_default)" />
                <text className="login_button_shadow_text" x="81" y="18" textAnchor="middle" dominantBaseline="middle">login</text>
                <text className="login_button_text" x="81" y="16" textAnchor="middle" dominantBaseline="middle">login</text>
            </svg>
            <svg role="button" tabIndex="0" name="register" className="register_button" width="162" height="32" onClick={() => handleRegister()} onKeyPress={clickOnEnter} style={{ zIndex: 4 }}>
                <radialGradient id="register_fill_default" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                    <stop offset="0%" style={{ stopColor: '#84314e', stopOpacity: 1 }} />
                    <stop offset="100%" style={{ stopColor: '#68293f', stopOpacity: 1 }} />
                </radialGradient>
                <radialGradient id="register_fill_click" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                    <stop offset="0%" style={{ stopColor: '#68293f', stopOpacity: 1 }} />
                    <stop offset="100%" style={{ stopColor: '#84314e', stopOpacity: 1 }} />
                </radialGradient>
                <rect className="register_button_background" width="100%" height="100%" />
                <text className="register_button_shadow_text" x="81" y="18" textAnchor="middle" dominantBaseline="middle">create a character</text>
                <text className="register_button_text" x="81" y="16" textAnchor="middle" dominantBaseline="middle">create a character</text>
            </svg>
        </>
    );
}

function LoggedInButtons({ homeLinks }) {
    return (
        <>
            <a role="button" href={homeLinks['profile']} style={{ display: "flex", zIndex: 2 }}>
                <svg className="profile_button" width="162" height="32">
                    <radialGradient id="profile_fill_default" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                        <stop offset="0%" style={{ stopColor: '#464f87', stopOpacity: 1 }} />
                        <stop offset="100%" style={{ stopColor: '#343d77', stopOpacity: 1 }} />
                    </radialGradient>
                    <radialGradient id="profile_fill_click" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                        <stop offset="0%" style={{ stopColor: '#343d77', stopOpacity: 1 }} />
                        <stop offset="100%" style={{ stopColor: '#464f87', stopOpacity: 1 }} />
                    </radialGradient>
                    <rect className="profile_button_background" width="100%" height="100%" />
                    <text className="profile_button_shadow_text" x="81" y="18" textAnchor="middle" dominantBaseline="middle">profile</text>
                    <text className="profile_button_text" x="81" y="16" textAnchor="middle" dominantBaseline="middle">profile</text>
                </svg>
            </a>
            <a role="button" href={homeLinks['logout']} style={{ display: "flex", zIndex: 2 }}>
                <svg className="logout_button" width="162" height="32">
                    <radialGradient id="logout_fill_default" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                        <stop offset="0%" style={{ stopColor: '#84314e', stopOpacity: 1 }} />
                        <stop offset="100%" style={{ stopColor: '#68293f', stopOpacity: 1 }} />
                    </radialGradient>
                    <radialGradient id="logout_fill_click" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                        <stop offset="0%" style={{ stopColor: '#68293f', stopOpacity: 1 }} />
                        <stop offset="100%" style={{ stopColor: '#84314e', stopOpacity: 1 }} />
                    </radialGradient>
                    <rect className="logout_button_background" width="100%" height="100%" />
                    <text className="logout_button_shadow_text" x="81" y="18" textAnchor="middle" dominantBaseline="middle">logout</text>
                    <text className="logout_button_text" x="81" y="16" textAnchor="middle" dominantBaseline="middle">logout</text>
                </svg>
            </a>
        </>
    );
}

window.Home = Home;