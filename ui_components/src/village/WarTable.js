import { apiFetch } from "../utils/network.js";
import { ModalProvider } from "../utils/modalContext.js";

export function WarTable({
    playerWarLogData,
    warRecordData,
    strategicDataState,
    villageAPI,
    handleErrors,
    getVillageIcon,
    getPolicyDisplayData
}) {
    const [playerWarLog, setPlayerWarLog] = React.useState(playerWarLogData.player_war_log);
    const [globalLeaderboardWarLogs, setGlobalLeaderboardWarLogs] = React.useState(playerWarLogData.global_leaderboard_war_logs);
    const [globalLeaderboardPageNumber, setGlobalLeaderboardPageNumber] = React.useState(1);
    const [warRecords, setWarRecords] = React.useState(warRecordData.war_records);
    const [warRecordsPageNumber, setWarRecordsPageNumber] = React.useState(1);
    const [selectedWarRecord, setSelectedWarRecord] = React.useState(null);
    function getVillageBanner(village_id) {
        switch (village_id) {
            case 1:
                return '/images/v2/decorations/strategic_banners/stratbannerstone.jpg';
            case 2:
                return '/images/v2/decorations/strategic_banners/stratbannercloud.jpg';
            case 3:
                return '/images/v2/decorations/strategic_banners/stratbannerleaf.jpg';
            case 4:
                return '/images/v2/decorations/strategic_banners/stratbannersand.jpg';
            case 5:
                return '/images/v2/decorations/strategic_banners/stratbannermist.jpg';
            default:
                return null;
        }
    }

    const GlobalLeaderboardNextPage = (page_number) => {
        apiFetch(
            villageAPI,
            {
                request: 'GetGlobalWarLeaderboard',
                page_number: page_number,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.warLogData.global_leaderboard_war_logs.length == 0) {
                return;
            } else {
                setGlobalLeaderboardPageNumber(page_number);
                setGlobalLeaderboardWarLogs(response.data.warLogData.global_leaderboard_war_logs);
            }
        });
    }
    const GlobalLeaderboardPreviousPage = (page_number) => {
        if (page_number > 0) {
            apiFetch(
                villageAPI,
                {
                    request: 'GetGlobalWarLeaderboard',
                    page_number: page_number,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setGlobalLeaderboardPageNumber(page_number);
                setGlobalLeaderboardWarLogs(response.data.warLogData.global_leaderboard_war_logs);
            });
        }
    }
    const WarRecordsNextPage = (page_number) => {
        apiFetch(
            villageAPI,
            {
                request: 'GetWarRecords',
                page_number: page_number,
            }
        ).then((response) => {
            if (response.errors.length) {
                handleErrors(response.errors);
                return;
            }
            if (response.data.warRecordData.war_records.length == 0) {
                return;
            } else {
                setWarRecordsPageNumber(page_number);
                setWarRecords(response.data.warRecordData.war_records);
            }
        });
    }
    const WarRecordsPreviousPage = (page_number) => {
        if (page_number > 0) {
            apiFetch(
                villageAPI,
                {
                    request: 'GetWarRecords',
                    page_number: page_number,
                }
            ).then((response) => {
                if (response.errors.length) {
                    handleErrors(response.errors);
                    return;
                }
                setWarRecordsPageNumber(page_number);
                setWarRecords(response.data.warRecordData.war_records);
            });
        }
    }

    return (
        <div className="wartable_container">
            <div className="row first">
                <div className="column first">
                    <div className="header">your war score</div>
                    <div className="player_warlog_container">
                        <WarLogHeader />
                        <PlayerWarLog log={playerWarLog} index={0} animate={false} getVillageIcon={getVillageIcon} />
                    </div>
                </div>
            </div>
            <div className="row second">
                <div className="column first">
                    <div className="header">global war score</div>
                    <div className="global_leaderboard_container">
                        <div className="warlog_label_row">
                            <div className="warlog_username_label"></div>
                            <div className="warlog_war_score_label">war score</div>
                            <div className="warlog_pvp_wins_label">pvp wins</div>
                            <div className="warlog_raid_label">raid</div>
                            <div className="warlog_reinforce_label">reinforce</div>
                            <div className="warlog_infiltrate_label">infiltrate</div>
                            <div className="warlog_defense_label">def</div>
                            <div className="warlog_captures_label">captures</div>
                            <div className="warlog_patrols_label">patrols</div>
                            <div className="warlog_resources_label">resources</div>
                            <div className="warlog_chart_label"></div>
                        </div>
                        {globalLeaderboardWarLogs
                            .map((log, index) => (
                                <PlayerWarLog log={log} index={index} animate={true} getVillageIcon={getVillageIcon} />
                            ))}
                    </div>
                    <div className="global_leaderboard_navigation">
                        <div className="global_leaderboard_navigation_divider_left">
                            <svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg>
                        </div>
                        <div className="global_leaderboard_pagination_wrapper">
                            {globalLeaderboardPageNumber > 1 && <a className="global_leaderboard_pagination" onClick={() => GlobalLeaderboardPreviousPage(globalLeaderboardPageNumber - 1)}>{"<< Prev"}</a>}
                        </div>
                        <div className="global_leaderboard_navigation_divider_middle"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
                        <div className="global_leaderboard_pagination_wrapper">
                            <a className="global_leaderboard_pagination" onClick={() => GlobalLeaderboardNextPage(globalLeaderboardPageNumber + 1)}>{"Next >>"}</a>
                        </div>
                        <div className="global_leaderboard_navigation_divider_right"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
                    </div>
                </div>
            </div>
            <div className="row third">
                <div className="column first">
                    <svg height="0" width="0">
                        <defs>
                            <filter id="war_record_hover">
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
                    <div className="header">war records</div>
                    <div className="war_records_container">
                        {warRecords
                            .map((record, index) => (
                                <WarRecord record={record} index={index} getVillageIcon={getVillageIcon} getVillageBanner={getVillageBanner} />
                            ))}
                    </div>
                    <div className="global_leaderboard_navigation">
                        <div className="global_leaderboard_navigation_divider_left">
                            <svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg>
                        </div>
                        <div className="global_leaderboard_pagination_wrapper">
                            {warRecordsPageNumber > 1 && <a className="global_leaderboard_pagination" onClick={() => WarRecordsPreviousPage(warRecordsPageNumber - 1)}>{"<< Prev"}</a>}
                        </div>
                        <div className="global_leaderboard_navigation_divider_middle"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
                        <div className="global_leaderboard_pagination_wrapper">
                            <a className="global_leaderboard_pagination" onClick={() => WarRecordsNextPage(warRecordsPageNumber + 1)}>{"Next >>"}</a>
                        </div>
                        <div className="global_leaderboard_navigation_divider_right"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
                    </div>
                    {selectedWarRecord &&
                        <div className="village_warlog_container">
                            <VillageWarLog
                                log={selectedWarRecord.attacker_war_log}
                                getVillageIcon={getVillageIcon}
                                animate={true}
                                is_attacker={true}
                                getPolicyDisplayData={getPolicyDisplayData}
                                strategicDataState={strategicDataState}
                            />
                            <VillageWarLog
                                log={selectedWarRecord.defender_war_log}
                                getVillageIcon={getVillageIcon}
                                animate={true}
                                is_attacker={false}
                                getPolicyDisplayData={getPolicyDisplayData}
                                strategicDataState={strategicDataState}
                            />
                        </div>
                    }
                </div>
            </div>
        </div>
    );

    function WarLogHeader() {
        return (
            <div className="warlog_label_row">
                <div className="warlog_username_label"></div>
                <div className="warlog_war_score_label">war score</div>
                <div className="warlog_pvp_wins_label">pvp wins</div>
                <div className="warlog_raid_label">raid</div>
                <div className="warlog_reinforce_label">reinforce</div>
                <div className="warlog_infiltrate_label">infiltrate</div>
                <div className="warlog_defense_label">def</div>
                <div className="warlog_captures_label">captures</div>
                <div className="warlog_patrols_label">patrols</div>
                <div className="warlog_resources_label">resources</div>
                <div className="warlog_chart_label"></div>
            </div>
        );
    }
    function PlayerWarLog({ log, index, animate, getVillageIcon }) {
        const scoreData = [
            { name: 'Objective Score', score: log.objective_score },
            { name: 'Resource Score', score: log.resource_score },
            { name: 'Battle Score', score: log.battle_score }
        ];
        const chart_colors = ['#2b5fca', '#5fca8c', '#d64866'];
        return (
            <div key={index} className="warlog_item">
                <div className="warlog_data_row">
                    {log.rank == 1 &&
                        <span className="warlog_rank_wrapper"><span className="warlog_rank first">{log.rank}</span></span>
                    }
                    {log.rank == 2 &&
                        <span className="warlog_rank_wrapper"><span className="warlog_rank second">{log.rank}</span></span>
                    }
                    {log.rank == 3 &&
                        <span className="warlog_rank_wrapper"><span className="warlog_rank third">{log.rank}</span></span>
                    }
                    {log.rank > 3 &&
                        <span className="warlog_rank_wrapper"><span className="warlog_rank">{log.rank}</span></span>
                    }
                    <div className="warlog_username">
                        <span><img src={getVillageIcon(log.village_id)} /></span>
                        <a href={"/?id=6&user=" + log.user_name}>{log.user_name}</a>
                    </div>
                    <div className="warlog_war_score">{log.war_score}</div>
                    <div className="warlog_pvp_wins">{log.pvp_wins}</div>
                    <div className="warlog_raid">
                        <span>{log.raid_count}</span>
                        <span className="warlog_red">({log.damage_dealt})</span>
                    </div>
                    <div className="warlog_reinforce">
                        <span>{log.reinforce_count}</span>
                        <span className="warlog_green">({log.damage_healed})</span>
                    </div>
                    <div className="warlog_infiltrate">{log.infiltrate_count}</div>
                    <div className="warlog_defense">
                        <span className="warlog_green">+{log.defense_gained}</span>
                        <span className="warlog_red">-{log.defense_reduced}</span>
                    </div>
                    <div className="warlog_captures">{log.villages_captured + log.regions_captured}</div>
                    <div className="warlog_patrols">{log.patrols_defeated}</div>
                    <div className="warlog_resources">{log.resources_stolen}</div>
                    <div className="warlog_chart">
                        <Recharts.PieChart width={50} height={50}>
                            <Recharts.Pie isAnimationActive={animate} stroke="none" data={scoreData} dataKey="score" outerRadius={16} fill="green">
                                {scoreData.map((entry, index) => (
                                    <Recharts.Cell key={`cell-${index}`} fill={chart_colors[index % chart_colors.length]} />
                                ))}
                            </Recharts.Pie>
                        </Recharts.PieChart>
                        <div className="warlog_chart_tooltip">
                            <div className="warlog_chart_tooltip_row">
                                <svg width="12" height="12">
                                    <rect width="100" height="100" fill="#2b5fca" />
                                </svg>
                                <div>Objective score ({Math.round((log.objective_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                            </div>
                            <div className="warlog_chart_tooltip_row">
                                <svg width="12" height="12">
                                    <rect width="100" height="100" fill="#5fca8c" />
                                </svg>
                                <div>Resource score ({Math.round((log.resource_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                            </div>
                            <div className="warlog_chart_tooltip_row">
                                <svg width="12" height="12">
                                    <rect width="100" height="100" fill="#d64866" />
                                </svg>
                                <div>Battle score ({Math.round((log.battle_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    function WarRecord({ record, index, getVillageIcon, getVillageBanner }) {
        const is_active = record.village_relation.relation_end ? false : true;
        const renderScoreBar = () => {
            const total_score = record.attacker_war_log.war_score + record.defender_war_log.war_score;
            const attacker_score_percentage = Math.round((record.attacker_war_log.war_score / total_score) * 100);
            return (
                <div className="war_record_score_bar">
                    <div className="war_record_score_bar_attacker"
                        style={{
                            width: attacker_score_percentage + "%",
                        }}>
                    </div>
                    <div className="war_record_score_bar_defender"
                        style={{
                            width: (100 - attacker_score_percentage) + "%",
                        }}>
                    </div>
                    <svg className="war_record_score_divider" viewBox="0 0 200 200" width="7" height="7" style={{ paddingBottom: "1px", left: (attacker_score_percentage - 1) + "%" }}>
                        <defs>
                            <linearGradient id="war_record_score_divider_gradient">
                                <stop stopColor="#f8de97" offset="0%" />
                                <stop stopColor="#bfa458" offset="100%" />
                            </linearGradient>
                            <filter id="record_name_glow">
                                <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur"></feGaussianBlur>
                                <feFlood floodColor="#ac3b3b" result="floodColor"></feFlood>
                                <feComponentTransfer in="blur" result="opacityAdjustedBlur">
                                    <feFuncA type="linear" slope="2"></feFuncA>
                                </feComponentTransfer>
                                <feComposite in="floodColor" in2="opacityAdjustedBlur" operator="in" result="coloredBlur"></feComposite>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"></feMergeNode>
                                    <feMergeNode in="SourceGraphic"></feMergeNode>
                                </feMerge>
                            </filter>
                        </defs>
                        <polygon points="0,0 0,25 40,25 40,175 0,175 0,200 200,200 200,175 160,175 160,25 200,25 200,0" fill="url(#war_record_score_divider_gradient)" stroke="#4d401c" strokeWidth="20"></polygon>
                    </svg>
                </div>
            );
        }
        return (
            <div key={index} className={"war_record" + (selectedWarRecord && (record.village_relation.relation_id == selectedWarRecord.village_relation.relation_id) ? " selected" : "")} onClick={() => setSelectedWarRecord(record)}
                style={{
                    background: `linear-gradient(to right, transparent 0%, #17161b 30%, #17161b 70%, transparent 100%), url('${getVillageBanner(record.village_relation.village1_id)}'), url('${getVillageBanner(record.village_relation.village2_id)}')`,
                    backgroundPosition: "center, -20% center, 115% center",
                    backgroundSize: "cover, auto, auto",
                    backgroundRepeat: "no-repeat"
                }}>
                <div className="war_record_village left">
                    <div className="war_record_village_inner">
                        <img src={getVillageIcon(record.village_relation.village1_id)} />
                    </div>
                </div>
                <div className="war_record_details_container">
                    <div className={"war_record_relation_name" + (is_active ? " active" : " inactive")}>{record.village_relation.relation_name}</div>
                    <div className="war_record_label_row">
                        <div className={"war_record_score left" + (is_active ? " active" : " inactive")}>{record.attacker_war_log.war_score}</div>
                        <div className={"war_record_status" + (is_active ? " active" : " inactive")}>
                            {record.village_relation.relation_end ?
                                <>
                                    {record.village_relation.relation_start + " - " + record.village_relation.relation_end}
                                </>
                                :
                                <>
                                    {"war active"}
                                </>
                            }
                        </div>
                        <div className={"war_record_score right" + (is_active ? " active" : " inactive")}>{record.defender_war_log.war_score}</div>
                    </div>
                    {renderScoreBar()}
                </div>
                <div className="war_record_village right">
                    <div className="war_record_village_inner">
                        <img src={getVillageIcon(record.village_relation.village2_id)} />
                    </div>
                </div>
            </div>
        );
    }

    function VillageWarLog({ log, getVillageIcon, animate, is_attacker, getPolicyDisplayData, strategicDataState }) {
        console.log(strategicDataState.find(item => item.village.name == "Stone"));
        const policy_name = getPolicyDisplayData(strategicDataState.find(item => item.village.name == log.village_name).village.policy_id).name;
        const scoreData = [
            { name: 'Objective Score', score: log.objective_score },
            { name: 'Resource Score', score: log.resource_score },
            { name: 'Battle Score', score: log.battle_score }
        ];
        const chart_colors = ['#2b5fca', '#5fca8c', '#d64866'];
        return (
            <div className="village_warlog">
                <div className="village_warlog_header">
                    <img src={getVillageIcon(log.village_id)} />
                    <div className="village_warlog_header_name">{log.village_name}</div>
                    <div className="village_warlog_header_policy">{policy_name}</div>
                    <div className={"village_warlog_header_war_score" + (is_attacker ? " attacker" : " defender")}>{log.war_score}</div>
                </div>
                <div className="village_warlog_chart_row">
                    <div className="village_warlog_chart">
                        <Recharts.PieChart width={150} height={150}>
                            <Recharts.Pie isAnimationActive={animate} stroke="none" data={scoreData} dataKey="score" outerRadius={75} fill="green">
                                {scoreData.map((entry, index) => (
                                    <Recharts.Cell key={`cell-${index}`} fill={chart_colors[index % chart_colors.length]} />
                                ))}
                            </Recharts.Pie>
                        </Recharts.PieChart>
                    </div>
                    <div className="village_warlog_chart_breakdown">
                        <div className="village_warlog_chart_breakdown_row">
                            <svg width="18" height="18">
                                <rect width="100" height="100" fill="#2b5fca" />
                            </svg>
                            <div>Objective score ({Math.round((log.objective_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                        </div>
                        <div className="village_warlog_chart_breakdown_row">
                            <svg width="18" height="18">
                                <rect width="100" height="100" fill="#5fca8c" />
                            </svg>
                            <div>Resource score ({Math.round((log.resource_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                        </div>
                        <div className="village_warlog_chart_breakdown_row">
                            <svg width="18" height="18">
                                <rect width="100" height="100" fill="#d64866" />
                            </svg>
                            <div>Battle score ({Math.round((log.battle_score / Math.max(log.war_score, 1)) * 100)}%)</div>
                        </div>
                    </div>
                </div>
                <div className="village_warlog_details">
                    <div className="village_warlog_details_item">
                        <div className="village_warlog_details_label">PVP WINS</div>
                        <div className="village_warlog_details_value">{log.pvp_wins}</div>
                    </div>
                    <div className="village_warlog_details_item">
                        <div className="village_warlog_details_label">RAID</div>
                        <div className="village_warlog_details_value">
                            <span>{log.raid_count}</span>
                            <span className="warlog_red">({log.damage_dealt})</span>
                        </div>
                    </div>
                    <div className="village_warlog_details_item">
                        <div className="village_warlog_details_label">REINFORCE</div>
                        <div className="village_warlog_details_value">
                            <span>{log.reinforce_count}</span>
                            <span className="warlog_green">({log.damage_healed})</span>
                        </div>
                    </div>
                    <div className="village_warlog_details_item">
                        <div className="village_warlog_details_label">INFILTRATE</div>
                        <div className="village_warlog_details_value">{log.infiltrate_count}</div>
                    </div>
                    <div className="village_warlog_details_item">
                        <div className="village_warlog_details_label">DEF</div>
                        <div className="village_warlog_details_value">
                            <span className="warlog_green">+{log.defense_gained}</span>
                            <span className="warlog_red">-{log.defense_reduced}</span>
                        </div>
                    </div>
                    <div className="village_warlog_details_item">
                        <div className="village_warlog_details_label">CAPTURES</div>
                        <div className="village_warlog_details_value">{log.regions_captured + log.villages_captured}</div>
                    </div>
                    <div className="village_warlog_details_item">
                        <div className="village_warlog_details_label">PATROLS</div>
                        <div className="village_warlog_details_value">{log.patrols_defeated}</div>
                    </div>
                    <div className="village_warlog_details_item">
                        <div className="village_warlog_details_label">RESOURCES</div>
                        <div className="village_warlog_details_value">{log.resources_stolen}</div>
                    </div>
                </div>
            </div>
        );
    }
}