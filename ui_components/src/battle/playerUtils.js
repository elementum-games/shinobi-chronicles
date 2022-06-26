// @flow

import type { BattleType, JutsuType } from "./battleSchema.js";

export function findPlayerJutsu(battle: BattleType, jutsuId: number, isBloodline: boolean = false): ?JutsuType {
    if(isBloodline) {
        return battle.playerBloodlineJutsu.find(jutsu => jutsu.id === jutsuId);
    }
    else {
        return battle.playerEquippedJutsu.find(jutsu => jutsu.id === jutsuId) ||
            battle.playerDefaultAttacks.find(jutsu => jutsu.id === jutsuId);
    }
}