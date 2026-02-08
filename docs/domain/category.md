## category (Production Category)

### Purpose
Category classifies zxProd by software type. Defines what the product represents: game, demo, utility, etc.

### Main Fields
- **title** - category title
- Supports localization (different titles for different languages)

### Relation with Production
- **categories** - array of category IDs in zxProd
  - One prod can belong to multiple categories
  - Stored as `numbersArray` in element structure

### Category Types

#### Main Categories
- **Games** - games
  - Arcade
  - Adventure
  - Strategy
  - Puzzle
  - Sport
  - and other game genres
- **Demos** - demonstration programs
  - Intro
  - Demo
  - Megademo
- **Utilities** - utilities
  - Graphics editors
  - Music editors
  - Development tools
  - System utilities
- **Applications** - applications
  - Text editors
  - Databases
  - Educational software

#### Special Categories

##### Compilations
Special categories exist for compilations:
- Defined in `CompilationCategoryIds`
- Indicate that prod is a collection of other products
- Related to `compilationItems` mechanism

### Usage in Interface

#### Filtering
Categories are used for production filtering:
- Catalogue by categories
- Search within category
- Navigation by software types

#### Display
- `ZxProdsCategoryComponent` - component for displaying category production
- Supports different layouts: screenshots, inlays, table
- Data converter: `zxProdCategoriesCatalogueDataResponseConverter`

### Category Hierarchy
Categories can have hierarchical structure:
- Parent categories (e.g., "Games")
- Child categories (e.g., "Arcade Games", "Adventure Games")
- Allows creating detailed classification

### Relations with Other Entities

#### zxProd
- Field **categories** contains array of category IDs
- One prod can have multiple categories
- Categories define product type and genre

#### Compilations
- Special categories for compilations
- If prod has compilation category:
  - Can contain other prods in `compilationItems`
  - Displayed as collection

### Constraints and Rules
1. Prod can belong to multiple categories simultaneously
2. Categories must be logically compatible (e.g., game can be both Arcade and Puzzle)
3. Special compilation categories have special processing logic
4. Categories are used for building catalogue and navigation
5. Category localization is mandatory for multilingual interface
6. Category changes can trigger statistics recalculation
7. Categories can be used in AI for automatic classification (aiRestartCategories)
