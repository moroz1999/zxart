import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {
  AuthorCollaboratorsApiService,
  CollaboratorGroupDto,
  CollaboratorPersonDto,
} from '../../services/author-collaborators-api.service';
import {ZxCollaboratorsSectionComponent} from '../../../../entities/zx-collaborators-section/zx-collaborators-section.component';

@Component({
  selector: 'zx-author-collaborators',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxCollaboratorsSectionComponent],
  templateUrl: './zx-author-collaborators.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorCollaboratorsComponent implements OnDestroy {
  @Input() elementId = 0;

  people: CollaboratorPersonDto[] = [];
  groups: CollaboratorGroupDto[] = [];
  loading = false;
  loaded = false;
  requested = false;

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: AuthorCollaboratorsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onLoad(): void {
    if (this.requested) {
      return;
    }
    this.requested = true;
    this.loading = true;
    this.subscriptions.add(
      this.api.getCollaborators(this.elementId).subscribe({
        next: result => {
          this.people = result.people;
          this.groups = result.groups;
          this.loading = false;
          this.loaded = true;
          this.cdr.markForCheck();
        },
        error: () => {
          this.loading = false;
          this.loaded = true;
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }
}
