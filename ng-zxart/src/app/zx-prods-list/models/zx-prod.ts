import {StructureElement} from '../../shared/models/structure-element';
import {ZxProdDto} from './zx-prod-dto';

export class ZxProd extends StructureElement {
  public title: string;
  public year: string = '';
  public imagesUrls: Array<string> = [];
  public hardware: Array<string> = [];
  public groupsTitles: Array<string> = [];
  public publishersTitles: Array<string> = [];
  public categoriesTitles: Array<string> = [];
  public languageTitles: Array<string> = [];
  public partyTitle: string = '';
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
    if (data.hardware) {
      this.hardware = data.hardware;
    }
    if (data.groupsTitles) {
      this.groupsTitles = data.groupsTitles;
    }
    if (data.publishersTitles) {
      this.publishersTitles = data.publishersTitles;
    }
    if (data.categoriesTitles) {
      this.categoriesTitles = data.categoriesTitles;
    }
    if (data.languageTitles) {
      this.languageTitles = data.languageTitles;
    }
    if (data.partyTitle) {
      this.partyTitle = data.partyTitle;
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
