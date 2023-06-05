import { apiFetch } from "../utils/network.js";

// Initialize
function Hotbar({ linkData }) {
    // Hooks
    const [player_data, setPlayerData] = React.useState(null);
    const [ai_data, setAIData] = React.useState(null);
    const [mission_data, setMissionData] = React.useState(null);
    //const [regen_time, setRegenTime] = React.useState(null);
    //const [regen_offset, setRegenOffset] = React.useState(null);
    const [quick_type, setQuickType] = React.useState("training");
    const [display_hotbar, toggleHotbarDisplay] = React.useState(false);
    const [display_keybinds, toggleKeybindDisplay] = React.useState(false);
    //const regen_time_var = React.useRef(0);
    const training_flag = React.useRef(0);
    const special_flag = React.useRef(0);
    const battle_flag = React.useRef(null);
    const quick_form_ref = React.useRef(null);
    
    // API
    function getPlayerData() {
        apiFetch(linkData.user_api, {
            request: 'getPlayerData'
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {
                setPlayerData(response.data.playerData);
                //setRegenTime(response.data.playerData.regen_time);
                //setRegenOffset(calculateRegenOffset(response.data.playerData.regen_time));
                //regen_time_var.current = response.data.playerData.regen_time;
                checkNotificationFlags(response.data.playerData.training, response.data.playerData.special, response.data.playerData.battle);
            }
        })
    }

    function getAIData() {
        apiFetch(linkData.user_api, {
            request: 'getAIData'
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {
                setAIData(response.data.aiData);
            }
        })
    }

    function getMissionData() {
        apiFetch(linkData.user_api, {
            request: 'getMissionData'
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {
                setMissionData(response.data.missionData);
            }
        })
    }

    // Utility
    /*function handleRegen() {
        if (regen_time_var.current <= 0 || regen_time_var.current == 30) {
            getPlayerData();
        }
        else {
            regen_time_var.current = regen_time_var.current - 1;
            setRegenTime(regen_time => regen_time - 1);
            setRegenOffset(calculateRegenOffset(regen_time_var.current));
        }
    }

    function calculateRegenOffset(time) {
        var percent = ((time / 60) * 100).toFixed(0);
        var offset = 126 - (126 * percent) / 100;
        return offset;
    }*/

    function quickSelectOnChange(event) {
        setQuickType(event.target.selectedOptions[0].getAttribute('data-state'));
    }

    function trainingSelectOnChange(event) {
        event.target.setAttribute("name", event.target.selectedOptions[0].getAttribute('data-name'));
    }

    function quickSubmitOnClick() {
        quick_form_ref.current.submit();
    }

    function setKeybindsOnClick() {
        toggleKeybindDisplay(!display_keybinds)
    }

    function hotbarToggle() {
        toggleHotbarDisplay(!display_hotbar);
    }

    function checkNotificationFlags(training, special, battle) {
        if (training == '0' && training_flag.current != '0') {
            createNotification("Training Complete!");
        }
        training_flag.current = training;
        if (special == '0' && special_flag.current != '0') {
            createNotification("Special Mission Complete!");
        }
        special_flag.current = special;
        if (battle != '0' && battle_flag.current == '0') {
            createNotification("You are in battle!");
        }
        battle_flag.current = battle;
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

    /*function displayCharacterSection(player_data, regen_time, regen_offset) {
        const health_width = Math.round((player_data.health / player_data.max_health) * 100);
        const chakra_width = Math.round((player_data.chakra / player_data.max_chakra) * 100);
        const stamina_width = Math.round((player_data.stamina / player_data.max_stamina) * 100);

        return (
            <div id="hb_character_section" className="hb_section">
                <div id="hb_character_container" className="d-flex">
                    {<div className={display_hotbar ? "hb_avatar_container d-in_block" : "hb_avatar_container d-in_block minimize"}>
                        <div className="hb_avatar_wrapper">
                            <img className="hb_avatar_img" src={player_data.avatar_link} />
                        </div>
                    </div>}
                    <div className={"hb_resources d-in_block"}>
                        <div className={"hb_name_container t-left d-flex"}>
                            <div className="d-in_block">
                                <div className={"ft-p ft-c1 ft-xlarge ft-b"}>{player_data.user_name}</div>
                                <div className={"ft-s ft-c1 ft-default"}>{player_data.rank_name} lvl {player_data.level}</div>
                            </div>
                            <div style={{ width: "100%" }} className="d-in_block">
                                <div id="hb_regentimer">
                                    <svg height="40" width="40" viewBox="0 0 50 50">
                                        <circle id="hb_regentimer_circle" stroke="#7C88C3" cx="24.5" cy="24" r="20" strokeWidth="4" stroke-mitterlimit="0" fill="none" strokeDasharray="126" strokeDashoffset={regen_offset} transform="rotate(-90, 24.5, 24)"></circle>
                                        <text id="hb_regentimer_text" className={"ft-s ft-b ft-large"} x="50%" y="50%" textAnchor="middle" dominantBaseline="middle">{regen_time}</text>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {/* Health Bar }
                        <div className="hb_resourceContainer">
                            <div id="hb_health" className="hb_resourceBarOuter">
                                <img className="hb_resource_corner_left" src="images/v2/decorations/barrightcorner.png" />
                                <label className="hb_innerResourceBarLabel">
                                    {player_data.health} / {player_data.max_health}
                                </label>
                                <div className={"hb_health hb_fill"} style={{ width: health_width + "%" }}>
                                    <svg className="hb_resource_highlight_container">
                                        <svg className="hb_resource_highlight_wrapper" viewBox="0 0 50 50">
                                            <polygon x="50" points="20,25 0,5 5,5 25,25 5,45 0,45" id="hb_health_highlight" className="hb_resource_highlight" />
                                        </svg>
                                    </svg>
                                </div>
                                <div className={"hb_health hb_preview"}></div>
                                <img className={"hb_resource_corner_right"} src="images/v2/decorations/barrightcorner.png" />
                            </div>
                        </div>

                        {/* Chakra Bar }
                        <div className="hb_resourceContainer">
                            <div id="hb_chakra" className="hb_resourceBarOuter">
                                <img className="hb_resource_corner_left" src="images/v2/decorations/barrightcorner.png" />
                                <label className="hb_innerResourceBarLabel">
                                    {player_data.chakra} / {player_data.max_chakra}
                                </label>
                                <div className={"hb_chakra hb_fill"} style={{ width: chakra_width + "%" }}>
                                    <svg className="hb_resource_highlight_container">
                                        <svg className="hb_resource_highlight_wrapper" viewBox="0 0 50 50">
                                            <polygon x="50" points="20,25 0,5 5,5 25,25 5,45 0,45" id="hb_chakra_highlight" className="hb_resource_highlight" />
                                        </svg>
                                    </svg>
                                </div>
                                <div className={"hb_chakra hb_preview"}></div>
                                <img className="hb_resource_corner_right" src="images/v2/decorations/barrightcorner.png" />
                            </div>
                        </div>

                        {/* Stamina Bar }
                        <div className="hb_resourceContainer">
                            <div id="hb_stamina" className="hb_resourceBarOuter">
                                <img className="hb_resource_corner_left" src="images/v2/decorations/barrightcorner.png" />
                                <label className="hb_innerResourceBarLabel">
                                    {player_data.stamina} / {player_data.max_stamina}
                                </label>
                                <div className={"hb_stamina hb_fill"} style={{ width: stamina_width + "%" }}>
                                    <svg className="hb_resource_highlight_container">
                                        <svg className="hb_resource_highlight_wrapper" viewBox="0 0 50 50">
                                            <polygon x="50" points="20,25 0,5 5,5 25,25 5,45 0,45" id="hb_stamina_highlight" className="hb_resource_highlight" />
                                        </svg>
                                    </svg>
                                </div>
                                <div className={"hb_stamina hb_preview"}></div>
                                <img className="hb_resource_corner_right" src="images/v2/decorations/barrightcorner.png" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }*/

    function displayQuickSection(player_data, mission_data, ai_data, link_data, quick_type) {
        return (
            <div id="hb_quick_section" className="hb_section">
                <div className="hb_divider d-in_block">
                    <div className={"hb_quick_title ft-s ft-c1 ft-min ft-b"}>QUICK MENU</div>
                    <div>
                        {(quick_type == "training") &&
                            <div>
                                <form id="hb_quick_form" ref={quick_form_ref} action={link_data.training} method="post">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_large t-hover"} type="button" value="TRAINING" />
                                </form>
                            </div>
                        }
                        {(quick_type == "arena") &&
                            <div>
                                <form id="hb_quick_form" ref={quick_form_ref} action={link_data.arena} method="get">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_large t-hover"} type="button" value="ARENA" />
                                </form>
                            </div>
                        }
                        {(quick_type == "missions") &&
                            <div>
                                <form id="hb_quick_form" ref={quick_form_ref} action={link_data.mission} method="get">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_large t-hover"} type="button" value="MISSIONS" />
                                </form>
                            </div>
                        }
                        {(quick_type == "specialmissions") &&
                            <div>
                                <form id="hb_quick_form" ref={quick_form_ref} action={link_data.specialmissions} method="get">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_large t-hover"} type="button" value="SPECIAL" />
                                </form>
                            </div>
                        }
                        {(quick_type == "ramen") &&
                            <div>
                                <form id="hb_quick_form" ref={quick_form_ref} action={link_data.healingShop} method="get">
                                    <input id="hb_quick_submit" onClick={quickSubmitOnClick} form="hb_quick_form" className={"hb_button button-bar_LARGE t-hover"} type="button" value="RAMEN" />
                                </form>
                            </div>
                        }
                    </div>
                </div>
                <div className={"hb_divider d-in_block"}>
                    <div>
                        <div>
                            <select id="hb_category_select" onChange={quickSelectOnChange} form="hb_quick_form" name="id" className="hb_quick_select">
                                <option data-state="training" value={link_data.training.slice(link_data.training.indexOf('=') + 1)}>Training</option>
                                <option data-state="arena" value={link_data.arena.slice(link_data.training.indexOf('=') + 1)}>Arena</option>
                                <option data-state="missions" value={link_data.mission.slice(link_data.training.indexOf('=') + 1)}>Missions</option>
                                <option data-state="specialmissions" value={link_data.specialmissions.slice(link_data.training.indexOf('=') + 1)}>Special Missions</option>
                                <option data-state="ramen" value={link_data.healingShop.slice(link_data.training.indexOf('=') + 1)}>Ramen</option>
                            </select>
                        </div>
                        {(quick_type == "training") &&
                            <>
                                <div>
                                    <select onChange={trainingSelectOnChange} form="hb_quick_form" id="hb_training_select" name="skill" className="hb_quick_select">
                                        <optgroup label="Skills">
                                            <option data-name="skill" value="taijutsu">Taijutsu Skill</option>
                                            <option data-name="skill" value="ninjutsu">Ninjutsu Skill</option>
                                            <option data-name="skill" value="genjutsu">Genjutsu Skill</option>
                                            {player_data.has_bloodline == true &&
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
                        {(quick_type == "arena") &&
                            <div>
                                <select form="hb_quick_form" id="hb_arena_select" name="fight" className="hb_quick_select">
                                    {(ai_data) &&
                                        ai_data.map(function (ai, i) {
                                            return <option key={i} value={ai.ai_id}>{ai.name}</option>
                                        })
                                    }
                                </select>
                            </div>
                        }
                        {(quick_type == "missions") &&
                            <div>
                                <select name="start_mission" form="hb_quick_form" id="hb_missions_select" className="hb_quick_select">
                                    {(mission_data) &&
                                        mission_data.map(function (mission, i) {
                                            return <option key={i} value={mission.mission_id}>{mission.name}</option>
                                        })
                                    }
                                </select>
                            </div>
                        }
                        {(quick_type == "specialmissions") &&
                            <div>
                                <select name="start" form="hb_quick_form" id="hb_specialmissions_select" className="hb_quick_select">
                                    <option value="easy">Easy</option>
                                    <option value="normal">Normal</option>
                                    <option value="hard">Hard</option>
                                    <option value="nightmare">Nightmare</option>
                                </select>
                            </div>
                        }
                        {(quick_type == "ramen") &&
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

    function displaySettingsSection(player_data) {
        return (
            <div id="hb_settings_section" className="hb_section">
                <div className="d-in_block hb_divider">
                    <div className={"hb_settings_title ft-s ft-c1 ft-min ft-b"}>SETTINGS (WIP)</div>
                    <input id="hb_settings_display" onClick={setKeybindsOnClick} className={"hb_button button-bar_large t-hover"} type="button" value="SET KEYBINDS" />
                </div>
                <div className="d-in_block hb_divider">
                    <div className="hb_checkbox_wrapper"><input id="hb_alert_checkbox" type="checkbox" className="hb_checkbox" form="hb_quick_form" name="alert" value="true" /><label className={"ft-s ft-c1 ft-min"}>ENABLE ALERTS</label></div>
                    <div className="hb_checkbox_wrapper"><input id="hb_hotkey_checkbox" type="checkbox" className="hb_checkbox" value="wow" /><label className={"ft-s ft-c1 ft-min"}>ENABLE HOTKEYS</label></div>
                </div>
            </div>
        );
    }

    function displaySetKeybinds() {
        return (
            <div id="hb_keybind_modal" className={display_keybinds ? "" : "minimize"}>
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
        getPlayerData();
        getMissionData();
        getAIData();

        /*const regenInterval = setInterval(() => {
            handleRegen();
        }, 1000);

        return () => clearInterval(regenInterval);*/
    }, []);

    // Display
    return (
        <div id="hotbar" className={display_hotbar ? "jc-center d-flex" : "jc-center d-flex minimize"}>
            {displayToggle()}
            {player_data && displayQuickSection(player_data, mission_data, ai_data, linkData, quick_type)}
            {player_data && displaySettingsSection(player_data)}
            {displaySetKeybinds()}
        </div>
    );
}

window.Hotbar = Hotbar;