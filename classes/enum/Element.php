<?php

enum Element: string {
    case NONE = 'None';
    case FIRE = 'Fire';
    case EARTH = 'Earth';
    case WIND = 'Wind';
    case WATER = 'Water';
    case LIGHTNING = 'Lightning';

    public static function values(): array {
        return array_map(function ($case){
            return $case->value;
        }, self::cases());
    }

    /**
     * @param Element[] $elements
     * @return string[]
     */
    public static function getValues(array $elements): array {
        return array_map(function($element) {
            return $element->value;
        }, $elements);
    }
}