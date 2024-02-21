import { getPolicyDisplayData } from "./villageUtils.js";
export function StrategicInfoItem({
  strategicInfoData
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_name_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_name"
  }, strategicInfoData.village.name), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_policy"
  }, getPolicyDisplayData(strategicInfoData.village.policy_id).name)), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_banner",
    style: {
      backgroundImage: "url(" + getStrategicInfoBanner(strategicInfoData.village.village_id) + ")"
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_kage_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "kage:"), strategicInfoData.seats.find(seat => seat.seat_type === "kage").user_name ? /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_seat"
  }, /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + strategicInfoData.seats.find(seat => seat.seat_type === "kage").user_name
  }, strategicInfoData.seats.find(seat => seat.seat_type === "kage").user_name)) : /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_seat"
  }, "-None-")), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_elder_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "elders:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_elders"
  }, strategicInfoData.seats.filter(seat => seat.seat_type === "elder").map((elder, index) => elder.user_name ? /*#__PURE__*/React.createElement("div", {
    key: elder.seat_key,
    className: "strategic_info_seat"
  }, /*#__PURE__*/React.createElement("a", {
    href: "/?id=6&user=" + elder.user_name
  }, elder.user_name)) : /*#__PURE__*/React.createElement("div", {
    key: elder.seat_key,
    className: "strategic_info_seat"
  }, "-None-")))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_points_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "points:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_points"
  }, strategicInfoData.village.points)), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_enemy_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "at war with ", /*#__PURE__*/React.createElement("img", {
    className: "strategic_info_war_icon",
    src: "/images/icons/war.png"
  })), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_relations"
  }, strategicInfoData.enemies.map((enemy, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "strategic_info_relation_item"
  }, enemy))))), /*#__PURE__*/React.createElement("div", {
    className: "column"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_population_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "village ninja:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_population"
  }, strategicInfoData.population.map((rank, index) => /*#__PURE__*/React.createElement("div", {
    key: rank.rank,
    className: "strategic_info_population_item"
  }, rank.count + " " + rank.rank)), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_population_item total"
  }, strategicInfoData.population.reduce((acc, rank) => acc + rank.count, 0), " total"))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_ally_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "allied with ", /*#__PURE__*/React.createElement("img", {
    className: "strategic_info_war_icon",
    src: "/images/icons/ally.png"
  })), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_relations"
  }, strategicInfoData.allies.map((ally, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "strategic_info_relation_item"
  }, ally)))))), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "column"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_region_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "regions owned:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_regions"
  }, strategicInfoData.regions.map((region, index) => /*#__PURE__*/React.createElement("div", {
    key: region.name,
    className: "strategic_info_region_item"
  }, region.name))))), /*#__PURE__*/React.createElement("div", {
    className: "column"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_resource_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_label"
  }, "resource points:"), /*#__PURE__*/React.createElement("div", {
    className: "strategic_info_supply_points"
  }, Object.values(strategicInfoData.supply_points).map((supply_point, index) => /*#__PURE__*/React.createElement("div", {
    key: index,
    className: "strategic_info_supply_item"
  }, /*#__PURE__*/React.createElement("span", {
    className: "supply_point_name"
  }, supply_point.name), " x", supply_point.count)))))));
}

function getStrategicInfoBanner(village_id) {
  switch (village_id) {
    case 1:
      return '/images/v2/decorations/strategic_banners/stratbannerstone.jpg';

    case 2:
      return '/images/v2/decorations/strategic_banners/stratbannercloud.jpg';

    case 3:
      return '/images/v2/decorations/strategic_banners/stratbannerleaf.jpg';

    case 4:
      return '/images/v2/decorations/strategic_banners/stratbannersand.jpg';

    case 5:
      return '/images/v2/decorations/strategic_banners/stratbannermist.jpg';

    default:
      return null;
  }
}