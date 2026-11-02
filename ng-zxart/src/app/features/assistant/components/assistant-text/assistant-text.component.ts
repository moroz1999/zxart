import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {AssistantSegment} from '../../models/assistant-view';

/** Text with the entities it mentions as links, and what a plan step will make named with the step. */
@Component({
  selector: 'zx-assistant-text',
  standalone: true,
  imports: [RouterLink, TranslateModule],
  templateUrl: './assistant-text.component.html',
  styleUrls: ['./assistant-text.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantTextComponent {
  @Input({required: true}) segments: AssistantSegment[] = [];
}
