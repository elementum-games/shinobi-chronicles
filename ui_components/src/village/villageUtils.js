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
            data.bonuses = ["25% increased Repair speed", "5% increase to Construction / Research speed for each village with greater progress", "50% reduced cost for village transfers"];
            data.resources = ["+70 Materials production / hour", "+100 Food production / hour", "+40 Wealth production / hour"];
            data.penalties = ["Cannot declare War"];
            data.glowClass = "growth_glow";
            break;
        case 2:
            data.banner = "/images/v2/decorations/policy_banners/espionagepolicy.jpg";
            data.name = "Eye of the Storm";
            data.phrase = "half truths, all lies.";
            data.description = "Become informants dealing in truths and lies.\nDeceive, divide and destroy.";
            data.bonuses = ["25% increased Infiltrate speed", "+1 Defense reduction from Infiltrating", "+1 Stability reduction from Infiltrating", "+1 Stealth"];
            data.resources = ["+70 Materials production / hour", "+40 Food production / hour", "+100 Wealth production / hour"];
            data.penalties = [];
            data.glowClass = "espionage_glow";
            break;
        case 3:
            data.banner = "/images/v2/decorations/policy_banners/defensepolicy.jpg";
            data.name = "Fortress of Solitude";
            data.phrase = "vigilant minds, enduring hearts.";
            data.description = "Show the might of will unyielding.\nPrepare, preserve, prevail.";
            data.bonuses = ["25% increased Reinforce speed", "+1 Defense gain from Reinforcing", "+1 Stability gain from Reinforcing", "+1 Scouting"];
            data.resources = ["+100 Materials production / hour", "+70 Food production / hour", "+40 Wealth production / hour"];
            data.penalties = [];
            data.glowClass = "defense_glow";
            break;
        case 4:
            data.banner = "/images/v2/decorations/policy_banners/warpolicy.jpg";
            data.name = "Forged in Flames";
            data.phrase = "blades sharp, minds sharper.";
            data.description = "Lead your village on the path of a warmonger.\nFeel no fear, no hesitation, no doubt.";
            data.bonuses = ["25% increased Raid speed", "+1 Defense reduction from Raiding", "+1 Stability reduction from Raiding", "25% increased objective damage from PvP wins"];
            data.resources = ["+70 Materials production / hour", "+70 Food production / hour", "+70 Wealth production / hour"];
            data.penalties = ["Cannot form Alliances"];
            data.glowClass = "war_glow";
            break;
        case 5:
            data.banner = "/images/v2/decorations/policy_banners/prosperitypolicy.jpg";
            data.name = "The Gilded Hand";
            data.phrase = "golden touch, boundless reach.";
            data.description = "In the art of war, wealth is our canvas.\nBuild empires, foster riches, command respect.";
            data.bonuses = ["25% increased Caravan speed", "+25 baseline region Stability", "+25 maximum region Stability", "25% reduced upkeep from upgrades"];
            data.resources = ["+40 Materials production / hour", "+70 Food production / hour", "+100 Wealth production / hour"];
            data.penalties = [];
            data.glowClass = "prosperity_glow";
            break;
    }

    return data;
}