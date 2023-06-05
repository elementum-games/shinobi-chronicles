<?php
/**
 * @var System $system
 * @var User $player
 */

$NotificationAPIManager = new NotificationAPIManager($system, $player);

?>

<link rel="stylesheet" type="text/css" href="ui_components/src/topbar/Topbar.css" />

<div id="topbarContainer"></div>
<script type="module" src="<?= $system->getReactFile("topbar/Topbar") ?>"></script>
<script>
    const topbarContainer = document.querySelector("#topbarContainer");

        window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Topbar, {
                links: {
                    notification_api: "<?= $system->router->api_links['notification'] ?>",
                },
                notificationAPIData: {
                    userNotifications: <?= json_encode(NotificationAPIPresenter::userNotificationResponse($NotificationAPIManager)) ?>,
                },
            }),
            topbarContainer
        );
    });
</script>