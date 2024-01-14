<?php
/** @var string $self_link */
/** @var string $view */

?>

<!-- Sub-menu-->
<div class='submenu'>
    <ul class='submenu'>
        <li style='width:49%;'><a href='<?= $self_link ?>&view=view_battles'>View Battles</a></li>
        <li style='width:49%;'><a href='<?= $self_link ?>&view=battle_history'>Battle History</a></li>
    </ul>
</div>
<div class='submenuMargin'></div>

<?php if ($view == 'view_battles'): ?>
    <?php require 'templates/view_battles/viewBattles.php'; ?>
<?php elseif ($view == 'battle_history'): ?>
    <?php require 'templates/view_battles/battleHistory.php'; ?>
<?php endif; ?>