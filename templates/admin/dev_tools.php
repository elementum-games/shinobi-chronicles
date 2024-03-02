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

<table class='table'>
    <tr><th colspan='2'>Dev Tools</th></tr>
    <tr>
        <th>Jutsu</th>
        <th>Stats</th>
    </tr>
    <tr>
        <td style='text-align: center;'>
            <form action='' method='post'>
                <input type='text' name='cap_jutsu' placeholder='Username' style='margin-bottom: 8px;' value='<?= $_GET['user_name'] ?? '' ?>' /><br />
                <input type='number' name='jutsu_level' min='1' max='100' placeholder='100' style='margin-bottom: 8px' value='100'/><br />
                <input type='submit' value='Set Jutsu Level' />
            </form>
        </td>
        <td>
            <form action='' method='post'>
                <input type='text' name='user' placeholder='Username' value='<?= $_GET['user_name'] ?? '' ?>' /><br />
                <br />

                Rank to set stats to:<br />
                <select name='rank'>
                    <option value='current'>Current Rank</option>
                    <?php for($i = 1; $i <= 4; $i++): ?>
                        <option value='<?= $i ?>'>Rank <?= $i ?></option>
                    <?php endfor; ?>
                </select><br />
                <br />

                <b>Stats</b><br />
                <?php foreach($stats as $stat): ?>
                    <label style='display: inline-block;width:105px;'><?= $stat ?></label>
                    <select name='<?= $stat ?>_percent'>
                        <option value='N/A'>-</option>
                        <?php for($i = 10; $i >= 0; $i--): ?>
                            <?php $percent = $i / 10; ?>
                            <option value='<?= $percent ?>'><?= ($percent * 100) ?>%</option>
                        <?php endfor; ?>
                    </select><br />
                <?php endforeach; ?>
                <br />
                <input type='submit' name='cap_stats' value='Cap Stats' />
            </form>
        </td>
    </tr>
</table>
