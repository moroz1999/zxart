## Tune (Chiptune)

### Purpose
A musical work for the ZX Spectrum: a chiptune in one of the tracker or chip
formats. A tune is a standalone musical work.

### Main Fields
- **title** — track title
- **year** — year of creation
- **description** — description
- **file** — the track file
- **fileName** — the file name it was uploaded under

### Technical Music Parameters

#### Format and Type
- **type** — the file format, either a tracker format (PT3, STC, ASC, FTC and
  the rest) or a chip format (AY, YM). It is read out of the uploaded file, so
  nobody fills it in by hand.
- **formatGroup** — the sound group: the chip and playback setup the tune was
  written for. It gathers formats that sound alike so they can be browsed
  together, and unlike the format it is the uploader's own choice. The whole
  site names a group the same way — the upload form, the tune page, the music
  search, an author's music and the radio — and the tune page links from it to
  the music search narrowed to that sound. Some families are recognised from
  the file itself (TurboSound, SAA, digital AY and FM) and the recognised value
  wins over the one that was picked.

#### Sound Characteristics
- **chipType** — the sound chip: AY-3-8910, AY-3-8912, YM2149, or TurboSound
  (two chips)
- **channels** — how many channels, normally 3 or 6
- **channelsType** — the channel order: ABC for the standard three, its
  permutations (ACB, BAC…), or ABCABC for TurboSound's six
- **frequency**, **intFrequency** — the chip and interrupt frequency

#### Metadata from the File
- **internalTitle**, **internalAuthor** — the title and author written inside
  the track file
- **container** — the file container
- **program** — the tracker the track was written in
- **time** — how long it plays

#### Conversion
The site converts a tune for playback, and records the channel order, chip,
frequencies and converter version the converted copy was made with.

#### Additional Files
Besides the track itself a tune can carry the tracker source it was written in
and an MP3 for listening in the browser; each is offered for download when it
exists. A tune can also be embedded on another site.

### Relations with Other Entities

#### Authorship
- **author** — the tune's authors. Authorship decides where the tune is filed
  and which author pages it appears on, so it is never left empty: at least one
  author must be given, and a tune submitted without one is credited to the
  "unknown author".

#### Party (Competitions)
- **party** — the demoparty the tune was released at
- **partyplace** — the place it took in the competition
- **compo** — the competition it ran in

#### Related Works
- **prod** — the production or release the tune belongs to: soundtracks. It is a
  production in almost every case, but a single release may be named instead, so
  both can be picked.
- **inspired** — the composition or melody the tune is based on

#### Press
- **mentions** — the press articles that mention the tune

### Special Flags
- **isPlayable** — can be played in the browser, decided by the format
- **isRealtime** — written live, in the limited time of a party competition
- **denyPlaying** — playing is not allowed for this tune

### Usage Statistics
- **plays** — how often the tune has been played

### Tags and Categorization
- **tagsText** — the tune's tags
- **tagsAmount** — how many tags it carries

### Voting and Comments
- **votes**, **votesAmount** — average rating and how many people voted
- **denyVoting** — voting is closed for this tune
- **commentsAmount**, **denyComments** — comments and whether they are closed

### Metadata
- **dateAdded** — when it was added
- **userId** — the visitor who added it
- **zxTunesId** — the tune's identity in the ZXTunes database

### Top Music
The top-music page ranks the whole collection by rating, highest first, and
offers no other ordering.

A strip of chips above it narrows the top to one named subset, exactly as the
top-graphics page does. The subset is part of the page address, so a narrowed
top can be linked to and shared, and paging through it keeps the subset. The
subsets are:

- covers, and originals — everything the "Cover" tag marks, and everything it
  does not
- the sound types: AY, beeper, digital AY, SAM Coupé, TurboSound and FM, each
  gathering the sound groups that belong to it
- games, demoscene, and press — by the category of the production the tune was
  written for, counting the whole category with everything filed beneath it, so
  a megademo's soundtrack counts as demoscene

Without a subset the page ranks the entire collection.

### Integration with External Services
MP3 files live on a separate music host. Tunes imported from ZXTunes keep their
identity there.

### Constraints and Rules
1. The file must be in one of the supported chiptune formats.
2. The chip and the channel count must match the format.
3. Whether a tune can be played in the browser follows from its format.
4. The title and author written inside the file are read out of it on upload.
5. Conversion adapts a track to different playback parameters.
6. The inspiration source is named for covers and remixes.
7. TurboSound's six channels need a second chip, real or emulated.
8. A tune is announced with its audio for link previews only when an MP3 exists.

How it is built: [../features/tune.md](../features/tune.md)
