import { FighterAvatar } from "./FighterAvatar.js";
import { ResourceBar } from "./ResourceBar.js";

const styles = {
    fighterDisplay: {
        display: "flex",
        flexDirection: "row",
        gap: "8px",
    },
    opponent: {
        flexDirection: "row-reverse",
    }
};

export default function FighterDisplay({ fighter, showChakra, isOpponent }) {
    const containerStyles = {
        ...styles.fighterDisplay,
        ...(isOpponent && styles.opponent)
    };

    return (
        <div className='fighterDisplay' style={containerStyles}>
            <FighterAvatar
                fighterName={fighter.name}
                avatarLink={fighter.avatarLink}
                maxAvatarSize={125}
            />
            <div className='resourceBars'>
                <ResourceBar
                    currentAmount={fighter.health}
                    maxAmount={fighter.maxHealth}
                    resourceType="health"
                />
                {showChakra &&
                    <ResourceBar
                        currentAmount={fighter.chakra}
                        maxAmount={fighter.maxChakra}
                        resourceType="chakra"
                    />
                }
            </div>
        </div>
    );
}
