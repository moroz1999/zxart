import {StructureElement} from './structure-element';
import {
  ZxProdDto,
  ZxProdConnectedItems,
  ZxProdConnectedElements,
  ZxProdConnectedElementDto, ZxProdAuthorship, LegalStatus,
} from './zx-prod-dto';

export class ZxProd extends StructureElement {
  public structureType: string;
  public title: string;
  public year: string = '';
  public youtubeId: string = '';
  public dateCreated: number;
  public imagesUrls: Array<string> = [];
  public inlaysUrls: Array<string> = [];
  public hardwareInfo: ZxProdConnectedItems = [];
  public groupsInfo: ZxProdConnectedElements = [];
  public publishersInfo: ZxProdConnectedElements = [];
  public authorsInfoShort: ZxProdAuthorship[] = [];
  public categoriesInfo: ZxProdConnectedElements = [];
  public languagesInfo: ZxProdConnectedItems = [];
  public partyInfo?: ZxProdConnectedElementDto;
  public releaseType?: string;
  public releaseFormat?: string;
  public partyPlace: number = 0;
  public votes: number;
  public userVote: number;
  public denyVoting: boolean;
  public legalStatus: LegalStatus;
  public externalLink: string;
  public loadingImageUrl?: string;

  constructor(data: ZxProdDto) {
    super(data);
    this.title = data.title;
    this.structureType = data.structureType;
    this.dateCreated = data.dateCreated * 1000;
    if (data.year) {
      this.year = data.year;
    }
    if (data.youtubeId) {
      this.youtubeId = data.youtubeId;
    }
    if (data.hardwareInfo) {
      this.hardwareInfo = data.hardwareInfo;
    }
    if (data.groupsInfo) {
      this.groupsInfo = data.groupsInfo;
    }
    if (data.publishersInfo) {
      this.publishersInfo = data.publishersInfo;
    }
    if (data.authorsInfoShort) {
      this.authorsInfoShort = data.authorsInfoShort;
    }
    if (data.categoriesInfo) {
      this.categoriesInfo = data.categoriesInfo;
    }
    if (data.languagesInfo) {
      this.languagesInfo = data.languagesInfo;
    }
    if (data.partyInfo) {
      this.partyInfo = data.partyInfo;
    }
    if (data.partyPlace) {
      this.partyPlace = data.partyPlace;
    }
    if (data.releaseType) {
      this.releaseType = data.releaseType;
    }
    if (data.releaseFormat) {
      this.releaseFormat = data.releaseFormat;
    }
    if (data.listImagesUrls) {
      this.imagesUrls = data.listImagesUrls;
      this.imagesUrls = this.imagesUrls.map(image => image.replace('http://localhost', 'https://zxart.ee'));
    }
    if (data.inlaysUrls) {
      this.inlaysUrls = data.inlaysUrls;
      this.inlaysUrls = this.inlaysUrls.map(image => image.replace('http://localhost', 'https://zxart.ee'));
    }
    this.votes = data.votes;
    this.userVote = data.userVote;
    this.denyVoting = data.denyVoting ?? false;
    this.externalLink = data.externalLink ?? '';
    this.legalStatus = data.legalStatus ?? 'unknown';

    if (this.imagesUrls.length > 0) {
      this.loadingImageUrl = this.imagesUrls.splice(0, 1)[0];
    }
  }
}
