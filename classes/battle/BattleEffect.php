<?php

class BattleEffect {
    public static array $buff_effects = [
        'heal','ninjutsu_boost','taijutsu_boost','genjutsu_boost',
        'cast_speed_boost','speed_boost','lighten','intelligence_boost','willpower_boost',
        'ninjutsu_resist','genjutsu_resist','taijutsu_resist','harden','evasion_boost','resist_boost'
    ];

    // combat id
    public string $user;
    // combat Id
    public string $target;

    public int $turns;
    public string $effect;
    public float $effect_amount;

    // Required unless this is a passive effect. Should be a valid Jutsu#jutsuType
    public ?string $damage_type;

    public float $power = 0;

    public bool $first_turn = false;
    public bool $layer_active = false;

    public function __construct(
        string $user,
        string $target,
        int $turns,
        string $effect,
        float $effect_amount,
        ?string $damage_type = null,
        float $power = 0,
        bool $first_turn = false,
        bool $layer_active = false
    ) {
        $this->user = $user;
        $this->target = $target;
        $this->turns = $turns;
        $this->effect = $effect;
        $this->effect_amount = $effect_amount;
        $this->damage_type = $damage_type;

        $this->power = $power;
        $this->first_turn = $first_turn;
        $this->layer_active = $layer_active;
    }

    public static function fromArray(array $raw_data): BattleEffect {
        return new BattleEffect(
            user: $raw_data['user'],
            target: $raw_data['target'],
            turns: $raw_data['turns'],
            effect: $raw_data['effect'],
            effect_amount: $raw_data['effect_amount'],
            damage_type: $raw_data['damage_type'] ?? null,
            power: $raw_data['power'] ?? 0,
            first_turn: $raw_data['first_turn'] ?? false,
            layer_active: $raw_data['layer_active'] ?? false,
        );    
    }
}