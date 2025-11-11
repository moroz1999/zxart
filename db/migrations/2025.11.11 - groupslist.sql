CREATE TABLE `engine_module_groupslist` (
  `id` int(10) NOT NULL DEFAULT 0,
  `title` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `items` varchar(255) NOT NULL,
  `languageId` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

ALTER TABLE `engine_module_groupslist`
  ADD PRIMARY KEY (`id`,`languageId`);
COMMIT;

ALTER TABLE `engine_structure_elements` CHANGE `structureType` `structureType` ENUM('actionsLog','adminLanguages','adminTranslation','adminTranslations','adminTranslationsGroup','article','author','authorAlias','authors','authorsCatalogue','authorsList','banner','bannerCategory','banners','catalogues','city','comment','comments','commentsList','countries','countriesList','country','deployments','detailedSearch','dispatchmentLog','errorPage','feedback','feedbackAnswer','file','folder','formFieldsGroup','formInput','formTextArea','game','group','groupAlias','groups','groupsCatalogue','language','letter','linkList','linkListItem','login','logViewer','marketing','metaFilter','metaFilters','musicCatalogue','newsMailAddress','newsMails','newsMailsAddresses','newsMailsGroup','newsMailsGroups','newsMailsTexts','notFoundLog','parser','parties','partiesCatalogue','partiesList','party','passwordReminder','picturesCatalogue','playlist','pressArticle','redirect','redirects','registration','registrationFields','registrationInput','root','search','searchLog','seo','settings','simpleSetting','stats','subMenuList','system','tag','tags','tagsList','translation','translations','translationsGroup','user','userGroup','userGroups','userPlaylists','users','userSystem','visitors','year','zxItemsList','zxMusic','zxPicture','zxProd','zxProdCategories','zxProdCategoriesCatalogue','zxProdCategory','zxProds','zxRelease','groupsList') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;