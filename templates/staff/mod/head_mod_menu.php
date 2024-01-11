<?php
/**
 * @var User $player
 * @var string $self_link
 */
?>

<style>
    #content label {
        display:inline-block;
        width: 80px;
    }
    #content div.modForm {
        width: 210px;
        margin: 0 auto;
        text-align: center;
    }
</style>

<br />
<table class='table'>
    <tr><th colspan='2'>Head Moderator actions</th></tr>
    <tr>
        <td colspan="2" style="text-align: center">
            <div class='submenu'>
                <ul class='submenu'>
                    <li style='width:32.9%;'><a href="<?=$self_link?>&view=banned_ips">Banned IP Addresses</a></li>
                    <li style='width:32.9%;'><a href="<?=$self_link?>&view=multi_accounts">Multi-account List</a></li>
                    <li style='width:32.9%;'><a href="<?=$self_link?>&view=mod_logs">Mod Logs</a></li>
                </ul>
            </div>
        </td>
    </tr>
    <tr><th style='width:50%;'>Unban user</th>
        <th style='width:50%;'>Unban journal/avatar/profile song</th>
    </tr>
    <tr><td>
            <form action='<?=$self_link?>' method='post'>
                <div class="modForm">
                    <p>Username</p>
                    <input type='text' name='user_name' value='<?=($_GET['unban_user_name'] ?? "")?>' /><br />
                    <select name='ban_type' style='width:100px;margin-top:5px;'>
                        <?php foreach(StaffManager::$ban_menu_items as $type): ?>
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
                <div class="modForm">
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
        <th>IP Address Management</th>
        <th>Activate User</th>
    </tr>
    <tr>
        <td style='text-align:center;'>
            <form style='display:inline-block' action='<?=$self_link?>' method='post'>
                <label for='ip_address'>Ban IP address</label><br />
                <input type='text' name='ip_address' value='<?=($_GET['ban_ip_address'] ?? "")?>' /><br />
                <input style='margin-top:5px;' type='submit' name='ban_ip' value='Ban' />
            </form>
            <form style='display:inline-block' action='<?=$self_link?>' method='post'>
                <label for='ip_address'>Unban IP address</label><br />
                <input type='text' name='ip_address' value='<?=($_GET['unban_ip_address'] ?? "")?>' /><br />
                <input style='margin-top:5px;' type='submit' name='unban_ip' value='Unban' />
            </form>
        </td>
        <td style='text-align:center;'>
            <form action='<?=$self_link?>' method='post'>
                <input type='text' name='user_name' />
                <input style='margin-top:5px;' type='submit' name='activate_user' value='Activate' />
            </form>
        </td>
    </tr>
    <tr><th colspan='2'>Global Message</th></tr>
    <tr>
        <td colspan='2' style='text-align:center;'>
            <form action='<?=$self_link?>' method='post'>
                <textarea name='global_message' style='width:475px;height:175px;'></textarea><br />
                <?php if($player->staff_manager->isHeadAdmin()): ?>
                    <input type="checkbox" name="close_sc" /> Close SC for maintenance<br />
                    Close in: <select name="sc_close_time">
                        <?php for($i=5; $i <= 60; $i += 5): ?>
                            <option value="<?=$i?>"><?=$i?> mins</option>
                        <?php endfor ?>
                    </select><br />
                    Close for: <select name="sc_downtime">
                        <?php for($i=5; $i <= 60; $i += 5): ?>
                            <option value="<?=$i?>"><?=$i?> mins</option>
                        <?php endfor ?>

                    </select><br />
                <?php endif ?>
                <input style='margin-top: 5px;' type='submit' value='Post' />
            </form>
        </td>
    </tr>
</table>
