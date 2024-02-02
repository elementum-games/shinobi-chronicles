<?php

class TestFighter extends Fighter {
    public string $name = 'test';
    public int $rank = 1;
    public int $regen_rate = 20;

    public $id;
    public string $gender = 'Non-binary';
    public int $total_stats;

    public function activeEffectsFromFormData(array $active_effects): array {
        $effects = [];

        foreach($active_effects as $active_effect) {
            if($active_effect['effect'] == 'none') continue;

            $effects[] = new BattleEffect(
                user: $this->combat_id,
                target: $this->combat_id,
                turns: 1,
                effect: $active_effect['effect'],
                effect_amount: $active_effect['amount']
            );
        }

        return $effects;
    }

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

    public function addJutsu(
        string $jutsu_type,
        float $base_power,
        string $effect,
        int $effect_amount,
        int $effect_length
    ): Jutsu {
        $id = count($this->jutsu) + 1;
        $jutsu = new Jutsu(
            id: $id,
            name: $this->name . 'j' . $id,
            rank: $this->rank,
            jutsu_type: $jutsu_type,
            base_power: $base_power,
            range: 1,
            effect_1: $effect,
            base_effect_amount_1: $effect_amount,
            effect_length_1: $effect_length,
            effect_2: 'none',
            base_effect_amount_2: 0,
            effect_length_2: 0,
            description: 'no',
            battle_text: 'nope',
            cooldown: 0,
            use_type: Jutsu::USE_TYPE_PROJECTILE,
            target_type: Jutsu::TARGET_TYPE_TILE,
            use_cost: 0,
            purchase_cost: 0,
            purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            parent_jutsu: 0,
            element: Jutsu::ELEMENT_NONE,
            hand_seals: 0
        );

        $jutsu->setLevel(100, 0);
        $this->jutsu[$jutsu->id] = $jutsu;
        $this->equipped_jutsu[] = [
            'id' => $jutsu->id,
            'type' => $jutsu->jutsu_type,
        ];

        return $jutsu;
    }

    public static function fromFormData(System $system, RankManager $rankManager, array $fighter_data, string $name): TestFighter {
        $fighter = new TestFighter();
        $fighter->id = 1;
        $fighter->rank = 4;
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
        $fighter->intelligence = 0;
        $fighter->willpower = 0;
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
        }

        $fighter->jutsu = [];
        $fighter->equipped_jutsu = [];

        return $fighter;
    }
}