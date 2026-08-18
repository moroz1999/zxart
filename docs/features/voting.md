# Voting — implementation

Domain rules: [../domain/voting.md](../domain/voting.md).

Public AJAX requests use the CMS `vote` action, so action privileges are checked
by `structureManager` before any voting logic runs.

## ZX art items

- `votes` stores the weighted average rating, `votesAmount` the number of
  accepted votes.
- Direct voting is rejected when `isVotingDenied()` returns true.
- Rating calculation and persistence belong to `ZxArt\Voting\VotingService`;
  structure elements only expose data and compatibility methods.

## Comments

Comments use `1` and `-1` votes, and their recalculation stays comment-specific.

## Vote histories

Every vote list carries the voter as a `CommentAuthorDto`. Its `url` is the SPA
page of the author the user is connected to, resolved through
`EntityUrlResolver::urlForUser()`, and `null` when the user has no author, so
the name renders as plain text. `userElement::getUrl()` must not be used for it:
it returns the author's legacy URL, which does not resolve as an SPA route.
