// @flow strict

export type JutsuElement = 'None' | 'Fire' | 'Earth' | 'Wind' | 'Water' | 'Lightning';

export type JutsuType = {|
    +id: number,
    +name: string,
    +description: string,
    +jutsuType: 'ninjutsu' | 'genjutsu' | 'taijutsu',
    +useType: 'physical' | 'projectile' | 'projectile_aoe' | 'spawn' | 'barrier',
    +targetType: 'fighter_id' | 'tile' | 'direction',
    +handSeals: $ReadOnlyArray<string>,
    +power: number,
    +cooldown: number,
    +range: number,
    +element: JutsuElement;
    +effect: string,
    +effectAmount: number,
    +effectDuration: number,
|};