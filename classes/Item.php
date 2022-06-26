<?php

use JetBrains\PhpStorm\Pure;

class Item {
    const USE_TYPE_WEAPON = 1;
    const USE_TYPE_ARMOR = 2;
    const USE_TYPE_CONSUMABLE = 3;
    const USE_TYPE_SPECIAL = 4;

    const PURCHASE_TYPE_PURCHASABLE = 1;
    const PURCHASE_TYPE_EVENT = 2;

    public int $id;
    public string $name;
    public string $description;
    public int $rank;

    public int $purchase_cost;

    public int $purchase_type;
    public int $use_type;

    public string $effect;
    public float $effect_amount;

    public int $quantity;

    public function __construct(
        int $id,
        string $name = '',
        string $description = '',
        int $rank = 0,

        int $purchase_cost = 0,

        int $purchase_type = Item::PURCHASE_TYPE_EVENT,
        int $use_type = Item::USE_TYPE_SPECIAL,

        string $effect = '',
        float $effect_amount = 0.0,
        int $quantity = 0
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->rank = $rank;

        $this->purchase_cost = $purchase_cost;

        $this->purchase_type = $purchase_type;
        $this->use_type = $use_type;

        $this->effect = $effect;
        $this->effect_amount = $effect_amount;
        $this->quantity = $quantity;
    }

    #[Pure]
    public static function fromDb(array $db_data, int $quantity = 0): Item {
        return new Item(
            id: $db_data['item_id'],
            name: $db_data['name'], 
            description: $db_data['description'] ?? "",
            rank: $db_data['rank'], 
            purchase_cost: $db_data['purchase_cost'],
            purchase_type: $db_data['purchase_type'], 
            use_type: $db_data['use_type'], 
            effect: $db_data['effect'], 
            effect_amount: $db_data['effect_amount'],
            quantity: $quantity
        );
    }

}