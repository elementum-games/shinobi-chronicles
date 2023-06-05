<?php
/**
 * @var System $system
 * @var User $player
 */
?>

<?php
function hotbar(): void {
	global $system;
	global $player;

    try {
        $hotbarManager = new HotbarManager($system, $player);
    } catch (Exception $e) {
        $system->message($e->getMessage());
    }

    $system->printMessage();
}
?>

<link rel="stylesheet" type="text/css" href="ui_components/src/hotbar/Hotbar.css" />

<div id="hotbarContainer"></div>
<script type="module" src="<?= $system->getReactFile("hotbar/Hotbar") ?>"></script>
<script>
    const hotbarContainer = document.querySelector("#hotbarContainer");

        window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Hotbar, {
                links: {
                    user_api: "<?= $system->router->api_links['user'] ?>",
                    training: "<?= $system->router->links['training'] ?>",
                    arena: "<?= $system->router->links['arena'] ?>",
                    mission: "<?= $system->router->links['mission'] ?>",
                    specialmissions: "<?= $system->router->links['specialmissions'] ?>",
                    healingShop: "<?= $system->router->links['healingShop'] ?>",
                    base_url: "<?= $system->router->base_url ?>"
                },
            }),
            hotbarContainer
        );
    });
</script>