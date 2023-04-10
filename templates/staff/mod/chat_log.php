<style>
    .post {
        background-color: yellow;
    }
</style>

<?php if($player->staff_manager->isUserAdmin()): ?>
    <table class="table">
        <tr><th>Search Chat Log</th></tr>
        <tr>
            <td style="text-align: center;">
                <form action="<?=$self_link?>" method="get">
                    <input type="hidden" name="id" value="<?=Router::PAGE_IDS['chat_log']?>" />
                    Post ID: <input type="text" name="post_id" />
                    <input type="submit" value="Search" />
                </form>
            </td>
        </tr>
    </table>
<?php endif ?>

<table class="table" style="table-layout: auto;">
    <tr><th colspan="3">Chat Log <?=($report ? " - <a href='{$system->router->links['report']}&page=view_report&report_id={$report['report_id']}'>(Report)</a>" : "") ?></th></tr>
    <?php if(!empty($posts)): ?>
        <?php foreach($posts as $post): ?>
            <tr style="text-align: center;" <?=($post['post_id'] == $post_id ? "class='post'" : "")?>>
                <td style="width:15%;"><?=Date(StaffManager::DATE_FORMAT, $post['time'])?></td>
                <td><?=$post['user_name']?></td>
                <td><?=nl2br($post['message'])?></td>
            </tr>
        <?php endforeach ?>
    <?php else: ?>
        <tr><td colspan="3" style="text-align: center;">No posts!</td></tr>
    <?php endif ?>
</table>
