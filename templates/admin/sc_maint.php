<style>
	.maint_label {
		display: inline-block;
		width: 100px;
		margin-bottom: 5px;
	}
	.small_text {
		width: 50px;
	}
</style>
<?php if($system->UPDATE_MAINTENANCE != null || !$system->SC_OPEN): ?>
    <table class="table">
        <tr><th><?=($system->SC_OPEN) ? "Stop Maintenance" : "Open SC"?></th></tr>
        <tr>
            <td style='text-align: center;'>
                <form action="<?=$self_link?>" method="post">
					<?php if(!$system->SC_OPEN): ?>
                    	Are you sure you would like to reopen SC?<br />
					<?php else: ?>
						Are you sure you want to cancel the maintenance period?<br />
					<?php endif ?>
                    <input type='submit' name='open_sc' value=<?=($system->SC_OPEN) ? 'Stop' : 'Open'?> />
                </form>
            </td>
        </tr>
    </table>
<?php else: ?>
	<table class="table">
		<tr><th colspan='2'>Initiate Server Maintenance</th></tr>
		<tr>
			<th>Planned Maintenance</th>
			<th>Close Server</th>
		</tr>
		<tr>
			<td style='text-align: center;'>
				<form action="<?=$self_link?>" method="post">
					You must allow at least 5 minutes notice of site closure.<br />
					You must allow at least 5 minutes of maintenance.<br />
					<label class='maint_label'>Begin Maint:</label>
					<input class='small_text' name='begin_time' value='5'>
					<select name='begin_type'>
						<option value='min'>Minute(s)</option>
						<option value='hour'>Hour(s)</option>
					</select><br />
					<label class='maint_label'>End Maint:</label>
					<input class='small_text' name='end_time' value='30'>
					<select name='end_type'>
						<option value='min'>Minute(s)</option>
						<option value='hour'>Hour(s)</option>
					</select><br />
					<input type="submit" name="start_maint" value="Start" />
				</form>
			</td>
			<td style='text-align: center;'>
				<form action="<?=$self_link?>" method="post">
					Close SC for emergency maintenance/rollbacks.<br />
					This is a HARD closure and must be ended through admin panel.<br />
					<input type="submit" name="close_server" value="Close" />
				</form>
			</td>
		</tr>
	</table>
<?php endif ?>
