import { ModalProvider, useModal } from "../utils/modalContext.js";
import { apiFetch } from "../utils/network.js";

function RamenShopReactContainer({
    playerMoney,
    playerHealth,
    ramenOwnerDetails,
    mysteryRamenDetails,
    basicMenuOptions,
    specialMenuOptions,
}) {
    return (
        <ModalProvider>
            <RamenShop
                playerMoney={playerMoney}
                playerHealth={playerHealth}
                ramenOwnerDetails={ramenOwnerDetails}
                mysteryRamenDetails={mysteryRamenDetails}
                basicMenuOptions={basicMenuOptions}
                specialMenuOptions={specialMenuOptions}
            />
        </ModalProvider>
    );
}

function RamenShop({
    playerMoney,
    playerHealth,
    ramenOwnerDetails,
    mysteryRamenDetails,
    basicMenuOptions,
    specialMenuOptions,
}) {
    const { openModal } = useModal();
    function BasicRamen({ index, ramenInfo }) {
        return (
            <div key={index} className="basic_ramen">
                <img src={ramenInfo.image} className="basic_ramen_img" />
                <div className="basic_ramen_details">
                    <div className="basic_ramen_name">{ramenInfo.name}</div>
                    <div className="basic_ramen_effect">{ramenInfo.effect}</div>
                    <div className="basic_ramen_button">{ramenInfo.cost}</div>
                </div>
            </div>
        );
    }
    function SpecialRamen({ index, ramenInfo }) {
        return (
            <div key={index} className="special_ramen">
                <img src={ramenInfo.image} className="special_ramen_img" />
                <div className="special_ramen_name">{ramenInfo.name}</div>
                <div className="special_ramen_description">{ramenInfo.description}</div>
                <div className="special_ramen_effect">{ramenInfo.effect}</div>
                <div className="special_ramen_duration">{ramenInfo.duration}</div>
                <div className="special_ramen_button">{ramenInfo.cost}</div>
            </div>
        );
    }
    return (
        <div className="ramen_shop_container">
            <div className="row first">
                <div className="column first">
                    <div className="ramen_shop_owner_container" style={{ background: `url(${ramenOwnerDetails.background})` }}>
                        <div className="ramen_shop_dialogue_container">
                            <div className="ramen_shop_dialogue_nameplate">{ramenOwnerDetails.name}</div>
                            <div className="ramen_shop_dialogue_text">{ramenOwnerDetails.dialogue}</div>
                        </div>
                        <img src={ramenOwnerDetails.image} className="ramen_shop_owner_img" />
                    </div>
                </div>
                <div className="column second">
                    <div className="player_info_container">
                        <div className="player_health_container">
                        </div>
                        <div className="player_money_container">
                            <div className="player_money_label">money:</div>
                            <div className="player_money_text">{playerMoney}</div>
                        </div>
                    </div>
                    <div className="ramen_shop_intro_container">
                        <div className="header">{ramenOwnerDetails.shop_name}</div>
                        <div className="intro_text">{ramenOwnerDetails.shop_description}</div>
                    </div>
                    <div className="header">basic menu</div>
                    {basicMenuOptions &&
                        <div className="ramen_shop_basic_menu_container">
                            <div className="ramen_shop_descriptive_text"></div>
                            <div className="ramen_shop_basic_menu">
                                {basicMenuOptions.map((option, index) => {
                                    return (
                                        <BasicRamen index={index} ramenInfo={option} />
                                    );
                                })}
                            </div>
                        </div>
                    }
                    {mysteryRamenDetails.mystery_ramen_enabled &&
                        <div className="ramen_shop_mystery_ramen_container">
                        </div>
                    }
                </div>
            </div>
            {specialMenuOptions && 
                <div className="row second">
                    <div className="column first">
                        <div className="header">special menu</div>
                        <div className="special_menu_container">
                            {specialMenuOptions.map((option, index) => {
                                return (
                                    <SpecialRamen index={index} ramenInfo={option} />
                                );
                            })}
                        </div>
                    </div>
                </div>
            }
        </div>
    );
}

window.RamenShopReactContainer = RamenShopReactContainer;