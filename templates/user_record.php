<?php
/**
 * @var System $system
 * @var User   $player
 * @var array $warnings
 * @var ?array $warning_to_view
 * @var array  $bans
 */
?>

<table class='table'>
    <?php if(isset($_GET['warning_id'])):?>
        <?php if($warning_to_view != null): ?>
            <tr><td>Invalid warning!</td></tr>
        <?php else: ?>
            <tr><th>Official Warning</th></tr>
            <tr>
                <td>
                    <div>
                        <label style="display: inline-block; width:7em; margin-left: 1rem; font-weight:bold;">Date:</label>
                        <?=Date("F m Y", $warning_to_view['time'])?><br />
                        <label style="display: inline-block; width:7em; margin-left: 1rem; font-weight:bold;">Issued By:</label>
                        <?=$warning_to_view['staff_name']?>
                    </div>
                    <hr />
                    <div style="text-align: center;"><?=$warning_to_view['data']?></div>
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
            <?php foreach($warnings as $warning_to_view): ?>
                <tr style="text-align: center;">
                    <td>
                        <?=$warning_to_view['staff_name']?>
                    </td>
                    <td>
                        <?=Date('F j, Y', $warning_to_view['time'])?>
                    </td>
                    <td>
                        <?=($warning_to_view['viewed'] ? 'Yes' : 'No')?>
                    </td>
                    <td>
                        <a href="<?=$system->router->getUrl('account_record', ['warning_id' => $warning_to_view['warning_id']]) ?>">
                            View
                        </a>
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
                    <td colspan="1" style="text-align: center;">
                        <?=Date('F j, Y', $info['time'])?>
                    </td>
                    <td colspan="3">
                        <?=$info['data']?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</table>
