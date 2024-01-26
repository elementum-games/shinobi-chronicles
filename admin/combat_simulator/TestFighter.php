<?php

class TestFighter extends Fighter {
    public string $name = 'test';
    public int $rank = 1;
    public int $regen_rate = 20;

    public $id;
    public string $gender = 'Non-binary';
    public int $total_stats;

    public function getAvatarSize(): int {
        return 125;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getInventory() {
    }

    public function hasItem(int $item_id): bool {
        return true;
    }

    public function hasEquippedJutsu(int $jutsu_id): bool {
        return true;
    }

    public function useJutsu(Jutsu $jutsu) {

    }

    public function setTotalStats() {
        $this->total_stats = $this->ninjutsu_skill + $this->genjutsu_skill + $this->taijutsu_skill + $this->bloodline_skill +
            $this->cast_speed + $this->speed + $this->intelligence + $this->willpower;
    }

    public function updateInventory() {

    }

    public function updateData() {

    }

    public function hasJutsu(int $jutsu_id): bool {
        // TODO: Implement hasJutsu() method.
        return true;
    }

    public static function fromFormData(System $system, RankManager $rankManager, array $fighter_data, string $name): TestFighter {
        $fighter = new TestFighter();
        $fighter->rank = 3;
        $fighter->health = 1000000;
        $fighter->max_health = 1000000;
        $fighter->name = $name;
        $fighter->system = $system;
        $fighter->ninjutsu_skill = (int)$fighter_data['ninjutsu_skill'];
        $fighter->taijutsu_skill = (int)$fighter_data['taijutsu_skill'];
        $fighter->genjutsu_skill = (int)$fighter_data['genjutsu_skill'];
        $fighter->bloodline_skill = (int)$fighter_data['bloodline_skill'];
        $fighter->speed = (int)$fighter_data['speed'];
        $fighter->cast_speed = (int)$fighter_data['cast_speed'];
        $fighter->intelligence = 10;
        $fighter->willpower = 10;
        $fighter->setTotalStats();

        $fighter_bloodline_boosts = [];
        for($i = 1; $i <= 3; $i++) {
            if(!empty($fighter_data["bloodline_boost_{$i}"]) && $fighter_data["bloodline_boost_{$i}"] != 'none') {
                $fighter_bloodline_boosts[] = new BloodlineBoost(
                    power: $fighter_data["bloodline_boost_{$i}_power"],
                    effect: $fighter_data["bloodline_boost_{$i}"]
                );
            }
        }

        if(count($fighter_bloodline_boosts) > 0) {
            $fighter->bloodline_id = 1;
            $fighter->bloodline = new Bloodline(
                id: 1,
                name: 'P1 Bloodline',
                rank: $fighter->rank,
                clan_id: 1,
                village_name: 'TestVillage',
                base_passive_boosts: [],
                base_combat_boosts: $fighter_bloodline_boosts,
                base_jutsu: [],
                jutsu: [],
            );

            $rank = $rankManager->ranks[$fighter->rank];
            $fighter->bloodline->setBoostAmounts(
                user_rank: $fighter->rank,
                ninjutsu_skill: $fighter->ninjutsu_skill,
                taijutsu_skill: $fighter->taijutsu_skill,
                genjutsu_skill: $fighter->genjutsu_skill,
                bloodline_skill: $fighter->bloodline_skill,
                base_stats: $rank->base_stats,
                total_stats: $fighter->total_stats,
                stats_max_level: $rankManager->statsForRankAndLevel($rank->id, $rank->max_level),
                regen_rate: $fighter->regen_rate
            );
            $fighter->applyBloodlineBoosts();
        }

        return $fighter;
    }
}