## Project Domain
Project is an archive of ZX Spectrum related art objects. Graphics, music, software and related entities - authors, authors groups and so on.
Read detailed files only WHEN you get specific domain questions.

## Entities

### picture
ZX Spectrum picture entity. Contains link to picture file in one of native formats and a lot of metadata.
Year, authors, tags, competitions with places, linked comments.
Technical parameters: type, palette, border, rotation. Special flags: isRealtime, isFlickering.
Detailed logic and structure: [domain/picture.md](domain/picture.md)

### tune (music)
ZX Spectrum tune entity. Chiptune in various tracker and chip formats.
Contains technical parameters: chipType, channels, frequency, format. Can be played in browser.
Detailed logic and structure: [domain/tune.md](domain/tune.md)
Music player and radio behavior: [domain/music-player-radio.md](domain/music-player-radio.md)

### prod
A piece of software. The abstract work, which can have many releases.
Contains metadata, categories, legal status, links to compilations/series, party participation.
Detailed logic and structure: [domain/prod.md](domain/prod.md)

### release
A published version of a production, holding its actual files and requirements.
Release type, hardware, format. Always belongs to its production.
Detailed logic and structure: [domain/release.md](domain/release.md)

### hardware
Machines, storage, operating systems, sound, controllers and expansions a production runs on.
Editable catalog with localized names; productions carry the shared set, releases only their deviations.
Includes the rules that derive hardware from a release's file format.
Detailed logic and structure: [domain/hardware.md](domain/hardware.md)

### press article
One article of a disk magazine. Always belongs to the issue it was published in.
Links the authors, people, groups, software, graphics, music and parties it mentions.
Detailed logic and structure: [domain/press.md](domain/press.md)

### party
A demoparty. Holds competitions for graphics, music and software.
Location, year, website. Organizes works by compo and place.
Detailed logic and structure: [domain/party.md](domain/party.md)

### category
Classifies productions by type: games, demos, utilities, applications.
Special categories for compilations. Supports hierarchy and localization.
Detailed logic and structure: [domain/category.md](domain/category.md)

### comment
A visitor's comment on a work or another entity, or a reply to another comment.
Detailed logic and structure: [domain/comments.md](domain/comments.md)

### author
Author of zx art item. Can have real name and/or nickname. Localized.
Links to works with roles (for prods: code, graphics, music, etc.).
Detailed logic and structure: [domain/authors-groups.md](domain/authors-groups.md)

### author alias
Author alias. Stores author nickname variation.
Detailed logic and structure: [domain/authors-groups.md](domain/authors-groups.md)

### group
Authors group. Teams, companies, demoscene groups.
Can be developer (groups) or publisher. Localized.
Detailed logic and structure: [domain/authors-groups.md](domain/authors-groups.md)

### group alias
Group alias. Stores group name variation and abbreviations.
Detailed logic and structure: [domain/authors-groups.md](domain/authors-groups.md)

## Features

### firstpage (main page)
A main page each visitor arranges themselves from content blocks.
Detailed logic and structure: [domain/firstpage.md](domain/firstpage.md)

### voting
Shared voting for ZX art items and comments.
Detailed logic and structure: [domain/voting.md](domain/voting.md)

### search
Full search across the archive, quick search in the header, and the detailed graphics and music searches.
Detailed logic and structure: [domain/search.md](domain/search.md)

### feedback form
Fixed name/email/message contact form. Sends an email only (no storage).
Detailed logic and structure: [domain/feedback.md](domain/feedback.md)

### geo section
Interactive map section for authors, groups, and demoparties grouped by country and city.
Detailed logic and structure: [domain/geo.md](domain/geo.md)

### statistics section
Dashboard of collection counts by year, daily activity, distributions and most active members.
Detailed logic and structure: [domain/stats.md](domain/stats.md)

### tags
Tag clouds and selected-tag browsers for graphics, music, and software.
Detailed logic and structure: [domain/tags.md](domain/tags.md)

### static content pages
The About section's editorial pages: introduction, FAQ, support and API documentation.
Detailed logic and structure: [domain/content.md](domain/content.md)

### interface language
Every visitor reads the site in the language they picked, and the choice follows
their account across devices.
How it is built: [features/language-auth.md](features/language-auth.md)

### playlists
Personal collections of works owned by one visitor.
Detailed logic and structure: [domain/playlists.md](domain/playlists.md)

### entity deletion
Deleting an entity from its own edit form, behind a confirmation, and only for those allowed to.
Detailed logic and structure: [domain/entity-deletion.md](domain/entity-deletion.md)

### entity descriptions
How the descriptions of works are written and shown, including fixed-width scene text.
Detailed logic and structure: [domain/descriptions.md](domain/descriptions.md)

### social posting
Telegram announcements of newly added pictures, tunes, productions and releases.
Detailed logic and structure: [domain/social-posting.md](domain/social-posting.md)
