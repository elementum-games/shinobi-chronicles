<?php

/** @var System $system */
require __DIR__ . "/../_authenticate_admin.php";

require __DIR__ . "/TestFighter.php";
require __DIR__ . "/calcDamage.php";

$rankManager = new RankManager($system);
$rankManager->loadRanks();

$rank_names = RankManager::fetchNames($system);

$results = null;

// Bloodlines
$bloodline_ids_by_rank = [];
$bloodlines_by_id = [];
$result = $system->db->query("SELECT * FROM `bloodlines` WHERE `rank` < 5 ORDER BY `rank` ASC");
while ($row = $system->db->fetch($result)) {
    $bloodline = Bloodline::fromArray($row);
    $bloodline->name = System::unescape($bloodline->name);

    $bloodlines_by_id[$bloodline->bloodline_id] = $bloodline;
    $bloodline_ids_by_rank[$row['rank']][] = $bloodline->bloodline_id;
}

// Jutsu
$jutsu_by_id = [];
$jutsu_ids_by_group = [];

$result = $system->db->query("SELECT * FROM `jutsu` 
     WHERE `purchase_type` != " . Jutsu::PURCHASE_TYPE_NON_PURCHASABLE . " 
     ORDER BY `rank` DESC, `purchase_cost` DESC"
);
while($row = $system->db->fetch($result)) {
    $jutsu = Jutsu::fromArray($row['jutsu_id'], $row);
    $jutsu_by_id[$jutsu->id] = [
        'id' => $jutsu->id,
        'name' => System::unescape($jutsu->name),
        'type' => $jutsu->jutsu_type,
        'use_type' => $jutsu->use_type,
        'power' => $jutsu->power,
        'element' => $jutsu->element->value,
        'is_bloodline' => $jutsu->is_bloodline,
        'effect' => $jutsu->effects[0]?->effect ?? "none",
        'effect_amount' => $jutsu->effects[0]?->effect_amount ?? 0,
        'effect_length' => $jutsu->effects[0]?->effect_length ?? 0,
        'effect2' => $jutsu->effects[1]?->effect ?? "none",
        'effect2_amount' => $jutsu->effects[1]?->effect_amount ?? "none",
        'effect2_length' => $jutsu->effects[1]?->effect_length ?? "none",
    ];

    $group = $rank_names[$jutsu->rank] . " " . ucwords($jutsu->jutsu_type->value);

    if(!isset($jutsu_ids_by_group[$group])) {
        $jutsu_ids_by_group[$group] = [];
    }

    $jutsu_ids_by_group[$group][] = $jutsu->id;
}


/** @var string[] $bloodline_combat_boosts */
require __DIR__ . '/../constraints/bloodline.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>SC Combat - VS mode</title>
    <?= $system->isDevEnvironment() ? Layout::$react_dev_tags : Layout::$react_prod_tags ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body style='background: #2a2a2a;'>
    <link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/admin/CombatSimulator.css") ?>" />
    <div id="combatSimulatorReactContainer"></div>
    <script type="module" src="<?= $system->getReactFile("admin/CombatSimulator") ?>"></script>

    <!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
    <script type="text/javascript">
        const container = document.querySelector("#combatSimulatorReactContainer");

        // const initialPostsResponse = json_encode($initialChatPostsResponse);
        // const initialBanInfo = json_encode(ChatAPIPresenter::banInfoResponse($system, $player));

        window.addEventListener('load', () => {
            ReactDOM.render(
                React.createElement(CombatSimulator, {
                    adminApiLink: "<?= $system->router->api_links['admin'] ?>",
                    formOptions: {
                        bloodlineCombatBoosts: <?= json_encode($bloodline_combat_boosts) ?>,
                        bloodlineRankLabels: <?= json_encode(Bloodline::$public_ranks) ?>,
                        damageEffects: <?= json_encode(BattleEffectsManager::DAMAGE_EFFECTS) ?>,
                        clashEffects: <?= json_encode(BattleEffectsManager::CLASH_EFFECTS) ?>,
                        buffEffects: <?= json_encode(BattleEffectsManager::BUFF_EFFECTS) ?>,
                        debuffEffects: <?= json_encode(BattleEffectsManager::DEBUFF_EFFECTS) ?>,
                        bloodlinesById: <?= json_encode($bloodlines_by_id) ?>,
                        bloodlineIdsByRank: <?= json_encode($bloodline_ids_by_rank) ?>,
                        jutsuById: <?= json_encode($jutsu_by_id) ?>,
                        jutsuIdsByGroup: <?= json_encode($jutsu_ids_by_group) ?>,
                        jutsuElements: <?= json_encode(Element::values()) ?>,
                        jutsuUseTypes: <?= json_encode(Jutsu::$use_types) ?>,
                        statCap: <?= $rankManager->ranks[System::SC_MAX_RANK]->stat_cap ?>,
                    },
                }),
                container
            );
        })
    </script>
</body>
</html>