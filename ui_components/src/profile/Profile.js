// @flow

import { CharacterAvatar } from "../CharacterAvatar.js";
import type {
    DailyTaskType,
    PlayerDataType,
    PlayerSettingsType,
    PlayerStatsType,
    PlayerAchievementsType
} from "../_schema/userSchema.js";

import RadarNinjaChart from '../charts/Chart.js';

type Props = {|
    +links: {|
        +clan: string,
        +team: string,
        +bloodlinePage: string,
        +buyBloodline: string,
        +buyForbiddenSeal: string,
    |},
    +playerData: PlayerDataType,
    +playerStats: PlayerStatsType,
    +playerSettings: PlayerSettingsType,
    +playerDailyTasks: $ReadOnlyArray<DailyTaskType>,
    +playerAchievements: PlayerAchievementsType,
|};
function Profile({
    links,
    playerData,
    playerStats,
    playerSettings,
    playerDailyTasks,
    playerAchievements,
}: Props) {
    return (
        <div className="profile_container">
            {/* First row */}
            <div className="profile_row_first">
                <StatusAttributes
                    playerData={playerData}
                    links={links}
                    playerSettings={playerSettings}
                />
            </div>
            
            {/* Second row */}
            <div className="profile_row_second">
                <PlayerStats
                    playerData={playerData}
                    playerStats={playerStats}
                />
                <div className="profile_row_second_col2">
                    <PlayerUserRep
                        playerData={playerData}
                    />
                    <PlayerBloodline
                        bloodlinePageUrl={links.bloodlinePage}
                        buyBloodlineUrl={links.buyBloodline}
                        playerData={playerData}
                    />
                    <DailyTasks
                        dailyTasks={playerDailyTasks}
                    />
                </div>
            </div>
            
            <div>
                 <RadarNinjaChart 
                    playerStats={playerStats}
                 />
            </div>

            <div className="profile_row_third">
                <h2>Achievements</h2>
                <PlayerAchievements
                    playerAchievements={playerAchievements}
                />
            </div>
        </div>
    );
}

function StatusAttributes({ playerData, playerSettings, links }) {
    return (
        <div className="status_attributes_wrapper">
            <div className="status_attributes box-primary">
                <div className="name_row ft-c1">
                    <div className="player_avatar_name_container">
                        <div className="profile_avatar_container">
                            <CharacterAvatar
                                imageSrc={playerData.avatar_link}
                                maxWidth={playerData.avatar_size * 0.5}
                                maxHeight={playerData.avatar_size * 0.5}
                                avatarStyle={playerSettings.avatar_style}
                                frameClassNames={["profile_avatar_frame", playerSettings.avatar_frame]}
                            />
                        </div>
                        <div>
                            <h2 className="player_name">{playerData.user_name}</h2>
                            <span className="player_title ft-p">{playerData.rank_name} lvl {playerData.level}</span>
                        </div>
                    </div>
                    <div className="player_badges">
                        <img src="/images/v2/decorations/red_diamond.png" />
                        <img src="/images/v2/decorations/red_diamond.png" />
                        <img src="/images/v2/decorations/red_diamond.png" />
                        <img src="/images/v2/decorations/red_diamond.png" />
                    </div>
                </div>
                <div className="exp_section ft-c3 ft-p ft-small">
                    <div className="exp_bar_container">
                        <div className="exp_bar_fill" style={{width: `${playerData.nextLevelProgressPercent}%`}}></div>
                    </div>
                    <span>TOTAL EXP: {playerData.exp.toLocaleString()}</span>
                    <span>NEXT LEVEL IN {Math.max(playerData.expForNextLevel - playerData.exp, 0).toLocaleString()} EXP</span>
                </div>
                <div className="status_info_sections ft-c3">
                    <div className="status_info_section section1" style={{ flexBasis: "38%" }}>
                        <p>
                            <label>Gender:</label>
                            <span>{playerData.gender}</span>
                        </p>
                        <p>
                            <label>Element{playerData.elements.length > 1 ? "s" : ""}:</label>
                            <span>{playerData.elements.join(", ")}</span>
                        </p>
                        <p>
                            <label>Forbidden Seal:</label>
                            {playerData.forbiddenSealName
                                ? <span>{playerData.forbiddenSealName} ({playerData.forbiddenSealTimeLeft})</span>
                                : <span><a href={links.buyForbiddenSeal}>None</a></span>
                            }

                        </p>
                    </div>
                    <div className="status_info_section section2" style={{ flexBasis: "28%" }}>
                        <p>
                            <label>Money:</label>
                            <span>&yen;{playerData.money.toLocaleString()}</span>
                        </p>
                        <p>
                            <label>Ancient Kunai:</label>
                            <span>{playerData.premiumCredits.toLocaleString()}</span>
                        </p>
                        <p>
                            <label>AK Purchased:</label>
                            <span>{playerData.premiumCreditsPurchased.toLocaleString()}</span>
                        </p>
                    </div>
                    <div className="status_info_section section3" style={{ flexBasis: "34%" }}>
                        <p>
                            <label>Village:</label>{playerData.villageName}
                        </p>
                        {playerData.clanId != null &&
                            <p>
                                <label>Clan:</label>
                                <span><a href={links.clan}>{playerData.clanName}</a></span>
                            </p>
                        }
                        {/*<span>Branch Family</span>*/}
                        <p>
                            <label>Team:</label>
                            <span>{playerData.teamId == null
                                ? "None"
                                : <a href={links.team}>{playerData.teamName}</a>}
                            </span>
                        </p>

                    </div>
                </div>
            </div>
        </div>
    );
}

