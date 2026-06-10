import {ZxProdDto} from '../../../shared/models/zx-prod-dto';
import {ZxProd} from '../../../shared/models/zx-prod';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

/**
 * Raw `/party-overview/` response — the winning entry of each compo, grouped by medium.
 */
export interface PartyOverviewResponse {
  readonly prods: ZxProdDto[];
  readonly pictures: ZxPictureDto[];
  readonly tunes: ZxTuneDto[];
}

/**
 * Overview winners ready for the dashboard (prods wrapped as {@link ZxProd} instances).
 */
export interface PartyOverview {
  readonly prods: ZxProd[];
  readonly pictures: ZxPictureDto[];
  readonly tunes: ZxTuneDto[];
}
