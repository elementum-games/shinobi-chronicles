// @flow strict

import type { VillageProposalType, VillageStrategicInfo } from "./villageSchema.js";

type Props = {|
    +viewOnly: boolean,
    +offeringVillageResources: VillageStrategicInfo["village"]["resources"],
    +offeringVillageRegions: VillageStrategicInfo["village"]["regions"],
    +offeredResources: VillageProposalType["trade_data"]["offered_resources"],
    +offeredRegions: VillageProposalType["trade_data"]["offered_regions"],
    +targetVillageResources: VillageStrategicInfo["village"]["resources"],
    +targetVillageRegions:  VillageStrategicInfo["village"]["regions"],
    +requestedResources: VillageProposalType["trade_data"]["requested_resources"],
    +requestedRegions: VillageProposalType["trade_data"]["requested_regions"],
    +proposalData: VillageProposalType,
|};

export function TradeDisplay({
    viewOnly,
    offeringVillageResources,
    offeringVillageRegions,
    offeredResources,
    offeredRegions,
    targetVillageResources,
    targetVillageRegions,
    requestedResources,
    requestedRegions,
    proposalData,
}: Props) {
    const [offeredRegionsState, setOfferedRegionsState] = React.useState([...offeredRegions.current]);
    const [requestedRegionsState, setRequestedRegionsState] = React.useState([...requestedRegions.current]);
    const [offeredResourcesState, setOfferedResourcesState] = React.useState([...offeredResources.current]);
    const [requestedResourcesState, setRequestedResourcesState] = React.useState([...requestedResources.current]);
    const toggleOfferedRegion = (regionId) => {
        setOfferedRegionsState(current => {
            // Check if the region is already selected
            if (current.includes(regionId)) {
                // If it is, filter it out (unselect it)
                return current.filter(id => id !== regionId);
            } else {
                // Otherwise, add it to the selected regions
                return [...current, regionId];
            }
        });
    };
    const toggleRequestedRegion = (regionId) => {
        setRequestedRegionsState(current => {
            // Check if the region is already selected
            if (current.includes(regionId)) {
                // If it is, filter it out (unselect it)
                return current.filter(id => id !== regionId);
            } else {
                // Otherwise, add it to the selected regions
                return [...current, regionId];
            }
        });
    };
    const handleOfferedResourcesChange = (resourceName, value) => {
        setOfferedResourcesState(currentResources =>
            currentResources.map(resource =>
                resource.resource_name === resourceName
                    ? { ...resource, count: value }
                    : resource
            )
        );
    };
    const handleRequestedResourcesChange = (resourceName, value) => {
        setRequestedResourcesState(currentResources =>
            currentResources.map(resource =>
                resource.resource_name === resourceName
                    ? { ...resource, count: value }
                    : resource
            )
        );
    };

    React.useEffect(() => {
        offeredRegions.current = [...offeredRegionsState];
    }, [offeredRegionsState]);
    React.useEffect(() => {
        requestedRegions.current = [...requestedRegionsState];
    }, [requestedRegionsState]);
    React.useEffect(() => {
        offeredResources.current = [...offeredResourcesState];
    }, [offeredResourcesState]);
    React.useEffect(() => {
        requestedResources.current = [...requestedResourcesState];
    }, [requestedResourcesState]);

    return (
        <>
            <div style={{ marginBottom: "20px", marginTop: "-30px", color: "#b6bdd0", fontSize: "11px", textAlign: "center", display: "flex", flexDirection: "column" }}>
                <span>Each village can offer up to 25000 resources of each resource type per trade.</span>
                <span>Trades have a cooldown of 24 hours.</span>
            </div>
            {viewOnly ?
                <div className="trade_display_container">
                    <div className="trade_display_offer_container">
                        <div className="header">Offered Resources</div>
                        <div className="trade_display_resources">
                            {proposalData.offered_resources
                                .map((resource, index) => {
                                    const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id)?.count ?? null : null;
                                    return (
                                        <div key={resource.resource_id} className="trade_display_resource_wrapper">
                                            <input
                                                type="text"
                                                min="0"
                                                max={total ? total : 25000}
                                                step="100"
                                                placeholder="0"
                                                className="trade_display_resource_input"
                                                value={resource.count}
                                                style={{ userSelect: "none" }}
                                                readOnly
                                            />
                                            <div className="trade_display_resource_name">{resource.resource_name}</div>
                                            {total ?
                                                <div className="trade_display_resource_total">{total}</div>
                                                :
                                                <div className="trade_display_resource_total">???</div>
                                            }
                                        </div>
                                    );
                                })}
                        </div>
                        <div className="header">Offered Regions</div>
                        <div className="trade_display_regions">
                            {proposalData.offered_regions
                                .filter(region => region.region_id > 5)
                                .map((region, index) => (
                                    <div key={region.name} className="trade_display_region_wrapper">
                                        <div className="trade_display_region_name">{region.name}</div>
                                    </div>
                                ))}
                        </div>
                    </div>
                    <div className="trade_display_request_container">
                        <div className="header">Requested Resources</div>
                        <div className="trade_display_resources">
                            {proposalData.requested_resources
                                .map((resource, index) => {
                                    const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id)?.count ?? null : null;
                                    return (
                                        <div key={resource.resource_id} className="trade_display_resource_wrapper">
                                            <input
                                                type="text"
                                                min="0"
                                                max={total ? total : 25000}
                                                step="100"
                                                placeholder="0"
                                                className="trade_display_resource_input"
                                                value={resource.count}
                                                onChange={(e) => handleRequestedResourcesChange(resource.resource_name, parseInt(e.target.value))}
                                                style={{ userSelect: "none" }}
                                                readOnly
                                            />
                                            <div className="trade_display_resource_name">{resource.resource_name}</div>
                                            {total ?
                                                <div className="trade_display_resource_total">{total}</div>
                                                :
                                                <div className="trade_display_resource_total">???</div>
                                            }
                                        </div>
                                    );
                                })}
                        </div>
                        <div className="header">Requested Regions</div>
                        <div className="trade_display_regions">
                            {proposalData.requested_regions
                                .filter(region => region.region_id > 5)
                                .map((region, index) => (
                                    <div key={region.name} className="trade_display_region_wrapper">
                                        <div className="trade_display_region_name">{region.name}</div>
                                    </div>
                                ))}
                        </div>
                    </div>
                </div>
                :
                <div className="trade_display_container">
                    <div className="trade_display_offer_container">
                        <div className="header">Offer Resources</div>
                        <div className="trade_display_resources">
                            {offeredResourcesState
                                .map((resource, index) => {
                                    const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
                                    return (
                                        <div key={resource.resource_id} className="trade_display_resource_wrapper">
                                            <input
                                                type="number"
                                                min="0"
                                                max={total ? total : 25000}
                                                step="100"
                                                placeholder="0"
                                                className="trade_display_resource_input"
                                                value={resource.count}
                                                onChange={(e) => handleOfferedResourcesChange(resource.resource_name, parseInt(e.target.value))}
                                            />
                                            <div className="trade_display_resource_name">{resource.resource_name}</div>
                                            {total ?
                                                <div className="trade_display_resource_total">{total}</div>
                                                :
                                                <div className="trade_display_resource_total">???</div>
                                            }
                                        </div>
                                    );
                                })}
                        </div>
                        <div className="header">Offer Regions</div>
                        <div className="trade_display_regions">
                            {offeringVillageRegions
                                .filter(region => region.region_id > 5)
                                .map((region, index) => (
                                    <div key={region.name} className="trade_display_region_wrapper">
                                        <div className="trade_display_region_name">{region.name}</div>
                                        <input
                                            type="checkbox"
                                            checked={offeredRegionsState.includes(region.region_id)}
                                            onChange={() => toggleOfferedRegion(region.region_id)}
                                        />
                                    </div>
                                ))}
                        </div>
                    </div>
                    <div className="trade_display_request_container">
                        <div className="header">Request Resources</div>
                        <div className="trade_display_resources">
                            {requestedResourcesState
                                .map((resource, index) => {
                                    const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
                                    return (
                                        <div key={resource.resource_id} className="trade_display_resource_wrapper">
                                            <input
                                                type="number"
                                                min="0"
                                                max={total ? total : 25000}
                                                step="100"
                                                placeholder="0"
                                                className="trade_display_resource_input"
                                                value={resource.count}
                                                onChange={(e) => handleRequestedResourcesChange(resource.resource_name, parseInt(e.target.value))}
                                            />
                                            <div className="trade_display_resource_name">{resource.resource_name}</div>
                                            {total ?
                                                <div className="trade_display_resource_total">{total}</div>
                                                :
                                                <div className="trade_display_resource_total">???</div>
                                            }
                                        </div>
                                    );
                                })}
                        </div>
                        <div className="header">Request Regions</div>
                        <div className="trade_display_regions">
                            {targetVillageRegions
                                .filter(region => region.region_id > 5)
                                .map((region, index) => (
                                    <div key={region.name} className="trade_display_region_wrapper">
                                        <div className="trade_display_region_name">{region.name}</div>
                                        <input
                                            type="checkbox"
                                            checked={requestedRegionsState.includes(region.region_id)}
                                            onChange={() => toggleRequestedRegion(region.region_id)}
                                        />
                                    </div>
                                ))}
                        </div>
                    </div>
                </div>
            }
        </>
    );
}