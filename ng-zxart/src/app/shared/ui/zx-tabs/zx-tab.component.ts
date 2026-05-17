import { ChangeDetectionStrategy, Component, ContentChild, Input } from '@angular/core';
import { ZxTabContentDirective } from './zx-tab-content.directive';

@Component({
  selector: 'zx-tab',
  standalone: true,
  template: '',
  styleUrl: './zx-tab.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTabComponent {
  @Input() label = '';
  @Input() count?: number;

  @ContentChild(ZxTabContentDirective) contentDirective?: ZxTabContentDirective;
}
