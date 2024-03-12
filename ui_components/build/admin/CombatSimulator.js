import { unSlug } from "../utils/string.js";
import { apiFetch } from "../utils/network.js";
import { numFormat } from "../utils/number.js";
const defaultFighterFormData = {
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
    effect2_length: 0
  },
  bloodline_id: 0,
  bloodline_boosts: Array.from({
    length: 3
  }).map(() => ({
    effect: 'none',
    power: 0
  })),
  active_effects: Array.from({
    length: 3
  }).map(() => ({
    effect: 'none',
    amount: 0
  })),
  ninjutsu_skill: 0,
  taijutsu_skill: 0,
  genjutsu_skill: 0,
  bloodline_skill: 0,
  speed: 0,
  cast_speed: 0,
  stats_preset: 'none'
};
export function CombatSimulator({
  adminApiLink,
  formOptions
}) {
  const [fighter1FormData, setFighter1FormData] = React.useState(defaultFighterFormData);
  const [fighter2FormData, setFighter2FormData] = React.useState(defaultFighterFormData);
  const [results, setResults] = React.useState(null);
  function runSimulation() {
    apiFetch(adminApiLink, {
      action: 'run_versus_simulation',
      fighter1: fighter1FormData,
      fighter2: fighter2FormData
    }).then(response => {
      if (response.errors.length > 0) {
        console.warn(response.errors);
        setResults(null);
        return;
      }
      setResults(response.data.results);
    });
  }
  return /*#__PURE__*/React.createElement("div", null, results != null && /*#__PURE__*/React.createElement(SimulationResults, {
    winningFighter: results.winning_fighter,
    player1Results: results.player1,
    player2Results: results.player2,
    damageDifference: results.damage_difference,
    winningPercent: results.winning_percent,
    collisionText: results.collision_text
  }), /*#__PURE__*/React.createElement("div", {
    className: "vs_container"
  }, /*#__PURE__*/React.createElement(FighterInput, {
    formKey: "fighter1",
    fighterFormData: fighter1FormData,
    setFighterFormData: setFighter1FormData,
    formOptions: formOptions
  }), /*#__PURE__*/React.createElement(FighterInput, {
    formKey: "fighter2",
    fighterFormData: fighter2FormData,
    setFighterFormData: setFighter2FormData,
    formOptions: formOptions
  }), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("button", {
    type: "button",
    onClick: runSimulation
  }, "Run Simulation")));
}
function SimulationResults({
  winningFighter,
  player1Results,
  player2Results,
  damageDifference,
  winningPercent,
  collisionText
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "results"
  }, /*#__PURE__*/React.createElement("div", {
    className: `player1 ${winningFighter === 'player1' ? 'winner' : ''}`
  }, /*#__PURE__*/React.createElement("b", null, "Player 1:"), /*#__PURE__*/React.createElement("br", null), numFormat(player1Results.raw_damage), " raw damage", /*#__PURE__*/React.createElement("br", null), numFormat(player1Results.collision_damage), " post-collision damage", /*#__PURE__*/React.createElement("br", null), numFormat(player1Results.damage_before_resist), " pre-resist damage", /*#__PURE__*/React.createElement("br", null), numFormat(player1Results.damage_dealt.toLocaleString()), " final damage dealt", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("br", null), numFormat(player1Results.damage_taken, 2), " damage taken", /*#__PURE__*/React.createElement("br", null)), /*#__PURE__*/React.createElement("div", {
    className: `player2 ${winningFighter === 'player2' ? 'winner' : ''}`
  }, /*#__PURE__*/React.createElement("b", null, "Player 2:"), /*#__PURE__*/React.createElement("br", null), player2Results.raw_damage.toLocaleString(), " raw damage", /*#__PURE__*/React.createElement("br", null), player2Results.collision_damage.toLocaleString(), " post-collision damage", /*#__PURE__*/React.createElement("br", null), player2Results.damage_before_resist.toLocaleString(), " pre-resist damage", /*#__PURE__*/React.createElement("br", null), player2Results.damage_dealt.toLocaleString(), " final damage dealt", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("br", null), player2Results.damage_taken.toLocaleString(), " damage taken", /*#__PURE__*/React.createElement("br", null)), /*#__PURE__*/React.createElement("div", {
    className: "collision"
  }, /*#__PURE__*/React.createElement("p", {
    dangerouslySetInnerHTML: {
      __html: collisionText.replace(/\[br]/g, "<br />")
    }
  }), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: '2px'
    }
  }, winningFighter != null && /*#__PURE__*/React.createElement("b", null, unSlug(winningFighter), " won by ", winningPercent.toLocaleString(), "% (", damageDifference.toLocaleString(), " damage)"))));
}
function FighterInput({
  fighterFormData,
  formKey,
  formOptions,
  setFighterFormData
}) {
  const statPresetOptions = ['25_25_50', '33_33_33', '40_40_20', '50_50_0', '0_80_20', '0_100_0'];
  const stats = ['ninjutsu_skill', 'taijutsu_skill', 'genjutsu_skill', 'bloodline_skill', 'speed', 'cast_speed'];

  // testing this more stuff

  // more testing really?

  function prefillStats(statPreset) {
    let [offenseType, _offSkill, _blSkill, _speed] = statPreset.split('_');
    let offSkill = parseInt(_offSkill) / 100;
    let blSkill = parseInt(_blSkill) / 100;
    let speed = parseInt(_speed) / 100;
    switch (offenseType) {
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
            type: 'ninjutsu'
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
            type: 'taijutsu'
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
            type: 'genjutsu'
          }
        }));
        break;
      default:
        console.warn('invalid offense type!');
    }
  }

  // testing

  function prefillBloodline(bloodline_id) {
    if (formOptions.bloodlinesById[bloodline_id] == null) {
      console.warn("Invalid bloodline ", bloodline_id);
      return;
    }
    const bloodline = formOptions.bloodlinesById[bloodline_id];
    let boosts = bloodline.base_combat_boosts.map(boost => ({
      effect: boost.effect,
      power: boost.power
    }));
    setFighterFormData(prevData => ({
      ...prevData,
      bloodline_id: bloodline_id,
      bloodline_boosts: [...boosts, ...defaultFighterFormData.bloodline_boosts.slice(boosts.length)]
    }));
  }
  function updateField(fieldName, newValue) {
    const fieldNameStr = fieldName;
    setFighterFormData(prevFighterFormData => ({
      ...prevFighterFormData,
      [fieldNameStr]: newValue
    }));
  }
  function updateBloodlineBoost(index, newBoost) {
    setFighterFormData(prevData => ({
      ...prevData,
      bloodline_boosts: [...fighterFormData.bloodline_boosts.slice(0, index), newBoost, ...fighterFormData.bloodline_boosts.slice(index + 1)]
    }));
  }
  function updateActiveEffect(index, newActiveEffect) {
    setFighterFormData(prevData => ({
      ...prevData,
      active_effects: [...fighterFormData.active_effects.slice(0, index), newActiveEffect, ...fighterFormData.active_effects.slice(index + 1)]
    }));
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "versusFighterInput"
  }, /*#__PURE__*/React.createElement("b", null, unSlug(formKey)), /*#__PURE__*/React.createElement("select", {
    value: fighterFormData.stats_preset,
    onChange: e => prefillStats(e.target.value),
    style: {
      display: "inline-block",
      marginLeft: "21px",
      marginBottom: "12px"
    }
  }, /*#__PURE__*/React.createElement("option", {
    value: "none"
  }, "Pre-fill stats (Off/BL/Speed)"), /*#__PURE__*/React.createElement("optgroup", {
    label: "Jonin Ninjutsu"
  }, statPresetOptions.map(option => /*#__PURE__*/React.createElement("option", {
    key: `stat_nin_${option}`,
    value: `nin_${option}`
  }, "Nin ", option.replace(/_/g, "/")))), /*#__PURE__*/React.createElement("optgroup", {
    label: "Jonin Taijutsu"
  }, statPresetOptions.map(option => /*#__PURE__*/React.createElement("option", {
    key: `stat_tai_${option}`,
    value: `tai_${option}`
  }, "Tai ", option.replace(/_/g, "/")))), /*#__PURE__*/React.createElement("optgroup", {
    label: "Jonin Genjutsu"
  }, statPresetOptions.map(option => /*#__PURE__*/React.createElement("option", {
    key: `stat_gen_${option}`,
    value: `gen_${option}`
  }, "Gen ", option.replace(/_/g, "/"))))), /*#__PURE__*/React.createElement("br", null), stats.map(stat => /*#__PURE__*/React.createElement("p", {
    key: stat
  }, /*#__PURE__*/React.createElement("label", null, stat, ":"), /*#__PURE__*/React.createElement("input", {
    type: "number",
    step: "10",
    value: fighterFormData[stat],
    onChange: e => {
      updateField(stat, parseInt(e.target.value));
    }
  }))), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("b", null, "Bloodline boosts"), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("select", {
    value: fighterFormData.bloodline_id,
    onChange: e => prefillBloodline(e.target.value)
  }, /*#__PURE__*/React.createElement("option", {
    value: "0"
  }, "Select to auto-fill boosts"), Object.keys(formOptions.bloodlineIdsByRank).map(rank => {
    const bloodlines = formOptions.bloodlineIdsByRank[rank].map(id => formOptions.bloodlinesById[id]);
    return /*#__PURE__*/React.createElement("optgroup", {
      key: `bloodlines_${rank}`,
      label: formOptions.bloodlineRankLabels[rank]
    }, bloodlines.map((bloodline, i) => /*#__PURE__*/React.createElement("option", {
      key: `bloodline:${i}`,
      value: bloodline.bloodline_id
    }, bloodline.name)));
  })), /*#__PURE__*/React.createElement("div", {
    className: "bloodline_boosts",
    style: {
      marginTop: "8px"
    }
  }, fighterFormData.bloodline_boosts.map((fighterBloodlineBoost, i) => /*#__PURE__*/React.createElement("p", {
    key: `bloodline_boost_${i}`
  }, /*#__PURE__*/React.createElement("select", {
    value: fighterBloodlineBoost.effect,
    onChange: e => {
      updateBloodlineBoost(i, {
        ...fighterBloodlineBoost,
        effect: e.target.value
      });
    }
  }, /*#__PURE__*/React.createElement("option", {
    value: "none"
  }, "None"), formOptions.bloodlineCombatBoosts.map(effect => /*#__PURE__*/React.createElement("option", {
    key: `bloodline_boost_${i}_option_${effect}`,
    value: effect
  }, unSlug(effect)))), /*#__PURE__*/React.createElement("input", {
    type: "number",
    style: {
      width: '60px'
    },
    value: fighterBloodlineBoost.power,
    onChange: e => {
      updateBloodlineBoost(i, {
        ...fighterBloodlineBoost,
        power: e.target.value
      });
    }
  })))), /*#__PURE__*/React.createElement("b", null, "Active Effects"), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("div", {
    className: "active_effects_input"
  }, fighterFormData.active_effects.map((fighterActiveEffect, i) => /*#__PURE__*/React.createElement("div", {
    key: `active_effect_${i}`,
    style: {
      margin: "4px auto"
    }
  }, /*#__PURE__*/React.createElement("label", null, "Effect ", i), /*#__PURE__*/React.createElement(EffectInput, {
    value: fighterActiveEffect.effect,
    formOptions: formOptions,
    onChange: newValue => updateActiveEffect(i, {
      ...fighterActiveEffect,
      effect: newValue
    })
  }), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("label", null, "Effect ", i, " Amount:"), /*#__PURE__*/React.createElement("input", {
    type: "number",
    value: fighterActiveEffect.amount,
    onChange: e => updateActiveEffect(i, {
      ...fighterActiveEffect,
      amount: parseInt(e.target.value)
    })
  })))), /*#__PURE__*/React.createElement("b", null, "Jutsu"), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement(JutsuInput, {
    jutsuFormData: fighterFormData.jutsu1,
    formOptions: formOptions,
    onChange: newValue => updateField('jutsu1', newValue)
  }));
}
function JutsuInput({
  jutsuFormData,
  formOptions,
  onChange
}) {
  function prefillJutsu(jutsu_id) {
    if (formOptions.jutsuById[jutsu_id] == null) {
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
      effect2_length: jutsu.effect2_length
    });
  }
  function updateField(fieldName, newValue) {
    let fieldNameString = fieldName;
    onChange({
      ...jutsuFormData,
      [fieldNameString]: newValue
    });
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "jutsu_input"
  }, /*#__PURE__*/React.createElement("select", {
    value: jutsuFormData.id,
    style: {
      margin: "2px auto 6px"
    },
    onChange: e => prefillJutsu(e.target.value)
  }, /*#__PURE__*/React.createElement("option", {
    value: "0"
  }, "Select to auto-fill jutsu"), Object.keys(formOptions.jutsuIdsByGroup).map(group => /*#__PURE__*/React.createElement("optgroup", {
    key: `jutsu_group:${group}`,
    label: group
  }, formOptions.jutsuIdsByGroup[group].map(jutsu_id => {
    const jutsu = formOptions.jutsuById[jutsu_id];
    return /*#__PURE__*/React.createElement("option", {
      key: `jutsu${jutsu.id}`,
      value: jutsu.id
    }, jutsu.name);
  })))), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("label", null, "Offense:"), /*#__PURE__*/React.createElement("select", {
    value: jutsuFormData.type,
    onChange: e => updateField('type', e.target.value)
  }, /*#__PURE__*/React.createElement("option", {
    value: "ninjutsu"
  }, "Ninjutsu"), /*#__PURE__*/React.createElement("option", {
    value: "taijutsu"
  }, "Taijutsu"), /*#__PURE__*/React.createElement("option", {
    value: "genjutsu"
  }, "Genjutsu")), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("label", null, "Use Type"), /*#__PURE__*/React.createElement("select", {
    value: jutsuFormData.use_type,
    onChange: e => updateField('use_type', e.target.value)
  }, formOptions.jutsuUseTypes.map(useType => /*#__PURE__*/React.createElement("option", {
    key: `jutsuUseType:${useType}`,
    value: useType
  }, unSlug(useType)))), /*#__PURE__*/React.createElement("label", null, "Base Power:"), /*#__PURE__*/React.createElement("input", {
    type: "number",
    step: "0.05",
    value: jutsuFormData.power,
    onChange: e => updateField('power', parseFloat(e.target.value)),
    style: {
      width: "70px"
    }
  }), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("label", null, "Element:"), /*#__PURE__*/React.createElement("select", {
    value: jutsuFormData.element,
    onChange: e => updateField('element', e.target.value)
  }, formOptions.jutsuElements.map(element => /*#__PURE__*/React.createElement("option", {
    key: `element:${element}`,
    value: element
  }, unSlug(element)))), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("label", null, "Bloodline"), /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: jutsuFormData.is_bloodline,
    onChange: () => updateField('is_bloodline', !jutsuFormData.is_bloodline)
  }), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("div", {
    className: "effect_input"
  }, /*#__PURE__*/React.createElement("label", null, "Effect"), /*#__PURE__*/React.createElement(EffectInput, {
    formOptions: formOptions,
    value: jutsuFormData.effect,
    onChange: newValue => updateField('effect', newValue)
  }), /*#__PURE__*/React.createElement("label", null, "Effect Amount:"), /*#__PURE__*/React.createElement("input", {
    type: "number",
    value: jutsuFormData.effect_amount,
    onChange: e => updateField('effect_amount', parseInt(e.target.value))
  }), /*#__PURE__*/React.createElement("label", null, "Effect Length:"), /*#__PURE__*/React.createElement("input", {
    type: "number",
    value: jutsuFormData.effect_length,
    onChange: e => updateField('effect_length', parseInt(e.target.value))
  })), /*#__PURE__*/React.createElement("div", {
    className: "effect_input"
  }, /*#__PURE__*/React.createElement("label", null, "Effect 2"), /*#__PURE__*/React.createElement(EffectInput, {
    formOptions: formOptions,
    value: jutsuFormData.effect2,
    onChange: newValue => updateField('effect2', newValue)
  }), /*#__PURE__*/React.createElement("label", null, "Effect 2 Amount:"), /*#__PURE__*/React.createElement("input", {
    type: "number",
    value: jutsuFormData.effect2_amount,
    onChange: e => updateField('effect2_amount', parseInt(e.target.value))
  }), /*#__PURE__*/React.createElement("label", null, "Effect 2 Length:"), /*#__PURE__*/React.createElement("input", {
    type: "number",
    value: jutsuFormData.effect2_length,
    onChange: e => updateField('effect2_length', parseInt(e.target.value))
  })));
}
function EffectInput({
  formOptions,
  value,
  onChange
}) {
  return /*#__PURE__*/React.createElement("select", {
    value: value,
    onChange: e => onChange(e.target.value)
  }, /*#__PURE__*/React.createElement("optgroup", {
    label: "Damage"
  }, formOptions.damageEffects.map(effect => /*#__PURE__*/React.createElement("option", {
    key: `dmg_${effect}`,
    value: effect
  }, unSlug(effect)))), /*#__PURE__*/React.createElement("optgroup", {
    label: "Clash"
  }, formOptions.clashEffects.map(effect => /*#__PURE__*/React.createElement("option", {
    key: `clash_${effect}`,
    value: effect
  }, unSlug(effect)))), /*#__PURE__*/React.createElement("optgroup", {
    label: "Buff"
  }, formOptions.buffEffects.map(effect => /*#__PURE__*/React.createElement("option", {
    key: `buff_${effect}`,
    value: effect
  }, unSlug(effect)))), /*#__PURE__*/React.createElement("optgroup", {
    label: "Debuff"
  }, formOptions.debuffEffects.map(effect => /*#__PURE__*/React.createElement("option", {
    key: `debuff_${effect}`,
    value: effect
  }, unSlug(effect)))));
}
window.CombatSimulator = CombatSimulator;