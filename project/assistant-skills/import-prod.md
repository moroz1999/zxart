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
- Add a release description only when the source explicitly provides a description of that specific release.
- Copy it verbatim without rewording; if it is available in several languages, use the English version.
- Do not invent a description yourself.
- Put the instructions into `instructions`, not into the description.
- Always add the source's pictures in the same plan: screens of the work as the production's `screenshots`, the cover or tape inlay as `inlays`, maps as `maps`. Look at a picture with `view_image` when unsure what it shows.
- An itch.io game page has no direct download links: call `list_itch_io_files`. Skip native builds (PC, Mac, Android) and files that are no release (manuals, maps, artwork). Each other file is a release of its own: its language, version and formats from the file's label; store the file with `set_release_itch_io_file`.
- One plan: new authors/groups, prod, its authors and pictures, then each release with its authors and its file.
