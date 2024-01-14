<?php if(!$system->SC_OPEN): ?>
    <table class="table">
        <tr><th>Open SC</th></tr>
        <tr>
            <td>
                <form action="<?=$self_link?>" method='post'>
                    Are you sure you would like to reopen SC?<br />
                    <input type='submit' name='open_sc' value='open' />
                </form>
            </td>
        </tr>
    </table>
<?php endif ?>