<?php
/**
 * @var System $system
 * @var array $stats
 */


?>

<?php $system->printMessage(); ?>

<?php if ($system->environment == System::ENVIRONMENT_PROD): ?>
    <h1 class='center'>WARNING: PRODUCTION ENVIRONMENT</h1>
<?php endif; ?>

<table>
    <tr><th>Dev Tools</th></tr>
    <tr><td>
        <form action='' method='post'>
            <input type='text' name='cap_jutsu' /><br />
            <input type='submit' value='Cap Jutsu' />
        </form>
        <br />
        <form action='' method='post'>
            <input type='text' name='user' /><br />
            <select name='rank'>
                <option value='current'>Current Rank</option>
                <?php for($i = 2; $i <= 8; $i++): ?>
                    <option value='<?= $i ?>'>Rank <?= $i ?></option>
                <?php endfor; ?>
            </select><br />

            Stats
            <?php foreach($stats as $stat): ?>
                <?= $stat ?>
                <select name='<?= $stat ?>_percent'>
                    <option value='0'>-</option>
                    <?php for($i = 10; $i >= 1; $i--): ?>
                        <?php $percent = $i / 10; ?>
                        <option value='<?= $percent ?>'><?= ($percent * 100) ?>%</option>
                    <?php endfor; ?>
                </select><br />
                <br />
            <?php endforeach; ?>
            <input type='submit' name='cap_stats' value='Cap Stats' />
        </form>
        <br />
    </td></tr>
</table>
