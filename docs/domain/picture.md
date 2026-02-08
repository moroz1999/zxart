## zxPicture (Graphics)

### Purpose
Graphic artwork for ZX Spectrum - pictures in native formats (SCR, MC, MG, etc.). Represents a standalone artistic work.

### Main Fields
- **title** - picture title
- **year** - year of creation
- **description** - description (pre-formatted text)
- **image** - image file ID
- **originalName** - original file name

### Technical Image Parameters

#### Type and Palette
- **type** - image type:
  - Standard ZX Spectrum formats (SCR, MC, MG, etc.)
  - Defines resolution and color model
- **palette** - palette:
  - Standard ZX Spectrum palette
  - Extended palettes (for formats with more colors)
- **border** - border color (0-7)
- **rotation** - image rotation (degrees)

#### Special Flags
- **isRealtime** - created in real-time (at party)
- **isFlickering** - uses flickering effect
  - Technique to increase number of colors through rapid frame switching

### Relations with Other Entities

#### Authorship
- **author** - picture authors (array of IDs)
- **originalAuthor** - original authors (array of IDs)
  - Used for ported/remade works

#### Party (Competitions)
- **party** - party ID (demoparty, competition)
- **partyplace** - place in competition
- **compo** - competition name (compo)
  - Link through `partyPicture`

#### Related Works
- **game** - related game (zxProd ID)
  - For loading screens and game graphics
- **inspired** - inspiration source (image file ID)
  - Original work that inspired the author
- **inspired2** - second inspiration source
- **sequence** - next image in sequence
  - For picture series

#### Executable File
- **exeFile** - executable file (file ID)
- **exeFileName** - executable file name
  - For pictures that are part of a program

#### Press
- **mentions** - press mentions (link `PRESS_PICTURES`, role parent)

### Tags and Categorization
- **tagsText** - tags (text)
- **tagsAmount** - number of tags

#### Special Tags
- `"Loading Screen"` - game loading screen
- `"Game graphics"` - game graphics

### Voting and Statistics
- **votes** - average rating
- **votesAmount** - number of votes
- **denyVoting** - deny voting
- **commentsAmount** - number of comments
- **denyComments** - deny comments
- **views** - number of views

### Metadata
- **dateAdded** - date added
- **userId** - ID of user who added the element
- **artCityId** - city ID (for Art City projects)

### Constraints and Rules
1. Image must be in one of native ZX Spectrum formats
2. Palette and type must correspond to each other
3. isFlickering indicates use of flickering technique to increase colors
4. isRealtime means work was created at competition in limited time
5. originalAuthor is used when picture is a port/remake
6. game links picture to game (for loading screens and game graphics)
7. inspired/inspired2 point to inspiration sources (references)
8. sequence is used to create series of related pictures
9. Border defines frame color around image (0-7 standard ZX Spectrum colors)
10. Rotation allows correcting image orientation
