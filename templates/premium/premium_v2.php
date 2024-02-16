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
                page: "<?= $landing_page ?>",
                playerData: <?= json_encode(
                    UserAPIPresenter::playerDAtaResponse(player: $player, rank_names: RankManager::fetchNames($system))
                ) ?>
            }),
            shopContainer
        );
    })
</script>
