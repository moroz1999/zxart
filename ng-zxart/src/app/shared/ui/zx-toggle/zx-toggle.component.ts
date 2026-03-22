import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {SvgIconComponent} from 'angular-svg-icon';

export interface ZxToggleOption {
  value: string;
  icon?: string;
  label?: string;
}

@Component({
  selector: 'zx-toggle',
  standalone: true,
  imports: [CommonModule, SvgIconComponent],
  templateUrl: './zx-toggle.component.html',
  styleUrl: './zx-toggle.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxToggleComponent {
  @Input() options: ZxToggleOption[] = [];
  @Input() value: string = '';
  @Input() size: 'sm' | 'md' = 'md';
  @Output() valueChange = new EventEmitter<string>();

  onValueChange(newValue: string): void {
    this.value = newValue;
    this.valueChange.emit(newValue);
  }
}
