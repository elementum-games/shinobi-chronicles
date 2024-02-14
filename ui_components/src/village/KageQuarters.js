import { apiFetch } from "../utils/network.js";
import { StrategicInfoItem } from "./StrategicInfoItem.js";
import { useModal } from '../utils/modalContext.js';
import { TradeDisplay } from "./TradeDisplay.js";

export function KageQuarters({
    playerID,
    playerSeatState,
    villageName,
    villageAPI,
    policyDataState,
    setPolicyDataState,
    seatDataState,
    pointsDataState,
    setPointsDataState,
    diplomacyDataState,
    resourceDataState,
    setResourceDataState,
    proposalDataState,
    setProposalDataState,
    strategicDataState,
    setStrategicDataState,
    handleErrors,
    getKageKanji,
    getVillageIcon,
    getPolicyDisplayData,
    StrategicInfoItem
}) {
    const kage = seatDataState.find(seat => seat.seat_type === 'kage');
    const [currentProposal, setCurrentProposal] = React.useState(null);
    const [currentProposalKey, setCurrentProposalKey] = React.useState(null);
    const [displayPolicyID, setDisplayPolicyID] = React.useState(policyDataState.policy_id);
    const [policyDisplay, setPolicyDisplay] = React.useState(getPolicyDisplayData(displayPolicyID));
    const [proposalRepAdjustment, setProposalRepAdjustment] = React.useState(0);
    const [strategicDisplayLeft, setStrategicDisplayLeft] = React.useState(strategicDataState.find(item => item.village.name == villageName));
    const [strategicDisplayRight, setStrategicDisplayRight] = React.useState(strategicDataState.find(item => item.village.name != villageName));
    const [offeredResources, setOfferedResources] = React.useState([
        { resource_id: 1, resource_name: "materials", count: 0 },
        { resource_id: 2, resource_name: "food", count: 0 },
        { resource_id: 3, resource_name: "wealth", count: 0 }
    ]);
    const [offeredRegions, setOfferedRegions] = React.useState([]);
    const [requestedResources, setRequestedResources] = React.useState([
        { resource_id: 1, resource_name: "materials", count: 0 },
        { resource_id: 2, resource_name: "food", count: 0 },
        { resource_id: 3, resource_name: "wealth", count: 0 }
    ]);
    const [requestedRegions, setRequestedRegions] = React.useState([]);
    const { openModal } = useModal();
    const ChangePolicy = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CreateProposal',
                type: 'policy',
                policy_id: displayPolicyID,
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
    const DeclareWar = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CreateProposal',
                type: 'declare_war',
                target_village_id: strategicDisplayRight.village.village_id,
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
    const OfferPeace = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CreateProposal',
                type: 'offer_peace',
                target_village_id: strategicDisplayRight.village.village_id,
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
    const OfferAlliance = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CreateProposal',
                type: 'offer_alliance',
                target_village_id: strategicDisplayRight.village.village_id,
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
    const BreakAlliance = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CreateProposal',
                type: 'break_alliance',
                target_village_id: strategicDisplayRight.village.village_id,
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
    const CancelProposal = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CancelProposal',
                proposal_id: currentProposal.proposal_id,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
            setCurrentProposal(null);
            setCurrentProposalKey(null);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const OfferTrade = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CreateProposal',
                type: 'offer_trade',
                target_village_id: strategicDisplayRight.village.village_id,
                offered_resources: offeredResources,
                offered_regions: offeredRegions,
                requested_resources: requestedResources,
                requested_regions: requestedRegions
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
    const EnactProposal = () => {
        apiFetch(
            villageAPI,
            {
                request: 'EnactProposal',
                proposal_id: currentProposal.proposal_id,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
            setCurrentProposal(null);
            setCurrentProposalKey(null);
            setPolicyDataState(response.data.policyData);
            setDisplayPolicyID(response.data.policyData.policy_id);
            setPolicyDisplay(getPolicyDisplayData(response.data.policyData.policy_id));
            setStrategicDataState(response.data.strategicData);
            setStrategicDisplayLeft(response.data.strategicData.find(item => item.village.name == villageName));
            setStrategicDisplayRight(response.data.strategicData.find(item => item.village.name == strategicDisplayRight.village.name));
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const BoostVote = () => {
        apiFetch(
            villageAPI,
            {
                request: 'BoostVote',
                proposal_id: currentProposal.proposal_id,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
            setCurrentProposal(response.data.proposalData[currentProposalKey]);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const CancelVote = () => {
        apiFetch(
            villageAPI,
            {
                request: 'CancelVote',
                proposal_id: currentProposal.proposal_id,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
            setCurrentProposal(response.data.proposalData[currentProposalKey]);
            openModal({
                header: 'Confirmation',
                text: response.data.response_message,
                ContentComponent: null,
                onConfirm: null,
            });
        });
    }
    const SubmitVote = (vote) => {
        apiFetch(
            villageAPI,
            {
                request: 'SubmitVote',
                proposal_id: currentProposal.proposal_id,
                vote: vote,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            setProposalDataState(response.data.proposalData);
            setCurrentProposal(response.data.proposalData[currentProposalKey]);
        });
    }
    React.useEffect(() => {
        if (proposalDataState.length && currentProposal === null) {
            setCurrentProposal(proposalDataState[0]);
            setCurrentProposalKey(0);
            setProposalRepAdjustment(proposalDataState[0].votes.reduce((acc, vote) => acc + parseInt(vote.rep_adjustment), 0));
        }
    }, [proposalDataState]);
    return (
        <>
            <div className="kq_container">
                <div className="row first">
                    <div className="column first">
                        <div className="kage_container">
                            <div className="kage_header">
                                <div className="header">Kage</div>
                                <div className="kage_kanji">{getKageKanji(villageName)}</div>
                            </div>

                            {kage.avatar_link &&
                                <div className="kage_avatar_wrapper">
                                    <img className="kage_avatar" src={kage.avatar_link} />
                                </div>
                            }
                            {!kage.avatar_link &&
                                <div className="kage_avatar_wrapper_empty">
                                    <div className="kage_avatar_fill"></div>
                                </div>
                            }
                            <div className="kage_nameplate_wrapper">
                                <div className="kage_nameplate_decoration nw"></div>
                                <div className="kage_nameplate_decoration ne"></div>
                                <div className="kage_nameplate_decoration se"></div>
                                <div className="kage_nameplate_decoration sw"></div>
                                <div className="kage_name">{kage.user_name ? kage.user_name : "---"}</div>
                                <div className="kage_title">{kage.seat_title + " of " + villageName}</div>
                            </div>
                        </div>
                    </div>
                    <div className="column second">
                        <div className="proposal_container">
                            <div className="header">Proposals</div>
                            <div className="content box-primary">
                                <div className="proposal_container_top">
                                    <div className="proposal_container_left">
                                        <svg className="previous_proposal_button" width="25" height="25" viewBox="0 0 100 100" onClick={() => cycleProposal("decrement")}>
                                            <polygon className="previous_proposal_triangle_inner" points="100,0 100,100 35,50" />
                                            <polygon className="previous_proposal_triangle_outer" points="65,0 65,100 0,50" />
                                        </svg>
                                        <div className="previous_proposal_button_label">previous</div>
                                    </div>
                                    <div className="proposal_container_middle">
                                        {currentProposalKey !== null &&
                                            <div className="proposal_count">
                                                PROPOSAL {currentProposalKey + 1} OUT OF {proposalDataState.length}
                                            </div>
                                        }
                                        {currentProposalKey === null &&
                                            <div className="proposal_count">
                                                PROPOSAL 0 OUT OF {proposalDataState.length}
                                            </div>
                                        }
                                        <div className="active_proposal_name_container">
                                            <svg className="proposal_decoration_nw" width="18" height="8">
                                                <polygon points="0,4 4,0 8,4 4,8" fill="#ad9357" />
                                                <polygon points="10,4 14,0 18,4 14,8" fill="#ad9357" />
                                            </svg>
                                            <div className="active_proposal_name">
                                                {currentProposal ? currentProposal.name : "NO ACTIVE PROPOSALs"}
                                            </div>
                                            <svg className="proposal_decoration_se" width="18" height="8">
                                                <polygon points="0,4 4,0 8,4 4,8" fill="#ad9357" />
                                                <polygon points="10,4 14,0 18,4 14,8" fill="#ad9357" />
                                            </svg>
                                        </div>
                                        <div className="active_proposal_timer">
                                            {(currentProposal && currentProposal.vote_time_remaining !== null) && currentProposal.vote_time_remaining}
                                            {(currentProposal && currentProposal.enact_time_remaining !== null) && currentProposal.enact_time_remaining}
                                        </div>
                                    </div>
                                    <div className="proposal_container_right">
                                        <svg className="next_proposal_button" width="25" height="25" viewBox="0 0 100 100" onClick={() => cycleProposal("increment")}>
                                            <polygon className="next_proposal_triangle_inner" points="0,0 0,100 65,50" />
                                            <polygon className="next_proposal_triangle_outer" points="35,0 35,100 100,50" />
                                        </svg>
                                        <div className="next_proposal_button_label">next</div>
                                    </div>
                                </div>
                                <div className="proposal_container_bottom">
                                    {playerSeatState.seat_type == "kage" &&
                                        <>
                                            <div className="proposal_cancel_button_wrapper">
                                            <div className={currentProposal ? "proposal_cancel_button" : "proposal_cancel_button disabled"} onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: "Are you sure you want to cancel this proposal?",
                                                ContentComponent: null,
                                                onConfirm: () => CancelProposal(),
                                            })}>cancel proposal</div>
                                            </div>
                                            {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                <div className="trade_view_button_wrapper alliance"
                                                onClick={
                                                    () => openModal({
                                                        header: 'View trade offer',
                                                        text: '',
                                                        ContentComponent: TradeDisplay,
                                                        componentProps: ({
                                                            viewOnly: true,
                                                            offeringVillageResources: resourceDataState,
                                                            offeringVillageRegions: strategicDisplayLeft.regions,
                                                            offeredResources: offeredResources,
                                                            setOfferedResources: setOfferedResources,
                                                            offeredRegions: offeredRegions,
                                                            setOfferedRegions: setOfferedRegions,
                                                            targetVillageResources: null,
                                                            targetVillageRegions: strategicDisplayRight.regions,
                                                            requestedResources: requestedResources,
                                                            setRequestedResources: setRequestedResources,
                                                            requestedRegions: requestedRegions,
                                                            setRequestedRegions: setRequestedRegions,
                                                        }),
                                                        onConfirm: null,
                                                    })
                                                }>
                                                    <div className="trade_view_button_inner">
                                                        <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                    </div>
                                                </div>
                                            }
                                            <div className="proposal_enact_button_wrapper">
                                                <div className={(currentProposal && (currentProposal.enact_time_remaining !== null ||
                                                    currentProposal.votes.length == seatDataState.filter(seat => seat.seat_type == "elder" && seat.seat_id != null).length
                                            )) ? "proposal_enact_button" : "proposal_enact_button disabled"} onClick={() => openModal({
                                                header: 'Confirmation',
                                                text: 'Are you sure you want to enact this proposal?',
                                                ContentComponent: null,
                                                onConfirm: () => EnactProposal(),
                                            })}>enact proposal</div>
                                                {/*proposalRepAdjustment > 0 &&
                                                <div className="rep_change positive">REPUATION GAIN: +{proposalRepAdjustment}</div>
                                            */}
                                                {proposalRepAdjustment < 0 &&
                                                    <div className="rep_change negative">REPUTATION LOSS: {proposalRepAdjustment}</div>
                                                }
                                            </div>
                                        </>
                                    }
                                    {playerSeatState.seat_type == "elder" &&
                                        <>
                                            {!currentProposal &&
                                                <>
                                                    <div className="proposal_yes_button_wrapper">
                                                        <div className="proposal_yes_button disabled">vote in favor</div>
                                                    </div>
                                                    <div className="proposal_no_button_wrapper">
                                                        <div className="proposal_no_button disabled">vote against</div>
                                                    </div>
                                                </>
                                            }
                                            {(currentProposal && currentProposal.vote_time_remaining != null && !currentProposal.votes.find(vote => vote.user_id == playerID)) &&
                                                <>
                                                    <div className="proposal_yes_button_wrapper">
                                                        <div className="proposal_yes_button" onClick={() => SubmitVote(1)}>vote in favor</div>
                                                    </div>
                                                    {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                        <div className="trade_view_button_wrapper alliance"
                                                            onClick={
                                                                () => openModal({
                                                                    header: 'View trade offer',
                                                                    text: '',
                                                                    ContentComponent: TradeDisplay,
                                                                    componentProps: ({
                                                                        viewOnly: true,
                                                                        offeringVillageResources: resourceDataState,
                                                                        offeringVillageRegions: strategicDisplayLeft.regions,
                                                                        offeredResources: offeredResources,
                                                                        setOfferedResources: setOfferedResources,
                                                                        offeredRegions: offeredRegions,
                                                                        setOfferedRegions: setOfferedRegions,
                                                                        targetVillageResources: null,
                                                                        targetVillageRegions: strategicDisplayRight.regions,
                                                                        requestedResources: requestedResources,
                                                                        setRequestedResources: setRequestedResources,
                                                                        requestedRegions: requestedRegions,
                                                                        setRequestedRegions: setRequestedRegions,
                                                                    }),
                                                                    onConfirm: null,
                                                                })
                                                            }>
                                                            <div className="trade_view_button_inner">
                                                                <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                            </div>
                                                        </div>
                                                    }
                                                    <div className="proposal_no_button_wrapper">
                                                        <div className="proposal_no_button" onClick={() => SubmitVote(0)}>vote against</div>
                                                    </div>
                                                </>
                                            }
                                            {(currentProposal && currentProposal.vote_time_remaining == null && !currentProposal.votes.find(vote => vote.user_id == playerID)) &&
                                                <>
                                                    <div className="proposal_yes_button_wrapper">
                                                        <div className="proposal_yes_button disabled">vote in favor</div>
                                                    </div>
                                                    {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                        <div className="trade_view_button_wrapper alliance"
                                                            onClick={
                                                                () => openModal({
                                                                    header: 'View trade offer',
                                                                    text: '',
                                                                    ContentComponent: TradeDisplay,
                                                                    componentProps: ({
                                                                        viewOnly: true,
                                                                        offeringVillageResources: resourceDataState,
                                                                        offeringVillageRegions: strategicDisplayLeft.regions,
                                                                        offeredResources: offeredResources,
                                                                        setOfferedResources: setOfferedResources,
                                                                        offeredRegions: offeredRegions,
                                                                        setOfferedRegions: setOfferedRegions,
                                                                        targetVillageResources: null,
                                                                        targetVillageRegions: strategicDisplayRight.regions,
                                                                        requestedResources: requestedResources,
                                                                        setRequestedResources: setRequestedResources,
                                                                        requestedRegions: requestedRegions,
                                                                        setRequestedRegions: setRequestedRegions,
                                                                    }),
                                                                    onConfirm: null,
                                                                })
                                                            }>
                                                            <div className="trade_view_button_inner">
                                                                <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                            </div>
                                                        </div>
                                                    }
                                                    <div className="proposal_no_button_wrapper">
                                                        <div className="proposal_no_button disabled">vote against</div>
                                                    </div>
                                                </>
                                            }
                                            {(currentProposal && currentProposal.vote_time_remaining != null && currentProposal.votes.find(vote => vote.user_id == playerID)) &&
                                                <>
                                                    <div className="proposal_cancel_vote_button_wrapper">
                                                        <div className="proposal_cancel_vote_button" 
                                                            onClick={
                                                                () => openModal({
                                                                    header: 'Confirmation',
                                                                    text: null,
                                                                    ContentComponent: null,
                                                                    onConfirm: () => CancelVote(),
                                                                })
                                                            }>
                                                            change vote</div>
                                                    </div>
                                                    {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                        <div className="trade_view_button_wrapper alliance"
                                                            onClick={
                                                                () => openModal({
                                                                    header: 'View trade offer',
                                                                    text: '',
                                                                    ContentComponent: TradeDisplay,
                                                                    componentProps: ({
                                                                        viewOnly: true,
                                                                        offeringVillageResources: resourceDataState,
                                                                        offeringVillageRegions: strategicDisplayLeft.regions,
                                                                        offeredResources: offeredResources,
                                                                        setOfferedResources: setOfferedResources,
                                                                        offeredRegions: offeredRegions,
                                                                        setOfferedRegions: setOfferedRegions,
                                                                        targetVillageResources: null,
                                                                        targetVillageRegions: strategicDisplayRight.regions,
                                                                        requestedResources: requestedResources,
                                                                        setRequestedResources: setRequestedResources,
                                                                        requestedRegions: requestedRegions,
                                                                        setRequestedRegions: setRequestedRegions,
                                                                    }),
                                                                    onConfirm: null,
                                                                })
                                                            }>
                                                            <div className="trade_view_button_inner">
                                                                <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                            </div>
                                                        </div>
                                                    }
                                                    {(currentProposal.votes.find(vote => vote.user_id == playerID).rep_adjustment == 0) &&
                                                        <div className="proposal_boost_vote_button_wrapper">
                                                    <div className="proposal_boost_vote_button" onClick={() => openModal({
                                                        header: 'Confirmation',
                                                        text: "When a vote Against is boosted:\n The Kage will lose 500 Reputation when the proposal is enacted.\n\nWhen a vote In Favor is boosted:\nTotal Reputation loss from Against votes will be reduced by 500.\n\nBoosting a vote will cost 500 Reputation when the proposal is passed.\n\nHowever, a boosted vote In Favor will only cost Reputation if there is a boosted vote Against. If there are more boosted votes In Favor than Against, the cost will be split between between votes In Favor.",
                                                        ContentComponent: null,
                                                        onConfirm: () => BoostVote(),
                                                    })}>boost vote</div>
                                                        </div>
                                                    }
                                                </>
                                            }
                                            {(currentProposal && currentProposal.vote_time_remaining == null && currentProposal.votes.find(vote => vote.user_id == playerID)) &&
                                                <>
                                                    <div className="proposal_cancel_vote_button_wrapper">
                                                        <div className="proposal_cancel_vote_button disabled">cancel vote</div>
                                                    </div>
                                                    {(currentProposal && (currentProposal.type == "offer_trade" || currentProposal.type == "accept_trade")) &&
                                                        <div className="trade_view_button_wrapper alliance"
                                                            onClick={
                                                                () => openModal({
                                                                    header: 'View trade offer',
                                                                    text: '',
                                                                    ContentComponent: TradeDisplay,
                                                                    componentProps: ({
                                                                        viewOnly: true,
                                                                        offeringVillageResources: resourceDataState,
                                                                        offeringVillageRegions: strategicDisplayLeft.regions,
                                                                        offeredResources: offeredResources,
                                                                        setOfferedResources: setOfferedResources,
                                                                        offeredRegions: offeredRegions,
                                                                        setOfferedRegions: setOfferedRegions,
                                                                        targetVillageResources: null,
                                                                        targetVillageRegions: strategicDisplayRight.regions,
                                                                        requestedResources: requestedResources,
                                                                        setRequestedResources: setRequestedResources,
                                                                        requestedRegions: requestedRegions,
                                                                        setRequestedRegions: setRequestedRegions,
                                                                    }),
                                                                    onConfirm: null,
                                                                })
                                                            }>
                                                            <div className="trade_view_button_inner">
                                                                <img src="/images/v2/icons/trade.png" className="trade_view_button_icon" />
                                                            </div>
                                                        </div>
                                                    }
                                                    <div className="proposal_boost_vote_button_wrapper">
                                                        <div className="proposal_boost_vote_button disabled">boost vote</div>
                                                    </div>
                                                </>
                                            }
                                        </>
                                    }
                                </div>
                            </div>
                            <div className="proposal_elder_header">Elders</div>
                            <div className="elder_list">
                                <svg height="0" width="0">
                                    <defs>
                                        <filter id="green_glow">
                                            <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                                            <feFlood floodColor="green" result="floodColor" />
                                            <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                                                <feFuncA type="linear" slope="3" />
                                            </feComponentTransfer>
                                            <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                                            <feMerge>
                                                <feMergeNode in="coloredBlur" />
                                                <feMergeNode in="SourceGraphic" />
                                            </feMerge>
                                        </filter>
                                        <filter id="red_glow">
                                            <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur" />
                                            <feFlood floodColor="red" result="floodColor" />
                                            <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                                                <feFuncA type="linear" slope="2" />
                                            </feComponentTransfer>
                                            <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur" />
                                            <feMerge>
                                                <feMergeNode in="coloredBlur" />
                                                <feMergeNode in="SourceGraphic" />
                                            </feMerge>
                                        </filter>
                                    </defs>
                                </svg>
                                {seatDataState
                                    .filter(elder => elder.seat_type === 'elder')
                                    .map((elder, index) => (
                                        <div key={elder.seat_key} className="elder_item">
                                            <div className="elder_vote_wrapper" style={{ visibility: (currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id)) ? null : "hidden" }}>
                                                <div className="elder_vote">
                                                    {(currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 1 && parseInt(vote.rep_adjustment) > 0)) &&
                                                        <img className="vote_yes_image glow" src="/images/v2/icons/yesvote.png" />
                                                    }
                                                    {(currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 0 && parseInt(vote.rep_adjustment) < 0)) &&
                                                        <img className="vote_no_image glow" src="/images/v2/icons/novote.png" />
                                                    }
                                                    {(currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 1 && parseInt(vote.rep_adjustment) == 0)) &&
                                                        <img className="vote_yes_image" src="/images/v2/icons/yesvote.png" />
                                                    }
                                                    {(currentProposal && currentProposal.votes.find(vote => vote.user_id == elder.user_id && vote.vote == 0 && parseInt(vote.rep_adjustment) == 0)) &&
                                                        <img className="vote_no_image" src="/images/v2/icons/novote.png" />
                                                    }
                                                </div>
                                            </div>
                                            <div className="elder_avatar_wrapper">
                                                {elder.avatar_link && <img className="elder_avatar" src={elder.avatar_link} />}
                                                {!elder.avatar_link && <div className="elder_avatar_fill"></div>}
                                            </div>
                                            <div className="elder_name">{elder.user_name ? <a href={"/?id=6&user=" + elder.user_name}>{elder.user_name}</a> : "---"}</div>
                                        </div>
                                    ))}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="row second">
                    <div className="column first">
                        <div className="header">Village policy</div>
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
                            {displayPolicyID != policyDataState.policy_id &&
                                <div className={playerSeatState.seat_type == "kage" ? "village_policy_change_button" : "village_policy_change_button disabled"} onClick={() => openModal({
                                    header: 'Confirmation',
                                    text: "Are you sure you want to change policies? You will be unable to select a new policy for 3 days.",
                                    ContentComponent: null,
                                    onConfirm: () => ChangePolicy(),
                                })}>change policy</div>
                            }
                            <div className="village_policy_main_container">
                                <div className="village_policy_main_inner">
                                    <div className="village_policy_banner" style={{ backgroundImage: "url(" + policyDisplay.banner + ")" }}></div>
                                    <div className="village_policy_name_container">
                                        <div className={"village_policy_name " + policyDisplay.glowClass}>{policyDisplay.name}</div>
                                    </div>
                                    <div className="village_policy_phrase">{policyDisplay.phrase}</div>
                                    <div className="village_policy_description">{policyDisplay.description}</div>
                                    {displayPolicyID > 1 &&
                                        <div className="village_policy_previous_wrapper">
                                            <svg className="previous_policy_button" width="20" height="20" viewBox="0 0 100 100" onClick={() => cyclePolicy("decrement")}>
                                                <polygon className="previous_policy_triangle_inner" points="100,0 100,100 35,50" />
                                                <polygon className="previous_policy_triangle_outer" points="65,0 65,100 0,50" />
                                            </svg>
                                        </div>
                                    }
                                    {displayPolicyID < 5 &&
                                        <div className="village_policy_next_wrapper">
                                            <svg className="next_policy_button" width="20" height="20" viewBox="0 0 100 100" onClick={() => cyclePolicy("increment")}>
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
                    </div>
                </div>
                <div className="row third">
                    <div className="column first">
                        <div className="kq_navigation_row">
                            <div className="header">Strategic information</div>
                        </div>
                    </div>
                </div>
                <div className="row fourth">
                    <div className="column first">
                        <div className="strategic_info_container">
                            <StrategicInfoItem
                                strategicInfoData={strategicDisplayLeft}
                                getPolicyDisplayData={getPolicyDisplayData}
                            />
                            <div className="strategic_info_navigation">
                                <div className="strategic_info_navigation_diplomacy_buttons">
                                    {strategicDisplayLeft.enemies.find(enemy => enemy == strategicDisplayRight.village.name) ?
                                        <div className="diplomacy_action_button_wrapper war cancel" onClick={() => openModal({
                                            header: 'Confirmation',
                                            text: "Are you sure you want to offer peace with " + strategicDisplayRight.village.name + "?",
                                            ContentComponent: null,
                                            onConfirm: () => OfferPeace(),
                                        })}>
                                            <div className="diplomacy_action_button_inner">
                                            </div>
                                        </div>
                                        :
                                        <div className="diplomacy_action_button_wrapper war" onClick={() => openModal({
                                            header: 'Confirmation',
                                            text: "Are you sure you declare war with " + strategicDisplayRight.village.name + "?",
                                            ContentComponent: null,
                                            onConfirm: () => DeclareWar(),
                                        })}>
                                            <div className="diplomacy_action_button_inner">
                                                <img src="/images/icons/war.png" className="diplomacy_action_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {strategicDisplayLeft.allies.find(ally => ally == strategicDisplayRight.village.name) ?
                                        <div className="diplomacy_action_button_wrapper alliance cancel" onClick={() => openModal({
                                            header: 'Confirmation',
                                            text: "Are you sure you want break an alliance with " + strategicDisplayRight.village.name + "?",
                                            ContentComponent: null,
                                            onConfirm: () => BreakAlliance(),
                                        })}>
                                            <div className="diplomacy_action_button_inner">
                                            </div>
                                        </div>
                                        :
                                        <div className="diplomacy_action_button_wrapper alliance" onClick={() => openModal({
                                            header: 'Confirmation',
                                            text: "Are you sure you want to form an alliance with " + strategicDisplayRight.village.name + "?\nYou can be a member of only one Alliance at any given time.",
                                            ContentComponent: null,
                                            onConfirm: () => OfferAlliance(),
                                        })}>
                                            <div className="diplomacy_action_button_inner">
                                                <img src="/images/icons/ally.png" className="diplomacy_action_button_icon" />
                                            </div>
                                        </div>
                                    }
                                </div>
                                <div className="strategic_info_navigation_village_buttons">
                                    {villageName != "Stone" &&
                                        <div className={strategicDisplayRight.village.village_id == 1 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[0])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(1)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {villageName != "Cloud" &&
                                        <div className={strategicDisplayRight.village.village_id == 2 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[1])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(2)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {villageName != "Leaf" &&
                                        <div className={strategicDisplayRight.village.village_id == 3 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[2])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(3)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {villageName != "Sand" &&
                                        <div className={strategicDisplayRight.village.village_id == 4 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[3])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(4)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                    {villageName != "Mist" &&
                                        <div className={strategicDisplayRight.village.village_id == 5 ? "strategic_info_nav_button_wrapper selected" : "strategic_info_nav_button_wrapper"} onClick={() => setStrategicDisplayRight(strategicDataState[4])}>
                                            <div className="strategic_info_nav_button_inner">
                                                <img src={getVillageIcon(5)} className="strategic_info_nav_button_icon" />
                                            </div>
                                        </div>
                                    }
                                </div>
                                <div className="strategic_info_navigation_diplomacy_buttons">
                                    {strategicDisplayLeft.allies.find(ally => ally == strategicDisplayRight.village.name) &&
                                        <div className="diplomacy_action_button_wrapper alliance"
                                            onClick={
                                                () => openModal({
                                                    header: 'View trade offer',
                                                    text: '',
                                                    ContentComponent: TradeDisplay,
                                                    componentProps: ({
                                                        viewOnly: false,
                                                        offeringVillageResources: resourceDataState,
                                                        offeringVillageRegions: strategicDisplayLeft.regions,
                                                        offeredResources: offeredResources,
                                                        setOfferedResources: setOfferedResources,
                                                        offeredRegions: offeredRegions,
                                                        setOfferedRegions: setOfferedRegions,
                                                        targetVillageResources: null,
                                                        targetVillageRegions: strategicDisplayRight.regions,
                                                        requestedResources: requestedResources,
                                                        setRequestedResources: setRequestedResources,
                                                        requestedRegions: requestedRegions,
                                                        setRequestedRegions: setRequestedRegions,
                                                    }),
                                                    onConfirm: () => OfferTrade(),
                                                })
                                            }>
                                            <div className="diplomacy_action_button_inner">
                                                <img src="/images/v2/icons/trade.png" className="diplomacy_action_button_icon" />
                                            </div>
                                        </div>
                                    }
                                </div>
                            </div>
                            <StrategicInfoItem
                                strategicInfoData={strategicDisplayRight}
                                getPolicyDisplayData={getPolicyDisplayData}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </>
    );

    function cyclePolicy(direction) {
        var newPolicyID;
        switch (direction) {
            case "increment":
                newPolicyID = Math.min(5, displayPolicyID + 1);
                setDisplayPolicyID(newPolicyID);
                setPolicyDisplay(getPolicyDisplayData(newPolicyID));
                break;
            case "decrement":
                newPolicyID = Math.max(1, displayPolicyID - 1);
                setDisplayPolicyID(newPolicyID);
                setPolicyDisplay(getPolicyDisplayData(newPolicyID));
                break;
        }
    }
    function cycleProposal(direction) {
        if (proposalDataState.length == 0) {
            return;
        }
        var newProposalKey;
        switch (direction) {
            case "increment":
                newProposalKey = Math.min(proposalDataState.length - 1, currentProposalKey + 1);
                setCurrentProposalKey(newProposalKey);
                setCurrentProposal(proposalDataState[newProposalKey]);
                setProposalRepAdjustment(proposalDataState[newProposalKey].votes.reduce((acc, vote) => acc + vote.rep_adjustment, 0));
                break;
            case "decrement":
                newProposalKey = Math.max(0, currentProposalKey - 1);
                setCurrentProposalKey(newProposalKey);
                setCurrentProposal(proposalDataState[newProposalKey]);
                setProposalRepAdjustment(proposalDataState[newProposalKey].votes.reduce((acc, vote) => acc + vote.rep_adjustment, 0));
                break;
        }
    }
}
