import {Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {CommentAuthorDto} from '../../../features/comments/models/comment.dto';
import {ZxCaptionDirective, ZxLinkDirective} from '../../directives/typography/typography.directives';
import {MatIconModule} from '@angular/material/icon';

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

  getIconName(badge: string): string {
    switch (badge.toLowerCase()) {
      case 'vip':
      case 'supporter':
        return 'star';
      case 'volunteer':
        return 'volunteer_activism'; // Or some other material icon that fits
      default:
        return 'person';
    }
  }

  getBadgeClass(badge: string): string {
    return `zx-user__badge--${badge.toLowerCase()}`;
  }
}
