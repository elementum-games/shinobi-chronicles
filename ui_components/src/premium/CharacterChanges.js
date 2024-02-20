import { useModal } from "../utils/modalContext.js";
import { RenderPurchaseConfirmation } from "./PurchaseConfirmation.js";
import type {PlayerDataType} from "../_schema/userSchema";

type characterChangeProps = {|
    +playerData: PlayerDataType
|}
export function CharacterChanges({
    playerData
}: characterChangeProps) {
    const {openModal} = useModal();
    const stats = ['ninjutsu', 'taijutsu', 'genjutsu', 'speed', 'cast_speed', 'intelligence', 'willpower'];

    const [newUsername, setName] = React.useState(playerData.user_name);
    const [statResetName, setStatReset] = React.useState(stats[0]);
    const [statResetAmount, setStatRestAmount] = React.useState(100);
    const handleNameFieldChange = (event) => {
        setName(event.target.value);
    };

    const handleStatResetChange = (event) => {
        setStatReset(event.target.value);
    };
    const handleStateResetAmountChange = (event) => {
        setStatRestAmount(event.target.value);
    }

    React.useEffect(() => {
        const testInterval = setInterval(() => {
            console.log(statResetName + ' @ ' + statResetAmount + '%');
        }, 1000);

        return () => clearInterval(testInterval);
    })

    return(
        <>
            <div className="purchaseContainer">
                <div className="box-secondary halfWidth center">
                    <b>Reset Character</b>
                    You can reset your to a level 1 Akademi-sai.
                    <br />
                    This change is free and can not be reversed.
                    <br />
                    <RenderPurchaseConfirmation
                        text="Are you certain you wish to reset your character?"
                    />
                </div>
                <div className="box-secondary halfWidth center">
                    <b>Individual Stat Reset</b>
                    You can reset an individual stat to free up space in your total stat cap.
                    <br />
                    This change is free and can be used to allow further training.
                    <select className="purchaseSelectField" onChange={handleStatResetChange}>
                        {stats.map(function(name) {
                            return (
                                <option key={name} value={name}>{name.replace('_', ' ')}</option>
                            )
                        })}
                    </select>
                    <select className="purchaseSelectField" onChange={handleStateResetAmountChange}>
                        {[100,90,80,70,60,50,40,30,20,10].map(function(percentAmount) {
                            return (
                                <option key={percentAmount} value={percentAmount}>{percentAmount}%</option>
                            )
                        })}
                    </select>
                    <RenderPurchaseConfirmation
                        text="Are you certain you wish to reset your X stat?"
                    />
                </div>
            </div>
            <table className="table">
                <tbody>
                <tr><th colSpan="2">Character Changes</th></tr>
                <tr>
                    <th>Reset</th>
                    <th>Individual Stat Reset</th>
                </tr>
                <tr className="center">
                    <td>
                        You can reset your character to a level 1 Akademi-sai.
                        <br />
                        This change is <b>free</b> and <u>is not</u> reversible.
                        <br />
                        <div className="purchase_button" onClick={() => openModal({
                            header: 'Purchase Confirmation',
                            text: 'Are you sure you would like to reset your character?',
                            ContentComponent: null,
                            onConfirm: () => console.log("purchase...."),
                        })}>purchase</div>
                    </td>
                    <td>
                        Your can reset an individual stat, freeing up space in your total stat cap to train something else higher.
                        <br />
                        This purchase is <b>free</b> and <u>is not</u> reversible.
                        <br />
                        <div className="purchase_button" onClick={() => openModal({
                            header: 'Purchase Confirmation',
                            text: 'Are you sure you would like to reset your character?',
                            ContentComponent: null,
                            onConfirm: () => {
                                playerData.premiumCredits -= 5;
                                openModal({
                                    header: 'Confirmation',
                                    text: 'Stat reset....',
                                    ContentComponent: null,
                                    onConfirm: () => window.location.reload()
                                }
                            )},
                        })}>purchase</div>
                    </td>
                </tr>
                <tr>
                    <th>Reset AI Battle Counts</th>
                    <th>Reset PvP Battle Counts</th>
                </tr>
                <tr className="center">
                    <td>
                        This will reset your AI Wins and AI Losses to 0.
                        <br />
                    </td>
                    <td>
                        This will reset your PvP Wins and PvP Losses to 0.
                        <br />
                    </td>
                </tr>
                <tr>
                    <th>Username Change</th>
                    <th>Gender Change</th>
                </tr>
                <tr className="center">
                    <td>
                        Your first username change is free and X Ancient Kunai afterward.
                        <br />
                        Free changes: Y
                        <br />
                        <input type="text" onChange={handleNameFieldChange}/>
                        <div className="purchase_button" onClick={() => openModal({
                            header: 'Confirm Purchase',
                            text: 'Are you certain you would like to change your username?',
                            ContentComponent: null,
                            onConfirm: () => openModal({
                                header: 'Purchase Confirmed',
                                text: 'You have successfully changed your name to ' + newUsername,
                                ContentComponent: null
                            })
                        })}>purchase</div>
                    </td>
                    <td>

                    </td>
                </tr>
                </tbody>
            </table>
        </>
    );
}