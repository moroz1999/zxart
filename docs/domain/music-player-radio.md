## Music Player and Radio

### Overview
The online music player provides playback for tune lists and a radio mode driven by criteria.
Radio presets are identified by a `radiotype` key and display labels stored in the database.

### Play Threshold and Analytics
- Play is logged after 75% of track duration is reached by real playback time.
- Only time while actively playing counts; pauses accumulate, and seeking does not grant time.
- Log once per track; if playback exceeds duration, reset the accumulated timer.
- Analytics event: `ym(94686067, 'reachGoal', 'musicplay')`.

### Radio Presets (Legacy Parity)
Preset labels are stored in the database. Current labels by `radiotype`:
- `discover`: "Непроголосованные"
- `randomgood`: "Лучшие"
- `games`: "Из игр"
- `demoscene`: "Сцена"
- `lastyear`: "С прошлого года"
- `ay`: "AY"
- `beeper`: "Бипер"
- `exotic`: "Экзотика"
- `underground`: "Андерграунд"

### Radio Criteria Mapping
Use legacy parity criteria for presets:
- `randomgood`: min rating = averageVote + 0.2
- `games`: has game + min rating = averageVote + 0.2
- `demoscene`: party place <= 1000 and != 0 + min rating = averageVote + 0.2
- `ay`: formatGroup in [ay, aycovox, aydigitalay, ts] + min rating = averageVote + 0.2
- `beeper`: formatGroup in [beeper, aybeeper] + min rating = averageVote + 0.2
- `exotic`: formatGroup in [digitalbeeper, tsfm, fm, digitalay, saa] + min rating = averageVote + 0.2
- `discover`: not voted by current user + best votes = 100
- `underground`: plays < 10 + best votes = 500
- `lastyear`: if month < 3 use years [Y-1, Y], otherwise [Y], plus min rating = averageVote + 0.2
- Always require playable (mp3 exists).

Notes:
- `averageVote` is loaded from config (current value is 3.8).
- `not voted by` applies only to authenticated users; anonymous users use their runtime id.

### Endpoints
- New radio endpoint: `POST /radio/next-tune` (criteria-driven).
- New play reporting endpoint: `POST /tunes/play`.
- Legacy `/randomTune/type:{type}` should be removed after the new player replaces the old one.

### Criteria Persistence
- Authenticated users: persist the last selected radio criteria in User Preferences (`radio_criteria`).
- Anonymous users: persist criteria in `localStorage`.
