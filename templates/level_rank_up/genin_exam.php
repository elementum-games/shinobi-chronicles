<?php
/**
 * @var System $system
 * @var User $player
 * @var string $exam_name
 * @var string $prompt
 * @var array $jutsu_data
 */

$number_name = [
    1 => 'first',
    2 => 'second',
    3 => 'third'
];
?>

<table class="table">
    <tr><th><?=$exam_name?></th></tr>
    <tr>
        <td style="text-align: center;">
            Welcome to the <?=$exam_name?>.<br />
            <br />
            <?php if(isset($_POST['begin_exam'])): ?>
                As you enter the exam area, above the exam grounds sits a group of five village ninja, their faces concealed
            behind anbu masks. Though you cannot see their eyes, you feel their gaze upon you. Once settled, you break the
            wax seal on your exam packet and begin reading the instructions.<br />
            <br />
            <?php endif ?>
            Your <?=$number_name[$player->exam_stage]?> assigned task is to perform the <?=$jutsu_data[$player->exam_stage]['name']?> jutsu.<br />
        </td>
    </tr>
    <tr>
        <td style="text-align: center;">
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
                        background-color: #FDD017;
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
                                $(this).css('border-color', '#FDD017');
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
                <div id='handSeals'>
                    <?php for($i = 1; $i <= 12; $i++): ?>
                        <p id='handseal_<?=$i?>' data-selected='no' data-handseal='<?=$i?>'>
                            <img src='./images/v2/handseals/Seal<?=$i?>.png' draggable='false' />
                            <span class='handsealNumber'>1</span>
                            <span class='handsealTooltip'><?=$i?></span>
                        </p>
                        <?php if($i == 6): ?>
                            <br />
                        <?php endif ?>
                    <?php endfor ?>
                </div>
                <form action='<?=$self_link?>' method='post'>
                    <input type='hidden' id='hand_seal_input' name='hand_seals' value='<?=$submitted_hand_seals?>' />
                    <p style='text-align:center;'>
                        <input type='submit' name='attack' value='Submit' />
                    </p>
                </form>
            </div>
        </td>
    </tr>
</table>