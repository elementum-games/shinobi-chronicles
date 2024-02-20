export function TradeDisplay({
    viewOnly,
    offeringVillageResources,
    offeringVillageRegions,
    offeredResources,
    setOfferedResources,
    offeredRegions,
    setOfferedRegions,
    targetVillageResources,
    targetVillageRegions,
    requestedResources,
    setRequestedResources,
    requestedRegions,
    setRequestedRegions,
}) {
    const toggleOfferedRegion = (regionId) => {
        setOfferedRegions(current => {
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
        setRequestedRegions(current => {
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
        setOfferedResources(currentResources =>
            currentResources.map(resource =>
                resource.resource_name === resourceName
                    ? { ...resource, count: value }
                    : resource
            )
        );
    };
    const handleRequestedResourcesChange = (resourceName, value) => {
        setRequestedResources(currentResources =>
            currentResources.map(resource =>
                resource.resource_name === resourceName
                    ? { ...resource, count: value }
                    : resource
            )
        );
    };

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
                            {offeredResources
                                .map((resource, index) => {
                                    const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
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
                            {offeredRegions
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
                            {requestedResources
                                .map((resource, index) => {
                                    const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
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
                            {requestedRegions
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
                            {offeredResources
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
                                            checked={offeredRegions.includes(region.region_id)}
                                            onChange={() => toggleOfferedRegion(region.region_id)}
                                        />
                                    </div>
                                ))}
                        </div>
                    </div>
                    <div className="trade_display_request_container">
                        <div className="header">Request Resources</div>
                        <div className="trade_display_resources">
                            {requestedResources
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
                                            checked={requestedRegions.includes(region.region_id)}
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