// @flow
import { ModalProvider } from "../utils/modalContext.js";
import { WarTable } from "./WarTable.js";
import { VillageHQ } from "./VillageHQ.js";
import { WorldInfo } from "./WorldInfo.js";
import { KageQuarters } from "./KageQuarters.js";
import { VillageUpgrades } from "./VillageUpgrades.js";

function Village({
    playerID,
    playerSeat,
    villageName,
    villageAPI,
    policyData,
    populationData,
    seatData,
    pointsData,
    diplomacyData,
    resourceData,
    clanData,
    proposalData,
    strategicData,
    challengeData,
    playerWarLogData,
    warRecordData,
    kageRecords,
    buildingUpgradeData,
}) {
    const [playerSeatState, setPlayerSeatState] = React.useState(playerSeat);
    const [policyDataState, setPolicyDataState] = React.useState(policyData);
    const [seatDataState, setSeatDataState] = React.useState(seatData);
    const [pointsDataState, setPointsDataState] = React.useState(pointsData);
    const [diplomacyDataState, setDiplomacyDataState] = React.useState(diplomacyData);
    const [resourceDataState, setResourceDataState] = React.useState(resourceData);
    const [proposalDataState, setProposalDataState] = React.useState(proposalData);
    const [strategicDataState, setStrategicDataState] = React.useState(strategicData);
    const [challengeDataState, setChallengeDataState] = React.useState(challengeData);
    const [buildingUpgradeDataState, setBuildingUpgradeDataState] = React.useState(buildingUpgradeData);
    const [villageTab, setVillageTab] = React.useState("villageHQ");

    function handleErrors(errors) {
        console.warn(errors);
    }

    return (
        <ModalProvider>
            <div className="navigation_row">
                <div className="nav_button" onClick={() => setVillageTab("villageHQ")}>village hq</div>
                <div className="nav_button" onClick={() => setVillageTab("worldInfo")}>world info</div>
                <div className="nav_button" onClick={() => setVillageTab("warTable")}>war table</div>
                <div className="nav_button" onClick={() => setVillageTab("villageUpgrades")}>upgrades</div>
                <div className={playerSeatState.seat_id != null ? "nav_button" : "nav_button disabled"} onClick={() => setVillageTab("kageQuarters")}>kage's quarters</div>
            </div>
            {villageTab === "villageHQ" &&
                <VillageHQ
                    playerID={playerID}
                    playerSeatState={playerSeatState}
                    setPlayerSeatState={setPlayerSeatState}
                    villageName={villageName}
                    villageAPI={villageAPI}
                    policyDataState={policyDataState}
                    populationData={populationData}
                    seatDataState={seatDataState}
                    setSeatDataState={setSeatDataState}
                    pointsDataState={pointsDataState}
                    diplomacyDataState={diplomacyDataState}
                    resourceDataState={resourceDataState}
                    setResourceDataState={setResourceDataState}
                    challengeDataState={challengeDataState}
                    setChallengeDataState={setChallengeDataState}
                    clanData={clanData}
                    kageRecords={kageRecords}
                    handleErrors={handleErrors}
                />
            }
            {villageTab === "kageQuarters" &&
                <KageQuarters
                    playerID={playerID}
                    playerSeatState={playerSeatState}
                    setPlayerSeatState={setPlayerSeatState}
                    villageName={villageName}
                    villageApiUrl={villageAPI}
                    policyDataState={policyDataState}
                    setPolicyDataState={setPolicyDataState}
                    seatDataState={seatDataState}
                    pointsDataState={pointsDataState}
                    setPointsDataState={setPointsDataState}
                    diplomacyDataState={diplomacyDataState}
                    setDiplomacyDataState={setDiplomacyDataState}
                    resourceDataState={resourceDataState}
                    setResourceDataState={setResourceDataState}
                    proposalDataState={proposalDataState}
                    setProposalDataState={setProposalDataState}
                    strategicDataState={strategicDataState}
                    setStrategicDataState={setStrategicDataState}
                    handleErrors={handleErrors}
                    buildingUpgradeDataState={buildingUpgradeDataState}
                    setBuildingUpgradeDataState={setBuildingUpgradeDataState}
                />
            }
            {villageTab === "worldInfo" &&
                <WorldInfo
                    villageName={villageName}
                    strategicDataState={strategicDataState}
                />
            }
            {villageTab === "warTable" &&
                <WarTable
                    playerWarLogData={playerWarLogData}
                    warRecordData={warRecordData}
                    strategicDataState={strategicDataState}
                    villageAPI={villageAPI}
                    handleErrors={handleErrors}
                />
            }
            {villageTab == "villageUpgrades" &&
                <VillageUpgrades
                    playerID={playerID}
                    playerSeatState={playerSeatState}
                    villageName={villageName}
                    villageAPI={villageAPI}
                    buildingUpgradeDataState={buildingUpgradeDataState}
                    setBuildingUpgradeDataState={setBuildingUpgradeDataState}
                    resourceDataState={resourceDataState}
                    setProposalDataState={setProposalDataState}
                />
            }
        </ModalProvider>
    );
}

window.Village = Village;