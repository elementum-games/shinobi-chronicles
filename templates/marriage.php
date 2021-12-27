<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var false|array $proposal_user
 * @var array $spouse
 */
?>

<style>
    label {
        width: 9em;
        display: inline-block;
    }
    input[type=submit] {
        margin: 3px;
    }

    div#perk_details {
        width: 70%;

        margin-left: 9em;
        padding: 5px;

        border: 1px solid black;
    }
</style>

<table class="table">
    <?php if($player->spouse <= 0): ?>
        <?php if($player->spouse < 0): ?>
            <?php if($proposal_user == false): ?>
                <tr><th>Cold Feet!</th></tr>
                <tr>
                    <td>
                        Your prospective spouse got cold feet! You are free to look for love again.
                    </td>
                </tr>
            <?php else: ?>
                <tr><th><?=$proposal_user['user_name']?>'s Proposal</th></tr>
                <tr>
                    <td style="text-align: center;">
                        <form style='display:inline;' action="<?=$self_link?>" method="post">
                            <input type="submit" name="accept_proposal" value="Accept" />
                        </form>
                        <form style='display:inline;' action="<?=$self_link?>" method="post">
                            <input type="submit" name="deny_proposal" value="Deny" />
                        </form>
                    </td>
                </tr>
            <?php endif ?>
        <?php else: ?>
            <tr><th>Send a Proposal</th></tr>
            <tr>
                <td style="text-align: center; padding: 5px;">
                    <form action="<?=$self_link?>" method="post">
                        <label>Username:</label><input type="text" name="user_name" /><br />
                        <input type="submit" name="propose" value="Propose" />
                    </form>
                </td>
            </tr>
        <?php endif ?>
    <?php else: ?>
        <tr><th>Marriage Detail</th></tr>
        <tr>
            <td style="padding: 8px;">
                <label>Spouse: </label><a href="<?=$system->links['members']?>&user=<?=$player->spouse_name?>"><?=$player->spouse_name?></a><br />
                <label>Anniversary:</label><?=Date('F j, Y', $player->marriage_time)?><br />
                <label>Location:</label><?=$spouse['location']?><br />
                <label>Perks:</label><br />
                    <div id="perk_details">
                        <em>Remain faithful, perks tbd!</em>
                    </div>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                <form action="<?=$self_link?>" method="post">
                    <?php if(!isset($_POST['divorce'])): ?>
                        <input type="submit" name="divorce" value="Divorce" />
                    <?php else: ?>
                        Are you certain you would like to proceed with divorce? Any perks you receive from this marriage
                    will be lost. If you marry the same user again, you will have to restart perk progress!
                    <br />
                        <input type="submit" name="confirm_divorce" value="Confirm Divorce" />
                    <?php endif ?>
                </form>
            </td>
        </tr>
    <?php endif ?>
</table>
