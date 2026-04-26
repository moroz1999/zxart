# Trickster CMS Unused Modules Cleanup

## Scope

Remove unused modules only from `trickster-cms/cms` and `trickster-cms/homepage`.
Do not modify anything in `project/`.

## Current Status

- [x] Read the relevant project instructions and CMS documentation.
- [x] Connected to the database container.
- [x] Verified the actual structure table and type column in the database.
- [x] Collected distinct module types from the database.
- [x] Compared database types with existing module folders in `trickster-cms`.
- [x] Start module removal after user approval.
- [x] Removed config references to deleted modules from `cms/config` and `homepage/config`.
- [x] Added SQL migration to drop dedicated tables of removed modules.
- [x] Removed all remaining `newsMail*` and `newsMails*` code, templates, assets, and config references.
- [x] Added SQL migration to drop newsmails-related tables.
- [x] Removed all remaining `banner`, `bannerCategory`, and `banners` code, templates, assets, and config references.
- [x] Added SQL migration to drop banner-related tables.

## Database Notes

- Database container: `zxart-db`
- Database: `zxart`
- Actual table: `engine_structure_elements`
- Actual type column: `structureType`

## Distinct Types Found In Database

```text
actionsLog, adminLanguages, adminTranslation, adminTranslations, adminTranslationsGroup,
article, author, authorAlias, authors, authorsCatalogue, authorsList, banner,
bannerCategory, banners, catalogues, city, comment, comments, commentsList,
countries, countriesList, country, deployments, detailedSearch, dispatchmentLog,
errorPage, feedback, feedbackAnswer, file, folder, formFieldsGroup, formInput,
formTextArea, game, group, groupAlias, groups, groupsCatalogue, groupsList,
language, letter, linkList, linkListItem, login, logViewer, marketing, metaFilter,
metaFilters, musicCatalogue, newsMailAddress, newsMails, newsMailsAddresses,
newsMailsGroup, newsMailsGroups, newsMailsTexts, notFoundLog, parser, parties,
partiesCatalogue, partiesList, party, passwordReminder, picturesCatalogue, playlist,
pressArticle, redirect, redirects, registration, registrationFields, registrationInput,
root, search, searchLog, seo, settings, simpleSetting, stats, subMenuList, system,
tag, tags, tagsList, translation, translations, translationsGroup, user, userGroup,
userGroups, userPlaylists, userSystem, users, visitors, year, zxItemsList, zxMusic,
zxPicture, zxProd, zxProdCategories, zxProdCategoriesCatalogue, zxProdCategory,
zxProds, zxRelease
```

## Planned Removals

### CMS package

- Keep `logs` by user decision.
- Keep `positions` by user decision.
- Keep `privileges` by user decision.
- Keep `shared` by user decision.
- [x] `translationsExport`

### Homepage package

- [x] `currencies`
- [x] `currency`
- [x] `currencySelector`
- [x] `event`
- [x] `events`
- [x] `eventsList`
- [x] `facebookSocialPlugin`
- [x] `formCheckBox`
- [x] `formDateInput`
- [x] `formFileInput`
- [x] `formSelect`
- [x] `formSelectOption`
- [x] `gallery`
- [x] `galleryImage`
- [x] `genericIcon`
- [x] `googleSocialPlugin`
- [x] `latestNews`
- [x] `map`
- [x] `news`
- [x] `newsList`
- [x] `newsMailForm`
- [x] `newsMailsText`
- [x] `newsMailSubContentCategories`
- [x] `newsMailSubContentCategory`
- [x] `newsMailTextSubContent`
- [x] `personnel`
- [x] `personnelList`
- [x] `poll`
- [x] `pollAnswer`
- [x] `pollPlaceholder`
- [x] `pollQuestion`
- [x] `polls`
- [x] `production`
- [x] `selectedEvents`
- [x] `selectedGalleries`
- [x] `service`
- Keep `shared` by user decision.
- [x] `shortcut`
- [x] `socialNetworks`
- [x] `socialPlugins`
- [x] `socialPost`
- [x] `socialPosts`
- [x] `subArticle`
- [x] `tabsWidget`
- [x] `widget`

## Execution Rule

After approval, remove modules one by one.
For each module, remove the module code and the related `css` and `js` files, then mark the item as done in this file.

## Remaining Config Mentions

Only non-registration keys remain in `homepage/config`, such as Google event IDs and image preset names in `images-desktop.php`.

## Database Cleanup

Migration file: `db/migrations/2026.04.26 - remove unused homepage module tables.sql`

Additional migration file: `db/migrations/2026.04.26 - remove newsmails module tables.sql`

Additional migration file: `db/migrations/2026.04.26 - remove banner module tables.sql`