function PlayerStats({ playerData, playerStats }) {
    const totalStatsPercent = Math.round((playerData.totalStats / playerData.totalStatCap) * 1000) / 10;
    return (
        <div className="stats_container">
            <h2>Character stats</h2>
            <div className="total_stats box-primary">
                <span className="ft-c3">Total stats trained: {playerData.totalStats.toLocaleString()} / {playerData.totalStatCap.toLocaleString()}</span>
                <div className="progress_bar_container total_stats_bar_container">
                    <div className="progress_bar_fill" style={{width: `${totalStatsPercent}%`}}></div>
                </div>
            </div>
            <div className="stat_lists">
                <div className="stat_list skills">
                    <div className="stat box-secondary">
                        <h3>Ninjutsu skill: {playerStats.ninjutsuSkill.toLocaleString()}</h3>
                        <div className="badge">忍術</div>
                        <span className="ft-c3">Focuses on the use of hand signs and chakra based/elemental attacks.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Taijutsu skill: {playerStats.taijutsuSkill.toLocaleString()}</h3>
                        <div className="badge">体術</div>
                        <span className="ft-c3">Focuses on the use of hand to hand combat and various weapon effects.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Genjutsu skill: {playerStats.genjutsuSkill.toLocaleString()}</h3>
                        <div className="badge">幻術</div>
                        <span className="ft-c3">Focuses on the use of illusions and high residual damage.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Bloodline skill: {playerStats.bloodlineSkill.toLocaleString()}</h3>
                        <div className="badge">血継</div>
                        <span className="ft-c3">Increases the control over one's bloodline.<br />
                        Helps with its mastery.</span>
                    </div>
                </div>
                <div className="stat_list attributes">
                    <div className="stat box-secondary">
                        <h3>Cast speed: {playerStats.castSpeed.toLocaleString()}</h3>
                        <div className="badge">印術</div>
                        <span className="ft-c3">Increases Ninjutsu/Genjutsu attack speed, affecting damage dealt and received.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Speed: {playerStats.speed.toLocaleString()}</h3>
                        <div className="badge">速度</div>
                        <span className="ft-c3">Increases Taijutsu attack speed, affecting damage dealt and received.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Intelligence: {playerStats.intelligence.toLocaleString()}</h3>
                        <div className="badge">知能</div>
                        <span className="ft-c3"></span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Willpower: {playerStats.willpower.toLocaleString()}</h3>
                        <div className="badge">根性</div>
                        <span className="ft-c3"></span>
                    </div>
                </div>
            </div>
        </div>
    );
}

