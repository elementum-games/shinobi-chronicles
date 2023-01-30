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

<div class="submenu">
    <ul class="submenu">
        <li style="width:49.7%;"><a href="<?=$self_link?>">Settings</a></li>
        <li style="width:49.7%;"><a href="<?=$self_link?>&view=account">Account Info</a></li>
    </ul>
</div>

<table class='table'>
    <?php if(!isset($_GET['view'])): ?>
        <tr><th>Avatar</th></tr>
        <tr>
            <td style='text-align:center;'>
                <div style='float:left;width:200px;'>
                    <?=$system->imageCheck($player->avatar_link, $player->getAvatarSize())?>
                </div>
                <?php if(!$player->checkBan(StaffManager::BAN_TYPE_AVATAR)):?>
                    <div>
                        <b>Avatar info:</b><br />
                        Avatar must be hosted on another website<br />
                        Default limit: <?=$player->getAvatarSize()?> x <?=$player->getAvatarSize()?> pixels<br />
                        Avatar can be larger than the limit, but it will be resized<br />
                        Max filesize: <?=$player->getAvatarFileSize()?><br />
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
        <tr><th>Password</th></tr>
        <tr>
            <td>
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
        <tr><th>Layout</th></tr>
        <tr>
            <td style="text-align: center;">
                <form action='<?=$self_link?>' method='post'>
                    <select name='layout'>";
                        <?php foreach($layouts as $layout):?>
                            <option value='<?=$layout?>' <?=($player->layout == $layout ? "selected='selected'" : "")?>><?=ucwords(str_replace("_", " ", $layout))?></option>
                        <?php endforeach ?>
                    </select>
                    <input type='submit' name='change_layout' value='Change' />
                </form>
            </td>
        </tr>
        <tr><th>Blocklist</th></tr>
        <tr>
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
        <tr><th>Journal</th></tr>
        <tr>
            <td style='text-align:center;'>
                <?php if(!$player->checkBan(StaffManager::BAN_TYPE_JOURNAL)):?>
                    <?php if($player->staff_level && !$player->forbidden_seal_loaded): ?>
                        <i>(Images will be resized down to a max of <?=$psuedoSeal->journal_image_x?>x<?=$psuedoSeal->journal_image_y?>)</i>
                    <?php elseif($player->forbidden_seal_loaded && $player->forbidden_seal->level != 0): ?>
                        <i>(images will be resized down to a max of <?=$player->forbidden_seal->journal_image_x?>x<?=$player->forbidden_seal->journal_image_y?>)</i>
                    <?php else: ?>
                        <i>(images will be resized down to a max of 200x300)</i>
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
    <?php else: ?>
        <?php if(isset($_GET['warning_id'])):?>
            <?php if($warning == false): ?>
                <tr><td>Invalid warning!</td></tr>
            <?php else: ?>
                <tr><th>Official Warning</th></tr>
                <tr>
                    <td>
                        <?=$warning['data']?>
                    </td>
                </tr>
            <?php endif ?>
        <?php else: ?>
            <tr><th colspan="4">Account Details</th></tr>
            <tr><th colspan="4">Official Warning(s)</th></tr>
            <?php if(empty($warnings)): ?>
                <tr><td colspan="4" style="text-align: center;">No Warnings</td></tr>
            <?php else: ?>
                <tr>
                    <th>Issued By</th>
                    <th>Date</th>
                    <th>Viewed</th>
                    <th></th>
                </tr>
                <?php foreach($warnings as $warning): ?>
                    <tr style="text-align: center;">
                        <td>
                            <?=$warning['staff_name']?>
                        </td>
                        <td>
                            <?=Date('F j, Y', $warning['time'])?>
                        </td>
                        <td>
                            <?=($warning['viewed'] ? 'Yes' : 'No')?>
                        </td>
                        <td>
                            <a href="<?=$system->links['settings']?>&view=account&warning_id=<?=$warning['warning_id']?>">View</a>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
            <tr><th colspan="4">Bans</th></tr>
            <?php if($bans == false): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">
                        No bans.
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <th colspan="1">Date</th>
                    <th colspan="3">Ban Info</th>
                </tr>
                <?php foreach($bans as $info): ?>
                    <tr>
                        <td colspan="1">
                            <?=Date('F j, Y', $info['time'])?>
                        </td>
                        <td colspan="3">
                            <?=$info['data']?>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
        <?php endif ?>
    <?php endif ?>
</table>