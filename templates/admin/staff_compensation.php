<?php
/**
 * @var System $system
 * @var string $self_link
 * @var array $staff_members
 * @var array $base_rates
 */
?>
<style>
    label.member {
        display: inline-block;
        margin-left: 10px;
        width: 7em;
    }
</style>
<table class="table">
    <tr><th>Staff Compensation</th></tr>
    <?php if(empty($staff_members)): ?>
        <tr><td style="text-align: center;">No staff members found!</td></tr>
    <?php else: ?>
        <tr>
            <td>
                <form action="<?=$self_link?>" method="post">
                    <?php foreach($staff_members as $x => $member): ?>
                        <input type="checkbox" name="include_<?=$member['user_id']?>" checked="checked" />
                        <label style="font-weight:bold;"><?=$member['user_name']?></label><br />
                        <label class="member">Staff Level:</label><?=StaffManager::$staff_level_names[$member['staff_level']]['long']?><br />
                        <label class="member">Payment:</label><input type="text" name="pay_<?=$member['user_id']?>" value="<?=$base_rates[$member['staff_level']]?>" /><br />
                        <br />
                    <?php endforeach ?>
                    <input type="submit" name="comp_staff" value="Compensate" />
                </form>
            </td>
        </tr>
    <?php endif ?>
</table>
