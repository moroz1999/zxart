import {Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {CommentAuthorDto} from '../../../features/comments/models/comment.dto';
import {ZxCaptionDirective, ZxLinkDirective} from '../../directives/typography/typography.directives';
import {MatIconModule, MatIconRegistry} from '@angular/material/icon';
import {DomSanitizer} from '@angular/platform-browser';
import {environment} from '../../../../environments/environment';

@Component({
  selector: 'zx-user',
  standalone: true,
  imports: [
    CommonModule,
    MatIconModule,
    ZxCaptionDirective,
    ZxLinkDirective
  ],
  templateUrl: './zx-user.component.html',
  styleUrls: ['./zx-user.component.scss']
})
export class ZxUserComponent {
  @HostBinding('class.zx-user') className = true;
  @Input({required: true}) user!: CommentAuthorDto;

  constructor(iconRegistry: MatIconRegistry, sanitizer: DomSanitizer) {
    iconRegistry.addSvgIcon(
      'volunteer',
      sanitizer.bypassSecurityTrustResourceUrl(`${environment.svgUrl}volunteer.svg`)
    );
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

  isSvgIcon(badge: string): boolean {
    return badge.toLowerCase() === 'volunteer';
  }

  getBadgeClass(badge: string): string {
    return `zx-user__badge--${badge.toLowerCase()}`;
  }
}
