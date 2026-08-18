# Hardware

The machines, storage, operating systems, sound chips, controllers and expansions
a work runs on.

## The catalogue
Hardware is an editable catalogue rather than a fixed list: each entry has a
code, the group it belongs to, where it sits in that group, and a name and short
name in every interface language. Only privileged visitors may edit it, and the
names shown anywhere on the site come from it — nothing invents its own.

## Who states what
A production states the hardware shared by all its releases. A release states
only where it differs. That way the common requirements are recorded once
instead of on every release of the same work.

## What each page shows
A **release** is shown what it actually runs on: what it states itself, plus
what it takes from its production. Most releases state nothing of their own, so
showing only their own set would leave them blank. The release's own page is the
one place that separates the two, and only visually — the production's
requirements plainly, the release's own highlighted.

A **production** is shown the set it states itself, not everything any of its
releases ever needed. A production is described by what its releases have in
common; folding in every variant would put a General Sound soundtrack and a
microdrive edition onto the card of what is plainly a 48K game, which says less
about it rather than more. The variants stay where they belong: on the releases,
as the deviations they are.

Searching works the other way round, deliberately. The catalogue filter matches
everything the work or any of its releases needs, so a search for something only
one release requires still finds the production. It is found by it without being
labelled with it.

## Inheritance fills gaps; it never widens a statement
A release takes its production's hardware **only in the groups it says nothing
about**. This is not a merge, and the difference is what makes the split safe.

A release that names machines has named all of them — it was built for those and
no others — so a production saying "48K and 128K" must not turn a 128K-only
release into one claiming both. The same holds group by group: a release naming
one disk system is not also on another because its production is, and one naming
a beeper soundtrack did not grow an AY. Where a release is silent, its
production's statement is the best knowledge there is, which is what lets a
release stating nothing at all still resolve a machine.

A production may well state more than one of its releases does: it describes the
original, and a re-release or a conversion may be narrower. That is precisely why
inheritance works this way.

One consequence is worth knowing: a release stating no hardware at all inherits
everything, so a production requiring General Sound makes such a release
unplayable in the browser, because that is the one thing the online emulators
cannot reproduce. That is the correct answer — the release was never known to be
free of it — but it does remove a play button that used to appear.

## Filling in from the file
Whenever a release is saved, the hardware its file format implies is filled in.
This only ever adds: nothing an editor chose is removed, and saving twice changes
nothing.

What a format can say is limited. A disk-image extension names the medium, not
the system on it — the same Beta Disk image can hold any of several operating
systems — so a container contributes its interface and nothing more. An
operating system is only concluded from an extension that names one.

Some formats say nothing on their own but become clear once the machine is known,
which the release takes from its production. When the machine is unknown, or when
two formats would imply systems that contradict each other, nothing is filled in
and an editor decides.

Snapshots and raw binaries say nothing about hardware at all.

How it is built: [../features/hardware.md](../features/hardware.md)
