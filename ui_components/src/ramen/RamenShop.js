import { ModalProvider, useModal } from "../utils/modalContext.js";
import { apiFetch } from "../utils/network.js";
import { ResourceBar } from "../utils/resourceBar.js";
import { parseKeywords } from "../utils/parseKeywords.js";

function RamenShopReactContainer({
    ramenShopAPI,
    playerData,
    playerResourcesData,
    characterRamenData,
    ramenOwnerDetails,
    basicRamenOptions,
    specialRamenOptions,
    mysteryRamenDetails
}) {
    return (
        <ModalProvider>
            <RamenShop
                ramenShopAPI={ramenShopAPI}
                playerData={playerData}
                playerResourcesData={playerResourcesData}
                characterRamenData={characterRamenData}
                ramenOwnerDetails={ramenOwnerDetails}
                basicMenuOptions={basicRamenOptions}
                specialMenuOptions={specialRamenOptions}
                mysteryRamenDetails={mysteryRamenDetails}
            />
        </ModalProvider>
    );
}

function RamenShop({
    ramenShopAPI,
    playerData,
    playerResourcesData,
    characterRamenData,
    ramenOwnerDetails,
    basicMenuOptions,
    specialMenuOptions,
    mysteryRamenDetails,
}) {
    const [playerDataState, setPlayerDataState] = React.useState(playerData);
    const [playerResourcesDataState, setPlayerResourcesDataState] = React.useState(playerResourcesData);
    const [characterRamenDataState, setCharacterRamenDataState] = React.useState(characterRamenData);
    const [mysteryRamenDetailsState, setMysteryRamenDetailsState] = React.useState(mysteryRamenDetails);
    const { openModal } = useModal();
    function BasicRamen({ index, ramenInfo, PurchaseBasicRamen }) {
        return (
            <div key={index} className="basic_ramen">
                <img src={ramenInfo.image} className="basic_ramen_img" />
                <div className="basic_ramen_details">
                    <div className="basic_ramen_name">{ramenInfo.label}</div>
                    <div className="basic_ramen_effect">{ramenInfo.health_amount} HP</div>
                    <div className="basic_ramen_button" onClick={() => PurchaseBasicRamen(ramenInfo.ramen_key)}><>&yen;</>{ramenInfo.cost}</div>
                </div>
            </div>
        );
    }
    function SpecialRamen({ index, ramenInfo }) {
        return (
            <div key={index} className="special_ramen">
                <img src={ramenInfo.image} className="special_ramen_img" />
                <div className="special_ramen_name">{ramenInfo.label}</div>
                <div className="special_ramen_description">{ramenInfo.description}</div>
                <div className="special_ramen_effect">{ramenInfo.effect}</div>
                <div className="special_ramen_duration">Duration: {ramenInfo.duration} minutes</div>
                <div className="special_ramen_button" onClick={() => PurchaseSpecialRamen(ramenInfo.ramen_key)}><>&yen;</>{ramenInfo.cost}</div>
            </div>
        );
    }
    function PurchaseBasicRamen(ramen_key) {
        apiFetch(
            ramenShopAPI,
            {
                request: 'PurchaseBasicRamen',
                ramen_key: ramen_key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setPlayerDataState(response.data.player_data);
            setPlayerResourcesDataState(response.data.player_resources);
        });
    }
    function PurchaseSpecialRamen(ramen_key) {
        apiFetch(
            ramenShopAPI,
            {
                request: 'PurchaseSpecialRamen',
                ramen_key: ramen_key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setPlayerDataState(response.data.player_data);
            setMysteryRamenDetailsState(response.data.mystery_ramen_details);
            setCharacterRamenDataState(response.data.character_ramen_data);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    function PurchaseMysteryRamen() {
        apiFetch(
            ramenShopAPI,
            {
                request: 'PurchaseMysteryRamen'
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setPlayerDataState(response.data.player_data);
            setMysteryRamenDetailsState(response.data.mystery_ramen_details);
            setCharacterRamenDataState(response.data.character_ramen_data);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    function handleErrors(errors) {
        openModal({
            header: 'Error',
            text: errors,
            ContentComponent: null,
            onConfirm: null,
        });
    }
    return (
        <div className="ramen_shop_container">
            <div className="row first">
                <div className="column first">
                    <div className="ramen_shop_owner_container" style={{ background: `url(${ramenOwnerDetails.background}) center center no-repeat` }}>
                        <div className="ramen_shop_dialogue_container">
                            <div className="ramen_shop_dialogue_nameplate">{ramenOwnerDetails.name}</div>
                            <div className="ramen_shop_dialogue_text">{parseKeywords(ramenOwnerDetails.dialogue)}</div>
                        </div>
                        <img src={ramenOwnerDetails.image} className="ramen_shop_owner_img" />
                    </div>
                </div>
                <div className="column second">
                    <div className="ramen_shop_intro_container box-primary">
                        <div className="header">{ramenOwnerDetails.shop_name}</div>
                        <div className="intro_text">{parseKeywords(ramenOwnerDetails.shop_description)}</div>
                    </div>
                    <div className="basic_menu_header_row">
                        <div className="header">Basic menu</div>
                        <div className="player_health_container">
                            <ResourceBar current_amount={playerResourcesDataState.health} max_amount={playerResourcesDataState.max_health} resource_type={"health"} />
                        </div>
                        <div className="player_money_container">
                            <div className="player_money_label">Money:</div>
                            <div className="player_money_text"><>&yen;</>{playerDataState.money.toLocaleString()}</div>
                        </div>
                    </div>
                    {basicMenuOptions &&
                        <div className="ramen_shop_basic_menu_container box-primary">
                            <div className="ramen_shop_descriptive_text">Light savory broth with eggs and noodles, perfect with sake.</div>
                            <div className="ramen_shop_basic_menu">
                                {basicMenuOptions.map((option, index) => {
                                    return (
                                        <BasicRamen index={index} ramenInfo={option} PurchaseBasicRamen={PurchaseBasicRamen} />
                                    );
                                })}
                            </div>
                        </div>
                    }
                    <div className="header">Mystery ramen</div>
                    <div className="ramen_shop_mystery_ramen_container box-primary">
                        {mysteryRamenDetailsState.mystery_ramen_unlocked ?
                            <>
                                {characterRamenDataState.mystery_ramen_available
                                    ?
                                    <>
                                        <img src={mysteryRamenDetailsState.image} className="special_ramen_img" />
                                        <div className="mystery_ramen_details_container">
                                            <div className="mystery_ramen_description">Made using leftover ingredients. You can't quite tell what's in it.</div>
                                            <div className="mystery_ramen_effects">
                                                {mysteryRamenDetailsState.effects.map((effect, index) => {
                                                    return (
                                                        <div key={index} className="mystery_ramen_effect">{effect.effect}</div>
                                                    );
                                                })}
                                            </div>
                                            <div className="mystery_ramen_duration">Duration: {mysteryRamenDetailsState.duration} minutes</div>
                                            <div className="mystery_ramen_button" onClick={() => PurchaseMysteryRamen()}><>&yen;</>{mysteryRamenDetailsState.cost}</div>
                                        </div>
                                    </>
                                    :
                                    <div className="mystery_ramen_locked">Check back soon!</div>
                                }
                            </>
                            :
                            <div className="mystery_ramen_locked">Mystery Ramen not yet unlocked.</div>
                        }
                    </div>
                </div>
            </div>
            <div className="row second">
                <div className="column first">
                    <div className="header">Special menu</div>
                    <div className="special_menu_container box-primary">
                        {specialMenuOptions.length > 0
                            ?
                            specialMenuOptions.map((option, index) => {
                                return (
                                    <SpecialRamen index={index} ramenInfo={option} />
                                );
                            })
                            :
                            <div className="special_menu_locked">Special ramen not yet unlocked.</div>
                        }
                    </div>
                </div>
            </div>
        </div>
    );
}

window.RamenShopReactContainer = RamenShopReactContainer;