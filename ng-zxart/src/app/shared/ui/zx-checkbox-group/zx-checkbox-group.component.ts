import {ChangeDetectionStrategy, Component} from '@angular/core';

/**
 * Boxed container that lays out several checkbox fields (e.g. zx-checkbox-field)
 * in a recessed, bordered inset as a wrapping row. Used for grouped flag lists
 * such as an entity's restrictions or display toggles.
 */
@Component({
  selector: 'zx-checkbox-group',
  standalone: true,
  templateUrl: './zx-checkbox-group.component.html',
  styleUrl: './zx-checkbox-group.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCheckboxGroupComponent {
}
