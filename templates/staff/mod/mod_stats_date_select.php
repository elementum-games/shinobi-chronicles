<?php
/**
 * @var System $system
 * @var DateTimeImmutable $PREVIOUS_MONTH
 * @var array $months
 * @var string $self_link
 * @var int $min_year
 * @var int $max_year
 */
?>

<table class="table">
    <tr><th>Select Date Range</th></tr>
    <tr>
        <td style="text-align: center;">
            <form action="<?=$self_link?>" method="post">
                <label style="display:inline-block; width: 100px;">Begin Date:</label>
                    <select name="start_month">
                        <?php foreach($months as $month): ?>
                            <option value="<?=$month?>" <?=($month == $PREVIOUS_MONTH->format('M') ? 'selected' : '') ?>><?=$month?></option>
                        <?php endforeach ?>
                    </select>
                    &nbsp;
                    <select name="start_day">
                        <?php for($i=1;$i<32;$i++): ?>
                            <option value="<?=$i?>" <?=($i == $PREVIOUS_MONTH->format('j') ? 'selected' : '') ?>><?=$i?></option>
                        <?php endfor ?>
                    </select>
                    &nbsp;
                    <select name="start_year">
                        <?php for($i=$min_year;$i<=$max_year;$i++): ?>
                            <option value="<?=$i?>" <?=($i == $PREVIOUS_MONTH->format('Y') ? 'selected' : '') ?>><?=$i?></option>
                        <?php endfor ?>
                    </select>
                <br />
                <label style="display:inline-block; width: 100px;">End Date:</label>
                    <select name="end_month">
                        <?php foreach($months as $month): ?>
                            <option value="<?=$month?>" <?=($month == $system->SERVER_TIME->format('M') ? 'selected' : '') ?>><?=$month?></option>
                        <?php endforeach ?>
                    </select>
                    &nbsp;
                    <select name="end_day">
                        <?php for($i=1;$i<32;$i++): ?>
                            <option value="<?=$i?>" <?=($i == $system->SERVER_TIME->format('j') ? 'selected' : '') ?>><?=$i?></option>
                        <?php endfor ?>
                    </select>
                    &nbsp;
                    <select name="end_year">
                        <?php for($i=$min_year;$i<=$max_year;$i++): ?>
                            <option value="<?=$i?>" <?=($i == $system->SERVER_TIME->format('Y') ? 'selected' : '') ?>><?=$i?></option>
                        <?php endfor ?>
                    </select>
                <br />
                <input style='margin-top: 8px;' type="submit" name="load_stats" value="Search" />
            </form>
        </td>
    </tr>
</table>