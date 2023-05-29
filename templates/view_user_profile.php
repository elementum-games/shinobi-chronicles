<?php

/**
 * @var System $system
 * @var User $viewUser
 * @var User $player
 * @var array $ranks
 * @var string $self_link
 */

$last_active = time() - $viewUser->last_active;

$clan_positions = array(
    1 => 'Leader',
    2 => 'Elder 1',
    3 => 'Elder 2',
);

?>
<table id='viewprofile' class='table'>
    <tr><th colspan='2'>View Profile</th>
    <tr><td colspan='2' style='text-align:center;'>
        <?php if($viewUser->last_active > time() - 120): ?>
            <span style='font-weight:bold;color:#00C000;'>Online</span>
        <?php elseif($viewUser->last_active > time() - 300): ?>
            <span style='font-weight:bold;color:#E0D000;'>Inactive</span>
        <?php else: ?>
            <span style='font-weight:bold;color:#D02020;'>Offline</span>
        <?php endif; ?>
        <br />

        <?php if($player->isModerator()): ?>
            (Last active <?= System::timeRemaining($last_active, 'long') ?> ago)
        <?php else: ?>
            <?php $days = floor($last_active / 86400); ?>
            <?php if($days == 0): ?>
                (Last active today)
            <?php elseif($days == 1): ?>
                (Last active yesterday)
            <?php else: ?>
                (Last active <?= $days ?> days ago)
            <?php endif; ?>
        <?php endif; ?>
    </td></tr>
    <tr>
        <td style='width:50%;text-align:center;'>
            <span style='font-size:1.3em;font-family:\"tempus sans itc\";font-weight:bold;'><?= $viewUser->user_name ?></span><br />
            <?= $system->imageCheck($viewUser->avatar_link, $viewUser->getAvatarSize()) ?><br />
        </td>
        <td style='width:50%;'>
			<label style='width:6.5em;'>Level:</label> 	<?= $viewUser->level ?><br />
			<label style='width:6.5em;'>Exp:</label> 	<?= $viewUser->exp ?><br />
			<label style='width:6.5em;'>Rank:</label> 	<?= $ranks[$viewUser->rank_num] ?><br />
            <br />

            <?php if($viewUser->gender != User::GENDER_NONE): ?>
                <label style='width:6.5em;'>Gender:</label> <?= $viewUser->gender ?><br />
            <?php endif; ?>

			<label style='width:6.5em;'>Village:</label> <?= $viewUser->village->name ?><br />
			<label style='width:6.5em;'>Bloodline:</label> <?= ($viewUser->bloodline_id ? $viewUser->bloodline_name : "None") ?>
            <br />
            <?php if (SenseiManager::isSensei($viewUser->user_id, $system)): ?>
                <label style='width:6.5em;'>Students:</label>
                <?php foreach ($students as $student): ?>
                    <a href='<?= $system->router->getUrl('members', ['user' => $student->user_name])?>'>
                        <?= $student->user_name ?></a>
                <?php endforeach; ?>
                <?php if (count($students) == 0): ?>
                    None
                <?php endif; ?>
                <br />
            <?php endif; ?>
            <?php if ($viewUser->sensei_id != 0): ?>
            <label style='width:6.5em;'>Sensei:</label>
            <a href='<?= $system->router->getUrl('members', ['user' => $sensei['user_name']])?>'>
                <?= $sensei['user_name'] ?></a><br />
            <?php endif; ?>
            <?php if($viewUser->clan): ?>
                <label style='width:6.5em;'>Clan:</label> <?= $viewUser->clan->name ?><br />
                <?php if($viewUser->clan_office): ?>
                    <label style='width:6.5em;'>Clan Rank:</label> <?= $clan_positions[$viewUser->clan_office] ?><br />
                <?php endif; ?>
            <?php endif; ?>
            <?php if($viewUser->team): ?>
                <label style='width:6.5em;'>Team:</label> <a href='<?= $self_link ?>&view_team=<?= $viewUser->team->id ?>'><?= $viewUser->team->name ?></a><br />
            <?php endif; ?>

            <br /><label style='width:6.5em;'>Spouse:</label>
            <?php if($viewUser->spouse > 0): ?>
                <a href='<?= $self_link ?>&user=<?= $viewUser->spouse_name ?>'><?= $viewUser->spouse_name ?></a><br />
                <label style='width:6.5em;'>Anniversary:</label> <?= Date('F j, Y', $viewUser->marriage_time) ?><br />
            <?php else: ?>
                None<br />
            <?php endif; ?>

            <?php if($player->user_id == $viewUser->spouse): ?>
                <label style='width:6.5em;'>Location:</label> <?= $viewUser->location->displayString() ?><br />
            <?php endif; ?>

            <br />
			<label style='width:6.5em;'>PvP wins:</label>	<?= $viewUser->pvp_wins ?><br />
			<label style='width:6.5em;'>PvP losses:</label>	<?= $viewUser->pvp_losses ?><br />
			<label style='width:6.5em;'>AI wins:</label> <?= $viewUser->ai_wins ?><br />
			<label style='width:6.5em;'>AI losses:</label> <?= $viewUser->ai_losses ?><br />
        </td></tr>

        <!--//send message/money/ak-->
        <tr><td style='text-align:center;' colspan='2'>
			<a href='<?= $system->router->base_url ?>?id=2&page=new_message&sender=<?= $viewUser->user_name ?>'>Send Message</a>

            <?php if($player->rank_num > 1): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['profile'] ?>&page=send_money&recipient=<?= $viewUser->user_name ?>'>Send Money</a>
                &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['profile'] ?>&page=send_ak&recipient=<?= $viewUser->user_name ?>'>Send AK</a>
            <?php endif; ?>
            <?php if($viewUser->rank_num >= 3 && $player->team): ?>
                <?php if($player->user_id == $player->team->leader && !$viewUser->team && !$viewUser->team_invite &&
                    $player->village->name == $viewUser->village->name): ?>
                    &nbsp;&nbsp; |  &nbsp;&nbsp;
                                <a href='<?= $system->router->base_url ?>?id=24&invite=1&user_name=<?= $viewUser->user_name ?>'>Invite to Team</a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($player->rank_num < 3 && $player->sensei_id == 0 && $player->village->name == $viewUser->village->name && SenseiManager::isSensei($viewUser->user_id, $system) && $viewUser->accept_students): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['villageHQ'] ?>&view=sensei&apply=<?= $viewUser->user_id ?>'>Send Application</a>
            <?php endif; ?>
            <?php if($player->rank_num < 3 && $player->sensei_id == $viewUser->user_id): ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['villageHQ'] ?>&view=sensei&leave=true'>Leave Sensei</a>
            <?php endif; ?>
            <?php if(SenseiManager::isSensei($player->user_id, $system) && $viewUser->sensei_id == $player->user_id && $viewUser->user_id != $player->user_id): ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['villageHQ'] ?>&view=sensei&kick=<?= $viewUser->user_id ?>'>Kick Student</a>
            <?php endif; ?>
        </td></tr>

            <?php if($journal): ?>
                <?php
                    if(!str_contains($journal, "\n")) {
                        $journal = wordwrap($journal, 100, "\r\n", true);
                    }
                    $journal = $system->html_parse(stripslashes($journal), true, true);
                    $class_name = $player->forbidden_seal->level > 0 ? 'forbidden_seal' : 'normal';
                ?>
                <style>
                    #journal {
                        white-space: pre-wrap;
                    }
                    #journal.normal img {
                        max-width: 400px;
                        max-height: 300px;
                    }
                </style>
                <tr><th colspan='2'>Journal</th></tr>
                <tr><td colspan='2'>
                    <div id='journal' class='<?= $class_name ?>'><?= $journal ?></div>
                </td></tr>
            <?php endif; ?>

            <!--//report player-->
            <tr>
				<td style='text-align: center;' colspan='2'>
					<a href='<?= $system->router->links['report'] ?>&report_type=1&content_id=<?= $viewUser->user_id ?>'>Report Profile/Journal</a>
				</td>
			</tr>
