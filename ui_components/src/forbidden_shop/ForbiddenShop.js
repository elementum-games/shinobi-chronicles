import { apiFetch } from "../utils/network.js";
import { clickOnEnter } from "../utils/uiHelpers.js"
import type {
    PlayerInventoryType
} from "../_schema/userSchema.js";

type Props = {|
    +links: {|
        +forbiddenShopAPI: string,
        +userAPI: string,
    |},
    +eventData: Array,
    +playerInventory: PlayerInventoryType,
|};
function ForbiddenShop({
    links,
    eventData,
    playerInventory,
}: Props) {
    /* Hooks
    const [userMenu, setUserMenu] = React.useState(navigationAPIData.userMenu);
    const [activityMenu, setActivityMenu] = React.useState(navigationAPIData.activityMenu);
    const [villageMenu, setVillageMenu] = React.useState(navigationAPIData.villageMenu);
    const [staffMenu, setStaffMenu] = React.useState(navigationAPIData.staffMenu);
    const [playerData, setPlayerData] = React.useState(userAPIData.playerData);
    const [playerResources, setPlayerResources] = React.useState(userAPIData.playerResources);
    const [playerSettings, setPlayerSettings] = React.useState(userAPIData.playerSettings);
    const [regenTime, setRegenTime] = React.useState(userAPIData.playerResources.regen_time);
    const [regenOffset, setRegenOffset] = React.useState(calculateRegenOffset(userAPIData.playerResources.regen_time));
    const regenTimeVar = React.useRef(userAPIData.playerResources.regen_time);
    const queryParameters = new URLSearchParams(window.location.search);*/
    //const pageID = React.useRef(queryParameters.get("id"));*/                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           

    function echangeEventCurrency(event_name, currency_name, quantity) {
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
    function exchangeForbiddenJutsuScroll(item_type, item_id) {
        apiFetch(links.forbiddenShopAPI, {
            request: 'exchangeForbiddenJutsuScroll',
            item_type: item_type,
            item_id: item_id,
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
                userAPI={links.userAPI}
            />
            <CurrencyExchange
                playerInventory={playerInventory}
                forbiddenShopAPI={links.forbiddenShopAPI}
                eventData={eventData}
                userAPI={links.userAPI}
            />
        </div>
    )
}

function ShopMenu({ ShopMenuButton }) {
    const [activeButtonName, setActiveButtonName] = React.useState(null);
    const dialogueText = React.useRef("...");
    function questionOneClick() {
        dialogueText.current = "... Akuji. Nevermind the what.\n Now, you have something that\n belongs to me."
        setActiveButtonName("questionOne");
    }
    function questionTwoClick() {
        dialogueText.current = "...";
        setActiveButtonName("questionTwo");
    }
    function scrollExchangeJump() {
        dialogueText.current = "...";
        setActiveButtonName("scrollExchange");
    }
    function currencyExchangeJump() {
        dialogueText.current = "...";
        setActiveButtonName("currencyExchange");
    }

    return (
        <div className="shop_menu_container">
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
                <div className="shop_owner_dialogue_container">
                    <div className="shop_owner_dialogue_text">
                    </div>
                </div>
            </div>
            <div className="shop_menu">
                <ShopMenuButton
                    onCLick={questionOneClick}
                    buttonText={"What is this place?"}
                    buttonName={"questionOne"}
                    activeButtonName={activeButtonName}
                />
                <ShopMenuButton
                    onCLick={questionTwoClick}
                    buttonText={"Who- What are you?"}
                    buttonName={"questionTwo"}
                    activeButtonName={activeButtonName}
                />
                <ShopMenuButton
                    onCLick={scrollExchangeJump}
                    buttonText={"Scroll exchange"}
                    buttonName={"scrollExchange"}
                    activeButtonName={activeButtonName}
                />
                <ShopMenuButton
                    onCLick={currencyExchangeJump}
                    buttonText={"Currency exchange"}
                    buttonName={"currencyExchange"}
                    activeButtonName={activeButtonName}
                />
            </div>
        </div>
    );
}

function ShopMenuButton({ onCLick, buttonText, buttonName, activeButtonName }) {
    return (
        <svg
            role="button"
            tabIndex="0"
            name={buttonName}
            className="shop_menu_button"
            width="162"
            height="32"
            onClick={() => onCLick()}
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
    +eventData: array,
    +userAPI: string,
|};
function CurrencyExchange({ playerInventory, forbiddenShopAPI, eventData, userAPI }) {
    return (
        <>
        </>
    );
}

type ScrollExchangeProps = {|
    +playerInventory: PlayerInventoryType,
    +forbiddenShopAPI: string,
    +eventData:array,
    +userAPI: string,
|};
function ScrollExchange({ playerInventory, forbiddenShopAPI, eventData, userAPI }) {
    return (
        <>
        </>
    );
}

window.ForbiddenShop = ForbiddenShop;