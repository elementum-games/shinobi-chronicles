<?php
/**
 * @var string $self_link
 */

?>
<style>
    #items p.item {
        display: inline-block;
        padding: 8px 10px;
        margin-right: 15px;
        vertical-align: top;
        /* Style */
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid #C0C0C0;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0);
    }

    #items p.item:last-child {
        margin-right: 1px;
    }

    #items p.item:hover {
        background: rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }

    .submitButtonContainer {
        display:block;
        text-align:center;
        margin:auto;
    }
</style>

<?php
$heal_items = [];
if(!empty($player->items)) {
    foreach($player->items as $item) {
        if ($item->effect === 'heal') {
            $heal_items[] = $item;
        }
    }
}
?>

<tr><td>
    <p style='text-align:center;font-style:italic;'>
        (You can use healing items during prep phase, but cannot heal past
        <?= Battle::MAX_PRE_FIGHT_HEAL_PERCENT ?>% of your max health)
    </p>
    <?php if(count($heal_items) > 0): ?>
        <div id='items'>
            <?php foreach($heal_items as $item): ?>
            <p class='item' data-id='<?= $item->id ?>'>
                <b><?= $item->name ?></b> (<?= $item->effect ?> <?= $item->effect_amount ?>)<br />
                (Owned <?= $item->quantity ?>)
                <?php endforeach; ?>
        </div>
        <form action='<?= $self_link ?>' method='post'>
            <input type='hidden' id='itemID' name='item_id' />
            <p class='submitButtonContainer'>
                <input id='submit' type='submit' name='submit_prep_action' value='Submit' />
            </p>
        </form>
    <?php else: ?>
        <p style='text-align:center;'>You do not have any healing items.</p>
    <?php endif; ?>
</td></tr>

<script type='text/javascript'>
    let currentlySelectedItem = false;
    $('.item').click(function(){
        if(currentlySelectedItem !== false) {
            $(currentlySelectedItem).css('box-shadow', '0px');
        }
        currentlySelectedItem = this;
        $(currentlySelectedItem).css('box-shadow', '0px 0px 4px 0px #000000');
        $('#itemID').val( $(this).attr('data-id') );
    });
</script>