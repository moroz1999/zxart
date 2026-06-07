import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CollaboratorGroupCardData} from './collaborator-group-card.model';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';

@Component({
  selector: 'zx-collaborator-group-card',
  standalone: true,
  imports: [CommonModule, TranslateModule, TextDirective, HeadingDirective],
  templateUrl: './zx-collaborator-group-card.component.html',
  styleUrl: './zx-collaborator-group-card.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCollaboratorGroupCardComponent {
  @Input() group!: CollaboratorGroupCardData;
}
