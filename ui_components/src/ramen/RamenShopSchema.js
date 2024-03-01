// @flow strict

export type BasicRamenType = {|
    +key: string,
    +cost: number,
    +healthAmount: number,
    +label: string,
    +image: string,
|};

export type CharacterRamenType = {|
    +id: number,
    +userId: number,
    +buffDuration: number,
    +purchaseTime: number,
    +buffEffects: Array<string>,
    +mysteryRamenAvailable: boolean,
    +mysteryRamenEffects: Array<string>,
    +purchaseCountSinceLastMystery: number,
|};

export type MysteryRamenType = {|
    +cost: number,
    +label: string,
    +duration: number,
    +image: string,
    +effects: Array<string>,
    +mysteryRamenUnlocked: boolean,
|};

export type RamenOwnerType = {|
    +name: string,
    +image: string,
    +background: string,
    +shopDescription: string,
    +dialogue: string,
    +shopName: string,
|};

export type SpecialRamenType = {|
    +key: string,
    +cost: number,
    +label: string,
    +image: string,
    +description: string,
    +effect: string,
    +duration: number,
|};