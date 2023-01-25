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

<table class='table'>
    <tr><th>Avatar</th></tr>
    <tr>
        <td style='text-align:center;'>
            <div style='float:left;width:200px;'>
                <?=$system->imageCheck($player->avatar_link, $player->getAvatarSize())?>
            </div>
            <div>
                <b>Avatar info:</b><br />
                Avatar must be hosted on another website<br />
                Default limit: <?=$player->getAvatarSize()?> x <?=$player->getAvatarSize()?> pixels<br />
                Avatar can be larger than the limit, but it will be resized<br />
                Max filesize: <?=$player->getAvatarFileSize()?><br />
            </div>
            <br style='clear:both;' />
            <br />
            <?php if(!$player->avatar_ban):?>
                <form action='<?=$self_link?>' method='post'>
                    <input type='text' name='avatar_link' value='<?=$player->avatar_link?>' style='width:250px;' /><br />
                    <input type='submit' name='change_avatar' value='Change' />
                </form>
            <?php else: ?>
                <p>You are currently banned from changing your avatar.</p>
            <?php endif ?>
        </td>
    </tr>
    <tr><th>Password</th></tr>
    <tr>
        <td>
            <form action='<?=$self_link?>' method='post'>
                <label for='current_password' style='width:150px;'>Current password:</label>
                <input type='password' name='current_password' /><br />
                <label for='new_password' style='width:150px;'>New password:</label>
                <input type='password' name='new_password' /><br />
                <label for='confirm_new_password' style='width:150px;'>Confirm new password:</label>
                <input type='password' name='confirm_new_password' /><br />
                <p style='text-align:center;'>
                    <input type='submit' name='change_password' value='Change' />
                </p>
            </form>
        </td>
    </tr>
    <tr><th>Layout</th></tr>
    <tr>
        <td>
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
                <input type='text' name='blacklist_name' style='width:250px;' /> <br />
                <input type='submit' name='blacklist_add' value='Add' />
                <input type='submit' name='blacklist_remove' value='Remove' />
            </form>
        </td>
    </tr>
    <tr><th>Journal</th></tr>
    <tr>
        <td style='text-align:center;'>
            <?php if($player->staff_level && !$player->forbidden_seal_loaded): ?>
                <i>(Images will be resized down to a max of <?=$psuedoSeal->journal_image_x?>x<?=$psuedoSeal->journal_image_y?>)</i>
            <?php elseif($player->forbidden_seal_loaded && $player->forbidden_seal->level != 0): ?>
                <i>(images will be resized down to a max of <?=$player->forbidden_seal->journal_image_x?>x<?=$player->forbidden_seal->journal_image_y?>)</i>
            <?php else: ?>
                <i>(images will be resized down to a max of 200x300)</i>
            <?php endif ?>
            <?php if(!$player->journal_ban):?>
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
                <br />
                <input type='submit' name='change_journal' value='Update' />
            </form>
            <?php else: ?>
                <p>You are currently banned from editing your journal.</p>
            <?php endif ?>
        </td>
    </tr>
</table>