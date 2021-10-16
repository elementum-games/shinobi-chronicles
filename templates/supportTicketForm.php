<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var array $request_types
 */
?>
<style type="text/css">
    form label {
        display: inline-block;
        width: 8em;

        font-weight: bold;
    }
    form input[type=text], select{
        width: 250px;
    }
    form textarea {
        margin-left: 8em;
        width: 500px;
        height: 350px;
    }
    form input[type=submit] {
        margin: 3px;
    }
</style>

<table class='table'>
    <tr><th>New Support Request</th></tr>
    <tr><td>
        <form action="<?=$self_link?>" method="post">
            <label>Subject:</label><input type="text" name="subject" /><br />
            <label>Request Type:</label><select name="support_type">
                <?php foreach($request_types as $type): ?>
                    <option value="<?=$type?>"><?=$type?></option>
                <?php endforeach ?>
            </select><br />
            <label>Content:</label><br />
            <textarea name="message"></textarea><br />
            <input type="submit" name="add_support" value="Submit Ticket" />
        </form>
    </td></tr>
</table>
