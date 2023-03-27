<?php
$gold_color = '#FDD017';
?>
<div style='margin:0;position:relative;'>
    <style>
        #handSeals p {
            display: inline-block;
            width: 80px;
            height: 110px;

            margin: 4px;

            position:relative;
        }
        #handSeals img {
            height: 74px;
            width: 74px;

            position: relative;
            z-index: 1;

            border: 3px solid rgba(0,0,0,0);
            border-radius: 5px;
        }

        #handSeals .handsealNumber {
            display: none;
            width: 18px;

            position: absolute;
            z-index: 20;

            text-align: center;

            left: 31px;
            right: 31px;
            bottom: 35px;

            /* Style */
            font-size: 14px;
            font-weight: bold;
            background-color: <?=$gold_color?>;
            border-radius: 10px;
        }
        #handSeals .handsealTooltip {
            display: block;
            margin: 0;
            text-align: center;
            height: 16px;
        }
        #handsealOverlay{
            width:100%;
            position:absolute;
            top:0;
            height:100%;
            background-color:rgba(255,255,255,0.9);
            z-index:50;
            display:none;
        }
    </style>
    <script type='text/javascript'>
        $(document).ready(function(){
            let hand_seals = [];

            $('#handSeals p img').click(function() {
                let parent = $(this).parent();
                let seal = parent.attr('data-handseal');

                // Select hand seal
                if(parent.attr('data-selected') === 'no') {
                    parent.attr('data-selected', 'yes');
                    $(this).css('border-color', '<?=$gold_color?>');
                    parent.children('.handsealNumber').show();

                    hand_seals.splice(hand_seals.length, 0, seal);
                }
                // De-select handseal
                else if(parent.attr('data-selected') === 'yes') {
                    parent.attr('data-selected', 'no');
                    $(this).css('border-color', 'rgba(0,0,0,0)');
                    parent.children('.handsealNumber').hide();

                    for(let x in hand_seals) {
                        if(hand_seals[x] === seal) {
                            hand_seals.splice(x,1);
                            break;
                        }
                    }
                }


                // Update display
                $('#hand_seal_input').val(hand_seals.join('-'));

                let id = '';
                for(let x in hand_seals) {
                    id = 'handseal_' + hand_seals[x];
                    $('#' + id).children('.handsealNumber').text((parseInt(x) + 1));
                }
            });
        });
    </script>

    <!--DIV START-->
    <div id='handSeals'>
    <?php for($i = 1; $i <= 12; $i++): ?>
        <p id='handseal_<?=$i?>' data-selected='no' data-handseal='<?=$i?>'>
            <img src='./images/handseal_<?=$i?>.png' draggable='false' />
            <span class='handsealNumber'>1</span>
            <span class='handsealTooltip'>&nbsp;</span>
        </p>
        <?php if($i == 6): ?>
            <br />
        <?php endif ?>
    <?php endfor ?>
</div>
