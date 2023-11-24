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
    table.aMenu a {
        width: 125px;
        display: inline-block;
        text-align: center;
    }
    table.aMenu td {
        text-align: center;
    }
</style>

<table class="table aMenu">
    <tr><th>Admin Panel Menu</th></tr>
    <?php if($player->staff_manager->isContentAdmin()): ?>
        <tr>
            <td>
                <?php
                    array_map(function($menu_item_slug) use ($system){
                        create_link($system, $menu_item_slug);
                    }, $player->staff_manager->getAdminPanelPerms('create_content'));
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php
                array_map(function($menu_item_slug) use ($system){
                    create_link($system, $menu_item_slug);
                }, $player->staff_manager->getAdminPanelPerms('edit_content'));
                ?>
            </td>
        </tr>
    <?php endif ?>
    <?php if($player->staff_manager->isUserAdmin()): ?>
        <tr>
            <td>
                <?php
                array_map(function($menu_item_slug) use ($system){
                    create_link($system, $menu_item_slug);
                }, $player->staff_manager->getAdminPanelPerms('misc_tools'));
                ?>
            </td>
        </tr>
    <?php endif ?>
</table>