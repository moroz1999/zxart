import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {CollaboratorPersonCardData} from '../zx-collaborator-person-card/collaborator-person-card.model';
import {CollaboratorGroupCardData} from '../zx-collaborator-group-card/collaborator-group-card.model';
import {ZxCollaboratorPersonCardComponent} from '../zx-collaborator-person-card/zx-collaborator-person-card.component';
import {ZxCollaboratorGroupCardComponent} from '../zx-collaborator-group-card/zx-collaborator-group-card.component';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxRowSkeletonComponent} from '../../shared/ui/zx-skeleton/components/zx-row-skeleton/zx-row-skeleton.component';
import {InViewportDirective} from '../../shared/directives/in-viewport.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';

/**
 * Presentational two-column collaborators panel (people + groups), shared by the author
 * "Worked with" and group "Connections" tabs. Lazy-loads via {@link load} when scrolled
 * into view; the host wrapper owns the data fetching and passes already-translated labels.
 */
@Component({
  selector: 'zx-collaborators-section',
  standalone: true,
  imports: [
    CommonModule,
    ZxCollaboratorPersonCardComponent,
    ZxCollaboratorGroupCardComponent,
    ZxPanelComponent,
    ZxRowSkeletonComponent,
    InViewportDirective,
    TextDirective,
  ],
  templateUrl: './zx-collaborators-section.component.html',
  styleUrl: './zx-collaborators-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCollaboratorsSectionComponent {
  @Input() title = '';
  @Input() peopleLabel = '';
  @Input() groupsLabel = '';
  @Input() loading = false;
  @Input() loaded = false;
  @Input() people: CollaboratorPersonCardData[] = [];
  @Input() groups: CollaboratorGroupCardData[] = [];

  @Output() load = new EventEmitter<void>();
}
