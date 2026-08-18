# Music player and radio

## Player
Tunes play in the browser. A list of tunes anywhere on the site — an author's
music, a party's compo, a playlist, a search result — becomes the playlist, and
playback continues while the visitor keeps browsing.

A play is counted once the visitor has actually heard three quarters of the
track. Only time spent playing counts: pausing does not accumulate it and
seeking forward does not grant it, so a play means someone listened.

## Radio
The radio picks tunes endlessly by criteria instead of following a list.

A visitor can narrow it by rating, year, the author's country, the sound group,
the file format, the party place, whether the tune comes from a party at all, and
whether they have rated it. Only tunes that can actually be played are offered.

Presets are ready-made sets of those criteria:

- **Undiscovered** — good tunes the visitor has not rated yet
- **Best** — the best-rated tunes
- **From games**, **Demoscene**, **Press** — by the category of the production the
  tune was written for, counting the whole category with everything filed beneath
  it
- **Since last year** — recent work
- **AY**, **Beeper**, **Exotic** — by the sound the tune was written for
- **Underground** — well-rated tunes hardly anybody has played

A preset only fills the filters in; the visitor can then adjust them.

The chosen criteria are remembered: with an account they follow the visitor
across devices, and without one they stay in the browser.

How it is built: [../features/music-player-radio.md](../features/music-player-radio.md)
