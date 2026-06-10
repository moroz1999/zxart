import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {map} from 'rxjs/operators';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {PartyCoreDto} from '../../models/party-core.dto';
import {PartyWorksService} from '../../services/party-works.service';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxProdsListComponent} from '../../../../entities/zx-prods-list/zx-prods-list.component';
import {ZxPicturesListComponent} from '../../../picture-list/components/zx-pictures-list/zx-pictures-list.component';
import {ZxMusicListComponent} from '../../../music-list/components/zx-music-list/zx-music-list.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {ZxSkeletonBoneComponent} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';

interface PartyOverviewVm {
  readonly prods: ZxProd[];
  readonly pictures: ZxPictureDto[];
  readonly tunes: ZxTuneDto[];
  readonly hasAny: boolean;
}

@Component({
  selector: 'zx-party-overview',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxStackComponent,
    ZxPanelComponent,
    ZxProdsListComponent,
    ZxPicturesListComponent,
    ZxMusicListComponent,
    ZxProdsListSkeletonComponent,
    ZxSkeletonBoneComponent,
  ],
  templateUrl: './zx-party-overview.component.html',
  styleUrl: './zx-party-overview.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyOverviewComponent implements OnInit {
  @Input() core!: PartyCoreDto;

  vm$: Observable<PartyOverviewVm | null> = of(null);

  constructor(private readonly partyWorks: PartyWorksService) {}

  ngOnInit(): void {
    this.vm$ = this.partyWorks.getOverview(this.core.id).pipe(
      map(overview => ({
        ...overview,
        hasAny: overview.prods.length > 0 || overview.pictures.length > 0 || overview.tunes.length > 0,
      })),
    );
  }
}
