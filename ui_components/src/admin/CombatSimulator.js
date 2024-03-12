// @flow strict

import { unSlug } from "../utils/string.js";
import { apiFetch } from "../utils/network.js";
import { numFormat } from "../utils/number.js";
import type { FighterFormData, JutsuFormData, BloodlineType } from "./combatSimulatorSchema.js";

const defaultFighterFormData: FighterFormData = {
    jutsu1: {
        id: 0,
        name: '',
        type: 'ninjutsu',
        use_type: 'projectile',
        power: 4,
        element: 'None',
        is_bloodline: false,
        effect: 'none',
        effect_amount: 0,
        effect_length: 0,
        effect2: 'none',
        effect2_amount: 0,
        effect2_length: 0,
    },
    bloodline_id: 0,
    bloodline_boosts: Array.from({length: 3}).map(() => ({
        effect: 'none',
        power: 0,
    })),
    active_effects:  Array.from({length: 3}).map(() => ({
        effect: 'none',
        amount: 0,
    })),
    ninjutsu_skill: 0,
    taijutsu_skill: 0,
    genjutsu_skill: 0,
    bloodline_skill: 0,
    speed: 0,
    cast_speed: 0,
    stats_preset: 'none'
}

type FormOptions = {
    +bloodlineCombatBoosts: $ReadOnlyArray<string>,
    +bloodlineRankLabels: { [key: string]: string },
    +damageEffects: $ReadOnlyArray<string>,
    +clashEffects: $ReadOnlyArray<string>,
    +buffEffects: $ReadOnlyArray<string>,
    +debuffEffects: $ReadOnlyArray<string>,
    +bloodlinesById: {
        [key: string|number]: BloodlineType
    },
    +bloodlineIdsByRank: {
        [key: string|number]: $ReadOnlyArray<number>,
    },
    +jutsuById: { [key: string|number]: JutsuFormData },
    +jutsuIdsByGroup: { [key: string|number]: $ReadOnlyArray<number> },
    +jutsuElements: $ReadOnlyArray<string>,
    +jutsuUseTypes: $ReadOnlyArray<string>,
    +statCap: number,
};

type Props = {
    +adminApiLink: string,
    +formOptions: FormOptions,
};
export function CombatSimulator({
    adminApiLink,
    formOptions,
}: Props): React$Node {
    const [fighter1FormData, setFighter1FormData] = React.useState<FighterFormData>(defaultFighterFormData);
    const [fighter2FormData, setFighter2FormData] = React.useState<FighterFormData>(defaultFighterFormData);
    const [results, setResults] = React.useState(null);

    function runSimulation() {
        apiFetch(adminApiLink, {
            action: 'run_versus_simulation',
            fighter1: fighter1FormData,
            fighter2: fighter2FormData,
        })
        .then(response => {
            if(response.errors.length > 0) {
                console.warn(response.errors);
                setResults(null);
                return;
            }

            setResults(response.data.results);
        });
    }

    return <div>
        {results != null && <SimulationResults
            winningFighter={results.winning_fighter}
            player1Results={results.player1}
            player2Results={results.player2}
            damageDifference={results.damage_difference}
            winningPercent={results.winning_percent}
            collisionText={results.collision_text}
        />}
        <div className='vs_container'>
            <FighterInput
                formKey='fighter1'
                fighterFormData={fighter1FormData}
                setFighterFormData={setFighter1FormData}
                formOptions={formOptions}
            />
            <FighterInput
                formKey='fighter2'
                fighterFormData={fighter2FormData}
                setFighterFormData={setFighter2FormData}
                formOptions={formOptions}
             />
            <br />
            <button type="button" onClick={runSimulation}>Run Simulation</button>
        </div>
    </div>;
}

