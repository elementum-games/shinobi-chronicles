// @flow strict

export type JutsuElement = 'None' | 'Fire' | 'Earth' | 'Wind' | 'Water' | 'Lightning';

export type JutsuType = {|
    +id: number,
    +name: string,
    +jutsuType: 'ninjutsu' | 'genjutsu' | 'taijutsu',
    +useType: 'physical' | 'projectile' | 'projectile_aoe' | 'spawn' | 'barrier',
    +targetType: 'fighter_id' | 'tile' | 'direction',
    +handSeals: $ReadOnlyArray<string>,
    +range: number,
    +element: JutsuElement;
|};