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

PNG, GIF and JPEG files are not accepted as native screens, even when renamed.
BMP is accepted only for ZX Evolution screens and must use no more than 16
colours. Extension matching is case-insensitive. Formats with a fixed payload
size accept only the byte counts listed below; variable-size formats still
require one of their listed native extensions.

| Picture format | Allowed extensions | Allowed file sizes, bytes |
|---|---|---:|
| Standard | `.scr`, `.bin`, `.c` | 6912 |
| Flash | `.scr` | 6912 |
| Monochrome | `.bin`, `.scr`, `.c` | 6144 |
| Tricolor | `.rgb`, `.3`, `.c` | 18432 |
| Multicolor | `.ifl`, `.c` | 9216 |
| Multicolor 4 | `.ifl` | 7680 |
| Timex 81 | `.scr`, `.mlt` | 12288 |
| Timex high resolution | `.scr` | 12289 |
| Timex high-resolution graphics | `.hrg` | 24578 |
| SAM mode 2 | `.ss2` | 12288 |
| SAM mode 3 | `.ss3`, `.ssx` | 24580, 24617 |
| SAM mode 4 | `.ss4`, `.ssx`, `.scr` | 24592, 24617, 24681 |
| BSC | `.bsc`, `.scr` | 11136 |
| BSP | `.bsp` | Variable |
| BMC4 | `.bmc4` | 11904 |
| MLT | `.mlt` | 12288 |
| MC | `.mc` | 12288 |
| Gigascreen | `.img`, `.gsc`, `.scr`, `.c` | 13824 |
| LCE | `.lce` | 13824 |
| MultiArtist 1 | `.mg1`, `.c` | 19456 |
| MultiArtist 2 | `.mg2`, `.c` | 18688 |
| MultiArtist 4 | `.mg4` | 15616 |
| MultiArtist 8 | `.mg8`, `.c` | 14080 |
| Attributes | `.atr`, `.attr`, `.c`, `.scr` | 768 |
| Low-resolution gigascreen | `.hlr`, `.c` | 1628 |
| Stellar | `.stl`, `.c`, `.bin` | 3072 |
| CHR$ | `.chr$`, `.ch$`, `.$c` | Variable |
| ULAplus | `.scr` | 6976 |
| ZX Evolution | `.bmp` | Variable |
| SXG | `.sxg` | Variable |
| GRF | `.grf` | Variable |
| NXI | `.nxi` | 49664 |
| SL2 | `.sl2` | 49152, 49280, 49408, 49664, 81920, 81936, 81952, 82176, 82432 |
| SPECASCII | `.specscii`, `.c` | Variable |
| S80 | `.s80` | 768 |
| S81 | `.s81` | 768 |
| SCA | `.sca` | Variable |

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
- **sequence** — the recorded drawing stages: an animation of the picture being drawn

#### Executable File
- **exeFile**, **exeFileName** — the runnable program a picture is part of

#### Downloads
A picture page offers the original screen file, PC renders at several zooms and
a print version. Its materials are offered next to them: the executable file and
the drawing stages, each under its own file name.

#### Press
- **mentions** — the press articles that mention the picture

### Tags and Categorization
- **tags** — the picture's tags
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
offers no other ordering. It shows a hundred works per page.

A strip of chips above it narrows the top to one named subset. The subset is
part of the page address rather than a hidden setting, so a narrowed top can be
linked to and shared, and paging through it keeps the subset. The subsets are:

- game loading screens, and graphics taken from running programs — the two
  special tags above
- originals — the works tagged as nobody's copy
- gigascreen — every format built on gigascreen frame mixing
- SAM Coupé, and ZX Spectrum Next — each machine's own screen formats

Without a subset the page ranks the entire collection.

### Fullscreen Gallery
Any list of pictures opens its pictures in a fullscreen gallery over the whole
screen. The picture is shown at a whole multiple of its own size, the largest
that fits the screen with a small margin, so every pixel of the original stays a
sharp square; a picture too large for the screen even at its own size is shrunk
to fit. A loupe magnifies the region under the pointer, the wheel changes its
magnification — the same as on the picture's own page. Beside the picture —
below it on a phone — the gallery names it, its authors and its format, shows
its rating, and lets the visitor vote and add it to favourites without leaving
the gallery. Pictures are turned with the arrows, the arrow keys, a swipe or the
thumbnails; following the picture's or an author's link closes the gallery.
Screenshots, maps and covers of software open in the same gallery, without the
info.

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
1. The image must meet the extension and size rules of its selected native ZX
   Spectrum format.
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
