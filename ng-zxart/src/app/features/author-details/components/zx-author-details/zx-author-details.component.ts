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
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';

@Component({
  selector: 'zx-author-details-view',
  standalone: true,
  imports: [
    CommonModule,
    ZxAuthorHeaderComponent,
    ZxAuthorWorksComponent,
    ZxAuthorCollaboratorsComponent,
    ZxStackComponent,
    CommentsListComponent,
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
