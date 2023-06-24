// @flow
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
    const [displayLogin, setDisplayLogin] = React.useState((loginErrorText != "" || loginMessageText != "") ? true : false);
    const [displayRegister, setDisplayRegister] = React.useState(registerErrorText == "" ? false : true);
    const [displayReset, setDisplayReset] = React.useState(resetErrorText == "" ? false : true);
    const [displayRules, setDisplayRules] = React.useState(false);
    const [displayTerms, setDisplayTerms] = React.useState(false);
    const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);
    const newsRef = React.useRef(null);
    const contactRef = React.useRef(null);

    return (
        <>
            <LoginSection
                displayLogin={displayLogin}
                setDisplayLogin={setDisplayLogin}
                displayRegister={displayRegister}
                setDisplayRegister={setDisplayRegister}
                displayReset={displayReset}
                setDisplayReset={setDisplayReset}
                displayRules={displayRules}
                setDisplayRules={setDisplayRules}
                displayTerms={displayTerms}
                setDisplayTerms={setDisplayTerms}
                loginErrorText={loginErrorText}
                registerErrorText={registerErrorText}
                resetErrorText={resetErrorText}
                loginMessageText={loginMessageText}
                registerPreFill={registerPreFill}
                newsRef={newsRef}
                contactRef={contactRef}
            />
            <NewsSection
                newsRef={newsRef}
                newsPosts={newsPosts}
            />
            <FeatureSection />
            <WorldSection />
            <ContactSection
                contactRef={contactRef}
            />
            <FooterSection />
        </>
    );
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
    registerPreFill,
    newsRef,
    contactRef,
}) {
    function handleLogin() {
        setDisplayRegister(false);
        setDisplayReset(false);
        setDisplayRules(false);
        setDisplayTerms(false);
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
        setDisplayRules(false);
        setDisplayTerms(false);
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
        setDisplayRules(true);
    }
    function handleTerms() {
        setDisplayLogin(false);
        setDisplayRegister(false);
        setDisplayReset(false);
        setDisplayRules(false);
        setDisplayTerms(true);
    }
    function scrollTo(element) {
        element.scrollIntoView({ behavior: 'smooth' });
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
                                <div className="login_error_label" style={{ marginBottom: "30px", marginLeft: "30px", marginTop: "-15px" }}>{registerErrorText}</div>
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
                        {displayRules &&
                            <div className="rules_modal" style={{ zIndex: 5 }}>
                                <div className="rules_header">
                                    <div className="rules_title">rules</div>
                                <div className="rules_close" onClick={() => setDisplayRules(false)}>close</div>
                                </div>
                                <div className="rules_content">
                                    These rules are meant to serve as a guideline for on-site behavior. Case-by-case interpretation and enforcement is at the
                                        discretion of the moderating staff. If you have any problems with a moderator's decision, do not call them out in the chat. Follow the
                                        chain of command; any problems with a moderator go to a head moderator FIRST before going to an admin.

                                    <br />

                                    <h3>Offensive language</h3>
                                    <div>
                                        Using offensive language is against the rules. All users are encouraged to avoid using language that would offend others in public
                                        or private settings. Shinobi Chronicles promotes an environment for a mixed age group; thus, inappropriate language is prohibited.
                                        This includes, but not limited to:
                                        <ul>
                                            <li>Profanity that bypasses the explicit language filter (e.g. w0rd instead of word)</li>
                                            <li>Racism</li>
                                            <li>Religious discrimination</li>
                                            <li>Explicit or excessive sexual references</li>
                                            <li>Inappropriate references to illegal drugs and their use</li>
                                            <li>Accounts with offensive usernames</li>
                                        </ul>
                                    </div>

                                    <h3>Images</h3>
                                    <div>
                                        All user pictures are subject to moderation (i.e. avatars, signatures, or any other publicly displayed images). Inappropriate pictures
                                        would contain the following:
                                        <ul>
                                            <li>Sexual content</li>
                                            <li>Profanity</li>
                                            <li>Racism</li>
                                            <li>Harassment </li>
                                        </ul>
                                        The administration reserves the right to deem user-pictures inappropriate, even when not falling under any of the above categories. If
                                        the subjected user refuses to change the picture after the request of staff, the administration will be forced to change the picture
                                        and ban the user.
                                    </div>

                                    <h3>Social Etiquette/Spamming</h3>
                                    <div>
                                        To promote a social and peaceful environment, a few guidelines have been set to ensure a user friendly experience. Those guidelines
                                        are as follows:
                                        <ul>
                                            <li>Within publicly accessible locations, excessive use of any language besides English is not allowed. (Other languages can be
                                                used in Personal Messages or other private places.)</li>
                                            <li>Sexually excessive, and/or racist posts are not allowed.</li>
                                            <li>Harassing other players and/or staff is not allowed</li>
                                            <li>Excessive use of BBCode, ASCII art, or meme faces is not permissible.</li>
                                            <li>Nonsensical posts that do not contribute to the conversation in any way are not allowed.</li>
                                            <li>Harassment, trolling, or otherwise pestering a user is not allowed.</li>
                                            <li>Unnecessarily breaking up chat messages into multiple short posts (e.g. "hello" "my" "name" "is" "bob") is not allowed.</li>
                                        </ul>
                                    </div>

                                    <h3>Account Responsibility:</h3>
                                    <div>
                                        <ul>
                                            <li>Account limits: 2 accounts</li>
                                            <li>Attacking your own account is not allowed.</li>
                                            <li>Account sharing is not allowed.</li>
                                            <li>Impersonating staff is forbidden</li>
                                        </ul>
                                    </div>

                                    <h3>Glitching/Hacking:</h3>
                                    <div>
                                        Exploiting bugs/glitches, attempting to hack/crack the site or its data, or changing site code is strictly prohibited. Any attempts
                                        will be met with severe punishment.
                                        <br />
                                        There is <i>Zero Tolerance</i> for planning attacks against other games anywhere on Shinobi-Chronicles. Any discussion of these topics
                                        is strictly forbidden and will be met with punishment as severe as the situation dictates.
                                    </div>

                                    <h3>Manga Spoilers</h3>
                                    <div>
                                        As this is an anime/manga-themed game, it can be expected that most of the userbase follows various ongoing manga/anime series.
                                        Since many people for various reasons do not read the manga, but only watch the anime, posting spoilers of things that have not
                                        happened in the anime yet of a major ongoing series (Naruto, One Piece, My Hero Academia, Demon Slayer, etc) is not allowed as
                                        it can significantly lessen the experience of watching the show.
                                        <br />
                                    </div>

                                    <h3>Bots/macros/etc:</h3>
                                    <div>
                                        Bots, macros, or any other devices (hardware or software) that play the game for you, are prohibited. Any characters caught botting
                                        will receive a ban along with a stat cut.
                                    </div>

                                    <h3>Links:</h3>
                                    <div>
                                        Linking to sites that violate any of these rules (e.g: sites with explicit content) is prohibited.<br />
                                            Linking to sites that contain language unsuitable for SC is allowed provided a clear warning is provided in the post. Linking to
                                            sites that break any of the other rules or linking to sites that contain inappropriate language without providing a warning is
                                            strictly not allowed.
                                    </div>
                                </div>
                            </div>
                        }
                        {displayTerms &&
                            <div className="terms_modal" style={{ zIndex: 5 }}>
                                <div className="terms_header">
                                <div className="terms_title">terms of service</div>
                                <div className="terms_close" onClick={() => setDisplayTerms(false)}>close</div>
                                </div>
                                <div className="terms_content">
                                    Shinobi-chronicles.com is a fan site: We did not create Naruto nor any of the characters and content in Naruto. While inspired by
                                    Naruto, the content of this site is fan-made and not meant to infringe upon any copyrights, it is simply here to further the
                                    continuing popularity of Japanese animation. In no event will shinobi-chronicles.com,
                                    its host, and any other companies and/or sites linked to shinobi-chronicles.com be liable to any party for any direct, indirect,
                                    special or other consequential damages for any use of this website, or on any other hyperlinked website, including, without limitation,
                                    any lost profits, loss of programs or other data on your information handling system or otherwise, even if we are expressly advised
                                    of the possibility of such damages.<br />

                                    <p>
                                    Shinobi-chronicles.com accepts no responsibility for the actions of its members i.e. Self harm, vandalism, suicide, homicide,
                                    genocide, drug abuse, changes in sexual orientation, or bestiality. Shinobi-chronicles.com will not be held responsible and does not
                                    encourage any of the above actions or any other form of anti social behaviour. The staff of shinobi-chronicles.com reserve the
                                    right to issue bans and/or account deletion for rule infractions. Rule infractions will be determined at the discretion of the
                                    moderating staff.</p>

                                    <p>Loans or transactions of real or in-game currency are between players. Staff take no responsibility for the
                                        completion of them. If a player loans real or in-game currency to another player, staff will not be responsible for ensuring the
                                        currency is returned.</p>

                                    <p>Ancient Kunai(Premium credits) that have already been spent on in-game purchases of any kind or traded to another player cannot be
                                        refunded. Staff are not responsible for lost shards or time on Forbidden Seals lost due to user bans.</p>
                                    <br />
                                    The Naruto series is created by and copyright Masashi Kishimoto and TV Tokyo, all rights reserved.
                                </div>
                            </div>
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
                    <svg className="home_diamond_svg" width="100" height="100" role="button" tabIndex="0" onClick={() => scrollTo(newsRef.current)}>
                        <g className={"home_diamond_rotategroup diamond_red"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45" height="45" />
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
            <div className="login_rules_button">
                <div className="home_diamond_container">
                    <svg className="home_diamond_svg" width="100" height="100" role="button" tabIndex="0" style={{ transform: "scale(0.85)" }} onClick={() => handleRules()}>
                        <g className={"home_diamond_rotategroup diamond_blue"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45" height="45" />
                            <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                            <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                            <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                        </g>
                        <text className="home_diamond_shadow_text" x="50" y="52" textAnchor="middle" dominantBaseline="middle">rules</text>
                        <text className="home_diamond_blue_text" x="50" y="50" textAnchor="middle" dominantBaseline="middle">rules</text>
                    </svg>
                </div>
            </div>
            <div className="login_terms_button">
                <div className="home_diamond_container">
                    <svg className="home_diamond_svg" width="100" height="100" role="button" tabIndex="0" style={{ transform: "scale(0.85)" }} onClick={() => handleTerms()}>
                        <g className={"home_diamond_rotategroup diamond_red"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45" height="45" />
                            <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                            <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                            <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                        </g>
                        <text className="home_diamond_shadow_text" x="50" y="40" textAnchor="middle" dominantBaseline="middle">terms of</text>
                        <text className="home_diamond_red_text" x="50" y="38" textAnchor="middle" dominantBaseline="middle">terms of</text>
                        <text className="home_diamond_shadow_text" x="50" y="64" textAnchor="middle" dominantBaseline="middle">service</text>
                        <text className="home_diamond_red_text" x="50" y="62" textAnchor="middle" dominantBaseline="middle">service</text>
                    </svg>
                </div>
            </div>
            {/*<div className="login_features_button">
                <div className="home_diamond_container">
                    <svg width="100" height="100">
                        <g className={"home_diamond_rotategroup diamond_blue"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45px" height="45px" />
                            <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                            <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                            <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                        </g>
                        <text className="home_diamond_shadow_text" x="50" y="40" textAnchor="middle" dominantBaseline="middle">game</text>
                        <text className="home_diamond_blue_text" x="50" y="38" textAnchor="middle" dominantBaseline="middle">game</text>
                        <text className="home_diamond_shadow_text" x="50" y="64" textAnchor="middle" dominantBaseline="middle">features</text>
                        <text className="home_diamond_blue_text" x="50" y="62" textAnchor="middle" dominantBaseline="middle">features</text>
                    </svg>
                </div>
            </div>
            <div className="login_world_button">
                <div className="home_diamond_container">
                    <svg width="100" height="100">
                        <g className={"home_diamond_rotategroup diamond_red"} transform="rotate(45 50 50)">
                            <rect className="home_diamond_rear" x="29" y="29" width="78" height="78" />
                            <rect className="home_diamond_up" x="4" y="4" width="45px" height="45px" />
                            <rect className="home_diamond_right" x="51" y="4" width="45" height="45" />
                            <rect className="home_diamond_left" x="4" y="51" width="45" height="45" />
                            <rect className="home_diamond_down" x="51" y="51" width="45" height="45" />
                        </g>
                        <text className="home_diamond_shadow_text" x="50" y="52" textAnchor="middle" dominantBaseline="middle">world info</text>
                        <text className="home_diamond_red_text" x="50" y="50" textAnchor="middle" dominantBaseline="middle">world info</text>
                        
                    </svg>
                </div>
            </div>*/}
            <div className="login_contact_button">
                <div className="home_diamond_container">
                    <svg className="home_diamond_svg" width="100" height="100" role="button" tabIndex="0" onClick={() => scrollTo(contactRef.current)}>
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

function NewsSection({ newsRef, newsPosts, githubURL, discordURL }) {
    function formatNewsDate(ticks) {
        var date = new Date(ticks * 1000);
        var formattedDate = date.toLocaleDateString('en-US', {
            month: '2-digit',
            day: '2-digit',
            year: '2-digit'
        });
        return formattedDate;
    }

    function NewsItem({ newsItem }) {
        return (
            <div className="news_item">
                <div className="news_item_header">
                    <div className="news_item_title">{newsItem.title.toUpperCase()}</div>
                    <div className="news_item_version"></div>
                    {/* array map tags */}
                    <div className="news_item_details">POSTED {formatNewsDate(newsItem.time)} BY {newsItem.sender.toUpperCase()}</div>
                </div>
                <div className="news_item_banner">

                </div>
                <div className="news_item_content" dangerouslySetInnerHTML={{ __html: newsItem.message }}>
                </div>
            </div>
        );
    }

    return (
        <div ref={newsRef} id="news_container" className={"home_section news_section"}>
            <div className="home_header">
                <label className="home_header_label">NEWS & UPDATES</label>
                <div className="home_external_links">
                    <a href={githubURL} className="home_github_wrapper">
                        <img className="home_github" src="../../../images/v2/icons/githubhover.png"/>
                    </a>
                    <a href={discordURL} className="home_discord_wrapper">
                        <img className="home_discord" src="../../../images/v2/icons/discordhover.png"/>
                    </a>
                </div>
            </div>
            <div className="news_item_container">
                {newsPosts.map((newsItem, index) => (
                    <NewsItem key={index} newsItem={newsItem} />
                ))}
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


window.Home = Home;