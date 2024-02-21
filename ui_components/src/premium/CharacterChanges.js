import { useModal } from "../utils/modalContext.js";
import { PurchaseConfirmation } from "./PurchaseConfirmation.js";
import type {PlayerDataType} from "../_schema/userSchema";

type characterChangeProps = {|
    +playerData: PlayerDataType,
    +costs: [],
    +genders: [],
    +skills: [],
|}
export function CharacterChanges({
    playerData,
    costs,
    genders,
    skills
}: characterChangeProps) {
    const {openModal} = useModal();

    const [newUsername, setName] = React.useState("");
    const [statResetName, setStatReset] = React.useState(skills[0]);
    const [newGender, setGender] = React.useState(genders[0]);

    const handleNameFieldChange = (event) => {
        setName(event.target.value);
    };

    const handleStatResetChange = (event) => {
        setStatReset(event.target.value);
    };

    const handleGenderFieldChange = (event) => {
        setGender(event.target.value);
    }

    function formatSkillName(skillName): string {
        let nameArray = skillName.split("_");
        let returnName = '';

        nameArray.map(function(namePos) {
            returnName += namePos[0].toUpperCase() + namePos.substring(1) + ' ';
        })
        return returnName.substring(0, returnName.length - 1);
    }

    /*React.useEffect(() => {
        const testInterval = setInterval(() => {
            console.log(
            "PD: " + playerData +
            "\r\nSRN: " + statResetName + "\r\nNCFN: " + newUsername
            + "\r\nGenders: " + genders + "\r\nGender Select: " + newGender
            + "\r\nSkills: " + skills);
        }, 1000);

        return () => clearInterval(testInterval);
    })*/

    return(
        <div className="purchaseContainer">
            <div className="box-secondary halfWidth center">
                <b>Reset Character</b>
                You can reset your to a level 1 Akademi-sai.<br />
                This change is free and can not be reversed.<br />
                    <PurchaseConfirmation
                        text="Are you certain you wish to reset your character?"
                        buttonValue={"reset"}
                    />
            </div>
            <div className="box-secondary halfWidth center">
                <b>Individual Stat Reset</b>
                You can reset an individual stat to free up space in your total stat cap.
                <br />
                This change is free and can be used to allow further training.
                <select className="purchaseSelectField" onChange={handleStatResetChange}>
                    {skills.map(function(name) {
                        return (
                            <option key={name} value={name}>{formatSkillName(name)}</option>
                        )
                    })}
                </select>
                    <PurchaseConfirmation
                        text={"Are you certain you wish to reset your " + formatSkillName(statResetName) + "?"}
                        buttonValue={"reset"}
                    />
            </div>
            <div className="box-secondary fullWidth center">
                <b>Reset Ai and PvP Battle Counts</b>
                <div className="subPurchaseContainer">
                    <div className="box-secondary halfWidth noBorder center">
                        Reset AI wins and losses to 0.<br />
                        Costs {costs.ai_count_reset} Ancient Kunai<br />
                        <PurchaseConfirmation
                            text="Are you certain you wish to reset your AI wins and losses?"
                            buttonValue={"reset"}
                        />
                    </div>
                    <div className="box-secondary halfWidth noBorder center">
                        You can reset your PvP wins and losses to 0.
                        <br />
                        Costs {costs.pvp_count_reset} Anicent Kunai<br />
                        <PurchaseConfirmation
                            text={"Are you certain you wish to reset your PvP wins and losses?"}
                            buttonValue={"reset"}
                        />
                    </div>
                </div>
                You can reset both AI and PvP wins and losses at a discounted rate of {costs.pvp_and_ai_count_reset} Ancient Kunai
                <PurchaseConfirmation
                    text="Are you certain you want to reset both you PvP and AI wins/losses?"
                    buttonValue={"reset"}
                />
            </div>
            <div className="box-secondary halfWidth center">
                <b>Change Name</b>
                You can change your character name for free once.<br />
                Each change afterward costs <br />
                Name case changes are free (example: name1 => NaMe1).<br />
                <input className="purchaseTextField" type="text" onChange={handleNameFieldChange} />
                <PurchaseConfirmation
                    text={
                        "Are you certain you wish to change your name from " + playerData.user_name + " to "
                        + newUsername + "?"
                    }
                    buttonValue={"change"}
                />
            </div>
            <div className="box-secondary halfWidth center">
                <b>Change Gender</b>
                You can change your characters gender for <br />
                ('None' gender will not be displayed on view profile)
                <select className="purchaseSelectField" onChange={handleGenderFieldChange}>
                    {genders.map(function(name) {
                        return (
                            <option key={name} value={name}>{formatSkillName(name)}</option>
                        )
                    })}
                </select>
                <PurchaseConfirmation
                    text={"Are you certain you wish to change your gender from " + playerData.gender
                        +  " to " + newGender + "?"
                    }
                    buttonValue={"change"}
                />
            </div>
            <div className="box-secondary fullWidth center">
                <b>Change Village</b>
                This is changing village....<br />
                This is just to show what full width would look like for now...
                <PurchaseConfirmation
                    text="Are you certain you would like to change your village?"
                    buttonValue={"change"}
                />
            </div>
            <div className="box-secondary halfWidth center">
                <b>Post-full-width Test</b>
                <PurchaseConfirmation
                    text="You would like to purchase???"
                />
            </div>
            <div className="box-secondary halfWidth center">
                <b>Post-full-width Test</b>
                <PurchaseConfirmation
                    text="You would like to purchase???"
                />
            </div>
        </div>
    );
}