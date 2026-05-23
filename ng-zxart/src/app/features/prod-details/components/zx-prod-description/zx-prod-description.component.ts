import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {
  ZxSkeletonBoneComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ProdDescriptionApiService} from '../../services/prod-description-api.service';
import {ProdDescriptionDto} from '../../models/prod-description.dto';

@Component({
  selector: 'zx-prod-description',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxSkeletonBoneComponent, HeadingDirective, TextDirective, ZxStackComponent],
  templateUrl: './zx-prod-description.component.html',
  styleUrls: ['./zx-prod-description.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDescriptionComponent implements OnInit {
  @Input({required: true}) elementId!: number;

  data$: Observable<ProdDescriptionDto | null> = of(null);

  readonly skeletonLines = [0, 1, 2];

  constructor(private readonly api: ProdDescriptionApiService) {}

  ngOnInit(): void {
    this.data$ = this.api.getDescription(this.elementId);
  }
}
