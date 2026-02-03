import {Directive, HostBinding} from '@angular/core';

@Directive({
  selector: '[zxHeading1]',
  standalone: true
})
export class ZxHeading1Directive {
  @HostBinding('class.zx-heading-1') className = true;
}

@Directive({
  selector: '[zxHeading2]',
  standalone: true
})
export class ZxHeading2Directive {
  @HostBinding('class.zx-heading-2') className = true;
}

@Directive({
  selector: '[zxHeading3]',
  standalone: true
})
export class ZxHeading3Directive {
  @HostBinding('class.zx-heading-3') className = true;
}

@Directive({
  selector: '[zxBody]',
  standalone: true
})
export class ZxBodyDirective {
  @HostBinding('class.zx-body') className = true;
}

@Directive({
  selector: '[zxBodyStrong]',
  standalone: true
})
export class ZxBodyStrongDirective {
  @HostBinding('class.zx-body-strong') className = true;
}

@Directive({
  selector: '[zxCaption]',
  standalone: true
})
export class ZxCaptionDirective {
  @HostBinding('class.zx-caption') className = true;
}

@Directive({
  selector: '[zxLink]',
  standalone: true
})
export class ZxLinkDirective {
  @HostBinding('class.zx-link') className = true;
}

@Directive({
  selector: '[zxLinkAlt]',
  standalone: true
})
export class ZxLinkAltDirective {
  @HostBinding('class.zx-link-alt') className = true;
}
