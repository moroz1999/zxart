## zxProd (Software Production)

### Purpose
Software production for ZX Spectrum - games, demos, utilities and other software. Represents an abstract work that can have multiple concrete releases (zxRelease).

### Main Fields
- **title** - production title
- **altTitle** - alternative title
- **year** - year of creation
- **description** - description (HTML)
- **instructions** - instructions (HTML)
- **youtubeId** - YouTube video ID
- **legalStatus** - legal distribution status:
  - `unknown` - unknown
  - `allowed` - allowed
  - `allowedzxart` - allowed only on zxart
  - `forbidden` - forbidden
  - `forbiddenzxart` - forbidden on zxart
  - `insales` - in sales
  - `mia` - missing in action (lost)
  - `unreleased` - not released
  - `recovered` - recovered
  - `donationware` - donationware
- **externalLink** - external link
- **tagsText** - tags (text)
- **compo** - competition name (compo)
- **language** - interface languages (array)

### Relations with Other Entities

#### Authorship
- **authors** - authors with roles (code, graphics, music, etc.)

#### Groups and Publishers
- **publishers** - publishers (link `zxProdPublishers`, role child)
- **groups** - developer groups (link `zxProdGroups`, role child)

#### Categories
- **categories** - production categories (array of IDs)
  - Define software type: games, demos, utilities, etc.
  - Special categories for compilations

#### Production Hierarchy
- **compilationItems** - compilation items (link `compilation`, role parent)
  - If prod is a compilation, contains list of included products
- **compilations** - compilations that include this prod (link `compilation`, role child)
- **seriesProds** - products in series (link `series`, role parent)
  - If prod is a series, contains list of series products
- **series** - series that include this prod (link `series`, role child)

#### Party (Competitions)
- **party** - party ID (demoparty, competition)
- **partyplace** - place in competition
- **compo** - competition name (compo)

#### Press and Articles
- **articles** - articles about the product (link `prodArticle`, role parent)
- **mentions** - press mentions (link `PRESS_SOFTWARE`, role parent)

#### Files and Media
- **connectedFile** - main file
- **inlayFilesSelector** - inlay files (covers)
- **mapFilesSelector** - map files
- **rzx** - playthrough recording files (RZX)
- **screenshots** - screenshots
- **bestPictures** - best pictures

#### Child Elements
- **releases** - product releases (zxRelease, link `structure`)
  - Each prod can have multiple releases
  - Flag **releasesOnly** - show only releases (hide the prod itself)

### Voting and Comments
- **votes** - average rating
- **votesAmount** - number of votes
- **denyVoting** - deny voting
- **commentsAmount** - number of comments
- **denyComments** - deny comments

### Metadata
- **dateAdded** - date added
- **userId** - ID of user who added the element

### Special Operations
- **joinAndDelete** - merge and delete products
- **splitData** - data for splitting product
- **aiRestartSeo** - restart AI for SEO
- **aiRestartIntro** - restart AI for intro
- **aiRestartCategories** - restart AI for categories

### Constraints and Rules
1. Prod is an abstraction - concrete files are stored in releases
2. Prod can be a compilation (contains other prods) or be included in compilations
3. Prod can be a series (contains other prods) or be included in series
4. Prod cannot simultaneously be a compilation and be included in a compilation (cyclic links are forbidden)
5. LegalStatus determines file distribution possibility
6. If releasesOnly flag is set, prod is hidden, only its releases are shown
