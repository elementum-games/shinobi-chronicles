import { apiFetch } from "../utils/network.js";
import type { NewsPostType } from "./newsSchema.js";


type Props = {|
    +newsApiLink: string,
    +loginErrorText: string,
    +registerErrorText: string,
    +resetErrorText: string,
    +loginMessageText: string,
    +registerPreFill: $ReadOnlyArray,
    +initialNewsPosts: $ReadOnlyArray < NewsPostType >,
|};
function Home({
    newsApiLink,
    loginErrorText,
    registerErrorText,
    resetErrorText,
    loginMessageText,
    registerPreFill,
    initialNewsPosts,
}: Props) {
    const [displayLogin, setDisplayLogin] = React.useState(loginErrorText == "" ? false : true);
    const [displayRegister, setDisplayRegister] = React.useState(registerErrorText == "" ? false : true);
    const [displayReset, setDisplayReset] = React.useState(resetErrorText == "" ? false : true);
    const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);

    return (
        <>
            <LoginSection
                displayLogin={displayLogin}
                setDisplayLogin={setDisplayLogin}
                displayRegister={displayRegister}
                setDisplayRegister={setDisplayRegister}
                displayReset={displayReset}
                setDisplayReset={setDisplayReset}
                loginErrorText={loginErrorText}
                registerErrorText={registerErrorText}
                resetErrorText={resetErrorText}
                loginMessageText={loginMessageText}
                registerPreFill={registerPreFill}
            />
            <NewsSection
                newsPosts={newsPosts}
            />
            <FeatureSection />
            <WorldSection />
            <ContactSection />
        </>
    );
}

