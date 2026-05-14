import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {HeadingDirective} from '../typography/directives/heading.directive';
import {environment} from '../../../../environments/environment';

@Component({
  selector: 'zx-dialog',
  standalone: true,
  imports: [CommonModule, TranslateModule, SvgIconComponent, ZxButtonComponent, HeadingDirective],
  templateUrl: './zx-dialog.component.html',
  styleUrl: './zx-dialog.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxDialogComponent implements OnInit {
  @Input() titleKey?: string;
  @Input() title?: string;
  @Input() showClose = true;
  @Input() customHeader = false;
  @Output() closeClick = new EventEmitter<void>();

  constructor(private iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}close.svg`, 'close')?.subscribe();
  }
}