type SimulationResultsProps = {
    +winningFighter: null | 'player1' | 'player2',
    +player1Results: {
        +raw_damage: number,
        +collision_damage: number,
        +damage_before_resist: number,
        +damage_dealt: number,
        +damage_taken: number,
    },
    +player2Results: {
        +raw_damage: number,
        +collision_damage: number,
        +damage_before_resist: number,
        +damage_dealt: number,
        +damage_taken: number,
    },
    +damageDifference: number,
    +winningPercent: number,
    +collisionText: string,
};
function SimulationResults({
    winningFighter,
    player1Results,
    player2Results,
    damageDifference,
    winningPercent,
    collisionText
}: SimulationResultsProps) {
    return <div className="results">
        <div className={`player1 ${winningFighter === 'player1' ? 'winner' : ''}`}>
            <b>Player 1:</b><br/>
            {numFormat(player1Results.raw_damage)} raw damage<br/>
            {numFormat(player1Results.collision_damage)} post-collision damage<br/>
            {numFormat(player1Results.damage_before_resist)} pre-resist damage<br/>
            {numFormat(player1Results.damage_dealt.toLocaleString())} final damage dealt<br/>
            <br/>
            {numFormat(player1Results.damage_taken, 2)} damage taken<br/>
        </div>
        <div className={`player2 ${winningFighter === 'player2' ? 'winner' : ''}`}>
            <b>Player 2:</b><br/>
            {player2Results.raw_damage.toLocaleString()} raw damage<br/>
            {player2Results.collision_damage.toLocaleString()} post-collision damage<br/>
            {player2Results.damage_before_resist.toLocaleString()} pre-resist damage<br/>
            {player2Results.damage_dealt.toLocaleString()} final damage dealt<br/>
            <br/>
            {player2Results.damage_taken.toLocaleString()} damage taken<br/>
        </div>
        <div className='collision'>
            <p dangerouslySetInnerHTML={{__html: collisionText.replace(/\[br]/g, "<br />")}} />
            <p style={{margin: '2px'}}>
                {winningFighter != null && <b>
                    {unSlug(winningFighter)} won by {winningPercent.toLocaleString()}%
                    ({damageDifference.toLocaleString()} damage)
                </b>}
            </p>
        </div>
    </div>;
}

