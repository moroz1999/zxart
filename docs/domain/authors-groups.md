## Authors and Groups

### author (Author)

#### Purpose
Author of works for ZX Spectrum - artist, musician, programmer. Can have real name and/or nickname.

#### Main Fields
- **realName** - author's real name
- **nickname** - pseudonym/nickname
- **artCityId** - external author identifier on the Artcity website
- Supports localization (different names for different languages)

#### Relations with Works
- **authorPicture** - link to pictures
- **authorMusic** - link to music
- **authorProd** - link to productions (through authorship with roles)

#### Authorship with Roles
For zxProd authorship includes roles:
- **code** - programming
- **graphics** - graphics
- **music** - music
- **design** - design
- **testing** - testing
- and other roles

Structure: `ZxProdAuthorship { title, url, roles[] }`

#### Original Authorship
- **originalAuthor** - used in zxPicture for ports/remakes
  - Points to author of original work
  - Current author - author of port/adaptation

### author alias (Author Alias)

#### Purpose
Variation of author's nickname. One author can have multiple aliases.

#### Usage
- Different spellings of same nickname
- Historical nickname changes
- Alternative pseudonyms
- Helps in searching and merging works of one author

### group (Group)

#### Purpose
Group of authors - team, company, demoscene group. Unites authors for collaborative work.

#### Group Types
- **Demoscene groups** - demoscene groups
- **Companies** - developer companies
- **Teams** - developer teams
- **Publishers** - publishers

#### Main Fields
- **title** - group title
- Supports localization

#### Relations with Works

##### As Developer
- **zxProdGroups** - developer groups of production
  - Link with zxProd, role child
  - Group created the product

##### As Publisher
- **zxProdPublishers** - production publishers
  - Link with zxProd, role child
  - Group published the product
- **zxReleasePublishers** - release publishers
  - Link with zxRelease, role child
  - Group published specific release
  - Group release lists exclude releases whose parent prod is already linked to the same group as developer or publisher
  - Group release lists show only hacker-release/crack-like release types: adaptation, localization, bugfix, mod, and crack
  - Non-hacker release publisher links are shown in the group's Published tab
- Group own works and Published lists expose filters by linked production categories. Selecting a category includes productions from its full subcategory tree. Published release rows use their parent production categories for this filter.

#### Difference Between Group and Publisher
- **groups** (zxProdGroups) - creators, developers
- **publishers** (zxProdPublishers) - publishers, distributors
- Same group can be both developer and publisher

### group alias (Group Alias)

#### Purpose
Variation of group name and abbreviations. One group can have multiple aliases.

#### Usage
- Different spellings of name
- Abbreviations (e.g., "Sinclair Research" → "SR")
- Historical name changes
- Alternative names in different languages
- Helps in searching and merging works of one group

### Relations Between Authors and Groups

#### Group Membership
Authors can be members of groups:
- One author can be member of multiple groups
- Membership can change over time
- Group can have multiple members
- Edit forms can queue multiple new authors before saving; authorship roles and membership dates are submitted per author ID.
- Group roster data is loaded through a separate endpoint after the group core response.
- Group connections exclude group members and their author aliases from collaborator people.
- The group connections tab is shown only when the core group response confirms real collaborator people or published developer groups.

#### Authorship Through Group
When work is created by group:
1. Group is specified in **groups** field (for prods)
2. Individual authors can be specified in **authors** field with roles
3. This allows reflecting both collective and individual authorship

#### Author Collaborators
- Co-author statistics include shared pictures (`authorPicture`), tunes (`authorMusic`), productions, and releases.
- When shared authorship is recorded against an author alias, the collaborators block displays and aggregates it under the alias's main author.
- The author collaborators block lists groups connected to the same works as the author.
- Product collaborators include developer groups (`zxProdGroups`) and publishers (`zxProdPublishers`) of products where the author has prod authorship.
- Release collaborators include publishers (`zxReleasePublishers`) of releases where the author has release authorship.

#### Author Works Ordering
- Author pictures, tunes, productions, and releases sorted by year use `structure_elements.dateCreated` as the secondary key and element ID as the final stable key.
- In year-grouped author works, an unspecified year (`0`) is displayed as `???`.
- Year-grouped author picture and software lists display one work per row on mobile.
- Author pictures, tunes, productions, and releases apply list filtering, result counting, sorting, and pagination in database queries; services resolve only the selected page of works.

#### Author Production Role Filters
- Author production filters list only distinct roles assigned to that author's productions, independently of pagination and the active role filter.
- Production and release cards on an author page show the complete recorded author list, including the author whose page is open.
- The software tab includes direct prod authorship and release authorship. A release is shown only when its parent prod is not already listed for the same author; release role filters apply to these release rows.
- Productions where the author is a publisher (`zxProdPublishers`, author as link parent) are included in the author's productions list, in the best-works dashboard, and in the production count that drives the software tab visibility. Such productions carry the synthetic `publisher` role, which is offered as a role filter alongside authorship roles.

