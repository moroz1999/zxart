## Production

### Purpose
A piece of software for the ZX Spectrum — a game, a demo, a utility, a magazine
issue. A production is the abstract work; the files it actually consists of live
in its releases, and one production may have many.

### Main Fields
- **title**, **altTitle** — the work's name, and the other name it is known by
- **year** — the year it was made
- **description** — description
- **instructions** — how to play or use it
- **youtubeId** — a video of it
- **tagsText** — its tags
- **language** — the interface languages it is available in
- **externalLink** — the work's own page elsewhere

### Legal status
A production records whether it may be distributed at all, and that decides
whether its releases can be downloaded:

- **unknown** — nothing is known about it
- **allowed** — freely distributable; **allowedzxart** — allowed here in
  particular
- **forbidden** — not distributable; **forbiddenzxart** — not allowed here in
  particular
- **insales** — currently sold, so never downloadable here
- **mia** — known to exist but lost
- **unreleased** — never published
- **recovered** — lost and found again
- **donationware** — free, but the authors ask for support

A production that is sold or asks for donations offers a button to buy or donate
rather than only a plain link to its site.

### Relations with Other Entities

#### Authorship
The people who made it, each with the role they had — code, graphics, music and
so on.

#### Groups and Publishers
The groups that developed it and the publishers that released it. Either may
differ from those of an individual release.

#### Hardware
A production states the hardware shared by all its releases, so the same
requirements are not repeated on every one of them. What is shown about the work
as a whole is that set together with everything its releases add.

#### Categories
The categories the production belongs to. Categories are what the software
section is browsed by, so a production is never without one: at least one must be
given, and a production submitted without any is filed under miscellaneous.

#### Compilations and series
A production may be a compilation, listing the works it collects, or be part of
one. It may likewise be a series, listing its parts, or be part of one. A
production cannot be both a compilation and part of that same compilation.

#### Party
The party it was released at, the compo it ran in and the place it took.

#### Press
The articles written under it, and separately the articles elsewhere that merely
mention it — a magazine's own contents and its coverage of other people's work
are different things and are shown apart.

#### Files and Media
Screenshots, covers and inlays, maps, and recorded playthroughs. Covers of a
production include the covers and advertising material of its releases, so
everything printed for the work is in one place.

#### Releases
The concrete published versions. A production can be marked as showing only its
releases, when the production itself is nothing more than their common heading.

### Voting and Comments
- **votes**, **votesAmount** — average rating and how many people voted
- **denyVoting** — voting is closed for this production
- **commentsAmount**, **denyComments** — comments and whether they are closed

### Metadata
- **dateAdded** — when it was added
- **userId** — the visitor who added it

Downloads and plays are counted on releases; a production shows the totals of
its own, since it holds no files itself.

### Special Operations
Two productions that turn out to be the same work can be merged, and one
production that turns out to be several can be split. The site can also be asked
to generate a production's summary text, its introduction and its categories
again.

### Browsing productions
The software section is one page, filtered by category, year, hardware, language,
legal status, format, type, first letter, tags and country, and ordered as the
visitor chooses. Every filter is part of the address, so a filtered catalogue can
be linked to and shared.

Browsing a category makes that category the subject of the page: it names the
heading and the document title, and the trail shows the chain it sits in.

Registered visitors can add productions in bulk from the catalogue, and from an
author's, group's or party's page — in which case the works are attached to
whatever they were added from, and the shared fields are prefilled from it. The
same applies to adding pictures and music, to adding releases to a production,
and to adding press articles under one.

### Constraints and Rules
1. A production is an abstraction; concrete files live in its releases.
2. A production may collect other productions or be collected by them, and the
   same for series — but never both ways round with the same work.
3. The legal status decides whether files may be distributed.
4. A production marked as releases-only is not shown itself; only its releases
   are.

How it is built: [../features/prod.md](../features/prod.md)
