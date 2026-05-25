import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {
  AuthorCollaboratorsApiService,
  CollaboratorGroupDto,
  CollaboratorPersonDto,
} from '../../services/author-collaborators-api.service';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';

@Component({
  selector: 'zx-author-collaborators',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxPanelComponent],
  templateUrl: './zx-author-collaborators.component.html',
  styleUrl: './zx-author-collaborators.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorCollaboratorsComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;

  people: CollaboratorPersonDto[] = [];
  groups: CollaboratorGroupDto[] = [];
  loading = true;
  error = false;

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: AuthorCollaboratorsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
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
