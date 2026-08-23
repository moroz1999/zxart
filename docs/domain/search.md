## Search

### Full search
One search covers the whole archive. The visitor types a phrase, optionally
restricts it to certain kinds of entity, and pages through the results. The
phrase, the kinds and the page are part of the address, so a search can be
linked to and shared.

Results of every kind come as one alphabetical list, and a page is a window into
it, so paging walks that one list instead of re-ordering every page. An entity
that exists in several languages is listed once.

The quick search in the site header answers the same question while typing, but
looks at titles only rather than at full content. It has a single page of
results, so it is ordered by closeness to the phrase instead: an exact title
first, then titles beginning with it, then titles containing it.

### Detailed search
Graphics and music have a search of their own, because they are looked for by
properties nothing else has.

Graphics can be narrowed by title, year range, rating, competition place, screen
format, and by whether the picture was made live at a party, is inspired by
another work, is one of a series, or belongs to a production. Tags can be
required or excluded, and authors can be narrowed by the country and city they
live in. The results are either the pictures themselves or the authors who made
them, and either can be ordered and paged. Every set of results can also be
fetched through the public export API or downloaded as one archive.

Music is searched the same way, with the sound group and file format standing in
for the screen format, and the title search also looking inside the track file's
own title.

Every filter is part of the address, so a search can be linked to and shared.

### File search
A visitor who has a file but does not know what is in it can upload it and get
back the releases its contents belong to. The file is unpacked as deep as it
goes — archives, disk images and tapes — and every item inside is identified.
Items nothing matches are marked as not found and can be listed on their own.
There is no search by typed file name.

How it is built: [../features/search.md](../features/search.md)