type FighterInputProps = {
    +fighterFormData: FighterFormData,
    +formKey: string,
    +formOptions: Props["formOptions"],
    +setFighterFormData: ((FighterFormData) => FighterFormData) => void,
};
function FighterInput({
    fighterFormData,
    formKey,
    formOptions,
    setFighterFormData
}: FighterInputProps) {
    const statPresetOptions = [
        '25_25_50',
        '33_33_33',
        '40_40_20',
        '50_50_0',
        '0_80_20',
        '0_100_0',
    ];
    const stats = [
        'ninjutsu_skill',
        'taijutsu_skill',
        'genjutsu_skill',
        'bloodline_skill',
        'speed',
        'cast_speed',
    ];

    // testing this more stuff

    // more testing really?

    function prefillStats(statPreset: string) {
        let [offenseType, _offSkill, _blSkill, _speed] = statPreset.split('_');
        let offSkill = parseInt(_offSkill) / 100;
        let blSkill = parseInt(_blSkill) / 100;
        let speed = parseInt(_speed) / 100;

        switch(offenseType) {
            case 'nin':
                setFighterFormData(prevData => ({
                    ...prevData,
                    ninjutsu_skill: offSkill * formOptions.statCap,
                    taijutsu_skill: 0,
                    genjutsu_skill: 0,
                    bloodline_skill: blSkill * formOptions.statCap,
                    speed: 0,
                    cast_speed: speed * formOptions.statCap,
                    jutsu1: {
                        ...prevData.jutsu1,
                        type: 'ninjutsu',
                    }
                }));
                break;
            case 'tai':
                setFighterFormData(prevData => ({
                    ...prevData,
                    ninjutsu_skill: 0,
                    taijutsu_skill: offSkill * formOptions.statCap,
                    genjutsu_skill: 0,
                    bloodline_skill: blSkill * formOptions.statCap,
                    speed: speed * formOptions.statCap,
                    cast_speed: 0,
                    jutsu1: {
                        ...prevData.jutsu1,
                        type: 'taijutsu',
                    }
                }));
                break;
            case 'gen':
                setFighterFormData(prevData => ({
                    ...prevData,
                    ninjutsu_skill: 0,
                    taijutsu_skill: 0,
                    genjutsu_skill: offSkill * formOptions.statCap,
                    bloodline_skill: blSkill * formOptions.statCap,
                    speed: 0,
                    cast_speed: speed * formOptions.statCap,
                    jutsu1: {
                        ...prevData.jutsu1,
                        type: 'genjutsu',
                    }
                }));
                break;
            default:
                console.warn('invalid offense type!');
        }
    }

    // testing

    function prefillBloodline(bloodline_id: number) {
        if(formOptions.bloodlinesById[bloodline_id] == null) {
            console.warn("Invalid bloodline ", bloodline_id);
            return;
        }

        const bloodline = formOptions.bloodlinesById[bloodline_id];

        let boosts = bloodline.base_combat_boosts.map(boost => ({
            effect: boost.effect,
            power: boost.power,
        }));

        setFighterFormData(prevData => ({
            ...prevData,
            bloodline_id: bloodline_id,
            bloodline_boosts: [
                ...boosts,
                ...defaultFighterFormData.bloodline_boosts.slice(boosts.length)
            ]
        }));
    }

    function updateField(fieldName: $Keys<FighterFormData>, newValue: mixed) {
        const fieldNameStr = (fieldName: string);
        setFighterFormData(prevFighterFormData => ({ ...prevFighterFormData, [ fieldNameStr ]: newValue }));
    }

    function updateBloodlineBoost(index: number, newBoost: FighterFormData["bloodline_boosts"][number]) {
        setFighterFormData(prevData => ({
            ...prevData,
            bloodline_boosts: [
                ...fighterFormData.bloodline_boosts.slice(0, index),
                newBoost,
                ...fighterFormData.bloodline_boosts.slice(index + 1)
            ]
        }));
    }

    function updateActiveEffect(index: number, newActiveEffect: FighterFormData["active_effects"][number]) {
        setFighterFormData(prevData => ({
            ...prevData,
            active_effects: [
                ...fighterFormData.active_effects.slice(0, index),
                newActiveEffect,
                ...fighterFormData.active_effects.slice(index + 1)
            ]
        }));
    }

    return <div className='versusFighterInput'>
        <b>{unSlug(formKey)}</b>
        <select
            value={fighterFormData.stats_preset}
            onChange={e => prefillStats(e.target.value)}
            style={{ display: "inline-block", marginLeft: "21px", marginBottom: "12px" }}
        >
            <option value='none'>Pre-fill stats (Off/BL/Speed)</option>
            <optgroup label="Jonin Ninjutsu">
                {statPresetOptions.map(option => (
                    <option key={`stat_nin_${option}`} value={`nin_${option}`}>
                        Nin {option.replace(/_/g, "/")}
                    </option>
                ))}
            </optgroup>
            <optgroup label="Jonin Taijutsu">
                {statPresetOptions.map(option => (
                    <option key={`stat_tai_${option}`} value={`tai_${option}`}>
                        Tai {option.replace(/_/g, "/")}
                    </option>
                ))}
            </optgroup>
            <optgroup label="Jonin Genjutsu">
                {statPresetOptions.map(option => (
                    <option key={`stat_gen_${option}`} value={`gen_${option}`}>
                        Gen {option.replace(/_/g, "/")}
                    </option>
                ))}
            </optgroup>
        </select>
        <br/>

        {stats.map(stat => (
            <p key={stat}>
                <label>{stat}:</label>
                <input
                    type='number'
                    step='10'
                    value={fighterFormData[stat]}
                    onChange={e => {
                        updateField(stat, parseInt(e.target.value));
                    }}
                />
            </p>
        ))}
        <br/>

        <b>Bloodline boosts</b><br/>
        <select
            value={fighterFormData.bloodline_id}
            onChange={e => prefillBloodline(e.target.value)}
        >
            <option value='0'>Select to auto-fill boosts</option>
            {Object.keys(formOptions.bloodlineIdsByRank).map(rank => {
                const bloodlines = formOptions.bloodlineIdsByRank[rank]
                    .map(id => formOptions.bloodlinesById[id]);

                return <optgroup key={`bloodlines_${rank}`} label={formOptions.bloodlineRankLabels[rank]}>
                    {bloodlines.map((bloodline, i) => (
                        <option key={`bloodline:${i}`} value={bloodline.bloodline_id}>
                            {bloodline.name}
                        </option>
                    ))}
                </optgroup>
            })}
        </select>

        <div className='bloodline_boosts' style={{ marginTop: "8px" }}>
            {fighterFormData.bloodline_boosts.map((fighterBloodlineBoost, i) => (
                <p key={`bloodline_boost_${i}`}>
                    <select
                        value={fighterBloodlineBoost.effect}
                        onChange={e=> {
                            updateBloodlineBoost(i, {
                                ...fighterBloodlineBoost,
                                effect: e.target.value
                            })
                        }}
                    >
                        <option value='none'>None</option>
                        {formOptions.bloodlineCombatBoosts.map(effect => (
                            <option key={`bloodline_boost_${i}_option_${effect}`} value={effect}>
                                {unSlug(effect)}
                            </option>
                        ))}
                    </select>
                    <input
                        type='number'
                        style={{ width: '60px' }}
                        value={fighterBloodlineBoost.power}
                        onChange={e => {
                            updateBloodlineBoost(i, {
                                ...fighterBloodlineBoost,
                                power: e.target.value
                            })
                        }}
                    />
                </p>
            ))}
        </div>

        <b>Active Effects</b><br/>
        <div className='active_effects_input'>
            {fighterFormData.active_effects.map((fighterActiveEffect, i) => (
                <div key={`active_effect_${i}`} style={{ margin: "4px auto" }}>
                    <label>Effect {i}</label>
                    <EffectInput
                        value={fighterActiveEffect.effect}
                        formOptions={formOptions}
                        onChange={newValue => updateActiveEffect(i, {
                            ...fighterActiveEffect,
                            effect: newValue
                        })}
                    />
                    <br/>

                    <label>Effect {i} Amount:</label>
                    <input
                        type='number'
                        value={fighterActiveEffect.amount}
                        onChange={e => updateActiveEffect(i, {
                            ...fighterActiveEffect,
                            amount: parseInt(e.target.value),
                        })}
                    />
                </div>
            ))}
        </div>

        <b>Jutsu</b>
        <br/>
        <JutsuInput
            jutsuFormData={fighterFormData.jutsu1}
            formOptions={formOptions}
            onChange={newValue => updateField('jutsu1', newValue)}
        />
    </div>;
}

