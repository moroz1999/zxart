import {StructureElement} from '../../shared/models/structure-element';
import {
  ZxProdDto,
  ZxProdConnectedItems,
  ZxProdConnectedElements,
  ZxProdConnectedElementDto,
} from './zx-prod-dto';

export class ZxProd extends StructureElement {
  public title: string;
  public year: string = '';
  public youtubeId: string = '';
  public imagesUrls: Array<string> = [];
  public hardwareInfo: ZxProdConnectedItems = [];
  public groupsInfo: ZxProdConnectedElements = [];
  public publishersInfo: ZxProdConnectedElements = [];
  public categoriesInfo: ZxProdConnectedElements = [];
  public languagesInfo: ZxProdConnectedItems = [];
  public partyInfo?: ZxProdConnectedElementDto;
  public partyPlace: number = 0;
  public votes: number;
  public votePercent: number;
  public loadingImageUrl?: string;

  constructor(data: ZxProdDto) {
    super(data);
    this.title = data.title;
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
    if (data.listImagesUrls) {
      this.imagesUrls = data.listImagesUrls;
      this.imagesUrls = this.imagesUrls.map(image => image.replace('http://localhost', 'https://zxart.ee'));
    }
    this.votes = data.votes;
    this.votePercent = data.votePercent;

    if (this.imagesUrls.length > 0) {
      this.loadingImageUrl = this.imagesUrls.splice(0, 1)[0];
    }
  }
}
