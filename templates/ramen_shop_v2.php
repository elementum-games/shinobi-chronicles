<?php
/**
 * @var System $system
 * @var User $player
 */
?>

<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/ramen/RamenShop.css") ?>" />
<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/utils/modal.css") ?>" />
<div id="ramenShopReactContainer"></div>
<script type="module" src="<?= $system->getReactFile("ramen/RamenShop") ?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const ramenShopContainer = document.querySelector("#ramenShopReactContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(RamenShopReactContainer, {
                playerID: <?= $player->user_id ?>,
                ramenShopAPI: "<?= $system->router->api_links['ramen_shop'] ?>",
                ramenOwnerDetails: <?= json_encode(RamenShopAPIPresenter::ramenShopOwnerResponse($system, $player)) ?>,
                mysteryRamenDetails: <?= json_encode(RamenShopAPIPresenter::getMysteryRamenResponse($system, $player)) ?>,
                basicRamenOptions: <?= json_encode(RamenShopAPIPresenter::getBasicRamenResponse($system, $player)) ?>,
                specialRamenOptions: <?= json_encode(RamenShopAPIPresenter::getSpecialRamenResponse($system, $player)) ?>,
            }),
            ramenShopContainer
        );
    })
</script>