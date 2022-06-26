<?php
/**
 * @var System $system
 * @var string $self_link
 * @var ?Team $team_invited_to
 * @var ?User $team_invited_to_leader
 *
 */

    $system->printMessage()
?>

<table class='table'><tr><th>Teams</th></tr>
    <tr><td style='text-align:center;'>
        Now that you are a Chuunin, you can create or join a team with up to 3 other ninja. If you want to join a team, check the village HQ
        to find a team and PM the leader for an invite.
    </td></tr>
</table>

<?php if($team_invited_to): ?>
<style>
    .teamInvite {
        display:inline-block;width:350px;vertical-align:top;margin-top:10px;
    }

    .teamLeader {
        display:inline-block;
        width:150px;
        height:145px;
    }
    .teamLeaderHeader {
        margin: 2px 2px 4px;
        padding:3px 5px;
        border:1px solid #000000;
        border-radius:15px;
        color:#000000;
        font-weight:bold;
        background: linear-gradient(to bottom, #DCCA12, #EFDA17, #DCCA12);
    }
    .teamLeaderAvatar {
        max-width:100px;
        max-height:100px;
    }
</style>
<table class='table'>
    <tr><th>Invited to Team</th></tr>
    <tr><td style='text-align:center;'>
        <div class='teamInvite'>
            You have been invited to join the team <b><?= $team_invited_to->name ?></b><br />
            <br />
            Team type: Shinobi<br />
            Points: <?= $team_invited_to->points ?><br />
            Boost: <?= $team_invited_to->boost ?><br />
            <br />
            <a href='<?=  $self_link ?>&accept_invite=1'><span class='button' style='width:8em;'>Accept</span></a>
            <a href='<?= $self_link ?>&decline_invite=1'><span class='button' style='width:8em;'>Decline</span></a>
        </div>
        <div class='teamLeader'>
            <p class='teamLeaderHeader'>Team Leader</p>
            <span style='font-size:1.2em;font-family:\"tempus sans itc\",serif;font-weight:bold;'>
                <?= $team_invited_to_leader->getName() ?>
            </span><br>
            <img class='teamLeaderAvatar' src='<?= $team_invited_to_leader->avatar_link ?>' /><br />
            <?= $team_invited_to_leader->rank_name ?>
        </div>
        <br />
    </td></tr>
</table>
<?php endif; ?>


<table class='table'>
    <tr><th>Create Team</th></tr>
    <tr><td style='text-align:center;'>
            <form action='<?= $self_link ?>' method='post'>
                <b>Name</b><br />
                <i>(<?= Team::MIN_NAME_LENGTH ?>-<?= Team::MAX_NAME_LENGTH ?> characters, only letters, numbers, spaces, dashes, and underscores allowed)</i><br />
                <input type='text' name='name' value='<?= ($name ?? '') ?>' /><br />
                <!--TYPE-->
                <input type='submit' name='create_team' value='Create' />
            </form>
        </td></tr>
</table>
