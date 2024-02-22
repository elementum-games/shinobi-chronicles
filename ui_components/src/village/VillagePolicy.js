// @flow strict

import type { VillagePolicyType, VillageSeatType } from "./villageSchema.js";
import { useModal } from "../utils/modalContext.js";
import { getPolicyDisplayData } from "./villageUtils.js";

type Props = {
    +policyDataState: VillagePolicyType,
    +playerSeatState: VillageSeatType,
    +displayPolicyID: number,
    +showPolicyControls?: boolean,
    +handlePrevPolicyClick?: () => void,
    +handleNextPolicyClick?: () => void,
    +handlePolicyChange?: () => void,
};
export default function VillagePolicy({
    policyDataState,
    playerSeatState,
    displayPolicyID,
    handlePrevPolicyClick,
    handleNextPolicyClick,
    handlePolicyChange,
    showPolicyControls = false
}: Props): React$Node {
    const { openModal } = useModal();
    const policyDisplay = getPolicyDisplayData(displayPolicyID)

    return <>
        <div className="village_policy_container">
            <div className="village_policy_bonus_container">
                {policyDisplay.bonuses.map((bonus, index) => (
                    <div key={index} className="policy_bonus_item">
                        <svg width="16" height="16" viewBox="0 0 100 100">
                            <polygon points="25,20 50,45 25,70 0,45" fill="#4a5e45" />
                            <polygon points="25,0 50,25 25,50 0,25" fill="#6ab352" />
                        </svg>
                        <div className="policy_bonus_text">{bonus}</div>
                    </div>
                ))}
            </div>
            {showPolicyControls && displayPolicyID !== policyDataState.policy_id &&
                <div
                    className={playerSeatState.seat_type === "kage"
                        ? "village_policy_change_button"
                        : "village_policy_change_button disabled"
                    }
                    onClick={() => openModal({
                        header: 'Confirmation',
                        text: "Are you sure you want to change policies? You will be unable to select a new policy for 3 days.",
                        ContentComponent: null,
                        onConfirm: handlePolicyChange,
                    })}
                >change policy</div>
            }
            <div className="village_policy_main_container">
                <div className="village_policy_main_inner">
                    <div className="village_policy_banner" style={{ backgroundImage: "url(" + policyDisplay.banner + ")" }}></div>
                    <div className="village_policy_name_container">
                        <div className={"village_policy_name " + policyDisplay.glowClass}>{policyDisplay.name}</div>
                    </div>
                    <div className="village_policy_phrase">{policyDisplay.phrase}</div>
                    <div className="village_policy_description">{policyDisplay.description}</div>
                    {showPolicyControls && displayPolicyID > 1 &&
                        <div className="village_policy_previous_wrapper">
                            <svg className="previous_policy_button" width="20" height="20" viewBox="0 0 100 100" onClick={handlePrevPolicyClick}>
                                <polygon className="previous_policy_triangle_inner" points="100,0 100,100 35,50" />
                                <polygon className="previous_policy_triangle_outer" points="65,0 65,100 0,50" />
                            </svg>
                        </div>
                    }
                    {showPolicyControls && displayPolicyID < 5 &&
                        <div className="village_policy_next_wrapper">
                            <svg className="next_policy_button" width="20" height="20" viewBox="0 0 100 100" onClick={handleNextPolicyClick}>
                                <polygon className="next_policy_triangle_inner" points="0,0 0,100 65,50" />
                                <polygon className="next_policy_triangle_outer" points="35,0 35,100 100,50" />
                            </svg>
                        </div>
                    }
                </div>
            </div>
            <div className="village_policy_penalty_container">
                {policyDisplay.penalties.map((penalty, index) => (
                    <div key={index} className="policy_penalty_item">
                        <svg width="16" height="16" viewBox="0 0 100 100">
                            <polygon points="25,20 50,45 25,70 0,45" fill="#4f1e1e" />
                            <polygon points="25,0 50,25 25,50 0,25" fill="#ad4343" />
                        </svg>
                        <div className="policy_penalty_text">{penalty}</div>
                    </div>
                ))}
            </div>
        </div>
    </>;
}