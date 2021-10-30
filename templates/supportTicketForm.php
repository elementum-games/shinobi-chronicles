<?php
/**
 * @var System $system
 * @var SupportManager $supportSystem
 * @var User $player
 * @var string $self_link
 * @var array $request_types
 */
?>

<script type="text/javascript">
    function calculateCost() {
        let supportType = $('#support_type').val();
        let costArray = <?= json_encode($supportSystem->requestPremiumCosts) ?>;

        $('#premiumCost').text(costArray[supportType] + ' AK');
    }
</script>

<table class='table'>
    <tr><th>New Support Request</th></tr>
    <tr><td>
        <form action="<?=$self_link?>" method="post">
            <label style="width:8em; font-weight:bold;">Subject:</label><input type="text" name="subject" /><br />
            <label style="width:8em; font-weight:bold;">Request Type:</label>
            <select id='support_type' name="support_type" onchange="calculateCost()">
                <?php foreach($request_types as $type): ?>
                    <option value="<?=$type?>"><?=$type?></option>
                <?php endforeach ?>
            </select><br />
            <label style="width:8em; font-weight:bold;">Content:</label><br />
            <textarea name="message" style="display:inline-block; width:500px;height:200px;margin-left:8em;"></textarea><br />
            <br />
            <input type="submit" name="add_support" value="Submit Ticket" />
            <input type="submit" name="add_support_prem" value="Submit Premium Ticket*" /><br />
            <br />
            *You may submit your support as premium status, giving it higher priority for processing. Note that some support
            types offer free premium service.
            <b>Current selection: <div id="premiumCost" style="display:inline;"><?=$supportSystem->requestPremiumCosts[$request_types[0]]?> AK</div></b>
        </form>
    </td></tr>
</table>
