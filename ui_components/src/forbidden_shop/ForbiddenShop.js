import { apiFetch } from "../utils/network.js";
import { clickOnEnter } from "../utils/uiHelpers.js"
import type {
    PlayerInventoryType
} from "../_schema/userSchema.js";

type LanternEventData = {|
    +red_lantern_id: number,
    +blue_lantern_id: number,
    +violet_lantern_id: number,
    +gold_lantern_id: number,
    +shadow_essence_id: number,
    +yen_per_lantern: number,
    +red_lanterns_per_blue: number,
    +red_lanterns_per_violet: number,
    +red_lanterns_per_gold: number,
    +red_lanterns_per_shadow: number,
|};

type Props = {|
    +links: {|
        +forbiddenShopAPI: string,
        +userAPI: string,
    |},
    +eventData: {|
        +lanternEvent: LanternEventData
    |},
    +jutsuData: Array,
    +playerInventory: PlayerInventoryType,
|};
function ForbiddenShop({
    links,
    eventData,
    jutsuData,
    playerInventory,
}: Props) {                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
    function exchangeEventCurrency(event_name, currency_name, quantity) {
        apiFetch(links.forbiddenShopAPI, {
            request: 'exchangeEventCurrency',
            event_name: event_name,
            currency_name: currency_name,
            quantity: quantity,
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {

            }
        })
    }

    function handleErrors(errors) {
        console.warn(errors);
    }

    return (
        <div className="forbidden_shop_container">
            <ShopMenu
                ShopMenuButton={ShopMenuButton}
            />
            <ScrollExchange
                playerInventory={playerInventory}
                forbiddenShopAPI={links.forbiddenShopAPI}
                eventData={eventData}
                jutsuData={jutsuData}
                userAPI={links.userAPI}
            />
            <LanternEventCurrencyExchange
                playerInventory={playerInventory}
                forbiddenShopAPI={links.forbiddenShopAPI}
                eventData={eventData.lanternEvent}
                userAPI={links.userAPI}
            />
        </div>
    )
}

function ShopMenu({ ShopMenuButton }) {
    const [activeButtonName, setActiveButtonName] = React.useState(null);
    const [dialogueText, setDialogueText] = React.useState(null);
    function questionOneClick() {
        setDialogueText("A remnant from a past forgotten.\nAn abyss between the realms of yours... and <span class='dialogue_highlight'>ours</span>.");
        setActiveButtonName("questionOne");
    }
    function questionTwoClick() {
        setDialogueText("... <span class='dialogue_highlight'>Akuji</span>. Nevermind the what.\n Now, you have something that\n belongs to me.");
        setActiveButtonName("questionTwo");
    }
    function scrollExchangeJump() {
        setDialogueText("...");
        setActiveButtonName("scrollExchange");
    }
    function currencyExchangeJump() {
        setDialogueText("...");
        setActiveButtonName("currencyExchange");
    }

    return (
        <div className="shop_menu_container">
            <img src="/../images/forbidden_shop/bluelight.png" className="shop_background_light_left" />
            <img src="/../images/forbidden_shop/bluelight.png" className="shop_background_light_right" />
            <img src="/../images/forbidden_shop/frame.png" className="shop_frame_nw" />
            <img src="/../images/forbidden_shop/frame.png" className="shop_frame_ne" />
            <img src="/../images/forbidden_shop/frame.png" className="shop_frame_se" />
            <img src="/../images/forbidden_shop/frame.png" className="shop_frame_sw" />
            <div className="shop_title">
                <img src="/../images/forbidden_shop/ayakashiabysslogo.png" className="shop_title_image" />
            </div>
            <div className="shop_owner_container">
                <div className="shop_owner">
                    <img src="/../images/forbidden_shop/akuji.png" className="shop_owner_image"/>
                </div>
                {dialogueText != null &&
                    <div className="shop_owner_dialogue_container">
                        <div className="shop_owner_nameplate">Akuji</div>
                        <div className="shop_owner_dialogue_text" dangerouslySetInnerHTML={{ __html: dialogueText }}></div>
                    </div>
                }
            </div>
            <div className="shop_menu">
                <ShopMenuButton
                    onClick={questionOneClick}
                    buttonText={"What is this place?"}
                    buttonName={"questionOne"}
                    activeButtonName={activeButtonName}
                    buttonClass={"button_first"}
                />
                <ShopMenuButton
                    onClick={questionTwoClick}
                    buttonText={"Who- What are you?"}
                    buttonName={"questionTwo"}
                    activeButtonName={activeButtonName}
                    buttonClass={"button_second"}
                />
                <ShopMenuButton
                    onClick={scrollExchangeJump}
                    buttonText={"Scroll exchange"}
                    buttonName={"scrollExchange"}
                    activeButtonName={activeButtonName}
                    buttonClass={"button_third"}
                />
                <ShopMenuButton
                    onClick={currencyExchangeJump}
                    buttonText={"Currency exchange"}
                    buttonName={"currencyExchange"}
                    activeButtonName={activeButtonName}
                    buttonClass={"button_fourth"}
                />
            </div>
        </div>
    );
}

