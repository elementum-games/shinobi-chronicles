import { apiFetch } from "../utils/network.js";

// Initialize
function Hotbar({ links, userAPIData }) {
    // Hooks
    const [playerData, setPlayerData] = React.useState(userAPIData.playerData);
    const [aiData, setAIData] = React.useState(userAPIData.aiData);
    const [missionData, setMissionData] = React.useState(userAPIData.missionData);
    const [quickType, setQuickType] = React.useState("training");
    const [displayHotbar, toggleHotbarDisplay] = React.useState(false);
    const [displayKeybinds, toggleKeybindDisplay] = React.useState(false);
    const trainingFlag = React.useRef(0);
    const specialFlag = React.useRef(0);
    const battleFlag = React.useRef(null);
    const quickFormRef = React.useRef(null);
    
    // API
    function getPlayerData() {
        apiFetch(links.user_api, {
            request: 'getPlayerData'
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {
                setPlayerData(response.data.playerData);
                checkNotificationFlags(response.data.playerData.training, response.data.playerData.special, response.data.playerData.battle);
            }
        })
    }

    function quickSelectOnChange(event) {
        setQuickType(event.target.selectedOptions[0].getAttribute('data-state'));
    }

    function trainingSelectOnChange(event) {
        event.target.setAttribute("name", event.target.selectedOptions[0].getAttribute('data-name'));
    }

    function quickSubmitOnClick() {
        quickFormRef.current.submit();
    }

    function setKeybindsOnClick() {
        toggleKeybindDisplay(!displayKeybinds)
    }

    function hotbarToggle() {
        toggleHotbarDisplay(!displayHotbar);
    }

    function checkNotificationFlags(training, special, battle) {
        if (training == '0' && trainingFlag.current != '0') {
            createNotification("Training Complete!");
        }
        trainingFlag.current = training;
        if (special == '0' && specialFlag.current != '0') {
            createNotification("Special Mission Complete!");
        }
        specialFlag.current = special;
        if (battle != '0' && battleFlag.current == '0') {
            createNotification("You are in battle!");
        }
        battleFlag.current = battle;
    }

    function createNotification(message) {
        if (!window.Notification) {
            console.log('Browser does not support notifications.');
        } else {
            // check if permission is already granted
            if (Notification.permission === 'granted') {
                // show notification here
                var notify = new Notification('Shinobi Chronicles', {
                    body: message,
                });
            } else {
                // request permission from user
                Notification.requestPermission().then(function (p) {
                    if (p === 'granted') {
                        // show notification here
                        var notify = new Notification('Shinobi Chronicles', {
                            body: message,
                        });
                    } else {
                        console.log('User blocked notifications.');
                    }
                }).catch(function (err) {
                    console.error(err);
                });
            }
        }
    }

    // Content
    function displayToggle() {
        return (
            <div id="hb_toggle" onClick={hotbarToggle} className={"t-hover ft-s ft-c1 ft-default"}>Toggle Hotbar</div>
        );
    }

    function displayQuickSection(playerData, missionData, aiData, link_data, quickType) {
        return (
            <div id="hb_quick_section" className="hb_section">
                <div className="hb_divider">
                    <div className={"hb_quick_title ft-s ft-c1 ft-min ft-b"}>QUICK MENU</div>
                    <div>
                        {(quickType == "training") &&
                            <div>
                                <form id="hb_quick_form" ref={quickFormRef} action={link_data.training} method="post">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_large t-hover"} type="button" value="TRAINING" />
                                </form>
                            </div>
                        }
                        {(quickType == "arena") &&
                            <div>
                                <form id="hb_quick_form" ref={quickFormRef} action={link_data.arena} method="get">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_large t-hover"} type="button" value="ARENA" />
                                </form>
                            </div>
                        }
                        {(quickType == "missions") &&
                            <div>
                                <form id="hb_quick_form" ref={quickFormRef} action={link_data.mission} method="get">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_large t-hover"} type="button" value="MISSIONS" />
                                </form>
                            </div>
                        }
                        {(quickType == "special_missions") &&
                            <div>
                                <form id="hb_quick_form" ref={quickFormRef} action={link_data.special_missions} method="get">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_large t-hover"} type="button" value="SPECIAL" />
                                </form>
                            </div>
                        }
                        {(quickType == "ramen") &&
                            <div>
                                <form id="hb_quick_form" ref={quickFormRef} action={link_data.healingShop} method="get">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_LARGE t-hover"} type="button" value="RAMEN" />
                                </form>
                            </div>
                        }
                    </div>
                </div>
                <div className="hb_divider">
                    <div>
                        <div>
                            <select id="hb_category_select" onChange={quickSelectOnChange} form="hb_quick_form" name="id" className="hb_quick_select">
                                <option data-state="training" value={link_data.training.slice(link_data.training.indexOf('=') + 1)}>Training</option>
                                <option data-state="arena" value={link_data.arena.slice(link_data.training.indexOf('=') + 1)}>Arena</option>
                                <option data-state="missions" value={link_data.mission.slice(link_data.training.indexOf('=') + 1)}>Missions</option>
                                <option data-state="special_missions" value={link_data.special_missions.slice(link_data.training.indexOf('=') + 1)}>Special Missions</option>
                                <option data-state="ramen" value={link_data.healingShop.slice(link_data.training.indexOf('=') + 1)}>Ramen</option>
                            </select>
                        </div>
                        {(quickType == "training") &&
                            <>
                                <div>
                                    <select onChange={trainingSelectOnChange} form="hb_quick_form" id="hb_training_select" name="skill" className="hb_quick_select">
                                        <optgroup label="Skills">
                                            <option data-name="skill" value="taijutsu">Taijutsu Skill</option>
                                            <option data-name="skill" value="ninjutsu">Ninjutsu Skill</option>
                                            <option data-name="skill" value="genjutsu">Genjutsu Skill</option>
                                            {playerData.has_bloodline == true &&
                                                <option data-name="skill" value="bloodline">Bloodline Skill</option>
                                            }
                                        </optgroup>
                                        <optgroup label="Attributes">
                                            <option data-name="attributes" value="speed">Speed Skill</option>
                                            <option data-name="attributes" value="cast_speed">Cast Speed Skill</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div id="hb_time_container">
                                    <label className={"hb_time_label ft-s ft-c1 ft-min ft-b"}>TIME:</label>
                                    <select form="hb_quick_form" id="hb_time_select" name="train_type" className="hb_quick_select">
                                        <option value="Short">Short</option>
                                        <option value="Long">Long</option>
                                        <option value="Extended">Extended</option>
                                    </select>
                                </div>
                            </>
                        }
                        {(quickType == "arena") &&
                            <div>
                                <select form="hb_quick_form" id="hb_arena_select" name="fight" className="hb_quick_select">
                                    {(aiData) &&
                                        aiData.map(function (ai, i) {
                                            return <option key={i} value={ai.ai_id}>{ai.name}</option>
                                        })
                                    }
                                </select>
                            </div>
                        }
                        {(quickType == "missions") &&
                            <div>
                                <select name="start_mission" form="hb_quick_form" id="hb_missions_select" className="hb_quick_select">
                                    {(missionData) &&
                                        missionData.map(function (mission, i) {
                                            return <option key={i} value={mission.mission_id}>{mission.name}</option>
                                        })
                                    }
                                </select>
                            </div>
                        }
                        {(quickType == "special_missions") &&
                            <div>
                                <select name="start" form="hb_quick_form" id="hb_special_missions_select" className="hb_quick_select">
                                    <option value="easy">Easy</option>
                                    <option value="normal">Normal</option>
                                    <option value="hard">Hard</option>
                                    <option value="nightmare">Nightmare</option>
                                </select>
                            </div>
                        }
                        {(quickType == "ramen") &&
                            <div>
                                <select form="hb_quick_form" id="hb_ramen_select" name="heal" className="hb_quick_select">
                                    <option value="vegetable">Vegetable</option>
                                    <option value="pork">Pork</option>
                                    <option value="deluxe">Deluxe</option>
                                </select>
                            </div>
                        }
                    </div>
                </div>
            </div>
        );
    }

    function displaySettingsSection(playerData) {
        return (
            <div id="hb_settings_section" className="hb_section">
                <div className="hb_divider">
                    <div className={"hb_settings_title ft-s ft-c1 ft-min ft-b"}>SETTINGS (WIP)</div>
                    <input id="hb_settings_display" onClick={setKeybindsOnClick} className={"hb_button button-bar_large t-hover"} type="button" value="SET KEYBINDS" />
                </div>
                <div className="hb_divider">
                    <div className="hb_checkbox_wrapper"><input id="hb_alert_checkbox" type="checkbox" className="hb_checkbox" form="hb_quick_form" name="alert" value="true" /><label className={"ft-s ft-c1 ft-min"}>ENABLE ALERTS</label></div>
                    <div className="hb_checkbox_wrapper"><input id="hb_hotkey_checkbox" type="checkbox" className="hb_checkbox" value="wow" /><label className={"ft-s ft-c1 ft-min"}>ENABLE HOTKEYS</label></div>
                </div>
            </div>
        );
    }

    function displaySetKeybinds() {
        return (
            <div id="hb_keybind_modal" className={displayKeybinds ? "" : "minimize"}>
                <img src="images/v2/decorations/nwbigcorner.png" className="nwbigcorner" />
                <img src="images/v2/decorations/nebigcorner.png" className="nebigcorner" />
                <img src="images/v2/decorations/sebigcorner.png" className="sebigcorner" />
                <img src="images/v2/decorations/swbigcorner.png" className="swbigcorner" />
                <div className={"t-center ft-min ft-s ft-c1 ft-b"}>KEYBINDS</div>
            </div>
        )
    }

    // Misc
    function handleErrors(errors) {
        console.warn(errors);
        //setFeedback([errors, 'info']);
    }

    // Initialize
    React.useEffect(() => {

    }, []);

    // Display
    return (
        <div id="hotbar" className={displayHotbar ? "jc-center d-flex" : "jc-center d-flex minimize"}>
            <div className="hb_inner">
                <div className={"hb_section_spacer"}></div>
                {displayToggle()}
                {playerData && displayQuickSection(playerData, missionData, aiData, links, quickType)}
                {playerData && displaySettingsSection(playerData)}
                {displaySetKeybinds()}
            </div>
        </div>
    );
}

window.Hotbar = Hotbar;