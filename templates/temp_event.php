<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 */
?>

<table class="table">
    <tr><th><?=System::SC_EVENT_NAME?> Details</th></tr>
    <tr>
        <td>
            Stuff
        </td>
    </tr>
    <tr>
        <td>
            <label>Exchange</label><br />
            <label>Red for Yen</label>
                <a href="<?=$system->router->getUrl("event", ["exchange" => "red_yen"])?>">Exchange!</a><br />
            <label>Red for Rep</label>
                <a href="<?=$system->router->getUrl("event", ["exchange" => "red_rep"])?>">Exchange!</a><br />
        </td>
    </tr>
</table>