#### Author Music Sound Type Filters
- Author music filters use the tune `formatGroup` sound type (for example, `ay` or `beeper`), not its file format.
- The available sound types are collected from all tunes attributed to that author, independently of pagination and the selected sound type.

#### Author Votes Display
- The author details page shows paginated votes on all works of the author via `zx-author-ratings` component.
- Backend: `GET /ratings/?action=byAuthor&id={authorId}&page={page}&perPage=20` → `RatingsService::getAuthorRatings()`.
- Work IDs are collected from `structure_links` (authorPicture, authorMusic) and `authorship` table, including all author alias IDs.

#### Author Comments Display
- The author details page shows paginated top-level comments on all works of the author via `zx-author-comments` component.
- Backend: `GET /comments/?action=byAuthor&id={authorId}&page={page}&lang={lang}` → `CommentsService::getAuthorCommentsPaginated()`.
- Comment IDs are found by joining `structure_links` (type=commentTarget) with the author's work IDs.

#### Author Mini Dashboard
- With graphics, music, and software sections present, the dashboard previews 2 pictures, 10 tunes, and 2 productions.
- With exactly two sections present, pictures and productions show up to 4 cards in two columns; music continues to show up to 10 tunes.
- With music as the only section, the dashboard continues to show up to 10 tunes.

#### Author Details Loading
- The author header loads with the core author response because it determines the visible page sections.
- The author header image uses the `authorPhoto` preset; alias pages use the main author's image.
- Content for dashboard, works, collaborators, votes, and comments is mounted only for its active author tab; its API request starts when the rendered block reaches the viewport.
- First loads display skeletons shaped for the target content. Paginated author blocks retain current content with a short opacity fade while the next page loads.

#### Author Details Tabs
- The author details content is organized as tabs: best works, each available work type, collaborators, and discussion.
- Discussion displays votes and comments on the author's works, followed by comments attached directly to the author.

#### Author Alias Details Page
- Author aliases use the same Angular details component as authors.
- An alias page keeps the alias identity and its directly attributed works and comments, while profile metadata such as location, account, links, and technical defaults comes from its referenced author.

### Authors Listing

The `/artists` and `/musicians` roots are catalogue dashboards. Each dashboard
shows active authors for a selectable period of one to five years (two years by
default), the top 50 authors by the relevant work rating, and the 100 most
recently added matching authors and aliases. Active-work authorship recorded
through an alias is displayed as the main author.
Dashboard sections request their data when they enter the viewport. Their initial
loading states use 18 active-author placeholders and table-shaped placeholders
matching the configured 50- and 100-row result limits.

The flat author and alias list is loaded from `/authorlist/`. Each content type
has its own named route with its own `<h1>`, all reusing the shared author
browser (filters + table):

- `/authors` — all authors (heading `author-browser.title.all`)
- `/artists/:letter` — graphics authors (heading `author-browser.title.graphics`)
- `/musicians/:letter` — music authors (heading `author-browser.title.music`)

The content type is fixed per route via the `items` route data (`''`,
`graphics`, or `music`) and forwarded to `/authorlist/` as the `items` query
parameter. The optional `/:letter` path segment applies the A–Z selector;
country, city, search, and pagination are query parameters. The browser builds
its letter, filter, and pagination links against the route's `basePath`.

With no letter selected and no active search or filter, the browser lists the
most recently added authors (sorted by descending id) under an
`author-browser.recent` heading instead of the full alphabetical catalogue; the
A–Z selector offers letters only (no "all" option). The shared group browser
(`/groups`, `/groups/:letter`) renders a page-level `menu.groups` display
heading, the same letters-only A–Z selector, and the same recent-default rule
under a `group-browser.recent` heading, reading its letter from the route.

Author-list responses contain identifiers and display data. Angular builds
author, group, country-filter, and city-filter links from those identifiers.

### Constraints and Rules

#### For Authors
1. Author must have either realName or nickname (or both)
2. Author aliases reference main author record
3. originalAuthor is used only for ports/adaptations
4. One author can have multiple roles in one work
5. Localization allows specifying names in different languages

#### For Groups
1. Group must have title
2. Group aliases reference main group record
3. Group can be both developer and publisher simultaneously
4. For prods: groups = developers, publishers = publishers
5. For releases: publishers can differ from parent prod publishers
6. Localization allows specifying titles in different languages

#### For Aliases
1. Alias always references main record (author or group)
2. Aliases are used for search and data normalization
3. Display usually uses main name, not alias
4. Aliases help when importing data from external sources
