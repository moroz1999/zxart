import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {ZxButtonComponent} from '../zx-button/zx-button.component';

@Component({
  selector: 'zx-edit-button',
  standalone: true,
  imports: [
    SvgIconComponent,
    ZxButtonComponent,
  ],
  templateUrl: './zx-edit-button.component.html',
  styleUrl: './zx-edit-button.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxEditButtonComponent implements OnInit {
  @Input() ariaLabel = '';
  @Input() title = '';
  @Input() size: 'xs' | 'sm' | 'md' = 'sm';
  @Input() color: 'primary' | 'secondary' | 'danger' | 'transparent' | 'outlined' = 'primary';
  @Input() expanded: boolean | null = null;
  @Input() icon: string = 'edit';
  @Output() editClick = new EventEmitter<MouseEvent>();

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}edit.svg`, 'edit')?.subscribe();
    if (this.icon !== 'edit') {
      this.iconReg.loadSvg(`${environment.svgUrl}${this.icon}.svg`, this.icon)?.subscribe();
    }
  }
}
