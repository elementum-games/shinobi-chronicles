<?php
/**
 * @var System $system
 * @var User $player
 * @var ForbiddenSeal $psuedoSeal
 *
 * @var string $self_link
 * @var string $journal
 * @var string $list
 *
 * @var array $layouts
 *
 * @var int $max_journal_length
 */
?>

<div class='submenu'>
    <ul class='submenu'>
        <li style='width:100%;'><a href='<?= $system->router->getUrl('account_record')?>'>Account Record</a></li>
   </ul>
</div>
<div class='submenuMargin'></div>

<table class='table'>
    <tr><th colspan='2'>Avatar</th></tr>
    <tr>
        <td colspan='2' style='text-align:center;'>
            <div style='float:left;width:200px;'>
                <?=$system->imageCheck($player->avatar_link, $player->getAvatarSize())?>
            </div>
            <?php if(!$player->checkBan(StaffManager::BAN_TYPE_AVATAR)):?>
                <div>
                    <b>Avatar info:</b><br />
                    Avatar must be hosted on another website<br />
                    Default limit: <?=$player->getAvatarSize()?> x <?=$player->getAvatarSize()?> pixels<br />
                    Avatar can be larger than the limit, but it will be resized<br />
                    Max filesize: <?=$player->getAvatarFileSizeDisplay()?><br />
                    <br />
                    <form action='<?=$self_link?>' method='post'>
                        <input type='text' name='avatar_link' value='<?=$player->avatar_link?>' style='width:250px;margin-bottom:5px;' />
                        <input type='submit' name='change_avatar' value='Change' />
                    </form>
                </div>
                <br style='clear:both;' />
            <?php else: ?>
                <p style="margin-top:90px;">You are currently banned from changing your avatar.</p>
            <?php endif ?>
        </td>
    </tr>
    <tr><th colspan='2'>Password</th></tr>
    <tr>
        <td colspan='2'>
            <form action='<?=$self_link?>' method='post'>
                <div style="margin-left:145px;">
                    <label for='current_password' style='width:150px;margin-bottom:5px;'>Current password:</label>
                    <input type='password' name='current_password' /><br />
                    <label for='new_password' style='width:150px;margin-bottom:5px;'>New password:</label>
                    <input type='password' name='new_password' /><br />
                    <label for='confirm_new_password' style='width:150px;margin-bottom:5px;'>Confirm new password:</label>
                    <input type='password' name='confirm_new_password' />
                </div>
                <p style='text-align:center;margin:0;'>
                    <input type='submit' name='change_password' value='Change' />
                </p>
            </form>
        </td>
    </tr>
    <tr>
        <th>Level and Rank Settings</th>
        <th>Layout</th>
    </tr>
    <tr>
        <td style="text-align: center;">
            By changing these settings, you will opt out of level or ranking up.<br />
            (Note: Disabling level up will only allow you to hold 5 levels lower than your exp)
            <form action="<?=$self_link?>" method="post">
                Allow level up<input type="checkbox" name="level_up" <?=($player->level_up ? "checked='checked'" : "")?> /><br />
                Allow rank up<input type="checkbox" name="rank_up" <?=($player->rank_up ? "checked='checked'" : "")?> /><br />
                <input type="submit" name="level_rank_up" value="Update" />
            </form>
        </td>
        <td style="text-align: center;">
            <label>Layout</label>
            <form action='<?=$self_link?>' method='post'>
                <select name='layout'>";
                    <?php foreach($layouts as $layout):?>
                        <option value='<?=$layout?>' <?=($player->layout == $layout ? "selected='selected'" : "")?>><?=ucwords(str_replace("_", " ", $layout))?></option>
                    <?php endforeach ?>
                </select>
                <input type='submit' name='change_layout' value='Change' />
            </form>
            <?php if($player->layout == "new_geisha"): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
                <div>
                    <label>Avatar Style</label>
                    <form action='<?=$system->router->getUrl('settings')?>' method='post'>
                        <select name='avatar_style'>";
                            <?php foreach ($avatar_styles as $style_key => $style_value): ?>
                                <option value='<?= $style_key ?>' <?= $style_key == $avatar_style ? "selected='selected'" : "" ?>><?= ucwords($style_value) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <br />
                        <input type='submit' name='change_avatar_style' value='Change' />
                    </form>
                </div>
                <div>
                    <label>Sidebar Position</label>
                    <form action='<?=$system->router->getUrl('settings')?>' method='post'>
                        <select name='sidebar_position'>";
                            <option value='left' <?=($sidebar_position == "left" ? "selected='selected'" : "")?>>Left</option>
                            <option value='right' <?=($sidebar_position == "right" ? "selected='selected'" : "")?>>Right</option>
                        </select>
                        <br />
                        <input type='submit' name='change_sidebar_position' value='Change' />
                    </form>
                </div>
                <div>
                    <label>Enable Alerts</label>
                    <form action='<?=$system->router->getUrl('settings')?>' method='post'>
                        <select name='enable_alerts'>";
                            <option value='1' <?=($enable_alerts == true ? "selected='selected'" : "")?>>True</option>
                            <option value='0' <?=($enable_alerts == false ? "selected='selected'" : "")?>>False</option>
                        </select>
                        <br />
                        <input type='submit' name='change_enable_alerts' value='Change' />
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
        </td>
    </tr>
    <tr>
        <th>Explicit Language</th>
        <th>Blocklist</th>
    </tr>
    <tr>
        <td style='text-align:center;'>
            Censor Explicit Language<br />
            <form action='<?=$self_link?>' method='post'>
                <select name='censor_explicit_language'>
                    <option value='on' <?= ($player->censor_explicit_language ? "selected='selected'" : "") ?>>On</option>
                    <option value='off' <?= (!$player->censor_explicit_language ? "selected='selected'" : "") ?>>Off</option>
                </select>
                <input type='submit' value='Change' style='margin-left: 5px;' />
            </form>
        </td>
        <td style='text-align:center;'>
            <?php if(!empty($player->blacklist)): ?>
                <?=$list?>
            <?php else: ?>
                <p style="text-align: center;">No blocked users!</p>
            <?php endif ?>
            <br />
            <form action='<?=$self_link?>' method='post'>
                <input type='text' name='blacklist_name' style='width:250px;margin-bottom:5px;' /> <br />
                <input type='submit' name='blacklist_add' value='Add' />
                <input type='submit' name='blacklist_remove' value='Remove' />
            </form>
        </td>
    </tr>
    <tr><th colspan='2'>Journal</th></tr>
    <tr>
        <td colspan='2' style='text-align:center;'>
            <?php if(!$player->checkBan(StaffManager::BAN_TYPE_JOURNAL)):?>
                <?php if($player->staff_level && $player->forbidden_seal->level == 0): ?>
                    <i>(Images will be resized down to a max of <?=$psuedoSeal->journal_image_x?>x<?=$psuedoSeal->journal_image_y?>)</i>
                <?php else: ?>
                    <i>(Images will be resized down to a max of <?=$player->forbidden_seal->journal_image_x?>x<?=$player->forbidden_seal->journal_image_y?>)</i>
                <?php endif ?>
                <script type=text/javascript>
                    $(document).ready(function(){
                        $('#journalMessage').keyup(function (evt) {
                            if(this.value.length >= <?=$max_journal_length?> - 20)
                            {
                                let remaining = <?=$max_journal_length?> - this.textLength;
                                $('#remainingCharacters').text('Characters remaining: ' + remaining + ' out of ' + <?=$max_journal_length?>);
                            }
                            else
                            {
                                $('#remainingCharacters').text('');
                            }
                        })
                    });
                </script>
            <form action='<?=$self_link?>' method='post'>
                <textarea
                    style='height:350px;width:95%;margin:10px 0;'
                    name='journal'
                    id='journalMessage'
                    maxlength='<?= $max_journal_length ?>'
                ><?= stripslashes($journal) ?></textarea>
                <br />
                <span id='remainingCharacters' class='red'></span>
                <input type='submit' name='change_journal' value='Update' />
            </form>
            <?php else: ?>
                <p>You are currently banned from editing your journal.</p>
            <?php endif ?>
        </td>
    </tr>
</table>