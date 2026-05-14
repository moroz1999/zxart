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
