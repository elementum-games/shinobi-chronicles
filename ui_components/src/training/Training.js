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
            <h2 className={'themeHeader'} style={{textAlign: 'center'}} >Cancel Training</h2>
            <p>
            Are you certain you wish to cancel your training? You will not gain any of your potential gains.
            </p>
            <button><a href={headers.selfLink + '&cancel_training=1&cancel_confirm=1'}>Confirm</a></button>
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
                    <p>Short: {trainingData.short}</p>
                    <p>Long: {trainingData.long}</p>
                    <p>Extended: {trainingData.extended}</p>
                </div>
            </div>
        </>
    )
}

//Displays Training Data {Training Details, CancelTrainingDetails}
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

function Option({
    title
}){
    let styleItem = {
        flex: '1 1 calc(25% - 20px)', // Distributes space for 4 items, reduces to 33.33% for 3 items
        backgroundColor: '8B4513',
        width: '100%',
        textAlign: 'center'
    }

    let buttonsArray = []

    return (
        <div  style={styleItem} id='optionContainer'>
            <h2>{title}</h2>
            {buttonsArray.map((item, index) => (
                <div key={index}>{item}</div>
            ))}
        </div>
    )
}

//Displays Training Selection Input {TrainingOption}
function SelectTrainingPanel({
}){

    let styleContainer = {
        display: 'flex',
        flexWrap: 'wrap',
    }

    return (
        <div style={styleContainer}>
            <Option
            key={0}
            title="Skills"
            />
            <Option
            key={1} 
            title="Attributes"
            />
            <Option 
            key={2}
            title="Jutsu"
            />
            <Option 
            key={3}
            title="Bloodline Jutsu"
            />
        </div>
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
            <SelectTrainingPanel

            />
        </div>
    )
}

window.Training = Training; //man I don't even know what this does