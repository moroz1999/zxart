CREATE TABLE `engine_module_pressarticle2` (
                                               `id` int(11) NOT NULL,
                                               `languageId` tinyint(4) NOT NULL,
                                               `introduction` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
                                               `content` mediumtext CHARACTER SET utf8mb4 NOT NULL,
                                               `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
                                               `allowComments` tinyint(4) NOT NULL,
                                               `externalLink` text CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `engine_module_pressarticle` ADD PRIMARY KEY (`id`, `languageId`);
ALTER TABLE `engine_structure_links` CHANGE `type` `type` ENUM('type','adFilesSelector','authorMusic','authorPicture','authorsCatalogue','ayTrack','bannerCategoryBanner','cityLink','commentTarget','compilation','connectedFile','countries','countryLink','displayinmenu','displayinmenumobile','feedbackAnswer','foreignRelative','gameLink','groupsCatalogue','groupSub','headerContent','infoFilesSelector','inlayFilesSelector','mapFilesSelector','mobileMenu','newsmailGroup','partiesCatalogue','partyMusic','partyPicture','partyProd','playlist','registrationField','registrationUserGroup','rightColumn','rzx','screenshotsSelector','softCatalogue','structure','submenulist','tagLink','tagsList','userRelation','zxProdCategory','zxProdGroups','zxProdPublishers','zxReleasePublishers','originalAuthor','series','prodArticle','pressAuthor') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `engine_module_pressarticle` ADD `hasAiData` TINYINT NOT NULL AFTER `externalLink`;

CREATE TABLE `engine_module_pressarticle_meta` (
                                                   `id` int(11) NOT NULL,
                                                   `h1` varchar(255) NOT NULL,
                                                   `metaTitle` varchar(255) NOT NULL,
                                                   `metaDescription` text NOT NULL,
                                                   `generatedDescription` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `engine_module_pressarticle_meta`
    ADD PRIMARY KEY (`id`);