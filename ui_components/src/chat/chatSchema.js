// @flow strict-local

// Keep these types in sync with return arrays from ChatAPIPresenter.php

export type ChatPostType = {|
    +id: number,
    +userName: string,
    +message: string,
    +userTitle: string,
    +userVillage: string,
    +avatarLink: string,
    +avatarStyle: string,
    +postTime: number,
    +timeString: string,
    +userProfileLink: string,
    +reportLink: string,
    +staffBannerName: string,
    +staffBannerColor: string,
    +userLinkClassNames: $ReadOnlyArray<string>,
|};