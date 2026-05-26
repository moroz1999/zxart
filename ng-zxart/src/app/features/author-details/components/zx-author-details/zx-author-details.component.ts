import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable, of} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {AuthorCoreDto} from '../../models/author-core.dto';
import {AuthorCoreApiService} from '../../services/author-core-api.service';
import {ZxAuthorHeaderComponent} from '../zx-author-header/zx-author-header.component';
import {ZxAuthorWorksComponent} from '../zx-author-works/zx-author-works.component';
import {ZxAuthorCollaboratorsComponent} from '../zx-author-collaborators/zx-author-collaborators.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxAuthorMiniDashboardComponent} from '../zx-author-mini-dashboard/zx-author-mini-dashboard.component';
import {ZxAuthorRatingsComponent} from '../zx-author-ratings/zx-author-ratings.component';
import {ZxAuthorCommentsComponent} from '../zx-author-comments/zx-author-comments.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxSkeletonBoneComponent} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';

@Component({
  selector: 'zx-author-details-view',
  standalone: true,
  imports: [
    CommonModule,
    ZxBreadcrumbsComponent,
    ZxAuthorHeaderComponent,
    ZxAuthorWorksComponent,
    ZxAuthorCollaboratorsComponent,
    ZxStackComponent,
    ZxAuthorMiniDashboardComponent,
    ZxAuthorRatingsComponent,
    ZxAuthorCommentsComponent,
    ZxInlineComponent,
    ZxPanelComponent,
    ZxSkeletonBoneComponent,
  ],
  templateUrl: './zx-author-details.component.html',
  styleUrl: './zx-author-details.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorDetailsComponent implements OnInit {
  @Input() elementId = 0;

  core$: Observable<AuthorCoreDto | null> = of(null);

  constructor(private readonly api: AuthorCoreApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      return;
    }
    this.core$ = this.api.getCore(+this.elementId).pipe(shareReplay(1));
  }

}
