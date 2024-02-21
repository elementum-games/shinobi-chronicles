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
  proposalData
}) {
  const [offeredRegionsState, setOfferedRegionsState] = React.useState([...offeredRegions.current]);
  const [requestedRegionsState, setRequestedRegionsState] = React.useState([...requestedRegions.current]);
  const [offeredResourcesState, setOfferedResourcesState] = React.useState([...offeredResources.current]);
  const [requestedResourcesState, setRequestedResourcesState] = React.useState([...requestedResources.current]);
  const toggleOfferedRegion = regionId => {
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
  const toggleRequestedRegion = regionId => {
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
    setOfferedResourcesState(currentResources => currentResources.map(resource => resource.resource_name === resourceName ? {
      ...resource,
      count: value
    } : resource));
  };
  const handleRequestedResourcesChange = (resourceName, value) => {
    setRequestedResourcesState(currentResources => currentResources.map(resource => resource.resource_name === resourceName ? {
      ...resource,
      count: value
    } : resource));
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
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: "20px",
      marginTop: "-30px",
      color: "#b6bdd0",
      fontSize: "11px",
      textAlign: "center",
      display: "flex",
      flexDirection: "column"
    }
  }, /*#__PURE__*/React.createElement("span", null, "Each village can offer up to 25000 resources of each resource type per trade."), /*#__PURE__*/React.createElement("span", null, "Trades have a cooldown of 24 hours.")), viewOnly ? /*#__PURE__*/React.createElement("div", {
    className: "trade_display_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_display_offer_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Offered Resources"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_resources"
  }, proposalData.offered_resources.map((resource, index) => {
    const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id)?.count ?? null : null;
    return /*#__PURE__*/React.createElement("div", {
      key: resource.resource_id,
      className: "trade_display_resource_wrapper"
    }, /*#__PURE__*/React.createElement("input", {
      type: "text",
      min: "0",
      max: total ? total : 25000,
      step: "100",
      placeholder: "0",
      className: "trade_display_resource_input",
      value: resource.count,
      style: {
        userSelect: "none"
      },
      readOnly: true
    }), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_name"
    }, resource.resource_name), total ? /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_total"
    }, total) : /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_total"
    }, "???"));
  })), /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Offered Regions"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_regions"
  }, proposalData.offered_regions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
    key: region.name,
    className: "trade_display_region_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_display_region_name"
  }, region.name))))), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_request_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Requested Resources"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_resources"
  }, proposalData.requested_resources.map((resource, index) => {
    const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id)?.count ?? null : null;
    return /*#__PURE__*/React.createElement("div", {
      key: resource.resource_id,
      className: "trade_display_resource_wrapper"
    }, /*#__PURE__*/React.createElement("input", {
      type: "text",
      min: "0",
      max: total ? total : 25000,
      step: "100",
      placeholder: "0",
      className: "trade_display_resource_input",
      value: resource.count,
      onChange: e => handleRequestedResourcesChange(resource.resource_name, parseInt(e.target.value)),
      style: {
        userSelect: "none"
      },
      readOnly: true
    }), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_name"
    }, resource.resource_name), total ? /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_total"
    }, total) : /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_total"
    }, "???"));
  })), /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Requested Regions"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_regions"
  }, proposalData.requested_regions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
    key: region.name,
    className: "trade_display_region_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_display_region_name"
  }, region.name)))))) : /*#__PURE__*/React.createElement("div", {
    className: "trade_display_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_display_offer_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Offer Resources"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_resources"
  }, offeredResourcesState.map((resource, index) => {
    const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
    return /*#__PURE__*/React.createElement("div", {
      key: resource.resource_id,
      className: "trade_display_resource_wrapper"
    }, /*#__PURE__*/React.createElement("input", {
      type: "number",
      min: "0",
      max: total ? total : 25000,
      step: "100",
      placeholder: "0",
      className: "trade_display_resource_input",
      value: resource.count,
      onChange: e => handleOfferedResourcesChange(resource.resource_name, parseInt(e.target.value))
    }), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_name"
    }, resource.resource_name), total ? /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_total"
    }, total) : /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_total"
    }, "???"));
  })), /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Offer Regions"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_regions"
  }, offeringVillageRegions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
    key: region.name,
    className: "trade_display_region_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_display_region_name"
  }, region.name), /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: offeredRegionsState.includes(region.region_id),
    onChange: () => toggleOfferedRegion(region.region_id)
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_request_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Request Resources"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_resources"
  }, requestedResourcesState.map((resource, index) => {
    const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
    return /*#__PURE__*/React.createElement("div", {
      key: resource.resource_id,
      className: "trade_display_resource_wrapper"
    }, /*#__PURE__*/React.createElement("input", {
      type: "number",
      min: "0",
      max: total ? total : 25000,
      step: "100",
      placeholder: "0",
      className: "trade_display_resource_input",
      value: resource.count,
      onChange: e => handleRequestedResourcesChange(resource.resource_name, parseInt(e.target.value))
    }), /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_name"
    }, resource.resource_name), total ? /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_total"
    }, total) : /*#__PURE__*/React.createElement("div", {
      className: "trade_display_resource_total"
    }, "???"));
  })), /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Request Regions"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_regions"
  }, targetVillageRegions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
    key: region.name,
    className: "trade_display_region_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_display_region_name"
  }, region.name), /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: requestedRegionsState.includes(region.region_id),
    onChange: () => toggleRequestedRegion(region.region_id)
  })))))));
}