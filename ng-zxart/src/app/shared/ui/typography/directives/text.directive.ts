import {Directive, HostBinding, input} from '@angular/core';
import {TypographyTone, TypographyVariant} from '../typography.types';

type TextVariant = Extract<TypographyVariant, 'body' | 'bodySm' | 'caption' | 'label'>;

@Directive({
  selector: '[appText]',
  standalone: true,
})
export class TextDirective {
  readonly appText = input<TextVariant>('body');
  readonly tone = input<TypographyTone>('primary');
  readonly truncate = input(false);

  @HostBinding('class.app-typography-body')
  get bodyClass(): boolean {
    return this.appText() === 'body';
  }

  @HostBinding('class.app-typography-body-sm')
  get bodySmClass(): boolean {
    return this.appText() === 'bodySm';
  }

  @HostBinding('class.app-typography-caption')
  get captionClass(): boolean {
    return this.appText() === 'caption';
  }

  @HostBinding('class.app-typography-label')
  get labelClass(): boolean {
    return this.appText() === 'label';
  }

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
