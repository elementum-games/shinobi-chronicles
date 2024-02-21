import { useModal } from "../utils/modalContext.js";

type purchaseConfirmationProps = {|
    +header: string,
    +text: string,
    +ContentComponent: function,
    +onConfirm: function,
    +buttonValue: 'purchase'
|}
export function PurchaseConfirmation({
    text,
    header = "Purchase Confirmation",
    contentComponent = null,
    onConfirm = null,
    buttonValue = "purchase"
}: purchaseConfirmationProps) {
    const { openModal } = useModal();

    return(
        <div className="purchase_button" onClick={() => openModal({
            header: header,
            text: text,
            ContentComponent: contentComponent,
            onConfirm: onConfirm,
        })}>{buttonValue.toLowerCase()}</div>
    );
}