function ShopMenuButton({ onClick, buttonText, buttonName, activeButtonName, buttonClass }) {
    return (
        <svg
            role="button"
            tabIndex="0"
            name={buttonName}
            className={"shop_menu_button " + buttonClass}
            width="162"
            height="32"
            onClick={() => onClick()}
            onKeyPress={clickOnEnter}
        >
            <defs>
                <radialGradient id="shop_button_fade" cx="50%" cy="50%" r="95%" fx="50%" fy="50%">
                    <stop offset="0%" style={{ stopColor: 'black' }} />
                    <stop offset="50%" style={{ stopColor: 'rgb(0,0,0,0.3)' }} />
                    <stop offset="90%" style={{ stopColor: 'rgb(0,0,0,0.0)' }} />
                </radialGradient>
            </defs>
            {(activeButtonName == buttonName) &&
                <>
                <rect className="shop_button_back_shadow" width="88%" height="70%" y="30%" x="6%" fill="url(#shop_button_fade)" />
                <rect className="shop_button_center_shadow" width="80%" height="100%" x="10%" y="25%" fill="url(#shop_button_fade)" />
                <rect className="shop_button_back active out" width="92%" height="70%" y="15%" x="4%"/>
                <rect className="shop_button_center active" width="80%" height="100%" x="10%" />
                <text className="shop_button_shadow_text" x="50%" y="18" textAnchor="middle" dominantBaseline="middle">{buttonText}</text>
                <text className="shop_button_text" x="50%" y="16" textAnchor="middle" dominantBaseline="middle" fill="white" style={{textDecoration: "none"}}>{buttonText}</text>
                </>
            }
            {(activeButtonName != buttonName) &&
                <>
                <rect className="shop_button_back_shadow" width="88%" height="70%" y="30%" x="6%" fill="url(#shop_button_fade)" />
                <rect className="shop_button_center_shadow" width="80%" height="100%" x="10%" y="25%" fill="url(#shop_button_fade)"/>
                <rect className="shop_button_back" width="88%" height="70%" y="15%" x="6%"/>
                <rect className="shop_button_center" width="80%" height="100%" x="10%" />
                <text className="shop_button_shadow_text" x="50%" y="18" textAnchor="middle" dominantBaseline="middle">{buttonText}</text>
                <text className="shop_button_text" x="50%" y="16" textAnchor="middle" dominantBaseline="middle" fill="#f0e2c6">{buttonText}</text>
                </>
            }
            
        </svg>
    );
}

type CurrencyExchangeProps = {|
    +playerInventory: PlayerInventoryType,
    +forbiddenShopAPI: string,
    +eventData: LanternEventData,
    +userAPI: string,
|};
function LanternEventCurrencyExchange({
    playerInventory,
    forbiddenShopAPI,
    eventData,
    userAPI
}: CurrencyExchangeProps) {
    const [currenciesToExchange, setCurrenciesToExchange] = React.useState({
        redLantern: 10000,
        blueLantern: 1000,
        violetLantern: 500,
        goldLantern: 50,
        shadowEssence: 2
    });

    return (
        <>
            <h3 className="forbidden_shop_sub_header">Event currency exchange</h3>
            <div className="currency_exchange box-secondary">
                <span className="event_name">Festival of Lanterns event</span>
                <span className="event_date">July 1 - July 15, 2023</span>

                <div className="currencies_to_exchange">
                    <CurrencyExchangeInput
                        name="Red Lantern"
                        quantityToExchange={currenciesToExchange.redLantern}
                        setQuantityToExchange={(newVal) => setCurrenciesToExchange(prevVal => ({
                            ...prevVal,
                            redLantern: newVal
                        }))}
                        yenForEach={eventData.yen_per_lantern}
                    />
                    <CurrencyExchangeInput
                        name="Blue Lantern"
                        quantityToExchange={currenciesToExchange.blueLantern}
                        setQuantityToExchange={(newVal) => setCurrenciesToExchange(prevVal => ({
                            ...prevVal,
                            blueLantern: newVal
                        }))}
                        yenForEach={eventData.yen_per_lantern * eventData.red_lanterns_per_blue}
                    />
                    <CurrencyExchangeInput
                        name="Violet Lantern"
                        quantityToExchange={currenciesToExchange.violetLantern}
                        setQuantityToExchange={(newVal) => setCurrenciesToExchange(prevVal => ({
                            ...prevVal,
                            violetLantern: newVal
                        }))}
                        yenForEach={eventData.yen_per_lantern * eventData.red_lanterns_per_violet}
                    />
                    <CurrencyExchangeInput
                        name="Gold Lantern"
                        quantityToExchange={currenciesToExchange.goldLantern}
                        setQuantityToExchange={(newVal) => setCurrenciesToExchange(prevVal => ({
                            ...prevVal,
                            goldLantern: newVal
                        }))}
                        yenForEach={eventData.yen_per_lantern * eventData.red_lanterns_per_gold}
                    />
                    <CurrencyExchangeInput
                        name="Shadow Essence"
                        quantityToExchange={currenciesToExchange.shadowEssence}
                        setQuantityToExchange={(newVal) => setCurrenciesToExchange(prevVal => ({
                            ...prevVal,
                            shadowEssence: newVal
                        }))}
                        yenForEach={eventData.yen_per_lantern * eventData.red_lanterns_per_shadow}
                    />
                </div>
            </div>
        </>
    );
}

