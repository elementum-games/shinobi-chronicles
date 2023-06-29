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
                playerData: <?= json_encode(
                    UserAPIPresenter::playerDataResponse(player: $player, rank_names: RankManager::fetchNames($system))
                ) ?>,
                playerStats: <?= json_encode(UserApiPresenter::playerStatsResponse($player)) ?>,
                playerSettings: <?= json_encode(UserAPIPresenter::playerSettingsResponse($player)) ?>
            }),
            profileContainer
        );
    })
</script>
