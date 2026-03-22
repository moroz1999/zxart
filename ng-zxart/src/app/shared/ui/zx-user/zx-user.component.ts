import {ChangeDetectionStrategy, Component, HostBinding, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CommentAuthorDto} from '../../../features/comments/models/comment.dto';
import {ZxCaptionDirective, ZxLinkDirective} from '../../directives/typography/typography.directives';
import {environment} from '../../../../environments/environment';

@Component({
  selector: 'zx-user',
  standalone: true,
  imports: [
    CommonModule,
    SvgIconComponent,
    ZxCaptionDirective,
    ZxLinkDirective,
  ],
  templateUrl: './zx-user.component.html',
  styleUrls: ['./zx-user.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxUserComponent implements OnInit {
  @HostBinding('class.zx-user') className = true;
  @Input({required: true}) user!: CommentAuthorDto;
  @Input() linkDisabled = false;
  @Input() namePrimary = false;

  @HostBinding('style.--zx-user-name-color')
  get nameColorVar(): string | null {
    return this.namePrimary ? 'var(--text-color)' : null;
  }

  constructor(private iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}star.svg`, 'star')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}volunteer.svg`, 'volunteer')?.subscribe();
  }

  getIconName(badge: string): string {
    switch (badge.toLowerCase()) {
      case 'vip':
      case 'supporter':
        return 'star';
      case 'volunteer':
        return 'volunteer';
      default:
        return 'person';
    }
  }

  getBadgeClass(badge: string): string {
    return `zx-user__badge--${badge.toLowerCase()}`;
  }
}
