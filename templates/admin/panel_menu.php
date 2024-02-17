<?php
/**
 * @var System $system
 * @var User $player
 */

function create_link($system, string $menu_item_slug): void {
    echo "<a href='{$system->router->getUrl('admin', ['page'=>$menu_item_slug])}'>" . System::unSlug($menu_item_slug) . "</a>";
}
?>

<style>
    .menuRow a {
        min-width: 80px;
        display: inline-block;
        text-align: center;
        margin: auto 10px;
    }
    .menuRow.content a {
        min-width: 100px;
    }

    .menuRow {
        display: flex;
        flex-direction: row;
        justify-content: space-evenly;
        align-items: center;

        padding: 6px 5px;

        border-bottom: 1px solid var(--sidebar-button-border-color);
    }
    .menuRow:last-child {
        border-bottom: none;
    }
</style>

<table class="table aMenu">
    <tr><th>Admin Panel Menu</th></tr>
    <tr><td>
        <?php if($player->staff_manager->isContentAdmin()): ?>
            <div class='menuRow content'>
                <?php
                    array_map(function($menu_item_slug) use ($system){
                        create_link($system, $menu_item_slug);
                    }, $player->staff_manager->getAdminPanelPerms('create_content'));
                ?>
            </div>
            <div class='menuRow content'>
                <?php
                    array_map(function($menu_item_slug) use ($system){
                        create_link($system, $menu_item_slug);
                    }, $player->staff_manager->getAdminPanelPerms('edit_content'));
                    ?>
            </div>
        <?php endif ?>
        <?php if($player->staff_manager->isUserAdmin()): ?>
            <div class='menuRow'>
                    <?php
                    array_map(function($menu_item_slug) use ($system){
                        create_link($system, $menu_item_slug);
                    }, $player->staff_manager->getAdminPanelPerms('user_tools'));
                    ?>
            </div>
        <?php endif ?>
        <?php if($player->staff_manager->isUserAdmin()): ?>
            <div class='menuRow'>
                    <?php
                    array_map(function($menu_item_slug) use ($system){
                        create_link($system, $menu_item_slug);
                    }, $player->staff_manager->getAdminPanelPerms('misc_tools'));
                    ?>
            </div>
        <?php endif ?>
        <?php if($player->staff_manager->isHeadAdmin()): ?>
            <div class='menuRow'>
                <a
                    style='width:auto;'
                    href='<?= $system->router->base_url ?>admin/combat_simulator/vs.php'
                    target='_blank'
                >Combat Simulator - Vs Mode</a>
            </div>
        <?php endif; ?>

    </td></tr>
</table>