export function CharacterAvatar({
  imageSrc,
  maxWidth,
  maxHeight,
  avatarStyle,
  frameClassNames = []
}) {
  return /*#__PURE__*/React.createElement("div", {
    style: {
      maxWidth,
      maxHeight
    },
    className: `avatar_frame ${avatarStyle} ${frameClassNames.join(' ')}`
  }, /*#__PURE__*/React.createElement("img", {
    className: `avatar_img ${avatarStyle}`,
    src: imageSrc
  }));
}