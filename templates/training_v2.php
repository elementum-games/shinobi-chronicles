<!-- Copied from templates/training.php -->
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

<link rel="stylesheet" type="text/css" href="<?= $system->getCssFileLink("ui_components/src/training/Training.css") ?>" />
<div id="trainingReactContainer"></div>
<script type="module" src="<?= $system->getReactFile("training/Training") ?>"></script>
<script>
    
    const trainingContainer = document.querySelector("#trainingReactContainer");


    window.addEventListener('load', () => {
        ReactDOM.render(
            React.createElement(Training, {
                playerData: {
                    hasPartialGainsBenefits: <?= $player->reputation->benefits[UserReputation::BENEFIT_PARTIAL_TRAINING_GAINS] ?: "false" ?>, /*bool*/
                    trainGains: <?= $player->trainingManager->train_gain ?>,
                    partialGain: <?= (!isset($partial_gain)) ? 0 : $partial_gain ?>,
                    hasActiveTraining: <?= $player->trainingManager->hasActiveTraining() ?: "false" ?>,
                    bloodlineID: <?= $player->bloodline_id ?: "0" ?> ,
                    hasTrainableBLJutsu: <?= $trainable_bl_jutsu ?: "false" ?>,
                    hasTeam: <?= isset($player->team) ?: "false" ?>,
                    hasTeamBoostTraining: <?= (isset($player->team) && $player->team->boost  == Team::BOOST_TRAINING) ? "true" : "false" ?>,
                    validSkillsArray: <?= json_encode($valid_skills) ?>,
                    validAttributesArray: <?= json_encode($valid_attributes) ?>,
                    allPlayerBloodlineJutsu: <?= (is_null($player->bloodline)) ? '[]' : json_encode($player->bloodline->jutsu) ?>,
                    allPlayerJutsu: <?= json_encode($player->jutsu) ?>,
                    jutsuMaxLevel: "<?= Jutsu::MAX_LEVEL ?>",
                },
                trainingData: {
                    short: "<?= $player->trainingManager->getTrainingInfo(TrainingManager::TRAIN_LEN_SHORT, TrainingManager::$skill_types[0])?>",
                    long: "<?= $player->trainingManager->getTrainingInfo(TrainingManager::TRAIN_LEN_LONG, TrainingManager::$skill_types[0])?>",
                    extended: "<?= $player->trainingManager->getTrainingInfo(TrainingManager::TRAIN_LEN_EXTENDED, TrainingManager::$skill_types[0])?>",
                    jutsuTrainingInfo: "<?= $player->trainingManager->getTrainingInfo(TrainingManager::TRAIN_LEN_SHORT, 'jutsu:clone_combo'); ?>",
                    timeRemaining: "<?= System::timeRemaining($player->train_time - time(), 'short', false) ?>",
                    trainingDisplay: "<?= $player->trainingManager->trainingDisplay() ?>",
                    trainType: "<?= $player->trainingManager->trainType() ?>",
                },
                headers: {
                    isSetCancelTraining: <?= isset($_GET['cancel_training']) ?: "false" ?>,
                    isSetCancelConfirm: <?= isset($_GET['cancel_confirm']) ?: "false" ?>,
                    selfLink: '<?= $self_link ?>'
                }
            }),
            trainingContainer
        );
    })
</script>

<!-- Animates Timer on screen -->
<script type="text/javascript">
    countdownTimer(<?=$player->trainingManager->train_time_remaining?>, 'train_time_remaining');
</script>