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
Software production entity. Abstract product that can have multiple releases.
Contains metadata, categories, legal status, links to compilations/series, party participation.
Detailed logic and structure: [domain/prod.md](domain/prod.md)

### release
Unique release (version) of prod. Contains actual files and technical requirements.
Release type, hardware requirements, format. Always belongs to parent prod.
Detailed logic and structure: [domain/release.md](domain/release.md)

### party
Demoparty or competition event. Contains competitions (compos) for pictures, music, and prods.
Location, year, website. Organizes works by compo and place.
Detailed logic and structure: [domain/party.md](domain/party.md)

### category
Category of zxProds. Classifies software by type: games, demos, utilities, applications.
Special categories for compilations. Supports hierarchy and localization.
Detailed logic and structure: [domain/category.md](domain/category.md)

### comment
User comment. Can be left to one of zx art items.
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
Configurable modular main page. Users can arrange, enable/disable, and configure content modules.
Detailed logic and structure: [domain/firstpage.md](domain/firstpage.md)
