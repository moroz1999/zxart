## Voting

### Scope
Voting is shared by ZX art items and comments. Public AJAX requests use the CMS `vote` action, so action privileges are checked by `structureManager` before voting logic runs.

### ZX Art Items
- `votes` stores the weighted average rating.
- `votesAmount` stores the number of accepted votes.
- Direct voting is rejected when `isVotingDenied()` returns true.
- Rating calculation and persistence belong to `ZxArt\Voting\VotingService`; structure elements only expose data and compatibility methods.

### Comments
- Comments use `1` and `-1` votes.
- Comment vote recalculation stays comment-specific.

### Vote Histories
Every vote list — recent votes, the votes on an author's, group's or party's
works, and the votes on a single element — carries the voter as a
`CommentAuthorDto`. Its `url` is the SPA page of the author the user is
connected to, resolved through `EntityUrlResolver::urlForUser()`, and `null`
when the user has no author, so the name renders as plain text.
`userElement::getUrl()` must not be used for it: it returns the author's legacy
URL, which does not resolve as an SPA route.
