## zxMusic (Chiptune)

### Purpose
Musical work for ZX Spectrum - chiptunes in various tracker and chip formats. Represents a standalone musical work.

### Main Fields
- **title** - track title
- **year** - year of creation
- **description** - description (pre-formatted text)
- **file** - track file ID
- **fileName** - file name

### Technical Music Parameters

#### Format and Type
- **type** - file type/format:
  - Tracker formats (PT3, STC, ASC, FTC, etc.)
  - Chip formats (AY, YM, etc.)
  - Filled in automatically from the uploaded file by the conversion service, so
    no form offers it. Exposed to the frontend as `format`.
- **formatGroup** - sound group (chip and playback setup the tune was written for)
  - Groups similar formats for filtering
  - Chosen by the author in the form, unlike `type`
  - Options come from `getFormatGroups()` on the element through the `enums` map
    of `/formdata/`; the labels are the SPA's own `player.formatGroup.*`
    translations (a `clientLabels` enum), so the upload form, the tune page, the
    music search, the author music tab and the radio all name a group alike
  - Exposed by `/tune-details/` as `formatGroup` and shown on the tune page as a
    link to the music search filtered by that group. The conversion service
    overrides it for the formats it can recognise on its own (TS, SAA, digital
    AY and FM families)

#### Sound Characteristics
- **chipType** - sound chip type:
  - AY-3-8910
  - AY-3-8912
  - YM2149
  - Turbosound (two chips)
- **channels** - number of channels (usually 3 or 6)
- **channelsType** - channel type:
  - ABC (standard 3 channels)
  - ACB, BAC, etc. (channel permutations)
  - ABCABC (Turbosound - 6 channels)
- **frequency** - chip frequency (string, e.g. "1773400 Hz")
- **intFrequency** - chip frequency (integer)

#### Metadata from File
- **internalTitle** - title from track file
- **internalAuthor** - author from track file
- **container** - file container
- **program** - program/tracker used to create the track
- **time** - track duration

#### Conversion
- **conversionChannelsType** - channel type after conversion
- **conversionChipType** - chip type after conversion
- **conversionFrequency** - frequency after conversion
- **conversionIntFrequency** - frequency after conversion (integer)
- **converterVersion** - converter version

#### Additional Files
- **trackerFile** - tracker file (source)
- **trackerFileName** - tracker file name
- **mp3Name** - MP3 file name (for listening)
- **mp3Url** - link to MP3 file for listening (if available)
- **originalFileUrl** - link to original track file for download (if available)
- **trackerFileUrl** - link to tracker/source file for download (if available)
- **embedCode** - player embed code

### Relations with Other Entities

#### Authorship
- **author** - track authors (array of IDs)
  - Authorship drives the tune's placement and author pages, so it is never left
    empty: the Angular form requires at least one author, and the backend falls
    back to the "unknown author" element when none is submitted.

#### Party (Competitions)
- **party** - party ID (demoparty, competition)
- **partyplace** - place in competition
- **compo** - competition name (compo)
  - Link through `partyMusic`

#### Related Works
- **game** - related production or release (`gameLink`)
  - For music from games
  - Holds a zxProd in almost every case, but a release is allowed too, so the
    form offers both
- **inspired** - inspiration source (text)
  - Original composition or melody

#### Press
- **mentions** - press mentions (link `PRESS_TUNES`, role parent)

### Special Flags
- **isPlayable** - can be played in browser
  - Determined automatically by format
- **isRealtime** - created in real-time (at party)
- **denyPlaying** - deny playing

### Usage Statistics
- **plays** - number of plays

### Tags and Categorization
- **tagsText** - tags (text)
- **tagsAmount** - number of tags

### Voting and Comments
- **votes** - average rating
- **votesAmount** - number of votes
- **denyVoting** - deny voting
- **commentsAmount** - number of comments
- **denyComments** - deny comments

### Metadata
- **dateAdded** - date added
- **userId** - ID of user who added the element
- **zxTunesId** - ID in ZXTunes database

### Top Music Route
- `/music/top` is the dedicated top-music page.
- The page encapsulates the music browser with descending vote sorting and does not expose the generic sorting control.
- A chip strip above the browser narrows the top to one named subset, exactly as
  the top-graphics page does. The chosen subset is the page's optional trailing
  route segment (`/music/top/cover`), never an element id, and the pagination
  links keep it.
- The segments are `cover` (the "Cover" tag), `original` (everything without it),
  the sound types `ay`, `beeper`, `digitalay`, `samcoupe`, `turbosound` and `fm`
  (each a set of `formatGroup` values) and `games` (tunes with a linked game);
  the bare `/music/top` is the whole collection. `MusicCollectionFilter`
  (`ZxArt\Tunes`) owns the mapping.
- `GET /musiclist/` serves those subsets when `filter` and `limit` are given: the
  page then covers the whole tune collection and no catalogue root is involved.

### Integration with External Services
- **MP3 Storage**: `https://music.zxart.ee/music/` - MP3 files storage
- **ZXTunes**: integration through zxTunesId

### Constraints and Rules
1. File must be in one of supported chiptune formats
2. chipType and channels must correspond to file format
3. isPlayable is determined automatically by browser playback capability
4. isRealtime means track was created at competition in limited time
5. mp3Name is used to provide MP3 version for listening
6. internalTitle and internalAuthor are extracted from file metadata
7. Conversion (conversion*) is used to adapt track for different playback parameters
8. game links music to game (for soundtracks)
9. inspired points to original composition (for covers and remixes)
10. Turbosound (6 channels) requires special chip or emulation of two AY chips
11. OpenGraph metadata includes `og:audio` only when an MP3 file is available

### Page Metadata
`/tune-details/` carries the page metadata (`metadata`) like every other
entity core response, resolved by `PageMetadataService::getForPath('/tune/<id>')`.
The Angular page applies it through `PageMetadataService.applyEntityMetadata()`;
it makes no metadata request of its own.

