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

    public function addJutsuFromFormData(array $form_data): Jutsu {
        $form_data['is_bloodline'] = filter_var($form_data['is_bloodline'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $id = count($this->jutsu) + 1;
        $jutsu = new Jutsu(
            id: $id,
            name: $this->name . 'j' . $id,
            rank: $this->rank,
            jutsu_type: JutsuOffenseType::from($form_data['type']),
            base_power: $form_data['power'],
            range: 1,
            effect_1: $form_data['effect'],
            base_effect_amount_1: (int)$form_data['effect_amount'],
            effect_length_1: (int)$form_data['effect_length'],
            effect_2: $form_data['effect2'],
            base_effect_amount_2: (int)$form_data['effect2_amount'],
            effect_length_2: (int)$form_data['effect2_length'],
            description: 'no',
            battle_text: 'nope',
            cooldown: 0,
            use_type: $form_data['use_type'] ?? Jutsu::USE_TYPE_PROJECTILE,
            target_type: Jutsu::TARGET_TYPE_TILE,
            use_cost: 0,
            purchase_cost: 0,
            purchase_type: $form_data['is_bloodline'] ? Jutsu::PURCHASE_TYPE_BLOODLINE : Jutsu::PURCHASE_TYPE_PURCHASABLE,
            parent_jutsu: 0,
            element: Element::from($form_data['element']),
            hand_seals: 0,
        );
        $jutsu->is_bloodline = $form_data['is_bloodline'];
        $jutsu->setLevel(100, 0);

        if($jutsu->is_bloodline) {
            $id = count($this->bloodline->jutsu);
            $jutsu->id = $id;
            $this->bloodline->jutsu[$id] = $jutsu;
        }
        else {
            $this->jutsu[$jutsu->id] = $jutsu;
            $this->equipped_jutsu[] = [
                'id' => $jutsu->id,
                'type' => $jutsu->jutsu_type,
            ];
        }

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

        $fighter->bloodline_id = 1;
        $fighter->bloodline = new Bloodline(
            id: 1,
            name: 'P1 Bloodline',
            rank: $fighter->rank,
            clan_id: 1,
            village_name: 'TestVillage',
            base_passive_boosts: [],
            base_combat_boosts: [],
            base_jutsu: [],
            jutsu: [],
        );

        $fighter_bloodline_boosts = [];
        foreach($fighter_data["bloodline_boosts"] as $boost) {
            $fighter_bloodline_boosts[] = new BloodlineBoost(
                power: $boost['power'],
                effect: $boost['effect']
            );
        }

        if(count($fighter_bloodline_boosts) > 0) {
            $fighter->bloodline->base_combat_boosts = $fighter_bloodline_boosts;

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