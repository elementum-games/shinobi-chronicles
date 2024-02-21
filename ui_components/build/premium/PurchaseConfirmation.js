import { useModal } from "../utils/modalContext.js";
export function PurchaseConfirmation({
  text,
  header = "Purchase Confirmation",
  contentComponent = null,
  onConfirm = null,
  buttonValue = "purchase"
}) {
  const {
    openModal
  } = useModal();
  return /*#__PURE__*/React.createElement("div", {
    className: "purchase_button",
    onClick: () => openModal({
      header: header,
      text: text,
      ContentComponent: contentComponent,
      onConfirm: onConfirm
    })
  }, buttonValue.toLowerCase());
}