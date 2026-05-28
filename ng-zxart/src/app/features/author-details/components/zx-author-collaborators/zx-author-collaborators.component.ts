import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {
  AuthorCollaboratorsApiService,
  CollaboratorGroupDto,
  CollaboratorPersonDto,
} from '../../services/author-collaborators-api.service';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxAuthorPersonCardComponent} from '../zx-author-person-card/zx-author-person-card.component';
import {ZxRowSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-row-skeleton/zx-row-skeleton.component';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

@Component({
  selector: 'zx-author-collaborators',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxPanelComponent, ZxAuthorPersonCardComponent, ZxRowSkeletonComponent, InViewportDirective, HeadingDirective, TextDirective],
  templateUrl: './zx-author-collaborators.component.html',
  styleUrl: './zx-author-collaborators.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorCollaboratorsComponent implements OnDestroy {
  @Input() elementId = 0;

  people: CollaboratorPersonDto[] = [];
  groups: CollaboratorGroupDto[] = [];
  loading = true;
  error = false;
  requested = false;

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: AuthorCollaboratorsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.requested) {
      return;
    }
    this.requested = true;
    this.subscriptions.add(
      this.api.getCollaborators(this.elementId).subscribe({
        next: result => {
          this.loading = false;
          this.people = result.people;
          this.groups = result.groups;
          this.cdr.markForCheck();
        },
        error: () => {
          this.loading = false;
          this.error = true;
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }
}
