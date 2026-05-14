import {Directive, HostBinding, input} from '@angular/core';
import {TypographyVariant} from '../typography.types';

type HeadingVariant = Extract<TypographyVariant, 'display' | 'headline' | 'title'>;

@Directive({
  selector: '[appHeading]',
  standalone: true,
})
export class HeadingDirective {
  readonly appHeading = input<HeadingVariant>('headline');
  readonly tone = input<'primary' | 'muted' | 'danger' | 'inherit'>('primary');
  readonly truncate = input(false);

  @HostBinding('class.app-typography-display')
  get displayClass(): boolean {
    return this.appHeading() === 'display';
  }

  @HostBinding('class.app-typography-headline')
  get headlineClass(): boolean {
    return this.appHeading() === 'headline';
  }

  @HostBinding('class.app-typography-title')
  get titleClass(): boolean {
    return this.appHeading() === 'title';
  }

  @HostBinding('class.app-typography-tone-primary')
  get primaryToneClass(): boolean {
    return this.tone() === 'primary';
  }

  @HostBinding('class.app-typography-tone-muted')
  get mutedToneClass(): boolean {
    return this.tone() === 'muted';
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
