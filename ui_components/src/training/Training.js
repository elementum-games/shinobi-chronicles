import type {
    PlayerDataType
} from "../_schema/userSchema.js";

type Props = {|
    +playerData: PlayerDataType
|};

const styles = {
    // general style
    // 1em = 16px
    body: {
        margin: '1em 0.5em',
        padding: '0em 0em 2em 0em',
        border: '0em solid #554d35e6',
        borderRadius: '0.5em'
    },
    topHeader: {
        border: '0em solid',
        borderRadius: '0.5em 0.5em, 0em, 0em' // Should match parent element
    },
    bgColor: {
        backgroundColor: '#d2c9b3',
        color: '958556e6',
    },
    indent: {
        margin: '0em 1em'
    },
    indentLabel: {
      fontWeight: 'bold',
      width: '80%',
    },
    roundBorder: {
        borderRadius: '0.5em 0.5em 0em 0em',
    },
    textAlignCenter: {
        textAlign: 'center'
    },
    //specific elements
    option: {
        padding: '1em, 0em',
        margin: '0em 0.3em',
        border: '0.5px solid #958556e6',
        borderRadius: '0 0 0.5em 0.5em', // Should match parent element
        flex: '1 1 calc(25% - 20px)', // Distributes space for 4 items, reduces to 33.33% for 3 items
        minWidth: '200px',
        backgroundColor: '8B4513',
        width: '100%',
        textAlign: 'center'
    }
  };

function CancelTrainingDetails({
    playerData,
    headers
}){

    return (
        <div style={styles.textAlignCenter}>
            <h2>Cancel Training</h2>
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
    const combinedHeaderStyles = {...styles.header, ...styles.topHeader}
    return (
        <>
            <div >
            <h2 style={combinedHeaderStyles}>Academy</h2>
                <div style={styles.indent}>
                    <p>Here at the academy, you can take classes to improve your skills, attributes, or skill with a jutsu.</p>
                    <h2>Skill/Attribute training</h2>
                    <p>Short: {trainingData.short}</p>
                    <p>Long: {trainingData.long}</p>
                    <p>Extended: {trainingData.extended}</p>
                    <h2>Jutsu Training</h2>
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
        <div style={styles.option}>
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
    const combinedStyles = {...styles.bgColor, ...styles.body}
    return (
        <div style={combinedStyles} id='TrainingContainer'>
            
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
                    <div style={styles.textAlignCenter} >
                        <h2 style={styles.header}> {trainingData.trainType} Training</h2>
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