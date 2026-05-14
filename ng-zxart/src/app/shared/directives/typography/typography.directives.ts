import {Directive, HostBinding} from '@angular/core';
export {HeadingDirective} from '../../ui/typography/directives/heading.directive';
export {TextDirective} from '../../ui/typography/directives/text.directive';

@Directive({
  selector: '[zxHeading1]',
  standalone: true
})
export class ZxHeading1Directive {
  @HostBinding('class.zx-heading-1') className = true;
  @HostBinding('class.app-typography-display') typographyClass = true;
  @HostBinding('class.app-typography-tone-primary') toneClass = true;
}

@Directive({
  selector: '[zxHeading2]',
  standalone: true
})
export class ZxHeading2Directive {
  @HostBinding('class.zx-heading-2') className = true;
  @HostBinding('class.app-typography-headline') typographyClass = true;
  @HostBinding('class.app-typography-tone-primary') toneClass = true;
}

@Directive({
  selector: '[zxHeading3]',
  standalone: true
})
export class ZxHeading3Directive {
  @HostBinding('class.zx-heading-3') className = true;
  @HostBinding('class.app-typography-title') typographyClass = true;
  @HostBinding('class.app-typography-tone-primary') toneClass = true;
}

@Directive({
  selector: '[zxBody]',
  standalone: true
})
export class ZxBodyDirective {
  @HostBinding('class.zx-body') className = true;
  @HostBinding('class.app-typography-body') typographyClass = true;
  @HostBinding('class.app-typography-tone-primary') toneClass = true;
}
@Directive({
  selector: '[zxBodySm]',
  standalone: true
})
export class ZxBodySmDirective {
  @HostBinding('class.zx-body-sm') className = true;
  @HostBinding('class.app-typography-body-sm') typographyClass = true;
  @HostBinding('class.app-typography-tone-primary') toneClass = true;
}

@Directive({
  selector: '[zxBodySmMuted]',
  standalone: true
})
export class ZxBodySmMutedDirective {
  @HostBinding('class.zx-body-sm-muted') className = true;
  @HostBinding('class.app-typography-body-sm') typographyClass = true;
  @HostBinding('class.app-typography-tone-muted') toneClass = true;
}

@Directive({
  selector: '[zxBodyStrong]',
  standalone: true
})
export class ZxBodyStrongDirective {
  @HostBinding('class.zx-body-strong') className = true;
  @HostBinding('class.app-typography-body') typographyClass = true;
  @HostBinding('class.app-typography-tone-strong') toneClass = true;
}

@Directive({
  selector: '[zxCaption]',
  standalone: true
})
export class ZxCaptionDirective {
  @HostBinding('class.zx-caption') className = true;
  @HostBinding('class.app-typography-caption') typographyClass = true;
  @HostBinding('class.app-typography-tone-muted') toneClass = true;
}

@Directive({
  selector: '[zxLink]',
  standalone: true
})
export class ZxLinkDirective {
  @HostBinding('class.zx-link') className = true;
  @HostBinding('class.app-typography-tone-link') toneClass = true;
}

@Directive({
  selector: '[zxLinkAlt]',
  standalone: true
})
export class ZxLinkAltDirective {
  @HostBinding('class.zx-link-alt') className = true;
  @HostBinding('class.app-typography-tone-link-alt') toneClass = true;
}
