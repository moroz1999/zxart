# zxart.ee — Website UI Kit

Click-thru recreation of the zxart.ee site, derived from the **`ng-zxart/`** Angular
app in the source repo. All component CSS lives in `../../preview/components.css`
(the same stylesheet the design-system cards use); page-level layout is in
`kit.css`.

## What's covered

- **Header** — logo, primary nav, search/theme/lang/sign-in cluster.
- **Picture card** — pixel-art tile with format/realtime/flickering badges,
  compo medal, 5-star vote strip, year.
- **Tune row** + **sticky bottom player** — chip name, duration, play state.
- **Prod card** — software-production tile (game, demo, intro, tool).
- **Letter selector** — A–Z navigator + sort chips.
- **Picture detail** — hero, metadata grid, action row, comments, asides.

## Screens (router state in `App.jsx`)

| Route | Component | Notes |
|---|---|---|
| `home` | `HomeScreen` | Latest pictures, top tunes, latest prods, top groups |
| `pictures` | `PicturesScreen` | Letter selector, sort, filter chips, grid |
| (picture click) | `PictureDetail` | Hero + metadata + comments + asides |
| `music` | `MusicScreen` | Tabs (Tunes / Authors / Top / Radio), tune list |
| anything else | `PlaceholderScreen` | Honest "not built" stub |

## What's intentionally *not* here

- Login / signup forms (the real flow is server-side OAuth).
- Upload / edit forms — out of scope for a visual kit.
- Live audio — the player is purely visual; no `<audio>` is wired up.
- The legacy Smarty templates (per project instructions).

## Deviations from production

- The pictures use **procedurally generated pixel-art SVGs** as stand-ins for
  real `.scr` images. They preserve `image-rendering: pixelated` and the 4:3
  aspect ratio so card layout matches.
- Comments / counts are illustrative.
- The header/player on the real site is Angular components with a Material
  Symbols ripple. Here, the ripple is omitted; hover/focus colors match.

## File map

```
ui_kits/website/
├── index.html          ← entry; mounts App
├── kit.css             ← page-level layout (extends colors_and_type.css + components.css)
├── App.jsx             ← router + theme + player state
├── data.jsx            ← sample fixtures
├── Icon.jsx            ← inline SVG icon paths (24px)
├── Primitives.jsx      ← ZxButton / ZxBadge / ZxMedal / ZxStars
├── Header.jsx
├── PictureCard.jsx     ← + procedural <PixelArtSVG/>
├── ProdCard.jsx
├── Player.jsx          ← TuneRow + Player
├── HomeScreen.jsx
├── PicturesScreen.jsx
├── PictureDetail.jsx
└── MusicScreen.jsx
```
