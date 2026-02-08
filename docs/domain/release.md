## zxRelease (Software Release)

### Purpose
Concrete release (version) of software production. Contains files specific to this version. Always linked to parent zxProd.

### Main Fields
- **title** - release title
- **version** - release version
- **year** - release year
- **description** - release description (HTML)
- **file** - main release file
- **fileName** - file name

### Relations with Other Entities

#### Parent Product
- **zxProd** - parent product (link `structure`, role child)
  - Each release must belong to one prod
  - Link through structural hierarchy

#### Authorship
- **authors** - authors with roles (code, graphics, music, etc.)
  - Can differ from prod authors (e.g., for ports)

#### Publishers
- **publishers** - release publishers (link `zxReleasePublishers`, role child)
  - Can differ from prod publishers

#### Compilations
- **compilations** - compilations that include this release (link `compilation`, role child)

### Technical Characteristics

#### Release Type
- **releaseType** - release type:
  - `tar` - TAR archive (for MB)
  - `trd` - TR-DOS disk
  - `tap` - TAP file
  - `z80` - Z80 snapshot
  - `sna` - SNA snapshot
  - `tzx` - TZX file
  - `scl` - SCL disk
  - `p` - ZX81 program
  - `o` - ZX81 program
  - `spg` - TSConf SPG
  - `img` - disk image
  - and other formats

#### Release Format
- **releaseFormat** - release format(s) (array)
  - Can contain multiple formats for one release
  - Stored in table `module_zxrelease_format`

#### Hardware Requirements
- **hardwareRequired** - required hardware (array)
  - Stored in table `module_zxrelease_hw_required`
  - Examples: ZX Spectrum 48K, 128K, Pentagon, ZX81, ZX80, TSConf, MB
  - Determines emulator launch capability

#### Languages
- **language** - interface languages (array)
  - Stored in table `zxitem_language`

### Files and Media
- **screenshotsSelector** - release screenshots
- **inlayFilesSelector** - inlay files (covers)
- **infoFilesSelector** - information files
- **adFilesSelector** - advertising materials

### Usage Statistics
- **downloads** - number of downloads
- **plays** - number of emulator launches

### Voting and Comments
- **votes** - average rating
- **votesAmount** - number of votes
- **denyVoting** - deny voting
- **commentsAmount** - number of comments
- **denyComments** - deny comments

### Metadata
- **dateAdded** - date added
- **userId** - ID of user who added the element
- **parsed** - flag that file was parsed

### Emulator Launch Capability
Determined by combination of:
1. **releaseType** - file type must be in runnable list
2. **hardwareRequired** - hardware must be supported by emulator

#### Launch Rules:
- **ZX Spectrum (USP)**: formats `trd`, `tap`, `z80`, `sna`, `tzx`, `scl`
- **ZX81**: formats `tzx`, `p`, `o` + ZX81 hardware
- **ZX80**: ZX80 hardware
- **TSConf**: formats `spg`, `img`, `trd`, `scl` + TSConf hardware
- **MB (Multiboard)**: format `tar` + MB hardware

### Constraints and Rules
1. Release must always have parent zxProd
2. Release contains concrete file, unlike abstract prod
3. One prod can have multiple releases (different versions, platforms, publishers)
4. Release can have its own authors and publishers, different from prod
5. Emulator launch capability is determined automatically by file type and hardware requirements
6. Release file can be parsed to extract metadata (parsed flag)
