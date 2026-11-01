import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {RouterLink} from '@angular/router';
import {AssistantSegment} from '../../models/assistant-view';

/** Text with the entities it mentions as links. */
@Component({
  selector: 'zx-assistant-text',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './assistant-text.component.html',
  styleUrls: ['./assistant-text.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantTextComponent {
  @Input({required: true}) segments: AssistantSegment[] = [];
}
