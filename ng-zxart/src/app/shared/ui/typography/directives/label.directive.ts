import {Directive, HostBinding, input} from '@angular/core';
import {TypographyTone} from '../typography.types';

@Directive({
  selector: '[appLabel]',
  standalone: true,
})
export class LabelDirective {
  readonly tone = input<TypographyTone>('primary');
  readonly truncate = input(false);

  @HostBinding('class.app-typography-label')
  readonly labelClass = true;

  @HostBinding('class.app-typography-tone-primary')
  get primaryToneClass(): boolean {
    return this.tone() === 'primary';
  }

  @HostBinding('class.app-typography-tone-muted')
  get mutedToneClass(): boolean {
    return this.tone() === 'muted';
  }

  @HostBinding('class.app-typography-tone-strong')
  get strongToneClass(): boolean {
    return this.tone() === 'strong';
  }

  @HostBinding('class.app-typography-tone-link')
  get linkToneClass(): boolean {
    return this.tone() === 'link';
  }

  @HostBinding('class.app-typography-tone-muted-link')
  get mutedLinkToneClass(): boolean {
    return this.tone() === 'muted-link';
  }

  @HostBinding('class.app-typography-tone-link-alt')
  get linkAltToneClass(): boolean {
    return this.tone() === 'link-alt';
  }

  @HostBinding('class.app-typography-tone-danger')
  get dangerToneClass(): boolean {
    return this.tone() === 'danger';
  }

  @HostBinding('class.app-typography-tone-inherit')
  get inheritToneClass(): boolean {
    return this.tone() === 'inherit';
  }

  @HostBinding('class.app-typography-truncate')
  get truncateClass(): boolean {
    return this.truncate();
  }
}
