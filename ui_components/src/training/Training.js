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
                {/* TODO: I'm not sure how train gains or partial gains works here */}
            Are you certain you wish to cancel your training? You will not gain any of your potential <strong>{playerData.trainGains}</strong> gains.
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
        {/* Test this with team */}
        { (playerData.hasTeam && playerData.hasTeamBoostTraining ) ? 
        <em>*Note: Your team has a chance at additional stat gains, these are not reflected above.</em> : <></>}
        </>
    )
}

function Option({
    title,
    children
}){
    let styleItem = {
        flex: '1 1 calc(25% - 20px)', // Distributes space for 4 items, reduces to 33.33% for 3 items
        backgroundColor: '8B4513',
        width: '100%',
        textAlign: 'center'
    }

    let buttonsArray = []

    return (
        <div style={styleItem} id='optionContainer'>
            <h2 className={"themeHeader"}>{title}</h2>
            {children}
        </div>
    )
}

//Displays Training Selection Input {TrainingOption}
function SelectTrainingPanel({
    playerData
}){

    let styleContainer = {
        display: 'flex',
        flexWrap: 'wrap',
    }

    return (
        <div style={styleContainer}>
            {/* Skill Training */}
            <Option
            key={0}
            title="Skills">
                <select>
                    <option value="grapefruit">Grapefruit</option>
                    <option value="lime">Lime</option>
                    <option selected value="coconut">Coconut</option>
                    <option value="mango">Mango</option>
                </select>

                <button>
                    Short
                </button>
                <button>
                    Long
                </button>
                <button>
                    Extended
                </button>
            </Option>
            
            {/* Attributes Training */}
            <Option
            key={1} 
            title="Attributes">
                <select>
                    <option value="grapefruit">Grapefruit</option>
                    <option value="lime">Lime</option>
                    <option selected value="coconut">Coconut</option>
                    <option value="mango">Mango</option>
                </select>

                <button>
                    Short
                </button>
                <button>
                    Long
                </button>
                <button>
                    Extended
                </button>
            </Option>

            {/* Jutsu Training */}
            <Option 
            key={2}
            title="Jutsu">
                <select>
                    <option value="grapefruit">Grapefruit</option>
                    <option value="lime">Lime</option>
                    <option selected value="coconut">Coconut</option>
                    <option value="mango">Mango</option>
                </select>

                <button> Train </button>
            </Option>
            
            {/* Hide if no Bloodline or Trainable Jutsu */}
            {/* Bloodline Jutsu Training */}
            {playerData.bloodlineID != 0 && playerData.hasTrainableBLJutsu ? (
            <Option 
            key={3}
            title="Bloodline Jutsu">
                <select>
                    <option value="grapefruit">Grapefruit</option>
                    <option value="lime">Lime</option>
                    <option selected value="coconut">Coconut</option>
                    <option value="mango">Mango</option>
                </select>

                <button> Train </button>
            </Option>
            ) : (<></>)}
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

            {/* Hide when training */}
            {!playerData.hasActiveTraining ? (
                    <SelectTrainingPanel 
                    playerData = {playerData}
                    />
                ) : (
                    <div style={{textAlign: 'center'}}>
                        <h2  className="themeHeader" style={{borderRadius: '0px'}}> {trainingData.trainType} Training</h2>
                        <p>{trainingData.trainingDisplay}</p>
                        <p id="train_time_remaining">{trainingData.timeRemaining} remaining...</p>
                        <button><a href={headers.selfLink + "&cancel_training=1"}>Cancel Training</a></button>
                    </div>
                )}

        </div>
    )
}

window.Training = Training; //man I don't even know what this does