</table>

<?php if($player->isModerator()): ?>
    <table class='table'><tr><th colspan='2'>Staff Info</th></tr>
        <tr><td colspan='2'>
            IP address: <?= $viewUser->current_ip ?><br />
            Email address: <?= $viewUser->email ?><br />
            <h3>Ban status:</h3>
            <?php $banned = false; ?>
            <?php if($viewUser->ban_data): ?>
                <?php
                    $count = 0;
                    $size = sizeof($viewUser->ban_data);
                ?>

                <?php foreach($viewUser->ban_data as $ban_name => $end_time): ?>
                    <?php $count++; ?>
                    <b><?= ucwords($ban_name) ?>:</b> (Expires: <?= $system->time_remaining($end_time - time()) ?>)
                    <?php if($count % 2 == 0): ?>
                        <br />
                    <?php elseif($count != $size): ?>
                        &nbsp;,&nbsp;
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php $banned = true; ?>
            <?php endif; ?>

            <?php if($viewUser->journal_ban): ?>
                Journal banned<br />
                <?php $banned = true; ?>
            <?php endif; ?>
            <?php if($viewUser->avatar_ban): ?>
                Avatar banned<br />
                <?php $banned = true; ?>
            <?php endif; ?>

            <?php if(!$banned): ?>
                No bans!<br />
            <?php endif; ?>

            <?php if($player->isHeadAdmin()): ?>
                </td></tr>
                <tr><td colspan='2' style='text-align:center;'>

                    <!--// Last chat post-->
                    <?php
                    $result = $system->query(
                    "SELECT `time` FROM `chat` WHERE `user_name`='{$viewUser->user_name}' ORDER BY `post_id` DESC LIMIT 1"
                    );
                    ?>
                    <?php if($system->db_last_num_rows > 0): ?>
                        <?php $last_post = $system->db_fetch($result)['time']; ?>
                        Last chat post: <?= System::timeRemaining(time() - $last_post, 'long') ?> ago<br />
                    <?php endif; ?>

                    <!--// Last AI-->
                    Last AI battle started: <?= System::timeRemaining((System::currentTimeMs() - $viewUser->last_ai_ms) / 1000, 'short') ?> ago<br />

                    <!--// Current training-->
                    <?php $display = ''; ?>
                    <?php if(str_contains($viewUser->train_type, 'jutsu:')): ?>
                        <?php $train_type = str_replace('jutsu:', '', $viewUser->train_type) ?>
                        <br />Training: <?= ucwords(str_replace('_', ' ', $train_type)) ?><br />
                            <?= System::timeRemaining($viewUser->train_time - time(), 'short', false, true) ?> remaining
                    <?php else: ?>
                       <br />Training: <?= ucwords(str_replace('_', ' ', $viewUser->train_type)) ?><br />
                            <?= System::timeRemaining($viewUser->train_time - time(), 'short', false, true) ?> remaining
                    <?php endif; ?>
            <?php endif; ?>
        </td></tr>
        <tr><td colspan='2' style='text-align:center;'>
        <a href='<?= $system->router->links['mod'] ?>&view_record=<?= $viewUser->user_name ?>'>View Record</a>&nbsp;&nbsp;|&nbsp;&nbsp;
        <a href='<?= $system->router->links['mod'] ?>&ban_user_name=<?= $viewUser->user_name ?>'>Ban user</a>

        <?php if($player->isHeadModerator()): ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['mod'] ?>&unban_user_name=<?= $viewUser->user_name ?>'>Unban user</a>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['mod'] ?>&ban_ip_address=<?= $viewUser->last_ip ?>'>Ban IP</a>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['mod'] ?>&unban_ip_address=<?= $viewUser->last_ip ?>'>Unban IP</a>
        <?php endif; ?>
        <?php if($player->isUserAdmin()): ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href='<?= $system->router->links['admin'] ?>&page=edit_user&user_name=<?= $viewUser->user_name ?>'>Edit user</a>
        <?php endif; ?>
    </td></tr>
<?php endif; ?>
</table>