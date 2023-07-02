<?php
/**
 * @var System $system
 * @var User $player
 * @var Layout $layout
 */
?>

<link rel="stylesheet" type="text/css" href="ui_components/src/profile/Profile.css" />
<div id="profileReactContainer"></div>
<script type="module" src="<?= $system->getReactFile("profile/Profile") ?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const profileContainer = document.querySelector("#profileReactContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Profile, {
                links: {
                    clan: "<?= $system->router->getUrl('clan') ?>",
                    team: "<?= $system->router->getUrl('team') ?>",
                    buyForbiddenSeal: "<?= $system->router->getUrl('premium', ['view' => 'forbidden_seal']) ?>",
                    bloodlinePage: "<?= $system->router->getUrl('bloodline') ?>",
                },
                playerData: <?= json_encode(
                    UserAPIPresenter::playerDataResponse(player: $player, rank_names: RankManager::fetchNames($system))
                ) ?>,
                playerStats: <?= json_encode(UserApiPresenter::playerStatsResponse($player)) ?>,
                playerSettings: <?= json_encode(UserAPIPresenter::playerSettingsResponse($player)) ?>,
                playerDailyTasks: <?= json_encode(UserApiPresenter::dailyTasksResponse($player->daily_tasks)) ?>,
                playerAchievements: <?= json_encode(UserApiPresenter::playerAchievementsResponse($player)) ?>,
            }),
            profileContainer
        );
    })
</script>
