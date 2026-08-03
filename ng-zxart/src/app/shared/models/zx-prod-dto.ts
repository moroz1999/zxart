export interface ZxProdConnectedItem {
    readonly id: string;
    readonly title: string;
}

export type ZxProdConnectedItems = Array<ZxProdConnectedItem>;

/** Hardware labels live in the SPA and are resolved from this backend code. */
export interface ZxProdHardwareItem {
    readonly id: string;
}

export type ZxProdHardwareItems = Array<ZxProdHardwareItem>;

export interface ZxProdConnectedElementDto {
    readonly id: number;
    readonly title: string;
    readonly structureType: 'author' | 'authorAlias' | 'group' | 'groupAlias' | 'party' | 'zxProdCategory';
}

export interface ZxProdAuthorship {
    readonly id: number;
    readonly structureType: 'author' | 'authorAlias';
    readonly title: string;
    readonly roles: string[];
}

export type ZxProdConnectedElements = Array<ZxProdConnectedElementDto>;

export type LegalStatus =
    'unknown' |
    'allowed' |
    'allowedzxart' |
    'forbidden' |
    'forbiddenzxart' |
    'insales' |
    'mia' |
    'unreleased' |
    'recovered' |
    'donationware';

export interface ZxProdDto {
    readonly id: number;
    readonly title: string;
    readonly structureType: 'zxProd' | 'zxRelease';
    readonly dateCreated: number;
    readonly year?: string;
    readonly youtubeId?: string;
    readonly listImagesUrls?: Array<string>;
    readonly inlaysUrls?: Array<string>;
    readonly hardwareInfo?: ZxProdHardwareItems;
    readonly groupsInfo?: ZxProdConnectedElements;
    readonly publishersInfo?: ZxProdConnectedElements;
    readonly authorsInfoShort?: ZxProdAuthorship[];
    readonly categoriesInfo?: ZxProdConnectedElements;
    readonly languagesInfo?: ZxProdConnectedItems;
    readonly partyInfo?: ZxProdConnectedElementDto;
    readonly partyPlace?: number;
    readonly votes: number;
    readonly votesAmount: number;
    readonly userVote: number;
    readonly denyVoting?: boolean;
    readonly legalStatus?: LegalStatus;
    readonly externalLink?: string;
}
