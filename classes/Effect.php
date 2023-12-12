<?php

class Effect
{
    public ?string $effect;
    public float $base_effect_amount;
    public float $display_effect_amount;
    public float $effect_amount;
    public int $effect_length;

    public function __construct(?string $effect, float $effect_amount, int $effect_length)
    {
        $this->effect = $effect;
        $this->base_effect_amount = $effect_amount;
        $this->display_effect_amount = $effect_amount;
        $this->effect_amount = $effect_amount;
        $this->effect_length = $effect_length;
    }
}