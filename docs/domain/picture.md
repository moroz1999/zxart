## Picture (Graphics)

### Purpose
Graphic artwork for the ZX Spectrum, stored in one of the machine's native screen
formats (SCR, MC, MG and the rest). A picture is a standalone artistic work.

### Main Fields
- **title** — picture title
- **year** — year of creation
- **description** — description
- **image** — the picture file
- **originalName** — the file name it was uploaded under

The stored file is a native ZX Spectrum screen rather than a browser image
format, and its extension follows the format it was saved in, so nothing can
preview a freshly picked file before it is saved — only its name is shown, and
the thumbnail appears once the site has converted the stored screen. The
reference images beside it (inspiration sources, next image in a sequence) are
ordinary pictures of the archive.

### Technical Image Parameters

#### Type and Palette
- **type** — the screen format, which fixes the resolution and the colour model
- **palette** — the standard ZX Spectrum palette, or an extended one for the
  formats that carry more colours
- **border** — border colour (0–7)
- **rotation** — rotation of the image in degrees

#### Special Flags
- **isRealtime** — created live, in the limited time of a party competition
- **isFlickering** — uses flickering: colours mixed by switching frames quickly

### Relations with Other Entities

#### Authorship
- **author** — the picture's authors. Authorship decides where the picture is
  filed and which author galleries it appears in, so it is never left empty: at
  least one author must be given, and a picture submitted without one is
  credited to the "unknown author".
- **originalAuthor** — the authors of the original, for ports and remakes

#### Party (Competitions)
- **party** — the demoparty the picture was released at
- **partyplace** — the place it took in the competition
- **compo** — the competition it ran in

#### Related Works
- **prod** — the production or release the picture belongs to: loading screens
  and in-game graphics. It is a production in almost every case, but a single
  release may be named instead, so both can be picked. Picture cards show the
  title of that production or release.
- **inspired**, **inspired2** — the works that inspired the author
- **sequence** — the next picture, for works published as a series

#### Executable File
- **exeFile**, **exeFileName** — the runnable program a picture is part of

#### Press
- **mentions** — the press articles that mention the picture

### Tags and Categorization
- **tagsText** — the picture's tags
- **tagsAmount** — how many tags it carries

Two tags carry meaning beyond description: "Loading Screen" marks a loading
screen and "Game Graphics" marks graphics taken from a running program.

### Voting and Statistics
- **votes**, **votesAmount** — average rating and how many people voted
- **denyVoting** — voting is closed for this picture
- **commentsAmount**, **denyComments** — comments and whether they are closed
- **views** — how often the picture has been viewed

### Metadata
- **dateAdded** — when it was added
- **userId** — the visitor who added it
- **artCityId** — the city, for Art City projects

### Top Graphics
The top-graphics page ranks the whole collection by rating, highest first, and
offers no other ordering.

A strip of chips above it narrows the top to one named subset. The subset is
part of the page address rather than a hidden setting, so a narrowed top can be
linked to and shared, and paging through it keeps the subset. The subsets are:

- game loading screens, and graphics taken from running programs — the two
  special tags above
- originals — the works tagged as nobody's copy
- gigascreen — every format built on gigascreen frame mixing
- SAM Coupé, and ZX Spectrum Next — each machine's own screen formats

Without a subset the page ranks the entire collection.

### Viewing Settings
How a picture is rendered — the colour mode, whether the border is drawn, and
whether hidden parts of the screen are shown — is the visitor's own choice and
follows them: anonymous visitors keep it in their browser, and a logged-in
visitor has it stored with their account and applied on every device.

The zoom of the detail viewer is stored the same way, but it changes the layout
rather than the picture, so it is remembered separately. The viewer only offers
the zooms that fit the screen in front of the visitor: a zoom too large for the
current screen is shown at the widest that fits, without overwriting what the
account has stored, so the default reads as 3× on a desktop and as full width on
a tablet or phone.

### Constraints and Rules
1. The image must be in one of the native ZX Spectrum formats.
2. The palette and the format must match each other.
3. The border colour is one of the eight standard ZX Spectrum colours.
4. Rotation exists to correct the orientation of the stored screen.
5. Original authors are named when the picture is a port or a remake.
6. A missing year — empty, not a number, or earlier than 1983 — is filled in
   when the picture is saved: first from the party it was released at, then from
   the production it belongs to. A source that has no year of its own is skipped
   rather than applied, so it cannot undo the year the other one supplied. The
   same rule holds for tunes.

How it is built: [../features/picture.md](../features/picture.md)
