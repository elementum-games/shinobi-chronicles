<?php
/**
 * @var System $system
 * @var User $player
 */

$ForbiddenShopManager = new ForbiddenShopManager($system, $player);
?>

<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/forbidden_shop/ForbiddenShop.css") ?>" />
<div id="forbiddenShopReactContainer"></div>
<script type="module" src="<?= $system->getReactFile("forbidden_shop/ForbiddenShop") ?>"></script>
<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
<script type="text/javascript">
    const forbiddenShopContainer = document.querySelector("#forbiddenShopReactContainer");

    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(ForbiddenShop, {
                links: {
                    forbiddenShopAPI: "<?= $system->router->api_links['forbidden_shop'] ?>",
                    userAPI: "<?= $system->router->api_links['user'] ?>",
                    missionLink: "<?= $system->router->getUrl('mission') ?>"
                },
                eventData: <?= json_encode(ForbiddenShopAPIPresenter::eventDataResponse()) ?>,
                availableEventJutsu: <?= json_encode(ForbiddenShopApiPresenter::eventJutsuResponse($ForbiddenShopManager)) ?>,
                initialPlayerInventory: <?= json_encode(UserAPIPresenter::playerInventoryResponse(player: $player)) ?>,
            }),
            forbiddenShopContainer
        );
    })
</script>
