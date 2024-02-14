import { apiFetch } from "../utils/network.js";
import { useModal } from '../utils/modalContext.js';

export function VillageUpgrades({
    playerSeatState,
    villageName,
    villageAPI,
    buildingUpgradeDataState,
    setBuildingUpgradeDataState,
    resourceDataState,
    setResourceDataState,
}) {
    const [selectedBuilding, setSelectedBuilding] = React.useState(null);
    const [selectedUpgrade, setSelectedUpgrade] = React.useState(null);
    const [hoveredUpgrade, setHoveredUpgrade] = React.useState(null);
    const { openModal } = useModal();
    const current_materials = resourceDataState.find(resource => resource.resource_name == "materials").count;
    const current_food = resourceDataState.find(resource => resource.resource_name == "food").count;
    const current_wealth = resourceDataState.find(resource => resource.resource_name == "wealth").count;

    function handleErrors(errors) {
        console.warn(errors);
    }
    const getBuildingUpkeep = (building) => {
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
        return {
            materials: materials_cost,
            food: food_cost,
            wealth: wealth_cost,
        };
    }
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
    const buildingClickHandler = (building) => {
        setSelectedUpgrade(null);
        setSelectedBuilding(building);
    };
    const renderBuildingDetails = () => {
        const upkeep = getBuildingUpkeep(selectedBuilding);
        const construction_progress_percent = (selectedBuilding.construction_progress / selectedBuilding.construction_progress_required) * 100;
        return (
            <div className="building_details">
                <div className="building_visual">
                    <div className="building_visual_inner" style={{ background: "url(/images/building_backgrounds/placeholderbuilding.jpg)" }}></div>
                </div>
                <div className="building_details_contents">
                    <div className="building_details_info_row">
                        <div className="building_info">
                            <div className="building_info_header_row">
                                <div className="building_info_name">
                                    {selectedBuilding.tier == 0 &&
                                        "Basic " + selectedBuilding.name
                                    }
                                    {selectedBuilding.tier > 0 &&
                                        "Tier " + selectedBuilding.tier + " " + selectedBuilding.name
                                    }
                                </div>
                                <div className="building_info_phrase">
                                    {selectedBuilding.phrase}
                                </div>
                            </div>
                            <div className="building_info_health_row">
                                <div className="building_info_health_bar">
                                </div>
                                <div className="building_info_health_label_row">
                                    <div className="building_info_health_label">building health</div>
                                    <div className="building_info_health_values">{selectedBuilding.health} / {selectedBuilding.max_health}</div>
                                    <div className="building_info_defense">defense {selectedBuilding.defense}</div>
                                </div>
                            </div>
                            <div className="building_info_description">{selectedBuilding.description}</div>
                        </div>
                        <div className="building_upkeep">
                            <div className="building_upkeep_cost_column">
                                <span style={{ color: "#e98b99" }}>{upkeep.materials}</span>
                                <span style={{ color: "#e98b99" }}>{upkeep.food}</span>
                                <span style={{ color: "#e98b99" }}>{upkeep.wealth}</span>
                            </div>
                            <div className="building_upkeep_label_column">
                                <img src="/images/icons/materials.png" alt="materials" style={{ maxHeight: "20px" }} />
                                <img src="/images/icons/food.png" alt="food" style={{ maxHeight: "19px", width: "15px" }} />
                                <img src="/images/icons/wealth.png" alt="wealth" style={{ maxHeight: "20px" }} />
                            </div>
                            <div className="building_upkeep_label">upkeep/day</div>
                        </div>
                    </div>
                    <div className="building_details_controls_row">
                        <div className="building_controls_container">
                            <div className="building_controls_label">upgrade to next tier</div>
                            <div className="building_controls">
                                <div className="building_upgrade_requirements" style={{ background: (selectedBuilding.status === "upgrading" ? `linear-gradient(to right, #362a4c 0%, #4c1f2f ${(100 - construction_progress_percent) / 2}%, #2d1d25 ${(100 - construction_progress_percent)}%, transparent ${(100 - construction_progress_percent) / 2}%, #2d1d25 ${(100 - construction_progress_percent)}%, transparent ${construction_progress_percent}%` : "") }}>
                                    {selectedBuilding.tier == 3 &&
                                        <>
                                            <span>No upgrades available</span>
                                        </>
                                    }
                                    {selectedBuilding.status === "upgrading" &&
                                        <>
                                            <div className="construction_progress_text">{selectedBuilding.construction_time_remaining}</div>
                                        </>
                                    }
                                    {(selectedBuilding.tier < 3 && selectedBuilding.status !== "upgrading") &&
                                        <>                                   
                                            <span style={{ fontSize: "10px", lineHeight: "16px" }}>upgrade cost: </span>
                                            <img src="/images/icons/materials.png" alt="materials" style={{ height: "16px" }} />
                                            <span style={{ color: (current_materials > selectedBuilding.materials_construction_cost ? "#96eeaf" : "#e98b99") }}>{selectedBuilding.materials_construction_cost}</span>
                                            <img src="/images/icons/food.png" alt="food" style={{ height: "16px" }} />
                                            <span style={{ color: (current_food > selectedBuilding.food_construction_cost ? "#96eeaf" : "#e98b99") }}>{selectedBuilding.food_construction_cost}</span>
                                            <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px" }} />
                                            <span style={{ color: (current_wealth > selectedBuilding.wealth_construction_cost ? "#96eeaf" : "#e98b99") }}>{selectedBuilding.wealth_construction_cost}</span>
                                            <img src="images/v2/icons/timer.png" alt="materials" style={{ height: "16px" }} />
                                            <span>{selectedBuilding.construction_time} {selectedBuilding.construction_time > 1 ? " days" : " day"}</span>
                                        </>
                                    }
                                </div>
                                <div className="building_buttons_container">
                                    {(selectedBuilding.status === "default" && playerSeatState.seat_type === "kage") &&
                                        <>
                                        {(!!selectedBuilding.requirements_met && current_materials > selectedBuilding.materials_construction_cost && current_food > selectedBuilding.food_construction_cost && current_wealth > selectedBuilding.wealth_construction_cost)
                                            ?
                                            <div className="construction_begin_button" onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "sometext?",
                                                ContentComponent: null,
                                                onConfirm: () => BeginConstruction(),
                                            })}>upgrade</div>
                                            :
                                            <div className="construction_begin_button disabled">upgrade</div>
                                        }
                                        </>
                                    }
                                    {(selectedBuilding.status === "upgrading" && playerSeatState.seat_type === "kage") &&
                                        <>
                                            <div className="construction_cancel_button" onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "sometext?",
                                                ContentComponent: null,
                                                onConfirm: () => CancelConstruction(),
                                            })}>cancel</div>
                                        </>
                                    }
                                    {(selectedBuilding.health < selectedBuilding.max_health && playerSeatState.seat_type === "kage")
                                        ?
                                        <>
                                            <div className="repair_begin_button" onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "sometext?",
                                                ContentComponent: null,
                                                onConfirm: () => BeginRepairs(),
                                            })}>repair</div>
                                        </>
                                        :
                                        <>
                                            <div className="repair_begin_button disabled">repair</div>
                                        </>
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        );
    }
    const renderUpgradesContainer = () => {
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
        )
        const renderUpgradeDetails = () => {
            return (
                <div className="upgrade_details_container">
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
                        <img src="/images/icons/materials.png" alt="materials" style={{ height: "16px" }} />
                        <span style={{ color: "#e98b99" }}>{selectedUpgrade.materials_research_cost}</span>
                        <img src="/images/icons/food.png" alt="food" style={{ height: "16px" }} />
                        <span style={{ color: "#e98b99" }}>{selectedUpgrade.food_research_cost}</span>
                        <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px" }} />
                        <span style={{ color: "#e98b99" }}>{selectedUpgrade.wealth_research_cost}</span>
                    </div>
                    <div className="upgrade_requirement_line">
                        <span style={{ fontSize: "10px", lineHeight: "16px" }}>daily upkeep: </span>
                        <img src="/images/icons/materials.png" alt="materials" style={{ height: "16px" }} />
                        <span style={{ color: "#e98b99" }}>{selectedUpgrade.materials_upkeep * 24}</span>
                        <img src="/images/icons/food.png" alt="food" style={{ height: "16px" }} />
                        <span style={{ color: "#e98b99" }}>{selectedUpgrade.food_upkeep * 24}</span>
                        <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px" }} />
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
            );
        }
        return (
            <div className="upgrades_container">
                <div className="upgrade_set_list">
                    {selectedBuilding.upgrade_sets.map((upgrade_set, index) => (
                        <div key={upgrade_set.key} className="upgrade_set_item">
                            {(selectedUpgrade !== null && upgrade_set.upgrades.find(u => u.key === selectedUpgrade.key))
                                ?
                                renderUpgradeDetails()
                                :
                                <>
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
                                </>
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
        );
    }
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
                    {selectedBuilding &&
                        <div className="building_details_container">
                            {renderBuildingDetails()}
                            {renderUpgradesContainer()}
                        </div>
                    }
                </div>
            </div>
        </div>
    );
}