<?php

use JetBrains\PhpStorm\Pure;

class Item {
    const USE_TYPE_WEAPON = 1;
    const USE_TYPE_ARMOR = 2;
    const USE_TYPE_CONSUMABLE = 3;
    const USE_TYPE_SPECIAL = 4;
    const USE_TYPE_CURRENCY = 5;

    const PURCHASE_TYPE_PURCHASABLE = 1;
    const PURCHASE_TYPE_REWARD = 2;

    public static array $USE_TYPE_LABELS = [
        self::USE_TYPE_WEAPON => 'weapon',
        self::USE_TYPE_ARMOR => 'armor',
        self::USE_TYPE_CONSUMABLE => 'consumable',
        self::USE_TYPE_SPECIAL => 'special',
        self::USE_TYPE_CURRENCY => 'currency',
    ];
    public static array $PURCHASE_TYPE_LABELS = [
        self::PURCHASE_TYPE_PURCHASABLE => 'purchasable',
        self::PURCHASE_TYPE_REWARD => 'reward',
    ];

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

    public int $max_quantity = 1;

    public function __construct(
        int $id,
        string $name = '',
        string $description = '',
        int $rank = 0,

        int $purchase_cost = 0,

        int $purchase_type = Item::PURCHASE_TYPE_REWARD,
        int $use_type = Item::USE_TYPE_SPECIAL,

        string $effect = '',
        float $effect_amount = 0.0,
        int $quantity = 0,

        int $max_quantity = 1
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

        $this->max_quantity = $max_quantity;
    }

    public function effectDisplayUnit(): string {
        switch($this->effect) {
            case 'residual_damage':
            case 'compound_residual':
            case 'diffuse':
            case 'element':
            case 'daze':
                return '%';
            case 'harden':
                return ' damage';
            case 'cripple':
            case 'lighten':
                return ' speed';
            case 'heal':
                return ' HP';
            case 'unknown':
            default:
                return '';
        }
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
            quantity: $quantity,
            max_quantity: $db_data['max_quantity']
        );
    }

}