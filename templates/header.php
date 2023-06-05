<?php
/**
 * @var System $system
 * @var User $player
 */

$NavigationAPIManager = new NavigationAPIManager($system, $player);

?>

<link rel="stylesheet" type="text/css" href="ui_components/src/header/Header.css" />

<div id="headerContainer"></div>
<script type="module" src="<?= $system->getReactFile("header/Header") ?>"></script>
<script>
    const headerContainer = document.querySelector("#headerContainer");

        window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Header, {
                links: {
                    navigation_api: "<?= $system->router->api_links['navigation'] ?>",
                },
                navigationAPIData: {
                    headerMenu: <?= json_encode(NavigationAPIPresenter::menuLinksResponse($NavigationAPIManager->getHeaderMenu())) ?>,
                },
            }),
            headerContainer
        );
    });
</script>