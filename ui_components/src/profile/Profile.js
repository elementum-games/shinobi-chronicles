// @flow

import { CharacterAvatar } from "../CharacterAvatar.js";
import type { PlayerDataType, PlayerSettingsType, PlayerStatsType } from "../_schema/userSchema.js";

type Props = {|
    +playerData: PlayerDataType,
    +playerStats: PlayerStatsType,
    +playerSettings: PlayerSettingsType,
|};
function Profile({
    playerData,
    playerStats,
    playerSettings,
}: Props) {
    return (
        <div className="profile_container">
            {/* First row */}
            <div className="profile_avatar_container">
                <CharacterAvatar
                    imageSrc={playerData.avatar_link}
                    maxWidth={playerData.avatar_size}
                    maxHeight={playerData.avatar_size}
                    avatarStyle={playerSettings.avatar_style}
                />
            </div>
            <StatusAttributes
                playerData={playerData}
            />
            {/* Second row */}
            <PlayerStats
                playerData={playerData}
                playerStats={playerStats}
            />
        </div>
    );
}

function StatusAttributes({ playerData }) {
    return (
        <div className="status_attributes box-primary">
            <div className="name_row ft-c1">
                <div>
                    <h2 className="player_name">{playerData.user_name}</h2>
                    <span className="player_title ft-p">{playerData.rank_name} lvl {playerData.level}</span>
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
                <span>TOTAL EXP: {playerData.exp}</span>
                <span>NEXT LEVEL IN {Math.max(playerData.expForNextLevel - playerData.exp, 0)} EXP</span>
            </div>
            <div className="status_info_sections ft-c3">
                <div className="status_info_section" style={{width: 220}}>
                    <span>Gender: {playerData.gender}</span>
                    <span>Element: {playerData.elements.join(", ")}</span>
                    <span>Money: {playerData.money} yen</span>
                </div>
                <div className="status_info_section" style={{width: 170}}>
                    <span>Village: {playerData.villageName}</span>
                    {playerData.clanId != null &&
                        <span>Clan: {playerData.clanName}</span>
                    }
                    <span>Ancient Kunai: {playerData.premiumCredits}</span>
                </div>
                <div className="status_info_section" style={{width: 230}}>
                    <span>Team: {playerData.teamId == null
                        ? "None"
                        : playerData.teamName}
                    </span>
                    {/*<span>Branch Family</span>*/}
                    <span>Forbidden Seal: {playerData.forbiddenSealName}</span>
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
                <span className="ft-c3">Total stats trained: {playerData.totalStats} / {playerData.totalStatCap}</span><br />
                <div className="total_stats_bar_container">
                    <div className="total_stats_bar_fill" style={{width: `${totalStatsPercent}%`}}></div>
                </div>
            </div>
            <div className="stat_list skills">
                <div className="stat box-secondary">
                    <h3>Ninjutsu skill: {playerStats.ninjutsuSkill}</h3>
                    <div className="badge">忍術</div>
                    <span className="ft-c3">Focuses on the use of hand signs and chakra based/elemental attacks.</span>
                </div>
                <div className="stat box-secondary">
                    <h3>Taijutsu skill: {playerStats.taijutsuSkill}</h3>
                    <div className="badge">体術</div>
                    <span className="ft-c3">Focuses on the use of hand to hand combat and various weapon effects.</span>
                </div>
                <div className="stat box-secondary">
                    <h3>Genjutsu skill: {playerStats.genjutsuSkill}</h3>
                    <div className="badge">幻術</div>
                    <span className="ft-c3">Focuses on the use of illusions and high residual damage.</span>
                </div>
                <div className="stat box-secondary">
                    <h3>Bloodline skill: {playerStats.bloodlineSkill}</h3>
                    <div className="badge">血継</div>
                    <span className="ft-c3">Increases the control over one's bloodline.<br />
                        Helps with its mastery.</span>
                </div>
            </div>
            <div className="stat_list attributes">
                <div className="stat box-secondary">
                    <h3>Cast speed: {playerStats.castSpeed}</h3>
                    <div className="badge">印術</div>
                    <span className="ft-c3">Increases Ninjutsu/Genjutsu attack speed, affecting damage dealt and received.</span>
                </div>
                <div className="stat box-secondary">
                    <h3>Speed: {playerStats.speed}</h3>
                    <div className="badge">速度</div>
                    <span className="ft-c3">Increases Taijutsu attack speed, affecting damage dealt and received.</span>
                </div>
                <div className="stat box-secondary">
                    <h3>Intelligence: {playerStats.intelligence}</h3>
                    <div className="badge">知能</div>
                    <span className="ft-c3">lol</span>
                </div>
                <div className="stat box-secondary">
                    <h3>Willpower: {playerStats.willpower}</h3>
                    <div className="badge">根性</div>
                    <span className="ft-c3">nope</span>
                </div>
            </div>
        </div>
    );
}

window.Profile = Profile;