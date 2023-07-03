// @flow

import type { AvatarStyles } from "./_schema/userSchema.js";

type CharacterAvatarProps = {|
    +imageSrc: string,
    +maxWidth: number,
    +maxHeight: number,
    +avatarStyle: AvatarStyles,
    +frameClassNames?: $ReadOnlyArray<string>,
|};
export function CharacterAvatar({
    imageSrc,
    maxWidth,
    maxHeight,
    avatarStyle,
    frameClassNames = []
}: CharacterAvatarProps): React$Node {
    return (
        <div style={{ maxWidth, maxHeight }} className={`avatar_frame ${avatarStyle} ${frameClassNames.join(' ')}`}>
            <img className={`avatar_img ${avatarStyle}`} src={imageSrc} />
        </div>
    )
}