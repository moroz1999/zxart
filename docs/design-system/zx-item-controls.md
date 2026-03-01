# zx-item-controls

Unified component that places the vote rating and playlist (favourites) button on one line.
Replaces the previous pattern of using `zx-rating` + `zx-playlist-button` side by side, ensuring
a consistent appearance across all item types (pictures, prods, music).

## Location

`ng-zxart/src/app/shared/ui/zx-item-controls/`

Also registered as the `zx-item-controls` custom element in `AppModule` for use in legacy Smarty templates.

## Inputs

| Input | Alias (HTML attr) | Type | Default | Description |
|---|---|---|---|---|
| `elementId` | `element-id` | `number` | required | Element ID for voting and playlist |
| `type` | `type` | `string` | required | Element type: `zxPicture`, `zxProd`, `zxMusic` |
| `votes` | `votes` | `number` | `0` | Current overall rating |
| `votesAmount` | `votes-amount` | `number` | `0` | Number of votes cast |
| `userVote` | `user-vote` | `number` | `0` | Current user's vote (0 = not voted) |
| `denyVoting` | `deny-voting` | `boolean` | `false` | Hide rating when true; playlist button always shows |

## Behaviour

- Manages vote state internally — parents do not need to handle vote responses.
- When `denyVoting` is `true`, only the playlist button is rendered.
- Accepts both typed Angular bindings and string HTML attributes (custom element / legacy bridge).

## Angular usage

```html
<zx-item-controls
  [elementId]="item.id"
  type="zxPicture"
  [votes]="item.votes"
  [votesAmount]="item.votesAmount"
  [userVote]="item.userVote ?? 0"
  [denyVoting]="item.denyVoting"
></zx-item-controls>
```

## Legacy Smarty usage

```html
<zx-item-controls
  element-id="{$element->id}"
  type="zxPicture"
  votes="{$element->votes}"
  votes-amount="{$element->votesAmount}"
  user-vote="{$element->getUserVote()}"
  deny-voting="{if $element->isVotingDenied()}true{else}false{/if}"
></zx-item-controls>
```

## Replaces

- In Angular: `<zx-rating>` + `<zx-playlist-button>` placed side by side.
- In legacy templates: `<zx-vote>` + `{include file=... component.playlist.tpl}`.
