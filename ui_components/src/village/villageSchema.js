// @flow strict

export type ResourceNameType = 'materials' | 'food' | 'wealth';

export type VillagePolicyType = {|
    +policy_id: number,
|};

export type VillageProposalType = {|
    +building_key: ?string,
    +upgrade_key: ?string,
    +proposal_id: number,
    +village_id: number,
    +target_village_id: ?number,
    +policy_id: number,
    +user_id: number,
    +start_time: number,
    +end_time: ?number,
    +name: string,
    +result: ?mixed,
    +type: string, // "offer_trade" | "accept_trade"
    +votes: $ReadOnlyArray<{}>,
    +vote_time_remaining: ?string,
    +enact_time_remaining: ?string,
    +trade_data: {
        +offered_resources: $ReadOnlyArray<{|
            +resource_id: number,
            +resource_name: ResourceNameType,
            +count: number,
        |}>,
        +offered_regions: $ReadOnlyArray<{
            ...
        }>,
        +requested_resources: $ReadOnlyArray<{|
            +resource_id: number,
            +resource_name: ResourceNameType,
            +count: number,
        |}>,
        +requested_regions: $ReadOnlyArray<{
            ...
        }>,
    },
|};

const RELATION_NEUTRAL = 1;
const RELATION_ALLIANCE = 2;
const RELATION_WAR = 3;

export type VillageSeatType = {|
    +seat_key: "kage" | "elder_1" | "elder_2" | "elder_3",
    +seat_id: number,
    +user_id: ?number,
    +village_id: number,
    +seat_type: "kage" | "elder",
    +seat_title: string,
    +seat_start: ?number,
    +user_name: string,
    +avatar_link: string,
    +is_provisional: boolean,
    +provisional_days_label: ?string,
|};

export type VillageResourceStrategicInfo = {|
    +claimed: number,
    +collected: number,
    +count: number,
    +lost: number,
    +produced: number,
    +resource_id: number,
    +resource_name: ResourceNameType,
    +spent: 0,
|};

export type VillageStrategicInfo = {|
    +village: {|
        +coords: {|
            +x: number,
            +y: number,
            +map_id: number,
        |},
        +village_id: number,
        +name: string,
        +points: number,
        +monthly_points: number,
        +leader: ?number,
        +map_location_id: number,
        +region_id: number,
        +kage_name: string,
        +resources: {
            +[key: string]: number,
        },
        +relations: {
            +[key: string]: {|
                +relation_id: number,
                +village1_id: number,
                +village2_id: number,
                +village1_name: string,
                +village2_name: string,
                +relation_type: typeof RELATION_NEUTRAL | typeof RELATION_ALLIANCE | typeof RELATION_WAR,
                +relation_name: string,
                +relation_start: number,
                +relation_end: ?number
            |},
        },
        +policy_id: number,
        +policy: {
            +infiltrate_speed: number,
            +infiltrate_defense: number,
            +reinforce_speed: number,
            +reinforce_defense: number,
            +raid_speed: number,
            +raid_defense: number,
            +caravan_speed: number,
            +patrol_respawn: number,
            +patrol_tier: number,
            +training_speed: number,
            +transfer_cost_reduction: number,
            +home_production_boost: number,
            +scouting: number,
            +stealth: number,
            +loot_capacity: number,
            +pvp_village_point: number,
            +war_enabled: true,
            +alliance_enabled: true,
            +materials_upkeep: number,
            +food_upkeep: number,
            +wealth_upkeep: number
        },
        +location: string,
        +prev_monthly_points: number|string,
    |},
    +seats: $ReadOnlyArray<VillageSeatType>,
    +population: $ReadOnlyArray<{|
        +rank: "academy" | "genin" | "chuunin" | "jonin",
        +count: number,
    |}>,
    +regions: $ReadOnlyArray<{|
        +name: string,
        +region_id: number,
    |}>,
    +supply_points: {
        +[key: string]: {|
            +name: ResourceNameType,
            +count: number,
        |}
    },
    +allies: $ReadOnlyArray<string>,
    +enemies: $ReadOnlyArray<string>
|};