function JutsuInput({
    jutsuFormData,
    formOptions,
    onChange
}: {
    +jutsuFormData: JutsuFormData,
    +formOptions: FormOptions,
    +onChange: (JutsuFormData) => void,
}) {
    function prefillJutsu(jutsu_id: number) {
        if(formOptions.jutsuById[jutsu_id] == null) {
            console.warn("Invalid jutsu ", jutsu_id);
        }

        const jutsu = formOptions.jutsuById[jutsu_id];

        onChange({
            id: jutsu_id,
            name: jutsu.name,
            type: jutsu.type,
            use_type: jutsu.use_type,
            power: jutsu.power,
            element: jutsu.element,
            is_bloodline: jutsu.is_bloodline,
            effect: jutsu.effect,
            effect_amount: jutsu.effect_amount,
            effect_length: jutsu.effect_length,
            effect2: jutsu.effect2,
            effect2_amount: jutsu.effect2_amount,
            effect2_length: jutsu.effect2_length,
        });
    }

    function updateField(fieldName: $Keys<JutsuFormData>, newValue: mixed) {
        let fieldNameString = (fieldName: string);
        onChange({
            ...jutsuFormData,
            [fieldNameString]: newValue
        });
    }

    return <div className='jutsu_input'>
        <select
            value={jutsuFormData.id}
            style={{margin: "2px auto 6px"}}
            onChange={(e) => prefillJutsu(e.target.value)}
        >
            <option value='0'>Select to auto-fill jutsu</option>
            {Object.keys(formOptions.jutsuIdsByGroup).map(group => (
                <optgroup key={`jutsu_group:${group}`} label={group}>
                    {formOptions.jutsuIdsByGroup[group].map(jutsu_id => {
                        const jutsu = formOptions.jutsuById[jutsu_id];

                        return <option key={`jutsu${jutsu.id}`} value={jutsu.id}>
                            {jutsu.name}
                        </option>;
                    })}
                </optgroup>
            ))}
        </select>
        <br/>

        <label>Offense:</label>
        <select
            value={jutsuFormData.type}
            onChange={e => updateField('type', e.target.value)}
        >
            <option value='ninjutsu'>
                Ninjutsu
            </option>
            <option value='taijutsu'>
                Taijutsu
            </option>
            <option value='genjutsu'>
                Genjutsu
            </option>
        </select><br />

        <label>Use Type</label>
        <select
            value={jutsuFormData.use_type}
            onChange={e => updateField('use_type', e.target.value)}
        >
            {formOptions.jutsuUseTypes.map(useType => (
                <option key={`jutsuUseType:${useType}`} value={useType}>
                    {unSlug(useType)}
                </option>
            ))}
        </select>

    <label>Base Power:</label>
    <input
        type='number'
        step='0.05'
        value={jutsuFormData.power}
        onChange={e => updateField('power', parseFloat(e.target.value))}
        style={{ width: "70px" }}
    /><br />

    <label>Element:</label>
    <select
        value={jutsuFormData.element}
        onChange={e => updateField('element', e.target.value)}
    >
        {formOptions.jutsuElements.map(element => (
            <option key={`element:${element}`} value={element}>
                {unSlug(element)}
            </option>
        ))}
    </select>
    <br />

    <label>Bloodline</label>
    <input
        type='checkbox'
        checked={jutsuFormData.is_bloodline}
        onChange={() => updateField('is_bloodline', !jutsuFormData.is_bloodline)}
    /><br />

    <div className='effect_input'>
        <label>Effect</label>
        <EffectInput
            formOptions={formOptions}
            value={jutsuFormData.effect}
            onChange={newValue => updateField('effect', newValue)}
        />

        <label>Effect Amount:</label>
        <input
            type='number'
            value={jutsuFormData.effect_amount}
            onChange={e => updateField('effect_amount', parseInt(e.target.value))}
        />

        <label>Effect Length:</label>
        <input
            type='number'
            value={jutsuFormData.effect_length}
            onChange={e => updateField('effect_length', parseInt(e.target.value))}
        />
    </div>
        <div className='effect_input'>
            <label>Effect 2</label>
            <EffectInput
                formOptions={formOptions}
                value={jutsuFormData.effect2}
                onChange={newValue => updateField('effect2', newValue)}
            />

            <label>Effect 2 Amount:</label>
            <input
                type='number'
                value={jutsuFormData.effect2_amount}
                onChange={e => updateField('effect2_amount', parseInt(e.target.value))}
            />

            <label>Effect 2 Length:</label>
            <input
                type='number'
                value={jutsuFormData.effect2_length}
                onChange={e => updateField('effect2_length', parseInt(e.target.value))}
            />
        </div>
    </div>;
}


function EffectInput({
    formOptions,
    value,
    onChange
}: {
    +formOptions: FormOptions,
    +value: string,
    +onChange: (string) => void,
}) {
    return <select value={value} onChange={(e) => onChange(e.target.value)}>
        <optgroup label="Damage">
            {formOptions.damageEffects.map(effect => (
                <option key={`dmg_${effect}`} value={effect}>
                    {unSlug(effect)}
                </option>
            ))}
        </optgroup>
        <optgroup label="Clash">
            {formOptions.clashEffects.map(effect => (
                <option key={`clash_${effect}`} value={effect}>
                    {unSlug(effect)}
                </option>
            ))}
        </optgroup>
        <optgroup label="Buff">
            {formOptions.buffEffects.map(effect => (
                <option key={`buff_${effect}`} value={effect}>
                    {unSlug(effect)}
                </option>
            ))}
        </optgroup>
        <optgroup label="Debuff">
            {formOptions.debuffEffects.map(effect => (
                <option key={`debuff_${effect}`} value={effect}>
                    {unSlug(effect)}
                </option>
            ))}
        </optgroup>
    </select>
}

window.CombatSimulator = CombatSimulator;