<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var array $request_types
 */
?>

<table class='table'>
    <tr><th>New Support Request</th></tr>
    <tr><td>
        <form action="<?=$self_link?>" method="post">
            <label style="width:8em; font-weight:bold;">Subject:</label><input type="text" name="subject" /><br />
            <label style="width:8em; font-weight:bold;">Request Type:</label><select name="support_type">
                <?php foreach($request_types as $type): ?>
                    <option value="<?=$type?>"><?=$type?></option>
                <?php endforeach ?>
            </select><br />
            <label style="width:8em; font-weight:bold;">Content:</label><br />
            <textarea name="message" style="display:inline-block; width:500px;height:200px;margin-left:8em;"></textarea><br />
            <br />
            <input type="submit" name="add_support" value="Submit Ticket" />
            <input type="submit" name="add_support_prem" value="Submit Premium Ticket*" /><br />
            <br />
            *You may submit a premium ticket at the cost AK to give your support higher priority. Account related issues
            cost 10AK each, all other supports are 1AK each. Bug reports, reporting staff members, etc., are not available
            for premium supports, they will be submitted at normal priority at no cost.
        </form>
    </td></tr>
</table>
