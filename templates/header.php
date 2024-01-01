<?php
/**
 * @var System $system
 */

?>

<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/header/Header.css") ?>" />

<div id="headerContainer"></div>
<script type="module" src="<?= $system->getReactFile("header/Header") ?>"></script>
<script>
    const headerContainer = document.querySelector("#headerContainer");

        window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Header, {
                links: {
                    navigation_api: "<?= $system->router->api_links['navigation'] ?>",
                    logout_link: "<?= $system->router->base_url . "?logout=1" ?>",
                },
                navigationAPIData: {
                    headerMenu: <?= json_encode(
                        NavigationAPIPresenter::menuLinksResponse(
                            NavigationAPIManager::getHeaderMenu($system),
                        )
                    ) ?>
                },
                timeZone: "<?= System::SERVER_TIME_ZONE ?>",
                updateMaintenance: <?= !is_null($system->UPDATE_MAINTENANCE) ? $system->UPDATE_MAINTENANCE->getTimestamp() : 0 ?>,
                scOpen: <?= (int) $system->SC_OPEN ?>
            }),
            headerContainer
        );
    });
</script>