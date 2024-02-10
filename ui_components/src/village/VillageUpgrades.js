import { apiFetch } from "../utils/network.js";

export function VillageUpgrades({
    buildingUpgradeDataState,
    setBuildingUpgradeDataState,
}) {
    const [selectedBuilding, setSelectedBuilding] = React.useState(null);
    //const [selectedUpgrade, setSelectedUpgrade] = React.useState(null);
    const [hoveredUpgrade, setHoveredUpgrade] = React.useState(null);
    const getBuildingUpkeepString = (building) => {
        let materials_cost = 0;
        let food_cost = 0;
        let wealth_cost = 0;
        let return_string = "upkeep cost / day: ";
        building.upgrade_sets.forEach((upgrade_set) => {
            upgrade_set.upgrades.forEach((upgrade) => {
                if (upgrade.status == "active") {
                    materials_cost += upgrade.materials_upkeep;
                    food_cost += upgrade.food_upkeep;
                    wealth_cost += upgrade.wealth_upkeep;
                }
            });
        });
        return_string += materials_cost + " materials, ";
        return_string += food_cost + " food, ";
        return_string += wealth_cost + " wealth ";
        return return_string;
    }
    /*const getUpgradeUpkeepString = () => {
        let materials_cost = 0;
        let food_cost = 0;
        let wealth_cost = 0;
        let return_string = "";
        if (materials_cost > 0) {
            return_string += materials_cost + " materials ";
        }
        if (food_cost > 0) {
            return_string += food_cost + " food ";
        }
        if (wealth_cost > 0) {
            return_string += wealth_cost + " wealth ";
        }
        return return_string;
    }*/
    function romanize(num) {
        switch (num) {
            case 1:
                return "I";
            case 2:
                return "II";
            case 3:
                return "III";
            case 4:
                return "IV";
            case 5:
                return "V";
            case 6:
                return "VI";
            case 7:
                return "VII";
            case 8:
                return "VIII";
            case 9:
                return "IX";
            case 10:
                return "X";
            default:
                return "I";
        }
    }
    const renderUpgradeItems = (upgrade_set) => (
        upgrade_set.upgrades.map((upgrade, index) => (
            <div
                key={upgrade.key}
                className={`upgrade_item ${upgrade.requirements_met && upgrade.status === "locked" ? "available" : upgrade.status}`}
                onMouseEnter={() => setHoveredUpgrade(upgrade)}
                onMouseLeave={() => setHoveredUpgrade(null)}
            >
                <div className="upgrade_item_wrapper">
                    <div className="upgrade_item_inner">
                        <div className="upgrade_tier">{romanize(index + 1)}</div>
                    </div>
                </div>
            </div>
        ))
    );
    const remainder = selectedBuilding !== null ? selectedBuilding.upgrade_sets.length % 3 : 0;
    const fillerDivsNeeded = remainder === 0 ? 0 : 3 - remainder;
    console.log(fillerDivsNeeded);
    return (
        <div className="upgradespage_container">
            <svg height="0" width="0">
                <defs>
                    <filter id="building_hover">
                        <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                        <feFlood floodColor="white" result="floodColor" />
                        <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                            <feFuncA type="linear" slope="1" />
                        </feComponentTransfer>
                        <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                        <feMerge>
                            <feMergeNode in="coloredBlur" />
                            <feMergeNode in="SourceGraphic" />
                        </feMerge>
                    </filter>
                </defs>
            </svg>
            <div className="row first">
                <div className="column first">
                    <div className="header">Village Buildings</div>
                    <div className="buildings_container">
                        {buildingUpgradeDataState
                            .map((building, index) => (
                                <div key={building.key} className="building_item" onClick={() => setSelectedBuilding(building)}>
                                    <div className="building_item_inner" style={{background: "url(/images/building_backgrounds/placeholderbuilding.jpg)"}}></div>
                                    <div className="building_nameplate">
                                        <div className="building_name">
                                            {building.tier == 0 &&
                                                "Basic " + building.name
                                            }
                                            {building.tier > 0 &&
                                                "Tier " + building.tier + " " + building.name
                                            }
                                        </div>
                                    </div>
                                </div>
                        ))}
                    </div>
                    {selectedBuilding && ( 
                        <div className="upgrades_container">
                            <div className="building_details">
                                <div className="building_name">
                                    {selectedBuilding.tier == 0 &&
                                        "Basic " + selectedBuilding.name
                                    }
                                    {selectedBuilding.tier > 0 &&
                                        "Tier " + selectedBuilding.tier + " " + selectedBuilding.name
                                    }
                                </div>
                                <div className="building_upkeep">
                                    {getBuildingUpkeepString(selectedBuilding)}
                                </div>
                                {/*selectedUpgrade && (
                                    <div className="selected_upgrade_cost">
                                        {getUpgradeUpkeepString(selectedUpgrade)}
                                    </div>
                                )*/}
                            </div>
                            <div className="upgrade_set_list">
                                {selectedBuilding.upgrade_sets.map((upgrade_set, index) => (
                                    <div key={upgrade_set.key} className="upgrade_set_item">
                                        <div className="upgrade_set_name">
                                            {upgrade_set.name}
                                        </div>
                                        <div className="upgrade_list">
                                            {renderUpgradeItems(upgrade_set)}
                                        </div>
                                        {(hoveredUpgrade !== null && upgrade_set.upgrades.find(u => u.key === hoveredUpgrade.key))
                                            ?
                                            <div className="upgrade_set_description">
                                                {hoveredUpgrade.name}
                                                <br></br>
                                                {hoveredUpgrade.description}
                                            </div>
                                            : <div className="upgrade_set_description">
                                                {upgrade_set.description}
                                            </div>
                                        }
                                    </div>
                                )).concat(
                                    Array.from({ length: fillerDivsNeeded }).map((_, fillerIndex) => (
                                        <div key={`filler-${fillerIndex}`} className="upgrade_set_item filler">
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}