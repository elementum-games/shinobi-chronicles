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
  return /*#__PURE__*/React.createElement(ModalProvider, null, /*#__PURE__*/React.createElement(RamenShop, {
    ramenShopAPI: ramenShopAPI,
    playerData: playerData,
    playerResourcesData: playerResourcesData,
    characterRamenData: characterRamenData,
    ramenOwnerDetails: ramenOwnerDetails,
    basicMenuOptions: basicRamenOptions,
    specialMenuOptions: specialRamenOptions,
    mysteryRamenDetails: mysteryRamenDetails
  }));
}
function RamenShop({
  ramenShopAPI,
  playerData,
  playerResourcesData,
  characterRamenData,
  ramenOwnerDetails,
  basicMenuOptions,
  specialMenuOptions,
  mysteryRamenDetails
}) {
  const [playerDataState, setPlayerDataState] = React.useState(playerData);
  const [playerResourcesDataState, setPlayerResourcesDataState] = React.useState(playerResourcesData);
  const [characterRamenDataState, setCharacterRamenDataState] = React.useState(characterRamenData);
  const [mysteryRamenDetailsState, setMysteryRamenDetailsState] = React.useState(mysteryRamenDetails);
  const {
    openModal
  } = useModal();
  function PurchaseBasicRamen(ramen_key) {
    apiFetch(ramenShopAPI, {
      request: 'PurchaseBasicRamen',
      ramen_key: ramen_key
    }).then(response => {
      if (response.errors.length) {
        handleErrors(response.errors);
        return;
      }
      setPlayerDataState(response.data.player_data);
      setPlayerResourcesDataState(response.data.player_resources);
    });
  }
  function PurchaseSpecialRamen(ramen_key) {
    apiFetch(ramenShopAPI, {
      request: 'PurchaseSpecialRamen',
      ramen_key: ramen_key
    }).then(response => {
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
        onConfirm: null
      });
    });
  }
  function PurchaseMysteryRamen() {
    apiFetch(ramenShopAPI, {
      request: 'PurchaseMysteryRamen'
    }).then(response => {
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
        onConfirm: null
      });
    });
  }
  function handleErrors(errors) {
    openModal({
      header: 'Error',
      text: errors,
      ContentComponent: null,
      onConfirm: null
    });
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_owner_container",
    style: {
      background: `url(${ramenOwnerDetails.background}) center center no-repeat`
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_dialogue_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_dialogue_nameplate"
  }, ramenOwnerDetails.name), /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_dialogue_text"
  }, parseKeywords(ramenOwnerDetails.dialogue))), /*#__PURE__*/React.createElement("img", {
    src: ramenOwnerDetails.image,
    className: "ramen_shop_owner_img"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "column second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_intro_container box-primary"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, ramenOwnerDetails.shop_name), /*#__PURE__*/React.createElement("div", {
    className: "intro_text"
  }, parseKeywords(ramenOwnerDetails.shop_description))), /*#__PURE__*/React.createElement("div", {
    className: "basic_menu_header_row"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Basic menu"), /*#__PURE__*/React.createElement("div", {
    className: "player_health_container"
  }, /*#__PURE__*/React.createElement(ResourceBar, {
    current_amount: playerResourcesDataState.health,
    max_amount: playerResourcesDataState.max_health,
    resource_type: "health"
  })), /*#__PURE__*/React.createElement("div", {
    className: "player_money_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "player_money_label"
  }, "Money:"), /*#__PURE__*/React.createElement("div", {
    className: "player_money_text"
  }, /*#__PURE__*/React.createElement(React.Fragment, null, "\xA5"), playerDataState.money.toLocaleString()))), basicMenuOptions && /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_basic_menu_container box-primary"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_descriptive_text"
  }, "Light savory broth with eggs and noodles, perfect with sake."), /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_basic_menu"
  }, basicMenuOptions.map((option, index) => {
    return /*#__PURE__*/React.createElement(BasicRamen, {
      index: index,
      ramenInfo: option,
      PurchaseBasicRamen: PurchaseBasicRamen
    });
  }))), /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Mystery ramen"), /*#__PURE__*/React.createElement("div", {
    className: "ramen_shop_mystery_ramen_container box-primary"
  }, mysteryRamenDetailsState.mystery_ramen_unlocked ? /*#__PURE__*/React.createElement(React.Fragment, null, characterRamenDataState.mystery_ramen_available ? /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("img", {
    src: mysteryRamenDetailsState.image,
    className: "special_ramen_img"
  }), /*#__PURE__*/React.createElement("div", {
    className: "mystery_ramen_details_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mystery_ramen_description"
  }, "Made using leftover ingredients. You can't quite tell what's in it."), /*#__PURE__*/React.createElement("div", {
    className: "mystery_ramen_effects"
  }, mysteryRamenDetailsState.effects.map((effect, index) => {
    return /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "mystery_ramen_effect"
    }, effect.effect);
  })), /*#__PURE__*/React.createElement("div", {
    className: "mystery_ramen_duration"
  }, "Duration: ", mysteryRamenDetailsState.duration, " minutes"), /*#__PURE__*/React.createElement("div", {
    className: "mystery_ramen_button",
    onClick: () => PurchaseMysteryRamen()
  }, /*#__PURE__*/React.createElement(React.Fragment, null, "\xA5"), mysteryRamenDetailsState.cost))) : /*#__PURE__*/React.createElement("div", {
    className: "mystery_ramen_locked"
  }, "Check back soon!")) : /*#__PURE__*/React.createElement("div", {
    className: "mystery_ramen_locked"
  }, "Mystery Ramen not yet unlocked.")))), /*#__PURE__*/React.createElement("div", {
    className: "row second"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column first"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Special menu"), /*#__PURE__*/React.createElement("div", {
    className: "special_menu_container box-primary"
  }, specialMenuOptions.length > 0 ? specialMenuOptions.map((option, index) => {
    return /*#__PURE__*/React.createElement(SpecialRamen, {
      index: index,
      ramenInfo: option
    });
  }) : /*#__PURE__*/React.createElement("div", {
    className: "special_menu_locked"
  }, "Special ramen not yet unlocked.")))));
}
function BasicRamen({
  index,
  ramenInfo,
  PurchaseBasicRamen
}) {
  return /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "basic_ramen"
  }, /*#__PURE__*/React.createElement("img", {
    src: ramenInfo.image,
    className: "basic_ramen_img"
  }), /*#__PURE__*/React.createElement("div", {
    className: "basic_ramen_details"
  }, /*#__PURE__*/React.createElement("div", {
    className: "basic_ramen_name"
  }, ramenInfo.label), /*#__PURE__*/React.createElement("div", {
    className: "basic_ramen_effect"
  }, ramenInfo.health_amount, " HP"), /*#__PURE__*/React.createElement("div", {
    className: "basic_ramen_button",
    onClick: () => PurchaseBasicRamen(ramenInfo.ramen_key)
  }, /*#__PURE__*/React.createElement(React.Fragment, null, "\xA5"), ramenInfo.cost)));
}
function SpecialRamen({
  index,
  ramenInfo
}) {
  return /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "special_ramen"
  }, /*#__PURE__*/React.createElement("img", {
    src: ramenInfo.image,
    className: "special_ramen_img"
  }), /*#__PURE__*/React.createElement("div", {
    className: "special_ramen_name"
  }, ramenInfo.label), /*#__PURE__*/React.createElement("div", {
    className: "special_ramen_description"
  }, ramenInfo.description), /*#__PURE__*/React.createElement("div", {
    className: "special_ramen_effect"
  }, ramenInfo.effect), /*#__PURE__*/React.createElement("div", {
    className: "special_ramen_duration"
  }, "Duration: ", ramenInfo.duration, " minutes"), /*#__PURE__*/React.createElement("div", {
    className: "special_ramen_button",
    onClick: () => PurchaseSpecialRamen(ramenInfo.ramen_key)
  }, /*#__PURE__*/React.createElement(React.Fragment, null, "\xA5"), ramenInfo.cost));
}
window.RamenShopReactContainer = RamenShopReactContainer;