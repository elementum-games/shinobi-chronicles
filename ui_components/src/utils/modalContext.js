import { Modal } from './modal.js';

const ModalContext = React.createContext();
export const useModal = () => React.useContext(ModalContext);

export const ModalProvider = ({ children }) => {
    const [modalProps, setModalProps] = React.useState({
        isOpen: false,
        header: '',
        text: '',
        ContentComponent: null,
    });

    const openModal = ({ header, text, ContentComponent, onConfirm }) => {
        setModalProps({
            isOpen: true,
            header,
            text,
            ContentComponent,
            onConfirm,
        });
    };

    const closeModal = () => {
        setModalProps((prevProps) => ({ ...prevProps, isOpen: false }));
    };

    return (
        <ModalContext.Provider value={{ ...modalProps, openModal, closeModal }}>
            {children}
            <Modal {...modalProps} onClose={closeModal} />
        </ModalContext.Provider>
    );
};