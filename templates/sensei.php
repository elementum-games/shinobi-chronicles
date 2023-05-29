<?php
/**
 * @var User $player
 * @var System $system
 * @var array $sensei_list
 * @var array $applications
 */
?>

<style>
    .table {
        text-align: center;
    }
    .sensei_container {
        display:inline-block;
        height:120px;
        width:140px;
        margin: 5px 15px 20px 15px;
        font-weight: bold;
    }
    .student_container {
        display:inline-block;
        height:120px;
        width:120px;
        margin: 10px 15px 20px 15px;
        font-weight: bold;
    }
    .sensei_avatar {
        max-width:120px;max-height:120px;
    }
    .student_avatar {
        max-width:80px;max-height:80px;
    }
    .take_exam_container {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .resign_container {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .graduated_container {
        margin-top: 10px;
    }
    .label_bold {
        font-weight: bold;
        margin: 0px;
    }
    .label_italics {
        font-style: italic;
        margin: 0px;
    }
    .application_table {
        width: 75% !important;
    }
    .small_image {
        max-width:20px;
        max-height:20px;
    }
</style>

<?php if ($player->staff_manager->isModerator()): ?>
    <table class="table">
        <tr>
            <th colspan="5">Mod View</th>
        </tr>
        <tr>
            <td>
                <a href="<?= $system->router->links['villageHQ'] ?>&view=sensei&village=Stone">Stone</a>
            </td>
            <td>
                <a href="<?= $system->router->links['villageHQ'] ?>&view=sensei&village=Cloud">Cloud</a>
            </td>
            <td>
                <a href="<?= $system->router->links['villageHQ'] ?>&view=sensei&village=Leaf">Leaf</a>
            </td>
            <td>
                <a href="<?= $system->router->links['villageHQ'] ?>&view=sensei&village=Sand">Sand</a>
            </td>
            <td>
                <a href="<?= $system->router->links['villageHQ'] ?>&view=sensei&village=Mist">Mist</a>
            </td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($resign): ?>
    <table class="table">
        <tr>
            <th>Confirm Resignation</th>
        </tr>
        <tr>
            <td>
                <div>
                    <p>Resigning as sensei will remove all of your students and record of your accomplishments as Sensei.</p>
                    <form action="<?= $system->router->links['villageHQ']?>&view=sensei" method="post">
                        <span>
                            <input type="submit" name="confirm_resignation" value="Confirm Resignation"/>
                        </span>
                    </form>
                </div>
            </td>
        </tr>
    </table>
<?php endif; ?>

<?php if (!SenseiManager::isSensei($player->user_id, $system) && $player->rank_num > 3): ?>
    <table class="table">
        <tr>
            <th>
                Apply to become a Sensei?
            </th>
        </tr>
        <tr>
            <td>
                <div>
                    <div>
                        <p class="label_bold">Requirements:</p>
                        <div><span>Jonin Rank - Level 75</span></div>
                        <div><span>5 Jutsu Mastered</span></div>
                        <div><span>Pass Aptitude Exam</span></div>
                    </div>
                    <p>As a Sensei you can have up to three students at a time.</p>
                    <p>Your students receive training bonuses based on your specialization and experience as a Sensei.</p>
                    <table class="table">
                        <tr>
                            <th>Tier</th>
                            <th>Unlock</th>
                            <th colspan="2">Bonuses</th>
                        </tr>
                        <tr>
                            <td>Tier 1</td>
                            <td>Default</td>
                            <td colspan="2">
                                <div>3% faster training in specialization</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tier 2</td>
                            <td>Gradute 3 Students</td>
                            <td colspan="2">
                                <div>6% faster training in specialization</div>
                                <div>3% faster in all other types</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tier 3</td>
                            <td>Gradute 8 Students</td>
                            <td colspan="2">
                                <div>9% faster training in specialization</div>
                                <div>4.5% faster in all other types</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tier 4</td>
                            <td>Gradute 15 Students</td>
                            <td colspan="2">
                                <div>12% faster training in specialization</div>
                                <div>6% faster in all other types</div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="take_exam_container">
                    <a href="<?= $system->router->links['villageHQ']?>&view=sensei&sensei_exam=true">Take Exam</a>
                </div>
            </td>
        </tr>
    </table>
<?php endif; ?>

<?php if (SenseiManager::isSensei($player->user_id, $system)): ?>
    <table class="table">
        <tr>
            <th>
                Sensei Details
            </th>
        </tr>
        <tr>
            <td>
                <div>
                    <p>As a Sensei you can have up to three students at a time.</p>
                    <p>Your students receive training bonuses based on your specialization and experience as a Sensei.</p>
                    <table class="table">
                        <tr>
                            <th>Tier</th>
                            <th>Unlock</th>
                            <th colspan="2">Bonuses</th>
                        </tr>
                        <tr>
                            <td>Tier 1</td>
                            <td>Default</td>
                            <td colspan="2">
                                <div>3% faster training in specialization</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tier 2</td>
                            <td>Gradute 3 Students</td>
                            <td colspan="2">
                                <div>6% faster training in specialization</div>
                                <div>3% faster in all other types</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tier 3</td>
                            <td>Gradute 8 Students</td>
                            <td colspan="2">
                                <div>9% faster training in specialization</div>
                                <div>4.5% faster in all other types</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tier 4</td>
                            <td>Gradute 15 Students</td>
                            <td colspan="2">
                                <div>12% faster training in specialization</div>
                                <div>6% faster in all other types</div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div>
                    <?php if (count($applications) > 0): ?>
                    <table class="table application_table">
                        <tr>
                            <th colspan=3>Pending Applications</th>
                        </tr>
                        <?php foreach ($applications as $application): ?>
                        <tr>
                            <td>
                                <a href="<?= $system->router->links['members'] ?>&user=<?= $application->student_name ?>">
                                    <?= $application->student_name ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= $system->router->links['villageHQ'] ?>&view=sensei&accept=<?= $application->student_id ?>">Accept</a>
                            </td>
                            <td>
                                <a href="<?= $system->router->links['villageHQ'] ?>&view=sensei&deny=<?= $application->student_id ?>">Deny</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
                <div class="resign_container">
                    <a href="<?= $system->router->links['villageHQ']?>&view=sensei&resign=true">Resign</a>
                </div>
            </td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($player->rank_num < 3): ?>
    <table class="table">
        <tr>
            <th>
                Sensei Info
            </th>
        </tr>
        <tr>
            <td>
                <div>
                    <p>Sensei are experienced players who provide training bonuses and teach you the ways of the Shinobi.</p>
                    <p>Apply to any number of Sensei by clicking an available student slot in the listing below.</p>
                    <p>You can change Sensei at any time by visiting their profile and selecting the "Leave Sensei" option.</p>
                    <?php if (count($applications) > 0): ?>
                        <table class="table application_table">
                            <tr>
                                <th colspan=2>Pending Applications</th>
                            </tr>
                            <?php foreach ($applications as $application): ?>
                            <tr>
                                <td><a href="<?= $system->router->links['members'] ?>&user=<?= $application->sensei_name ?>"><?= $application->sensei_name ?></a></td>
                                <td><a href="<?= $system->router->links['villageHQ'] ?>&view=sensei&close=<?= $application->sensei_id ?>">Close Application</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>
<?php endif; ?>

<table class="table">
    <tr>
        <th>Sensei</th>
        <th colspan="3">Students</th>
    </tr>
    <?php foreach ($sensei_list as $sensei): ?>
    <tr>
        <td>
            <div>
                <p class="label_italics">
                    <?= $sensei['bloodline_name'] ?>
                </p>
            </div>
            <div>
                <p class="label_bold">
                    Specialization: <?= ucwords($sensei['specialization']) ?>
                </p>
            </div>
            <div class="sensei_container">
                <img src='<?= $sensei['avatar_link'] ?>' class="sensei_avatar" />
                <span>
                    <a href='<?= $system->router->links['members'] ?>&user=<?= $sensei['user_name'] ?>'>
                        <?= $sensei['user_name'] ?>
                    </a>
                </span>
            </div>
        </td>
        <td colspan="3">
            <div class="graduated_container">
                <p class="label_bold">Graduated: <?= $sensei['graduated'] ?></p>
            </div>
            <div>
                <p class="label_bold">
                    <?= ucwords($sensei['specialization'])?> (+<?= $sensei['boost_primary'] ?>%) | Other (+<?= $sensei['boost_secondary'] ?>%)
                </p>
            </div>
            <?php foreach ($sensei['students'] as $student): ?>
            <div class="student_container">
                <span>Student</span>
                <div><img src='<?= $student->avatar_link ?>' class="student_avatar" /></div>
                <span>
                    <a href='<?= $system->router->links['members'] ?>&user=<?= $student->user_name ?>'>
                        <?= $student->user_name ?>
                    </a>
                </span>
            </div>
            <?php endforeach; ?>
            <?php if (count($sensei['students']) < 3): ?>
            <?php for ($i = 0; $i < (3 - count($sensei['students'])); $i++): ?>
            <div class="student_container">
                <span>Student</span>
                <div><img src='../images/default_avatar.png' class="student_avatar" /></div>
                <span>
                    <?php if ($player->sensei_id == 0 && $player->rank_num < 3): ?>
                    <a href='<?= $system->router->links['villageHQ'] ?>&view=sensei&apply=<?= $sensei['sensei_id'] ?>'>
                        (Available)
                    </a>
                    <?php else: ?>
                    (Available)
                    <?php endif; ?>
                </span>
            </div>
            <?php endfor; ?>
            <?php endif; ?>
            <div>
                <?= $system->html_parse($sensei['recruitment_message']) ?>
            </div>
            <?php if($player->staff_manager->isModerator()): ?>
            <div>
                <a class='imageLink' href='<?= $system->router->links['villageHQ'] ?>&view=sensei&clear=<?= $sensei['sensei_id'] ?>'>
                    <img class='small_image' src='../images/delete_icon.png' />
                </a>
            </div>
            <?php endif ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>