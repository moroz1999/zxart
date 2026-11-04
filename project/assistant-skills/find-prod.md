---
name: find-prod
description: Finding whether a production from a source is already in the catalogue. Load it before adding or changing a production from a source.
---

- A same title is only a candidate. Short, generic titles (Info, Demo, Intro, Music, Tetris) say almost nothing.
- Find candidates both ways:
  - `search_prods` by title and by alt title;
  - `search_prods` by the source's people (`authorIds`, `groupIds`): the work may be listed under another title.
- Compare each candidate with what the source gives. What the source leaves out (no year, no group) is no evidence either way and no reason to ask.
- People: the source's authors and groups against the candidate's authors and groups, each found with `find-authors`; an alias counts as its author or group.
  - A person of the source belongs to a group of the candidate when they are its member (`get_group`) or are credited on its other productions (`search_authors` with `name` and `prods.groupIds`).
- Also compare the year (a re-release may be later), party and compo, the authors' and groups' country and city, hardware, category.
- Same work: some of the source's people are the candidate's people, and nothing contradicts.
- Another work: none of the source's people is among the candidate's authors or the people of its groups, or the year, party or country clearly disagree. Add it as new; in the plan name the namesake you checked and why it is another work.
- Ask the user only when the people cannot be compared: the source names nobody, or the candidate credits no author and no group. Then do not create or change anything.
- Never fit an existing production to the source. Different authors, group, year or party mean another work or a wrong record; the user decides which.
- Same work: add only what the source adds. A stored field the source contradicts stays; mention it in your answer.
