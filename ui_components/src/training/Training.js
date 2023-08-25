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
        <div style={{textAlign: 'center'}}>
            <h2 className={'themeHeader'}>Cancel Training</h2>
            <p>
                {/* TODO: I'm not sure how train gains or partial gains works here */}
            Are you certain you wish to cancel your training? You will not gain any of your potential <strong>{playerData.trainGains}</strong> gains.
            </p>
            <button><a href={headers.selfLink + '&cancel_training=1&cancel_confirm=1'}>Confirm</a></button>
        </div>
    )
}

function TrainingDetails({
    playerData,
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
                    <h3>Jutsu Training:</h3>
                    <p>{trainingData.jutsuTrainingInfo}</p>
                    { (playerData.hasTeam && playerData.hasTeamBoostTraining ) ? 
                    <em>*Note: Your team has a chance at additional stat gains, these are not reflected above.</em> : <></>}
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
        playerData = {playerData}
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
    playerData,
    headers
}){

    // TODO: I think this is a heavy calculation for a react component so might need to change this in the future
    //Only works on words with _ separators 
    function capitalize(word){

        const arr = word.replace("_", " ").split(" ");

        for (var i = 0; i < arr.length; i++) {
            arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1);
        }

        const result = arr.join(" ");
        return result;
    }

    let styleContainer = {
        display: 'flex',
        flexWrap: 'wrap',
    }
    
    let tempkey = 0; //for child elements

    return (
        <div style={styleContainer}>
            {/* Skill Training */}
            <Option
            key={0}
            title="Skills">
                <form action={headers.selfLink} method="post">
                    <select name="skill">
                        {playerData.validSkillsArray.map( (skillName) => {
                            return (<option key={tempkey++} value={skillName}>{capitalize(skillName)}</option>)
                        })}
                    </select>   

                    <br></br>

                    <input type="submit" name="train_type" value="Short" />
                    <input type="submit" name="train_type" value="Long" />
                    <input type="submit" name="train_type" value="Extended" />
                </form>
            </Option>
            
            {/* Attributes Training */}
            <Option
            key={1} 
            title="Attributes">
                <form action={headers.selfLink} method="post">
                    <select name="attributes">
                        {playerData.validAttributesArray.map( (attributeName) => {
                            return (<option key={tempkey++} value={attributeName}>{capitalize(attributeName)}</option>)
                        })}
                    </select>   

                    <br></br>

                    <input type="submit" name="train_type" value="Short" />
                    <input type="submit" name="train_type" value="Long" />
                    <input type="submit" name="train_type" value="Extended" />
                </form>
            </Option>

            {/* Jutsu Training */}
            <Option 
            key={2}
            title="Jutsu">
                <form action={headers.selfLink} method="post">
                    <select name="jutsu">
                        {Object.keys(playerData.allPlayerJutsu).map((key) => {

                            const item = playerData.allPlayerJutsu[key].name;
                            const id = playerData.allPlayerJutsu[key].id;
                            const level = playerData.allPlayerJutsu[key].level;

                            if(level <= playerData.jutsuMaxLevel){
                                return <option key={key} value={id}>{item}</option>;
                            } else {
                                return null;
                            }

                        })}
                    </select>   

                    <br></br>

                    <input type="submit" name="train_type" value="Train" />
                </form>
            </Option>
            
            {/* Hide if no Bloodline or Trainable Jutsu */}
            {/* Bloodline Jutsu Training */}
            {playerData.bloodlineID != 0 && playerData.hasTrainableBLJutsu ? (
            <Option 
            key={3}
            title="Bloodline Jutsu">
                <form action={headers.selfLink} method="post">
                <select name="bloodline_jutsu">
                    {Object.keys(playerData.allPlayerBloodlineJutsu).map((key) => {

                        const item = playerData.allPlayerBloodlineJutsu[key].name;
                        const id = playerData.allPlayerBloodlineJutsu[key].id;
                        const level = playerData.allPlayerBloodlineJutsu[key].level;

                        if(level <= playerData.jutsuMaxLevel){
                            return <option key={key} value={id}>{item}</option>;
                        } else {
                            return null;
                        }

                    })}
                </select>

                <br></br>

                <input type="submit" name="train_type" value="Train" />
                </form>
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
                    headers = {headers}
                    />
                ) : (
                    <div style={{textAlign: 'center'}}>
                        <h2  className="themeHeader" style={{borderRadius: '0px'}}> {trainingData.trainType} Training</h2>
                        <p>{trainingData.trainingDisplay}</p>
                        <p id="train_time_remaining">{trainingData.timeRemaining} remaining...</p>
                        {
                            (!headers.isSetCancelTraining) ? <button><a href={headers.selfLink + "&cancel_training=1"}>Cancel Training</a></button> : <></>
                        }
                    </div>
                )}

        </div>
    )
}

window.Training = Training; //man I don't even know what this does