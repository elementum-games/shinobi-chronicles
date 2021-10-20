<?php

class BattleEffect {
    public static array $buff_effects = [
        'heal','ninjutsu_boost','taijutsu_boost','genjutsu_boost',
        'cast_speed_boost','speed_boost','lighten','intelligence_boost','willpower_boost',
        'ninjutsu_resist','genjutsu_resist','taijutsu_resist','harden'
    ];

    const TYPE_BLOODLINE = 'bloodline';

    // combat id
    public string $user;
    // combat Id
    public string $target;

    public int $turns;
    public string $effect;
    public float $effect_amount;

    // Jutsu type or 'BLOODLINE'
    public string $effect_type;

    public float $power = 0;

    public bool $first_turn = false;
    public bool $layer_active = false;

    public function __construct(
        string $user,
        string $target,
        int $turns,
        string $effect,
        float $effect_amount,
        string $effect_type,
        float $power = 0,
        bool $first_turn = false,
        bool $layer_active = false
    ) {
        $this->user = $user;
        $this->target = $target;
        $this->turns = $turns;
        $this->effect = $effect;
        $this->effect_amount = $effect_amount;
        $this->effect_type = $effect_type;

        $this->power = $power;
        $this->first_turn = $first_turn;
        $this->layer_active = $layer_active;
    }

    public static function fromArray(array $raw_data): BattleEffect {
        return new BattleEffect(
            $raw_data['user'],
            $raw_data['target'],
            $raw_data['turns'],
            $raw_data['effect'],
            $raw_data['effect_amount'],
            $raw_data['effect_type'],
            $raw_data['power'] ?? 0,
            $raw_data['first_turn'] ?? false,
            $raw_data['layer_active'] ?? false,
        );    
    }
}