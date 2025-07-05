interface VoteElement {
    votes: number,
    userVote: number
}

export type VoteElements<T extends string> = {
    [key in T]: Array<VoteElement>;
}
