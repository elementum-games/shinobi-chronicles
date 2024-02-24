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
 * @var Layout $current_layout
 *
 * @var array $layouts
 *
 * @var int $max_journal_length
 */
?>

<style>
    .player_card {
        height: 220px;
        width: 435px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
        .player_card .avatar_frame {
            flex-basis: 50%;
        }
    .player_card_background {
        min-height: 250px;
        min-width: 450px;
        opacity: 0.75;
        position: relative;
    }
    .player_card_bg_wrapper {
        height: 220px;
        width: 435px;
        overflow: hidden;
        position: absolute;
    }
    .player_card_details {
        flex-basis: 50%;
        display: flex;
        flex-direction: column;
        color: var(--font-color-3);
        font-family: var(--font-secondary);
        z-index: 1;
        height:200px;
    }

    p.notificationDisableOption {
        display: inline-block;
        width: 150px;
        text-align: left;
    }
</style>

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
                    <?php if(!$player->forbidden_seal->direct_avatar_upload): ?>
                        <form action='<?=$self_link?>' method='post'>
                            <input type='text' name='avatar_link' value='<?=$player->avatar_link?>' style='width:250px;margin-bottom:5px;' />
                            <input type='submit' name='change_avatar' value='Change' />
                        </form>
                    <?php else: ?>
                    <?php endif ?>
                </div>
                <br style='clear:both;' />
            <?php else: ?>
                <p style="margin-top:90px;">You are currently banned from changing your avatar.</p>
            <?php endif ?>
        </td>
    </tr>
    <?php if ($system->isDevEnvironment() && $system->enable_dev_only_features): ?>
    <tr><th colspan='2'>Player Card</th></tr>
    <tr>
        <td colspan="2">
           <div style="display: flex">
            <?php if(!$player->checkBan(StaffManager::BAN_TYPE_AVATAR)):?>
                <div class="player_card">
                    <div class="player_card_bg_wrapper">
                        <img class="player_card_background" src="<?= $card_image ?>" />
                    </div>
                    <div style="max-width: 200px; max-height: 200px;" class="avatar_frame <?= $avatar_style ?> <?= $user_color ?> <?= $avatar_frame ?>">
                        <img class="<?= $avatar_style ?>" src="<?= $player->avatar_link ?>" />
                    </div>
                    <div class="player_card_details">
                        <div class="player_card_name <?= $user_color ?>"> <?= $player->user_name ?></div>
                        <div class="player_card_title"></div>
                        <div class="player_card_rank"></div>
                        <div class="player_card_village"><?= $player->village->name ?></div>
                        <div class="player_card_clan"><?= $player->clan?->name ?></div>
                        <div class="player_card_team"><?= $player->team?->name ?></div>
                        <div class="player_card_bloodline"><?= $player->bloodline?->name ?></div>
                    </div>
                </div>
                <div style="margin-left: 5px">
                    <b>Player Card info:</b><br />
                    Images must be hosted on another website<br />
                    Background Limit: 450 x 250 pixels<br />
                    Banner Limit: 450 x 40 pixels<br />
                    Images can be larger than the limit, but will be resized<br />
                    Max filesize: <?=$player->getAvatarFileSizeDisplay()?><br />
                    <br />
                    <form action='<?=$self_link?>' method='post'>
                        <input type='text' name='card_image' value='<?=$card_image?>' style='width:250px;margin-bottom:5px;' />
                        <input type='submit' name='change_card_image' value='Change' />
                    </form>
                    <br />
                    <form action='<?=$self_link?>' method='post'>
                        <input type='text' name='banner_image' value='<?=$banner_image?>' style='width:250px;margin-bottom:5px;' />
                        <input type='submit' name='change_banner_image' value='Change' />
                    </form>
                </div>
                <br style='clear:both;' />
            <?php else: ?>
                <p style="margin-top:90px;">You are currently banned from changing your avatar.</p>
            <?php endif ?>
            </div>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <th>Password</th>
        <th>Notification Settings</th>
    </tr>
    <tr>
        <td>
            <form action='<?=$self_link?>' method='post'>
                <div style="text-align: center">
                    <label for='current_password' style='width:150px;margin-bottom:5px;'>Current password:</label>
                    <input type='password' name='current_password' /><br />
                    <label for='new_password' style='width:150px;margin-bottom:5px;'>New password:</label>
                    <input type='password' name='new_password' /><br />
                    <label for='confirm_new_password' style='width:150px;margin-bottom:0px;'>Confirm new password:</label>
                    <input type='password' name='confirm_new_password' />
                </div>
                <p style='text-align:center;'>
                    <input type='submit' name='change_password' value='Change' />
                </p>
            </form>
        </td>
        <td style="text-align: center;">
            <form action="<?=$self_link?>" method="post">
                <b>General Notifications</b><br />
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_SPAR?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_SPAR) ? "checked='checked'" : "")?> />Spar</p>
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_TEAM ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_TEAM) ? "checked='checked'" : "")?> />Team Invites</p><br />
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_MARRIAGE ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_MARRIAGE) ? "checked='checked'" : "")?> />Proposals</p>
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_EVENT ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_EVENT) ? "checked='checked'" : "")?> />Events</p><br />
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_CHAT ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_CHAT) ? "checked='checked'" : "")?> />Chat Mentions</p>
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_NEWS ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_NEWS) ? "checked='checked'" : "")?> />News</p><br />
                <br />
                <b>War-based Notifications</b><br />
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_CARAVAN ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_CARAVAN) ? "checked='checked'" : "")?> />Caravan</p>
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_RAID ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_RAID) ? "checked='checked'" : "")?> />Raid</p><br />
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_KAGE_CHANGE ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_KAGE_CHANGE) ? "checked='checked'" : "")?> />Kage Change</p>
                <p class="notificationDisableOption"><input type="checkbox" name="<?= NotificationManager::NOTIFICATION_DIPLOMACY ?>"
                        <?=(!$player->blocked_notifications->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_DIPLOMACY) ? "checked='checked'" : "")?> />Diplomatic</p><br />
                <input type="submit" style='margin-top: 5px' name="update_notifications" value="Update" />
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
                <input type="submit" style='margin-top: 5px' name="level_rank_up" value="Update" />
            </form>
        </td>
        <td style="text-align: center;">
            <label>Layout</label>
            <form action='<?=$self_link?>' method='post'>
                <select name='layout'>";
                    <?php foreach($layouts as $layout):?>
                        <option value='<?=$layout?>' <?=($player->layout == $layout ? "selected='selected'" : "")?>><?=ucwords(str_replace("_", " ", $layout_names[$layout]))?></option>
                    <?php endforeach ?>
                </select>
                <input type='submit' name='change_layout' value='Change' />
            </form>
            <span><i>Note: Legacy layouts may not be compatible with all pages</i></span>
            <?php if($system->layout->usesV2Interface()): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px; flex-wrap: wrap">
                <div>
                    <label>Avatar Style</label>
                    <form action='<?=$system->router->getUrl('settings')?>' method='post'>
                        <select name='avatar_style'>";
                            <?php foreach ($avatar_styles as $style_key => $style_value): ?>
                                <option value='<?= $style_key ?>' <?= $style_key == $avatar_style ? "selected='selected'" : "" ?>><?= ucwords($style_value) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <br />
                        <input type='submit' style='margin-top: 7px' name='change_avatar_style' value='Change' />
                    </form>
                </div>
                <div>
                    <label>Avatar Frame</label>
                    <form action='<?=$system->router->getUrl('settings')?>' method='post'>
                        <select name='avatar_frame'>";
                            <?php foreach ($avatar_frames as $frame_key => $frame_value): ?>
                                <option value='<?= $frame_key ?>' <?= $frame_key == $avatar_frame ? "selected='selected'" : "" ?>><?= ucwords($frame_value) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <br />
                        <input type='submit' style='margin-top: 5px' name='change_avatar_frame' value='Change' />
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
                        <input type='submit' style='margin-top: 5px' name='change_sidebar_position' value='Change' />
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
                        <input type='submit' style='margin-top: 5px' name='change_enable_alerts' value='Change' />
                    </form>
                </div>
                <div>
                    <label>Sidebar Collapsed (Mobile)</label>
                    <form action='<?=$system->router->getUrl('settings')?>' method='post'>
                        <select name='sidebar_collapse'>";
                            <option value='closed' <?=($sidebar_collapse == "closed" ? "selected='selected'" : "")?>>Closed</option>
                            <option value='open' <?=($sidebar_collapse == "open" ? "selected='selected'" : "")?>>Open</option>
                        </select>
                        <br />
                        <input type='submit' style='margin-top: 5px' name='change_sidebar_collapse' value='Change' />
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
            <?= $player->blacklist->generateSettingsList($self_link) ?>
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