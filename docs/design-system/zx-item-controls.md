# zx-item-controls

Unified component that places the vote rating and playlist (favourites) button on one line.
Replaces the previous pattern of using `zx-rating` + `zx-playlist-button` side by side, ensuring
a consistent appearance across all item types (pictures, prods, music).

## Location

`ng-zxart/src/app/shared/ui/zx-item-controls/`

For use in legacy Smarty templates, see `zx-item-legacy-controls`.

## Inputs

| Input | Type | Default | Description |
|---|---|---|---|
| `elementId` | `number` | required | Element ID for voting and playlist |
| `type` | `string` | required | Element type: `zxPicture`, `zxProd`, `zxMusic` |
| `votes` | `number` | `0` | Current overall rating |
| `votesAmount` | `number` | `0` | Number of votes cast |
| `userRating` | `number \| null` | `null` | Current user's vote (null = not voted) |
| `denyVoting` | `boolean` | `false` | Hide rating when true; playlist button always shows |

## Behaviour

- Manages vote state internally — parents do not need to handle vote responses.
- When `denyVoting` is `true`, only the playlist button is rendered.

## Angular usage

```html
<zx-item-controls
  [elementId]="item.id"
  type="zxPicture"
  [votes]="item.votes"
  [votesAmount]="item.votesAmount"
  [userRating]="item.userVote"
  [denyVoting]="item.denyVoting"
></zx-item-controls>
```

## Replaces

- In Angular: `<zx-rating>` + `<zx-playlist-button>` placed side by side.
- In legacy templates: see `zx-item-legacy-controls`, which replaces `<zx-vote>` + `{include file=... component.playlist.tpl}`.
