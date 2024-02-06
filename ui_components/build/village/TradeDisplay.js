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
  setRequestedRegions
}) {
  const toggleOfferedRegion = regionId => {
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
  const toggleRequestedRegion = regionId => {
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
    setOfferedResources(currentResources => currentResources.map(resource => resource.resource_name === resourceName ? {
      ...resource,
      count: value
    } : resource));
  };
  const handleRequestedResourcesChange = (resourceName, value) => {
    setRequestedResources(currentResources => currentResources.map(resource => resource.resource_name === resourceName ? {
      ...resource,
      count: value
    } : resource));
  };
  return viewOnly ? /*#__PURE__*/React.createElement("div", {
    className: "trade_display_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "trade_display_offer_container"
  }, /*#__PURE__*/React.createElement("div", {
    class: "schedule_challenge_subtext_wrapper",
    style: {
      marginBottom: "20px",
      marginTop: "-10px"
    }
  }, /*#__PURE__*/React.createElement("span", {
    class: "schedule_challenge_subtext"
  }, "Each village can offer up to 25000 resources of each resource type per trade."), /*#__PURE__*/React.createElement("span", {
    class: "schedule_challenge_subtext"
  }, "Trades have a cooldown of 24 hours.")), /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Offered Resources"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_resources"
  }, offeredResources.map((resource, index) => {
    const total = offeringVillageResources ? offeringVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
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
  }, offeredRegions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
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
  }, requestedResources.map((resource, index) => {
    const total = targetVillageResources ? targetVillageResources.find(total => total.resource_id === resource.resource_id).count : null;
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
  }, requestedRegions.filter(region => region.region_id > 5).map((region, index) => /*#__PURE__*/React.createElement("div", {
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
  }, offeredResources.map((resource, index) => {
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
    checked: offeredRegions.includes(region.region_id),
    onChange: () => toggleOfferedRegion(region.region_id)
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_request_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "header"
  }, "Request Resources"), /*#__PURE__*/React.createElement("div", {
    className: "trade_display_resources"
  }, requestedResources.map((resource, index) => {
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
    checked: requestedRegions.includes(region.region_id),
    onChange: () => toggleRequestedRegion(region.region_id)
  }))))));
}