function LoginSection({ displayLogin, setDisplayLogin, displayRegister, setDisplayRegister, displayReset, setDisplayReset, loginErrorText, registerErrorText, resetErrorText, loginMessageText, registerPreFill }) {
    function handleLogin() {
        setDisplayRegister(false);
        setDisplayReset(false);
        if (!displayLogin) {
            setDisplayLogin(true);
        }
        else {
            document.getElementById('login_form').submit();
        }
    }
    function handleRegister() {
        setDisplayReset(false);
        setDisplayLogin(false);
        if (!displayRegister) {
            setDisplayRegister(true);
        }
        else {
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

    return (
        <div className={"home_section login_section"}>
            <div className="login_center_wrapper">
                <div className="login_center_inner">
                    <div className="login_inner_title"><img src="/images/v2/decorations/homepagelogo.png" /></div>
                    <div className="login_inner_version">0.9 MAKE THINGS LOOK GOOD</div>
                    <div className="login_inner_input_container">
                        {displayLogin &&
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
                            {loginMessageText != "" &&
                                <div className="login_input_bottom">
                                    <div className="login_message_label">{loginMessageText}</div>
                                </div>
                            }
                            {loginErrorText != "" &&
                                <div className="login_input_bottom">
                                    <div className="login_error_label">{loginErrorText}</div>
                                    <div className="reset_link" onClick={() => handleDisplayReset()}>reset password</div>
                                </div>
                            }
                            </form>
                        }
                        {displayRegister &&
                            <form id="register_form" action="" method="post" style={{ zIndex: 3 }}>
                            <div className="register_input_top">
                                <input type="hidden" name="register" value="register" />
                                <div className="register_username_container">
                                    <div className="register_username_wrapper">
                                        <label className="register_field_label">username</label>
                                        <input type="text" name="user_name" className="register_username_input login_text_input" defaultValue={registerPreFill.user_name}/>
                                    </div>
                                    <div className="register_close" onClick={() => setDisplayRegister(false)}>close</div>
                                </div>
                                <div className="register_password_container">
                                    <div className="register_password_wrapper">
                                        <label className="register_field_label">password</label>
                                        <input type="password" name="password" className="register_password_input login_text_input" />
                                    </div>
                                    <div className="register_confirm_wrapper">
                                        <label className="register_field_label">confirm password</label>
                                        <input type="password" name="confirm_password" className="register_password_confirm login_text_input" />
                                    </div>
                                </div>
                                <div>
                                    <div className="register_email_wrapper">
                                        <label className="register_field_label">email</label>
                                        <input type="text" name="email" className="login_username_input login_text_input" defaultValue={registerPreFill.email} />
                                    </div>
                                </div>
                                <div>
                                    <div className="register_email_notice">(Note: Currently we cannot send emails to addresses from:</div>
                                    <div className="register_email_notice">hotmail.com, live.com, msn.com, outlook.com)</div>
                                </div>
                                <div className="register_character_container">
                                    <div className="register_gender_wrapper">
                                        <div className="register_field_label">gender</div>
                                        <div>
                                            <input className="register_option" type="radio" id="register_gender_male" name="gender" value="Male" defaultChecked={registerPreFill.gender == "Male"} />
                                            <label className="register_option_label" htmlFor="register_gender_male">Male</label>
                                        </div>
                                        <div>
                                            <input className="register_option" type="radio" id="register_gender_female" name="gender" value="Female" defaultChecked={registerPreFill.gender == "Female"} />
                                            <label className="register_option_label" htmlFor="register_gender_female">Female</label>
                                        </div>
                                        <div>
                                            <input className="register_option" type="radio" id="register_gender_nonbinary" name="gender" value="Non-binary" defaultChecked={registerPreFill.gender == "Non-binary"} />
                                            <label className="register_option_label" htmlFor="register_gender_nonbinary">Non-binary</label>
                                        </div>
                                        <div>
                                            <input className="register_option" type="radio" id="register_gender_none" name="gender" value="None" defaultChecked={registerPreFill.gender == "None"} />
                                            <label className="register_option_label" htmlFor="register_gender_none">None</label>
                                        </div>
                                    </div>
                                    <div className="register_village_wrapper">
                                        <div className="register_field_label">village</div>
                                        <div>
                                            <input className="register_option" type="radio" id="register_village_stone" name="village" value="Stone" defaultChecked={registerPreFill.village == "Stone"} />
                                            <label className="register_option_label" htmlFor="register_village_stone">Stone</label>
                                            </div>
                                            <div>
                                            <input className="register_option" type="radio" id="register_village_cloud" name="village" value="Cloud" defaultChecked={registerPreFill.village == "Cloud"} />
                                            <label className="register_option_label" htmlFor="register_village_cloud">Cloud</label>
                                            </div>
                                            <div>
                                            <input className="register_option" type="radio" id="register_village_leaf" name="village" value="Leaf" defaultChecked={registerPreFill.village == "Leaf"} />
                                            <label className="register_option_label" htmlFor="register_village_leaf">Leaf</label>
                                            </div>
                                            <div>
                                            <input className="register_option" type="radio" id="register_village_sand" name="village" value="Sand" defaultChecked={registerPreFill.village == "Sand"} />
                                            <label className="register_option_label" htmlFor="register_village_sand">Sand</label>
                                        </div>
                                        <div>
                                            <input className="register_option" type="radio" id="register_village_mist" name="village" value="Mist" defaultChecked={registerPreFill.village == "Mist"} />
                                            <label className="register_option_label" htmlFor="register_village_mist">Mist</label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div className="register_terms_notice">By clicking 'Create a Character' I affirm that I have read and agree to abide by the Rules and Terms of Service. I understand that if I fail to abide by the rules as determined by the moderating staff, I may be temporarily or permanently banned and that I will not be compensated for time lost. I also understand that any actions taken by anyone on my account are my responsibility.</div>
                                </div>
                            </div>
                            {registerErrorText != "" &&
                                <div className="register_input_bottom">
                                <div className="login_error_label" style={{ marginBottom: "10px" }}>{registerErrorText}</div>
                                </div>
                            }
                            </form>
                        }
                        {displayReset &&
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
                                {resetErrorText != "" &&
                                    <div className="login_error_label">{resetErrorText}</div>
                                }
                                <div className="reset_link" onClick={() => handleReset()}>send email</div>
                            </div>
                        </form>
                        }
                        <svg role="button" tabIndex="0" name="login" className="login_button" width="162" height="32" onClick={() => handleLogin()} style={{ zIndex: 2 }}>
                            <rect className="login_button_background" width="100%" height="100%" />
                            <text className="login_button_shadow_text" x="81" y="18" textAnchor="middle" dominantBaseline="middle">login</text>
                            <text className="login_button_text" x="81" y="16" textAnchor="middle" dominantBaseline="middle">login</text>
                        </svg>
                        <svg role="button" tabIndex="0" name="register" className="register_button" width="162" height="32" onClick={() => handleRegister()} style={{zIndex:4}}>
                            <rect className="register_button_background" width="100%" height="100%" />
                            <text className="register_button_shadow_text" x="81" y="18" textAnchor="middle" dominantBaseline="middle">create a character</text>
                            <text className="register_button_text" x="81" y="16" textAnchor="middle" dominantBaseline="middle">create a character</text>
                        </svg>
                    </div>
                </div>
            </div>
            <div className="login_news_button">
                <div className="home_diamond_container">
                    <svg className="home_diamond_svg" width="100" height="100">
                        <g className={"home_diamond_rotategroup diamond_red"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45px" height="45px" />
                            <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                            <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                            <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                        </g>
                        <text className="home_diamond_shadow_text" x="50" y="40" textAnchor="middle" dominantBaseline="middle">news &</text>
                        <text className="home_diamond_red_text" x="50" y="38" textAnchor="middle" dominantBaseline="middle">news &</text>
                        <text className="home_diamond_shadow_text" x="50" y="64" textAnchor="middle" dominantBaseline="middle">updates</text>
                        <text className="home_diamond_red_text" x="50" y="62" textAnchor="middle" dominantBaseline="middle">updates</text>
                    </svg>
                </div>
            </div>
            <div className="login_features_button">
                <div className="home_diamond_container">
                    <svg width="100" height="100">
                        {/*<g className={"home_diamond_rotategroup diamond_blue"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45px" height="45px" />
                            <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                            <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                            <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                        </g>
                        <text className="home_diamond_shadow_text" x="50" y="40" textAnchor="middle" dominantBaseline="middle">game</text>
                        <text className="home_diamond_blue_text" x="50" y="38" textAnchor="middle" dominantBaseline="middle">game</text>
                        <text className="home_diamond_shadow_text" x="50" y="64" textAnchor="middle" dominantBaseline="middle">features</text>
                        <text className="home_diamond_blue_text" x="50" y="62" textAnchor="middle" dominantBaseline="middle">features</text>*/}
                    </svg>
                </div>
            </div>
            <div className="login_world_button">
                <div className="home_diamond_container">
                    <svg width="100" height="100">
                        {/*<g className={"home_diamond_rotategroup diamond_red"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45px" height="45px" />
                            <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                            <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                            <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                        </g>
                        <text className="home_diamond_shadow_text" x="50" y="52" textAnchor="middle" dominantBaseline="middle">world info</text>
                        <text className="home_diamond_red_text" x="50" y="50" textAnchor="middle" dominantBaseline="middle">world info</text>*/}
                        
                    </svg>
                </div>
            </div>
            <div className="login_contact_button">
                <div className="home_diamond_container">
                    <svg className="home_diamond_svg" width="100" height="100">
                        <g className={"home_diamond_rotategroup diamond_blue"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45px" height="45px" />
                            <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                            <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                            <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                        </g>
                        <text className="home_diamond_shadow_text" x="50" y="52" textAnchor="middle" dominantBaseline="middle">contact us</text>
                        <text className="home_diamond_blue_text" x="50" y="50" textAnchor="middle" dominantBaseline="middle">contact us</text>
                    </svg>
                </div>
            </div>
        </div>
    );
}

function NewsSection({ }) {
    return (
        <></>
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

function ContactSection({ }) {
    return (
        <></>
    );
}

window.Home = Home;