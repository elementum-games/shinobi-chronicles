import { useModal } from "../utils/modalContext.js";

type purchaseConfirmationProps = {|
    +header: string,
    +text: string,
    +ContentComponent: function,
    +onConfirm: function,
|}
export function RenderPurchaseConfirmation({
    text,
    header = "Purchase Confirmation",
    contentComponent = null,
    onConfirm = null
}: purchaseConfirmationProps) {
    const { openModal } = useModal();

    return(
        <div className="purchase_button" onClick={() => openModal({
            header: header,
            text: text,
            ContentComponent: contentComponent,
            onConfirm: onConfirm,
        })}>purchase</div>
    );
}