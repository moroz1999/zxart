interface VoteElement {
    votes: number,
    votesAmount: number,
    userVote: number
}

export type VoteElements<T extends string> = {
    [key in T]: Array<VoteElement>;
}
