<?php
/**
 * @var System $system
 * @var User $player
 */
?>

<?php
function headerModule(): void {
	global $system;
	global $player;

    try {
        $headerManager = new HeaderManager($system, $player);
    } catch (Exception $e) {
        $system->message($e->getMessage());
    }

    $system->printMessage();
}
?>

<link rel="stylesheet" type="text/css" href="ui_components/src/header/Header.css" />

<div id="headerContainer"></div>
<script type="module" src="<?= $system->getReactFile("header/Header") ?>"></script>
<script>
    const headerContainer = document.querySelector("#headerContainer");

        window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Header, {
                linkData: {
                    header_api: "<?= $system->router->api_links['header'] ?>",
                },
            }),
            headerContainer
        );
    });
</script>