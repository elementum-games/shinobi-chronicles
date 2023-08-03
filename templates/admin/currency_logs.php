<?php
/**
 * @var System $system
 * @var array $logs
 *
 * @var int $previous
 * @var int $next
 * @var int $offset
 * @var int $max
 *
 * @var int|null $character_id
 * @var string|null $currency_type
 */

 $self_link = $system->router->getUrl('admin', [
     'page' => 'logs',
     'log_type' => 'currency_logs',
     'character_id' => $character_id ?? 0
 ])
?>

<style>
    .currency_log_filters {
        display:flex;
        justify-content: space-evenly;
    }
    .currency_log_filters p {
        margin: 0;
    }
    .currency_log_filters p:last-child {
        justify-self: flex-end;
    }
</style>
<table class='table'>
    <tr><td style='text-align: center;'>
        <form action="<?= $system->router->getUrl('admin') ?>" method="get">
            <input type='hidden' name='id' value='<?= Router::PAGE_IDS['admin'] ?>' />
            <input type='hidden' name='page' value='logs' />
            <input type='hidden' name='log_type' value='currency_logs' />

            <div class='currency_log_filters'>
                <p>
                    Character ID: <input type='number' name='character_id' value='<?= $character_id ?? 0 ?>' />
                </p>
                <p>
                    Currency Type:
                    <select name='currency_type'>
                        <option value='all'>All</option>
                        <option value='premium_credits'>Ancient Kunai</option>
                        <option value='money'>Yen</option>
                    </select>
                </p>
                <p>
                    <input type='submit' value='Search' />
                </p>
            </div>
        </form>
    </td></tr>
</table>

<table class='table'>
    <?php if(empty($logs)): ?>
        <tr><td colspan='7' style="text-align: center;">No logs!</td> </tr>
    <?php else: ?>
        <tr>
            <th style="width:15%;">Date</th>
            <th style="width:5%;">User ID</th>
            <th style="width:9%;">Currency Type</th>
            <th>Previous Balance</th>
            <th>New Balance</th>
            <th>Trans. Amount</th>
            <th>Description</th>
        </tr>
        <?php foreach($logs as $log): ?>
            <tr style="text-align: center;">
                <td><?=Date(StaffManager::DATE_FORMAT, $log['transaction_time'])?></td>
                <td><?=$log['character_id']?></td>
                <td><?=System::unSlug($log['currency_type'])?></td>
                <td><?=number_format($log['previous_balance'])?></td>
                <td><?=number_format($log['new_balance'])?></td>
                <td><?=number_format($log['transaction_amount'])?></td>
                <td><?=$log['transaction_description']?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php if(!empty($logs)): ?>
    <div style="text-align: center">
        <?php if($offset != 0): ?>
            <a href="<?=$self_link?>&offset=<?=$previous?>">Previous</a>
        <?php endif ?>
        <?php if($max != 0 && $max != $offset): ?>
            <?php if($offset != 0): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            <?php endif ?>
            <a href="<?=$self_link?>&offset=<?=$next?>">Next</a>
        <?php endif ?>
    </div>
<?php endif; ?>


