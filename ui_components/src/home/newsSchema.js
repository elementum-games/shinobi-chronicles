// @flow strict-local

export type NewsPostType = {|
    +post_id: number,
    +sender: string,
    +title: string,
    +message: string,
    +time: number,
    +tags: $ReadOnlyArray < string >,
    +version: string,
|};