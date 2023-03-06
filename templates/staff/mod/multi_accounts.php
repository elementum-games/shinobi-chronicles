<style>
    .approved {
        background-color: green !important;
    }
    .pending {
        background-color: yellow !important;
    }
    .denied {
        background-color: red !important;
    }
</style>
<div class='submenu'>
    <ul class='submenu'>
        <li style='width:24.55%;'><a href="<?=$self_link?>">Current IP</a></li>
        <li style='width:24.55%;'><a href="<?=$self_link?>&type=last_ip">Last IP</a></li>
        <li style='width:24.55%;'><a href="<?=$self_link?>&type=email">Email</a></li>
        <li style='width:24.55%;'><a href="<?=$self_link?>&type=password">Password</a></li>
    </ul>
</div>
<table class="table" style="table-layout: auto;">
    <tr><th colspan="6">Multi-accounts - <?=System::unSlug($query_type)?></th></tr>
    <?php if(!empty($accounts)): ?>
        <tr>
            <th>Username</th>
            <th style="width:25%;">Password</th>
            <th>Current IP</th>
            <th>Last IP</th>
            <th>Email</th>
            <th></th>
        </tr>
        <?php foreach($accounts as $x => $account): ?>
            <tr style="text-align: center;" class="table_multicolumns">
                <td class="row<?=$x%2+1?> <?=$account['multi_status']?>"><?=$account['user_name']?></td>
                <td class="row<?=$x%2+1?> <?=$account['multi_status']?>" style="word-break: break-word;"><?=$account['password']?></td>
                <td class="row<?=$x%2+1?> <?=$account['multi_status']?>"><?=$account['current_ip']?></td>
                <td class="row<?=$x%2+1?> <?=$account['multi_status']?>"><?=$account['last_ip']?></td>
                <td class="row<?=$x%2+1?> <?=$account['multi_status']?>"><?=$account['email']?></td>
                <td class="row<?=$x%2+1?> <?=$account['multi_status']?>">
                    <a href="<?=$self_link?>&action=<?=StaffManager::MULTI_PENDING?>&user_id=<?=$account['user_id']?>">Pend</a><br />
                    <a href="<?=$self_link?>&action=<?=StaffManager::MULTI_APPROVED?>&user_id=<?=$account['user_id']?>">Approve</a><br />
                    <a href="<?=$self_link?>&action=<?=StaffManager::MULTI_DENIED?>&user_id=<?=$account['user_id']?>">Deny</a>
                </td>
            </tr>
        <?php endforeach ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align: center;">
                No multiple accounts found!
            </td>
        </tr>
    <?php endif ?>
</table>