<?php
/**
 * @var User $player
 * @var System $system
 * @var int $convo_count
 * @var int $convo_count_max
 * 
 */

//PM ban
if($player->checkBan(StaffManager::BAN_TYPE_PM)) {
    $ban_type = StaffManager::BAN_TYPE_PM;
    $expire_int = $player->ban_data[$ban_type];
    $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($expire_int-time()));
    require 'templates/ban_info.php';
    return true;
}
?>

<link rel="stylesheet" type="text/css" href="ui_components/src/inbox/inbox.css" />



<div id="inboxReactContainer"></div>


<script src="../node_modules/react/cjs/react.development.js" crossorigin></script>
<script src="../node_modules/react-dom/cjs/react-dom.development.js" crossorigin></script>
<script type="module" src="<?= $system->link?><?= $system->getReactFile("inbox/inbox") ?>"></script>
<script>
    const inboxContainer = document.querySelector("#inboxReactContainer");

    const inboxAPILink = "<?= $system->api_links['inbox'] ?>";
    const convo_count = <?= $convo_count ?>;
    const convo_count_max = <?= $convo_count_max ?>;
    const url_object = new URL(window.location.href);
    const sender = url_object.searchParams.get('sender');


    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Inbox, {
                inboxAPILink: inboxAPILink,
                convo_count: convo_count,
                convo_count_max: convo_count_max,
                sender: sender
            }),
            inboxContainer
        );
    });
</script>