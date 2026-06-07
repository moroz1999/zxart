import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {
  GroupCollaboratorGroupDto,
  GroupCollaboratorPersonDto,
  GroupCollaboratorsApiService,
} from '../../services/group-collaborators-api.service';
import {ZxCollaboratorsSectionComponent} from '../../../../entities/zx-collaborators-section/zx-collaborators-section.component';

@Component({
  selector: 'zx-group-connections',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxCollaboratorsSectionComponent],
  templateUrl: './zx-group-connections.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupConnectionsComponent implements OnDestroy {
  @Input() elementId = 0;

  people: GroupCollaboratorPersonDto[] = [];
  publishedGroups: GroupCollaboratorGroupDto[] = [];
  loading = false;
  loaded = false;
  requested = false;

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: GroupCollaboratorsApiService,
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
          this.publishedGroups = result.publishedGroups;
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
