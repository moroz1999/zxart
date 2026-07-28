## zxPicture (Graphics)

### Purpose
Graphic artwork for ZX Spectrum - pictures in native formats (SCR, MC, MG, etc.). Represents a standalone artistic work.

### Main Fields
- **title** - picture title
- **year** - year of creation
- **description** - description (pre-formatted text)
- **image** - image file ID
- **originalName** - original file name

The stored file is a native ZX Spectrum screen, not a browser image format, and
its extension follows the format it was saved in. So the edit form accepts any
file in that field and shows no local preview of a picked one (only its name),
while the thumbnail of the stored file comes from the element's converted image
URL (`getImageUrl()`) — the `adminImage` preset cannot render it. The reference
images beside it (`inspired`, `inspired2`, `sequence`) are ordinary images.

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
  - Authorship drives the picture's placement and author pages, so it is never
    left empty: the Angular form requires at least one author, and the backend
    falls back to the "unknown author" element when none is submitted.
- **originalAuthor** - original authors (array of IDs)
  - Used for ported/remade works

#### Party (Competitions)
- **party** - party ID (demoparty, competition)
- **partyplace** - place in competition
- **compo** - competition name (compo)
  - Link through `partyPicture`

#### Related Works
- **game** - related production or release (`gameLink`)
  - For loading screens and game graphics
  - Holds a zxProd in almost every case, but a release is allowed too, so the
    form offers both
  - Picture list cards display the linked zxProd or zxRelease title when available
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
- `"Game Graphics"` - game graphics

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

### Top Graphics Route
- `/pictures/top` is the dedicated top-graphics page.
- Author picture lists and picture view logging use the `/pictures-data/` JSON endpoint.
- The page encapsulates the picture browser with descending vote sorting and does not expose the generic sorting control.
- A chip strip above the browser narrows the top to a single tag: all graphics,
  the "Loading Screen" tag (game loading screens) or the "Game Graphics" tag.
  The selected tag id lives in the `tag` query param and is preserved by the
  pagination links.

### Picture List Endpoint
`GET /picturelist/` (`ZxArt\Controllers\Picturelist`) serves three shapes:
- `limit` present — a paged, sorted slice of the whole picture collection.
  The optional `tagId` narrows it to the pictures carrying that tag. Sortable by
  `title`, `date`, `year`, `votes`; the response is `{total, items}`.
- `action=related&pictureId=…` — related pictures, with `kind` selecting the
  relation (`author`, `tags`, `prod`).
- `elementId=…` without `limit` — the pictures of a single element (release,
  party compo, …), optionally narrowed by `compoType`.

### Viewer Settings
`PictureSettingsService` owns the picture-viewing settings and persists each one
as a user preference through `UserPreferencesService`, so anonymous visitors keep
them in localStorage and logged-in users have them fetched from and written to
the backend.

- Render settings (`picture_mode`, `picture_border`, `picture_hidden`) are
  exposed as `settings` and feed `PictureUrlBuilderService`.
- The details viewer zoom (`picture_scale`: `1`, `2`, `3`, `wide`) is exposed
  separately as `scale`, because it changes the layout rather than the image URL.

The stored zoom is shared across devices, while the viewer only offers the zooms
that fit the current screen (`SCALES_BY_DEVICE`). A stored value too large for
the screen falls back to `wide` for display without being overwritten, so the
default of `3` renders as 3× on desktop and as `wide` on tablet and phone.

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
11. A missing year (empty, non-numeric or earlier than 1983) is filled in on save by `ZxArtItem::updateYear()`, shared by every zx item: first from the linked party, then from the linked release. A source without a year of its own is skipped, so it cannot overwrite the value the other one provided. Called from `publicReceive` and from the batch upload actions.
