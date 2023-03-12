<?php
/**
 * @var System $system
 * @var User $player
 * @var int $time_remaining
 * @var string $logout_display
 * @var string $side_menu_start
 * @var string $side_menu_end
 * @var array $menu_items
 */
?>


<?=$side_menu_start?>
<?php foreach($menu_items as $menu_name => $items): ?>
    <h2><p><?=ucwords($menu_name)?> Menu</p></h2>
    <?php foreach($items as $item_data): ?>
        <li><a id="<?=$item_data['menu_id']?>" href="<?=$item_data['link']?>"><?=$item_data['title']?></a></li>
    <?php endforeach ?>
<?php endforeach ?>
<?=str_replace("<!--LOGOUT_TIMER-->", $logout_display, $side_menu_end)?>
<script type="text/javascript">countdownTimer(<?=$time_remaining?>, 'logoutTimer');</script>
