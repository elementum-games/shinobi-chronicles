function _extends() { _extends = Object.assign ? Object.assign.bind() : function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }
import { Modal } from './modal.js';
const ModalContext = React.createContext();
export const useModal = () => React.useContext(ModalContext);
export const ModalProvider = ({
  children
}) => {
  const [modalProps, setModalProps] = React.useState({
    isOpen: false,
    header: '',
    text: '',
    ContentComponent: null
  });
  const openModal = ({
    header,
    text,
    ContentComponent,
    onConfirm
  }) => {
    setModalProps({
      isOpen: true,
      header,
      text,
      ContentComponent,
      onConfirm
    });
  };
  const closeModal = () => {
    setModalProps(prevProps => ({
      ...prevProps,
      isOpen: false
    }));
  };
  return /*#__PURE__*/React.createElement(ModalContext.Provider, {
    value: {
      ...modalProps,
      openModal,
      closeModal
    }
  }, children, /*#__PURE__*/React.createElement(Modal, _extends({}, modalProps, {
    onClose: closeModal
  })));
};