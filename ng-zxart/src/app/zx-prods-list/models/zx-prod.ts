import {StructureElement} from '../../shared/models/structure-element';
import {ZxProdDto} from './zx-prod-dto';

export class ZxProd extends StructureElement {
  public title: string;
  public imagesUrls: Array<string>;
  public votes: number;
  public votePercent: number;
  public imageUrl?: string;
  public image2Url?: string;

  constructor(data: ZxProdDto) {
    super(data);
    this.title = data.title;
    this.imagesUrls = data.imagesUrls;
    this.votes = data.votes;
    this.votePercent = data.votePercent;

    if (this.imagesUrls) {
      if (this.imagesUrls.length > 0) {
        this.imageUrl = this.imagesUrls[0];
      }
      if (this.imagesUrls.length > 1) {
        this.image2Url = this.imagesUrls[1];
      }
    }
  }
}
