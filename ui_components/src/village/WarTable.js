import { apiFetch } from "../utils/network.js";

export function WarTable({
    warLogData,
    villageAPI,
    handleErrors,
    getVillageIcon
}) {
    const [playerWarLog, setPlayerWarLog] = React.useState(warLogData.player_war_log);
    const [globalLeaderboardWarLogs, setGlobalLeaderboardWarLogs] = React.useState(warLogData.global_leaderboard_war_logs);
    const [globalLeaderboardPageNumber, setGlobalLeaderboardPageNumber] = React.useState(1);

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
    function WarLog({ log, index, animate, getVillageIcon }) {
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

    return (
        <div className="wartable_container">
            <div className="row first">
                <div className="column first">
                    <div className="header">your war score</div>
                    <div className="player_warlog_container">
                        <WarLogHeader />
                        <WarLog log={playerWarLog} index={0} animate={false} getVillageIcon={getVillageIcon} />
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
                                <WarLog log={log} index={index} animate={true} getVillageIcon={getVillageIcon} />
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
        </div>
    );
}