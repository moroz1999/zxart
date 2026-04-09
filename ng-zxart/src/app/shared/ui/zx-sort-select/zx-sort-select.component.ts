import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {ZxSelectComponent, ZxSelectOption} from '../zx-select/zx-select.component';

@Component({
  selector: 'zx-sort-select',
  standalone: true,
  imports: [FormsModule, TranslateModule, ZxSelectComponent],
  template: `
    <label>{{ labelKey | translate }}</label>
    <zx-select
      [(ngModel)]="value"
      (ngModelChange)="valueChange.emit($event)"
      [options]="options"
    ></zx-select>
  `,
  styles: [`:host { display: flex; align-items: center; gap: var(--gap-sm); }`],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxSortSelectComponent {
  @Input() labelKey = '';
  @Input() options: ZxSelectOption[] = [];
  @Input() value = '';
  @Output() valueChange = new EventEmitter<string>();
}
