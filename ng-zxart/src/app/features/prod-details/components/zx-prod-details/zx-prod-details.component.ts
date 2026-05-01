import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable, of} from 'rxjs';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ProdCoreApiService} from '../../services/prod-core-api.service';
import {ProdCoreDto} from '../../models/prod-core.dto';

@Component({
  selector: 'zx-prod-details',
  standalone: true,
  imports: [CommonModule, ZxSkeletonComponent],
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