type CurrencyExchangeInputProps = {|
    +name: string,
    +quantityToExchange: number,
    +yenForEach: number,
    +setQuantityToExchange: (number) => void,
|};
function CurrencyExchangeInput({
    name,
    quantityToExchange,
    setQuantityToExchange,
    yenForEach
}) {
    const yenToReceive = quantityToExchange * yenForEach;

    return (
        <div className="currency_to_exchange">
            <span>{name}</span>
            <span>x{quantityToExchange.toLocaleString()}</span>
            <input
                type="number"
                value={quantityToExchange}
                onChange={(e) => setQuantityToExchange(e.target.value)}
            />
            <span>{yenToReceive.toLocaleString()} yen</span>
        </div>
    );
}

type ScrollExchangeProps = {|
    +playerInventory: PlayerInventoryType,
    +forbiddenShopAPI: string,
    +eventData: LanternEventData,
    +jutsuData: array,
|};
function ScrollExchange({ playerInventory, forbiddenShopAPI, eventData, jutsuData }) {
    function exchangeForbiddenJutsuScroll(item_type, item_id) {
        apiFetch(forbiddenShopAPI, {
            request: 'exchangeForbiddenJutsuScroll',
            item_type: item_type,
            item_id: item_id,
        }).then(response => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            else {
                // update remaining # of scrolls for display, get new list of jutsu
            }
        })
    }
    return (
        <div className="scroll_exchange_section">
            <div className="scroll_exchange_header">
                <div className="scroll_exchange_title">Forbidden scroll exchange</div>
                <div className="scroll_count_container">
                    <div className="scroll_count_label">FORBIDDEN SCROLLS</div>
                    <div className="scroll_count"></div>
                </div>
            </div>
            <div className="scroll_exchange_container">
                {jutsuData
                    .map((jutsu_data) => (
                        <JutsuScroll
                            key={jutsu_data.jutsu_id}
                            jutsu_data={jutsu_data}
                            onClick={exchangeForbiddenJutsuScroll}
                        />
                    )
                )}
            </div>
        </div>
    );
}

function JutsuScroll({ jutsu_data, onClick }) {
    return (
        <div className="jutsu_scroll" onClick={() => onClick()}>
            <div className="jutsu_scroll_inner">
                <div className="jutsu_name">{jutsu_data.name}</div>
                <div className="jutsu_type">
                    <div className="jutsu_scroll_divider"><svg width="100%" height="2"><line x1="0%" y1="1" x2="95%" y2="1" stroke="#77694e" strokeWidth="1"></line></svg></div>
                    <div className="jutsu_type_label">forbidden {jutsu_data.jutsu_type}</div>
                    <div className="jutsu_scroll_divider"><svg width="100%" height="2"><line x1="0%" y1="1" x2="95%" y2="1" stroke="#77694e" strokeWidth="1"></line></svg></div>
                </div>
                <div className="jutsu_description">{jutsu_data.description}</div>
                <div className="jutsu_stats_container">
                    <div className="jutsu_power"><span style={{ fontWeight: "700" }}>POWER:</span> {jutsu_data.power}</div>
                    <div className="jutsu_cooldown"><span style={{ fontWeight: "700" }}>COOLDOWN:</span> {jutsu_data.cooldown} TURNS</div>
                    <div className="jutsu_effect"><span style={{ fontWeight: "700" }}>EFFECT:</span> {jutsu_data.effect} ({jutsu_data.effect_amount}%)</div>
                    <div className="jutsu_duration"><span style={{ fontWeight: "700" }}>DURATION:</span> {jutsu_data.effect_duration} TURNS</div>
                </div>
                <div className="jutsu_scroll_divider_bottom"><svg width="100%" height="2"><line x1="0%" y1="1" x2="95%" y2="1" stroke="#77694e" strokeWidth="1"></line></svg></div>
                <div className="jutsu_tags">
                    <div className="jutsu_tag_forbidden">forbidden technique</div>
                    <div className="jutsu_tag_scaling">scales with rank</div>
                </div>
            </div>
        </div>
    )
}

window.ForbiddenShop = ForbiddenShop;