<?php

require_once __DIR__ . "/RamenOwnerDto.php";
require_once __DIR__ . "/MysteryRamenDto.php";
require_once __DIR__ . "/BasicRamenDto.php";
require_once __DIR__ . "/SpecialRamenDto.php";


class RamenShopManager {
    /* ramen shop owner names indexed by village id */
    const RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE = [
        1 => "Suika",
        2 => "Suika",
        3 => "Suika",
        4 => "Suika",
        5 => "Suika",
    ];
    /* ramen shop owner images indexed by village id */
    const RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE = [
        1 => "images/ramen/Suika.png",
        2 => "images/ramen/Suika.png",
        3 => "images/ramen/Suika.png",
        4 => "images/ramen/Suika.png",
        5 => "images/ramen/Suika.png",
    ];
    /* ramen shop owner background images indexed by village id */
    const RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE = [
        1 => "images/ramen/RamenStand.jpg",
        2 => "images/ramen/RamenStand.jpg",
        3 => "images/ramen/RamenStand.jpg",
        4 => "images/ramen/RamenStand.jpg",
        5 => "images/ramen/RamenStand.jpg",
    ];
    /* ramen shop owner descriptions indexed by village id */
    const RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE = [
        1 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
        2 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
        3 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
        4 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
        5 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
    ];
    /* ramen shop owner dialogue options indexed by village id */
    const RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE = [
        1 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
        2 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
        3 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
        4 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
        5 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
    ];

    /**
     * Load the ramen shop owner details for the given village
     * @param Village $village
     * @return RamenOwnerDto
     */
    public static function loadRamenOwnerDetails(Village $village): RamenOwnerDto {
        return new RamenOwnerDto(
            name: self::RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE[$village->village_id],
            image: self::RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE[$village->village_id],
            background: self::RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE[$village->village_id],
            description: self::RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE[$village->village_id],
            dialogue: self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[$village->village_id][array_rand(self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[$village->village_id])],
        );
    }

    public static function loadBasicRamen(): array {
        return [];
    }

    public static function loadSpecialRamen(): array {
        return [];
    }

    /**
     * Load the mystery ramen details for the given village
     * @param Village $village
     * @return MysteryRamenDto
     */
    public static function loadMysteryRamenDetails(): MysteryRamenDto {
        return new MysteryRamenDto(
            mystery_ramen_enabled: false,
        );
    }
}