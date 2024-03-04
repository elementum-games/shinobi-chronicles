<?php

class Effect {
    public ?string $effect;
    public float $base_effect_amount;
    public float $effect_amount;
    public int $effect_length;
    public int $potential_damage = 0;

    public JutsuOffenseType $damage_type;

    public function __construct(
        ?string $effect,
        float $effect_amount,
        int $effect_length,
        JutsuOffenseType $damage_type
    ) {
        $this->effect = $effect;
        $this->base_effect_amount = $effect_amount;
        $this->effect_amount = $effect_amount;
        $this->effect_length = $effect_length;
        $this->damage_type = $damage_type;

        $this->potential_damage = 0;
    }
}