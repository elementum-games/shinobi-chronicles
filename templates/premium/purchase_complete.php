<?php

function renderPurchaseComplete(string $title, string $message): void {
?>

    <table class="table">
        <tr><th><?= $title ?>></th></tr>
        <tr>
            <td style="text-align: center;">
                <?=$message?>
            </td>
        </tr>
    </table>
    <?php
}