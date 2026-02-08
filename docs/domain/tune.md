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
- **formatGroup** - format group
  - Groups similar formats for filtering

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
- **embedCode** - player embed code

### Relations with Other Entities

#### Authorship
- **author** - track authors (array of IDs)

#### Party (Competitions)
- **party** - party ID (demoparty, competition)
- **partyplace** - place in competition
- **compo** - competition name (compo)
  - Link through `partyMusic`

#### Related Works
- **game** - related game (zxProd ID)
  - For music from games
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
