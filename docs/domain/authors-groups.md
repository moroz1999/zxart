## Authors and Groups

### author (Author)

#### Purpose
Author of works for ZX Spectrum - artist, musician, programmer. Can have real name and/or nickname.

#### Main Fields
- **realName** - author's real name
- **nickname** - pseudonym/nickname
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

#### Authorship Through Group
When work is created by group:
1. Group is specified in **groups** field (for prods)
2. Individual authors can be specified in **authors** field with roles
3. This allows reflecting both collective and individual authorship

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
