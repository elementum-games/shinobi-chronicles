<?php
/**
 * @var System $system
 * @var User $player
 * @var string $landing_page
 */
?>

<link rel="stylesheet" type="text/css" href="<?=$system->getCssFileLink(file_name: 'ui_components/src/utils/modal.css')?>" />
<link rel="stylesheet" type="text/css" href="<?=$system->getCssFileLink(file_name: "ui_components/src/premium/Premium.css")?>" />
<div id="premiumShopContainer"></div>
<script type="module" src="<?=$system->getReactFile(component_name: "premium/Premium")?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const shopContainer = document.querySelector("#premiumShopContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(PremiumPage, {
                APILinks: {
                    user: "<?= $system->router->api_links['user'] ?>",
                    premium_shop: "<?= $system->router->api_links['premium_shop'] ?>",
                },
                APIData: {
                    playerData: <?= json_encode(UserAPIPresenter::playerDataResponse(player: $player, rank_names: RankManager::fetchNames($system))) ?>,
                    costs: <?= json_encode(PremiumAPIPresenter::getCosts(player: $player)) ?>,
                },
                initialPage: "<?= $landing_page ?>",
                genders: <?= json_encode(User::$genders) ?>,
                skills: <?= json_encode(array_merge(TrainingManager::$skill_types, TrainingManager::$attribute_types)) ?>,
            }),
            shopContainer
        );
    })
</script>
