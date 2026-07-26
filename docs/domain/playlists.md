# Playlists

A playlist is a user's personal collection of works (pictures, tunes, prods).
Every user owns their playlists; there are no shared or public playlists.

## Structure placement

A playlist element is linked as a **child of its owner's user element**, not of
the `userPlaylists` catalogue. That user branch is not reachable by a path walk
from the site root, so `structureManager::getElementById()` cannot resolve a
playlist. Playlists must be loaded through the owner's link list and attached
directly to the catalogue (`getElementsByIdList($ids, $catalogueId, true)`),
which is what `userPlaylistsElement::getPlaylists()` does. Any code that looks
up a playlist by id must go through that list.

`getPlaylists()` caches its result on the catalogue element for the request, so
a playlist deleted during the same request is still present in the list and has
to be filtered out of the response explicitly.

## API

`/playlists-data/` serves the current user's own playlists; anonymous requests
get 401.

- `GET` — the full list.
- `POST ?action=create|rename|delete` — one mutation, then the full updated list.

Every response returns the complete list, so the frontend re-renders from a
single source of truth instead of patching local state. Each entry carries its
content counts (`pictures`, `tunes`, `prods`), read from the `playlist` links.

## Frontend

`/playlists` lists the playlists as panels: name, content counts, and rename /
delete controls. Deletion asks for confirmation through `ConfirmDialogService`.
Removing a playlist does not remove the works it points at.

`PlaylistsApiService` owns the list state — the page subscribes and never
triggers loading itself.
