<?php
/**
 * @var System $system
 * @var User $player
 */
?>

<?php
function topbar(): void {
	global $system;
	global $player;

    try {
        $topbarManager = new TopbarManager($system, $player);
    } catch (Exception $e) {
        $system->message($e->getMessage());
    }

    $system->printMessage();
}
?>

<link rel="stylesheet" type="text/css" href="ui_components/src/topbar/Topbar.css" />

<div id="topbarContainer"></div>
<script type="module" src="<?= $system->getReactFile("topbar/Topbar") ?>"></script>
<script>
    const topbarContainer = document.querySelector("#topbarContainer");

        window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Topbar, {
                linkData: {
                    topbar_api: "<?= $system->router->api_links['topbar'] ?>",
                },
            }),
            topbarContainer
        );
    });
</script>