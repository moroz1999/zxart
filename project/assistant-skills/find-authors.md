---
name: find-authors
description: Finding which existing author a name from a source means. Load it before crediting, adding or changing any person.
---

- Search the person with `search_authors` by name. Aliases are found too.
- Several authors with this name: choose the most similar one. Check:
  - same real name, country or city (they are in the search results);
  - member of the group from the source: `search_authors` with `name` and `groupIds`;
  - worked with another author of this production: `search_authors` with `name` and `coAuthorId`;
  - worked on productions of this group: `search_authors` with `name` and `prods.groupIds`.
- Write in the plan why you chose this author.
- Nothing matches, but there are authors with this name: ask the user. Do not guess.
- Nobody with this name: the person is new.
- Name of a group: it is a group, not an author. Search it with `search_groups`.
