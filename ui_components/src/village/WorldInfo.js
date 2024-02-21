// @flow

import { StrategicInfoItem } from "./StrategicInfoItem.js";
import { getVillageIcon } from "./villageUtils.js";

export function WorldInfo({
    villageName,
    strategicDataState,
}) {
    const [strategicDisplayLeft, setStrategicDisplayLeft] = React.useState(strategicDataState.find(item => item.village.name === villageName));
    const [strategicDisplayRight, setStrategicDisplayRight] = React.useState(strategicDataState.find(item => item.village.name !== villageName));
    return (
        <div className="worldInfo_container">
            <div className="row first">
                <div className="column first">
                    <div className="header">Strategic information</div>
                    <div className="strategic_info_container">
                        <StrategicInfoItem strategicInfoData={strategicDisplayLeft} />
                        <div className="strategic_info_navigation" style={{marginTop: "155px"}}>
                            <div className="strategic_info_navigation_village_buttons">
                                {villageName !== "Stone" &&
                                    <div className={strategicDisplayRight.village.village_id === 1 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[0])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(1)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                                {villageName !== "Cloud" &&
                                    <div className={strategicDisplayRight.village.village_id === 2 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[1])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(2)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                                {villageName !== "Leaf" &&
                                    <div className={strategicDisplayRight.village.village_id === 3 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[2])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(3)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                                {villageName !== "Sand" &&
                                    <div className={strategicDisplayRight.village.village_id === 4 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[3])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(4)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                                {villageName !== "Mist" &&
                                    <div className={strategicDisplayRight.village.village_id === 5 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[4])}>
                                        <div className="strategic_info_nav_button_inner">
                                            <img src={getVillageIcon(5)} className="strategic_info_nav_button_icon" />
                                        </div>
                                    </div>
                                }
                            </div>
                        </div>
                        <StrategicInfoItem strategicInfoData={strategicDisplayRight} />
                    </div>
                </div>
            </div>
        </div>
    );
}