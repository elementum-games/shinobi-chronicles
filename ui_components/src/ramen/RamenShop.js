// @flow strict

import { ModalProvider, useModal } from "../utils/modalContext.js";
import { apiFetch } from "../utils/network.js";
import { ResourceBar } from "../utils/resourceBar.js";
import { parseKeywords } from "../utils/parseKeywords.js";

import type {
    CharacterRamenType,
    MysteryRamenType,
    RamenOwnerType,
    BasicRamenType,
    SpecialRamenType
} from "./RamenShopSchema.js";

import type {
    PlayerDataType,
    PlayerResourcesType
} from "../_schema/userSchema.js";

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

type Props = {|
    +ramenShopAPI: string,
    +playerData: PlayerDataType,
    +playerResourcesData: PlayerResourcesType,
    +characterRamenData: CharacterRamenType,
    +ramenOwnerDetails: RamenOwnerType,
    +basicMenuOptions: Array<BasicRamenType>,
    +specialMenuOptions: Array<SpecialRamenType>,
    +mysteryRamenDetails: MysteryRamenType,
|};
function RamenShop({
    ramenShopAPI,
    playerData,
    playerResourcesData,
    characterRamenData,
    ramenOwnerDetails,
    basicMenuOptions,
    specialMenuOptions,
    mysteryRamenDetails,
}: Props) {
    const [playerDataState, setPlayerDataState] = React.useState(playerData);
    const [playerResourcesDataState, setPlayerResourcesDataState] = React.useState(playerResourcesData);
    const [characterRamenDataState, setCharacterRamenDataState] = React.useState(characterRamenData);
    const [mysteryRamenDetailsState, setMysteryRamenDetailsState] = React.useState(mysteryRamenDetails);
    const [ramenOwnerDetailsState, setRamenOwnerDetailsState] = React.useState(ramenOwnerDetails);
    const { openModal } = useModal();
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
            setRamenOwnerDetailsState(response.data.ramen_owner_details);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    function PurchaseSpecialRamen(ramen_key, ramen_label, ramen_cost, ramen_effects, ramen_duration) {
        // Construct the confirmation message with ramen details
        const confirmationMessage = `Are you sure you want to purchase ${ramen_label} Ramen for ¥${ramen_cost}? 
        
        You will receive ${ramen_effects} for ${ramen_duration} minutes.`;
    
        // Open a confirmation modal before purchasing
        openModal({
            header: 'Confirmation',
            text: confirmationMessage,
            ContentComponent: null,
            onConfirm: () => {
                // Proceed with the purchase if confirmed
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
                    setRamenOwnerDetailsState(response.data.ramen_owner_details);
                    openModal({
                        header: 'Confirmation',
                        text: response.data.response_message,
                        ContentComponent: null,
                        onConfirm: null,
                    });
                });
            },
            onCancel: () => {
                // Do nothing if canceled
            },
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
                    <div className="ramen_shop_owner_container" style={{ background: `url(${ramenOwnerDetailsState.background}) center center no-repeat` }}>
                        <div className="ramen_shop_dialogue_container">
                            <div className="ramen_shop_dialogue_nameplate">{ramenOwnerDetailsState.name}</div>
                            <div className="ramen_shop_dialogue_text">{parseKeywords(ramenOwnerDetailsState.dialogue)}</div>
                        </div>
                        <img src={ramenOwnerDetailsState.image} className="ramen_shop_owner_img" />
                    </div>
                </div>
                <div className="column second">
                    <div className="ramen_shop_intro_container box-primary">
                        <div className="header">{ramenOwnerDetailsState.shop_name}</div>
                        <div className="intro_text">{parseKeywords(ramenOwnerDetailsState.shop_description)}</div>
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
                            <div className="ramen_shop_descriptive_text">{ramenOwnerDetailsState.basic_menu_description}</div>
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
                                    <SpecialRamen index={index} ramenInfo={option} PurchaseSpecialRamen={PurchaseSpecialRamen} />
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
function SpecialRamen({ index, ramenInfo, PurchaseSpecialRamen }) {
    const { ramen_key, label, description, cost, effect, duration } = ramenInfo;

    const handlePurchase = () => {
        PurchaseSpecialRamen(ramen_key, label, cost, effect, duration);
    };

    return (
        <div key={index} className="special_ramen">
            <img src={ramenInfo.image} className="special_ramen_img" />
            <div className="special_ramen_name">{label}</div>
            <div className="special_ramen_description">{description}</div>
            <div className="special_ramen_duration">Duration: {duration} minutes</div>
            <div className="special_ramen_effect">{effect}</div>
            <div className="special_ramen_button" onClick={handlePurchase}><>&yen;</>{cost}</div>
        </div>
    );
}

window.RamenShopReactContainer = RamenShopReactContainer;
