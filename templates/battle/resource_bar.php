<style>
    .resourceBarOuter {
        display: flex;
        position: relative;
        height: 17px;

        width: 260px;
        /* Allows overriding this from outside */
        max-width: 100%;

        border: 1px solid black;
        border-radius: 17px;

        background: rgba(0,0,0,0.7);
        overflow: hidden;
    }

    .resourceBarOuter .text {
        display: block;
        position: absolute;
        left: 0;
        right: 0;
        align-self: center;

        font-size: 12px;
        font-weight: bold;
        letter-spacing: 0.2px;
        line-height:15px;
        text-align: center;

        color: #ffffff;
        text-shadow:
                -1px 0 0 rgba(0,0,0,0.7),
                -1px -1px 0 rgba(0,0,0,0.7),
                0 -1px 0 rgba(0,0,0,0.7),
                1px -1px 0 rgba(0,0,0,0.7),
                1px 0 0 rgba(0,0,0,0.7),
                1px 1px 0 rgba(0,0,0,0.7),
                0 1px 0 rgba(0,0,0,0.7),
                -1px 1px 0 rgba(0,0,0,0.7);

        z-index: 100;
    }

    .resourceFill {
        position: absolute;
        top: 0;
        z-index: 2;
        height: 100%;
    }

    .health {
        background: linear-gradient(to right, rgb(200, 30, 20), rgb(240, 50, 50));
    }
    .health.preview {
        background: rgb(240, 50, 50);
    }

    .chakra {
        background: #1060ff linear-gradient(to right, #1060ff, #2080ff);
    }
    .chakra.preview {
        background: #2080ff;
    }

    .stamina {
        background: linear-gradient(to right, rgb(10, 180, 10), rgb(40, 220, 40));
    }
    .stamina.preview {
        background:  rgb(40, 220, 40);
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
        <div class='resourceFill <?= $resource_type ?>' style='width:<?= $resource_percent ?>%;'></div>
        <div class='text'><?= sprintf("%.2f", $current_amount) ?> / <?= sprintf("%.2f", $max_amount) ?></div>
    </div>
    <?php
}



