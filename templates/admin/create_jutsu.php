<?php
/**
 * @var System $system
 * @var array $jutsu_constraints
 * @var Jutsu[] $ALL_JUTSU
 */

 require_once __DIR__ . '/../../classes/RankManager.php';
 $RANK_NAMES = RankManager::fetchNames($system);
?>

<table class='table'>
    <tr><th>Create Jutsu</th></tr>
    <tr><td>
        <form action="<?= $system->routerV2->current_route ?>" method="post">
            <?php
                $existing_jutsu = null;
                require 'templates/admin/jutsu_form.php';
            ?>
            <p style='text-align:center;'>
                <input type="submit" name="jutsu_data" value="Create">
            </p>
        </form>
    </td></tr>
</table>
