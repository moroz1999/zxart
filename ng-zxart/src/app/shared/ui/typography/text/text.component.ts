import {ChangeDetectionStrategy, Component, HostBinding, input} from '@angular/core';
import {TypographyTone, TypographyVariant} from '../typography.types';

@Component({
  selector: 'app-text',
  standalone: true,
  templateUrl: './text.component.html',
  styleUrl: './text.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TextComponent {
  readonly variant = input<TypographyVariant>('body');
  readonly tone = input<TypographyTone>('inherit');
  readonly truncate = input(false);

  @HostBinding('class.app-text')
  readonly textClass = true;

  @HostBinding('class.app-typography-display')
  get displayClass(): boolean {
    return this.variant() === 'display';
  }

  @HostBinding('class.app-typography-headline')
  get headlineClass(): boolean {
    return this.variant() === 'headline';
  }

  @HostBinding('class.app-typography-title')
  get titleClass(): boolean {
    return this.variant() === 'title';
  }

  @HostBinding('class.app-typography-body')
  get bodyClass(): boolean {
    return this.variant() === 'body';
  }

  @HostBinding('class.app-typography-body-sm')
  get bodySmClass(): boolean {
    return this.variant() === 'bodySm';
  }

  @HostBinding('class.app-typography-caption')
  get captionClass(): boolean {
    return this.variant() === 'caption';
  }

  @HostBinding('class.app-typography-label')
  get labelClass(): boolean {
    return this.variant() === 'label';
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
