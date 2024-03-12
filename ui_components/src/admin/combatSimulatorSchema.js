// @flow strict

import type { JutsuElement, JutsuType } from "../_schema/jutsu.js";



export type FighterFormData = {|
    jutsu1: JutsuFormData,
    bloodline_id: number,
    bloodline_boosts: $ReadOnlyArray<{
        effect: string,
        power: number,
    }>,
    active_effects: $ReadOnlyArray<{
        effect: string,
        amount: number,
    }>,
    ninjutsu_skill: number,
    taijutsu_skill: number,
    genjutsu_skill: number,
    bloodline_skill: number,
    speed: number,
    cast_speed: number,
    stats_preset: string,
|};

export type JutsuFormData = {
    id: number,
    name: string,
    type: JutsuType["jutsuType"],
    use_type: JutsuType["useType"],
    power: number,
    element: JutsuElement,
    is_bloodline: bool,
    effect: string,
    effect_amount: number,
    effect_length: number,
    effect2: string,
    effect2_amount: number,
    effect2_length: number,
};

export type BloodlineType = {
    bloodline_id: number,
    name: string,
    base_combat_boosts: $ReadOnlyArray<{
        +power: number,
        +effect: string,
    }>,
    +jutsu: $ReadOnlyArray<{
        id: number,
    }>
};