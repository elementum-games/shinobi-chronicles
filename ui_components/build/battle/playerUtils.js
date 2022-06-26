export function findPlayerJutsu(battle, jutsuId, isBloodline = false) {
  if (isBloodline) {
    return battle.playerBloodlineJutsu.find(jutsu => jutsu.id === jutsuId);
  } else {
    return battle.playerEquippedJutsu.find(jutsu => jutsu.id === jutsuId) || battle.playerDefaultAttacks.find(jutsu => jutsu.id === jutsuId);
  }
}