<?php
/**
 * @var string $self_link
 */
?>

<br />
<table class='table'>
    <tr><th colspan='2'>Head Moderator actions</th></tr>
    <tr><th style='width:50%;'>Unban user</th>
        <th style='width:50%;'>Unban journal/avatar/profile song</th>
    </tr>
    <tr><td>
            <form action='<?=$self_link?>' method='post'>
                <style type='text/css'>
                    label {
                        display:inline-block;
                        width: 80px;
                    }
                </style>
                <div style='width:210px;margin-left:auto;margin-right:auto;text-align:center;'>
                    <p>Username</p>
                    <input type='text' name='user_name' value='<?=($_GET['unban_user_name'] ?? "")?>' /><br />
                    <select name='ban_type' style='width:100px;margin-top:5px;'>
                        <?php foreach(StaffManager::$ban_types as $type): ?>
                            <option value="<?=$type?>"><?=ucwords($type)?> Ban</option>
                        <?php endforeach ?>
                    </select>
                </div>
                <p style='margin-top:3px;text-align:center;'>
                    <input type='submit' name='unban' value='Unban'  />
                </p>
            </form>
        </td>
        <td style='text-align:center;'>
            <form action='<?=$self_link?>' method='post'>
                <div style='width:210px;margin-left:auto;margin-right:auto;'>
                    <p>Username</p>
                    <input type='text' name='user_name' value='<?=($_GET['unban_user_name'] ?? "")?>' /><br />
                    <div style='width:50%;float:left;text-align:left;margin-left:9%;'>
                        <p>Journal</p>
                        <input type='checkbox' name='journal' value='unban' /> Unban<br />
                    </div>
                    <div style='width:40%;float:right;text-align:left;'>
                        <p>Avatar</p>
                        <input type='checkbox' name='avatar' value='unban' /> Unban<br />
                    </div>
                </div>
                <p style='text-align:center;margin-top:3px;'>
                    <input type='submit' name='profile_unban' value="Unban" />
                </p>
            </form>
        </td>
    </tr>
    <tr>
        <th>Ban IP Address</th>
        <th>Unban IP Address</th>
    </tr>
    <tr>
        <td style='text-align:center;'>
            <form action='<?=$self_link?>' method='post'>
                <label for='ip_address'>IP address</label><br />
                <input type='text' name='ip_address' value='<?=($_GET['ban_ip_address'] ?? "")?>' /><br />
                <input style='margin-top:5px;' type='submit' name='ban_ip' value='Ban' />
            </form>
        </td>
        <td style='text-align:center;'>
            <form action='<?=$self_link?>' method='post'>
                <label for='ip_address'>IP address</label><br />
                <input type='text' name='ip_address' value='<?=($_GET['unban_ip_address'] ?? "")?>' /><br />
                <input style='margin-top:5px;' type='submit' name='unban_ip' value='Unban' />
            </form>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center">
            <a href="<?=$self_link?>&view=banned_ips">View Banned IP Addresses</a>
        </td>
    </tr>
    <tr><th colspan='2'>Global Message</th></tr>
    <tr>
        <td colspan='2' style='text-align:center;'>
            <form action='<?=$self_link?>' method='post'>
                <textarea name='global_message' style='width:475px;height:175px;'></textarea><br />
                <input style='margin-top: 5px;' type='submit' value='Post' />
            </form>
        </td>
    </tr>
</table>
