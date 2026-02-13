# Comments

## Business Logic
- **Authorization**: Only authorized users can post comments.
- **Targets**: Comments can be attached to various entities (prods, releases, pictures, tunes, authors, etc.) or other comments (replies).
- **Editing and Deletion**:
    - Only the author of the comment has permission to edit or delete it.
    - Editing and deletion are allowed only within a limited time window (default is 2 hours, defined by `EDIT_LIMIT` in `commentElement`).
    - When a comment is deleted, all its replies are deleted automatically. This is handled by the CMS core (`deleteElementData`), which recursively traverses `structure` links.
- **Author Information**:
    - The `author` field in `commentElement` is deprecated. 
    - Real author information is stored via a link of type `author` to a `user` element.
    - If a user is anonymous, the deprecated `author` field might still contain a name.
    - API responses may contain `author = null` when both linked user and legacy author name are missing; clients must handle this case and skip author rendering.
- **Content**:
    - URLs in comment content are automatically converted into clickable HTML links.
    - To get the original content without HTML decorations (e.g., for editing), use `$comment->getValue('content')` on the `commentElement`. This method retrieves the raw storage value from the underlying `dataChunk`.

## Technical Structure
- **Linking**:
    - All comments for an entity are linked via `commentTarget` links.
    - Parent-child relationships in a tree are represented by the same `commentTarget` links (for root entities) and additionally by `structure` links for nested comments.
- **Timestamps**:
    - `dateCreated` and `dateModified` properties contain formatted date strings.
    - For time calculations, use `getCreatedTimestamp()` and `getModifiedTimestamp()` which return UTC Unix timestamps.
- **Permissions**:
    - Upon creation, the author is automatically granted `delete`, `publicReceive` (save), and `publicForm` (edit form) privileges for that specific comment.
