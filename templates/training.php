<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var array $valid_skills
 * @var array $valid_attributes
 * @var bool $trainable_bl_jutsu
 */
?>

<style>
    span.header {
        font-weight: bold;
        margin-left: 10px;
    }
    p.indent {
        margin-left: 25px;
        margin-top: 5px;
        margin-bottom: 8px;
    }
    p.indent label {
        font-weight: bold;
        width: 70px;
    }
</style>

<?php if($player->trainingManager->hasActiveTraining() && isset($_GET['cancel_training']) && !isset($_GET['cancel_confirm'])): ?>
    <table class="table">
        <tr><th>Cancel Training</th></tr>
        <tr>
            <td style="text-align: center;">
                Are you certain you wish to cancel your training?<br />
                <?php if(!$player->reputation->benefits[UserReputation::BENEFIT_PARTIAL_TRAINING_GAINS]): ?>
                    You will not gain any of your potential <?= (!empty($system->event) && $system->event instanceof DoubleExpEvent) ? $player->trainingManager->train_gain * DoubleExpEvent::exp_modifier : $player->trainingManager->train_gain ?> gains.
                <?php else: ?>
                    <?= $partial_gain ?>
                <?php endif ?>
                <br />
                <a href="<?=$self_link?>&cancel_training=1&cancel_confirm=1">Confirm</a>
            </td>
        </tr>
    </table>
<?php endif ?>

<table class="table">
    <tr><th colspan="<?=($player->bloodline_id != 0 && $trainable_bl_jutsu) ? 4 : 3?>">Training</th></tr>
    <tr>
        <td style='text-align: center' colspan="<?=($player->bloodline_id != 0 && $trainable_bl_jutsu) ? 4 : 3?>">
            <p style="text-align:center;">You can train to improve your skills, attributes, or proficiency with jutsu.</p>
            <span class="header">Skill/Attribute training:</span><br />
            <p class="indent">
                <label>Short:</label>
                <?= $player->trainingManager->getTrainingInfo(TrainingManager::TRAIN_LEN_SHORT, TrainingManager::$skill_types[0]); ?>
                <br />
                <label>Long:</label>
                <?= $player->trainingManager->getTrainingInfo(TrainingManager::TRAIN_LEN_LONG, TrainingManager::$skill_types[0]); ?>
                <br />
                <label>Extended:</label>
                <?= $player->trainingManager->getTrainingInfo(TrainingManager::TRAIN_LEN_EXTENDED, TrainingManager::$skill_types[0]); ?>
                <?php if($player->team != null && $player->team->boost == Team::BOOST_TRAINING): ?>
                    <br /><em>*Note: Your team has a chance at additional stat gains, these are not reflected above.</em>
                <?php endif ?>
            </p>
            <span class="header">Jutsu training:</span><br />
            <p class="indent">
                <?= $player->trainingManager->getTrainingInfo(TrainingManager::TRAIN_LEN_SHORT, 'jutsu:clone_combo'); ?>
            </p>
        </td>
    </tr>
    <?php if($player->trainingManager->hasActiveTraining()): ?>
        <tr>
            <th colspan="<?=($player->bloodline_id != 0 && $trainable_bl_jutsu) ? 4 : 3?>">
                <?= $player->trainingManager->trainType(); ?> Training
            </th>
        </tr>
        <tr>
            <td style="text-align: center;" colspan="<?=($player->bloodline_id != 0 && $trainable_bl_jutsu) ? 4 : 3?>">
                <?= $player->trainingManager->trainingDisplay(); ?>
                <p id="train_time_remaining"><?= System::timeRemaining($player->train_time - time(), 'short', false) ?> Remaining</p>
                <script type="text/javascript">
                    countdownTimer(<?=$player->trainingManager->train_time_remaining?>, 'train_time_remaining');
                </script>
                <a href="<?=$self_link?>&cancel_training=1">Cancel Training</a>
            </td>
        </tr>
    <?php else: ?>
        <tr>
            <th style="width:<?=($player->bloodline_id != 0 && $trainable_bl_jutsu) ? 25 : 33?>%;">Skills</th>
            <th style="width:<?=($player->bloodline_id != 0 && $trainable_bl_jutsu) ? 25 : 33?>%;">Attributes</th>
            <th style="width:<?=($player->bloodline_id != 0 && $trainable_bl_jutsu) ? 25 : 33?>%;">Jutsu</th>
            <?php if($player->bloodline_id != 0 && $trainable_bl_jutsu): ?>
                <th style="width:25%;">Bloodline Jutsu</th>
            <?php endif ?>
        </tr>
        <tr style="text-align: center;">
            <td>
                <form action="<?=$self_link?>" method="post">
                    <select style="margin-bottom: 5px" name="skill">
                        <?php foreach($valid_skills as $skill): ?>
                            <option value="<?=$skill?>" <?=($player->trainingManager->train_type == $skill ? "selected='selected'" : "")?>
                            ><?=ucwords(str_replace('_', ' ', $skill))?></option>
                        <?php endforeach ?>
                    </select><br />
                    <input type="submit" name="train_type" value="Short" />
                    <input type="submit" name="train_type" value="Long" />
                    <input type="submit" name="train_type" value="Extended" />
                </form>
            </td>
            <td>
                <form action="<?=$self_link?>" method="post">
                    <select style="margin-bottom: 5px" name="attributes">
                        <?php foreach($valid_attributes as $attribute): ?>
                            <option value="<?=$attribute?>" <?=($player->trainingManager->train_type == $attribute ? "selected='selected'" : "")?>
                            ><?=ucwords(str_replace('_', ' ', $attribute))?></option>
                        <?php endforeach ?>
                    </select><br />
                    <input type="submit" name="train_type" value="Short" />
                    <input type="submit" name="train_type" value="Long" />
                    <input type="submit" name="train_type" value="Extended" />
                </form>
            </td>
            <td>
                <form action="<?=$self_link?>" method="post">
                    <select style="margin-bottom: 5px" name="jutsu">
                        <?php foreach($player->jutsu as $id => $jutsu): ?>
                            <?php if($jutsu->level >= Jutsu::MAX_LEVEL): ?>
                                <?php continue; ?>
                            <?php endif ?>
                            <option value="<?=$id?>" title="<?=$jutsu->jutsu_type?>" <?=($player->trainingManager->train_type == 'jutsu:'.System::slug($jutsu->name) ? "selected='selected'" : "")?>
                            ><?=$jutsu->name?></option>
                        <?php endforeach ?>
                    </select><br />
                    <input type="submit" name="train_type" value="Train" />
                </form>
            </td>
            <?php if($player->bloodline_id != 0 && $trainable_bl_jutsu): ?>
                <td>
                    <form action="<?=$self_link?>" method="post">
                        <select style="margin-bottom: 5px" name="bloodline_jutsu">
                            <?php foreach($player->bloodline->jutsu as $id => $jutsu): ?>
                                <?php if($jutsu->level >= Jutsu::MAX_LEVEL): ?>
                                    <?php continue; ?>
                                <?php endif ?>
                                <option value="<?=$id?>" title="<?=$jutsu->jutsu_type?>" <?=($player->trainingManager->train_type == 'jutsu:'.System::slug($jutsu->name) ? "selected='selected'" : "")?>
                                ><?=$jutsu->name?></option>
                            <?php endforeach ?>
                        </select><br />
                        <input type="submit" name="train_type" value="Train" />
                    </form>
                </td>
            <?php endif ?>
        </tr>
    <?php endif ?>
</table>