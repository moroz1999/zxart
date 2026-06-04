import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {ChangeDetectionStrategy, Component, HostListener, Input, OnInit} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxStackComponent} from '../zx-stack/zx-stack.component';
import {PopoverAnimation} from '../../animations/popover-animations';

@Component({
  selector: 'zx-icon-popover',
  standalone: true,
  imports: [
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    SvgIconComponent,
    ZxButtonComponent,
    ZxStackComponent,
  ],
  templateUrl: './zx-icon-popover.component.html',
  styleUrls: ['./zx-icon-popover.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [PopoverAnimation],
})
export class ZxIconPopoverComponent implements OnInit {
  @Input() iconName = 'edit';
  @Input() ariaLabel = '';
  @Input() size: 'xs' | 'sm' | 'md' = 'sm';
  @Input() color: 'primary' | 'secondary' | 'danger' | 'transparent' | 'outlined' = 'primary';
  @Input() stackSpacing: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl' = 'xs';

  popoverOpen = false;

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
    {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 4},
    {originX: 'start', originY: 'top', overlayX: 'start', overlayY: 'bottom', offsetY: -4},
  ];

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}${this.iconName}.svg`, this.iconName)?.subscribe();
  }

  togglePopover(event: Event): void {
    event.stopPropagation();
    this.popoverOpen = !this.popoverOpen;
  }

  closePopover(): void {
    this.popoverOpen = false;
  }

  @HostListener('document:keydown.escape')
  onEscape(): void {
    if (this.popoverOpen) {
      this.closePopover();
    }
  }
}
