import { apiFetch } from "../utils/network.js";
import { useModal } from '../utils/modalContext.js';

export function VillageUpgrades({
    playerSeatState,
    villageAPI,
    buildingUpgradeDataState,
    setBuildingUpgradeDataState,
}) {
    const [selectedBuilding, setSelectedBuilding] = React.useState(null);
    const [selectedUpgrade, setSelectedUpgrade] = React.useState(null);
    const [hoveredUpgrade, setHoveredUpgrade] = React.useState(null);
    const { openModal } = useModal();
    function handleErrors(errors) {
        console.warn(errors);
    }
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
    const BeginConstruction = () => {
        apiFetch(
            villageAPI,
            {
                request: 'BeginConstruction',
                building_key: selectedBuilding.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setBuildingUpgradeDataState(response.data.buildingUpgradeData);
            setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const CancelConstruction = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CancelConstruction',
                building_key: selectedBuilding.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setBuildingUpgradeDataState(response.data.buildingUpgradeData);
            setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const BeginResearch = () => {
        apiFetch(
            villageAPI,
            {
                request: 'BeginResearch',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setBuildingUpgradeDataState(response.data.buildingUpgradeData);
            setSelectedUpgrade(null);
            setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const CancelResearch = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CancelResearch',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setBuildingUpgradeDataState(response.data.buildingUpgradeData);
            setSelectedUpgrade(null);
            setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const renderUpgradeItems = (upgrade_set) => (
        upgrade_set.upgrades.map((upgrade, index) => (
            <div
                key={upgrade.key}
                className={`upgrade_item ${upgrade.requirements_met && upgrade.status === "locked" ? "available" : upgrade.status}`}
                onMouseEnter={() => setHoveredUpgrade(upgrade)}
                onMouseLeave={() => setHoveredUpgrade(null)}
            >
                <div className="upgrade_item_wrapper" onClick={() => setSelectedUpgrade(upgrade)}>
                    <div className="upgrade_item_inner">
                        <div className="upgrade_tier">{romanize(index + 1)}</div>
                    </div>
                </div>
            </div>
        ))
    );
    const buildingClickHandler = (building) => {
        setSelectedUpgrade(null);
        setSelectedBuilding(building);
    };
    const remainder = selectedBuilding !== null ? selectedBuilding.upgrade_sets.length % 3 : 0;
    const fillerDivsNeeded = remainder === 0 ? 0 : 3 - remainder;
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
                                <div key={building.key} className="building_item" onClick={() => buildingClickHandler(building)}>
                                    <div className="building_item_inner" style={{ background: "url(/images/building_backgrounds/placeholderbuilding.jpg)" }}>
                                        {building.status === "upgrading" &&
                                            <>
                                                <div className="construction_overlay"></div>
                                                <div className="construction_progress_overlay" style={{ height: `${(building.construction_progress / building.construction_progress_required) * 100}%` }}></div>
                                                <div className="construction_progress_text_container">
                                                    <div className="construction_progress_label">UNDER CONSTRUCTION</div>
                                                    <div className="construction_progress_text">{building.construction_time_remaining}</div>
                                                </div>
                                            </>
                                        }
                                    </div>
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
                    {(selectedBuilding && !selectedUpgrade) && (
                        <div className="building_construction_container">
                            <div className="building_visual" style={{ marginBottom: "20px" }}>
                                <div className="building_visual_inner" style={{ background: "url(/images/building_backgrounds/placeholderbuilding.jpg)" }}>
                                    {selectedBuilding.status === "upgrading" &&
                                        <>
                                        <div className="construction_overlay"></div>
                                        <div className="construction_progress_overlay" style={{ height: `${(selectedBuilding.construction_progress / selectedBuilding.construction_progress_required) * 100}%` }}></div>
                                            <div className="construction_progress_text_container">
                                                <div className="construction_progress_label">UNDER CONSTRUCTION</div>
                                                <div className="construction_progress_text">{selectedBuilding.construction_time_remaining}</div>
                                            </div>
                                        </>
                                    }
                                </div>
                                <div className="building_nameplate">
                                    <div className="building_name">
                                        {selectedBuilding.tier == 0 &&
                                            "Basic " + selectedBuilding.name
                                        }
                                        {selectedBuilding.tier > 0 &&
                                            "Tier " + selectedBuilding.tier + " " + selectedBuilding.name
                                        }
                                    </div>
                                </div>
                            </div>
                            <div className="upgrade_requirement_line">
                                <span style={{fontSize: "10px", lineHeight: "16px"}}>upgrade cost: </span>
                                <img src="/images/icons/materials.png" alt="materials" style={{height: "16px"}} />
                                <span style={{ color: "#e98b99"}}>{selectedBuilding.materials_construction_cost}</span>
                                <img src="/images/icons/food.png" alt="food" style={{ height: "16px"}} />                                                                                                                                                                         
                                <span style={{ color: "#e98b99" }}>{selectedBuilding.food_construction_cost}</span>
                                <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px"}} />
                                <span style={{ color: "#e98b99" }}>{selectedBuilding.wealth_construction_cost}</span>
                            </div>
                            <div className="upgrade_requirement_line">
                                <span style={{fontSize: "10px", lineHeight: "16px"}}>construction time: </span>
                                <img src="images/v2/icons/timer.png" alt="materials" style={{ height: "16px" }} />
                                <span>{selectedBuilding.construction_time} {selectedBuilding.construction_time > 1 ? " days" : " day"}</span>
                            </div>
                            <div className="building_buttons_container">
                                {(!!selectedBuilding.requirements_met && selectedBuilding.status === "default" && playerSeatState.seat_type === "kage") &&
                                    <>
                                        <div className="construction_begin_button" onClick={() => openModal({
                                            header: 'Confirmation',
                                            text: "sometext?",
                                            ContentComponent: null,
                                            onConfirm: () => BeginConstruction(),
                                        })}>upgrade {selectedBuilding.name}</div>
                                    </>
                                }
                                {(selectedBuilding.status === "upgrading" && playerSeatState.seat_type === "kage") &&
                                    <>
                                        <div className="construction_cancel_button" onClick={() => openModal({
                                            header: 'Confirmation',
                                            text: "sometext?",
                                            ContentComponent: null,
                                            onConfirm: () => CancelConstruction(),
                                        })}>cancel construction</div>
                                    </>
                                }
                            </div>
                        </div> 
                    )}
                    {selectedUpgrade && (
                        <div className="upgrade_research_container">
                            {selectedUpgrade.status === "researching" &&
                                <div className="research_progress_text_container">
                                    <div className="research_progress_label">RESEARCH IN PROGRESS</div>
                                    <div className="research_progress_text">{selectedUpgrade.research_time_remaining}</div>
                                </div>
                            }
                            <div className="upgrade_name">{selectedUpgrade.name}</div>
                            <div className="upgrade_description">{selectedUpgrade.description}</div>
                            <div className="upgrade_requirement_line">
                                <span style={{ fontSize: "10px", lineHeight: "16px" }}>research cost: </span>
                                <img src="/images/icons/materials.png" alt="materials" style={{ height: "16px"}} />
                                <span style={{ color: "#e98b99" }}>{selectedUpgrade.materials_research_cost}</span>
                                <img src="/images/icons/food.png" alt="food" style={{ height: "16px"}} />
                                <span style={{ color: "#e98b99" }}>{selectedUpgrade.food_research_cost}</span>
                                <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px"}} />
                                <span style={{ color: "#e98b99" }}>{selectedUpgrade.wealth_research_cost}</span>
                            </div>
                            <div className="upgrade_requirement_line">
                                <span style={{ fontSize: "10px", lineHeight: "16px" }}>daily upkeep: </span>
                                <img src="/images/icons/materials.png" alt="materials" style={{ height: "16px"}} />
                                <span style={{ color: "#e98b99" }}>{selectedUpgrade.materials_upkeep * 24}</span>
                                <img src="/images/icons/food.png" alt="food" style={{ height: "16px"}} />
                                <span style={{ color: "#e98b99" }}>{selectedUpgrade.food_upkeep * 24}</span>
                                <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px"}} />
                                <span style={{ color: "#e98b99" }}>{selectedUpgrade.wealth_upkeep * 24}</span>
                            </div>
                            <div className="upgrade_requirement_line">
                                <span style={{ fontSize: "10px", lineHeight: "16px" }}>research time: </span>
                                <img src="images/v2/icons/timer.png" alt="materials" style={{ height: "16px" }} />
                                <span>{selectedUpgrade.research_time} {selectedUpgrade.research_time > 1 ? " days" : " day"}</span>
                            </div>
                            <div className="upgrade_buttons_container">
                                {(!!selectedUpgrade.requirements_met && selectedUpgrade.status === "locked" && playerSeatState.seat_type === "kage") &&
                                    <>
                                        <div className="research_begin_button" onClick={() => openModal({
                                            header: 'Confirmation',
                                            text: "sometext?",
                                            ContentComponent: null,
                                            onConfirm: () => BeginResearch(),
                                        })}>begin research</div>
                                    </>
                                }
                                {(selectedUpgrade.status === "researching" && playerSeatState.seat_type === "kage") &&
                                    <>
                                        <div className="research_cancel_button" onClick={() => openModal({
                                            header: 'Confirmation',
                                            text: "sometext?",
                                            ContentComponent: null,
                                            onConfirm: () => CancelResearch(),
                                        })}>cancel research</div>
                                    </>
                                }
                            </div>
                        </div>
                    )}
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