// @flow strict

export function getVillageIcon(village_id) {
    switch (village_id) {
        case 1:
            return '/images/village_icons/stone.png';
        case 2:
            return '/images/village_icons/cloud.png';
        case 3:
            return '/images/village_icons/leaf.png';
        case 4:
            return '/images/village_icons/sand.png';
        case 5:
            return '/images/village_icons/mist.png';
        default:
            return null;
    }
}

export function getVillageBanner(village_id) {
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

export function getPolicyDisplayData(policy_id) {
    let data = {
        banner: "",
        name: "",
        phrase: "",
        description: "",
        bonuses: [],
        penalties: [],
        glowClass: ""
    };

    switch (policy_id) {
        case 0:
            data.banner = "";
            data.name = "Inactive Policy";
            data.phrase = "";
            data.description = "";
            data.bonuses = [];
            data.resources = [];
            data.penalties = [];
            data.glowClass = "";
            break;
        case 1:
            data.banner = "/images/v2/decorations/policy_banners/growthpolicy.jpg";
            data.name = "From the Ashes";
            data.phrase = "bonds forged, courage shared.";
            data.description = "In unity, find the strength to overcome.\nOne village, one heart, one fight.";
            data.bonuses = ["25% increased Caravan speed", "+25 base resource production", "+5% training speed", "50% reduced cost for village transfers"];
            data.penalties = ["-30 Materials/hour", "-50 Food/hour", "-20 Wealth/hour", "Cannot declare War"];
            data.glowClass = "growth_glow";
            break;
        case 2:
            data.banner = "/images/v2/decorations/policy_banners/espionagepolicy.jpg";
            data.name = "Eye of the Storm";
            data.phrase = "half truths, all lies.";
            data.description = "Become informants dealing in truths and lies.\nDeceive, divide and destroy.";
            data.bonuses = ["25% increased Infiltrate speed", "+1 Defense reduction from Infiltrate", "+1 Stealth", "+10 Loot Capacity"];
            data.penalties = ["-25 Materials/hour", "-25 Food/hour", "-50 Wealth/hour"];
            data.glowClass = "espionage_glow";
            break;
        case 3:
            data.banner = "/images/v2/decorations/policy_banners/defensepolicy.jpg";
            data.name = "Fortress of Solitude";
            data.phrase = "vigilant minds, enduring hearts.";
            data.description = "Show the might of will unyielding.\nPrepare, preserve, prevail.";
            data.bonuses = ["25% increased Reinforce speed", "+1 Defense gain from Reinforce", "+1 Scouting", "Increased Patrol strength"];
            data.penalties = ["-45 Materials/hour", "-30 Food/hour", "-25 Wealth/hour"];
            data.glowClass = "defense_glow";
            break;
        case 4:
            data.banner = "/images/v2/decorations/policy_banners/warpolicy.jpg";
            data.name = "Forged in Flames";
            data.phrase = "blades sharp, minds sharper.";
            data.description = "Lead your village on the path of a warmonger.\nFeel no fear, no hesitation, no doubt.";
            data.bonuses = ["25% increased Raid speed", "+1 Defense reduction from Raid", "+1 Village Point from PvP", "Faster Patrol respawn"];
            data.penalties = ["-30 Materials/hour", "-40 Food/hour", "-30 Wealth/hour", "Cannot form Alliances"];
            data.glowClass = "war_glow";
            break;
        case 5:
            data.banner = "/images/v2/decorations/policy_banners/prosperitypolicy.jpg";
            data.name = "The Gilded Hand";
            data.phrase = "golden touch, boundless reach.";
            data.description = "In the art of war, wealth is our canvas.\nBuild empires, foster riches, command respect.";
            data.bonuses = ["25% reduced upkeep cost from Upgrades", "+25 baseline Stability", "+25 maximum Stability", "+25% increased income from PvE"];
            data.resources = ["+40 Materials production / hour", "+70 Food production / hour", "+100 Wealth production / hour"];
            data.penalties = [];
            data.glowClass = "prosperity_glow";
            break;
    }

    return data;
}