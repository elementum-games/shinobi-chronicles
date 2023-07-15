// @flow

import type { BattleType, BattleJutsuType } from "./battleSchema.js";

export function findPlayerJutsu(battle: BattleType, jutsuId: number, isBloodline: boolean = false): ?BattleJutsuType {
    if(isBloodline) {
        return battle.playerBloodlineJutsu.find(jutsu => jutsu.id === jutsuId);
    }
    else {
        return battle.playerEquippedJutsu.find(jutsu => jutsu.id === jutsuId) ||
            battle.playerDefaultAttacks.find(jutsu => jutsu.id === jutsuId);
    }
}