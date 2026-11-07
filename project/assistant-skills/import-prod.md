---
name: import-prod
description: Adding a production from a page of another site.
---

- First identify the source's people: call `load_skill` with `find-authors` and follow it.
- Then call `load_skill` with `find-prod` and follow it. Same work: offer an update, not a new one.
- Create an author or group only if no author, group or alias of that name exists; say so in the plan.
- Find the party with `find_parties` (title and year).
- Pick a leaf category (e.g. Megademo), never a top-level section (e.g. Demoscene).
- Always add a release, even without a downloadable file; give it its year, authors and their roles.
- Collect the hardware carefully, also from the features the page mentions (AY, Kempston, 128K, Beta Disk…).
- Hardware is split between the production and its releases. The production holds only what every release needs. Each release MUST get in `hardware` what is unique to it: its machine and whatever only that version uses (e.g. a ZX81 .p release gets ZX81, a 128K version gets 128K). A work for several machines never puts them all on the production, or every release inherits all of them.
- Copy the source's description of the work into the production's `description`: exactly and in full, no rewording, no retelling, no cuts, nothing added of your own. If it is available in several languages, use the English version. Do not invent a description yourself.
- One text describing the work is the production's, even when it names a version or says what the version brings.
- A release gets a description only when the source describes that release apart from the work, e.g. the changes of each version in a list of several. Never put the work's description into a release.
- Put the instructions into `instructions`, not into the description.
- Always add the source's pictures in the same plan: screens of the work as the production's `screenshots`, the cover or tape inlay as `inlays`, maps as `maps`. Look at a picture with `view_image` when unsure what it shows.
- A release holds exactly one program file. Every downloadable file of the work (another format such as .tap and .sna, another language or version) is a release of its own, its file set with `set_release_file` (`set_release_itch_io_file` for itch.io). Never put a program file into a release's file lists: `screenshots`, `inlays`, `info` (instructions, manuals) and `ads` take pictures and documents only.
- Leave `releaseFormats` out for a release that gets a file: they are read from the file.
- An itch.io game page has no direct download links: call `list_itch_io_files`. Skip native builds (PC, Mac, Android) and files that are no release (manuals, maps, artwork). Each other file is a release of its own: its language and version from the file's label; store the file with `set_release_itch_io_file`.
- One plan: new authors/groups, prod, its authors and pictures, then each release with its authors and its file.
