<?php
/**
 * @var $self_link
 */
?>

<table id='mod_panel' class='table'>
    <tr>
        <th style='width:50%;'>Ban user</th>
        <th style='width:50%;'>Ban journal/avatar/profile song</th>
    </tr>
    <tr>
        <td>
            <form action='<?=$self_link?>' method='post'>
                <style type='text/css'>
                    label {
                        display:inline-block;
                        width: 80px;
                    }
                </style>
                <div style='width:210px;margin-left:auto;margin-right:auto;text-align:center;'>
                    <p>Username</p>
                    <input type='text' name='user_name' value='<?=($_GET['ban_user_name'] ?? "")?>' /><br />
                    <div style='text-align:left;padding-top:13px;'>
                        <label for='ban_type'>Ban type:</label>
                        <select name='ban_type' style='width:100px;'>
                            <?php foreach(StaffManager::$ban_types as $type): ?>
                                <option value="<?=$type?>"><?=ucwords($type)?> Ban</option>
                            <?php endforeach ?>
                        </select>
                        <p style='margin-top:8px;'>
                            <label for='ban_length'>Ban length:</label>
                            <select name='ban_length' style='width:100px;'>
                                <?php foreach($ban_lengths as $id => $name): ?>
                                    <option value="<?=$id?>"><?=$name?></option>
                                <?php endforeach ?>
                            </select>
                        </p>
                    </div>
                </div>
                <p style='margin-top:3px;text-align:center;'>
                    <input type='submit' name='ban' value='Ban'  />
                </p>
            </form>
        </td>
        <td style='text-align:center;'>
            <form action='<?=$self_link?>' method='post'>
                <div style='width:210px;margin-left:auto;margin-right:auto;'>
                    <p>Username</p>
                    <input type='text' name='user_name' value='<?=($_GET['ban_user_name'] ?? "")?>' /><br />
                    <div style='width:50%;float:left;text-align:left;margin-left:9%;'>
                        <p>Journal</p>
                        <input type='checkbox' name='journal[]' value='ban' /> Ban<br />
                        <input type='checkbox' name='journal[]' value='remove' /> Remove<br />
                    </div>
                    <div style='width:40%;float:right;text-align:left;'>
                        <p>Avatar</p>
                        <input type='checkbox' name='avatar[]' value='ban' /> Ban<br />
                        <input type='checkbox' name='avatar[]' value='remove' /> Remove<br />
                    </div>
                </div>
                <p style='text-align:center;margin-top:3px;'>
                    <input type='submit' name='profile_ban' />
                </p>
            </form>
        </td>
    </tr>
    <tr><th colspan='2'>View Record</th></tr>
    <tr><td colspan='2' style='text-align:center;'>
            <form action='<?=$self_link?>' method='get'>
                <input type='hidden' name='id' value='16' />
                Username<br />
                <input type='text' name='view_record' /><br />
                <input style='margin-top: 5px;' type='submit' value='View' />
            </form>
        </td>
    </tr>
</table>
