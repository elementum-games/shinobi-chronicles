import { apiFetch } from "../utils/network.js";
import { useModal } from '../utils/modalContext.js';

export function VillageUpgrades({
    playerSeatState,
    villageName,
    villageAPI,
    buildingUpgradeDataState,
    setBuildingUpgradeDataState,
    resourceDataState,
    setProposalDataState,
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
                if (upgrade.status == "active" || upgrade.status == "activating") {
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
                request: 'CreateProposal',
                type: 'begin_construction',
                building_key: selectedBuilding.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
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
                request: 'CreateProposal',
                type: 'cancel_construction',
                building_key: selectedBuilding.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
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
                request: 'CreateProposal',
                type: 'begin_research',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
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
                request: 'CreateProposal',
                type: 'cancel_research',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const ActivateUpgrade = () => {
        apiFetch(
            villageAPI,
            {
                request: 'ActivateUpgrade',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setBuildingUpgradeDataState(response.data.buildingUpgradeData);
            setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
            setSelectedUpgrade(null);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const DeactivateUpgrade = () => {
        apiFetch(
            villageAPI,
            {
                request: 'DeactivateUpgrade',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setBuildingUpgradeDataState(response.data.buildingUpgradeData);
            setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
            setSelectedUpgrade(null);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const CancelActivation = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CancelActivation',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setBuildingUpgradeDataState(response.data.buildingUpgradeData);
            setSelectedBuilding(response.data.buildingUpgradeData.find(b => b.key === selectedBuilding.key));
            setSelectedUpgrade(null);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const CheckBoostConstruction = () => {
        apiFetch(
            villageAPI,
            {
                request: 'GetConstructionBoostCost',
                building_key: selectedBuilding.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            openModal({
                header: 'Confirmation',
                text: "Boosting the construction of this building will cost " + response.data.response_message + " Village Points",
                ContentComponent: null,
                onConfirm: () => BoostConstruction(),
            });
        });
    }
    const CheckBoostResearch = () => {
        apiFetch(
            villageAPI,
            {
                request: 'GetResearchBoostCost',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            openModal({
                header: 'Confirmation',
                text: "Boosting the research of this upgrade will cost " + response.data.response_message + " Village Points",
                ContentComponent: null,
                onConfirm: () => BoostResearch(),
            });
        });
    }
    const BoostConstruction = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CreateProposal',
                type: 'boost_construction',
                building_key: selectedBuilding.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const BoostResearch = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CreateProposal',
                type: 'boost_research',
                upgrade_key: selectedUpgrade.key,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
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
        const renderHealthBar = () => {
            const percentage = (selectedBuilding.health / selectedBuilding.max_health) * 100;
            let barColor;
            let strokeColor = '#2b2c2c';
            if (percentage > 50) {
                barColor = '#00b044';
            } else if (percentage > 25) {
                barColor = 'yellow';
            } else {
                barColor = 'red';
            }
            return (
                <div className='building_health_bar'>
                    <svg width="325" height="9">
                        <g transform="skewX(-25)">
                            <rect x="5" y="0" width="50" height="5" style={{ fill: strokeColor, stroke: strokeColor, strokeWidth: '0' }} />
                        </g>
                        <g transform="skewX(-25)">
                            <rect x="5" y="0" width={percentage * 3.2} height="5" style={{ fill: barColor, stroke: strokeColor, strokeWidth: '0' }} />
                        </g>
                        <g transform="skewX(-25)">
                            <rect x="5" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="15" y="0" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="25" y="0" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="35" y="0" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="45" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="55" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="65" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="75" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="85" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="95" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="105" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="115" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="125" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="135" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="145" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="155" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="165" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="175" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="185" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="195" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="205" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="215" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="225" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="235" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="245" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="255" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="265" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="275" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="285" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="295" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="305" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                            <rect x="315" y="0" rx="2" ry="2" width="10" height="5" style={{ fill: 'transparent', stroke: strokeColor, strokeWidth: '2' }} />
                        </g>
                    </svg>
                </div>
            );
        }
        return (
            <div className="building_details">
                <div className="building_visual">
                    <div className="building_visual_inner" style={{ background: `url(${selectedBuilding.background_image}) center` }}></div>
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
                                    {renderHealthBar()}
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
                                <div className="building_upgrade_requirements" style={{background: (selectedBuilding.status === "upgrading" ? `linear-gradient(to right, #362a4c 0%, #4c1f2f ${(construction_progress_percent / 2)}%, #2d1d25 ${construction_progress_percent}%, transparent ${construction_progress_percent}%` : "") }}>
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
                                            {/*<span style={{ fontSize: "10px", lineHeight: "16px" }}>upgrade cost: </span>*/}
                                            <img src="/images/icons/materials.png" alt="materials" style={{ height: "16px" }} />
                                            <span style={{ color: (current_materials > selectedBuilding.materials_construction_cost ? "#96eeaf" : "#e98b99") }}>{selectedBuilding.materials_construction_cost}</span>
                                            <img src="/images/icons/food.png" alt="food" style={{ height: "16px" }} />
                                            <span style={{ color: (current_food > selectedBuilding.food_construction_cost ? "#96eeaf" : "#e98b99") }}>{selectedBuilding.food_construction_cost}</span>
                                            <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px" }} />
                                            <span style={{ color: (current_wealth > selectedBuilding.wealth_construction_cost ? "#96eeaf" : "#e98b99") }}>{selectedBuilding.wealth_construction_cost}</span>
                                            <img src="images/v2/icons/timer.png" alt="materials" style={{ height: "16px" }} />
                                            <span>{selectedBuilding.construction_time} {(selectedBuilding.construction_time > 1 || selectedBuilding.construction_time == 0) ? " days" : " day"}</span>
                                        </>
                                    }
                                </div>
                                <div className="building_buttons_container">
                                    {(selectedBuilding.status === "default" && playerSeatState.seat_type === "kage") &&
                                        <>
                                        {(!!selectedBuilding.requirements_met && current_materials > selectedBuilding.materials_construction_cost && current_food > selectedBuilding.food_construction_cost && current_wealth > selectedBuilding.wealth_construction_cost)
                                            ?
                                            <div className="construction_begin_button upgrades_control_button" onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "Are you sure you want to begin construction of " + selectedBuilding.name + "?\nYou may only have one building under construction at a time.",
                                                ContentComponent: null,
                                                onConfirm: () => BeginConstruction(),
                                            })}>build</div>
                                            :
                                            <div className="construction_begin_button  upgrades_control_button disabled">build</div>
                                        }
                                        </>
                                    }
                                    {(selectedBuilding.status === "upgrading" && playerSeatState.seat_type === "kage") &&
                                        <>
                                            <div className="construction_cancel_button upgrades_control_button" onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "Are you sure you want to cancel construction of " + selectedBuilding.name + "?\nExisting progress toward construction will be saved.",
                                                ContentComponent: null,
                                                onConfirm: () => CancelConstruction(),
                                            })}>cancel</div>
                                            {selectedBuilding.construction_boosted
                                                ?
                                            <div className="construction_boost_button upgrades_control_button" onClick={() => CheckBoostConstruction()}>boost</div>
                                                :
                                                <div className="construction_boost_button upgrades_control_button disabled">boost</div>
                                            }

                                        </>
                                    }
                                    {selectedBuilding.status !== "upgrading" &&
                                        <>
                                            {(selectedBuilding.health < selectedBuilding.max_health && playerSeatState.seat_type === "kage")
                                                ?
                                                <>
                                                    <div className="repair_begin_button upgrades_control_button" onClick={() => openModal({
                                                        header: 'Confirmation',
                                                        text: "sometext?",
                                                        ContentComponent: null,
                                                        onConfirm: () => BeginRepairs(),
                                                    })}>repair</div>
                                                </>
                                                :
                                                <>
                                                    <div className="repair_begin_button upgrades_control_button disabled">repair</div>
                                                </>
                                            }
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
                            <div className="upgrade_tier">{romanize(upgrade.tier)}</div>
                        </div>
                    </div>
                </div>
            ))
        )
        const renderUpgradeDetails = () => {
            const research_progress_percent = (selectedUpgrade.research_progress / selectedUpgrade.research_progress_required) * 100;
            return (
                <div className="upgrade_details_container">
                    <div className="upgrade_name">{selectedUpgrade.name}</div>
                    <div className="upgrade_description">{selectedUpgrade.description}</div>
                    <div className="upgrade_controls_container">
                        <div className="upgrade_buttons_container">
                            {(!!selectedUpgrade.requirements_met && selectedUpgrade.status === "locked" && playerSeatState.seat_type === "kage") &&
                                <>
                                    <div className="research_begin_button upgrades_control_button" onClick={() => openModal({
                                        header: 'Confirmation',
                                        text: "Are you sure you want to begin research for " + selectedUpgrade.name + "?\nYou may only have one upgrade under research at a time.",
                                        ContentComponent: null,
                                        onConfirm: () => BeginResearch(),
                                    })}>research</div>
                                </>
                            }
                            {(!(!!selectedUpgrade.requirements_met) && selectedUpgrade.status === "locked" && playerSeatState.seat_type === "kage") &&
                                <>
                                    <div className="research_begin_button upgrades_control_button disabled">research</div>
                                </>
                            }
                            {(selectedUpgrade.status === "researching" && playerSeatState.seat_type === "kage") &&
                                <>
                                    <div className="research_cancel_button upgrades_control_button" onClick={() => openModal({
                                        header: 'Confirmation',
                                        text: "Are you sure you want to cancel research for " + selectedUpgrade.name + "?\nExisting progress toward research will be saved.",
                                        ContentComponent: null,
                                        onConfirm: () => CancelResearch(),
                                    })}>cancel</div>
                                    {selectedUpgrade.research_boosted
                                        ?
                                        <div className="research_boost_button upgrades_control_button disabled">boost</div>
                                        :
                                        <div className="research_boost_button upgrades_control_button" onClick={() => CheckBoostResearch()}>boost</div>
                                    }
                                </>
                            }
                            {(selectedUpgrade.status === "inactive" && playerSeatState.seat_type === "kage") &&
                                <>
                                    <div className="upgrade_toggle_on_button upgrades_control_button" onClick={() => openModal({
                                        header: 'Confirmation',
                                        text: "Activating " + selectedUpgrade.name + " will take 3 days and require upkeep during the activation period.",
                                        ContentComponent: null,
                                        onConfirm: () => ActivateUpgrade(),
                                    })}>activate</div>
                                </>
                            }
                            {(selectedUpgrade.status === "active" && playerSeatState.seat_type === "kage") &&
                                <>
                                    <div className="upgrade_toggle_off_button upgrades_control_button" onClick={() => openModal({
                                        header: 'Confirmation',
                                        text: "Are you sure you want to deactivate " + selectedUpgrade.name + "?\nUpkeep will be disabled and reactivation will take 3 days.",
                                        ContentComponent: null,
                                        onConfirm: () => DeactivateUpgrade(),
                                    })}>deactivate</div>
                                </>
                            }
                            {(selectedUpgrade.status === "activating" && playerSeatState.seat_type === "kage") &&
                                <>
                                    <div className="upgrade_toggle_off_button upgrades_control_button" onClick={() => openModal({
                                        header: 'Confirmation',
                                        text: "Are you sure you want to cancel activation for " + selectedUpgrade.name + "?\nUpkeep will be disabled and reactivation will take 3 days.",
                                        ContentComponent: null,
                                        onConfirm: () => CancelActivation(),
                                    })}>cancel</div>
                                </>
                            }
                        </div>
                        {selectedUpgrade.status === "locked" && 
                            <>
                                <div className="upgrade_controls_label">research cost</div>
                                <div className="upgrade_research_requirements">
                                    <img src="/images/icons/materials.png" alt="materials" style={{ height: "16px" }} />
                                    <span style={{ color: (current_materials > selectedUpgrade.materials_research_cost ? "#96eeaf" : "#e98b99") }}>{selectedUpgrade.materials_research_cost}</span>
                                    <img src="/images/icons/food.png" alt="food" style={{ height: "16px" }} />
                                    <span style={{ color: (current_food > selectedUpgrade.food_research_cost ? "#96eeaf" : "#e98b99") }}>{selectedUpgrade.food_research_cost}</span>
                                    <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px" }} />
                                    <span style={{ color: (current_wealth > selectedUpgrade.wealth_research_cost ? "#96eeaf" : "#e98b99") }}>{selectedUpgrade.wealth_research_cost}</span>
                                    <img src="images/v2/icons/timer.png" alt="materials" style={{ height: "16px" }} />
                                    <span>{selectedUpgrade.research_time} {(selectedUpgrade.research_time > 1 || selectedUpgrade.research_time == 0) ? " days" : " day"}</span>
                                </div>
                            </>
                        }
                        {selectedUpgrade.status === "researching" && 
                            <>
                                <div className="upgrade_controls_label">researching</div>
                                    <div className="upgrade_research_requirements" style={{ background: `linear-gradient(to right, #362a4c 0%, #4c1f2f ${(research_progress_percent / 2)}%, #2d1d25 ${research_progress_percent}%, transparent ${research_progress_percent}%` }}>
                                    <div className="research_progress_text">{selectedUpgrade.research_time_remaining}</div>
                                </div>
                            </>
                        }
                        {selectedUpgrade.status === "activating" &&
                            <>
                                <div className="upgrade_controls_label">activating</div>
                                    <div className="upgrade_research_requirements" style={{ background: `linear-gradient(to right, #362a4c 0%, #4c1f2f ${(research_progress_percent / 2)}%, #2d1d25 ${research_progress_percent}%, transparent ${research_progress_percent}%` }}>
                                    <div className="research_progress_text">{selectedUpgrade.research_time_remaining}</div>
                                </div>
                            </>
                        }
                        {(selectedUpgrade.status == "active" || selectedUpgrade.status == "unlocked" || selectedUpgrade.status == "inactive") &&
                            <>
                                <div className="upgrade_controls_label">upkeep cost</div>
                                <div className="upgrade_research_requirements">
                                    <img src="/images/icons/materials.png" alt="materials" style={{ height: "16px" }} />
                                    <span style={{ color: "#e98b99" }}>{selectedUpgrade.materials_upkeep}</span>
                                    <img src="/images/icons/food.png" alt="food" style={{ height: "16px" }} />
                                    <span style={{ color: "#e98b99" }}>{selectedUpgrade.food_upkeep}</span>
                                    <img src="/images/icons/wealth.png" alt="wealth" style={{ height: "16px" }} />
                                    <span style={{ color: "#e98b99" }}>{selectedUpgrade.wealth_upkeep}</span>
                                    <img src="images/v2/icons/timer.png" alt="materials" style={{ height: "16px" }} />
                                    <span>upkeep/hour</span>
                                </div>
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
                                    <div className="building_item_inner" style={{ background: `url(${building.background_image}) center` }}>
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