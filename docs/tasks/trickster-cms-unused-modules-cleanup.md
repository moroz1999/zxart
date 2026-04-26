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
- [ ] Start module removal after user approval.

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

- [ ] `logs`
- [ ] `positions`
- [ ] `privileges`
- [ ] `shared`
- [ ] `translationsExport`

### Homepage package

- [ ] `currencies`
- [ ] `currency`
- [ ] `currencySelector`
- [ ] `event`
- [ ] `events`
- [ ] `eventsList`
- [ ] `facebookSocialPlugin`
- [ ] `formCheckBox`
- [ ] `formDateInput`
- [ ] `formFileInput`
- [ ] `formSelect`
- [ ] `formSelectOption`
- [ ] `gallery`
- [ ] `galleryImage`
- [ ] `genericIcon`
- [ ] `googleSocialPlugin`
- [ ] `latestNews`
- [ ] `map`
- [ ] `news`
- [ ] `newsList`
- [ ] `newsMailForm`
- [ ] `newsMailsText`
- [ ] `newsMailSubContentCategories`
- [ ] `newsMailSubContentCategory`
- [ ] `newsMailTextSubContent`
- [ ] `personnel`
- [ ] `personnelList`
- [ ] `poll`
- [ ] `pollAnswer`
- [ ] `pollPlaceholder`
- [ ] `pollQuestion`
- [ ] `polls`
- [ ] `production`
- [ ] `selectedEvents`
- [ ] `selectedGalleries`
- [ ] `service`
- [ ] `shared`
- [ ] `shortcut`
- [ ] `socialNetworks`
- [ ] `socialPlugins`
- [ ] `socialPost`
- [ ] `socialPosts`
- [ ] `subArticle`
- [ ] `tabsWidget`
- [ ] `widget`

## Execution Rule

After approval, remove modules one by one.
For each module, remove the module code and the related `css` and `js` files, then mark the item as done in this file.