type PlayerBloodlineProps = {|
    +playerData: PlayerDataType,
    +bloodlinePageUrl: string,
    +buyBloodlineUrl: string,
|};
function PlayerBloodline({ playerData, bloodlinePageUrl, buyBloodlineUrl }) {
    return (
        <div className="bloodline_display">
            <div className="bloodline_mastery_indicator">
                {playerData.has_bloodline
                    ? <img src="/images/v2/bloodline/level3.png" />
                    : <img src="/images/v2/bloodline/inactive.png" />
                }
            </div>
            <div className="bloodline_name ft-c3">
                Bloodline:&nbsp;{playerData.bloodlineName != null
                    ? <a href={bloodlinePageUrl}>{playerData.bloodlineName.replace(/&#039;/g, "'")}</a>
                    : <a href={buyBloodlineUrl}>None</a>}
            </div>
        </div>
    );
}

type PlayerUserRepProps = {|
    +playerData: PlayerDataType
|};
function PlayerUserRep({playerData}: PlayerUserRepProps) {
    let img_link = "images/village_icons/" + playerData.villageName.toLowerCase() + ".png";
    return (
        <div className="reputation_display">
            <div className="reputation_indicator">
                <img src={img_link} />
                <span className="village_name">{playerData.villageName}</span>
            </div>
            <div className="reputation_info ft-c3">
                <span className="reputation_name">
                    <b>{playerData.villageRepTier}</b>&nbsp;({playerData.villageRep} rep)
                </span>
                <span className="weekly_reputation">
                    {playerData.weeklyRep}/{playerData.maxWeeklyRep} PvE
                    &nbsp;|&nbsp;&nbsp;
                    {playerData.weeklyPvpRep}/{playerData.maxWeeklyPvpRep} PvP
                </span>
            </div>
        </div>
    )
}

type DailyTasksProps = {|
    +dailyTasks: $ReadOnlyArray<DailyTaskType>,
|};
function DailyTasks({ dailyTasks }: DailyTasksProps) {
    return (
        <div className="daily_tasks_container">
            <h2>Daily tasks</h2>
            {dailyTasks.map(((dailyTask, i) => (
                <div key={`daily_task:${i}`} className="daily_task">
                    <h3>{dailyTask.name}</h3>
                    <section className="ft-small ft-c3 prompt_rewards">
                        <span>{dailyTask.prompt}</span>
                        <p style={{margin: 0, textAlign: "right"}}>
                            <span>&yen;{dailyTask.rewardYen}</span><br />
                            <span>{dailyTask.rewardRep} rep</span>
                        </p>
                    </section>
                    <section className="ft-small ft-c1">
                        <div className="progress_bar_container dark">
                            <div className="progress_bar_fill" style={{width: `${dailyTask.progressPercent}%`}}></div>
                        </div>
                        <span style={{marginLeft: "6px"}}>{dailyTask.progressCaption}</span>
                    </section>
                </div>
            )))}
        </div>
    )
}

type PlayerAchievementsProps = {|
    +playerAchievements: PlayerAchievementsType,
|};
function PlayerAchievements({ playerAchievements }: PlayerAchievementsProps) {
    return (
        <div className="achievements_container">
            {playerAchievements.completedAchievements.map(achievement => (
                <div key={`achievement:${achievement.id}`} className="achievement completed box-secondary">
                    <span className="achievement_name">{achievement.name}</span>
                    <span className="achievement_prompt">{achievement.prompt}</span>
                    <div className="achievement_progress">
                        <div className="progress_bar_container">
                            <div className="progress_bar_fill" style={{width: `${achievement.progressPercent}%`}}></div>
                        </div>
                        <span className="progress_label">{achievement.progressLabel}</span>
                    </div>
                </div>
            ))}
        </div>
    );
}

window.Profile = Profile;