<?php
/**
 * @var string $self_link
 */
?>
<table class="table">
    <tr><th>Reset User Password</th></tr>
    <tr>
        <td style="text-align: center;">
            <form action="<?=$self_link?>" method="post">
                Username: <input type="text" name="user_name" />
                <input type="submit" name="reset_password" value="Reset" />
            </form>
        </td>
    </tr>
</table>