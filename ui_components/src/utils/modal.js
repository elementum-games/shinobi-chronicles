export function Modal({ isOpen, header, text, ContentComponent, onConfirm, onClose }) {
    return (
        <>
            {isOpen && "closed" && (
                <>
                    <div className="modal_backdrop"></div>
                    <div className="modal">
                        <div className="modal_header">{header}</div>
                        <div className="modal_text">{text}</div>
                        {ContentComponent && (
                            <div className="modal_content">
                                <ContentComponent />
                            </div>
                        )}
                        {onConfirm && ( 
                            <div className="modal_confirm_button" onClick={() => onConfirm()}>confirm</div>
                        )}
                        <div className="modal_cancel_button" onClick={() => onClose()}>cancel</div>
                    </div>
                </>
            )}
        </>
    );
}