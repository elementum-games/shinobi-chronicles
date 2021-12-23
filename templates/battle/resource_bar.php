<style type='text/css'>
    .resourceBarOuter {
        height: 16px;
        width: 225px;
        margin: 2px 0;
        border-style:solid;
        border-width:1px;

        position: relative;
        background: rgba(0, 0, 0, 0.7);
    }
    .resourceBarOuter .text {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        text-align: center;

        color: #f0f0f0;
        line-height: 16px;
        font-size: 12px;
        text-shadow: 0 0 2px black;
    }

    .resourceFill {
        height: 100%;
    }
    .healthFill {
        background: linear-gradient(to bottom, #A00000, #D00000, #A00000);
    }
    .chakraFill {
        background: linear-gradient(to bottom, #001aA0, #1030e0, #001aA0);
    }
    .staminaFill {
        background: linear-gradient(to bottom, #00A000, #00D000, #00A000);
    }
</style>
<?php

/**
 * @param        $current_amount
 * @param        $max_amount
 * @param string $resource_type health, chakra, or stamina
 */
function resourceBar(float $current_amount, float $max_amount, string $resource_type) {
    $resource_percent =  round(($current_amount / $max_amount) * 100);
    ?>
    <div class='resourceBarOuter'>
        <div class='resourceFill <?= $resource_type ?>Fill' style='width:<?= $resource_percent ?>%;'></div>
        <div class='text'><?= sprintf("%.2f", $current_amount) ?> / <?= sprintf("%.2f", $max_amount) ?></div>
    </div>
    <?php
}



