## Category

### Purpose
A category says what a production is: a game, a demo, a utility, an application.
It is what the software section is browsed by.

### Main Fields
- **title** — the category's name, translated per language

### Relation with Productions
A production carries the categories it belongs to, and it may belong to several
at once — a game can be both arcade and puzzle.

### Category Types

#### Main categories
- **Games**, with genres beneath them: arcade, adventure, strategy, puzzle,
  sport and the rest
- **Demoscene**, with demos, intros, megademos, trackmos and the rest beneath it
- **Press**, with the magazines beneath it
- **Utilities**: graphics and music editors, development and system tools
- **Applications**: text editors, databases, educational software

#### Compilations
Some categories mark a production as a collection of other productions rather
than a work of its own. A production in such a category lists the works it
collects.

### Hierarchy
Categories form a tree: a parent category such as Games holds the genres beneath
it. Anything filed under a genre also belongs to the category above it, so
browsing or filtering by a parent covers everything beneath it.

### Browsing
The software section is one page, and the chosen category is part of its address,
so a category can be linked to and shared. Historical category addresses lead to
the same place.

### Constraints and Rules
1. A production may belong to several categories at once.
2. The categories a production carries must make sense together.
3. Compilation categories change what a production is: a collection rather than a
   single work.
4. A category must be translated for every interface language.
5. Categories can be suggested automatically for a production that has none.

How it is built: [../features/category.md](../features/category.md)
