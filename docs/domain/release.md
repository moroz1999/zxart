## Release

### Purpose
A concrete published version of a production, holding the files that version
actually consists of. A release always belongs to one production; a production
with no release is a work the archive knows of but does not hold.

### Main Fields
- **title** — release title
- **version** — which version it is
- **year** — the year it was published
- **description** — description
- **file**, **fileName** — the release file itself

### Relations with Other Entities

#### Parent Production
Every release belongs to exactly one production. Unlike the other works there is
no sensible fallback for a missing one, so a release without a production is
refused rather than filed somewhere arbitrary.

#### Authorship
A release names its own authors with their roles — code, graphics, music and so
on. They may differ from the production's, which is what a port or a conversion
is.

#### Publishers
A release names its own publishers, which may likewise differ from the
production's.

#### Compilations
A release records the compilations that include it.

### Technical Characteristics

#### Release Type
The kind of file the release is: a TR-DOS disk, a TAP or TZX tape, a Z80 or SNA
snapshot, an SCL disk, a ZX81 program, a TSConf SPG, a disk image, and so on.

#### Release Format
A release can be published in more than one format at once.

#### Hardware Requirements
A release states only the hardware it needs **beyond** what its production
already states, so the same requirements are not repeated on every release. What
actually decides behaviour — whether it can be launched, which files are
playable — is the two sets together.

Hardware is also filled in from the release's file format whenever it is saved,
so an obviously implied requirement is never missing.

#### Languages
The interface languages the release is available in.

### Files and Media
Besides the release file itself a release carries screenshots, inlays and covers,
information files and advertising material. Covers are grouped by kind and shown
one section per kind.

The contents of the release archive are listed, and single files inside it can be
previewed or downloaded on their own — but only when the release itself may be
downloaded at all. Tape files inside it can also be played back as sound.

### Downloading
Whether a release may be downloaded follows the legal status of its production:

- A production that is forbidden, or currently on sale, is not downloadable.
- A demo version is always downloadable, whatever the production's status.
- A production explicitly marked as freely downloadable is downloadable.
- A registered visitor may download an otherwise restricted release when the
  production is known to be more than twenty years old — but never when it is on
  sale.

A release nobody may download offers no download at all, and neither its file
nor the contents of its archive are listed. A production that is on sale or asks
for donations offers a link to buy or to donate instead.

### Running in the browser
A release can be launched in an emulator when its file type is one that can be
started and the hardware it needs — its own plus its production's — can be
emulated:

- **ZX Spectrum**: TRD, TAP, Z80, SNA, TZX, SCL
- **ZX81**: TZX, P, O, on ZX81 hardware
- **ZX80**: on ZX80 hardware
- **TSConf**: SPG, IMG, TRD, SCL, on TSConf hardware
- **Multiboard**: TAR, on Multiboard hardware
- **Timex**: TAP, TZX, Z80, SNA, SZX, on Timex TC2048 or TC2068 hardware. A
  cartridge cannot be launched, so a release distributed as one is not playable.

Hardware the emulators cannot reproduce — General Sound — makes a release
unplayable only when it is the only sound the release has. Where the release also
names another sound chip it can still be heard, so it stays playable and only the
General Sound track is missing.

The emulator window names the emulator it runs and links to its home page.

A screenshot taken while playing is filed with whatever was launched: with the
release when launched from the release, and with the production when launched
from the production's list of releases.

### Usage Statistics
- **downloads** — how often the release has been downloaded
- **plays** — how often it has been launched in an emulator

### Voting and Comments
- **votes**, **votesAmount** — average rating and how many people voted
- **denyVoting** — voting is closed for this release
- **commentsAmount**, **denyComments** — comments and whether they are closed

### Metadata
- **dateAdded** — when it was added
- **userId** — the visitor who added it
- **parsed** — whether the release file has been read into its contents

### Cloning
A release can be copied under the same production, carrying over its hardware,
languages, publishers and authorship, and starting its download and play counts
from zero. It is how a second format or a second publisher of the same version is
recorded without typing everything again. Whoever added a release may clone it.

### Constraints and Rules
1. A release always belongs to a production.
2. A release holds a concrete file; the production it belongs to is the abstract
   work.
3. One production may have many releases — versions, platforms, publishers.
4. A release may have its own authors and publishers, different from the
   production's.
5. Whether a release can be launched follows from its file type and hardware.

How it is built: [../features/release.md](../features/release.md)
