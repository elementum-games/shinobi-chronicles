import type {
    PlayerDataType
} from "../_schema/userSchema.js";

type Props = {|
    +playerData: PlayerDataType
|};

function CancelTrainingDetails({
    playerData,
    headers
}){
    return (
        <>
            <p>Cancel Training</p>
            <p>
            Are you certain you wish to cancel your training? You will not gain any of your potential gains.
            </p>
            <button><a href="<?=$self_link?>&cancel_training=1&cancel_confirm=1">Confirm</a></button>
        </>
    )
}

function TrainingDetails({
    trainingData
}){
    return (
        <>
            <div id='DetailPanelContainer'>
            <h2 className={'themeHeader'} style={{textAlign: 'center'}}>Academy</h2>
                <div>
                    <p>Here at the academy, you can take classes to improve your skills, attributes, or skill with a jutsu.</p>
                    <h3>Skill/Attribute training</h3>
                    <label>Short: {trainingData.short}</label>
                    <label>Long: {trainingData.long}</label>
                    <label>Extended: {trainingData.Extended}</label>
                </div>
            </div>
        </>
    )
}

function DetailPanel({
    playerData,
    trainingData,
    headers
}){
    let content;
    
    if( playerData.hasActiveTraining && headers.isSetCancelTraining && !(headers.isSetCancelConfirm) ){
        content = 
        <CancelTrainingDetails
        playerData = {playerData}
        headers = {headers}
        />;
    } else {
        content = 
        <TrainingDetails
        trainingData = {trainingData}
        />;
    }


    return (
        <>
        {content}
        </>
    )
}

function Training({
    playerData,
    trainingData,
    headers     
}: props
){
    return (
        <div id='TrainingContainer'>
            <DetailPanel 
            playerData = {playerData}
            trainingData = {trainingData}
            headers = {headers}
            />
        </div>
    )
}

window.Training = Training; //man I don't even know what this does