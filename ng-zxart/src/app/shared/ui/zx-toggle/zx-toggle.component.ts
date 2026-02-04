import {Component, EventEmitter, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatButtonToggleModule} from '@angular/material/button-toggle';
import {MatIconModule} from '@angular/material/icon';
import {FormsModule} from '@angular/forms';

export interface ZxToggleOption {
  value: string;
  icon?: string;
  label?: string;
}

@Component({
  selector: 'zx-toggle',
  standalone: true,
  imports: [CommonModule, MatButtonToggleModule, MatIconModule, FormsModule],
  templateUrl: './zx-toggle.component.html',
  styleUrl: './zx-toggle.component.scss'
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
