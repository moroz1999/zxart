## Authors and Groups

### Author

#### Purpose
Someone who made works for the ZX Spectrum — an artist, a musician, a
programmer. An author is known by a real name, a nickname, or both, and must
have at least one of them.

#### Main Fields
- **realName** — the person's real name
- **nickname** — the name they are known by in the scene
- **wikiLink** — their page on SpeccyWiki
- **artCityId** — their identity on Artcity
- **deny3a** — hides the external links the author does not want shown

Names are recorded per interface language, so an author written in Cyrillic can
also be written in Latin script.

#### Works
An author is linked to the pictures, tunes, productions and releases they made.
For software the link also records what they did — code, graphics, music,
design, testing and so on — and one person may have several roles in the same
work.

An author who published somebody else's work counts as its publisher, and their
page lists those productions under that role alongside the ones they made.

#### Original authorship
A picture that is a port or a remake names the author of the original separately
from the author of the port.

### Author alias

An alias is another name the same author is known by: a different spelling, an
earlier nickname, another pseudonym. One author may have many.

Works recorded against an alias belong to the author behind it, and are counted
and credited there, so an author's page is complete whichever name a work was
filed under. An alias has a page of its own showing what was attributed to that
name, while everything about the person — where they live, their account, their
links — comes from the author.

### Group

#### Purpose
A group of authors: a demoscene group, a company, a development team, a
publisher.

#### Main Fields
- **title** — the group's name, recorded per interface language
- **wikiLink** — its page on SpeccyWiki

#### Works
A group is linked to a work either as its developer — it made the work — or as
its publisher — it released somebody else's. The same group can be both, on
different works or even on the same one.

A group's works are one list with a filter by that role: everything, what it
made, what it published, and the hacked releases it put out — adaptations,
localizations, bugfixes, mods and cracks. A role the group has nothing under is
not offered, and the combined view is offered only when more than one role
remains. A work linked under two roles appears once.

The list can also be narrowed by the category of the productions in it,
including everything filed beneath the chosen category.

### Group alias

Another name the same group is known by: a different spelling, an abbreviation,
an earlier name, a name in another language. One group may have many.

### Authors and groups together

#### Membership
An author can belong to several groups, a group can have many members, and
membership has a beginning and an end, so it reflects who was in a group when.

#### Collective and individual credit
A work made by a group names the group, and may also name the individual members
with their roles, so both the collective and the personal credit are recorded.

#### Collaborators
An author's page shows who they worked with: the people credited on the same
pictures, tunes, productions and releases, and the groups connected to those
works. Work recorded under an alias counts towards the author behind it. A
group's own members are not listed as its collaborators — they are its roster.

### The author page
An author's page opens on a summary of their best work, and then offers each
kind of work they made, who they worked with, and the discussion of their work.

Works can be filtered by role for software and by sound type for music; only the
roles and sound types that author actually has are offered. Sorted by year, a
work whose year is unknown is shown as unknown rather than as the year zero.

The discussion collects the votes and comments left on all of the author's
works, followed by the comments left on the author themselves.

### Browsing authors and groups
Authors are browsed by first letter, and separately for graphics and for music,
so an artist and a musician are looked for in the section they belong to. With
no letter chosen and nothing searched for, the listing shows the most recently
added authors rather than the whole alphabet.

Graphics and music also have a dashboard of their own: who has been active over a
chosen recent period, the fifty highest-rated authors of that kind, and the
hundred most recently added. Activity recorded under an alias counts towards the
author behind it.

Groups are browsed the same way.

Registered visitors can add an author or a group from those listings, and add an
alias from an author's page.

### Merging and converting
Two records of the same person or group can be merged: the one that was picked
is absorbed into the one whose page the merge was started from, and it can either
disappear entirely or stay behind as an alias.

Records can also be converted — an author into a group, a group into an author,
an alias into a record of its own — for cases where something was filed as the
wrong kind of thing.

### Claimed authorship
A visitor can claim to be an author, and once that is approved they may edit
their own works.

The claim always follows what happens to the author: it is dropped when the
author is deleted or converted, and moved to the surviving record when authors
are merged, so nobody is left claiming something that no longer exists.

### Constraints and Rules

#### Authors
1. An author has a real name, a nickname, or both.
2. An alias always belongs to a main author.
3. The original author is recorded only for ports and adaptations.
4. One author may hold several roles in one work.

#### Groups
1. A group has a name.
2. An alias always belongs to a main group.
3. A group may be developer and publisher at once.
4. A release's publishers may differ from its production's.

#### Aliases
1. An alias always points at a main record.
2. Aliases exist for finding and for normalising imported data.
3. The main name is what is normally displayed.

How it is built: [../features/authors-groups.md](../features/authors-groups.md)
