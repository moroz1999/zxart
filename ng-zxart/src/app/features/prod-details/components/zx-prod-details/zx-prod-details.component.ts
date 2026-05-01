import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable, of} from 'rxjs';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxYoutubeEmbedComponent} from '../../../../shared/ui/zx-youtube-embed/zx-youtube-embed.component';
import {ProdCoreApiService} from '../../services/prod-core-api.service';
import {ProdCoreDto} from '../../models/prod-core.dto';
import {ZxProdInfoTableComponent} from '../zx-prod-info-table/zx-prod-info-table.component';
import {ZxProdEditingControlsComponent} from '../zx-prod-editing-controls/zx-prod-editing-controls.component';
import {ZxProdDescriptionComponent} from '../zx-prod-description/zx-prod-description.component';
import {ZxProdInstructionsComponent} from '../zx-prod-instructions/zx-prod-instructions.component';

@Component({
  selector: 'zx-prod-details',
  standalone: true,
  imports: [
    CommonModule,
    ZxSkeletonComponent,
    ZxYoutubeEmbedComponent,
    ZxProdInfoTableComponent,
    ZxProdEditingControlsComponent,
    ZxProdDescriptionComponent,
    ZxProdInstructionsComponent,
  ],
  templateUrl: './zx-prod-details.component.html',
  styleUrls: ['./zx-prod-details.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDetailsComponent implements OnInit {
  @Input() elementId = 0;

  core$: Observable<ProdCoreDto | null> = of(null);

  constructor(private readonly api: ProdCoreApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      this.core$ = of(null);
      return;
    }
    this.core$ = this.api.getCore(+this.elementId);
  }
}
