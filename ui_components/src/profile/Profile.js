// @flow

import { CharacterAvatar } from "../CharacterAvatar.js";
import type { PlayerDataType, PlayerSettingsType } from "../_schema/userSchema.js";

type Props = {|
    +playerData: PlayerDataType,
    +playerSettings: PlayerSettingsType,
|};
function Profile({
    playerData,
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
            <div className="status_attributes box-primary">

            </div>
            {/* Second row */}
            <div className="stats_container">
                <h2>Character stats</h2>
                <div className="total_stats box-primary">
                    <span className="ft-c3">Total stats trained: 25 000 / 25 000</span><br />
                    [resource bar]
                </div>
                <div className="stat_list skills">
                    <div className="stat box-secondary">
                        <h3>Ninjutsu skill: 138</h3>
                        <div className="badge">忍術</div>
                        <span className="ft-c3">Focuses on the use of hand signs and chakra based/elemental attacks.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Taijutsu skill: 138</h3>
                        <div className="badge">体術</div>
                        <span className="ft-c3">Focuses on the use of hand to hand combat and various weapon effects.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Genjutsu skill: 138</h3>
                        <div className="badge">幻術</div>
                        <span className="ft-c3">Focuses on the use of illusions and high residual damage.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Bloodline skill: 138</h3>
                        <div className="badge">血継</div>
                        <span className="ft-c3">Increases the control over one's bloodline.<br />
                        Helps with its mastery.</span>
                    </div>
                </div>
                <div className="stat_list attributes">
                    <div className="stat box-secondary">
                        <h3>Cast speed: 138</h3>
                        <div className="badge">印術</div>
                        <span className="ft-c3">Increases Ninjutsu/Genjutsu attack speed, affecting damage dealt and received.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Speed: 138</h3>
                        <div className="badge">速度</div>
                        <span className="ft-c3">Increases Taijutsu attack speed, affecting damage dealt and received.</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Intelligence: 138</h3>
                        <div className="badge">知能</div>
                        <span className="ft-c3">lol</span>
                    </div>
                    <div className="stat box-secondary">
                        <h3>Willpower: 138</h3>
                        <div className="badge">根性</div>
                        <span className="ft-c3">nope</span>
                    </div>
                </div>
            </div>
        </div>
    );
}

window.Profile = Profile;