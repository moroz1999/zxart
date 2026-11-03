---
name: add-authors
description: Adding people (authors) and their aliases, e.g. from a scene page, a group roster or a credits list.
---

- First call `load_skill` with `find-authors` and find every person as it says.
- Person exists: update it. Do not create a new one.
- Another name of an existing author: create an alias (`create_author_alias`), not a new author.
- `title` is the nickname.
- Set real name, country, city and alias dates only when the source says them.
- Country and city: find them with `find_locations`.
- Group membership: `set_group_member`. Person was in the group under an alias: make the alias the member.
- Do not touch `displayInMusic`, `displayInGraphics`, the deny flags and the music settings.
- One plan: new authors, then their aliases, then memberships.
