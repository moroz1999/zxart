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
- One plan: new authors/groups, prod, its authors and files, then each release with its authors and files.
