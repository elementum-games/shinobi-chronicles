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
        <td style="text-align: center">
            <p><b><?= System::SC_EVENT_NAME ?> is now active!</b></p>
            <div>
                <p>Welcome to the annual Festival of Shadows<br />
                A solemn tradition where lanterns are set adrift to guide spirits to the afterlife.</p>
                <p>As realms of the living and supernatural converge, mischievous yokai grow active to feed on souls of the dead and living alike.<br /> Chakra-infused lanterns are used to ward off the shadows, but an unprecedented surge in yokai attacks endangers the villages.</p>
                <p>As shinobi it falls on you to recover lanterns, defend your fellow villagers, and uncover the source of the encroaching darkness.</p>
            </div>
            <table class="table" style="width: 85%">
                <tr>
                    <th>Red Lantern</th>
                    <th>Blue Lantern</th>
                    <th>Violet Lantern</th>
                    <th>Gold Lantern</th>
                    <th><?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "Shadow Essence" : "???" ?></th>
                </tr>
                <tr>
                    <td><?php echo $player->hasItem($system->event_data['red_lantern_id']) ? $player->items[$system->event_data['red_lantern_id']]->quantity : "0" ?>x</td>
                    <td><?php echo $player->hasItem($system->event_data['blue_lantern_id']) ? $player->items[$system->event_data['blue_lantern_id']]->quantity : "0" ?>x</td>
                    <td><?php echo $player->hasItem($system->event_data['violet_lantern_id']) ? $player->items[$system->event_data['violet_lantern_id']]->quantity : "0" ?>x</td>
                    <td><?php echo $player->hasItem($system->event_data['gold_lantern_id']) ? $player->items[$system->event_data['gold_lantern_id']]->quantity : "0" ?>x</td>
                    <td><?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? $player->items[$system->event_data['shadow_essence_id']]->quantity : "0" ?>x</td>
                </tr>
            </table>
            <table class="table" style="margin-bottom: 25px">
                <tr>
                    <th>Cost</th>
                    <th>Reward</th>
                    <th>Exchange</th>
                </tr>
                <tr>
                    <td>1x Red Lantern</td>
                    <td>25&#165;</td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "red_yen_small"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>10x Red Lantern</td>
                    <td>250&#165;</td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "red_yen_medium"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>100x Red Lantern</td>
                    <td>2500&#165;</td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "red_yen_large"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>50x Red Lantern</td>
                    <td>1 Reputation</td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "red_rep"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>1x Blue Lantern</td>
                    <td>5x Red Lantern</td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "blue_red"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>1x Violet Lantern</td>
                    <td>20x Red Lantern</td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "violet_red"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>1x Gold Lantern</td>
                    <td>50x Red Lantern</td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "gold_red"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>100x Red Lantern</td>
                    <td><?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "1x Shadow Essence" : "???" ?></td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "red_shadow"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "1x Shadow Essence" : "???" ?></td>
                    <td><?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "100x Red Lantern" : "???" ?></td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "shadow_red"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "5x Shadow Essence" : "???" ?>
                    </td>
                    <td>
                        <?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "Sacred Red Lantern" : "???" ?>
                    </td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "shadow_sacred_red"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "5x Shadow Essence" : "???" ?>
                    </td>
                    <td>
                        <?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "Sacred Blue Lantern" : "???" ?>
                    </td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "shadow_sacred_blue"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "5x Shadow Essence" : "???" ?>
                    </td>
                    <td>
                        <?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "Sacred Violet Lantern" : "???" ?>
                    </td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "shadow_sacred_violet"])?>">Claim</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "25x Shadow Essence" : "???" ?>
                    </td>
                    <td>
                        <?php echo $player->hasItem($system->event_data['shadow_essence_id']) ? "Forbidden Jutsu Scroll" : "???" ?>
                    </td>
                    <td>
                        <a href="<?=$system->router->getUrl("event", ["exchange" => "shadow_jutsu"])?>">Claim</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
